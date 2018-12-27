
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
					var target = $('.et_type').first();
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
	
	//add new event
	$('body').on('click','.create_event',function(event){
		var target = $(this).data("target");
		
		var content = "<div style='text-align:center;padding:10px; color:#fff'>Chargement ...</div>";
		popup(content, 500, true);
		$.ajax({
			type: 'POST',
			url: target,
			dataType : 'json',
			success: function(data){
				if(data.state){
					content = data.content;
					$(".popup").html(content);
					centerBloc($('.popup_content'), $('.popup'));
					
					if($("#datebegin_event").length > 0 && $("#dateend_event").length > 0){
						var startDate = $('#datebegin_event');
						var endDate = $('#dateend_event');

						$.timepicker.datetimeRange(
							startDate,
							endDate,
							{
								minInterval: (1000*60*60), // 1hr
								dateFormat: 'dd/mm/yy',
								timeFormat: 'HH:mm',
								start: {}, // start picker options
								end: {} // end picker options
							}
						);
					}
				}else{
					
				}
			},
			error: function(jqXHR, textStatus, errorThrown) {
			}
		});
    });
	
});
