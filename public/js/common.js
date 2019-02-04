$(function() {

    /*
    * on taping on search input
    */
    $('.a_list_search_input').on('keyup', function(ed){
        var $this = $(this);
        var query = $this.val();
        var targetId = $this.attr("data-target-id");
        var a_table_line = $('#'+targetId+' .a_table_line');

        var countResult = 0;
        a_table_line.each(function( index ) {
            var text = $( this ).attr("data-text");
            text = text.toLowerCase();
            text = text.replace(/[èéêë]/g,"e");
            text = text.replace(/[àâä]/g,"a");
            text = text.replace(/[ûüù]/g,"u");
            text = text.replace(/[îï]/g,"i");
            text = text.replace(/[ôö]/g,"o");

            query = query.toLowerCase();
            query = query.replace(/[èéêë]/g,"e");
            query = query.replace(/[àâä]/g,"a");
            query = query.replace(/[ûüù]/g,"u");
            query = query.replace(/[îï]/g,"i");
            query = query.replace(/[ôö]/g,"o");

            if(text.indexOf(query) > -1){
                $( this ).css("display", "table-row");
                countResult++;
            }else{
                $( this ).css("display", "none");
            }
        });
        $('.result_state').html(countResult+" resultat(s)");
    });

    /*
    * on reset search
    */
    $('.a_list_search_reset').on('click', function(ed){
        $('.a_list_search_input').val("");
        $('.a_list_search_input').focus();
        $( ".a_list_search_input" ).trigger( "keyup" );
    });

    $('body').on('click','.login_ajax',function(e){
        e.preventDefault(true);
        var target = $(this).attr("href");

        var content = "<div style='text-align:center;padding:10px; color:#fff'>Chargement ...</div>";
        popup(content, 370, true);
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

    $('body').on('submit','#form_login_ajax',function(e){
        e.preventDefault(true);
        let $this = $(this);

        console.log('form_login_ajax');
        let target = $this.attr('action');
        console.log(target);

        let datafull = {
            username: $("#username").val(),
            password: $("#password").val()
        };

        let data = JSON.stringify(datafull);

        $("#login_msg").html('');

        $("#username, #password").prop('disabled', true);
        $(".loading_wrap").show();
        $(".btn_wrap").hide();
        $.ajax({
            type: 'POST',
            contentType: "application/json",
            url: target,
            data: data,
            dataType : 'json',
            success: function(data){
                console.log(data);
                var status = false;

                if(data.hasOwnProperty('state')){
                    if(data.state == 1){
                        status = true;
                    }
                }

                if(status){
                    location.reload();
                }else{
                    $("#login_msg").html('<span class="error_msg">Veuillez bien verifier votre email et mot de passe.</span>');
                }
                $("#username, #password").prop('disabled', false);
                $(".loading_wrap").hide();
                $(".btn_wrap").show();
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.log(jqXHR.status);
                console.log(textStatus);
                console.log(errorThrown);
                $("#login_msg").html('<span class="error_msg">Veuillez bien verifier votre email et mot de passe.</span>');
                $("#username, #password").prop('disabled', false);
                $(".loading_wrap").hide();
                $(".btn_wrap").show();
            }
        });
    });
    
	
	/*
	* Navigation pagination ajax
	*/
	$('body').on('click','a.pagination_item, .sl_type_item a, .et_type_item a, .et_tag',function(e){
        e.preventDefault(true);
		var $this = $(this);
        var target = $this.attr("href");
        var p_cr = $this.closest(".p_cr");
        var p_type = p_cr.attr("data-type");
		var p_cr_height = p_cr.height();
		var p_cr_width = p_cr.width();
		p_cr.find(".p_load").css("height",p_cr_height);
		p_cr.find(".p_load").css("width",p_cr_width);
		p_cr.find(".p_load").css("display","block");
		p_cr.find(".p_load").css("padding-top",(p_cr_height/2) - 40);
		
        $.ajax({
            type: 'POST',
            url: target,
            dataType : 'json',
            success: function(data){
				if(data.state){
                    p_cr.find(".p_load").css("display","none");
                    switch (p_type) {
                        case 'school':
                            var htmlappend = '';
                            for(var i = 0; i <data.schools.length; i++ ){
                                var school = data.schools[i];
                                htmlappend += school.school_view;
                            }
                            htmlappend += '<div class="both"></div>';
                            p_cr.find(".p_list").html(htmlappend);
                            p_cr.find(".pagination").html(data.pagination);
                            p_cr.find(".sl_type").html(data.typeLinks);
                            history.pushState('', 'School - page '+data.page, data.currentUrl);
                            truncateSchoolLabel();
                            break;
                        case 'event':
                            var htmlappend = '';
                            if(data.events.length > 0){
                                for(var i = 0; i <data.events.length; i++ ){
                                    var event = data.events[i];
                                    htmlappend += event.event_view;
                                }
                                htmlappend += '<div class="both"></div>';
                            }else{
                                htmlappend += '<div class="mg_v20 tacenter" style="width:100%"><strong>Aucun évènement à afficher</strong></div>';
                            }
                            $(".p_list").html(htmlappend);
                            $(".pagination").html(data.pagination);
                            $(".et_type").html(data.typeLinks);
                            history.pushState('', 'Evènements - page '+data.page, data.currentUrl);
                            break;
                        case 'post_search':
                            var htmlappend = '';
                            for(var i = 0; i <data.posts.length; i++ ){
                                var post = data.posts[i];
                                htmlappend += post.post_view;
                            }
                            htmlappend += '<div class="both"></div>';
                            $(".p_list").html(htmlappend);
                            $(".pagination").html(data.pagination);
                            history.pushState('', 'Articles - page '+data.page, data.currentUrl);
                            break;
                    }
					var p_stop = p_cr.find('.p_stop').first();
					$('html, body').stop().animate({scrollTop: - 50 + p_stop.offset().top}, 500);
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

    
    $('.search_input').val("");
    /*
    * on taping on search input
    */
    $('body').on('keyup','.search_input',function(event){
        var $this = $(this);
        var contextSearch = $this.closest(".search");
        var query = $this.val();

        var countResult = 0;
        contextSearch.find(".search_item").each(function( index ) {
            $( this ).hide();
            console.log($( this ).attr("data-text"));
            var text = $( this ).attr("data-text");
            text = text.toLowerCase();
            text = text.replace(/[èéêë]/g,"e");
            text = text.replace(/[àâä]/g,"a");
            text = text.replace(/[ûüù]/g,"u");
            text = text.replace(/[îï]/g,"i");
            text = text.replace(/[ôö]/g,"o");

            query = $.trim(query);
            query = query.toLowerCase();
            query = query.replace(/[èéêë]/g,"e");
            query = query.replace(/[àâä]/g,"a");
            query = query.replace(/[ûüù]/g,"u");
            query = query.replace(/[îï]/g,"i");
            query = query.replace(/[ôö]/g,"o");

            console.log(query);
            if(text.indexOf(query) > -1){
                var dataItemDisplay = contextSearch.attr('data-item-display');

                if (typeof dataItemDisplay !== typeof undefined && dataItemDisplay !== false) {
                    $( this ).css("display", dataItemDisplay);
                }else{
                    $( this ).css("display", "block");
                }

                countResult++;
            }else{
                $( this ).css("display", "none");
            }
        });
        contextSearch.find('.result_state').html(countResult+" résultat"+(countResult > 1 ? "s" : ""));
    });

    /*
    * on reset search
    */
    $('.search_reset').on('click', function(e){
        var $this = $(this);
        var contextSearch = $this.closest(".search");
        contextSearch.find('.search_input').val("");
        contextSearch.find('.search_input').focus();
        contextSearch.find( ".search_input" ).trigger( "keyup" );
    });

});

function processLogin(){
    $('.popup_content').remove();
    var target = "/login-ajax";

    var content = "<div style='text-align:center;padding:10px; color:#fff'>Chargement ...</div>";
    popup(content, 370, true);
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
}

function resetBlocEdit(bloc_editable){
	var bloc_view = bloc_editable.find(".bloc_view");
	var bloc_edit = bloc_editable.find(".bloc_edit");
	var btn_edit = bloc_editable.find(".btn_edit");
	var btn_save = bloc_editable.find(".btn_save");
	var btn_reset = bloc_editable.find(".btn_reset");
	var btn_loading = bloc_editable.find(".btn_loading");
	bloc_view.show();
	bloc_edit.hide();
	btn_edit.show();
	btn_save.hide();
	btn_reset.hide();
	btn_loading.hide();
}

function editBlocEdit(bloc_editable){
	var bloc_view = bloc_editable.find(".bloc_view");
	var bloc_edit = bloc_editable.find(".bloc_edit");
	var btn_edit = bloc_editable.find(".btn_edit");
	var btn_save = bloc_editable.find(".btn_save");
	var btn_reset = bloc_editable.find(".btn_reset");
	var btn_loading = bloc_editable.find(".btn_loading");
	bloc_editable.find("input, select, textarea").prop('disabled', false);
	bloc_view.hide();
	bloc_edit.show();
	btn_edit.hide();
	btn_save.css("display", "inline-block");
	btn_reset.show();
	btn_loading.hide();
}

function loadBlocEdit(bloc_editable){
	var bloc_view = bloc_editable.find(".bloc_view");
	var bloc_edit = bloc_editable.find(".bloc_edit");
	var btn_edit = bloc_editable.find(".btn_edit");
	var btn_save = bloc_editable.find(".btn_save");
	var btn_reset = bloc_editable.find(".btn_reset");
	var btn_loading = bloc_editable.find(".btn_loading");
	bloc_editable.find("input, select, textarea").prop('disabled', true);
	bloc_view.hide();
	bloc_edit.show();
	btn_edit.hide();
	btn_save.hide();
	btn_reset.hide();
	btn_loading.show();
}

$(function() {
	$('body').on('click','.btn_edit',function(event){
        var $this = $(this);
        var bloc_editable = $this.closest(".bloc_editable");
		editBlocEdit(bloc_editable);
    });
    
	$('body').on('click','.btn_reset',function(event){
        var $this = $(this);
        var bloc_editable = $this.closest(".bloc_editable");
		resetBlocEdit(bloc_editable);
    });
});

$(function() {
    $(window).resize(function() {
        initSpinner();
    });
});

function createSpinner() {
	var spinner = "<div id='spinnerloading'><div><span>Chargement...</span></div></div>";
	$( "body" ).prepend( spinner );
	initSpinner();
}

function initSpinner() {
	$("#spinnerloading").css('width',$(window).width());
	$("#spinnerloading").css('height',$(window).height());
	$("#spinnerloading div").css('margin-top',($(window).height() - $("#spinnerloading div").height())/2);
}

function destroySpinner() {
	$( "#spinnerloading" ).remove();
}

/*, format = 'dd/mm/YYYY HH:ii'*/
function isValidDate(value) {
    // capture all the parts
    var matches = value.match(/^(\d{2})\/(\d{2})\/(\d{4}) (\d{2}):(\d{2})$/);
    //var matches = value.match(/^(\d{2})\.(\d{2})\.(\d{4}) (\d{2}):(\d{2}):(\d{2})$/);
    if (matches === null) {
        return false;
    } else{
        // now lets check the date sanity
        var year = parseInt(matches[3], 10);
        var month = parseInt(matches[2], 10) - 1; // months are 0-11
        var day = parseInt(matches[1], 10);
        var hour = parseInt(matches[4], 10);
        var minute = parseInt(matches[5], 10);
        var second = parseInt('00', 10);
        var date = new Date(year, month, day, hour, minute, second);
        if (date.getFullYear() !== year
          || date.getMonth() != month
          || date.getDate() !== day
          || date.getHours() !== hour
          || date.getMinutes() !== minute
          || date.getSeconds() !== second
        ) {
           return false;
        } else {
           return true;
        }
    
    }
}
