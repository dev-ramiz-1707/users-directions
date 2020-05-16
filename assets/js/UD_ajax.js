//* Ajax call
jQuery(document).ready(function($){

    jQuery('.direction_section').on('click', function() {
      jQuery(this).toggleClass('checked');
    });


    jQuery('.UD-btn-save .btn-save-settings').on('click',function(){


      var Ud_is_delete_database = jQuery('.is_delete_data');

      //jQuery('.is_delete_data').click(function(){
        if( jQuery('.is_delete_data').prop("checked") == true ){
            Ud_is_delete_database = "true";
            //alert("Checkbox is checked.");
          }
        else if( jQuery('.is_delete_data').prop("checked") == false ){
            Ud_is_delete_database = "false";
            //alert("Checkbox is unchecked.");
          }
      //});

      //return false;      

      jQuery.ajax({
              type : "post",
              url : ajax_data.ajax_url,
              data : { action: "UD_ajax_update_plugin_data", Ud_is_delete_database : Ud_is_delete_database },
              success: function(response) {
              
                  console.log("UD_ajax_update_plugin_data", response);
                  //jQuery('.update_message').html(response);
                  
                    swal({
                      text: "Good job!",
                      title: "You saved your settings!",
                      icon: "success",
                      button: "Aww yiss!",
                    });
                  return false;
                
              }            
      });

      return false;
    });

    jQuery('.UD-btn-save .btn-save-general').on('click',function(){


        //var selected_role_page = [];
        var items = {};

        jQuery('.direction_section').each(function() {
          //var items = {};
          var get_sected_role_id = jQuery(this).attr('id');
          //console.log(get_sected_role_id);
          var get_selected_page_id = jQuery( "."+get_sected_role_id).val();
          console.log(get_sected_role_id + " : " + get_selected_page_id);

          if(jQuery(this).hasClass('checked')){
            items[get_sected_role_id] = get_selected_page_id;
          }else{
            items[get_sected_role_id] = "";
          }
          
          //selected_role_page.push( items );
        });        
        //console.log(selected_role_page);
        


        jQuery.ajax({
                type : "post",
                url : ajax_data.ajax_url,
                data : { action: "UD_user_role_direction_update", UD_directions : items },
                success: function(response) {
                
                    console.log(response);
                    jQuery('.update_message').html(response);
                    
                    swal({
                        text: "Good job!",
                        title: "You saved your settings!",
                        icon: "success",
                        button: "Aww yiss!",
                      });
                    return false;
                   
                }            
        });

        return false;
        
      });
});