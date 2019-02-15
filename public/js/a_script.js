$(function() {
	var body = $('body');
	var a_header = $('#a_header');
	var a_nav = $('#a_nav');
	var a_nav_main = $('#a_nav_main');
	var a_nav_main_item_text = $('.a_nav_main_item_text');
	var a_nav_sub_item_text = $('.a_nav_sub_item_text');
	var a_nav_foot = $('#a_nav_foot');
	var a_content = $('#a_content');
	var a_content_header = $('#a_content_header');
	var a_content_body = $('#a_content_body');
	var a_content_footer = $('#a_content_footer');
	var a_nav_item = $('.a_nav_item');
	
	//var a_nav_width_max = 240;
	var a_nav_width_max = 200;
	var a_nav_width_min = 40;
	
	var ww_width_min= 600;
	var ww_height_min= 300;
	
	var width_seuil = 800;
	
	var ww = $(window);
	
	function init(){
		if(ww.width() > width_seuil){
			var a_nav_width = a_nav_width_max;
			a_nav_main_item_text.css('display', 'block');
			a_nav_sub_item_text.css('display', 'block');
		}
		else{
			var a_nav_width = a_nav_width_min;
			a_nav_main_item_text.css('display', 'none');
			a_nav_sub_item_text.css('display', 'none');
		}
		
		var a_nav_height = ww.height() - a_header.height();
		var a_content_height = ww.height() - a_header.height();
		a_nav.width(a_nav_width);
		a_content.css('margin-left', a_nav_width);
		
		$('#a_content_body_left').css('width',$('#a_content_body').width()-260);
		$('#a_content_body_right').css('margin-left',$('#a_content_body_left').width());
		
		if(ww.height() > ww_height_min){
			body.css('overflow', 'hidden');
			a_nav.height(a_nav_height);
			var a_nav_main_height = a_nav.height() - a_nav_foot.height();
			a_nav_main.height(a_nav_main_height);
			a_content.height(a_content_height);
			
			var a_content_body_height = a_content.height() - a_content_header.height() - a_content_footer.height();
			a_content_body.height(a_content_body_height);
		}else{
			body.css('overflow', 'auto');
		}
	}
	
	$("#a_nav_main").mCustomScrollbar({
		theme:"minimal-dark",
		animationSpeed:0
	});
	
	/*
    $(window).resize(function() {
        init();
    });

    init();
    init();
	*/
	
	$('.a_nav_sub_content').on('click', function(e) {
        e.stopPropagation(); 
    });
	
	
	a_nav_item.on('click', function(e) {
		var animateTime = 350;
		
		$('.a_nav_sub_content').stop().animate({ height: '0' }, animateTime);
		$('.a_nav_item').removeClass("open");
		
		if($(this).find('.a_nav_sub_content').height() === 0){
			autoHeightAnimate($(this).find('.a_nav_sub_content'), animateTime);
			$(this).addClass("open");
		}else{
			$(this).find('.a_nav_sub_content').stop().animate({ height: '0' }, animateTime);
		}
    });
	
	
	function autoHeightAnimate(element, time){
		var curHeight = element.height(), // Get Default Height
		autoHeight = element.css('height', 'auto').height(); // Get Auto Height
		element.height(curHeight); // Reset to Default Height
		element.stop().animate({ height: autoHeight }, time); // Animate to Auto Height
	}

	
});
