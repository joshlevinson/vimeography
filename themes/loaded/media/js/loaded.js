(function($){
	$(document).ready(function(){
	
        // Listen for the ready event for any vimeo video players on the page
        /*var player = document.querySelectorAll('iframe')[0];
        console.log(player);
        
        $f(player).addEvent('ready', ready);*/

        /**
		* Utility function for adding an event. Handles the inconsistencies
		* between the W3C method for adding events (addEventListener) and
		* IE's (attachEvent).
		*/
        function addEvent(element, eventName, callback) {
            if (element.addEventListener) {
                element.addEventListener(eventName, callback, false);
            }
            else {
                element.attachEvent(eventName, callback, false);
            }
        }
                
		function ready(player_id) {
			//alert('ready');
			$('#vimeography-player').animate({'opacity':1}, 600);
		}
		
		//$('.vimeography-info p').expander({expandPrefix: ' ', expandText: '[+show more]', userCollapseText: '[-]'});
		
		$('.vimeography-thumbnail img').mouseover(function(){
			$(this).closest('.vimeography-thumbnail-crop').stop().animate({backgroundColor: '#50534f'}, 300);
		}).mouseout(function(){
			$(this).closest('.vimeography-thumbnail-crop').stop().animate({backgroundColor: '#ff5c23'}, 300);
		});
		       	
		$('.vimeography-thumbnail-crop a').click(function(e){
			//cleanup
			$('.vimeography-data p, .vimeography-data iframe').fadeOut();
			
			var data_div = $(this).parent().next().next();
			$(data_div).find('p').fadeIn();
			
    		var vid_id = $(this).attr('data-id');
    		
    		var video = $('<iframe />', {
    			src: 'http://player.vimeo.com/video/'+vid_id+'?title=0&byline=0&portrait=0&autoplay=1&api=1',
    			width: 615,
    			height: 346,
    			frameborder: 0,
    			allowFullScreen: true
    		});
    		
    		$(data_div).append(video);
    		/*$('#vimeography-player').animate({'opacity':0});
    		var vid_id = $(this).attr('href').substr(1);
    		    		
    		$('#vimeography-info h1').html($('#vimeography-data h1[data-id="'+vid_id+'"]').html());
    		
    		var description = $('<p />').html($('#vimeography-data p[data-id="'+vid_id+'"]').html()).expander({expandPrefix: ' ', expandText: '[+show more]', userCollapseText: '[-]'});
    		
    		$('#vimeography-info p').replaceWith(description);
			
    		$('#vimeography-player iframe').attr('src', 'http://player.vimeo.com/video/'+vid_id+'?title=0&byline=0&portrait=0&autoplay=0&api=1&player_id=vimeography-embed');*/
    		e.preventDefault();
		});
	});
})(jQuery);