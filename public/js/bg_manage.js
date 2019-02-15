
$(function() {
    //change illustration post open popup
    $('body').on('click','#change_illustration',function(event){
        var target = $(this).data("target");

        var content = "<div style='text-align:center;padding:10px; color:#fff'>Chargement ...</div>";
        popup(content, 530, true);
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
    *upload illustration for post
    */
    $('body').on('change','#illustrationfile',function(event){
        var $this = $(this);
        var file = $this[0].files[0];
        var target = $this.data('target');
        var data = new FormData();
        data.append('file', file);

        var size = file.size;
        var fileType = file.type;
        var ValidImageTypes = ["image/jpeg", "image/png"];
        if(size > 1024 * 1024 * 5){
            var content = '<div style="padding:10px; width:auto; background:#fff; border-radius:3px; "><div style="text-align:center; margin-bottom: 20px">	<span>Veuillez uploader une image de taille inférieure à 5MB.</span></div><div style="text-align:center">	<span class="button_closable" style="background:#888; border-radius: 3px; cursor:pointer; display:inline-block; margin:auto; padding:5px 15px;">	OK	</span></div></div>';
            popup(content, 500, true);
		}else if($.inArray(fileType, ValidImageTypes) < 0){
            var content = '<div style="padding:5px; width:auto; background:#fff; border-radius:3px;"><div style="text-align:center; margin-bottom: 20px">	<span>Veuillez uploader un fichier image de type png ou jpg.</span></div><div style="text-align:center">	<span class="button_closable" style="background:#888; border-radius: 3px; cursor:pointer; display:inline-block; margin:auto; padding:5px 15px;">	OK	</span></div></div>';
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
                        $(".illustration_item").removeClass("active");
                        $(".images_popup_wrapper").append(data.illustrationItemContent);

                        $("#post_illustration").attr("src", data.illustration116x116);
                        $("#post_illustration"+".600x250").attr("src", data.illustration600x250);

                        destroySpinner();
                        $(".images_popup_wrapper").animate({ scrollTop: $('.images_popup_wrapper').prop("scrollHeight")}, 500);
                    }else{
                        destroySpinner();
                        alert("Une erreur est survenue");
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.log(jqXHR.status);
                    console.log(textStatus);
                    console.log(errorThrown);
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
	
    $('#form_post_edit').on('submit', function(e){
        e.preventDefault();
        var $this = $(this);
		var bloc_editable = $this.find(".bloc_editable");
        var target = $this.attr('action');
        var title = bloc_editable.find("#post_input_title").val().trim();
        if(title == ""){
            var title_error_msg = "Veuillez fournir un titre à votre article";
            if($('.error_title').length > 0){
                $('.error_title').show().html(title_error_msg);
            }
        }else{
            if($('.error_title').length > 0){
                $('.error_title').hide().html("");
            }
            var data = {
                title : title,
                slug : bloc_editable.find("#post_input_slug").val(),
            };
            loadBlocEdit(bloc_editable);
            $.ajax({
                type: 'POST',
                url: target,
                data: data,
                dataType : 'json',
                success: function(data){
                    if(data.state){
                        bloc_editable.find("#post_view_title").text(data.title);
                        bloc_editable.find("#post_view_slug").text(data.slug);
                        $(".sl_title_"+data.postId).text(data.title);
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
        }
    });
	
    $('.btn_doedit_content_post').on('click', function(){
        var $this = $(this);
		var bloc_editable = $this.closest(".bloc_editable");
        var target = $this.data('target');
		var data = {
			introduction : bloc_editable.find(".post_input_introduction").val(),
            content : CKEDITOR.instances['post_input_content'].getData()
			//content : bloc_editable.find(".post_input_content").val()
		};
		loadBlocEdit(bloc_editable);
        $.ajax({
            type: 'POST',
            url: target,
            data: data,
            dataType : 'json',
            success: function(data){
				if(data.state){
					bloc_editable.find(".post_view_introduction").html(data.introduction);
					bloc_editable.find(".post_view_content").html(data.content);
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
    *select post illustration
    */
    $('body').on('click','.illustration_popup',function(event){
        var $this = $(this);
        var target = $this.data('target');
        var type = $this.data('type');

        createSpinner();
        $.ajax({
            type: 'POST',
            url: target,
            dataType : 'json',
            success: function(data){
                $(".image_item").removeClass("active");
                $this.closest(".image_item").addClass("active");
                $("#post_illustration").attr("src", data.illustration116x116);
                $("#post_illustration"+".600x250").attr("src", data.illustration600x250);
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

    //delete post illustration
    $('body').on('click','.delete_illustration',function(event){
        var $this = $(this)
        var target = $this.data("target");
        var type = $this.data("type");

        createSpinner();
        $.ajax({
            type: 'POST',
            url: target,
            dataType : 'json',
            success: function(data){
                if(data.state){
                    $this.closest(".image_item").remove();
                    if(data.isCurrent){
                        $("#default_illustration").addClass("active");
                        $("#post_illustration").attr("src", data.illustration116x116);
                        $("#post_illustration"+".600x250").attr("src", data.illustration600x250);

                        $(".images_popup_wrapper").animate({ scrollTop: 0}, 500);
                    }
                    destroySpinner();
                }else{
                    alert("Une erreur est survenue");
                    destroySpinner();
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                destroySpinner();
            }
        });

    });
    
    $('body').on('click','#show_map_event',function(event){
        $("#map_event").show();
        $(this).hide();
        $("#hide_map_event").css('display','inline-block');
        
    });
    
    $('body').on('click','#hide_map_event',function(event){
        $("#map_event").hide();
        $(this).hide();
        $("#show_map_event").css('display','inline-block');
    });

    if($('#show_map_event').length == 1){
        var $this = $('#show_map_event');
        var coords = [{
            'latitude': $this.attr('data-latitude'),
            'longitude': $this.attr('data-longitude'),
            'label': $this.attr('data.location'),
        }];
        var bloc = 'map_event';
        showMapEvent(coords, bloc)
    }
    function showMapEvent(coords, bloc) {
		var centerLat = 0;
		var centerLng = 0;
		var zoom = 15;
		var coordsLength = coords.length;
		
		if(coordsLength > 1){
			zoom = 6;
		}
		
		for(var i = 0; i < coordsLength; i++ ){
			var coord = coords[i];
			centerLat += coord.latitude / coordsLength;
			centerLng += coord.longitude / coordsLength;
		}
		
		var mapOptions = {
			zoom: zoom,
			center: new google.maps.LatLng(centerLat,centerLng),
			mapTypeId: google.maps.MapTypeId.ROADMAP
		}
		var map = new google.maps.Map(document.getElementById(bloc), mapOptions);

		var markers = [];
		for(var i = 0; i < coordsLength; i++ ){
			var coord = coords[i];
			var myLatLng = new google.maps.LatLng(coord.latitude,coord.longitude);
			markers[i] = new google.maps.Marker({
				position: myLatLng,
				title: coord.label
			});
			//markers[i].setIcon(coord.icon);
			markers[i].setMap(map);
		}
    }
    
	
	//delete commentaire
	$('body').on('click','.delete_post_comment ',function(e){
		var id = $(this).data("id");
		var target = $(this).data("target");
		
		var content = "";
		content += '<div style="padding:10px; width:auto; background:#fff; border-radius:3px">';
			content += '<div style="text-align:center;padding:10px 0"> Voulez vous supprimer ce commentaire de la liste?</div>';
			content += '<div style="text-align:center">	';
				content += '<span class="button_closable" style="background:#bbb; border-radius: 3px; cursor:pointer; display:inline-block; margin:auto; padding:5px;margin:5px">	Annuler	</span>';
				content += '<span class="confirm_delete_post_comment button_closable" data-entity="post_comment" data-id="'+id+'" data-target="'+target+'" style="background:#bbb; border-radius: 3px; cursor:pointer; display:inline-block; margin:auto; padding:5px;margin:5px">	Confirmer	</span>	';
			content += '</div>';
		content += '</div>';
        
		popup(content, 500, true);
    });

	$('body').on('click','.delete_event_comment ',function(e){
		var id = $(this).data("id");
		var target = $(this).data("target");
		
		var content = "";
		content += '<div style="padding:10px; width:auto; background:#fff; border-radius:3px">';
			content += '<div style="text-align:center;padding:10px 0"> Voulez vous supprimer ce commentaire de la liste?</div>';
			content += '<div style="text-align:center">	';
				content += '<span class="button_closable" style="background:#bbb; border-radius: 3px; cursor:pointer; display:inline-block; margin:auto; padding:5px;margin:5px">	Annuler	</span>';
				content += '<span class="confirm_delete_event_comment button_closable" data-entity="event_comment" data-id="'+id+'" data-target="'+target+'" style="background:#bbb; border-radius: 3px; cursor:pointer; display:inline-block; margin:auto; padding:5px;margin:5px">	Confirmer	</span>	';
			content += '</div>';
		content += '</div>';
        
		popup(content, 500, true);
    });
    
	//confirm delete
	$('body').on('click','.confirm_delete_post_comment, .confirm_delete_event_comment',function(e){
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
				if(data.state){
                    var entity = $this.attr('data-entity');
                    switch (entity) {
						case 'post_comment':
							$( "#post_comment_"+data.id ).remove();
							$(".nb_post_comments").html(data.comments.length);
							break;
                        case 'event_comment':
                            $( "#event_comment_"+data.id ).remove();
                            $(".nb_event_comments").html(data.comments.length);
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