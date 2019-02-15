
$(function() {
    //delete event
    $('body').on('click','.delete_event',function(e){
        var id = $(this).data("id");
        var title = $(this).data("title");
        var target = $(this).data("target");

        var content = "";
        content += '<div style="padding:10px; width:auto; background:#fff; border-radius:3px">';
        content += '<div style="text-align:center;padding:10px 0"> Voulez vous effectuer la suppression de l\'évènement "<strong>'+title+'</strong>"?</div>';
        content += '<div style="text-align:center">	';
        content += '<span class="button_closable" style="background:#bbb; border-radius: 3px; cursor:pointer; display:inline-block; margin:auto; padding:5px;margin:5px">	Annuler	</span>';
        content += '<span class="confirm_delete_event button_closable" data-entity="event" data-id="'+id+'" data-target="'+target+'" style="background:#bbb; border-radius: 3px; cursor:pointer; display:inline-block; margin:auto; padding:5px;margin:5px">	Confirmer	</span>	';
        content += '</div>';
        content += '</div>';

        popup(content, 500, true);
    });

    //confirm delete field
    $('body').on('click','.confirm_delete_event',function(e){
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
                        case 'event':
                            $( "#event_"+data.id ).remove();
                            $(".nb_events").html(data.events.length);
                            $(".nb_published_events").html(data.publishedEvents.length);
                            $(".nb_not_published_events").html(data.notPublishedEvents.length);
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
});