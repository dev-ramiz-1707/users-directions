<?php
/**
 * @package  users-directions
 */
/*
Plugin Name: User's directions   
Plugin URI: https://github.com/dev-ramiz-1707/users-directions
Description:This plugin will help redirect the user to a page based on the thair role.
Version: 1.0.0
Author: Ramiz Theba
Author URI: https://github.com/dev-ramiz-1707
License: GPLv2 or later
Text Domain: user-directions
*/
/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.
This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.
You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
Copyright 2005-2015 Automattic, Inc.
*/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

defined( 'ABSPATH' ) or die( 'Hey, what are you doing here? You silly human!' );

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'UD_VERSION', '1.0.0' );


class UD_Activation_process
{
	function __construct() {
               
        // enqueue script and styles
        add_action('admin_enqueue_scripts', array( $this, 'UD_enqueue_script_and_style') );  
        
        //Generate wp admin menu
        add_action( 'admin_menu', array( $this, 'UD_generate_wp_admin_menu' ) );


        // ajax call for updates role user redirection 
        add_action( 'wp_ajax_UD_user_role_direction_update', array($this, 'UD_ajax_user_role_direction_update')); 
        
        // ajax call for update plugin data         
        add_action( 'wp_ajax_UD_ajax_update_plugin_data', array($this, 'UD_ajax_update_plugin_data')); 

        //Redirect user to selected pages
        add_filter('login_redirect', array($this, 'UD_User_S_Directions' ), 10, 3 );
        
    }

    
	function activate() {
        
        //Create table for plugin data 
        $this->UD_Create_table();

        // enque script and style while plugin activate
        $this->UD_enqueue_script_and_style();                
        
        // generated a Page on wp dashbaord
        $this->UD_generate_wp_admin_menu();
     
		// flush rewrite rules
        flush_rewrite_rules();
        
    }

    
	function deactivate() {
		// flush rewrite rules		
		flush_rewrite_rules();
    }
    
    // Create table fucntion for plguin 
    function UD_Create_table(){
        global $wpdb;

        require_once( ABSPATH . '/wp-admin/includes/upgrade.php' );

        $UD_table_role_directions = $wpdb->prefix . 'ud_role_directions';
        $UD_table_settings = $wpdb->prefix . 'ud_settings';

        // create the UD metabox database table
        if($wpdb->get_var("show tables like '$UD_table_role_directions'") != $UD_table_role_directions){
            $sql = "CREATE TABLE `". $UD_table_role_directions . "` ( ";
            $sql .= "  `id`  int(11)   NOT NULL auto_increment,";
            $sql .= "  `role`  varchar(255)   NOT NULL,";
            $sql .= "  `redirect_url`  varchar(255)   NOT NULL,";
            $sql .= "  PRIMARY KEY (`id`) ";
            $sql .= "); ";
            
            dbDelta($sql);
        }

        // create the UD plugin settings table
        if($wpdb->get_var("show tables like '$UD_table_settings'") != $UD_table_settings){
            
            $sql_create = "CREATE TABLE `". $UD_table_settings . "` ( ";
            $sql_create .= "  `UD_meta_key`  varchar(255)   NOT NULL,";
            $sql_create .= "  `UD_meta_value`  varchar(255)   NOT NULL";
            $sql_create .= "); ";
            
            dbDelta($sql_create);

            $sql_insert = "INSERT INTO $UD_table_settings (UD_meta_key, UD_meta_value) VALUES ('UD_delete_plugin_data', 'false')";
            dbDelta($sql_insert);
        }
    }

    // enqueue script and styles
    function UD_enqueue_script_and_style(){

        $current_screen = get_current_screen();
        
        if ( strpos($current_screen->base, 'user-s-direction')  === false) {
            return;
        }else{
            
            // styles 
            wp_enqueue_style('bootstrap_4_css', '//maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css');            
            wp_enqueue_style('US_custom_css', plugins_url('assets/css/UD_custom.css',__FILE__ ));

            // scripts
            wp_enqueue_script('bootstrap_4_js', '//maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js', ['jquery'], time(), true);            
            wp_enqueue_script('sweetalert_js', plugins_url('assets/js/sweetalert.min.js',__FILE__ ), ['jquery'], time(), true);        

            wp_register_script( 'UD_ajax_js', plugins_url('assets/js/UD_ajax.js',__FILE__ ), [ 'jquery' ], time(), true );
            wp_localize_script( 'UD_ajax_js', 'ajax_data', [ 'ajax_url' => admin_url('admin-ajax.php' ) ] );        
            wp_enqueue_script( 'UD_ajax_js' );

            wp_enqueue_script('UD_custom_js', plugins_url('assets/js/UD_custom.js',__FILE__ ), ['jquery'], time(), true);
        
        } 

    }
    
    // ajax call for updates role user redirection 
    function UD_generate_wp_admin_menu(){        
        $page_title = 'User\'s Direction Dashboard';
		$menu_title = 'User\'s Direction';
		$capability = 'manage_options';  
		$menu_slug  = 'user-s-direction';
		$function   =  array( $this, 'UD_admin_settings_page' );
		$icon_url   = 'dashicons-redo';
		$position   = 70;
		
		add_menu_page( $page_title,   $menu_title,    $capability,    $menu_slug,    $function,    $icon_url,    $position );

    }

    function UD_ajax_user_role_direction_update(){
        global $wpdb, $wp_roles;
		
		$registerd_roles = $wp_roles->roles;
        $UD_table = $wpdb->prefix . 'ud_role_directions';        

        if( isset($_POST['UD_directions']) ){
			
            if(is_array($_POST['UD_directions'])){
				$UD_directions_final = array();				
				
				$unfilter_data = $_POST['UD_directions'];							
				
				$UD_directions_final = filter_var_array($unfilter_data, FILTER_VALIDATE_INT);			
				
                foreach($UD_directions_final as $UD_key => $UD_value){
                    
                    $UD_find_in_db = $wpdb->get_results("SELECT * FROM $UD_table where role = '".$UD_key."'");
        
                    if(!empty($UD_find_in_db)){
        
                        $UD_settings_insert_update = $wpdb->update( 
                            $UD_table,
                            array(                        
                                'redirect_url' => $UD_value
                            ), 
                            array(
                                'role' => $UD_key
                            ), 
                            array( 
                                '%s'                                    
                            ),                    
                            array( 
                                '%s'
                            )
                        );
        
                    }else{
        
                        $UD_settings_insert_update = $wpdb->replace( 
                            $UD_table, 
                            array(
                                'role' => $UD_key,
                                'redirect_url' => $UD_value
                            ), 
                            array( 
                                '%s',
                                '%s'                
                            ) 
                        );

                    }   
                   
                }

                die();
            }

            die();
        }

        die();
    }


    function  UD_ajax_update_plugin_data(){
        
        global $wpdb;

        $key = "UD_delete_plugin_data";

        if( isset($_POST['Ud_is_delete_database']) ){

            $ud_is_delete_database = sanitize_text_field($_POST['Ud_is_delete_database']);
            $ud_is_delete_database_value = esc_attr($ud_is_delete_database);         
            $UD_table_role_directions = $wpdb->prefix . 'ud_role_directions';
            $UD_table_settings = $wpdb->prefix . 'ud_settings';

            $UD_settings_updates = $wpdb->update( 
                $UD_table_settings,
                array(                        
                    'UD_meta_value' => $ud_is_delete_database_value
                ), 
                array(
                    'UD_meta_key' => $key
                ), 
                array( 
                    '%s'                              
                ),                    
                array(
                    '%s'
                )
            );
           
        }
        
        die();
    }


    function UD_User_S_Directions( $redirect_to, $request, $user  ){
        global $wpdb;
        $UD_table = $wpdb->prefix . 'ud_role_directions';

        $UD_final_user_role = "";
        if ( !empty( $user->roles ) && is_array( $user->roles ) ) {
            foreach ( $user->roles as $role )
                $UD_final_user_role = $role;
        }

        $UD_find_in_db = $wpdb->get_results("SELECT * FROM $UD_table where role = '".$UD_final_user_role."'");
        

        if(!empty($UD_find_in_db)){
        
            if($UD_find_in_db[0]->redirect_url != "" ){
                return get_permalink((int) $UD_find_in_db[0]->redirect_url);
            }

        }
        
        return $redirect_to;
    }
    

    function UD_admin_settings_page(){
        include( plugin_dir_path( __FILE__ ) . "admin/setting-page.php" );
    }

    
}

if ( class_exists( 'UD_Activation_process' ) ) {
	$UD_Activation_process = new UD_Activation_process();
}

// activation
register_activation_hook( __FILE__, array( $UD_Activation_process, 'activate' ) );
// deactivation
register_deactivation_hook( __FILE__, array( $UD_Activation_process, 'deactivate' ) );
// uninstall
//register_uninstall_hook(__FILE__, 'uninstall' );

// function uninstall(){
//     global $wpdb;
//     $UD_table_role_directions = $wpdb->prefix . 'ud_role_directions';
//     $UD_table_settings = $wpdb->prefix . 'ud_settings';
    
//     $UD_get_settings_plugin_data = $wpdb->get_row( "SELECT * FROM $UD_table_settings where UD_meta_key='UD_delete_plugin_data'" );

//     if( $UD_get_settings_plugin_data->UD_meta_key == "UD_delete_plugin_data" && $UD_get_settings_plugin_data->UD_meta_value == "false" ){            
        
//         $sql1 = "DROP TABLE IF EXISTS $UD_table_role_directions";
//         $wpdb->query($sql1);

//         $sql2 = "DROP TABLE IF EXISTS $UD_table_settings";
//         $wpdb->query($sql2);
        
//     }
// }
