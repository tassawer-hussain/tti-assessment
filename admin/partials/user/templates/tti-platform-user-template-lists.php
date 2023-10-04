<?php
/**
*   User lists assessment tab content
*/

?>
<!-- list tab content -->
<div class="wrap">    
	
    <h2><?php _e( 'Assessments List', 'tti-platform'); ?></h2>
        <div id="tti-platform-wp-list-table-demo">			
            <div id="tti-platform-post-body">		
				<form id="tti-user-list-form" method="post">
					<?php 
						$lists_obj->prepare_items(); 
						$lists_obj->search_box( __( 'Search', 'tti-platform' ), 'tti-platform');
						$lists_obj->display(); 
					?>					
				</form>
            </div>			
        </div>
</div>