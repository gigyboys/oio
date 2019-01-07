
$(function() {
	
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
	
	//going_btn, maybe_btn
	$('body').on('click','.going_btn, .maybe_btn',function(){
        var $this = $(this);
        var target = $this.data('target');
        $('.actions .btn_list').hide();
        $('.actions .loading').show();
        $.ajax({
            type: 'POST',
            url: target,
            dataType : 'json',
            success: function(data){
				if(data.state == 1){
					$(".participation_block").html(data.participationHtml);
				}else if(data.state == 3){
                    processLogin();
				}
				$('.actions .btn_list').show();
				$('.actions .loading').hide();
            },
            error: function(jqXHR, textStatus, errorThrown) {
				console.log(jqXHR.status);
				console.log(textStatus);
				console.log(errorThrown);
			}
        });		
    });
	
	$('.btn_event_new_cmt').on('click', function(){
        var $this = $(this);
        var target = $this.data('target');
		console.log(target);
		if($.trim($("#event_cmt_message").val()) != ""){
			var data = {
				comment : $("#event_cmt_message").val()
			};
			$("#et_add_comment_error").html("");
			$("#et_add_comment_action .btn_save").hide();
			$("#et_add_comment_action .btn_loading").css("display", "inline-block");
            $("#event_cmt_message").prop('disabled', true);
			$.ajax({
				type: 'POST',
				url: target,
				data: data,
				dataType : 'json',
				success: function(data){
					console.log(data.state);
					if(data.state == 1){
						$("#event_list_cmt").append(data.commentItem);
						$("#event_cmt_message").val("");
						$("#info_comment").html(data.infoComment);
					}
					else if(data.state == 3){
                        processLogin();
					}
                    $("#et_add_comment_action .btn_save").css("display", "inline-block");
                    $("#et_add_comment_action .btn_loading").hide();
                    $("#event_cmt_message").prop('disabled', false);
				},
				error: function(jqXHR, textStatus, errorThrown) {
					console.log(jqXHR.status);
					console.log(textStatus);
					console.log(errorThrown);
				}
			});		
		}else{
			var errorHtml = '<div class="error_txt">Veuillez bien remplir le champ</div>';
			$("#et_add_comment_error").html(errorHtml);
		}	
    });
	
	/*
	* load commentItem evet
	*/
	$('#event_load_comment').on('click', function(){
        var $this = $(this);
        var target = $this.attr("data-target");
		
		var data = {
		};
		$("#event_load_comment_action #event_load_comment").hide();
		$("#event_load_comment_action .btn_loading").css("display", "block");
		$.ajax({
			type: 'POST',
			url: target,
			data: data,
			dataType : 'json',
			success: function(data){
				console.log(data.state);
				if(data.state){
					var htmlprepend = '';
					for(var i = 0; i <data.comments.length; i++ ){
						var comment = data.comments[i];
						htmlprepend += comment.commentItem;
					}
					$("#event_list_cmt").prepend(htmlprepend);
					if(data.previousCommentId > 0){
						$("#event_load_comment").attr("data-target", data.urlLoadComment);
						$("#event_load_comment").attr("data-previous-cmt", data.previousCommentId);
						
						$("#event_load_comment_action #event_load_comment").css("display", "block");
						$("#event_load_comment_action .btn_loading").hide();
					}else{
						$("#event_load_comment_action").remove();
					}
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
