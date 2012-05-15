(function($){
	$(document).ready(function() {
		// Thumbnails
		
		var height = $('#vimeography-thumbnails a').height(); // get the height of the first thumbnail
		var timeout = 0;
		
		function nextA() {
			clearTimeout(timeout);
				
			$('#vimeography-thumbnails').css({bottom: 0}).animate({bottom: -height}, 600);
			$('#vimeography-thumbnails a:last-child').prependTo('#vimeography-thumbnails');
					 
			// Main
			
			$('#vimeography-main div.active').removeClass('active').animate({opacity: 0})
			
			var vimeo_id = $('#vimeography-thumbnails a:last').attr('data-id');
			$('#vimeography-main div[data-id="'+vimeo_id+'"]').addClass('active').animate({opacity: 1});		
			
			// call the function nextA() repeatedly every 5000 ms
			timeout = setTimeout(function() { nextA(); }, 5000);
		}
		
		// Arrow handling
		$('#vimeography-main-wrapper') 
			.mouseenter(function() {
				$('#vimeography-arrow').stop().animate({opacity: 1});
			})
			.mouseleave(function() {
				$('#vimeography-arrow').stop().animate({opacity: 0});
			});
			
		$('#vimeography-arrow')
			.css({opacity: 0})
			.bind('keydown mousedown', function(){
				$(this).addClass('btn-down');//give the impression button is pressing 
			})
			.bind('keyup blur mouseup mouseleave', function(){
				$(this).removeClass('btn-down');//give the impression button is being released
			})
			.click(function() {//When user clicks the "Arrow" button
				nextA(); // make the image 'rotate' through the list
			});	
	
		nextA(); // This is where it starts ! :)
	});
})(jQuery)