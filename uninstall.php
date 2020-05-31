<?php

	
        // if uninstall.php is not called by WordPress, die
        if (!defined('WP_UNINSTALL_PLUGIN')) {
            die;
        }

        global $wpdb;
        $UD_table_role_directions = $wpdb->prefix . 'ud_role_directions';
        $UD_table_settings = $wpdb->prefix . 'ud_settings';
        
        $UD_get_settings_plugin_data = $wpdb->get_row( "SELECT * FROM $UD_table_settings where UD_meta_key='UD_delete_plugin_data'" );

        if( $UD_get_settings_plugin_data->UD_meta_key == "UD_delete_plugin_data" && $UD_get_settings_plugin_data->UD_meta_value == "true" ){
            
            $sql1 = "DROP TABLE IF EXISTS $UD_table_role_directions";
            $wpdb->query($sql1);

            $sql2 = "DROP TABLE IF EXISTS $UD_table_settings";
            $wpdb->query($sql2);
            
        }