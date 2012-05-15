<?php

class Vimeography_Themes_Journey extends Mustache
{
    public $data;
    public $featured;
    
	public function __construct()
	{
		wp_register_script('aw-showcase', plugins_url('media/js/plugins/jquery.aw-showcase.min.js', __FILE__ ), array('jquery'));
		wp_register_script('journey.js', plugins_url('media/js/journey.js', __FILE__ ), array('jquery'));
		wp_register_style('showcase.css', plugins_url('media/css/plugins/showcase/showcase.css', __FILE__ ));
		wp_register_style('journey.css', plugins_url('media/css/journey.css', __FILE__ ));
		
		wp_enqueue_script('jquery');
		wp_enqueue_script('aw-showcase');
		wp_enqueue_script('journey.js');
		wp_enqueue_style('showcase.css');
		wp_enqueue_style('journey.css');
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