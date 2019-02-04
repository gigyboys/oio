$(function() {
	var timeOutId = 0;
	
	var search_entity_item = $('.search_entity_item');
	var search_entity_content = $('.search_entity_content');
	var search_single_result = $('.search_single_result');
	
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
		
		search_entity_content.css('display','none');
		var content = $("#search_critere_"+$(this).data("entity"));
		content.css('display','block');
		
		search_single_result.css('display','none');
		var content_single = $("#search_single_result_"+$(this).data("entity"));
		content_single.css('display','block');
		
    });
	
	$('#form_search').submit( function(e){
		e.preventDefault();
		$('#btn_search').trigger('click');
	});
	
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
	
	$('#q_sl').on('input', function() {
		clearTimeout(timeOutId);
		timeOutId = setTimeout(function(){ getSingleSchoolResult(); }, 700);
	});
	
	$( "#btn_search" ).click(function() {
		var target = $(this).data("target");
		var critere = "";
		var entity = $(".search_entity_item.selected").data("entity");
		var q = "";
		q = $("#q_"+entity).val();
		q = $.trim(q);
		if(q == ""){
			alert("Ne pas laisser le critère de recherche à vide.");
		}else{
			switch (entity) {
				case "sl":
					critere = "entity=school";
					break;
				case "bg":
					critere = "entity=post";
					break;
				case "at":
					critere = "entity=advert";
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
			}
			window.location=target+"?"+critere;
		}
	});
});

