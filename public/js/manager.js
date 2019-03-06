
$(function() {

    $('body').on('click','.list_tag .tag_item, .list_school .school_item ',function(e){
        var $this = $(this);
        $this.find(".toggle_case").trigger("click");
    });

    if($("#datebegin_event").length > 0 && $("#dateend_event").length > 0){
        var startDate = $('#datebegin_event');
        var endDate = $('#dateend_event');

        $.timepicker.datetimeRange(
            startDate,
            endDate,
            {
                minInterval: (1000*60*60), // 1hr
                dateFormat: 'dd/mm/yy',
                timeFormat: 'HH:mm',
                start: {}, // start picker options
                end: {} // end picker options
            }
        );
    }

    if($("#event_input_datebegin").length > 0 && $("#event_input_dateend").length > 0){
        var startDate = $('#event_input_datebegin');
        var endDate = $('#event_input_dateend');

        $.timepicker.datetimeRange(
            startDate,
            endDate,
            {
                minInterval: (1000*60*60), // 1hr
                dateFormat: 'dd/mm/yy',
                timeFormat: 'HH:mm',
                start: {}, // start picker options
                end: {} // end picker options
            }
        );
    }

    //togglePublishState
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
                            case 'option':
                                $(".nb_options").html(data.options.length);
                                $(".nb_published_options").html(data.publishedOptions.length);
                                $(".nb_not_published_options").html(data.notPublishedOptions.length);
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
                            case 'event':
                                $(".nb_events").html(data.events.length);
                                $(".nb_published_events").html(data.publishedEvents.length);
                                $(".nb_not_published_events").html(data.notPublishedEvents.length);
                                break;
                            case 'userTeam':
                                $(".nb_userteams").html(data.userTeams.length);
                                $(".nb_published_userteams").html(data.publishedUserTeams.length);
                                $(".nb_not_published_userteams").html(data.notPublishedUserTeams.length);
                                break;
                            case 'job':
                                $(".nb_jobs").html(data.jobs.length);
                                $(".nb_published_jobs").html(data.publishedJobs.length);
                                $(".nb_not_published_jobs").html(data.notPublishedJobs.length);
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