<?php

// Require Mustache.php
require_once(VIMEOGRAPHY_PATH . '/vendor/mustache/Mustache.php');

class Vimeography_Core extends Vimeography
{
	const ENDPOINT = 'http://vimeo.com/api/v2/';
	const FORMAT = '.json';
		
	protected $_source;
	protected $_named;
	protected $_type;
	
	protected $_theme;
	protected $_featured;
	
	protected $_debug = FALSE;
		
	public static function factory($class, $settings)
	{
		require_once(VIMEOGRAPHY_PATH .'lib/'. $class . '.php');
		$class_name = 'Vimeography_'. ucfirst($class);
		
		if (class_exists($class_name))
		{
			return new $class_name($settings);
		}
		else
		{
            throw new Vimeography_Exception('Class not found: '.$class_name);
		}
	}
			
	public function __construct($settings)
	{
		require_once(VIMEOGRAPHY_PATH .'lib/exception.php');
		
		$this->_theme = $settings['theme'];
		$this->_featured = $settings['featured'];
		$this->_source = $settings['from'];
		$this->_named = $settings['named'];
	}
	
	/**
	 * Overload the constructed debugger to print the data instead of render it.
	 * 
	 * @access public
	 * @param mixed $debug (default: TRUE)
	 * @return void
	 */
	public function debug($debug = TRUE)
	{
		$this->_debug = $debug;
		return $this;
	}
				
	/**
	 * Build the endpoint url based on the provided information.
	 * 
	 * @access protected
	 * @param mixed $data
	 * @return void
	 */
	protected function _build_url($source)
	{
		switch ($source)
		{
			case 'album':
				$result = 'album/'.$this->_named.'/'.$this->_type;
				break;
			case 'channel':
				$result = 'channel/'.$this->_named.'/'.$this->_type;
				break;
			case 'group':
				$result = $this->_named.'/'.$this->_type;
				break;
			case 'user':
				$result = $this->_named.'/'.$this->_type;
				break;
			case 'video':
				$result = 'video/'.$this->_named;
				break;
			default:
				if (empty($source))
				{
					throw new Vimeography_Exception('You must provide a source from where to retrieve Vimeo videos.');
				}
				else
				{
					throw new Vimeography_Exception($source.' is not a valid Vimeo source parameter.');
				}
				break;
		}
		
		return self::ENDPOINT.$result.self::FORMAT;
	}
		
	/**
	 * Retrieves the requested data from Vimeo API.
	 * 
	 * TODO: This could potentially return a 404 page, and we don't want that, nor do we want to show it in the exception.
	 * @access protected
	 * @param mixed $data
	 * @return void
	 */
	protected function _retrieve()
	{
		$urls = array();
		$urls[] = $this->_build_url($this->_source);
		
		if (!empty($this->_featured))
			$urls[] = self::ENDPOINT.'video/'.$this->_featured.self::FORMAT;
		
		$result = array();
				
		foreach ($urls as $url)
		{
			$response = wp_remote_get($url);
			
			if ($response->errors)
			{
				foreach ($response->errors as $error)
				{
					throw new Vimeography_Exception('the plugin did not retrieve data from the Vimeo API! '. $error[0]);
				}
			}
				
			if (strpos($response['body'], 'not found'))
				throw new Vimeography_Exception('the plugin could not retrieve data from the Vimeo API! '. $response['body']);
																		
			$result[] = $response['body'];
						
			if (count(json_decode($response['body'])) === 20)
			{
				// let's get some more stinkin' videos!
				$second_set = wp_remote_get($url.'?page=2');
				
				if (! $second_set)
					throw new Vimeography_Exception('Could not connect to the Vimeo API. Check your interwebs connection!');
					
				if (strpos($second_set['body'], 'not found'))
					throw new Vimeography_Exception('Error retrieving data from Vimeo API! '. $response['body']);
				
			}
		}
		return $result;
	}
		
	public function render($data)
	{
		if (! $this->_debug)
		{
			if (! isset($this->_theme))
				throw new Vimeography_Exception('You must specify a theme in either the admin panel or the shortcode.');
				
			if (!@require_once(VIMEOGRAPHY_PATH .'themes/'. $this->_theme . '/'.$this->_theme.'.php'))
				throw new Vimeography_Exception('The "'.$this->_theme.'" theme does not exist or is improperly structured.');	
							
			$class = 'Vimeography_Themes_'.ucfirst($this->_theme);
			
			if (!class_exists($class))
				throw new Vimeography_Exception('The "'.$this->_theme.'" theme class does not exist or is improperly structured.');	
						
			$mustache = new $class;
			$theme = $this->_load_theme($this->_theme);
						
			$mustache->data = json_decode($data[0]);

			if (isset($data[1]))
			{
				// featured video option is set
				$featured = json_decode($data[1]);
				
				// check if featured video is in the source array, and if so, remove it to avoid duplicates.
				$i = 0;
				foreach ($mustache->data as $video)
				{
					if ($video->id === $featured[0]->id)
						unset($mustache->data[$i]);
					$i++;
				}
			}
			else
			{
				$data = json_decode($data[0]);
				$featured = $data[0];
			}
			
			$mustache->featured = $featured;
							
			return $mustache->render($theme);
		}
		else
		{
			echo '<h1>Vimeography Debug</h1>';
			echo '<pre>';
			print_r(json_decode($data));
			echo '</pre>';
			die;
		}
	}
	
	protected function _load_theme($name)
	{
		$path = VIMEOGRAPHY_PATH . '/themes/' . $name . '/videos.mustache';
		if (! $result = @file_get_contents($path))
			throw new Vimeography_Exception('The gallery template for the "'.$name.'" theme cannot be found.');
		return $result;
	}
}