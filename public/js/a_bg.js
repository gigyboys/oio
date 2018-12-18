
$(function() {
	
	/*
	 *modification category bg common
	 */
    $('#btn_save_bgcat_common').on('click', function(){
        var $this = $(this);
		var bloc_editable = $this.closest(".bloc_editable");
        var target = $this.data('target');
		console.log(target);
		var data = {
			defaultName : bloc_editable.find("#bgcat_input_defaultname").val(),
			slug : bloc_editable.find("#bgcat_input_slug").val()
		};
		loadBlocEdit(bloc_editable);
        $.ajax({
            type: 'POST',
            url: target,
            data: data,
            dataType : 'json',
            success: function(data){
				if(data.state){
					bloc_editable.find("#bgcat_view_defaultname").text(data.defaultName);
					bloc_editable.find("#bgcat_view_slug").text(data.slug);
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
	
	
	/*
	 *modification category bg translate
	 */
    $('.btn_save_bgcat_trans').on('click', function(){
        var $this = $(this);
		var bloc_editable = $this.closest(".bloc_editable");
        var target = $this.data('target');
		console.log(target);
		var data = {
			name : bloc_editable.find(".bgcatt_input_name").val(), 
			description : bloc_editable.find(".bgcatt_input_description").val()
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
					bloc_editable.find(".bgcatt_view_name").html(data.name);
					bloc_editable.find(".bgcatt_view_description").html(data.description);
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

    /*
     *modification tag blog
     */
    $('#btn_doedit_tag').on('click', function(){
        var $this = $(this);
        var bloc_editable = $this.closest(".bloc_editable");
        var target = $this.data('target');
        console.log(target);
        var data = {
            name : bloc_editable.find("#bgtag_input_name").val(),
            slug : bloc_editable.find("#bgtag_input_slug").val(),
        };
        loadBlocEdit(bloc_editable);
        $.ajax({
            type: 'POST',
            url: target,
            data: data,
            dataType : 'json',
            success: function(data){
                if(data.state){
                    bloc_editable.find("#bgtag_view_name").text(data.name);
                    bloc_editable.find("#bgtag_view_slug").text(data.slug);
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


    //delete post
    $('body').on('click','.delete_post',function(e){
        var id = $(this).data("id");
        var title = $(this).data("title");
        var target = $(this).data("target");

        var content = "";
        content += '<div style="padding:10px; width:auto; background:#fff; border-radius:3px">';
        content += '<div style="text-align:center;padding:10px 0"> Voulez vous effectuer la suppression de l\'article "<strong>'+title+'</strong>"?</div>';
        content += '<div style="text-align:center">	';
        content += '<span class="button_closable" style="background:#bbb; border-radius: 3px; cursor:pointer; display:inline-block; margin:auto; padding:5px;margin:5px">	Annuler	</span>';
        content += '<span class="confirm_delete_post button_closable" data-entity="post" data-id="'+id+'" data-target="'+target+'" style="background:#bbb; border-radius: 3px; cursor:pointer; display:inline-block; margin:auto; padding:5px;margin:5px">	Confirmer	</span>	';
        content += '</div>';
        content += '</div>';

        popup(content, 500, true);
    });

    //confirm delete field
    $('body').on('click','.confirm_delete_post',function(e){
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
                        case 'post':
                            $( "#post_"+data.id ).remove();
                            $(".nb_posts").html(data.posts.length);
                            $(".nb_published_posts").html(data.publishedPosts.length);
                            $(".nb_not_published_posts").html(data.notPublishedPosts.length);
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