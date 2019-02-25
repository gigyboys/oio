
$(function() {
	
	//add new event
	$('body').on('click','.create_job',function(event){
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

	/*
    *upload cv job
    */
   $('body').on('change','#cvfile',function(event){
		var $this = $(this);
		var file = $this[0].files[0];
		var target = $this.data('target');
		var data = new FormData();
		data.append('file', file);

		var size = file.size;
		if(size > 1024 * 1024 * 5){
			var content = '<div style="padding:10px; width:auto; background:#fff; border-radius:3px; "><div style="text-align:center; margin-bottom: 20px">	<span>Veuillez uploader une image de taille inférieure à 5MB.</span></div><div style="text-align:center">	<span class="button_closable" style="background:#888; border-radius: 3px; cursor:pointer; display:inline-block; margin:auto; padding:5px 15px;">	OK	</span></div></div>';
			popup(content, 500, true);
		}else{
			createSpinner();
			$.ajax({
				type: 'POST',
				url: target,
				data: data,
				contentType: false,
				processData: false,
				dataType : 'json',
				success: function(data){
					if(data.state){
						$("#apply_files").html(data.applyFilesHtml);
					}else{
						alert("Une erreur est survenue");
					}
					destroySpinner();
				},
				error: function(jqXHR, textStatus, errorThrown) {
					console.log(jqXHR.status);
					console.log(textStatus);
					console.log(errorThrown);
					destroySpinner();
				},
				xhr: function() {
					var myXhr = $.ajaxSettings.xhr();
					if (myXhr.upload) {
						myXhr.upload.addEventListener('progress', function(e) {
							if (e.lengthComputable) {
								var percentage = parseInt(e.loaded / e.total * 100);
								$("#spinnerloading .progress").remove();
								$("#spinnerloading div span").after("<span class='progress'> "+percentage+"%</span>");
							}
						} , false);
					}
					return myXhr;
				}
			});
		}
	});
	

	/*
	*upload document job
	*/
	$('body').on('change','#documentfile',function(event){
		var $this = $(this);
		var file = $this[0].files[0];
		var target = $this.data('target');
		var data = new FormData();
		data.append('file', file);

		var size = file.size;
		if(size > 1024 * 1024 * 5){
			var content = '<div style="padding:10px; width:auto; background:#fff; border-radius:3px; "><div style="text-align:center; margin-bottom: 20px">	<span>Veuillez uploader une image de taille inférieure à 5MB.</span></div><div style="text-align:center">	<span class="button_closable" style="background:#888; border-radius: 3px; cursor:pointer; display:inline-block; margin:auto; padding:5px 15px;">	OK	</span></div></div>';
			popup(content, 500, true);
		}else{
			createSpinner();
			$.ajax({
				type: 'POST',
				url: target,
				data: data,
				contentType: false,
				processData: false,
				dataType : 'json',
				success: function(data){
					if(data.state){
						$("#apply_files").html(data.applyFilesHtml);
					}else{
						alert("Une erreur est survenue");
					}
					destroySpinner();
				},
				error: function(jqXHR, textStatus, errorThrown) {
					console.log(jqXHR.status);
					console.log(textStatus);
					console.log(errorThrown);
					destroySpinner();
				},
				xhr: function() {
					var myXhr = $.ajaxSettings.xhr();
					if (myXhr.upload) {
						myXhr.upload.addEventListener('progress', function(e) {
							if (e.lengthComputable) {
								var percentage = parseInt(e.loaded / e.total * 100);
								$("#spinnerloading .progress").remove();
								$("#spinnerloading div span").after("<span class='progress'> "+percentage+"%</span>");
							}
						} , false);
					}
					return myXhr;
				}
			});
		}
	});
	

	/*
	*delete document job
	*/
	$('body').on('click','.btn_delete_doc',function(event){
		var $this = $(this);
		var target = $this.data('target');
		var data = new FormData();

		createSpinner();
		$.ajax({
			type: 'POST',
			url: target,
			data: data,
			contentType: false,
			processData: false,
			dataType : 'json',
			success: function(data){
				if(data.state){
					$("#apply_files").html(data.applyFilesHtml);
				}else{
					alert("Une erreur est survenue");
				}
				destroySpinner();
			},
			error: function(jqXHR, textStatus, errorThrown) {
				console.log(jqXHR.status);
				console.log(textStatus);
				console.log(errorThrown);
				destroySpinner();
			}
		});
	});
});
