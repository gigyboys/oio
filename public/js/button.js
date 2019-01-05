$(function() {
    var dd_target = $('.dd_target');
    
    $(document).click(function() {
        var dd_target = $('.dd_target');
        dd_target.hide();
		$(".popup_user_info").remove();
		$("view_result_wrap").hide();

        if($('.toggler_target').is(':visible')){
            $('.toggler_src').addClass('open');
        }else{
            $('.toggler_src').removeClass('open');
        }
		
    });

    $('body').on('click','.dd_src',function(e){
        var $this = $(this);
        e.stopPropagation(); 
        e.preventDefault(true);

        console.log("click dd_src");
        var dd_target = $('.dd_target');
        
        var dd_target_to = $this.closest(".dd").find(".dd_target");
        console.log(dd_target_to.css('display'))
        if(dd_target_to.css('display') == 'none'){
            dd_target.hide();
            dd_target_to.css('display','block').css('margin-left',0);
            var decalage = $(window).width() - dd_target_to.width() - dd_target_to.offset().left - 10;
            console.log("decalage : "+decalage);
            if (decalage < 0){
                dd_target_to.css('margin-left',decalage);
            }
			
			$(window).resize(function() {
				var decalage = $(window).width() - dd_target_to.width() - $this.offset().left - 10; 
				if (decalage < 0){
					dd_target_to.css('margin-left',decalage);
				}else{
					dd_target_to.css('margin-left',0);
				}
			});
        }else{
            dd_target.hide();
            dd_target_to.hide();
        }

        if($('.toggler_target').is(':visible')){
            $('.toggler_src').addClass('open');
        }else{
            $('.toggler_src').removeClass('open');
        }
    });

    $('body').on('click','.dd_target',function(e){
        e.stopPropagation(); 
        return false;      
    });

    $('body').on('click','.popup_user_info',function(e){
        e.stopPropagation(); 
        return false;      
    });

    $('body').on('click','.link',function(e){
		if($(this).attr("target") == "_blank"){
			window.open($(this).attr("href"), '_blank');
		}else{
			window.location=$(this).attr("href");
		}
    }); 
    
});