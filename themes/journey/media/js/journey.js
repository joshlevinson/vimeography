(function($){
    $(document).ready(function(){
    	$('#vimeography-main').awShowcase({
    		content_width: 671,
    		content_height: 377,
    		interval: 4000,
    		transition: 'fade',
    		continuous: true,
    		auto: false,
    		buttons: false,
    		arrows: false,
    		thumbnails: true,
    		thumbnails_direction: 'horizontal',
    		thumbnails_slidex: 1
    	});
    	
    	$('.showcase-thumbnail').click(function(e){
    		var id = $(this).attr('data-id');
    		var title = $(this).attr('data-title');
    		var duration = $(this).attr('data-duration');
    		var plays = $(this).attr('data-plays');
    		var description = $('p.vimeography-description[data-id="'+id+'"]').html();
    		
    		if (description.length > 500) {description = description.substring(0, 500) + '...';}
    		
    		$('#vimeography-title').html(title);
    		$('#vimeography-duration').html(duration);
    		$('#vimeography-description').html(description);
    		$('#vimeography-play-count').html(plays);
    		
    	});
    	
    	/* Populate the sidebar with the first video's data */
    	
    	var firstvideo = $('.showcase-thumbnail').eq(0);
    	
    	$('#vimeography-title').html($(firstvideo).attr('data-title'));
    	$('#vimeography-duration').html($(firstvideo).attr('data-duration'));
    	$('#vimeography-description').html($('p.vimeography-description').eq(0).html());
    	$('#vimeography-play-count').html($(firstvideo).attr('data-plays'));
    	
    	/* Use the playbar to control the video insertion */
    	$('.vimeography-playbar').live('click', function(e){
    		var vid_id = $(this).attr('data-id');
    		var video = $('<iframe />', {
    			src: 'http://player.vimeo.com/video/'+vid_id+'?title=0&byline=0&portrait=0&autoplay=1&api=1',
    			width: 671,
    			height: 377,
    			frameborder: 0,
    			allowFullScreen: true
    		});
    		
    		$(this).parent().html(video);
    	});
    	
    	$('.vimeography-playbar').live('mouseenter', function(){
    		console.info($(this).find('.vimeography-playbar-background'));
    		$($(this).find('.vimeography-playbar-background')).stop().animate({ opacity: ".2" }, 250);
    	}).live('mouseleave', function(){
    		$($(this).find('.vimeography-playbar-background')).stop().animate({ opacity: ".0" }, 250);
    	});
    					
    });
})(jQuery);