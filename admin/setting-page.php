<?php

global $wpdb;
global $UD_settings;
global $UD_role_directions;

$UD_role_directions = $wpdb->prefix . 'ud_role_directions';
$UD_settings = $wpdb->prefix . 'ud_settings';

function UD_get_roles() {
  global $wp_roles;

  $all_roles = $wp_roles->roles;
  //$editable_roles = apply_filters('editable_roles', $all_roles);

  return $all_roles;
}

function UD_get_settings_plugin_data(){
  
  global $wpdb;
  $UD_table_settings = $wpdb->prefix . 'ud_settings';

  $sql_query = $wpdb->prepare(  'SHOW TABLES LIKE %s', $UD_table_settings );
  //$UD_table_settings = $wpdb->prefix . 'ud_settings';

  //echo $UD_table_settings;

  if( $wpdb->get_var( $sql_query ) == $UD_table_settings){
    $UD_get_settings_plugin_data = $wpdb->get_results("SELECT * FROM $UD_table_settings where UD_meta_key='UD_delete_plugin_data'");
    
    $UD_get_settings_plugin_data_test = $wpdb->get_row( "SELECT * FROM $UD_table_settings where UD_meta_key='UD_delete_plugin_data'" );
    
    //echo "<pre>";
    //print_r($UD_get_settings_plugin_data_test);
    //echo "</pre>";
    return $UD_get_settings_plugin_data[0]->UD_meta_value;
  }else{
    return "false";
  }
  
  //return $UD_get_settings;

}

UD_get_settings_plugin_data();

function UD_get_select_page_dropdown($role_key, $selected_role_key){
  $is_selected = "";
  $pages = get_pages();

  ?>

    <select class="form-control <?php echo $role_key; ?>" name="some_input_counter">

      <?php 

      foreach( $pages as $key_page => $value_page ){

        if($selected_role_key == $value_page->ID){
          $is_selected = "selected";
        }else{
          $is_selected = "";
        }
        echo "<option value='".$value_page->ID."' ".$is_selected.">".$value_page->post_title."</option>";
        //echo " key page ". $value_page->ID ." => name ".$value_page->post_title."<br>";
      }

      ?>

    </select> 

  <?php
}


//echo "<pre>";
//print_r($pages);
//echo "<pre>";


// foreach(get_r() as $key => $value){
//   echo "Role : ". $key ." => name  ".$value['name']."<br>"; 
// }

?>
<div class="UD_container container">

  <div class="UD_plugin_head">
    <h3>Welcome To User's direction <span class="UD-direction-icon"><img class="rounded" src="<?php echo plugins_url('/users-directions/assets/img/direction.png'); ?>" /></span></h3>
    <p class="UD_plugin_quote">Easy to give direction to users on the basis of their role</p>
  </div>
  
  <div class="settig-section">

    <ul class="nav nav-tabs" id="myTab" role="tablist">
        <li class="nav-item">
          <a class="nav-link active" id="general-tab" data-toggle="tab" href="#general" role="tab" aria-controls="home" aria-selected="true">General</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" id="settings-tab" data-toggle="tab" href="#settings" role="tab" aria-controls="settings" aria-selected="false">Settings</a>
        </li>
    </ul>
    <div class="tab-content" id="myTabContent">
      <div class="tab-pane fade show active" id="general" role="tabpanel" aria-labelledby="general-tab">

        <!-- <h1>General</h1> -->

        
        <div class="UD_section_container">
          <h2 class="UD_big_head">General setup</h2>
          <h3 class="UD_small_head">Select the user role and select the page where you want to redirect while logging in.</h2>
        </div>
        <div class="setting-forms">
            <form id="form">
              
              <div class="heading_table">
                <div>Select the role of user and the appropriate pages ( <input type="checkbox" class="form-control" id="test" checked="checked"></input> tik to active redirection of particular role )</div>              
              </div>

              <div class="parent_container">
                
                <div class="template_element">

                <?php
                      //$i_inc = 1;
                      foreach(UD_get_roles() as $UD_role_key => $UD_role_value){
                          $UD_find_in_db = $wpdb->get_results("SELECT * FROM $UD_role_directions where role = '".$UD_role_key."'"); 
						  //print_r($UD_find_in_db);
                        ?>

                          <div class="form-row">
                              <div class="col mt-2">
                                <input type="text" class="form-control" value="<?php echo $UD_role_key; ?>" placeholder="<?php echo $UD_role_value['name']; ?>" readonly>
                              </div>
                              <div class="col mt-2">
                                <!-- <input type="text" class="form-control" placeholder="Last name"> -->
                                <?php 
                                    // echo "<pre>";
                                    // print_r($UD_find_in_db);
                                    // echo "<pre>";
                                    // exit();
                                    $redirect_urls = "";
                                    $redirect_checked = false;
                                    if( !empty( $UD_find_in_db ) ){
                                      $redirect_urls = $UD_find_in_db[0]->redirect_url;
                                      if( $UD_find_in_db[0]->redirect_url != "" )
                                      $redirect_checked = true;
                                    } 
                                    
                                    UD_get_select_page_dropdown($UD_role_key, $redirect_urls);
                                ?>
                              </div>
                              <div class="col mt-2">
                                <input type="checkbox" class="form-control direction_section direction_section_<?php echo $UD_role_key;  ?> <?php if( $redirect_checked ){ echo "checked";  }  ?>" id="<?php echo $UD_role_key;  ?>" <?php if( $redirect_checked ){ echo "checked='checked'";  }  ?>>                        
                                <!-- <button type="button" class="remove btn btn-danger">x</button> -->
                              </div>
                            </div>


                        <?php

                        //echo "Role : ". $key ." => name  ".$value['name']."<br>"; 
                        //echo "<option value='".$key."'>".$value['name']."</option>";

                        //$i_inc++;
                      }
                      
              
                    ?>
                                
              
                </div>
                
              </div>
              

              <div class="UD-btn-save mt-3">
                <button onclick="return false;" class="btn-save btn btn-success btn-save-general" >Save</button>
                <div class="update_message"></div>
              </div>
            </form>
        </div>
      </div>
      <div class="tab-pane fade" id="settings" role="tabpanel" aria-labelledby="settings-tab">

      <div class="UD_section_container">
        <h2 class="UD_big_head">Settings </h2>
        <h3 class="UD_small_head">Select Configuration or Settings for plugin</h2>
      </div>

      <div class="setting-forms">
        <form id="form">
          
          <div class="heading_table">
            <div>Please select or check the option you want to setup</div>              
          </div>

          <?php
            //UD_get_settings();
            //UD_get_settings_plugin_data
          ?>

          <div class="custom-control custom-checkbox">
            <input type="checkbox" class="custom-control-input is_delete_data" id="defaultUnchecked" <?php if( UD_get_settings_plugin_data() == "true" ){ echo "checked='checked'"; }else{ echo ""; } ?> >
            <label class="custom-control-label" for="defaultUnchecked">Do you want to delete plugin data after the plugin has deactivated</label>
          </div>

          <div class="UD-btn-save mt-3">
              <button onclick="return false;" class="btn-save btn btn-success btn-save-settings" >Save</button>
          </div>

        </form>
      </div>     

        

      </div>
    </div>
 
</div>


</div>

