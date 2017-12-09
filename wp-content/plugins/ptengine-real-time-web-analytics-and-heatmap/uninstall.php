<?php
if( !defined( 'WP_UNINSTALL_PLUGIN' ) ) exit();

delete_option( 'key_ptengine_badge_visible' );
delete_option( 'key_ptengine_tag_position' );

delete_option( 'key_ptengine_account' );
delete_option( 'key_ptengine_pwd' );
delete_option( 'key_ptengine_uid' );

delete_option( 'key_ptengine_sid' );
delete_option( 'key_ptengine_site_id' );
delete_option( 'key_ptengine_pgid' );
delete_option( 'key_ptengine_site_name' );
delete_option( 'key_ptengine_timezone' );
delete_option( 'key_ptengine_code' );

delete_option( 'key_ptengine_area' );
delete_option( 'key_ptengine_dc_init' );

delete_option( 'key_ptengine_nonce_id' );

?>
