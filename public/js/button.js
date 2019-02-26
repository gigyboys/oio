$(function() {
    $('body').on('click', function(){
        $('.ddt').hide();
		$(".popup_user_info").remove();
		$("view_result_wrap").hide();

        togglerNavStatus();
    });

    $('body').on('click','.dds',function(e){
        var $this = $(this);
        e.stopPropagation(); 
        e.preventDefault(true);

        var ddt = $('.ddt');
        var ddt_to = $this.closest(".dd").find(".ddt");
        if(ddt_to.css('display') == 'none'){
            ddt.hide();
            ddt_to.css('display','block').css('margin-left',0);
            var decalage = $(window).width() - ddt_to.width() - ddt_to.offset().left - 10;
            if (decalage < 0){
                ddt_to.css('margin-left',decalage);
            }
			
			$(window).resize(function() {
				var decalage = $(window).width() - ddt_to.width() - $this.offset().left - 10; 
				if (decalage < 0){
					ddt_to.css('margin-left',decalage);
				}else{
					ddt_to.css('margin-left',0);
				}
			});
        }else{
            ddt.hide();
            ddt_to.hide();
        }

        togglerNavStatus();
    });

    $('body').on('click','.ddt',function(e){
        e.stopPropagation(); 
        return false;      
    });

    $('body').on('click','.popup_user_info',function(e){
        e.stopPropagation(); 
        return false;      
    });

    $('body').on('click','.ddt a',function(e){
		if(!$(this).hasClass("et_tag")){
            if($(this).attr("target") == "_blank"){
                window.open($(this).attr("href"), '_blank');
            }else{
                window.location=$(this).attr("href");
            }
		}
    }); 
    
    function togglerNavStatus(){
        if($('.toggler_target').is(':visible')){
            $('.toggler_src').addClass('open');
        }else{
            $('.toggler_src').removeClass('open');
        }
    }
});