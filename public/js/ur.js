
$(function() {
	var tab_ur_item = $('.tab_ur_item');
	var content_tab_ur_item = $('.content_tab_ur_item');
	var timeOutIdEnterArray = []; 
	var timeOutIdLeaveArray = [];

    $('body').on('click','.tab_ur_item',function(e){
        tab_ur_item.removeClass('selected');
        $(this).addClass('selected');
		var this_id = $(this).attr('id');
		var content = $("#content_"+this_id);
        $('.content_tab_ur_item').removeClass('selected');
		
		content.addClass('selected');
        initTabsl();
		document.title = $(this).data("title");
		history.pushState('', '', $(this).attr("data-link"));
		
    });
	
	//mise a jour de common information.
	$('#form_ur_common').submit( function(e){
		e.preventDefault();
		$('#btn_save_ur_common').trigger('click');
	});
	
	$('#btn_save_ur_common').on('click', function(){
        var $this = $(this);
		var bloc_editable = $this.closest(".bloc_editable");
        var target = $this.data('target');
		console.log(target);
		var data = {
			name : bloc_editable.find("#ur_input_name").val(), 
			username : bloc_editable.find("#ur_input_username").val(), 
			location : bloc_editable.find("#ur_input_location").val(), 
			email : bloc_editable.find("#ur_input_email").val()
		};
		loadBlocEdit(bloc_editable);
        $.ajax({
            type: 'POST',
            url: target,
            data: data,
            dataType : 'json',
            success: function(data){
				switch(data.state) {
					case 0:
						alert(data.message);
						break;
					case 1:
						//name
						$("#ur_view_name").text(data.name);
						$("#ur_input_name").val(data.name);
						$("#ur_input_name_error").html("");
						$("#nav_src_ur_name").text(data.name);
						$("#nav_target_ur_name").text(data.name);
						
						//username
						$("#ur_view_username").text(data.username);
						$("#ur_input_username").val(data.username);
						$("#ur_input_username_error").html("");
						$("#nav_target_ur_username").text(data.username);
						
						//location
						if(data.location.trim() == ""){
							$("#ur_view_location_wrap").css("display","none");
						}else{
							$("#ur_view_location_wrap").css("display","inline");
						}
						$("#ur_view_location").text(data.location);
						$("#ur_input_location").val(data.location);
						$("#ur_input_location_error").html("");
						
						//email
						$("#ur_view_email").text(data.email);
						$("#ur_input_email").val(data.email);
						$("#ur_input_email_error").html("");
						$("#nav_target_ur_email").text(data.email);
						
						resetBlocEdit(bloc_editable);
						document.title = data.title;
						history.pushState('', 'Profile '+data.username, data.url);
						
						$("#ur_profil_link").attr("href", data.url);
						$("#tab_ur_about").attr("data-link", data.url);
						$("#ur_setting_link").attr("href", data.urlSetting);
						$("#tab_ur_setting").attr("data-link", data.urlSetting);
						break;
					case 2:
						$("#ur_input_name_error").html(data.error.errorName);
						$("#ur_input_username_error").html(data.error.errorUsername);
						$("#ur_input_email_error").html(data.error.errorEmail);
						editBlocEdit(bloc_editable);
						//alert(data.message);
						break;
					case 3:
						window.location = data.urlLogin;
						break;
					default:
						alert("Une erreur est survenue");
				}
				bloc_editable.find(".btn_loading").hide();
            },
            error: function(jqXHR, textStatus, errorThrown) {
				console.log(jqXHR.status);
				console.log(textStatus);
				console.log(errorThrown);
				bloc_editable.find(".btn_loading").hide();
			}
        });
    });
	
	$('#btn_save_ur_biography').on('click', function(){
		$("#msg_biography").html("");
        var $this = $(this);
		var bloc_editable = $this.closest(".bloc_editable");
        var target = $this.data('target');
		console.log(target);
		var data = {
			biography : bloc_editable.find("#ur_area_biography").val()
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
					resetBlocEdit(bloc_editable);
					$("#msg_biography").html("<span style='color:#090'>Votre biographie a bien été mise à jour</span>");
					$("#ur_view_biography").html(data.biography);
				}
				else{
					editBlocEdit(bloc_editable);
					alert(data.message);
				}
				bloc_editable.find(".btn_loading").hide();
            },
            error: function(jqXHR, textStatus, errorThrown) {
				console.log(jqXHR.status);
				console.log(textStatus);
				console.log(errorThrown);
				bloc_editable.find(".btn_loading").hide();
			}
        });
    });


	$('#password').on('submit', function(e){
        e.preventDefault();
        $('#btn_save_ur_password').trigger('click');
    });

	$('#btn_save_ur_password').on('click', function(){
		$("#msg_password").html("");
        var $this = $(this);
		var bloc_editable = $this.closest(".bloc_editable");
        var target = $this.data('target');
		console.log(target);
		var data = {
			currentPassword : bloc_editable.find("#ur_input_current_password").val(), 
			newPassword : bloc_editable.find("#ur_input_new_password").val(), 
			repeatPassword : bloc_editable.find("#ur_input_repeat_password").val()
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
					resetBlocEdit(bloc_editable);
					$("#msg_password").html("<span style='color:#090'>Votre mot de passe a été modifié avec succès.</span>");
					$("#ur_input_current_password").val("");
					$("#ur_input_new_password").val("");
					$("#ur_input_repeat_password").val("");
				}
				else{
					editBlocEdit(bloc_editable);
					alert(data.message);
				}
				bloc_editable.find(".btn_loading").hide();
            },
            error: function(jqXHR, textStatus, errorThrown) {
				console.log(jqXHR.status);
				console.log(textStatus);
				console.log(errorThrown);
				bloc_editable.find(".btn_loading").hide();
			}
        });
    });
	
	$('body').on('mouseover','.user_hover_info',function(){
        var $this = $(this);
		if(!$this.hasClass("ishover")){
			$this.addClass("ishover");
			var nbElementIsHover = $(".ishover").length;
			$this.attr("data-userinfoid", nbElementIsHover);
		}
		var userid = $this.data("userid");
		var name = $this.data("name");
		var userinfoid = $this.data("userinfoid");
		clearTimeout(timeOutIdEnterArray[userinfoid]);
		if($this.find(".popup_user_info").length == 0){
			clearTimeout(timeOutIdLeaveArray[userinfoid]);
			timeOutIdEnterArray[userinfoid] = setTimeout(function(){
				var popupWidth = 360;
				if($(window).width() - 40 < 360){
                    popupWidth =$(window).width() - 40 ;
				}
				var htmlprepend = '<div data-userinfoid="'+ userinfoid +'" data-userid="'+ userid +'" class="popup_user_info" style="box-shadow: 1px 1px 5px #999;position:absolute; margin-top:20px; border-radius:3px; border:1px solid #eee; background:#fff; padding:10px 10px; width:'+popupWidth+'px; z-index:10">';
				htmlprepend += 'Informations sur '+ name;
				htmlprepend += '</div>';
				$this.prepend(htmlprepend);
				var decalage = $(window).width() - $this.find(".popup_user_info").width() - $this.find(".popup_user_info").offset().left - 30; 
				//alert (decalage);
				if (decalage < 0){
					$this.find(".popup_user_info").css('margin-left',decalage);
				}
				//ajax
				if(sessionStorage.getItem("info_user_"+userid)){
					$this.find('.popup_user_info').html(sessionStorage.getItem("info_user_"+userid));
				}else{
					var target = $this.data('target');
					var data = {
						id : userid
					};
					$.ajax({
						type: 'POST',
						url: target,
						data: data,
						dataType : 'json',
						success: function(data){
							console.log(data.state);
							if(data.state){
								sessionStorage.setItem("info_user_"+userid, data.content);
								$this.find('.popup_user_info').html(sessionStorage.getItem("info_user_"+userid));
							}
							else{
							}
						},
						error: function(jqXHR, textStatus, errorThrown) {
							console.log(jqXHR.status);
							console.log(textStatus);
							console.log(errorThrown);
						}
					});
				}
				
			}, 650);
		}else{
			clearTimeout(timeOutIdLeaveArray[userinfoid]);
		}
	});
	
	$('body').on('mouseover','.popup_user_info',function(){
		var $this = $(this);
		var userid = $this.data("userid");
		var userinfoid = $this.data("userinfoid");
		clearTimeout(timeOutIdLeaveArray[userinfoid]);
	});
	
	$('body').on('mouseout','.user_hover_info',function(){
        var $this = $(this);
		var userid = $this.data("userid");
		var userinfoid = $this.data("userinfoid");
		clearTimeout(timeOutIdLeaveArray[userinfoid]);
		timeOutIdLeaveArray[userinfoid] = setTimeout(function(){
			$this.find(".popup_user_info").remove();
		}, 270);
		clearTimeout(timeOutIdEnterArray[userinfoid]);
	});
	
	
	//change avatar open popup
	$('body').on('click','#change_avatar',function(event){
		var target = $(this).data("target");
		
		var content = "<div style='text-align:center;padding:10px; color:#fff'>Chargement ...</div>";
		popup(content, 560, true);
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
	
	//delete avatar
	$('body').on('click','.delete_avatar',function(event){
		var $this = $(this)
		var target = $this.data("target");
		createSpinner();
		$.ajax({
			type: 'POST',
			url: target,
			dataType : 'json',
			success: function(data){
				if(data.state){
					$this.closest(".avatar_item").remove();
					if(data.isCurrentAvatar){
						$("#default_avatar").addClass("active");
						$("#avatar_banner").attr("src", data.avatar32x32);
						$("#user_avatar").attr("src", data.avatar116x116);
						$("#avatar_banner_target").attr("src", data.avatar50x50);
						
						$("#avatars_wrapper").animate({ scrollTop: 0}, 500);
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
    *select avatar
    */
    $('body').on('click','.user_avatar_popup',function(event){
        var $this = $(this);
        var target = $this.data('target');
		
		createSpinner();
        $.ajax({
            type: 'POST',
            url: target,
            dataType : 'json',
            success: function(data){
				$(".avatar_item").removeClass("active");
				$this.closest(".avatar_item").addClass("active");
				$("#avatar_banner").attr("src", data.avatar32x32);
				$("#user_avatar").attr("src", data.avatar116x116);
				$("#avatar_banner_target").attr("src", data.avatar50x50);
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
    *upload avatar for user
    */
	$('body').on('change','#avatarfile',function(event){
        var $this = $(this);
        var file = $this[0].files[0];
        var target = $this.data('target');
        var data = new FormData();
		console.log(target);
        data.append('avatar[file]', file);

        var size = this.files[0].size;
        var fileType = this.files[0].type;
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
						$(".avatar_item").removeClass("active");
						$("#avatars_wrapper").append(data.avatarItemContent);

						$("#avatar_banner").attr("src", data.avatar32x32);
						$("#user_avatar").attr("src", data.avatar116x116);
						$("#avatar_banner_target").attr("src", data.avatar50x50);

						destroySpinner();
						$("#avatars_wrapper").animate({ scrollTop: $('#avatars_wrapper').prop("scrollHeight")}, 500);
					}else{
						destroySpinner();
						alert("Une erreur est survenue");
					}
				},
				error: function(jqXHR, textStatus, errorThrown) {
					console.log(jqXHR.status);
					console.log(textStatus);
					console.log(errorThrown);
					destroySpinner();
				}
			});
        }
    });
});