<?php

class Vimeography_Gallery_List extends Mustache 
{
	public $galleries;
	public $pagination;
    
	public function __construct()
	{
		wp_register_script( 'bootstrap_tooltip_js', VIMEOGRAPHY_URL.'media/js/bootstrap-tooltip.js');
		wp_enqueue_script( 'bootstrap_tooltip_js');
		$this->galleries = $this->_get_galleries_to_display();	
	}
	
	public function new_gallery_url()
	{
		return get_admin_url().'admin.php?page=vimeography-new-gallery';
	}
	
	public function admin_url()
	{
		return get_admin_url().'admin.php?page=vimeography-';
	}
		
	public function galleries_to_show()
	{
		return (empty($this->galleries)) ? FALSE : TRUE;
	}
	
	public function galleries()
	{
		$galleries = array();
		
		foreach ($this->galleries as $gallery)
		{
			$gallery->edit_url = get_admin_url().'admin.php?page=vimeography-edit-galleries&id='.$gallery->id;
			
			switch($gallery->source_type)
			{
				case 'user':
					$url = 'http://vimeo.com/';
					break;
				case 'group':
					$url = 'http://vimeo.com/groups/';
					break;
				case 'album':
					$url = 'http://vimeo.com/album/';
					break;
				case 'channel':
					$url = 'http://vimeo.com/channels/';
					break;
				default:
					break;
			}
			$gallery->source_url = $url.$gallery->source_name;
						
			$galleries[] = $gallery;
		}
		
		return $galleries;
	}
		
	protected function _get_galleries_to_display()
	{
		global $wpdb;
		$number_of_galleries = $wpdb->get_results('SELECT COUNT(*) as count from '. VIMEOGRAPHY_GALLERY_TABLE);
		$limit = 10;
				
		$number_of_pages = ceil($number_of_galleries[0]->count / $limit);
				 
		$current_page = isset($_GET['p']) ? $wpdb->escape(intval($_GET['p'])) : 1;
				
		$offset = ($current_page - 1) * $limit;
				
		$this->pagination = $this->_do_pagination($current_page, $number_of_pages);

		return $wpdb->get_results('SELECT * from '.VIMEOGRAPHY_GALLERY_META_TABLE.' AS meta JOIN '.VIMEOGRAPHY_GALLERY_TABLE.' AS gallery ON meta.gallery_id = gallery.id LIMIT '.$limit.' OFFSET '.$offset.';');
	}
	
	/**
	 * This just creates a huge list of numbered pages at the bottom…. not pretty if someone creates 100 galleries, but not sure who might actually do that.
	 * 
	 * @access protected
	 * @param mixed $current_page
	 * @param mixed $number_of_pages
	 * @return void
	 */
	protected function _do_pagination($current_page, $number_of_pages)
	{
		if ($number_of_pages <= 1) return FALSE;
				
		$pagination = array();
		
		$pagination['previous-page'] = $current_page - 1 > 0 ? $current_page - 1 : FALSE;
		$pagination['next-page'] = $current_page == $number_of_pages ? FALSE : $current_page + 1;
		
		for ($i = 1; $i <= $number_of_pages; $i++)
		{
			$page = array();
			$page['number'] = $i;
			if ($i == $current_page) $page['active'] = TRUE;
			$pagination['pages'][] = $page;
		}
						
		return $pagination;
		
	}

}