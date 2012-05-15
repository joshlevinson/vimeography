<?php

class Vimeography_Themes_Default extends Mustache
{
    public $data;
    public $featured;
    
	public function __construct()
	{
		wp_register_style('default.css', plugins_url('media/css/default.css', __FILE__ ));		
		wp_register_style('bootstrap', plugins_url('media/css/bootstrap.css', __FILE__ ));		
		wp_enqueue_style('default.css');
		wp_enqueue_style('bootstrap');
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