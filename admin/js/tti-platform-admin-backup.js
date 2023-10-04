jQuery(document).ready( function($){
    
    jQuery('#validate_assessment').on('click', function(e) {
        e.preventDefault();
        var valid;	
        valid = validateContact();
        var organization = jQuery('#organization').val();
        var api_key = jQuery('#api_key').val();
        var account_login = jQuery('#account_login').val();
        var api_service_location = jQuery('#api_service_location').val();
        var survay_location = jQuery('#survay_location').val();
        var objAssess = [];
        if(valid) {
            jQuery.ajax({
                url : ajaxurl,
                type : 'post',
                dataType: 'json',
                data : {
                    action : 'get_assessments',
                    api_key : api_key,
                    account_login : account_login,
                    api_service_location : api_service_location,
                },
                beforeSend: function() {
                    jQuery('#loader').css('display', 'inline-block');
                },
                success : function( response ) {
                    jQuery('#loader').css('display', 'none');
                    jQuery('#status-error').css('display', 'none');
                    
                    getarray = jQuery.parseJSON(response);
                    getarrayhtml = JSON.parse(response);
                    jQuery("#assessment-result").html('<pre>'+JSON.stringify(getarrayhtml, undefined, 2)+'</pre>');
                    if(getarray.status == 'error') {
                        jQuery('#status-ok').text(getarray.message);
                        jQuery('#status-ok').css({'background': 'red', 'display': 'inline-block'});
                    } else {
                        jQuery('#status-ok').text('Success!');
                        jQuery('#status-ok').css({'background': 'green', 'display': 'inline-block'});
                        var len = getarray.length;
                        for(var i=0; i<len; i++){
                            objAssess[i] = { 
                                'account_id': getarray[i]['account_id'],
                                'disabled': getarray[i]['disabled'], 
                                'name': getarray[i]['name'],
                                'link_id': getarray[i]['login']
                            };
                        }
                        jQuery('#afterResponse').css('display', 'block');
                        jQuery('#add_assessment').css('display', 'block');
                        jQuery.each(objAssess, function (j, assessment) {
                            var status = assessment.disabled;
                            if(status === 0) {
                                jQuery('#assessment_name').append(jQuery('<option>', { 
                                    value: assessment.name,
                                    text : assessment.name,
                                    link_id : assessment.link_id,
                                    status : assessment.disabled
                                }));
                            }
                        });
                        jQuery('<input>').attr({type: 'hidden', id: 'organization_hidden', name: 'organization_hidden', value: organization }).appendTo('#afterResponse');
                        jQuery('<input>').attr({type: 'hidden', id: 'api_key_hidden', name: 'api_key_hidden', value: api_key }).appendTo('#afterResponse');
                        jQuery('<input>').attr({type: 'hidden', id: 'account_login_hidden', name: 'account_login_hidden', value: account_login }).appendTo('#afterResponse');
                        jQuery('<input>').attr({type: 'hidden', id: 'api_service_location_hidden', name: 'api_service_location_hidden', value: api_service_location }).appendTo('#afterResponse');
                        jQuery('<input>').attr({type: 'hidden', id: 'survay_location_hidden', name: 'survay_location_hidden', value: survay_location }).appendTo('#afterResponse');
                    }
                },
                complete:function(){},
                error:function (){
                    jQuery('#loader').css('display', 'none');
                    jQuery('#status-error').css('display', 'inline-block');
                    console.log('Error!');
                }
            });         
        }
    });
    function validateContact() {
        var valid = true;	
        $(".assessment-wrap input").css('border','');
        $("span.info").html('');
        if(!$("#api_key").val()) {
            $("#api-info").html("(required)");
            $("#api_key").css('border','1px solid red');
            valid = false;
        }
        if(!$("#account_login").val()) {
            $("#account-info").html("(required)");
            $("#account_login").css('border','1px solid red');
            valid = false;
        }
        if(!$("#api_service_location").val()) {
            $("#service-info").html("(required)");
            $("#api_service_location").css('border','1px solid red');
            valid = false;
        }
        if(!$("#survay_location").val()) {
            $("#survay-info").html("(required)");
            $("#survay_location").css('border','1px solid red');
            valid = false;
        }
        return valid;
    }
    jQuery('#add_assessment').on('click', function(e) {
        e.preventDefault();
        var name = jQuery('#assessment_name').val();
        var link_id = jQuery("#assessment_name option:selected").attr("link_id");
        var status_assessment = jQuery("#assessment_name option:selected").attr("status");
        var print_report = jQuery("input[name='print_report']:checked").val();
        var organization_hidden = jQuery('#organization_hidden').val();
        var api_key_hidden = jQuery('#api_key_hidden').val();
        var account_login_hidden = jQuery('#account_login_hidden').val();
        var api_service_location_hidden = jQuery('#api_service_location_hidden').val();
        var survay_location_hidden = jQuery('#survay_location_hidden').val();
        jQuery.ajax({
            url : ajaxurl,
            type : 'post',
            dataType: 'json',
            data : {
                action : 'insert_assessments',
                name : name,
                link_id : link_id,
                status_assessment : status_assessment,
                print_report : print_report,
                organization_hidden : organization_hidden,
                api_key_hidden : api_key_hidden,
                account_login_hidden : account_login_hidden,
                api_service_location_hidden : api_service_location_hidden,
                survay_location_hidden : survay_location_hidden
            },
            beforeSend: function() {
                jQuery('#loader_insert_assessment').css('display', 'inline-block');
            },
            success : function( response ) {
                console.log(response.status);
                jQuery('#loader_insert_assessment').css('display', 'none');
                jQuery('#record_inserted').html(response.message);
                if(response.status === 0) {
                    jQuery('#record_inserted').css({'display':'inline-block', 'background':'red'});
                } else if(response.status === 1) {
                    jQuery('#record_inserted').css({'display':'inline-block', 'background':'green'});
                    setTimeout(function(){
                        window.location.href = siteurl+"/wp-admin/edit.php?post_type=tti_assessments";
                    }, 2000);
                }
            },
            error:function (){
                console.log('Error!');
            }
        });
    });
}, jQuery);

(function() {
    var win;
    var shortcodeMsg;
    tinymce.PluginManager.add('mybutton', function( editor, url ) {
        editor.addButton( 'mybutton', {
            text: tinyMCE_object.button_name,
            icon: 'indent',
            text: ' TTI Shortcodes',
            onclick: function() {
                win = editor.windowManager.open({
                    title: 'Assessment Shortcodes',
                    width: 800,
                    height: 550,
                    url: tti_platform_admin_ajax_obj.siteurl+'/wp-content/plugins/tti-platform/admin/shortcode-popup.php',
                    //bodyType: 'tabpanel',
//                    body: [
//                        {
//                            title : 'Assessment',
//                            desc : 'Assessment',
//                            type : 'form',
//                            items :
//                            [
//                                {
//                                    type: 'container',
//                                    name: 'container',
//                                    //label: 'container',
//                                    html: "<h1>To have a participant take an assessment select the assessment below and click insert code.<h1>"
//                                },
//                                {
//                                    type   : 'listbox',
//                                    name   : 'assessment_list',
//                                    id     : 'assessment_list',
//                                    label  : 'Select Assessment: ',
//                                    values : get_assessments_list(),
//                                    onselect: function( ) {
//                                        if (this.value() != null) {
//                                            shortcodeMsg = '[take_assessment assess_id="' + this.value() + '" button_text="Take the Assessment Now"]';
//                                        }
//                                    }
//                                }
//                            ]
//                        },
//                        {
//                            title : 'Text Feedback',
//                            type : 'form',
//                            items : 
//                            [
//                                {
//                                    type   : 'listbox',
//                                    name   : 'assessment_list_for_feedback',
//                                    id     : 'assessment_list_for_feedback',
//                                    label  : 'Select Assessment: ',
//                                    values : get_assessments_list(),
//                                    onselect: function( ) {
//                                        if (this.value() != null) {
//                                            $('#assessment_text_feedback_id').remove();
//                                            $('<input>').attr({
//                                                type: 'hidden',
//                                                id: 'assessment_text_feedback_id',
//                                                name: 'assessment_text_feedback_id',
//                                                value: this.value()
//                                            }).appendTo($('body'));
//                                        }
//                                    },
//                                    onclick: function(){
//                                        //alert('Test');
//                                    }
//                                },
//                                {
//                                    type   : 'listbox',
//                                    name   : 'assessment_metadata',
//                                    id     : 'assessment_metadata',
//                                    label  : 'Select Feedback: ',
//                                    values : get_assessments_metadeta(),
//                                    onselect: function( ) {
//                                        if (this.value() != null) {
//                                            shortcodeMsg = '[assessment_text_feedback gen_char_intro="yes" gen_char_par="123" gen_char_feedback="feedback"]';
//                                        }
//                                    }
//                                },
//                                
//                            ]
//                        },
//                        {
//                            title : 'Graphic Feedback',
//                            type : 'form',
//                            items : 
//                            [
////                                {
////                                    type: 'textbox',
////                                    name: 'testkleur',
////                                    classes: 'ditiseentest',
////                                    label: 'Choose your color'
////                                }
//                            ]
//                        },
//                        {
//                            title : 'Custom Feedback',
//                            type : 'form',
//                            items : 
//                            [
////                                {
////                                    type: 'textbox',
////                                    name: 'testkleur',
////                                    classes: 'ditiseentest',
////                                    label: 'Choose your color'
////                                }
//                            ]
//                        }
//                    ],
                    buttons: [
                        {
                            text: 'Insert Code',
                            id: 'plugin-slug-button-insert',
                            class: 'btn',
                            onclick: function( e ) {
                                var currentTab = jQuery('#current_tab_assessment').val();
                                var assessment_id = jQuery('#assessment_id').val();
                                if(currentTab == null) {
                                    console.log('Assessment');
                                    editor.insertContent( '[take_assessment assess_id="' + assessment_id + '" button_text="Take the Assessment Now"]' );
                                    editor.windowManager.close();
                                } else if(currentTab == 'Assessment') {
                                    console.log('Assessment');
                                    editor.insertContent( '[take_assessment assess_id="' + assessment_id + '" button_text="Take the Assessment Now"]' );
                                    editor.windowManager.close();
                                } else if(currentTab == 'Text') {
                                    console.log('Text');
                                    editor.insertContent( '[assessment_text_feedback gen_char_intro="yes" gen_char_par="123" gen_char_feedback="1"]' );
                                    editor.windowManager.close();
                                } else if(currentTab == 'Graphic') {
                                    console.log('Graphic');
                                    editor.insertContent( '[assessment_text_feedback gen_char_intro="yes" gen_char_par="123" gen_char_feedback="2"]' );
                                    editor.windowManager.close();
                                } else if(currentTab == 'Custom') {
                                    console.log('Custom');
                                    editor.insertContent( '[assessment_text_feedback gen_char_intro="yes" gen_char_par="123" gen_char_feedback="3"]' );
                                    editor.windowManager.close();
                                }
                            }
                        },
                        {
                            text: 'Cancel',
                            id: 'plugin-slug-button-cancel',
                            onclick: 'close'
                        }
                    ],
                    onsubmit: function(e) {
                        var data = win.toJSON();
                        //alert(JSON.stringify(data));
                    }
                });
            }
        });
    });
})();




function get_assessments_metadeta(id) {
    var dataArr = [];
    console.log(id);
    jQuery.ajax({
        url : ajaxurl,
        type : 'post',
        dataType: 'json',
        async:false,
        data : {
            action : 'get_assessments_metadeta',
            assessment_text_feedback_id : id
        },
        beforeSend: function() {},
        success : function( response ) {
            $.each(response, function( key, value ) {
                dataArr.push({text: value.title, value: value.value});
            });
        },
        error:function (){
            console.log('Error!');
        }
    });
    return dataArr;
}


function get_assessments_metadetaa() {
    var dataArr = [];
    var assessment_text_feedback_id = jQuery('#assessment_text_feedback_id').val();
    //assessment_text_feedback_id = '153';
    if(assessment_text_feedback_id != '') {
        jQuery.ajax({
            url : ajaxurl,
            type : 'POST',
            dataType: 'json',
            data : {
                action : 'get_assessments_metadetaa',
                assessment_text_feedback_id : assessment_text_feedback_id
            },
            beforeSend: function() {},
            success : function( response ) {
            //    setTimeout(function(){
//                    $.each(response, function( key, value ) {
//                        dataArr.push({text: value.title, value: key});
//                        console.log(key);
//                    }, 2000);
                    
                //});
                get_assessments_metadeta(assessment_text_feedback_id);
                
            },
            error:function (){
                console.log('Error!');
            }
        });
        //return dataArr;
    }
}