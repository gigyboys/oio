
$(function() {
    //delete job
    $('body').on('click','.delete_job',function(e){
        var id = $(this).data("id");
        var title = $(this).data("title");
        var target = $(this).data("target");

        var content = "";
        content += '<div style="padding:10px; width:auto; background:#fff; border-radius:3px">';
        content += '<div style="text-align:center;padding:10px 0"> Voulez vous effectuer la suppression de l\'offre "<strong>'+title+'</strong>"?</div>';
        content += '<div style="text-align:center">	';
        content += '<span class="button_closable" style="background:#bbb; border-radius: 3px; cursor:pointer; display:inline-block; margin:auto; padding:5px;margin:5px">	Annuler	</span>';
        content += '<span class="confirm_delete_job button_closable" data-entity="job" data-id="'+id+'" data-target="'+target+'" style="background:#bbb; border-radius: 3px; cursor:pointer; display:inline-block; margin:auto; padding:5px;margin:5px">	Confirmer	</span>	';
        content += '</div>';
        content += '</div>';

        popup(content, 500, true);
    });

    //confirm delete field
    $('body').on('click','.confirm_delete_job',function(e){
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
                        case 'job':
                            $( "#job_"+data.id ).remove();
                            $(".nb_jobs").html(data.jobs.length);
                            $(".nb_published_jobs").html(data.publishedJobs.length);
                            $(".nb_not_published_jobs").html(data.notPublishedJobs.length);
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