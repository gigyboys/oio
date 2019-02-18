
$(function() {

    //change illustration job open popup
    $('body').on('click','#change_job_illustration',function(job){
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
    *upload illustration for job
    */
    $('body').on('change','#job_illustrationfile',function(job){
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
                        $(".job_illustration_item").removeClass("active");
                        $(".images_popup_wrapper").append(data.illustrationItemContent);

                        $("#job_illustration").attr("src", data.illustration116x116);
                        $("#job_illustration"+".600x250").attr("src", data.illustration600x250);

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
    *select job illustration
    */
    $('body').on('click','.job_illustration_popup',function(job){
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
                $("#job_illustration").attr("src", data.illustration116x116);
                $("#job_illustration"+".600x250").attr("src", data.illustration600x250);
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

    
    //delete job illustration
    $('body').on('click','.delete_job_illustration',function(job){
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
                        $("#job_illustration").attr("src", data.illustration116x116);
                        $("#job_illustration"+".600x250").attr("src", data.illustration600x250);

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

    $('#form_job_edit').on('submit', function(e){
        e.preventDefault();
        var $this = $(this);
        var bloc_editable = $this.find(".bloc_editable");
        var target = $this.attr('action');
        
        var hasNotError = true;
        
        //title
        var title = $("#job_input_title").val().trim();
        if(title == ""){
            var title_error_msg = "Veuillez fournir un titre à votre offre";
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
            slug : bloc_editable.find("#job_input_slug").val(),
            sectorId : bloc_editable.find("#sectorId").val(),
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
                        bloc_editable.find("#job_view_title").text(data.title);
                        bloc_editable.find("#job_view_slug").text(data.slug);
                        bloc_editable.find("#job_view_sector").text(data.sectorName);
                        $(".jb_title_"+data.jobId).text(data.title);
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

});