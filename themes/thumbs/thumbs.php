<?php

class Vimeography_Themes_Thumbs extends Mustache
{
    public $data;
    public $featured;
    
	public function __construct()
	{
		wp_register_script('expander', plugins_url('media/js/plugins/jquery.expander.min.js', __FILE__ ), array('jquery'));
		wp_register_script('froogaloop', 'http://a.vimeocdn.com/js/froogaloop2.min.js');
		wp_register_script('thumbs.js', plugins_url('media/js/thumbs.js', __FILE__ ), array('jquery'));
		wp_register_style('thumbs.css', plugins_url('media/css/thumbs.css', __FILE__ ));
		
		wp_enqueue_script('jquery');
		wp_enqueue_script('expander');		
		wp_enqueue_script('froogaloop');		
		wp_enqueue_script('thumbs.js');		
		wp_enqueue_style('thumbs.css');
	}
	        
    public function info()
    {
    	// optional helpers
    	require_once(VIMEOGRAPHY_PATH .'lib/helpers.php');
    	$helpers = new Vimeography_Helpers;
    	
    	// add featured video to the beginning of the array
    	if (is_array($this->featured))
    		array_unshift($this->data, $this->featured[0]);
    	
    	$items = array();
    	    	
    	foreach($this->data as $item)
    	{
			if ($item->duration AND ! strpos($item->duration, ':'))
			{
				$item->duration = $helpers->seconds_to_minutes($item->duration);
			}
			$items[] = $item;
    	}
    	
    	return $items;
    }
}