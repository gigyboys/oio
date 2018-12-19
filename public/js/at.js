
$(function() {
	/*
	* load commentItem
	*/
	$('#at_load_comment').on('click', function(){
        var $this = $(this);
        var target = $this.attr("data-target");
		
		var data = {
		};
		$("#at_load_comment_action #at_load_comment").hide();
		$("#at_load_comment_action .btn_loading").css("display", "block");
		$.ajax({
			type: 'POST',
			url: target,
			data: data,
			dataType : 'json',
			success: function(data){
				console.log(data.state);
				if(data.state){
					var htmlappend = '';
					for(var i = 0; i <data.comments.length; i++ ){
						var comment = data.comments[i];
						htmlappend += comment.commentItem;
					}
					$("#at_list_cmt").append(htmlappend);
					if(data.previousCommentId > 0){
						$("#at_load_comment").attr("data-target", data.urlLoadComment);
						$("#at_load_comment").attr("data-previous-cmt", data.previousCommentId);
						
						$("#at_load_comment_action #at_load_comment").css("display", "block");
						$("#at_load_comment_action .btn_loading").hide();
					}else{
						$("#at_load_comment_action").remove();
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
	
	$('body').on('click','.btn_at_new_cmt',function(e){
        var $this = $(this);
        var target = $this.data('target');
		if($.trim($("#at_cmt_message").val()) != ""){
			var data = {
				message : $("#at_cmt_message").val()
			};
			$("#at_add_comment_error").html("");
			$("#at_add_comment_action .btn_save").hide();
			$("#at_add_comment_action .btn_loading").css("display", "inline-block");
			$.ajax({
				type: 'POST',
				url: target,
				data: data,
				dataType : 'json',
				success: function(data){
					console.log(data.state);
					if(data.state){
						$("#at_list_cmt").prepend(data.commentItem);
						$("#at_cmt_message").val("");
						$("#info_comment").html(data.infoComment);
						$("#at_add_comment_action .btn_save").css("display", "inline-block");
						$("#at_add_comment_action .btn_loading").hide();
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
		}else{
			var errorHtml = '<div style="color:#d22">Veuillez bien remplir le champ</div>';
			$("#at_add_comment_error").html(errorHtml);
		}	
    });
	
	$('body').on('click','.not_school_advert',function(e){
		var $this = $(this);
        var target = $this.data('target');
		var data = {
		};
        $.ajax({
            type: 'POST',
            url: target,
            data: data,
            dataType : 'json',
            success: function(data){
                console.log(data.state);
				if(data.state){
					$this.remove();
					if($("#btn_add_to_sl .not_school_advert").length == 0){
						$("#btn_add_to_sl").remove();
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