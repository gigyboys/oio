$(function() {
	var timeOutId = 0;
	
	function getSingleSchoolResult(){
        var target = $("#q_sl").data('target');

		var data = {
			entity : "school",
			q : $("#q_sl").val(),
			category : $("#cat_sl_input_id").attr("data-slug"),
			type : $("#type_sl_input_id").attr("data-slug")
		};
		
        $.ajax({
            type: 'GET',
            url: target,
            data: data,
            dataType : 'json',
            success: function(data){
				if(data.state){
					$("#search_single_result_sl").html(data.school_view);
				}
				else{
					console.log("une erreur est survenue");
				}
            },
            error: function(jqXHR, textStatus, errorThrown) {
				console.log(jqXHR.status);
				console.log(textStatus);
				console.log(errorThrown);
			}
        });
	}
	
	$('body').on('click','.search_entity_item',function(e){
        $('.search_entity_item').removeClass('selected');
        $(this).addClass('selected');
		
		$('.search_entity_content').css('display','none');
		var content = $("#search_critere_"+$(this).attr("data-entity"));
		content.css('display','block');
		
		$('.search_single_result').css('display','none');
		var content_single = $("#search_single_result_"+$(this).attr("data-entity"));
		content_single.css('display','block');
		
		//new comportement
		if($(this).attr("data-mixed") == "1"){
			$('.search_entity_item.mixed').addClass('selected');
			$('.search_entity_item.mixed').html($(this).html());
			$('.search_entity_item.mixed').attr("data-entity", $(this).attr("data-entity"));
			$('.other_search').addClass('selected');
		}else{
			$('.other_search').removeClass('selected');
		}
		$('.ddt').hide();
    });
	
	$('#form_search').submit( function(e){
		e.preventDefault();
		$('#btn_search').trigger('click');
	});
	
	//school
	$( ".search_cat_sl_item" ).click(function() {
		$("#search_sl_cat_name").html($(this).attr("data-name"));
		$("#cat_sl_input_id").attr("data-slug",$(this).attr("data-slug"));
		$("#cat_sl_input_id").val($(this).attr("data-category-id"));
		$(".ddt").hide();
		getSingleSchoolResult();
	});
	
	$( ".search_type_sl_item" ).click(function() {
		$("#search_sl_type_name").html($(this).attr("data-name"));
		$("#type_sl_input_id").attr("data-slug",$(this).attr("data-slug"));
		$("#type_sl_input_id").val($(this).attr("data-type-id"));
		$(".ddt").hide();
		getSingleSchoolResult();
	});
	
	//event
	$( ".search_tag_et_item" ).click(function() {
		$("#search_et_tag_name").html($(this).attr("data-name"));
		$("#tag_et_input_id").attr("data-slug",$(this).attr("data-slug"));
		$("#tag_et_input_id").val($(this).attr("data-event-tag-id"));
		$(".ddt").hide();
	});
	
	$( ".search_et_period_item" ).click(function() {
		$("#search_et_period_name").html($(this).attr("data-name"));
		$("#period_input_id").attr("data-slug",$(this).attr("data-slug"));
		$("#period_input_id").val($(this).attr("data-slug"));
		$(".ddt").hide();
	});

	$('#q_sl').on('input', function() {
		clearTimeout(timeOutId);
		timeOutId = setTimeout(function(){ getSingleSchoolResult(); }, 700);
	});
	
	$( "#btn_search" ).click(function() {
		var target = $(this).data("target");
		var critere = "";
		var entity = $(".search_entity_item.visible.selected").attr("data-entity");
		var q = "";
		q = $("#q_"+entity).val();
		q = $.trim(q);
		if(q == ""){
			//alert("Ne pas laisser le critère de recherche à vide.");
			var content = '';
			content += '<div class="pd_5 b_w">';
			content += '	<div class="ta_c pd_10">';
			content += '		<div class="mg_b10 error_msg">Ne pas laisser le critère de recherche à vide.</div>';
			content += '		<div class="mg_t10"><span class="button_closable standar_button">OK</span></div>';
			content += '	</div>';
			content += '</div>';
			popup(content, 400, true);
		}else{
			switch (entity) {
				case "sl":
					critere = "entity=school";
					break;
				case "bg":
					critere = "entity=post";
					break;
				case "et":
					critere = "entity=event";
					break;
			}
			q = encodeURIComponent(q.trim());
			var q2 = q.replace(/\s/g,"%20");
			critere = critere+"&q="+q2;
			
			switch (entity) {
				case "sl":
					critere = critere+"&category="+$("#cat_sl_input_id").attr("data-slug");
					critere = critere+"&type="+$("#type_sl_input_id").attr("data-slug");
					break;
				case "et":
					critere = critere+"&tag="+$("#tag_et_input_id").attr("data-slug");
					critere = critere+"&period="+$("#period_input_id").attr("data-slug");
					break;
			}
			window.location=target+"?"+critere;
		}
	});
});

