(function( $ ) {
    'use strict';
    	
       /* Initialize the  completed profile datatables */
       let tti_assessment_cp_table = $('#tti_assessment_cp_table').DataTable({
        'columnDefs': [{
            'targets': 1,
            'checkboxes': {
               'selectRow': true
            }
        }],
        'pageLength': 50,
        'select': {
            'style': 'multi'
        },
         "language": {
            "lengthMenu": tti_platform_public_ajax_obj.menu_display,
            "zeroRecords": tti_platform_public_ajax_obj.zeroRecords,
            "info": tti_platform_public_ajax_obj.info,
            "infoEmpty": tti_platform_public_ajax_obj.infoEmpty,
            "infoFiltered": tti_platform_public_ajax_obj.infoFiltered,
            "search": tti_platform_public_ajax_obj.Search,
            "paginate": {
                "first" :     tti_platform_public_ajax_obj.First,
                "previous" :  tti_platform_public_ajax_obj.Previous,
                "next" :      tti_platform_public_ajax_obj.Next,
                "last" :      tti_platform_public_ajax_obj.Last
            },
        },
        responsive: true
    });

    // $('#tti_cp_download_btn').on('click', function () { 
    //     var email = $(this).data('email');
    //     var asses_id = $(this).data('assess');
    //     var href = $(this).data('href');
    //     console.log(email);
    //     console.log(asses_id);
    //     console.log(href);

    // });

   
})( jQuery );