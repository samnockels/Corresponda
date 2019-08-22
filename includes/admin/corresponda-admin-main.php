<?php
defined('ABSPATH') || exit;
?>

<div class="container mt-5">
   <div id="corresponda_main_heading" class="text-center py-4 text-light">
      <h4 class="m-0 p-0 font-weight-light">Corresponda</h4>
   </div>
   <div class="container mt-5">

      <form id="hc_create_widget_form" method="POST" action="">
         <input type="hidden" name="action" value="corresponda_create_new_widget">
         <div class="container d-flex flex-column align-items-start">
            <h5>Create Widget</h5>
            <div class="col-6 my-2 p-0">
               <input id="hc_widget_name" name="widget_name" class="form-control" placeholder="Enter name..." required/>
               <small id="widget_name_help" class="form-text text-muted">The name of the widget. This name is only used to identify the widget, and is not displayed to the user.</small>
            </div>
            <div class="col-6 my-2 p-0">
               <select id="hc_widget_tag" class="form-control py-2" required>
                  <option selected disabled>Widget Type</option>
                  <option value="hc_dropdown_with_text">Dropdown with text</option>
                  <option value="hc_autocomplete_with_text">Autocomplete Search with text</option>
               </select>
               <small id="widget_name_help" class="form-text text-muted">This can either be a simple dropdown, or an autocomplete search. This can be changed later.</small>
            </div>
            <button type="submit" class="btn btn-primary mt-2">Create</button>
         </div>
      </form>

      <hr class="my-5 p-3">

      <?php
      $widgets = hc_get_all_widgets();
      if( !$widgets ):
      ?>

         <div class="alert alert-warning" role="alert">
            <strong>No Widgets!</strong> Please create a new widget using the form above
         </div>

      <?php
      else:
      ?>
      <h5>
      <span class="badge table-primary mb-4 font-weight-light py-2 px-3">
         <?php echo count($widgets) ?> Widget<?php echo count($widgets) > 1 ? 's' : '' ?>
      </span>
      </h5>
      <div class="form-group mb-0">
         <select class="form-control" id="selectWidget">
            <option disabled selected>Select Widget to Edit</option>
            <?php
            foreach( hc_get_all_widgets() as $widget ):
            ?>
               <option><?php echo __($widget['name'], 'corresponda') ?></option>
            <?php
            endforeach;
            ?>
         </select>
      </div>

      <div id="edit_widget_dropdown" class="bg-white p-3" style="display:none;">

         <div class="container py-2 d-flex flex-row align-items-start">
            <div class="col-6">
               <h4 id="hc_widget_name_heading" class="mt-2"></h4>
            </div>
            <div class="col-6">
               <button id="hc_delete_widget" class="btn btn-danger float-right">Delete Widget</button>
            </div>
         </div>

         <hr class="my-2">

         <div class="container py-2 d-flex flex-column align-items-start">
            <div class="col-12">
               <h5 class="font-weight-light mt-2 mb-3">How to use this widget</h5>
               <div class="alert alert-info" role="alert">
                  <p class="alert-heading">To use this widget on your pages and posts, use the following shortcode</p>  
                  <h5>[corresponda name="<span id="hc_shortcode_name"></span>"]</h5>
               </div>
            </div>
         </div>

         <hr class="my-2">

         <div class="container py-2 d-flex flex-column align-items-start">
            <div class="col-12">
               <h5 class="font-weight-light mt-2 mb-3">Widget Preview</h5>
               <div id="hc_widget_preview" class="alert alert-light" role="alert">
                  
               </div>
            </div>
         </div>

         <hr class="my-2">
         
         <div class="container py-2">
            <div class="flex-row">
               <div class="col-12">
                  <h5 class="font-weight-light mt-2">Widget Options</h5>
               </div>
            </div>
            <div class="d-flex flex-row">
               <div class="col-6">
                  <div class="input-group mt-3">
                     <div class="input-group-prepend">
                        <span class="input-group-text" id="basic-addon1">Placeholder Text</span>
                     </div>
                     <input id="hc_widget_title" class="form-control" value=""/>
                     <div class="input-group-append">
                        <button id="hc_widget_save_title" class="btn btn-info" type="button">Save</button>
                     </div>
                     <small id="widget_title_help" class="form-text text-muted">This text will be displayed to users before they make a selection.</small>
                  </div>

                  <div class="input-group mt-3">
                     <div class="input-group-prepend">
                        <span class="input-group-text" id="basic-addon1">Widget Type</span>
                     </div>
                     <select id="hc_widget_types" class="form-control py-2" required>
                        <option value="hc_dropdown_with_text">Dropdown with text</option>
                        <option value="hc_autocomplete_with_text">Autocomplete Search with text</option>
                     </select>
                     <div class="input-group-append">
                        <button id="hc_widget_save_tag" class="btn btn-info" type="button">Save</button>
                     </div>
                  </div>
                  
               </div>

               <div class="col-6">
                  <form id="hc_upload_csv" method="POST" action=''>
                     <div class='card m-0 p-0 my-3'>
                        <div class='card-header'>Import options from CSV</div>
                        <div class='card-body'>
                           <input id="hc_options_csv_upload" type="file" name="hc_options_csv_upload" class="form-control-file pl-3" value="Upload CSV" required>
                        </div>
                        <div class='card-footer bg-white'>
                           A CSV file with an option on a new line. 
                           <br> 
                           Format: <b> value, corresponding text  </b>
                           <br> 
                           To add a link in the corresponding text, make sure your links are in the format:
                           <code>[display text](https://google.com)</code>
                        </div>
                        <button type="submit" class="btn btn-info">Import</button>
                     </div>
                  </form>
               </div>
            </div>
            
         </div>
         <form id="hc_options_form" method='POST' action=''>
            <table class="table table-bordered">
               <thead class="table-info">
                  <tr>
                     <td>Option Value</td>
                     <td>Corresponding Text</td>
                     <td style="width: 170px;"></td>
                  </tr>
               </thead>
               <tbody id="hc_options_table_body" class="bg-white">
               </tbody>
            </table>
            <button type="button" id="hc_signup_add_option" class="btn btn-success">Add Option</button>
            <button type="submit" id="hc_signup_save_options float-right" class="btn btn-primary">Save</button>
         </form>
      </div>
   <?php
   endif;
   ?>
   </div>
</div>

<?php
// Add link modal
?>
<!-- Modal -->
<div class="modal fade" id="insertLinkModal" tabindex="-1" role="dialog" aria-labelledby="insertLinkModalTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="insertLinkModalTitle">Insert Link</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <input id="hc_add_link_name" class="form-control mb-2" type="text" placeholder="Display Name">
        <input id="hc_add_link_url"  class="form-control" type="text" placeholder="URL">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
        <button id="hc_add_link_btn" type="button" class="btn btn-primary">Insert</button>
      </div>
    </div>
  </div>
</div>