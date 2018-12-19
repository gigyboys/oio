
$(function() {
	

    /*
     * position des userTeams
     */
	if($(".list_ur_team_position").length == 1){
        $(".list_ur_team_position").dragsort({
            dragSelector: ".ur_team_item",
            dragEnd: function() {
            	var order = '';
            	var count = 0
                $(".list_ur_team_position .ur_team_item").each(function( index ) {
                	count++;
                	if(count != 1){
                        order += '-';
                    }
                    order += $(this).attr('data-id');
                });
                var target = $(".list_ur_team_position").attr('data-target');
            	 console.log(order + target);

                var data = {
                    'order' : order,
                };

                $.ajax({
                    type: 'GET',
                    url: target,
                    data: data,
                    dataType : 'json',
                    success: function(data){
                        console.log(data.state);
                        if(data.state){

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
			},
            dragBetween: false,
            placeHolderTemplate: '<div class="ur_team_item"><div class="avatar"></div><div class="name"></div></div>'
        });
    }
	
	/*
	* save userTeam edit
	*/
    $('#btn_save_user_team').on('click', function(){
        var $this = $(this);
		var bloc_editable = $this.closest(".bloc_editable");
        var target = $this.data('target');
		console.log(target);
		var data = {
			role : bloc_editable.find("#urt_input_role").val(),
			description : CKEDITOR.instances['urt_input_description'].getData()
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
					bloc_editable.find("#urt_view_role").text(data.role);
					bloc_editable.find("#urt_view_description").html(data.description);
					resetBlocEdit(bloc_editable);
				}
				else{
					alert("une erreur est survenue");
                    resetBlocEdit(bloc_editable);
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