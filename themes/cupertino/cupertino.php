<?php

class Vimeography_Themes_Cupertino extends Mustache
{
    public $data;
    public $featured;
    
	public function __construct()
	{
		wp_register_script('jquery-tools', 'http://cdn.jquerytools.org/1.2.7/tiny/jquery.tools.min.js', array('jquery'));
		wp_register_script('cupertino.js', plugins_url('media/js/cupertino.js', __FILE__ ), array('jquery'));
		wp_register_style('reveal.css', plugins_url('media/css/plugins/reveal/reveal.css', __FILE__ ));
		wp_register_style('cupertino.css', plugins_url('media/css/cupertino.css', __FILE__ ));
		
		wp_enqueue_script('jquery');
		wp_enqueue_script('jquery-tools');
		wp_enqueue_script('cupertino.js');		
		wp_enqueue_style('reveal.css');
		wp_enqueue_style('cupertino.css');
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