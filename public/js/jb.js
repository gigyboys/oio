
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
	
	
});
