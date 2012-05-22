(function($){
	$(document).ready(function(){
	
        // Listen for the ready event for any vimeo video players on the page
        var player = document.querySelectorAll('iframe')[0];
        console.log(player);
        
        $f(player).addEvent('ready', ready);

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
			$('.vimeography-thumbs-player').animate({'opacity':1}, 600);
		}
		
		$('.vimeography-thumbs-info p').expander({expandPrefix: ' ', expandText: '[+show more]', userCollapseText: '[-]'});
		       	
		$('.vimeography-thumbs-thumbnail').click(function(e){
    		$('.vimeography-thumbs-player').animate({'opacity':0});
    		var vid_id = $(this).attr('href').substr(1);
    		    		
    		$('.vimeography-thumbs-info h1').html($('.vimeography-thumbs-data h1[data-id="'+vid_id+'"]').html());
    		
    		var description = $('<p />').html($('.vimeography-thumbs-data p[data-id="'+vid_id+'"]').html()).expander({expandPrefix: ' ', expandText: '[+show more]', userCollapseText: '[-]'});
    		
    		$('.vimeography-thumbs-info p').replaceWith(description);
			
    		$('.vimeography-thumbs-player iframe').attr('src', 'http://player.vimeo.com/video/'+vid_id+'?title=0&byline=0&portrait=0&autoplay=0&api=1&player_id=vimeography-thumbs-embed');
    		e.preventDefault();
		});
	});
})(jQuery);