<?php

class Vimeography_Themes_Loaded extends Mustache
{
    public $data;
    public $featured;
    
	public function __construct()
	{
		wp_register_script('expander', plugins_url('media/js/plugins/jquery.expander.min.js', __FILE__ ), array('jquery'));
		wp_register_script('froogaloop', 'http://a.vimeocdn.com/js/froogaloop2.min.js');
		wp_register_script('coloring.js', plugins_url('media/js/plugins/jquery.coloring.js', __FILE__ ), array('jquery'));
		wp_register_script('loaded.js', plugins_url('media/js/loaded.js', __FILE__ ), array('jquery', 'coloring.js'));
		wp_register_style('loaded.css', plugins_url('media/css/loaded.css', __FILE__ ));
		
		wp_enqueue_script('jquery');
		wp_enqueue_script('expander');		
		wp_enqueue_script('froogaloop');
		wp_enqueue_script('coloring.js');		
		wp_enqueue_script('loaded.js');		
		wp_enqueue_style('loaded.css');
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
    	$row = array();
    	$i = 1;
    	
    	foreach($this->data as $item)
    	{
			if ($item->duration AND ! strpos($item->duration, ':'))
			{
				$item->duration = $helpers->seconds_to_minutes($item->duration);
			}
			
			/* BUG! the remaining videos that are not a modulo of 4 are discared. we could lose up to three videos! */
			$row['row'][] = $item;
			
			if ($i % 4 == 0) 
			{
				$items[] = $row;
				$row = array();
			}
			
			$i++;
    	}
    	
    	return $items;
    }
}