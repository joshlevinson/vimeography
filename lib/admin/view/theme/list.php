<?php

class Vimeography_Theme_List extends Mustache 
{
    
	public function __construct()
	{
		//wp_register_style('cloud.css', plugins_url('media/css/cloud.css', __FILE__ ));
		//wp_enqueue_style('cloud.css');
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
	
	private static function _install_theme()
	{
		$result = unzip_file( $file, $to );
	}
           
}