
$(function() {
	
	
	/*
	* Navigation pagination ajax
	*/
	$('body').on('click','a.et_pagination_item, .et_type_item a',function(e){
        e.preventDefault(true);
		var $this = $(this);
		//alert($this.attr("href"));
		var target = $this.attr("href");
		var wrap_height = $("#et_list_wrap").height();
		var wrap_width = $("#et_list_wrap").width();
		$("#et_load_list").css("height",wrap_height);
		$("#et_load_list").css("width",wrap_width);
		$("#et_load_list").css("display","block");
		$("#et_load_list").css("padding-top",(wrap_height/2) - 40);
		
        $.ajax({
            type: 'POST',
            url: target,
            dataType : 'json',
            success: function(data){
				if(data.state){
					$("#et_load_list").css("display","none");
					var htmlappend = '';
					for(var i = 0; i <data.events.length; i++ ){
						var event = data.events[i];
						htmlappend += event.event_view;
					}
					htmlappend += '<div class="both"></div>';
					$("#et_list").html(htmlappend);
					$(".et_pagination").html(data.pagination);
					$(".et_type").html(data.typeLinks);
					history.pushState('', 'Evènements - page '+data.page, data.currentUrl);
					var target = $('.et_pagination').first();
					$('html, body').stop().animate({scrollTop: - 50 + target.offset().top}, 500);
				}
				else{
					alert("une erreur est survenue");
				}
            },
            error: function(jqXHR, textStatus, errorThrown) {
				console.log(jqXHR.status);
				console.log(textStatus);
				console.log(errorThrown);
			}
        });
    });
	
});
