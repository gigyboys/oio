
$(function() {
    $('#btn_save_accessibility').on('click', function(){
        var $this = $(this);
		var bloc_editable = $this.closest(".bloc_editable");
        var target = $this.data('target');
		console.log(target);
		var data = {
			categoriesIndex : bloc_editable.find("#sg_input_catindex").val(),
			schoolsByPage : bloc_editable.find("#sg_input_slbypage").val(),
			postsByPage : bloc_editable.find("#sg_input_postsbypage").val(),
			eventsByPage : bloc_editable.find("#sg_input_eventsbypage").val(),
		};
		loadBlocEdit(bloc_editable);
        $.ajax({
            type: 'POST',
            url: target,
            data: data,
            dataType : 'json',
            success: function(data){
                console.log(data.state);
				if(data.state){
					bloc_editable.find("#sg_view_catindex").text(data.categoriesIndex);
					bloc_editable.find("#sg_view_slbypage").text(data.schoolsByPage);
					bloc_editable.find("#sg_view_postsbypage").text(data.postsByPage);
					bloc_editable.find("#sg_view_eventsbypage").text(data.eventsByPage);
					bloc_editable.find("#sg_input_catindex").val(data.categoriesIndex);
					bloc_editable.find("#sg_input_slbypage").val(data.schoolsByPage);
					bloc_editable.find("#sg_input_postsbypage").val(data.postsByPage);
					bloc_editable.find("#sg_input_eventsbypage").val(data.eventsByPage);
					resetBlocEdit(bloc_editable);
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