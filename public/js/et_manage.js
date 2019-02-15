
$(function() {
    

	// map : getting coord event
    $('.get-coords-event').on('click', function(){
        var content = '<div style="padding:10px; width:auto; background:#fff;"><div id="map" style="height: 400px"></div> </div>';
        popup(content, 600, true);

        var myLatlng = {lat: -18.90329215475846, lng: 47.5195606651306};
        if($('#event_input_latitude').val().trim() != "" && $('#event_input_longitude').val().trim() != ""){
            myLatlng = {lat: Number($('#event_input_latitude').val().trim()), lng: Number($('#event_input_longitude').val().trim())};
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
            setCoordonneesEvent(latitude, longitude);
        });

        marker.addListener('dragend', function(event) {
            var latitude = event.latLng.lat();
            var longitude = event.latLng.lng();
            setCoordonneesEvent(latitude, longitude);
        });
    });


    function setCoordonneesEvent(lat, lng) {
		$('#event_input_latitude').val(lat);
		$('#event_input_longitude').val(lng);
    }

    //change illustration event open popup
    $('body').on('click','#change_event_illustration',function(event){
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

    /*
    *upload illustration for event
    */
    
    $('body').on('change','#event_illustrationfile',function(event){
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
                        $(".event_illustration_item").removeClass("active");
                        $(".images_popup_wrapper").append(data.illustrationItemContent);

                        $("#event_illustration").attr("src", data.illustration116x116);
                        $("#event_illustration"+".600x250").attr("src", data.illustration600x250);

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

    /*
    *select event illustration
    */
    $('body').on('click','.event_illustration_popup',function(event){
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
                $("#event_illustration").attr("src", data.illustration116x116);
                $("#event_illustration"+".600x250").attr("src", data.illustration600x250);
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

    
    //delete event illustration
    $('body').on('click','.delete_event_illustration',function(event){
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
                        $("#event_illustration").attr("src", data.illustration116x116);
                        $("#event_illustration"+".600x250").attr("src", data.illustration600x250);

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

    $('#form_event_edit').on('submit', function(e){
        e.preventDefault();
        var $this = $(this);
        var bloc_editable = $this.find(".bloc_editable");
        var target = $this.attr('action');
        
        var hasNotError = true;
        
        //title
        var title = $("#event_input_title").val().trim();
        if(title == ""){
            var title_error_msg = "Veuillez fournir un titre à votre évènement";
            if($('.error_title').length > 0){
                $('.error_title').show().html(title_error_msg);
            }
            hasNotError = false;
        }else{
            if($('.error_title').length > 0){
                $('.error_title').hide().html("");
            }
        }

        
        var data = {
            title : title,
            slug : bloc_editable.find("#event_input_slug").val(),
        };
        if(hasNotError){
            loadBlocEdit(bloc_editable);
            $.ajax({
                type: 'POST',
                url: target,
                data: data,
                dataType : 'json',
                success: function(data){
                    if(data.state){
                        bloc_editable.find("#event_view_title").text(data.title);
                        bloc_editable.find("#event_view_slug").text(data.slug);
                        $(".et_title_"+data.eventId).text(data.title);
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
    
    /*
    * edit datebegin dateend
     */
    $('#form_event_edit_date').on('submit', function(e){
        e.preventDefault();
        var $this = $(this);
        var bloc_editable = $this.find(".bloc_editable");
        var target = $this.attr('action');
        
        var hasNotError = true;

        //datebegin
        var datebeginText = $("#event_input_datebegin").val().trim();
        if(!isValidDate(datebeginText)){
            var datebegin_error_msg = 'La date et heure debut doit être de la forme "dd/mm/yyyy hh:mm"';
            if($('.error_datebegin').length > 0){
                $('.error_datebegin').show().html(datebegin_error_msg);
            }
            hasNotError = false;
        }else{
            if($('.error_datebegin').length > 0){
                $('.error_datebegin').hide().html("");
            }
        }

        //dateend
        var dateendText = $("#event_input_dateend").val().trim();
        if(!isValidDate(dateendText)){
            var dateend_error_msg = 'La date et heure fin doit être de la forme "dd/mm/yyyy hh:mm"';
            if($('.error_dateend').length > 0){
                $('.error_dateend').show().html(dateend_error_msg);
            }
            hasNotError = false;
        }else{
            if($('.error_dateend').length > 0){
                $('.error_dateend').hide().html("");
            }
        }
        
        var data = {
            datebeginText : datebeginText,
            dateendText : dateendText,
        };
        if(hasNotError){
            loadBlocEdit(bloc_editable);
            $.ajax({
                type: 'POST',
                url: target,
                data: data,
                dataType : 'json',
                success: function(data){
                    if(data.state){
                        bloc_editable.find("#event_view_datebegin").text(data.datebegin);
                        bloc_editable.find("#event_view_dateend").text(data.dateend);
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
    
    /*
    * edit location
    */
    $('#form_event_edit_location').on('submit', function(e){
        e.preventDefault();
        var $this = $(this);
        var bloc_editable = $this.find(".bloc_editable");
        var target = $this.attr('action');
        
        var hasNotError = true;
        
        //location
        var location = $("#event_input_location").val().trim();
        /*
        if(location == ""){
            var location_error_msg = "Veuillez fournir Le lieu ou se déroule l'évènement";
            if($('.error_location').length > 0){
                $('.error_location').show().html(location_error_msg);
            }
            hasNotError = false;
        }else{
            if($('.error_location').length > 0){
                $('.error_location').hide().html("");
            }
        }
        */

        var data = {
            location : bloc_editable.find("#event_input_location").val(),
            city : bloc_editable.find("#event_input_city").val(),
            latitude : bloc_editable.find("#event_input_latitude").val(),
            longitude : bloc_editable.find("#event_input_longitude").val(),
        };
        if(hasNotError){
            loadBlocEdit(bloc_editable);
            $.ajax({
                type: 'POST',
                url: target,
                data: data,
                dataType : 'json',
                success: function(data){
                    if(data.state){
                        bloc_editable.find("#event_view_location").text(data.location);
                        bloc_editable.find("#event_view_city").text(data.city);
                        bloc_editable.find("#event_view_latitude").text(data.latitude);
                        bloc_editable.find("#event_view_longitude").text(data.longitude);
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

    $('.btn_doedit_content_event').on('click', function(){
        var $this = $(this);
        var bloc_editable = $this.closest(".bloc_editable");
        var target = $this.data('target');
        var data = {
            introduction : bloc_editable.find(".event_input_introduction").val(),
            content : CKEDITOR.instances['event_input_content'].getData()
        };
        loadBlocEdit(bloc_editable);
        $.ajax({
            type: 'POST',
            url: target,
            data: data,
            dataType : 'json',
            success: function(data){
                if(data.state){
                    bloc_editable.find(".event_view_introduction").html(data.introduction);
                    bloc_editable.find(".event_view_content").html(data.content);
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