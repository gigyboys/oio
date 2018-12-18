
$(function() {

    $('body').on('click','.list_tag .tag_item, .list_school .school_item ',function(e){
        var $this = $(this);
        $this.find(".toggle_case").trigger("click");
    });


    //tooglePublishState btn_doedit_post
    $('body').on('click','.toggle_publishState, .toggle_case',function(e){
        e.stopPropagation();
        var $this = $(this);
        var target = $this.data('target');
        $this.hide();
        $this.next(".state_loading").css("display", "inline-block");

        $.ajax({
            type: 'POST',
            url: target,
            dataType : 'json',
            success: function(data){
                if(data.state){
                    if(data.case){
                        $this.find(".greenstate").hide();
                        $this.find(".redstate").show();
                    }else{
                        $this.find(".greenstate").show();
                        $this.find(".redstate").hide();
                    }
                    $this.next(".state_loading").hide();
                    $this.show();
                    //fields case
                    var entity = $this.attr('data-entity');
                    if(entity != ""){
                        switch (entity) {
                            case 'school':
                                $(".nb_schools").html(data.schools.length);
                                $(".nb_published_schools").html(data.publishedSchools.length);
                                $(".nb_not_published_schools").html(data.notPublishedSchools.length);
                                break;
                            case 'field':
                                $(".nb_fields").html(data.fields.length);
                                $(".nb_published_fields").html(data.publishedFields.length);
                                $(".nb_not_published_fields").html(data.notPublishedFields.length);
                                break;
                            case 'contact':
                                $(".nb_contacts").html(data.contacts.length);
                                $(".nb_published_contacts").html(data.publishedContacts.length);
                                $(".nb_not_published_contacts").html(data.notPublishedContacts.length);
                                break;
                            case 'document':
                                $(".nb_docs").html(data.documents.length);
                                $(".nb_published_docs").html(data.publishedDocuments.length);
                                $(".nb_not_published_docs").html(data.notPublishedDocuments.length);
                                break;
                            case 'post':
                                $(".nb_posts").html(data.posts.length);
                                $(".nb_published_posts").html(data.publishedPosts.length);
                                $(".nb_not_published_posts").html(data.notPublishedPosts.length);
                                break;
                        }
                    }
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