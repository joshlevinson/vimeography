<?php

class Vimeography_Themes_Cloud extends Mustache 
{
    public $data;
    public $featured;
    
	public function __construct()
	{
		wp_register_style('cloud.css', plugins_url('media/css/cloud.css', __FILE__ ));
		wp_enqueue_style('cloud.css');
	}
    
    public function info()
    {
    	// optional helpers
    	require_once(VIMEOGRAPHY_PATH .'lib/helpers.php');
    	$helpers = new Vimeography_Helpers;
    	
    	// add featured video to the beginning of the array
    	if (is_array($this->featured))
    		array_unshift($this->data, $this->featured[0]);
    	    	
    	foreach($this->data as $item)
    	{
			if ($item->duration AND ! strpos($item->duration, ':'))
			{
				$item->duration = $helpers->seconds_to_minutes($item->duration);
			}
			
			$plays = $item->stats_number_of_plays;
			
			switch ($plays)
			{
				case ($plays < 100):
					$item->popularity = 'not-popular';
					break;
				case (101 < $plays AND $plays < 500):
					$item->popularity = 'not-very-popular';
					break;
				case (501 < $plays AND $plays < 5000):
					$item->popularity = 'somewhat-popular';
					break;
				case (5001 < $plays AND $plays < 10000):
					$item->popularity = 'popular';
					break;
				case (10001 < $plays AND $plays < 50000):
					$item->popularity = 'very-popular';
					break;
				case (50000 < $plays):
					$item->popularity = 'ultra-popular';
					break;
			}
			
			$items[] = $item;
    	}
    	
    	return $items;
    }
       
}