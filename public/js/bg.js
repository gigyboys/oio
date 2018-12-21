
$(function() {

	/*
	* load posts
	*/
	$('#bg_load_post').on('click', function(){
        var $this = $(this);
        var target = $this.attr("data-target");
		
		var data = {
		};
		$("#bg_load_post_action #bg_load_post").hide();
		$("#bg_load_post_action .btn_loading").css("display", "block");
		$.ajax({
			type: 'POST',
			url: target,
			data: data,
			dataType : 'json',
			success: function(data){
				console.log(data.state);
				if(data.state){
					var htmlappend = '';
					for(var i = 0; i <data.posts.length; i++ ){
						var post = data.posts[i];
						htmlappend += post.postItem;
					}
					$("#list_post").append(htmlappend);
					if(data.previousPostId > 0){
						$("#bg_load_post").attr("data-target", data.urlLoadPost);
						$("#bg_load_post").attr("data-previous-post", data.previousPostId);
						
						$("#bg_load_post_action #bg_load_post").css("display", "block");
						$("#bg_load_post_action .btn_loading").hide();
					}else{
						$("#bg_load_post_action").remove();
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
	
	/*
	* load commentItem
	*/
	$('#load_comment').on('click', function(){
        var $this = $(this);
        var target = $this.attr("data-target");
		
		var data = {
		};
		$("#bg_load_comment_action #load_comment").hide();
		$("#bg_load_comment_action .btn_loading").css("display", "block");
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
					$("#bg_post_list_cmt").prepend(htmlprepend);
					if(data.previousCommentId > 0){
						$("#load_comment").attr("data-target", data.urlLoadComment);
						$("#load_comment").attr("data-previous-cmt", data.previousCommentId);
						
						$("#bg_load_comment_action #load_comment").css("display", "block");
						$("#bg_load_comment_action .btn_loading").hide();
					}else{
						$("#bg_load_comment_action").remove();
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
	
	$('.btn_bg_post_new_cmt').on('click', function(){
        var $this = $(this);
        var target = $this.data('target');
		console.log(target);
		if($.trim($("#bg_post_cmt_message").val()) != ""){
			var data = {
				comment : $("#bg_post_cmt_message").val()
			};
			$("#bg_add_comment_error").html("");
			$("#bg_add_comment_action .btn_save").hide();
			$("#bg_add_comment_action .btn_loading").css("display", "inline-block");
            $("#bg_post_cmt_message").prop('disabled', true);
			$.ajax({
				type: 'POST',
				url: target,
				data: data,
				dataType : 'json',
				success: function(data){
					console.log(data.state);
					if(data.state == 1){
						$("#bg_post_list_cmt").append(data.commentItem);
						$("#bg_post_cmt_message").val("");
						$("#info_comment").html(data.infoComment);
					}
					else if(data.state == 3){
                        processLogin();
					}
                    $("#bg_add_comment_action .btn_save").css("display", "inline-block");
                    $("#bg_add_comment_action .btn_loading").hide();
                    $("#bg_post_cmt_message").prop('disabled', false);
				},
				error: function(jqXHR, textStatus, errorThrown) {
					console.log(jqXHR.status);
					console.log(textStatus);
					console.log(errorThrown);
				}
			});		
		}else{
			var errorHtml = '<div class="error_txt">Veuillez bien remplir le champ</div>';
			$("#bg_add_comment_error").html(errorHtml);
		}	
    });

	/*
	*load calendar
	*/
	$('body').on('click','.bg_month_btn',function(e){
		var $this = $(this);
        var target = $this.data('target');
		var data = {
		};
		
		
		var bg_calendar_height = $("#bg_calendar_ct").height();
		var bg_calendar_width = $("#bg_calendar_ct").width();
		$("#bg_load_calendar").css("height",bg_calendar_height);
		$("#bg_load_calendar").css("width",bg_calendar_width);
		$("#bg_load_calendar").css("display","block");
		$("#bg_load_calendar").css("padding-top",(bg_calendar_height/2) );
		
        $.ajax({
            type: 'POST',
            url: target,
            data: data,
            dataType : 'json',
            success: function(data){
                console.log(data.state);
				if(data.state){
					$("#bg_load_calendar").css("display","none");
					$("#bg_calendar_ct").html(data.calendar);
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

	
	//add new post
	$('body').on('click','.create_post',function(event){
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
				}else{
					
				}
			},
			error: function(jqXHR, textStatus, errorThrown) {
			}
		});
    });
	
});