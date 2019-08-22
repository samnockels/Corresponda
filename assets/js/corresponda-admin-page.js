(function iifeJquery( $ ) {
 
    "use strict";
     
    $(document).ready(function domReady() {
   
       //==================================== 
       // Corresponda functionality
       //==================================== 
   
       var Corresponda = new function(){
           this.widgetDropdown  = $('#edit_widget_dropdown')
           this.nameHeading     = $('#hc_widget_name_heading')
           this.shortcodeName   = $('#hc_shortcode_name')
           this.widgetPreview   = $('#hc_widget_preview')
           this.title     	    = $('#hc_widget_title')
           this.titleSave 	   	= $('#hc_widget_save_title')
           this.options   		= $('#hc_options_table_body')
           this.widgetType 	    = $('#hc_widget_types')
           this.addLinkBtn      = $('#hc_add_link_btn')
           this.selectedWidgetName = "";
       }
   
       /**
        * when loading existing widgets, we will 
        * cache them here	
        */
       Corresponda.loadedWidgetsCache = [];
   
       /**
        * Show/Hide selected widget
        */
       Corresponda.hide = function(){ Corresponda.widgetDropdown.hide() }
       Corresponda.show = function(){ Corresponda.widgetDropdown.show() }
   
       /**
        * Remove an option from a widget
        */
       window.correspondaRemoveWidgetOption = function(el){
           el.parentElement.parentElement.remove()
       }

       /**
        * Insert a link 
        */
       window.correspondaInsertLink = function(el){
            
           Corresponda.inputToAddLink = $(el.parentElement.parentElement).find('input[name=value]')

           // reset 
           $("#hc_add_link_name").val('');
           $("#hc_add_link_url").val('');
           // focus
           $("#hc_add_link_name").focus();
           // show
           $('#insertLinkModal').modal('show');
       }

       $('#hc_add_link_btn').click(function(){
           let link = '[' + $('#hc_add_link_name').val() + ']' + '(' + $('#hc_add_link_url').val() + ')'
           console.log(link)
           Corresponda.inputToAddLink.val(Corresponda.inputToAddLink.val() + link)
           $('#insertLinkModal').modal('hide');
       })
   
       /**
        * Add an option to a widget
        * @optional name
        * @optional value
        */
       Corresponda.addOption = function( name = '', value = '' ){
           Corresponda.options.append(
               $('<tr/>')
                 .attr("class", "hc_option")
                 .html(`<td>
                             <input type='text' class='form-control' name='name' placeholder='Name' required value='`+name+`'>
                        </td>
                        <td>
                             <input type='text' class='form-control' name='value' placeholder='Value' required value='`+value+`'>
                        </td>
                        <td>
                             <button type="button" class="btn btn-danger" onclick="window.correspondaRemoveWidgetOption(this);">Remove</button>
                             <button type="button" class="btn btn-info"   onclick="window.correspondaInsertLink(this);">Insert Link</button>
                        </td>`));
       }

       Corresponda.refreshWidgetPreview = function(){
            this.widgetPreview.html(`
                <div class="d-flex justify-content-center">
                    <div class="spinner-border" role="status">
                    <span class="sr-only">Refreshing Widget...</span>
                    </div>
                </div>
            `);
            $.ajax({
                url: document.location.origin + '/wp-admin/admin-ajax.php',
                type: 'post',
                data: {
                    action :      'corresponda_get_widget_preview',
                    widget_name:  Corresponda.selectedWidgetName
                }
            })
            .done(function(response){
                if( response.success == false ){
                    Corresponda.widgetPreview.html('There was a problem rendering the widget.')
                    return;
                }
                Corresponda.widgetPreview.html(response)
            });
       }

       /**
        * Load a widget into the options table
        * @param widget
        */
       Corresponda.load = function( widget ){	
           this.hide()
           this.nameHeading.text(widget.name)
           this.shortcodeName.text(widget.name)
           this.options.empty()
           this.title.val('')
           this.widgetType.val(widget.tag).change()
           if( widget.title ){
               this.title.val(widget.title)
           }
           // console.log(widget.options)
           if( widget.options.length > 0 ){
               widget.options.forEach(function( option ){
                   this.addOption(option.name, option.value)
               }, this)
           }
           Corresponda.refreshWidgetPreview(widget.name)
           this.show()
           return;
       }
   
       /**
        * Update a widget
        */
       Corresponda.updateWidget = function( widget_name, data ){
      
           if( !widget_name || (!data.title && !data.tag && !data.options) ){
               swal({title: "Error",text: "An error has occurred.",icon: "error"})
               return;
           }
   
           data.action 	    = 'corresponda_update_widget';
           data.widget_name = widget_name;
   
           $.ajax({
               url: document.location.origin + '/wp-admin/admin-ajax.php',
               type: 'post',
               data: data,
               dataType:"json"
           })
           .done(function(response){
               if( !response.success ){
                   swal({title: "Error",text: "An error has occurred.",icon: "error"})
                   return;
               }
               // remove from cache so next time it is loaded, we load the saved version
               Corresponda.loadedWidgetsCache[widget_name] = undefined
               swal({title: "Success",text: "Widget saved successfully",icon: "success"})
               Corresponda.refreshWidgetPreview()
           });
       }
   
       //==================================== 
       // Events 
       //==================================== 
   
        /**
         * Create new widget
         */
        $("#hc_create_widget_form").on('submit', function(e){
           e.preventDefault();
           $.ajax({
               url: document.location.origin + '/wp-admin/admin-ajax.php',
               type: 'post',
               data: {
                   action :      'corresponda_create_new_widget',
                   widget_tag:   $("#hc_widget_tag").find(":selected").val(),
                   widget_name:  $("#hc_widget_name").val()
               }
           })
           .done(function(response){
               if( !response.success ){
                   swal({title: "Error", text: response.error, icon: "error"})
                   return;
               }
               swal({title: "Success", text: "Widget added successfully. Reloading...", icon: "success"})
               location.reload()
           });
        });
   
        /**
         * Get widget 
         */
        $("#selectWidget").change(function(){
           let widget_name = $(this).find("option:selected").text()
           
           Corresponda.selectedWidgetName = widget_name
           
           // check cache first
           if( Corresponda.loadedWidgetsCache[widget_name] !== undefined ){
               Corresponda.load(Corresponda.loadedWidgetsCache[widget_name])
               return;
           }
   
           // not in cache, so lets load it
           $.ajax({
               url: document.location.origin + '/wp-admin/admin-ajax.php',
               type: 'post',
               data: {
                   action: 'corresponda_get_widget',
                   widget_name: widget_name
               }
           })
           .done(function(response){
               if( !response.success || !response.widget ){
                   swal({title: "Error", text: response.error, icon: "error"})
                   return;
               }
               // store widget in cache first
               Corresponda.loadedWidgetsCache[widget_name] = response.widget
               // now load
               Corresponda.load(response.widget)
           });
        });
   
        /**
         * Change title of widget
         */
        $("#hc_widget_save_title").click(function saveWidgetTitle(){
           if( $("#hc_widget_title").val() == "" ){
               swal({title: "Error",text: "You can't have an empty title.",icon: "error"})
               return;
           }
           swal({title: "Saving",text: "Saving title...",icon: "info"})
           Corresponda.updateWidget(Corresponda.selectedWidgetName, {
               title: $("#hc_widget_title").val()
           });
           Corresponda.refreshWidgetPreview()
        })
   
        /**
         * Save widget type (tag)
         */
        $("#hc_widget_save_tag").click(function saveWidgetTag(){
           if( Corresponda.widgetType.val() == "" ){
               swal({title: "Error",text: "Please select a Widget Type.",icon: "error"})
               return;
           }
           swal({title: "Saving",text: "Saving widget type...",icon: "info"})
           Corresponda.updateWidget(Corresponda.selectedWidgetName, {
               tag: Corresponda.widgetType.val()
           });
           Corresponda.refreshWidgetPreview()
        })
   
        /**
         * Save a widgets options
         */
        $('#hc_options_form').submit(function saveWidgetOptions(e){
           e.preventDefault();
           let options = [];
           $("#hc_options_form").find('tr.hc_option').toArray().forEach(function(row){
               let name = row.getElementsByTagName('input')[0].value
               let val  = row.getElementsByTagName('input')[1].value
               options.push({"name":name, "value":val});
           });
           if( options.length == 0 ){
               options = 'unset'
           }
           Corresponda.updateWidget(Corresponda.selectedWidgetName, {
               options: options
           })
           Corresponda.refreshWidgetPreview()
        })
        
        /**
         * Delete a widget
         */
        $('#hc_delete_widget').click(function deleteWidget(e){
           e.preventDefault();

           swal({
                title: "Are you sure?", 
                text:  "This will permanantely delete the widget '" + Corresponda.selectedWidgetName + "', are you sure you want to proceed?", 
                type:  "warning",
                showCancelButton: true,
                closeOnConfirm: false,
                showLoaderOnConfirm: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "Yes, delete it." 
            }, function delete_widget(){
                $.ajax({
                    url: document.location.origin + '/wp-admin/admin-ajax.php',
                    type: 'post',
                    data: {
                        action : 'corresponda_delete_widget',
                        widget_name: Corresponda.selectedWidgetName
                    }
                }).done(function (response) {
                    if(response.success){
                        swal("Success","Widget deleted. Reloading the page...","success");
                        location.reload();
                    }else{
                        swal("Error","An unexpected error occurred.","error");
                    }
                }).fail(function () {
                    swal("Error","An unexpected error occurred.","error");
                });
            });
        })
        
        /**
         * Add option to currently displayed list of option
         */
        $('#hc_signup_add_option').click(function addOption(){
           Corresponda.addOption();
        });
   
        /**
         * Load a csv file
         */
        $('#hc_upload_csv').submit(function(e){
           e.preventDefault();
           
           swal("Processing", "Processing file...", "info")
   
           const reader = new FileReader()
           reader.readAsText($("#hc_options_csv_upload")[0].files[0])
           reader.onload = e => { 
               const rows = e.target.result.split('\n').filter(val => {return val !== ""}).map(row=>{
                   row = row.split(',')
                   Corresponda.addOption( row[0].trim(), row[1].trim() )
               })
           }
           $('#hc_signup_save_options').click(); // save
           Corresponda.refreshWidgetPreview()
        })

        
   
    }); 
   
   })(jQuery);