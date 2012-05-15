<?php

class Vimeography_Gallery_Edit extends Mustache
{
	public $tab_to_show;
	public $messages = array();
	
	public $gallery;
	
	public function __construct()
	{
		//wp_register_style('cloud.css', plugins_url('media/css/cloud.css', __FILE__ ));
		//wp_enqueue_style('cloud.css');
		
		if (isset($_POST))
			$this->_validate_form();

		global $wpdb;
		
		$gallery_id = $wpdb->escape(intval($_GET['id']));
		$this->gallery = $wpdb->get_results('SELECT * from '.VIMEOGRAPHY_GALLERY_META_TABLE.' AS meta JOIN '.VIMEOGRAPHY_GALLERY_TABLE.' AS gallery ON meta.gallery_id = gallery.id WHERE meta.gallery_id = '.$gallery_id.' LIMIT 1;');
		if (! $this->gallery)
			$this->messages[] = array('type' => 'error', 'heading' => 'Uh oh.', 'message' => 'That gallery no longer exists. It\'s gone. Kaput!');
			
		if (isset($_GET['created']) && $_GET['created'] == 1)
		{
			$this->tab_to_show = 'appearance';
			$this->messages[] = array('type' => 'success', 'heading' => 'Gallery created.', 'message' => 'Welp, that was easy.');
		}
		
	}
	
	public function admin_url()
	{
		return get_admin_url().'admin.php?page=vimeography-';
	}
					
	public function vimeography()
	{
		return do_shortcode( "[vimeography id='".$this->gallery[0]->id."']" );
	}
	
	public function selected()
	{
		return array(
			$this->gallery[0]->source_type => TRUE,
			$this->gallery[0]->cache_timeout => TRUE,
		);
	}
	
	public function gallery()
	{
		$this->gallery[0]->featured_video = $this->gallery[0]->featured_video == 0 ? '' : $this->gallery[0]->featured_video;
		return $this->gallery;
	}
	
	public function themes()
	{
		$themes = array();
		
		$theme_names = $this->_get_vimeography_themes();
		
		foreach ($theme_names as $theme_name)
		{
			$theme = array();
			
			$local_path = VIMEOGRAPHY_PATH . 'themes/' . $theme_name . '/' . $theme_name .'.jpg';
			
			$theme['thumbnail'] = file_exists($local_path) ? VIMEOGRAPHY_URL . 'themes/' . $theme_name . '/' . $theme_name .'.jpg' : 'http://placekitten.com/g/200/150';
			$theme['name'] = $theme_name;
			$theme['description'] = 'is a beautiful thumbnail slider coupled with descriptions, titles and playcounts.';
			$theme['active'] = $theme_name === $this->gallery[0]->theme_name ? TRUE : FALSE;
			
			$themes[] = $theme;
		}
				
		return $themes;
	}
	
	/**
	 * Finds list of installed Vimeography themes by getting the directories in the theme folder.
	 * 
	 * @access public
	 * @static
	 * @return array of themes
	 */
	private static function _get_vimeography_themes() {
		$themes = array();
		
		$directories = glob(VIMEOGRAPHY_PATH . 'themes/*' , GLOB_ONLYDIR);
		
		foreach ($directories as $dir)
		{
			$theme_name = substr($dir, strrpos($dir, '/')+1);
			$themes[] = $theme_name;
		}
		
		return $themes;
	}
			
	protected function _validate_form()
	{
		global $wpdb;
		$id = $wpdb->escape(intval($_GET['id']));
		
		if (!empty($_POST['vimeography_basic_settings']))
		{
			$messages = $this->vimeography_validate_basic_settings($id, $_POST);
		}
		elseif (!empty($_POST['vimeography_appearance_settings']))
		{
			$messages = $this->vimeography_validate_appearance_settings($id, $_POST);
		}
		elseif (!empty($_POST['vimeography_advanced_settings']))
		{
			$messages = $this->vimeography_validate_advanced_settings($id, $_POST);
		}
		else
		{
			return FALSE;
		}		
	}
		
	private function vimeography_validate_basic_settings($id, $input)
	{
		try
		{
			global $wpdb;
			
			$settings['source_type'] = $wpdb->escape(wp_filter_nohtml_kses($input['vimeography_basic_settings']['source']));
			$settings['source_name'] = $wpdb->escape(wp_filter_nohtml_kses($input['vimeography_basic_settings']['named']));
			$settings['title'] = $wpdb->escape(wp_filter_nohtml_kses($input['vimeography_basic_settings']['gallery_title']));
					
			if ($wpdb->update( VIMEOGRAPHY_GALLERY_TABLE, array('title' => $settings['title']), array( 'id' => $id ) ) === FALSE)
			{
				throw new Exception('Your basic gallery title and settings were not updated.');
			}
			else
			{			
				if ($wpdb->update( VIMEOGRAPHY_GALLERY_META_TABLE, array('source_name' => $settings['source_name'], 'source_type' => $settings['source_type']), array( 'gallery_id' => $id ) ) === FALSE)
				{
					//$wpdb->print_error();
					throw new Exception('Your basic gallery settings were not updated.');
				}
			}
			
			$this->delete_vimeography_cache($id);
			$this->messages[] = array('type' => 'success', 'heading' => __('Settings updated.'), 'message' => __('Nice work. You are pretty good at this.'));
			$this->tab_to_show = 'basic-settings';
		}
		catch (Exception $e)
		{
			$this->messages[] = array('type' => 'error', 'heading' => 'Ruh roh.', 'message' => $e->getMessage());
		}
        
	}
	
	private function vimeography_validate_appearance_settings($id, $input)
	{
		try
		{
			global $wpdb;
			$settings['theme_name'] = $wpdb->escape(wp_filter_nohtml_kses($input['vimeography_appearance_settings']['theme_name']));
					
			$result = $wpdb->update( VIMEOGRAPHY_GALLERY_META_TABLE, array('theme_name' => $settings['theme_name']), array( 'gallery_id' => $id ) );
			if ($result === FALSE)
				throw new Exception('Your theme could not be updated.');
			
        	$this->messages[] = array('type' => 'success', 'heading' => __('Theme updated.'), 'message' => __('You are now using the "') . $settings['theme_name'] . __('" theme.'));
        	$this->tab_to_show = 'appearance';
		}
		catch (Exception $e)
		{
			$this->messages[] = array('type' => 'error', 'heading' => 'Ruh roh.', 'message' => $e->getMessage());
		}
	}
	
	private function vimeography_validate_advanced_settings($id, $input)
	{
		try
		{
			global $wpdb;
			$settings['cache_timeout'] = $wpdb->escape(wp_filter_nohtml_kses($input['vimeography_advanced_settings']['cache_timeout']));
			$settings['featured_video'] = $wpdb->escape(wp_filter_nohtml_kses($input['vimeography_advanced_settings']['featured_video']));
								
			$result = $wpdb->update( VIMEOGRAPHY_GALLERY_META_TABLE, array('cache_timeout' => $settings['cache_timeout'], 'featured_video' => $settings['featured_video']), array( 'gallery_id' => $id ) );
			
			if ($result === FALSE)
				throw new Exception('Your advanced settings could not be updated.');
				
			$this->delete_vimeography_cache($id);
			$this->messages[] = array('type' => 'success', 'heading' => __('Settings updated.'), 'message' => __('Nice work. You are pretty good at this.'));
        	$this->tab_to_show = 'advanced-settings';
		}
		catch (Exception $e)
		{
			$this->messages[] = array('type' => 'error', 'heading' => 'Ruh roh.', 'message' => $e->getMessage());
		}
	}
	
	public static function delete_vimeography_cache($id)
    {
    	return delete_transient('vimeography_cache_'.$id);
    }

}