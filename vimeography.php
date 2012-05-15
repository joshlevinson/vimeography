<?php
/*
	Plugin Name: Vimeography
	Plugin URI: http://vimeography.com
	Description: Vimeography is the easiest way to set up a custom Vimeo gallery on your site.
	Version: 0.2
	Author: Dave Kiss
	Author URI: http://davekiss.com
	License: MIT
*/

if (!function_exists('json_decode'))
	wp_die('Vimeography needs the JSON PHP extension.');
	
global $wpdb;

// Define constants
define( 'VIMEOGRAPHY_URL', plugin_dir_url(__FILE__) );
define( 'VIMEOGRAPHY_PATH', plugin_dir_path(__FILE__) );
define( 'VIMEOGRAPHY_BASENAME', plugin_basename( __FILE__ ) );
define( 'VIMEOGRAPHY_VERSION', '0.2');
define( 'VIMEOGRAPHY_GALLERY_TABLE', $wpdb->prefix . "vimeography_gallery");
define( 'VIMEOGRAPHY_GALLERY_META_TABLE', $wpdb->prefix . "vimeography_gallery_meta");

require_once(VIMEOGRAPHY_PATH . '/vendor/mustache/Mustache.php');
		
class Vimeography
{								
	public function __construct()
	{
		add_action( 'admin_init', array(&$this, 'vimeography_requires_wordpress_version') );
		add_action( 'admin_init', array(&$this, 'vimeography_init' ) );
		add_action( 'admin_menu', array(&$this, 'vimeography_add_menu'));
		
		register_activation_hook(VIMEOGRAPHY_BASENAME, array(&$this, 'vimeography_create_tables'));
		register_uninstall_hook(VIMEOGRAPHY_BASENAME, 'vimeography_delete_tables');
		
		add_filter( 'plugin_action_links', array(&$this, 'vimeography_filter_plugin_actions'), 10, 2 );
		add_shortcode('vimeography', array(&$this, 'vimeography_shortcode'));
		
		// Add shortcode support for widgets  
		add_filter('widget_text', 'do_shortcode');
	}
	
	/**
	 * Check the wordpress version is compatible, and disable plugin if not.
	 * 
	 * @access public
	 * @return void
	 */
	public function vimeography_requires_wordpress_version() {
		global $wp_version;
		$plugin = plugin_basename( __FILE__ );
		$plugin_data = get_plugin_data( __FILE__, false );
	
		if ( version_compare($wp_version, "3.3", "<" ) ) {
			if( is_plugin_active($plugin) ) {
				deactivate_plugins( $plugin );
				wp_die( "'".$plugin_data['Name']."' requires WordPress 3.3 or higher, and has been deactivated! Please upgrade WordPress and try again.<br /><br />Back to <a href='".admin_url()."'>WordPress admin</a>." );
			}
		}
	}
			
	/**
	 * Init plugin options to white list our options
	 * Runs when the admin_init hook fires and registers the plugin with settings api
	 * 
	 * @access public
	 * @return void
	 */
	public function vimeography_init(){
		register_setting( 'vimeography_advanced_settings', 'vimeography_advanced_settings', array(&$this, 'vimeography_validate_advanced_settings') );
		
		wp_register_style( 'bootstrap_css', VIMEOGRAPHY_URL.'media/css/bootstrap.min.css');
		wp_register_style( 'bootstrap_responsive_css', VIMEOGRAPHY_URL.'media/css/bootstrap-responsive.min.css');
		wp_register_style( 'vimeography-admin.css', VIMEOGRAPHY_URL.'media/css/admin.css');
		wp_register_script( 'bootstrap_tab_js', VIMEOGRAPHY_URL.'media/js/bootstrap-tab.js');
		wp_register_script( 'bootstrap_alert_js', VIMEOGRAPHY_URL.'media/js/bootstrap-alert.js');
		wp_register_script( 'vimeography-admin.js', VIMEOGRAPHY_URL.'media/js/admin.js', 'jquery');
		
		wp_enqueue_style( 'bootstrap_css');
		wp_enqueue_style( 'bootstrap_responsive_css');
		wp_enqueue_style( 'vimeography-admin.css');
		wp_enqueue_script( 'jquery');
		wp_enqueue_script( 'bootstrap_tab_js');
		wp_enqueue_script( 'bootstrap_alert_js');
		wp_enqueue_script( 'vimeography-admin.js');
	}
	
	/**
	 * Add Settings link to "installed plugins" admin page.
	 * 
	 * @access public
	 * @param mixed $links
	 * @param mixed $file
	 * @return void
	 */
	public function vimeography_filter_plugin_actions($links, $file)
	{		
		if ( $file == VIMEOGRAPHY_BASENAME )
		{
			$settings_link = '<a href="admin.php?page=vimeography-edit-galleries">' . __('Settings') . '</a>';
			if (!in_array($settings_link, $links))
				array_unshift( $links, $settings_link ); // before other links
		}
		return $links;
	}

	/**
	 * Adds a new top level menu to the admin menu.
	 * 
	 * @access public
	 * @return void
	 */
	public function vimeography_add_menu()
	{
		add_menu_page( 'Vimeography Page Title', 'Vimeography', 'manage_options', 'vimeography-edit-galleries', '' );
		add_submenu_page( 'vimeography-edit-galleries', 'Edit Galleries', 'Edit Galleries', 'manage_options', 'vimeography-edit-galleries', array(&$this, 'vimeography_render_template' ));
		add_submenu_page( 'vimeography-edit-galleries', 'New Gallery', 'New Gallery', 'manage_options', 'vimeography-new-gallery', array(&$this, 'vimeography_render_template' ));
		add_submenu_page( 'vimeography-edit-galleries', 'My Themes', 'My Themes', 'manage_options', 'vimeography-my-themes', array(&$this, 'vimeography_render_template' ));
		add_submenu_page( 'vimeography-edit-galleries', 'Buy Themes', 'Buy Themes', 'manage_options', 'vimeography-buy-themes', array(&$this, 'vimeography_render_template' ));
		add_submenu_page( 'vimeography-edit-galleries', 'Vimeography Pro', 'Vimeography Pro', 'manage_options', 'vimeography-pro', array(&$this, 'vimeography_render_template' ));
		add_submenu_page( 'vimeography-edit-galleries', 'Help', 'Help', 'manage_options', 'vimeography-help', array(&$this, 'vimeography_render_template' ));
	}
	
	public function vimeography_render_template()
	{
		if ( !current_user_can( 'manage_options' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}
				
		switch(current_filter())
		{
			case 'vimeography_page_vimeography-new-gallery':
				require_once(VIMEOGRAPHY_PATH . 'lib/admin/view/gallery/new.php');
				$mustache = new Vimeography_Gallery_New();
				$template = $this->_load_template('gallery/new');
				break;
			case 'toplevel_page_vimeography-edit-galleries':
				if (isset($_GET['id']))
				{
					require_once(VIMEOGRAPHY_PATH . 'lib/admin/view/gallery/edit.php');
					$mustache = new Vimeography_Gallery_Edit();
					$template = $this->_load_template('gallery/edit');
				}
				else
				{
					require_once(VIMEOGRAPHY_PATH . 'lib/admin/view/gallery/list.php');
					$mustache = new Vimeography_Gallery_List();
					$template = $this->_load_template('gallery/list');
				}				
				break;
			case 'vimeography_page_vimeography-my-themes':
				require_once(VIMEOGRAPHY_PATH . 'lib/admin/view/theme/list.php');
				$mustache = new Vimeography_Theme_List();
				$template = $this->_load_template('theme/list');
				break;
			case 'vimeography_page_vimeography-buy-themes':
				require_once(VIMEOGRAPHY_PATH . 'lib/admin/view/theme/buy.php');
				$mustache = new Vimeography_Theme_Buy();
				$template = $this->_load_template('theme/buy');
				break;
			case 'vimeography_page_vimeography-pro':
				require_once(VIMEOGRAPHY_PATH . 'lib/admin/view/vimeography/pro.php');
				$mustache = new Vimeography_Pro();
				$template = $this->_load_template('vimeography/pro');
				break;
			case 'vimeography_page_vimeography-help':
				require_once(VIMEOGRAPHY_PATH . 'lib/admin/view/vimeography/help.php');
				$mustache = new Vimeography_Help();
				$template = $this->_load_template('vimeography/help');
				break;
			default:
				wp_die( __('The admin template for "'.current_filter().'" cannot be found.') );
			break;
		}
		echo $mustache->render($template);
	}
	
	protected function _load_template($name)
	{
		$path = VIMEOGRAPHY_PATH . 'lib/admin/templates/' . $name .'.mustache';
		if (! $result = @file_get_contents($path))
			wp_die('The admin template "'.$name.'" cannot be found.');
		return $result;
	}
		
	/**
	 * Create tables and define defaults when plugin is activated.
	 * 
	 * @access public
	 * @return void
	 */
	public function vimeography_create_tables() {
		global $wpdb;
		
		delete_option('vimeography_default_settings');
		delete_option('vimeography_advanced_settings');
		
		add_option('vimeography_advanced_settings', array(
			'active' => FALSE,
			'client_id' => '',
			'client_secret' => '',
			'access_token' => '',
			'access_token_secret' => '',
		));
		
		add_option('vimeography_default_settings', array(
			'source_type' => 'channel',
			'source_name' => 'hd',
			'featured_video' => '',
			'cache_timeout' => 3600,
			'theme_name' => 'journey',
		));
			      
		$sql = 'CREATE TABLE '.VIMEOGRAPHY_GALLERY_TABLE.' (
		id mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
		title varchar(150) NOT NULL,
		date_created datetime NOT NULL,
		is_active tinyint(1) NOT NULL,
		PRIMARY  KEY  (id)
		);
		CREATE TABLE '.VIMEOGRAPHY_GALLERY_META_TABLE.' (
		id mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
		gallery_id mediumint(8) unsigned NOT NULL,
		source_type varchar(50) NOT NULL,
		source_name varchar(50) NOT NULL,
		featured_video int(9) unsigned DEFAULT NULL,
		cache_timeout mediumint(7) NOT NULL,
		theme_name varchar(50) NOT NULL,
		PRIMARY  KEY  (id)
		);
		';
			
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
		add_option("vimeography_db_version", VIMEOGRAPHY_VERSION);
	}
	
	/**
	 * Delete options table entries ONLY when plugin deactivated AND deleted
	 * 
	 * @access public
	 * @return void
	 */
	public static function vimeography_delete_tables() {
		delete_option('vimeography_advanced_settings');
		delete_option('vimeography_default_settings');
		delete_option('vimeography_db_version');
		
		global $wpdb;
				
		$wpdb->query('DROP TABLE '.VIMEOGRAPHY_GALLERY_TABLE.', '.VIMEOGRAPHY_GALLERY_META_TABLE);
	}
															   	
	/**
	 * Read the shortcode and return the output.
	 * example:
	 * [vimeography from='user' named='davekiss' theme='apple']
	 * 
	 * @access public
	 * @param mixed $atts
	 * @return void
	 */
	public function vimeography_shortcode($atts)
	{

		// Get admin panel options
		$default_settings = get_option('vimeography_default_settings');

		// Get shortcode attributes
		$settings = shortcode_atts( array(
			'id' => '',
			'theme' => $default_settings['theme_name'],
			'featured' => $default_settings['featured_video'],
			'from' => $default_settings['source_type'],
			'named' => $default_settings['source_name'],
			'cache' => $default_settings['cache_timeout'],
			'width' => '',
			'height' => '',
		), $atts );
		
		if (intval($settings['id']))
		{
			global $wpdb;
			$gallery_info = $wpdb->get_results('SELECT * from '.VIMEOGRAPHY_GALLERY_META_TABLE.' AS meta JOIN '.VIMEOGRAPHY_GALLERY_TABLE.' AS gallery ON meta.gallery_id = gallery.id WHERE meta.gallery_id = '.$settings['id'].' LIMIT 1;');
			if ($gallery_info)
			{
				$settings['theme'] = $gallery_info[0]->theme_name;
				$settings['featured'] = $gallery_info[0]->featured_video;
				$settings['from'] = $gallery_info[0]->source_type;
				$settings['named'] = $gallery_info[0]->source_name;
				$settings['cache'] = $gallery_info[0]->cache_timeout;
			}
		}
		
		try
		{
			require_once(VIMEOGRAPHY_PATH . 'lib/core.php');
		    $vimeography = Vimeography_Core::factory('videos', $settings);
		    
			// if cache is set, render it. otherwise, get the json, set the cache, and render it		
			if (($vimeography_data = $this->get_vimeography_cache($settings['id'])) === FALSE)
			{
		    	// cache not set, let's do a new request to the vimeo API and cache it
		        $vimeography_data = $vimeography->get('videos');
		        $transient = $this->set_vimeography_cache($settings['id'], $vimeography_data, $settings['cache']);
			}
			return $vimeography->render($vimeography_data);
		}
		catch (Vimeography_Exception $e)
		{
			return "Vimeography error: ".$e->getMessage();
		}
	}
		
	/**
	 * Get the JSON data stored in the Vimeography cache for the provided gallery id.
	 * 
	 * @access public
	 * @static
	 * @param mixed $id
	 * @return void
	 */
	public static function get_vimeography_cache($id)
	{
		return FALSE === ( $vimeography_cache_results = get_transient( 'vimeography_cache_'.$id ) ) ? FALSE : $vimeography_cache_results;
		
	    /*if ( FALSE === ( $vimeography_cache_results = get_transient( 'vimeography_cache' ) ) ) {
	    	return FALSE;
	    }
	    
	    return $vimeography_cache_results;*/
    }
    
    /**
     * Set the JSON data to the Vimeography cache for the provided gallery id.
     * 
     * @access public
     * @static
     * @param mixed $id
     * @param mixed $data
     * @param mixed $cache_limit
     * @return void
     */
    public static function set_vimeography_cache($id, $data, $cache_limit)
    {
		return set_transient( 'vimeography_cache_'.$id, $data, $cache_limit );
    }
    
    /**
     * Clear the Vimeography cache for the provided gallery id.
     * 
     * @access public
     * @static
     * @param mixed $id
     * @return void
     */
    public static function delete_vimeography_cache($id)
    {
    	return delete_transient('vimeography_cache_'.$id);
    }
	
}

new Vimeography;