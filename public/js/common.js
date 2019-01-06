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
	btn_save.show();
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
	var spinner = "<div id='spinnerloading'><div><span>Loading...</span></div></div>";
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
