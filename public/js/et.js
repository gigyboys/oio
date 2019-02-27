
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
					initListParticipants();
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

	//popup participations
	$('body').on('click','.participations_popup',function(event){
		var target = $(this).data("target");
		
		var content = "<div style='text-align:center;padding:10px; color:#fff'>Chargement ...</div>";
		popup(content, 470, true);
		$.ajax({
			type: 'POST',
			url: target,
			dataType : 'json',
			success: function(data){
				if(data.state){
					content = data.participationsPopupHtml;
					$(".popup").html(content);
					centerBloc($('.popup_content'), $('.popup'));
				}else{
					
				}
			},
			error: function(jqXHR, textStatus, errorThrown) {
			}
		});
	});

	initListParticipants();
	$(window).resize(function() {
        initListParticipants();
	});

	if ( typeof events !== 'undefined' ) {
		buildEventLinks();
		showEvent();
		nextEvent();
	}

	if($("#gallery").length > 0){
		if($("#gallery img").length >= 5){
			$("#gallery").unitegallery({
				tiles_type:"justified"
			});
		}else {
			$("#gallery").unitegallery({
				
			});
		}
	}

	$('body').on('click','.et_link',function(event){
		indexEvent = $(this).attr("data-index");
		showEvent();
	});
});

var indexEvent = 0;
function nextEvent(){
	setTimeout(function(){
		showEvent();
		nextEvent();
	}, 7500);
}

function buildEventLinks(){
	var links = "";
	for (var i = 0; i < events.length; i++) {
		event = events[i];
		links += "<span class='et_link' id='et_link_"+event.id+"' data-index='"+i+"'></span>";
	}
	$("#search_header .et_pn").html("<div class='et_link_wrap'>"+links+"</div>");
}

function showEvent(){
	$("#search_header .et_detail *").css("color", "transparent");
	var event = events[indexEvent];
	var jours = 0;
	var hours = 0;
	var minutes = 0;
	var secondes = 0;
	var diff = event.diff;
	var current = true;
	if(diff < 0){
		current = false;
		diff = diff * -1
	}
	if(diff >= (60 * 60 * 24)){
		jours = parseInt(diff / (60 * 60 * 24));
		diff = diff - jours * (60 * 60 * 24);
	}
	if(diff >= (60 * 60)){
		hours = parseInt(diff / (60 * 60));
		diff = diff - hours * (60 * 60);
	}
	if(diff >= (60)){
		minutes = parseInt(diff / (60));
		secondes = diff - minutes * (60);
	}
	var labelTime = "";
	if(current){
		labelTime += "En cours depuis ";
	}else{
		labelTime += "Bientôt dans ";
	}
	if(jours){
		if(jours > 1){
			labelTime += jours+" jours " ;
		}else{
			labelTime += jours+" jour " ;
		}
	}
	if(hours){
		if(hours > 1){
			labelTime += hours+" heures " ;
		}else{
			labelTime += hours+" heure " ;
		}
	}
	if(minutes){
		if(minutes > 1){
			labelTime += minutes+" minutes" ;
		}else{
			labelTime += minutes+" minute" ;
		}
	}
	
	//link
	$(".et_link").removeClass("selected");
	$("#et_link_"+event.id).addClass("selected");

	setTimeout(function(){
		$("#search_header .et_detail").html("<div class='event_info'><div><strong><a href='"+event.url+"'>"+event.title+"</a></strong></div><div>"+labelTime+"</div></div>");
		$("#search_header .et_detail *").css("color", "#000");
		indexEvent++;
		if(events.length <= indexEvent){
			indexEvent = 0;
		}
	}, 700);
}

function initListParticipants() {
	$(".list_wrap").removeClass("d_n").addClass("d_ib");
	if($(".list_wrap").length == 1){
		$(".list_wrap .user_hover_info").css("display", "inline-block");
		var count = 0;
		while ($(".list").width() < $(".list_wrap").width()) {
			$(".list_wrap .user_hover_info:visible").last().hide();
			var countHide = $(".list_wrap .user_hover_info:hidden").length;
			var count = parseInt($(".bound_participations").attr("data-bound-count"))+countHide;
			$(".bound_participations").html("+"+count);
			$(".bound_participations").attr("data-count", count);
			if(count <= 0){
				$(".bound_participations").hide();
			}else{
				$(".bound_participations").css("display", "inline-block");
			}
		}
		if(count <= 0){
			$(".bound_participations").hide();
		}else{
			$(".bound_participations").css("display", "inline-block");
		}
	}
}
