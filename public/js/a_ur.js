
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
	
	//delete user team
	$('body').on('click','.delete_user_team',function(e){
		var id = $(this).data("id");
		var name = $(this).data("name");
		var target = $(this).data("target");
		
		var content = "";
		content += '<div style="padding:10px; width:auto; background:#fff; border-radius:3px">';
			content += '<div style="text-align:center;padding:10px 0"> Voulez vous retirer "<strong>'+name+'</strong>" de l\'équipe ?</div>';
			content += '<div style="text-align:center">	';
				content += '<span class="button_closable" style="background:#bbb; border-radius: 3px; cursor:pointer; display:inline-block; margin:auto; padding:5px;margin:5px">	Annuler	</span>';
				content += '<span class="confirm_delete_user_team button_closable" data-entity="userTeam" data-id="'+id+'" data-target="'+target+'" style="background:#bbb; border-radius: 3px; cursor:pointer; display:inline-block; margin:auto; padding:5px;margin:5px">	Confirmer	</span>	';
			content += '</div>';
		content += '</div>';
        
		popup(content, 500, true);
    });

    //confirm delete field
    $('body').on('click','.confirm_delete_user_team',function(e){
        var $this = $(this);
        var id = $this.data("id");
        var target = $this.data('target');
        var data = {
            id : id
        };

        createSpinner();
        $.ajax({
            type: 'POST',
            url: target,
            data: data,
            dataType : 'json',
            success: function(data){
                console.log(data.state);
                if(data.state){
                    var entity = $this.attr('data-entity');
                    switch (entity) {
                        case 'userTeam':
                            console.log("suppression dans dom");
                            $( "#user_team_"+data.id ).remove();
                            $(".nb_userteams").html(data.userTeams.length);
                            $(".nb_published_userteams").html(data.publishedUserTeams.length);
                            $(".nb_not_published_userteams").html(data.notPublishedUserTeams.length);
                            break;
                    }
                    $( ".a_list_search_input" ).trigger( "keyup" );
                }else{
                    alert("une erreur est survenue");
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