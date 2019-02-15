
$(function() {
	/*
	$(".sl_related").mCustomScrollbar({
		theme:"minimal-dark",
		animationSpeed:0,
		axis:'x',
		advanced:{autoExpandHorizontalScroll:true}
	});
*/
	$('body').on('click','.tab_sl_item',function(e){
        e.preventDefault(true);
        $('.tab_sl_item').removeClass('selected');
        $(this).addClass('selected');
		var this_id = $(this).attr('id');
		$('.ctab_sl_item').removeClass('active');
		
		$("#c"+this_id).addClass('active');
		document.title = $(this).data("title");
		history.pushState('', '', $(this).attr("href"));
		initTabsl();
    });

	var row 	= 60; 		//60
    var row4 	= row*4; 	//
    var row8 	= row*8; 	//
    var row12 	= row*12; 	//
    var row16 	= row*16; 	//960
    var row20 	= row*20; 	//1200
	
    function slContent(){
        largeur_window = $(window).width();
		var sl_list = $("#sl_list");
		var sll = $(".sll");
		var sll_ct = $(".sll_ct");
		var slr = $(".slr");

        if(largeur_window<row20){
            if(largeur_window>row16){
				slr.css('display', 'block');
                widthBloc = sl_list.width()-row4;
                sll_ct.css('width', row12);
                sll.css('width', widthBloc);
                slr.css('width', row4);
                sll.css('float', 'left');
                slr.css('margin-left', widthBloc);
            }
            else{
				if(largeur_window>row12){
					slr.css('display', 'block');
					slr.css('width', row4);
					widthBloc = sl_list.width()-row4;
					sll_ct.css('width', row8);
					sll.css('width', widthBloc);
					sll.css('float', 'left');
					slr.css('margin-left', widthBloc);
				}else{
                    slr.css('display', 'none');
                    /*
                    slr.css('position', 'fixed');
                    slr.css('top', '0');
                    slr.css('right', '0');
                    slr.css('background', '#fff');
                    slr.css('border-left', '1px solid #aaa');
                    slr.css('z-index', '50');
                    slr.css('height', $(window).height());
                    slr.css('overflow', 'auto');
						*/
					if(largeur_window>row8){
						widthBloc = sl_list.width();
						sll_ct.css('width', row8);
						sll.css('width', widthBloc);
						sll.css('float', 'none');
					}
					else{
						widthBloc = sl_list.width();
						sll_ct.css('width', row4);
						sll.css('width', widthBloc);
						sll.css('float', 'none');
					}
				}
            }
        }
        else {
            sll.css('width', row16);
            sll_ct.css('width', row16);
			slr.css('display', 'block');
            slr.css('width', row4);
            sll.css('float', 'left');
			slr.css('margin-left', row16);
        }
		
    }
	
	function toggleInfoSchool(){
		var slgl = $( "#sl_gl" );
		if(slgl.length != 0){
			var slglposition = $( "#sl_gl" ).offset().top;
			var scrollTop = $( document ).scrollTop();
			if(slglposition-scrollTop+30 < 0){
				$( "#sl_view_hidden" ).show();
				$( "#navigation" ).css("border-bottom","1px solid #ddd");
				$( "#navigation" ).css("box-shadow","0px 0px 0px #fff");
			}else{
				$( "#sl_view_hidden" ).hide();
				$( "#navigation" ).css("border-bottom","1px solid #888");
				$( "#navigation" ).css("box-shadow","1px 1px 7px #999");
			}
		}
	}
	
    $(window).resize(function() {
        //slContent();
    });

    //slContent();
	toggleInfoSchool();
	$( window ).scroll(function() {
		toggleInfoSchool();
	});
	
    $(window).resize(function() {
        toggleInfoSchool();
    });
	
	/*
	* toggle school of the day description
	*/
	$('body').on('click','#sl_oftheday_title',function(){
        $('#sl_oftheday_desc').toggle();
    });
	
	/*
	* on click button evaluate
	*/
	$('body').on('click','#btn_eval_sl',function(){
        var $this = $(this);
        var target = $this.data('target');
		if($.trim($("#sl_evaluation_comment").val()) != ""){
			var data = {
				'comment' : $.trim($("#sl_evaluation_comment").val()),
                'mark' : $("#sl_evaluation_mark").val()
			};
			$("#add_evaluation_error").html("");
			//$("#add_evaluation_action .btn_save").hide();
			//$("#add_evaluation_action .btn_loading").css("display", "inline-block");
			var bloc_editable = $this.closest(".bloc_editable");
			loadBlocEdit(bloc_editable);
			$.ajax({
				type: 'POST',
				url: target,
				data: data,
				dataType : 'json',
				success: function(data){
					if(data.state == 1){
						$("#my_evaluation").html(data.myEvaluationItem);
						//$("#evaluation_empty").remove();

						if(data.passMark){
							$("#pass_mark").html(data.passMark);
							$("#pass_mark_wrap").show();
							$(".mark_empty").hide();
						}
					} else if(data.state == 3){
						resetBlocEdit(bloc_editable);
						processLogin();
					}
				},
				error: function(jqXHR, textStatus, errorThrown) {
					console.log(jqXHR.status);
					console.log(textStatus);
					console.log(errorThrown);
				}
			});	
		}else{
			var errorHtml = '<div class="warning_msg mg_v5"><i class="fas fa-exclamation-triangle"></i> Veuillez bien fournir votre évaluation</div>';
			$("#add_evaluation_error").html(errorHtml);
		}
    });
	
	/*
	* click on evaluation star
	*/
	$('body').on('click','.eval_star_item',function(){
        var $this = $(this);
		$(".eval_star_item").each(function() {
			var item = $(this);
			if(item.data("value") <= $this.data("value")){
				item.addClass("selected");
			}else{
				item.removeClass("selected");
			}
			$("#sl_evaluation_mark").val($this.data("value"));
		});
    });
	
	//toggleSubscription school
	$('body').on('click','.toggle_subscription',function(){
        var $this = $(this);
        var target = $this.data('target');
        $('.subscription_loading').show();
        $('.toggle_subscription').hide();
        $.ajax({
            type: 'POST',
            url: target,
            dataType : 'json',
            success: function(data){
				if(data.state == 1){
					if(data.active){
						$(".subscription_btn").hide();
						$(".unsubscription_btn").css('display', 'inline-block');
					}else{
						$(".subscription_btn").css('display', 'inline-block');
						$(".unsubscription_btn").hide();
					}
					$("#document_wrap").html(data.documentHtml);
				}else if(data.state == 3){

                    processLogin();
				}
                $('.subscription_loading').hide();
				$('.toggle_subscription').css('display', 'inline-block');
            },
            error: function(jqXHR, textStatus, errorThrown) {
				console.log(jqXHR.status);
				console.log(textStatus);
				console.log(errorThrown);
                $('.subscription_loading').hide();
				$('.toggle_subscription').css('display', 'inline-block');
			}
        });		
    });
	
	//tab sl about
	$('body').on('click','.t_header_item',function(e){
        $('.t_header_item').removeClass('selected');
        $(this).addClass('selected');
		var this_id = $(this).attr('id');
		$('.t_content_item').removeClass('selected');
		
		$("#c_"+this_id).addClass('selected');
		initTabsl();
    });
	initTabsl();
	$(window).resize(function() {
        initTabsl();
    });
	
	if($('#sl_map').length == 1 && typeof coords != 'undefined'){
		showSchoolMap(coords, bloc);
	}

	function showSchoolMap(coords, bloc) {
		var centerLat = 0;
		var centerLng = 0;
		var zoom = 13;
		var coordsLength = coords.length;
		
		if(coordsLength > 1){
			zoom = 5.6;
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
		
		initMap(map, typeId = 0);

		function setMapOnMarker(map, coord, markers, index) {
			var myLatLng = new google.maps.LatLng(coord.latitude,coord.longitude);
			markers[index] = new google.maps.Marker({
				position: myLatLng,
				title: coord.label,
				url: coord.url,
				icon: {
					url:coord.icon
				}
			});
			//markers[i].setIcon(coord.icon);
			markers[index].setMap(map);
			markers[index].addListener('click', function(event) {
				window.location.href = this.url;
			});
		}

		function initMap(map, typeId = 0) {
			for (var i = 0; i < markers.length; i++) {
				markers[i].setMap(null);
			}
			markers = [];
			var countMarker = 0;
			for(var i = 0; i < coordsLength; i++ ){
				var coord = coords[i];
				if(typeId == 0){
					setMapOnMarker(map, coord, markers, countMarker);
					countMarker++;
				}else{
					if(coord.typeId == typeId){
						setMapOnMarker(map, coord, markers, countMarker);
						countMarker++;
					}
				}
			}
		}

		$('body').on('click','.link_in_map',function(e){
			e.preventDefault(true);
			$(".link_in_map").removeClass('active');
			$(this).addClass('active');
			var typeId = $(this).attr('data-type-id');
			initMap(map, typeId);
		});
	}
		
	function showMapContact(coords, bloc) {
		var centerLat = 0;
		var centerLng = 0;
		var zoom = 13;
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
	
	//show contact with coord gps on map
	$('body').on('click','#btn_show_map_contacts, .btn_show_map',function(event){
		var target = $(this).data("target");
		
		var content = "<div style='text-align:center;padding:10px; color:#fff'>Chargement ...</div>";
		popup(content, 650, true);
		
		$.ajax({
			type: 'POST',
			url: target,
			dataType : 'json',
			success: function(data){
				if(data.state){
					var content = "";
					content += '<div style="padding:10px; width:auto; background:#fff; border-radius:3px">';
						content += '<div id="map_contact" style="height:360px">';
						content += '</div>';
					content += '</div>';
					$(".popup").html(content);
					showMapContact(data.coords, "map_contact");
					centerBloc($('.popup_content'), $('.popup'));
				}else{
					
				}
			},
			error: function(jqXHR, textStatus, errorThrown) {
			}
		});
		
    });

	/*
	* check status of the document
	 */
	$('body').on('click','.btn_download',function(event){
        var $this = $(this);
        var target = $this.data("target");

        setTimeout(function()
		{
            $.ajax({
                type: 'POST',
                url: target,
                dataType : 'json',
                success: function(data){
                    if(data.state){
						var countDownloads = "téléchargements";
						if(parseInt(data.countDownloads) > 1){
							countDownloads = data.countDownloads+" téléchargements";
						} else {
							countDownloads = data.countDownloads+" téléchargement";
						}
						$this.closest(".document_item").find(".download_count").text(countDownloads);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                }
            });

		}, 1000);
    });
	
	//eviter la troisieme ligne
	truncateSchoolLabel();
});

//eviter la troisieme ligne
function truncateSchoolLabel() {
	$( ".sl_item" ).each(function() {
		var slitem = $( this );
		var slitemlabel = slitem.find(".sl_label");
		var slitemlabellien = slitem.find(".sl_label a");

		var textin = "";
		while (slitemlabel.height() < slitemlabellien.height() && slitem.find(".sl_label a .sl_name").text().length > 30) {
			textin = slitem.find(".sl_label a .sl_name").text();
			var n = textin.lastIndexOf(" ");
			textin = textin.substring(0,n);
			slitem.find(".sl_label a .sl_name").text(textin);
		}
	});
}

function initTabsl(){
	$('.t_header').each(function( index ) {
		var t_header = $(this);
		var selected = t_header.find('.t_header_item.selected');
		if(selected.length > 0){
			var decalage = selected.offset().left - t_header.find('.t_header_content').offset().left;
			t_header.find('.t_header_sep_slide').animate({
				marginLeft: decalage,
				width: selected.outerWidth(true),
			}, 0, function() {});
		} 
	});
}