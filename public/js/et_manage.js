
$(function() {
    //change illustration post open popup
    /*
    $('body').on('click','#change_illustration',function(event){
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
*/
    /*
    *upload illustration for post
    */
    /*
    $('body').on('change','#illustrationfile',function(event){
        var $this = $(this);
        var file = $this[0].files[0];
        var target = $this.data('target');
        var data = new FormData();
        console.log(target);
        data.append('file', file);
        //console.log(data);

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
            }
        });
    });
	*/
    $('#btn_doedit_event').on('click', function(){
        var $this = $(this);
		var bloc_editable = $this.closest(".bloc_editable");
        var target = $this.data('target');
		var data = {
			title : bloc_editable.find("#event_input_title").val(),
			slug : bloc_editable.find("#event_input_slug").val(),
			datebeginText : bloc_editable.find("#event_input_datebegin").val(),
			dateendText : bloc_editable.find("#event_input_dateend").val(),
			location : bloc_editable.find("#event_input_location").val(),
			city : bloc_editable.find("#event_input_city").val(),
		};
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
					bloc_editable.find("#event_view_datebegin").text(data.datebegin);
					bloc_editable.find("#event_view_dateend").text(data.dateend);
					bloc_editable.find("#event_view_location").text(data.location);
					bloc_editable.find("#event_view_city").text(data.city);
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

    /*
    *select post illustration
    */
    /*
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
*/
});