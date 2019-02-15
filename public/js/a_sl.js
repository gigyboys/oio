
$(function() {
	var timeOutIdQuery = 0;

    $( "#datebegin, #dateend" ).datepicker({
		dateFormat: 'dd/mm/yy'
    });
    $( "#datebegin_assignation, #dateend_assignation" ).datepicker({
		dateFormat: 'dd/mm/yy',
        minDate: 0
    });

	// map : getting coord contact
    $('.get-coords').on('click', function(){
        var content = '<div style="padding:10px; width:auto; background:#fff;"><div id="map" style="height: 400px"></div> </div>';
        popup(content, 600, true);

        var myLatlng = {lat: -18.90329215475846, lng: 47.5195606651306};
        if($('#contact_input_latitude').val().trim() != "" && $('#contact_input_longitude').val().trim() != ""){
            myLatlng = {lat: Number($('#contact_input_latitude').val().trim()), lng: Number($('#contact_input_longitude').val().trim())};
        }

        var map = new google.maps.Map(document.getElementById('map'), {
            zoom: 9,
            center: myLatlng
        });

        var marker = new google.maps.Marker({
            draggable:true,
            position: myLatlng,
            map: map,
            title: 'Position'
        });

        map.addListener('click', function(event) {
            var latitude = event.latLng.lat();
            var longitude = event.latLng.lng();
            marker.setPosition({lat: latitude, lng: longitude});
            setCoordonnees(latitude, longitude);
        });

        marker.addListener('dragend', function(event) {
            var latitude = event.latLng.lat();
            var longitude = event.latLng.lng();
            setCoordonnees(latitude, longitude);
        });
    });


    function setCoordonnees(lat, lng) {
		$('#contact_input_latitude').val(lat);
		$('#contact_input_longitude').val(lng);
    }

    /*
     * position des établissements
     */
	if($(".list_sl_position").length == 1){
        $(".list_sl_position").dragsort({
            dragSelector: ".sl_item",
            dragEnd: function() {
            	var order = '';
            	var count = 0
                $(".list_sl_position .sl_item").each(function( index ) {
                	count++;
                	if(count != 1){
                        order += '-';
                    }
                    order += $(this).attr('data-id');
                });
                var target = $(".list_sl_position").attr('data-target');

                var data = {
                    'order' : order,
                };

                $.ajax({
                    type: 'GET',
                    url: target,
                    data: data,
                    dataType : 'json',
                    success: function(data){
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
            placeHolderTemplate: '<div class="sl_item"><div class="logo"></div><div class="name"></div></div>'
        });
	}

    /*
    *upload cover for school
    */
    $('#coverfile').on('change', function(){
        var $this = $(this);
        var file = $this[0].files[0];
        var target = $this.data('target');
        var data = new FormData();
        data.append('file', file);
		
        $.ajax({
            type: 'POST',
            url: target,
            data: data,
            contentType: false,
            processData: false,
            dataType : 'json',
            success: function(data){
				if(data.state == 1){
					$("#cover_box").html(data.cover300x100);
				}else{
					alert("Une erreur est survenue. Veuillez selectionner un fichier image valide de taille inférieure à 2Mo")
				}
            },
            error: function(jqXHR, textStatus, errorThrown) {
				console.log(jqXHR.status);
				console.log(textStatus);
				console.log(errorThrown);
			}
        });
    });


    $('#btn_sl_doedit').on('click', function(){
        var $this = $(this);
		var bloc_editable = $this.closest(".bloc_editable");
        var target = $this.data('target');
		var data = {
			'name' 		: bloc_editable.find("#sl_input_name").val(),
            'shortName'	: bloc_editable.find("#sl_input_shortname").val(),
            'slug' 		: bloc_editable.find("#sl_input_slug").val(),
            'slogan' 	: bloc_editable.find("#sl_input_slogan").val(),
            'typeId' 	: bloc_editable.find("input[name=typeId]:checked").val(),
            'optionId' 	: bloc_editable.find("input[name=optionId]:checked").val()
		};
		loadBlocEdit(bloc_editable);
        $.ajax({
            type: 'POST',
            url: target,
            data: data,
            dataType : 'json',
            success: function(data){
				if(data.state){
					bloc_editable.find("#sl_view_name").text(data.name);
					bloc_editable.find("#sl_view_shortname").text(data.shortName);
					bloc_editable.find("#sl_view_slug").text(data.slug);
					bloc_editable.find("#sl_view_slogan").text(data.slogan);
					/*type*/
                    bloc_editable.find("#sl_view_type").text(data.typeName);
                    /*options*/
                    bloc_editable.find("#sl_view_option").text(data.optionName);
                    $(".opt_name_"+data.schoolId).text(data.optionPluralName)
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
	
    $('#btn_sl_doedit_desc').on('click', function(){
        var $this = $(this);
		var bloc_editable = $this.closest(".bloc_editable");
        var target = $this.data('target');
		var data = {
            'shortDescription' : bloc_editable.find(".slt_input_shortdescription").val(),
            /*'description' : bloc_editable.find(".slt_input_description").val(),*/
            description : CKEDITOR.instances['sl_input_description'].getData()
		};
		loadBlocEdit(bloc_editable);
        $.ajax({
            type: 'POST',
            url: target,
            data: data,
            dataType : 'json',
            success: function(data){
				if(data.state){
					bloc_editable.find(".slt_view_shortdescription").html(data.shortDescription);
					bloc_editable.find(".slt_view_description").html(data.description);
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
	* save common field
	*/
    $('#btn_save_field_common').on('click', function(){
        var $this = $(this);
		var bloc_editable = $this.closest(".bloc_editable");
        var target = $this.data('target');
		var data = {
			name : bloc_editable.find("#field_input_name").val(),
			slug : bloc_editable.find("#field_input_slug").val(),
			/*description : bloc_editable.find(".field_input_description").val(),*/
			description : CKEDITOR.instances['field_input_description'].getData()
		};
		loadBlocEdit(bloc_editable);
        $.ajax({
            type: 'POST',
            url: target,
            data: data,
            dataType : 'json',
            success: function(data){
				if(data.state){
					bloc_editable.find("#field_view_name").text(data.name);
					bloc_editable.find("#field_view_slug").text(data.slug);
					bloc_editable.find("#field_view_description").html(data.description);
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

	/*
	* doedit document
	*/
    $('#btn_doedit_document').on('click', function(){
        var $this = $(this);
		var bloc_editable = $this.closest(".bloc_editable");
        var target = $this.data('target');
		var data = {
			name : bloc_editable.find("#doc_input_name").val(),
			description : bloc_editable.find(".doc_input_description").val(),
			authorizationId : bloc_editable.find("#doc_input_authorization").val()
		};
		loadBlocEdit(bloc_editable);
        $.ajax({
            type: 'POST',
            url: target,
            data: data,
            dataType : 'json',
            success: function(data){
				if(data.state){
					bloc_editable.find("#doc_view_name").text(data.name);
					bloc_editable.find("#doc_view_description").html(data.description);
					bloc_editable.find("#doc_view_authorization").html(data.authorizationName);
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

	/*
	* save common contact
	*/
    $('#btn_save_contact_common').on('click', function(){
        var $this = $(this);
		var bloc_editable = $this.closest(".bloc_editable");
        var target = $this.data('target');
		var data = {
			name : bloc_editable.find("#contact_input_name").val(),
			slug : bloc_editable.find("#contact_input_slug").val(),
			address : bloc_editable.find("#contact_input_address").val(),
			email : bloc_editable.find("#contact_input_email").val(),
			phone : bloc_editable.find("#contact_input_phone").val(),
			website : bloc_editable.find("#contact_input_website").val(),
            latitude : bloc_editable.find("#contact_input_latitude").val(),
			longitude : bloc_editable.find("#contact_input_longitude").val(),
			description : bloc_editable.find(".contact_input_description").val(),
		};
		loadBlocEdit(bloc_editable);
        $.ajax({
            type: 'POST',
            url: target,
            data: data,
            dataType : 'json',
            success: function(data){
				if(data.state){
					bloc_editable.find("#contact_view_name").text(data.name);
					bloc_editable.find("#contact_view_slug").text(data.slug);
					bloc_editable.find("#contact_view_address").text(data.address);
					bloc_editable.find("#contact_view_email").text(data.email);
					bloc_editable.find("#contact_view_phone").text(data.phone);
					bloc_editable.find("#contact_view_website").text(data.website);
                    bloc_editable.find("#contact_view_latitude").text(data.latitude);
					bloc_editable.find("#contact_view_longitude").text(data.longitude);
					bloc_editable.find("#contact_view_description").html(data.description);
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
	
	//add entity common
    $('body').on('click','.add_field_btn, .add_contact_btn',function(e){
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
	
	//delete field
	$('body').on('click','.delete_field',function(e){
		var id = $(this).data("id");
		var name = $(this).data("name");
		var target = $(this).data("target");
		
		var content = "";
		content += '<div style="padding:10px; width:auto; background:#fff; border-radius:3px">';
			content += '<div style="text-align:center;padding:10px 0"> Voulez vous effectuer la suppression de la filière "<strong>'+name+'</strong>"?</div>';
			content += '<div style="text-align:center">	';
				content += '<span class="button_closable" style="background:#bbb; border-radius: 3px; cursor:pointer; display:inline-block; margin:auto; padding:5px;margin:5px">	Annuler	</span>';
				content += '<span class="confirm_delete_field button_closable" data-entity="field" data-id="'+id+'" data-target="'+target+'" style="background:#bbb; border-radius: 3px; cursor:pointer; display:inline-block; margin:auto; padding:5px;margin:5px">	Confirmer	</span>	';
			content += '</div>';
		content += '</div>';
        
		popup(content, 500, true);
    });
	
	//delete evaluation
	$('body').on('click','.delete_evaluation',function(e){
		var id = $(this).data("id");
		var target = $(this).data("target");
		
		var content = "";
		content += '<div style="padding:10px; width:auto; background:#fff; border-radius:3px">';
			content += '<div style="text-align:center;padding:10px 0"> Voulez vous retirer cette évaluation de la liste?</div>';
			content += '<div style="text-align:center">	';
				content += '<span class="button_closable" style="background:#bbb; border-radius: 3px; cursor:pointer; display:inline-block; margin:auto; padding:5px;margin:5px">	Annuler	</span>';
				content += '<span class="confirm_delete_evaluation button_closable" data-entity="evaluation" data-id="'+id+'" data-target="'+target+'" style="background:#bbb; border-radius: 3px; cursor:pointer; display:inline-block; margin:auto; padding:5px;margin:5px">	Confirmer	</span>	';
			content += '</div>';
		content += '</div>';
        
		popup(content, 500, true);
    });

	//delete contact
	$('body').on('click','.delete_contact',function(e){
        var id = $(this).data("id");
		var name = $(this).data("name");
		var target = $(this).data("target");

		var content = "";
		content += '<div style="padding:10px; width:auto; background:#fff; border-radius:3px">';
			content += '<div style="text-align:center;padding:10px 0"> Voulez vous effectuer la suppression du contact "<strong>'+name+'</strong>"?</div>';
			content += '<div style="text-align:center">	';
				content += '<span class="button_closable" style="background:#bbb; border-radius: 3px; cursor:pointer; display:inline-block; margin:auto; padding:5px;margin:5px">	Annuler	</span>';
				content += '<span class="confirm_delete_contact button_closable" data-entity="schoolContact" data-id="'+id+'" data-target="'+target+'" style="background:#bbb; border-radius: 3px; cursor:pointer; display:inline-block; margin:auto; padding:5px;margin:5px">	Confirmer	</span>	';
			content += '</div>';
		content += '</div>';

		popup(content, 500, true);
    });

	//delete school category
	$('body').on('click','.delete_sl_cat',function(e){
        var id = $(this).data("id");
		var name = $(this).data("name");
		var target = $(this).data("target");
		var school = $(this).data("school");

		var content = "";
		content += '<div style="padding:10px; width:auto; background:#fff; border-radius:3px">';
			content += '<div style="text-align:center;padding:10px 0"> Voulez vous effectuer la suppression de la catégorie "<strong>'+name+'</strong>"?</div>';
			if(school != "0"){
                content += '<div class="sep"></div>';
                content += '<div style="text-align:center;padding:10px 0"> Cette catégorie contient '+school+' établissement(s). La suppression de cette catégorie n\'engendre pas la suppression des établissements qui y sont liés</div>';
			}
			content += '<div style="text-align:center">	';
				content += '<span class="button_closable" style="background:#bbb; border-radius: 3px; cursor:pointer; display:inline-block; margin:auto; padding:5px;margin:5px">	Annuler	</span>';
				content += '<span class="confirm_delete_sl_category button_closable" data-entity="schoolCategory" data-id="'+id+'" data-target="'+target+'" style="background:#bbb; border-radius: 3px; cursor:pointer; display:inline-block; margin:auto; padding:5px;margin:5px">	Confirmer	</span>	';
			content += '</div>';
		content += '</div>';

		popup(content, 500, true);
    });
	
	$('#input_sl_search_not_admin').on('keyup', function(ed){
        var $this = $(this);
		var query = $this.val();
		if(ed.which != 38 && ed.which != 40 && ed.which != 13){
			$('#view_result_wrap').show();
			
			$('#view_query').html(query);
			var target = $this.data('target');
			clearTimeout(timeOutIdQuery);
			timeOutIdQuery = setTimeout(function(){
				var data = {
					query: query
				};
				$.ajax({
					type: 'POST',
					url: target,
					data: data,
					dataType : 'json',
					success: function(data){
						if(data.users.length>0){
							var htmlappend = '<div>';
							for(var i = 0; i <data.users.length; i++ ){
								var user = data.users[i];
								//alert(user.username);
								htmlappend += '<div style="background:#ccc; height:1px; width:100%; margin:3px 0"></div>';
								htmlappend += '<div data-id="'+user.id+'" data-target="'+user.urlSetAdmin+'" data-username="'+user.username+'" class="item_user" style="padding:2px; cursor:pointer">';
								htmlappend += '<div style="width:60px;height:60px;background:#ddd;float:left">';
								htmlappend += '<img id="" style="width: 60px; height: 60px" src="'+user.avatar+'" alt="'+user.username+'" />';
								htmlappend += '</div>';
								htmlappend += '<div style="margin-left:65px">';
								htmlappend += user.username+'<br />'+user.name+'<br />'+user.email;
								htmlappend += '</div>';
								htmlappend += '<div style="clear:both"></div>';
								htmlappend += '</div>';
							}
							htmlappend += '</div>';
							$("#view_result").html(htmlappend);
							$("#view_result .item_user").first().addClass("user_selected");
							
							$('#view_result').on('mouseover','.item_user',function(){
								var $this = $(this);
								$("#view_result .item_user").removeClass("user_selected");
								$this.addClass("user_selected");
							});
							
						}else{
							//alert("Aucun resultat");
							var htmlappend = '<div>';
							htmlappend += '<div style="background:#ccc; height:1px; width:100%; margin:3px 0"></div>';
							htmlappend += '<div>Aucun resultat trouvé</div>';
							htmlappend += '</div>';
							$("#view_result").html(htmlappend);
						}
					},
					error: function(jqXHR, textStatus, errorThrown) {
					}
				});
			}, 400);
		}
    });
	
		
	$(document).keydown(function(ed) {
		var userSelected = $( "#view_result .item_user.user_selected" );
		var indexUserSelected = $( "#view_result .item_user" ).index( userSelected );
		var users = $( "#view_result .item_user" );
		var lengthusers = users.length;
		switch(ed.which) {
			case 38: // up
				var indexPrecUserSelected = indexUserSelected-1;
				if(indexUserSelected == 0){
					indexPrecUserSelected = lengthusers-1
				}
				
				userSelected.removeClass("user_selected");
				$( "#view_result .item_user" ).eq(indexPrecUserSelected).addClass("user_selected");
				ed.preventDefault();
			break;

			case 40: // down
				var indexNextUserSelected = 0;
				if(lengthusers-1>indexUserSelected){
					indexNextUserSelected = indexUserSelected+1;
				}
				
				userSelected.removeClass("user_selected");
				$( "#view_result .item_user" ).eq(indexNextUserSelected).addClass("user_selected");
				ed.preventDefault();
			break;

			case 13: // enter
				if(userSelected.length == 1){
					//alert("id : "+ userSelected.data("id") + " - name : "+userSelected.data("username"));
					setAdmin(userSelected);
					ed.preventDefault();
				}
			break;

			default: return;
		}
		
	});
	
	function setAdmin(element){
		var $this = element;
		//alert("id : "+ $this.data("id") + " - name : "+$this.data("username"));
		$("#view_result").html("");
		$("#view_result_wrap").hide();
		var target = $this.data('target');
		var data = {
		};
        $.ajax({
            type: 'POST',
            url: target,
            data: data,
            dataType : 'json',
            success: function(data){
				if(data.state){
					$("#a_sl_list_admin").prepend(data.schoolAdminItem);
				}
				else{
					alert("une erreur est survenue");
				}
            },
            error: function(jqXHR, textStatus, errorThrown) {
			}
        });
	}
	
	$('#view_result_wrap').on('click', '.item_user', function(ed){
		var $this = $(this);
		setAdmin($this);
    });
	
	$('.remove_school_admin').on('click', '.item_user', function(ed){
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
				if(data.state){
					$this.closest(".school_admin_item").remove();
				}
				else{
					alert("une erreur est survenue");
				}
            },
            error: function(jqXHR, textStatus, errorThrown) {
			}
        });
    });
	
	
	$(document).click(function() {
		$("#view_result").html("");
		$("#view_result_wrap").hide();
    });
	
	$('body').on('click','#view_result_wrap',function(e){
        e.stopPropagation(); 
        return false;      
    });
	
	//confirm delete
	$('body').on('click','.confirm_delete_field, .confirm_delete_contact, .confirm_delete_sl_category, .confirm_delete_evaluation',function(e){
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
                        case 'field':
                            $( "#field_"+data.id ).remove();
                            $(".nb_fields").html(data.fields.length);
                            $(".nb_published_fields").html(data.publishedFields.length);
                            $(".nb_not_published_fields").html(data.notPublishedFields.length);
                            break;
                        case 'schoolContact':
                            $( "#contact_"+data.id ).remove();
                            $(".nb_contacts").html(data.contacts.length);
                            $(".nb_published_contacts").html(data.publishedContacts.length);
                            $(".nb_not_published_contacts").html(data.notPublishedContacts.length);
                            break;
                        case 'schoolCategory':
                            $( "#sl_category_"+data.id ).remove();
                            $(".nb_categories").html(data.categories.length);
                            break;
						case 'evaluation':
							$( "#evaluation_"+data.id ).remove();
							$(".nb_evaluations").html(data.evaluations.length);
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
	
	/*
	* save common contact
	*/
    $('#btn_save_sl_contact_common').on('click', function(){
        var $this = $(this);
		var bloc_editable = $this.closest(".bloc_editable");
        var target = $this.data('target');
		var data = {
			address : bloc_editable.find("#sl_contact_input_address").val(),
			email : bloc_editable.find("#sl_contact_input_email").val(),
			phone : bloc_editable.find("#sl_contact_input_phone").val(),
			website : bloc_editable.find("#sl_contact_input_website").val(),
			longitude : bloc_editable.find("#sl_contact_input_longitude").val(),
			latitude : bloc_editable.find("#sl_contact_input_latitude").val()
		};
		loadBlocEdit(bloc_editable);
        $.ajax({
            type: 'POST',
            url: target,
            data: data,
            dataType : 'json',
            success: function(data){
				if(data.state){
					bloc_editable.find("#sl_contact_view_address").text(data.address);
					bloc_editable.find("#sl_contact_view_email").text(data.email);
					bloc_editable.find("#sl_contact_view_phone").text(data.phone);
					bloc_editable.find("#sl_contact_view_website").text(data.website);
					bloc_editable.find("#sl_contact_view_longitude").text(data.longitude);
					bloc_editable.find("#sl_contact_view_latitude").text(data.latitude);
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
	* save translate contact
	*/
    $('.btn_save_sl_contactt').on('click', function(){
        var $this = $(this);
		var bloc_editable = $this.closest(".bloc_editable");
        var target = $this.data('target');
		var data = {
			description : bloc_editable.find(".sl_contactt_input_description").val()
		};
		loadBlocEdit(bloc_editable);
        $.ajax({
            type: 'POST',
            url: target,
            data: data,
            dataType : 'json',
            success: function(data){
				if(data.state){
					bloc_editable.find(".sl_contactt_view_description").html(data.description);
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
	 *modification category sl common
	 */
    $('#btn_doedit_slcat').on('click', function(){
        var $this = $(this);
		var bloc_editable = $this.closest(".bloc_editable");
        var target = $this.data('target');
		var data = {
			name : bloc_editable.find("#slcat_input_name").val(),
			slug : bloc_editable.find("#slcat_input_slug").val(),
			/*description : bloc_editable.find("#slcat_input_description").val()*/
			description : CKEDITOR.instances['slcat_input_description'].getData()
		};
		loadBlocEdit(bloc_editable);
        $.ajax({
            type: 'POST',
            url: target,
            data: data,
            dataType : 'json',
            success: function(data){
				if(data.state){
					bloc_editable.find("#slcat_view_name").text(data.name);
					bloc_editable.find("#slcat_view_slug").text(data.slug);
					bloc_editable.find("#slcat_view_description").html(data.description);
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
    $('.btn_save_slcat_trans').on('click', function(){
        var $this = $(this);
		var bloc_editable = $this.closest(".bloc_editable");
        var target = $this.data('target');
		var data = {
			name : bloc_editable.find(".slcatt_input_name").val(), 
			description : bloc_editable.find(".slcatt_input_description").val()
		};
		loadBlocEdit(bloc_editable);
        $.ajax({
            type: 'POST',
            url: target,
            data: data,
            dataType : 'json',
            success: function(data){
				if(data.state){
					bloc_editable.find(".slcatt_view_name").html(data.name);
					bloc_editable.find(".slcatt_view_description").html(data.description);
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
	
	
	//toggleCategory sl
    $('.a_sl_toggle_cat').on('click', function(){
        var $this = $(this);
        var target = $this.data('target');
        $.ajax({
            type: 'POST',
            url: target,
            dataType : 'json',
            success: function(data){
				if(data.state){
					if(data.isCategory){
						$this.find(".is_cat_btn").hide();
						$this.find(".is_not_cat_btn").show();
					}else{
						$this.find(".is_cat_btn").show();
						$this.find(".is_not_cat_btn").hide();
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
	
	
	//setDefaultSchool
	$('body').on('click','.cat_sl_item',function(e){
		var $this = $(this);
        var target = $this.data('target');
		var data = {
		};

		createSpinner();
		$.ajax({
            type: 'POST',
            url: target,
            data: data,
            dataType : 'json',
            success: function(data){
				if(data.state){
					$( "#cat_default_sl" ).html(data.schoolName+" <span id=\"cat_default_null\" style=\"cursor:pointer\"><img src=\"/image/pull_icon.png\" style=\"width:12px; height:auto\"></span>");
				}
				else{
					alert("une erreur est survenue");
				}
				destroySpinner();
            },
            error: function(jqXHR, textStatus, errorThrown) {
				console.log(jqXHR.status);
				console.log(textStatus);
				console.log(errorThrown);
			}
        });
    });
	
	//setDefaultSchool null
    $('body').on('click','#cat_default_null',function(e){
		var $this = $(this);
        var target = $('#cat_default_sl').data('target');
		var data = {
		};

		createSpinner();
		$.ajax({
            type: 'POST',
            url: target,
            data: data,
            dataType : 'json',
            success: function(data){
				if(data.state){
					$( "#cat_default_sl" ).html("Aucun établissement par défaut");
				}
				else{
					alert("une erreur est survenue");
				}
				destroySpinner();
            },
            error: function(jqXHR, textStatus, errorThrown) {
				console.log(jqXHR.status);
				console.log(textStatus);
				console.log(errorThrown);
			}
        });
    });
	
	//remove school from category
    $('body').on('click','.remove_sl_cat',function(e){
		var categoryId = $(this).data("cat-id");
		var schoolId = $(this).data("sl-id");
		var categoryName = $(this).data("cat-name");
		var schoolName = $(this).data("sl-name");
		var target = $(this).data("target");
		
		var content = "";
		content += '<div style="padding:10px; width:auto; background:#fff; border-radius:3px">';
			content += '<div style="text-align:center;padding:10px 0"> Voulez vous retirer "'+schoolName+'" de la catégorie "'+categoryName+'"?</div>';
			content += '<div style="text-align:center">	';
				content += '<span class="button_closable" style="background:#888; border-radius: 3px; cursor:pointer; display:inline-block; margin:auto; padding:5px;margin:5px">	Annuler	</span>';
				content += '<span class="confirm_remove_sl_cat button_closable" data-cat-id="'+categoryId+'" data-sl-id="'+schoolId+'" data-target="'+target+'" style="background:#888; border-radius: 3px; cursor:pointer; display:inline-block; margin:auto; padding:5px;margin:5px">	Confirmer	</span>	';
			content += '</div>';
		content += '</div>';
        
		popup(content, 500, true);
		
    });
	
	//confirm remove school from category
    $('body').on('click','.confirm_remove_sl_cat',function(e){
		var $this = $(this);
		var categoryId = $this.data("cat-id");
		var schoolId = $this.data("sl-id");
        var target = $this.data('target');
		var data = {
			schoolId : schoolId
		};
		/*
		var widthElement = $( "#cat_sl_"+data.schoolId ).width()-2;
		var heightElement = $( "#cat_sl_"+data.schoolId ).height()-2;
		$( "#cat_sl_"+data.schoolId ).find(".a_table_cell_id").prepend("<div class=\"load_line\" style=\"position:absolute; text-align:center; margin-top: -8px; height:"+heightElement+"px; width:"+widthElement+"px; background:rgba(255,255,255,0.8)\">Suppression chargement ...</div>");
        */
		createSpinner();
		$.ajax({
            type: 'POST',
            url: target,
            //data: data,
            dataType : 'json',
            success: function(data){
				if(data.state){
					$( "#cat_sl_"+data.schoolId ).remove();
					if(data.isDefaultSchool){
						$( "#cat_default_sl" ).html("Aucune école par défaut");
					}
				}
				else{
					alert("une erreur est survenue");
				}
                $( ".a_list_search_input" ).trigger( "keyup" );
				destroySpinner();
            },
            error: function(jqXHR, textStatus, errorThrown) {
				console.log(jqXHR.status);
				console.log(textStatus);
				console.log(errorThrown);
			}
        });
		
    });
	
	//delete contact
	$('body').on('click','.delete_school_contact',function(e){
		var id = $(this).data("id");
		var defaultname = $(this).data("defaultname");
		var target = $(this).data("target");
		
		var content = "";
		content += '<div style="padding:10px; width:auto; background:#fff; border-radius:3px">';
			content += '<div style="text-align:center;padding:10px 0"> Voulez vous effectuer la suppression du contact "'+defaultname+'"?</div>';
			content += '<div style="text-align:center">	';
				content += '<span class="button_closable" style="background:#bbb; border-radius: 3px; cursor:pointer; display:inline-block; margin:auto; padding:5px;margin:5px">	Annuler	</span>';
				content += '<span class="confirm_delete_school_contact button_closable" data-id="'+id+'" data-target="'+target+'" style="background:#bbb; border-radius: 3px; cursor:pointer; display:inline-block; margin:auto; padding:5px;margin:5px">	Confirmer	</span>	';
			content += '</div>';
		content += '</div>';
        
		popup(content, 500, true);
    });
	
	//confirm delete contact
	$('body').on('click','.confirm_delete_school_contact',function(e){
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
					$( "#contact_"+data.id ).remove();
				}
				else{
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
	
    /*
    *upload logo for school
    */
    $('#logofile').on('change', function(){
        var $this = $(this);
        var file = $this[0].files[0];
        var target = $this.data('target');
        var data = new FormData();
        data.append('file', file);
		
        $.ajax({
            type: 'POST',
            url: target,
            data: data,
            contentType: false,
            processData: false,
            dataType : 'json',
            success: function(data){
				if(data.state == 1){
					$("#school_logo").attr("src", data.logo116x116);
				}else{
					alert("Une erreur est survenue. Veuillez selectionner un fichier image valide de taille inférieure à 2Mo")
				}
            },
            error: function(jqXHR, textStatus, errorThrown) {
				console.log(jqXHR.status);
				console.log(textStatus);
				console.log(errorThrown);
			}
        });
    });
	
	//change school logo, cover
	$('body').on('click','#change_logo, #change_cover',function(event){
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
    *select school logo, cover
    */
    $('body').on('click','.logo_popup, .cover_popup',function(event){
        var $this = $(this);
        var target = $this.data('target');
        var type = $this.data('type');
		
		createSpinner();
        $.ajax({
            type: 'POST',
            url: target,
            dataType : 'json',
            success: function(data){
				if(type == "logo"){
					$(".logo_item").removeClass("active");
					$this.closest(".logo_item").addClass("active");
					$("#school_logo").attr("src", data.logo116x116);
				}else if(type == "cover"){
					$(".cover_item").removeClass("active");
					$this.closest(".cover_item").addClass("active");
					$("#school_cover").attr("src", data.cover300x100);
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
	
	//delete school logo, cover
	$('body').on('click','.delete_logo, .delete_cover',function(event){
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
					if(type == "logo"){
						$this.closest(".logo_item").remove();
						if(data.isCurrentLogo){
							$("#default_logo").addClass("active");
							$("#school_logo").attr("src", data.logo116x116);
							
							$("#logos_wrapper").animate({ scrollTop: 0}, 500);
						}
					}else if(type == "cover"){
						$this.closest(".cover_item").remove();
						if(data.isCurrent){
							$("#default_cover").addClass("active");
							$("#school_cover").attr("src", data.cover300x100);
							
							$("#covers_wrapper").animate({ scrollTop: 0}, 500);
						}
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
	
    /*
    *upload logo, cover for school
    */
	$('body').on('change','#logofile, #coverfile',function(event){
        var $this = $(this);
        var file = $this[0].files[0];
        var target = $this.data('target');
        var type = $this.data('type');
        var data = new FormData();
		
        data.append('file', file);
		
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
					if(type == "logo"){
						$(".logo_item").removeClass("active");
						$("#logos_wrapper").append(data.logoItemContent);
						$("#school_logo").attr("src", data.logo116x116);
						$("#logos_wrapper").animate({ scrollTop: $('#logos_wrapper').prop("scrollHeight")}, 500);
					}else if(type == "cover"){
						$(".cover_item").removeClass("active");
						$("#covers_wrapper").append(data.coverItemContent);
						$("#school_cover").attr("src", data.cover300x100);
						$("#covers_wrapper").animate({ scrollTop: $('#covers_wrapper').prop("scrollHeight")}, 500);
					}
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