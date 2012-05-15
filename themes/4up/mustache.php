<?php

class Themes_4up_Mustache extends Mustache 
{
    public $data;
    public $featured;
            
    public function styles()
    {
    	//$path = dirname(__FILE__);
    	
    	return array(
    		array('path' => '/themes/4up/media/css/4up.css'),
    		array('path' => '/themes/4up/media/css/plugins/showcase/showcase.css'),
    	);
    	
    }
    
    public function scripts()
    {
    	return array(
    		array('path' => 'https://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js'),
    		array('path' => '/themes/4up/media/js/plugins/jquery.aw-showcase.min.js'),
    	);
    }
    
    public function info()
    {
    	$items = array();
    	$helpers = new Vimeography_Helpers;
    	    	
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