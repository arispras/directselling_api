/**
 * jQuery script for creating lookup box
 * @version 1.2
 * @requires jQuery UI
 *
 * Copyright (c) 2016 Lucky
 * Licensed under the GPL license:
 *   http://www.gnu.org/licenses/gpl.html
 */

(function($) {
   var  $dialog ;
   var ItemTable;
  function lookupbox_table(options) {
   
   var settings = {
      id: "lookup-dialog-box",
      title: "Lookup",
      url: "",
      loadingDivId: "loading1",
      imgLoader: '<img src="images/ajax-loader.gif">',
      notFoundMessage: "Data not found!",
      requestErrorMessage: "Request error!",
      tableHeader: null,
      onSearch: null,
      searchButtonId: "lookupbox-search-button",
      searchTextId: "lookupbox-search-key",
      searchResultId: "lookupbox-search-result",
      width: 400,
      htmlForm: '<form id="lookupbox-search-form" onsubmit="return false"><input id="lookupbox-search-key" type="text" name="key" placeholder="Enter search criteria." /> <input type="button" id="lookupbox-search-button" value="Search" /> <span id="loading1"></span></form><div id="lookupbox-search-result"></div>',
     
      modal: true,
      draggable: true,
      onItemSelected: null,
      item: null,
      hiddenFields: [],
      vdata: null
    };
    $.extend(settings, options);

    $(document).ready(function() {
      if ($("#" + settings.id).length == 0) {
        $dialog = $('<div id="' + settings.id + '"></div>');
      }
      else {
        $dialog = $('#' + settings.id);
      }

      var ItemTable;
      var table = "<table  cellpadding='0' cellspacing='0' border='0' class='display' width='100%' id='lookupbox-result' >";

                table=table + "  <thead>";
                if (settings.tableHeader != null) {
                  table = table + "<tr id='lookupbox-result-header' class='lookupbox-result-header'>";
                  for (var i = 0; i<settings.tableHeader.length; i++){
                    table = table + "<th>" + settings.tableHeader[i] + "</th>";
                  }
                  table = table + "</tr>";
                }
                table = table +"           </thead>";
                table = table +"           <tbody id='itemlookup'>";
                table = table +"           </tbody>";
                table = table + "</table>";
                table = table + "<div class='modal-footer'>";
                table = table + "        <button id='ok_dialog_btn' type='button'  class='btn btn-default'>Ok</button>";
                table = table + "          <button id='close_dialog_btn' type='button' class='btn btn-default' >Close</button>";
                table = table + "        </div>";
          
      $dialog = $dialog.html(table)
        .dialog({
          autoOpen: false,
          title: settings.title,
          modal: settings.modal,
          draggable: settings.draggable,
          width: settings.width
        });

      
         ItemTable = $('#lookupbox-result').DataTable( {
          "processing": true,
          "serverSide": true,
           "ajax": {
           "url": settings.url,
          //"type": "POST",
           //"data": function ( data ) {
           // }
             "data": settings.vdata,

          },
          
          "columnDefs": [
          { "targets": 0, "visible": false }
          ]
        } );

        
        $('#lookupbox-result tbody').on( 'click', 'tr', function () {
          if ( $(this).hasClass('selected') ) {
            $(this).removeClass('selected');
          }
          else {
            ItemTable.$('tr.selected').removeClass('selected');
            $(this).addClass('selected');
          }
        } );
      
        $('#lookupbox-result').removeClass( 'display' ).addClass('table table-striped table-bordered');        

        $("#close_dialog_btn").click(function(){
            $dialog.dialog('close');

          });

         $("#ok_dialog_btn").click(function(){
            //table = $('#ItemTable').DataTable();
            var a=ItemTable.rows('.selected').data().length;
            if( a>0)
              {
                var d=ItemTable.row('.selected').data();
                settings.onItemSelected.call(this, d);
                $dialog.dialog("close");

                 
              }
              else {
              
               a=-1;
               alert("Please Select Row!")
              }

         });

      // $("#" + settings.searchButtonId).click(function(){
      //   if (settings.onSearch == null) {
      //      //alert(settings.vdata['where']);
      //     $.ajax({
      //       beforeSend: function(){
      //         $('#' + settings.loadingDivId).html(settings.imgLoader);
      //       },
      //       url: settings.url + $("#" + settings.searchTextId).val(),
      //       data: settings.vdata,
      //       success: function(result) {
      //         try {
      //           var data = null;

      //           if (typeof result == 'string')
      //             data = $.parseJSON(result);
      //           else if (typeof result == 'object')
      //             data = result;

      //           settings.item = data;
      //           var table = "<table cellspacing='0' cellpadding='3' id='lookupbox-result' class='lookupbox-result'>";

      //           if (settings.tableHeader != null) {
      //             table = table + "<tr id='lookupbox-result-header' class='lookupbox-result-header'>";
      //             for (var i = 0; i<settings.tableHeader.length; i++){
      //               table = table + "<th>" + settings.tableHeader[i] + "</th>";
      //             }
      //             table = table + "</tr>";
      //           }

      //           for(i=0;i<data.length;i++){
      //             var rowClass = 'lookupbox-result-row odd';
      //             if (i % 2 == 0) {
      //               rowClass = 'lookupbox-result-row even';
      //             }
      //             table = table + "<tr id='lookupbox-result-row-" + i + "' class='" + rowClass + "'>";
      //             for (var key in data[i]){
      //               if ($.inArray(key, settings.hiddenFields) === -1) {
      //                 table = table + "<td style='cursor:pointer'>" + data[i][key] + "</td>";
      //               }
      //             }
      //             table = table + "</tr>";
      //           }
      //           table = table + "</table>";

      //           $("#" + settings.searchResultId).html(table);

      //           if (settings.onItemSelected != null) {
      //             $("#lookupbox-result tr").click(function(){
      //               var arr = $(this).attr("id").split("-");
      //               if (typeof settings.onItemSelected === "function") {
      //                 settings.onItemSelected.call(this, settings.item[arr[arr.length - 1]]);
      //               }
      //               $dialog.dialog("close");
      //             });
      //           }
      //         }
      //         catch(e) {
      //           $("#" + settings.searchResultId).html(settings.notFoundMessage);
      //         }
      //         $('#' + settings.loadingDivId).html('');
  				// 	},
      //       error: function(xhr, status, ex) {
      //         $("#" + settings.searchResultId).html(settings.requestErrorMessage);
      //       }
      //     });
      //   }
      //   else{
      //     if (typeof settings.onSearch === "function") {
      //       settings.onSearch.call();
      //     }
      //   }
      // });

      // $("#" + settings.searchTextId).keyup(function(e){
      //   if(e.keyCode == 13){
      //     $("#" + settings.searchButtonId).trigger('click');
      //   }
      // });

      $dialog.dialog('open');
      
    });
  
    
  }

  $.fn.lookupbox_table = function(options) {
    var settings = options;
    return this.each(function() {
      $(this).click(function () {
        lookupbox_table(settings);
      });
    });
  }
})(jQuery);
