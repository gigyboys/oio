
$(function() {
	$('#btn_new_subject').on('click', function(){
        var $this = $(this);
		var bloc_editable = $this.closest(".bloc_editable");
        var target = $this.data('target');
		if($.trim($(".fm_subject_title").val()) != "" && $.trim($(".fm_subject_message").val())){
			var data = {
				title : bloc_editable.find(".fm_subject_title").val(),
				message : bloc_editable.find(".fm_subject_message").val(),
			};
			$("#fm_add_subject_error").html("");
			loadBlocEdit(bloc_editable);
			$.ajax({
				type: 'POST',
				url: target,
				data: data,
				dataType : 'json',
				success: function(data){
					if(data.state){
						window.location = data.urlSubject;
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
			$("#fm_add_subject_error").html(errorHtml);
		}	
    });
	
	$('#btn_fm_new_msg').on('click', function(){
        var $this = $(this);
        var target = $this.data('target');
		if($.trim($("#fm_message").val()) != ""){
			var data = {
				message : $("#fm_message").val()
			};
			$("#fm_add_message_error").html("");
			$("#fm_add_message_action .btn_save").hide();
			$("#fm_add_message_action .btn_loading").css("display", "inline-block");
			$.ajax({
				type: 'POST',
				url: target,
				data: data,
				dataType : 'json',
				success: function(data){
					if(data.state){
						$("#fm_list_msg").append(data.messageItem);
						$("#fm_message").val("");
						$("#fm_add_message_action .btn_save").css("display", "inline-block");
						$("#fm_add_message_action .btn_loading").hide();
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
			$("#fm_add_message_error").html(errorHtml);
		}
    });
});