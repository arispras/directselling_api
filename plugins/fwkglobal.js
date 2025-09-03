/* ====================================================
fwkglobal : 
Kumpulan fungsi2 Javascript untuk frame work 
Created by Arispra(aris_prs@yahoo.com) on dec 2016 
=======================================================
*/
 
function setInitial(){
/* fungsi : untuk setting hak akses button dan  user ID pada hiddenfield 
Created by Arispra(aris_prs@yahoo.com) on dec 2016 */
var userData = JSON.parse(localStorage.getItem('user_dataLS'));
var userid=userData.user_id;
$("#userid").val(userid);





// var mnIndex=localStorage.getItem('mnIndexLS');
// var menuData = JSON.parse(localStorage.getItem('menuLS'));
// var menuDataList=menuData[mnIndex];
// 						     if (menuDataList.new_){
// 							     if ((menuDataList.new_)==1){
// 								   $('#btn-new').prop('disabled', false);
// 								   	  }
// 									  else{
// 								   $('#btn-new').prop('disabled', true);
// 								  }
// 							   }
// 							  if (menuDataList.edit_){
// 								   if ((menuDataList.edit_)==1){
// 								    $('#btn-edit').prop('disabled', false);
// 								   }else{
// 								    $('#btn-edit').prop('disabled', true);
								  
// 								   }
// 							   }
// 							   if (menuDataList.delete_){
// 								   if ((menuDataList.delete_)==1){
// 								    $('#btn-delete').prop('disabled', false);

// 								  }else{
// 								    $('#btn-delete').prop('disabled', true);

// 								   }
// 							   }
// 							   if (menuDataList.print_){
// 								   if ((menuDataList.print_)==1){
// 								    $('#btn-print').prop('disabled', false);
// 								  }else{
// 								   $('#btn-print').prop('disabled', true);
// 							     }
// 							   }
//  }

// function GetContentDelete(){
//  var mnIndex=localStorage.getItem('mnIndexLS');
//  var menuData = JSON.parse(localStorage.getItem('menuLS'));
//  var defaulContentDelete="";
//  var menuDataList=menuData[mnIndex];
// 							   if (menuDataList.delete_){
// 								   if ((menuDataList.delete_)==1){
								    
// 								      defaulContentDelete="<button type='button' id='btn-delete'  class='btn btn-danger btn-sm' disabled=true ><span class='glyphicon glyphicon-remove'></span></button>";

// 								  }else{
								   
// 								      defaulContentDelete="<button type='button' id='btn-delete'  class='btn btn-info danger-sm' disabled=true ><span class='glyphicon glyphicon-remove'></span></button>";

// 								   }
// 							   }
//  return defaulContentDelete;
//  }


// function GetContentEdit(){
//  var mnIndex=localStorage.getItem('mnIndexLS');
//  var menuData = JSON.parse(localStorage.getItem('menuLS'));
//  var defaulContentEdit="";
//  var menuDataList=menuData[mnIndex];
// 							  if (menuDataList.edit_){
// 								   if ((menuDataList.edit_)==1){
								    
// 								     defaulContentEdit="<button type='button' id='btn-edit'  class='btn btn-info btn-sm' disabled=false ><span class='glyphicon glyphicon-edit'></span></button>";
// 								   }else{
								    
// 								      defaulContentEdit="<button type='button' id='btn-edit'  class='btn btn-info btn-sm' disabled=true ><span class='glyphicon glyphicon-edit'></span></button>";
								  	 	
// 								   }
// 								   return defaulContentEdit;			 
// 							   }
 			   
//  }

// function GetContentPreview(){
//  var defaulContentPreview="<button type='button' id='btn-preview'  class='btn btn-info btn-sm' ><span class='glyphicon glyphicon-file'></span></button>";
//   return defaulContentPreview;						   
//  }

 
//  // ==== BELUM DIPAKAI ========== 
// function btn_access_setting(){
//  var mnIndex=localStorage.getItem('mnIndexLS');
//  var menuData = JSON.parse(localStorage.getItem('menuLS'));
//  var defaulContentDelete;
//  var defaulContentEdit;
//  var defaulContentPreview="<button type='button' id='btn-preview'  class='btn btn-info btn-sm' data-toggle='tooltip' data-placement='top' title='Preview' ><span class='glyphicon glyphicon-file'></span></button>";
//  var menuDataList=menuData[mnIndex];
// 	 if (menuDataList.new_){
// 		if ((menuDataList.new_)==1){
// 		   $('#btn-new').prop('disabled', false);
// 		  }
// 		  else{
// 		   $('#btn-new').prop('disabled', true);
// 			  }
// 	   }
// 	 if (menuDataList.edit_){
// 		if ((menuDataList.edit_)==1){
// 			 $('#btn-edit').prop('disabled', false);
// 			defaulContentEdit="<button type='button' id='btn-edit'  class='btn btn-info btn-sm' data-toggle='tooltip' data-placement='top' title='Edit'><span class='glyphicon glyphicon-edit'></span></button>";
// 		}else{
// 			$('#btn-edit').prop('disabled', true);
// 			defaulContentEdit="<button type='button' id='btn-edit'  class='btn btn-info btn-sm' disabled=true ><span class='glyphicon glyphicon-edit'></span></button>";
								  
// 		 }
// 	}
// 	if (menuDataList.delete_){
// 		if ((menuDataList.delete_)==1){
// 		$('#btn-delete').prop('disabled', false);
// 		 defaulContentDelete="<button type='button' id='btn-delete'  class='btn btn-danger btn-sm' data-toggle='tooltip' data-placement='top' title='Delete'><span class='glyphicon glyphicon-remove'></span></button>";

// 		}else{
// 		  $('#btn-delete').prop('disabled', true);
// 		defaulContentDelete="<button type='button' id='btn-delete'  class='btn btn-info danger-sm' disabled=true ><span class='glyphicon glyphicon-remove'></span></button>";
// 		}
// 	}
// 	if (menuDataList.print_){
// 		if ((menuDataList.print_)==1){
// 		   $('#btn-print').prop('disabled', false);
// 		  }else{
// 		   $('#btn-print').prop('disabled', true);
// 		}
// 	}


 }


function fillcombo(combobox,id,text,tblname,where ){
/* Fungsi : untuk mengisi data Combobox/select 
Created by Arispra(aris_prs@yahoo.com) on dec 2016 */

var eId=id;
var eText=text;
var eTblname=tblname;
var ewhere=where;
       $.ajax({
					type: "POST",
					//url: "http://10.20.24.9:8080/datatablesku/crud.php",
					url: "../../fwkClass/fwk_fillcombo.php",
					dataType: "json",
					data: {id:eId,text:eText,tblname:eTblname,where:ewhere},
					success: function(data) {
					combobox.empty();
					combobox.append($('<option>').text("<Select>").attr('value', ""));
					$.each(data, function(i, obj){
							combobox.append($('<option>').text(obj.text).attr('value', obj.id));
						});	
					}
				});
}

function fillcheckbox(div_id,checkbox_name,id,text,tblname,where ){
/* Fungsi : untuk mengisi data checkbox pada div 
Created by Arispras(aris_prs@yahoo.com) on may 2017 */

var eId=id;
var eText=text;
var eTblname=tblname;
var ewhere=where;
 var idCounter=1;
 var checkbox=checkbox_name+"[]";
       $.ajax({
					type: "POST",
					//url: "http://10.20.24.9:8080/datatablesku/crud.php",
					url: "../../fwkClass/fwk_fillcombo.php",
					dataType: "json",
					data: {id:eId,text:eText,tblname:eTblname,where:ewhere},
					success: function(data) {
					 div_id.empty();
					
					 $.each(data, function(i, obj){
						 var val =obj.text ;
           				// div_id.append ( "<label for='chk_" + idCounter + "'>" + val + "</label><input id='chk_" + idCounter + "' type='checkbox' name='" + checkbox + "' value='" + obj.id + "' /><br>" );
            			div_id.append ( "<div class='checkbox'>  <label><input id='chk_" + idCounter + "' name='" + checkbox + "' type='checkbox' value='" + obj.id + "'>"+ val +"</label></div>");
            			idCounter ++;

						});	
					}
				});
}


function fillcombo_sinc(combobox,id,text,tblname,where,distincts){
/* Fungsi : untuk mengisi data Combobox/select 
Created by Arispra(aris_prs@yahoo.com) on dec 2016 */

var eId=id;
var eText=text;
var eTblname=tblname;
var ewhere=where;
var edistinct=distincts;
       $.ajax({
					type: "POST",
					//url: "http://10.20.24.9:8080/datatablesku/crud.php",
					async: false,
					url: "../../fwkClass/fwk_fillcombo.php",

					dataType: "json",
					data: {id:eId,text:eText,tblname:eTblname,where:ewhere,distinct:edistinct},
					success: function(data) {
					combobox.empty();
					combobox.append($('<option>').text("<Pilih Data>").attr('value', ""));
					$.each(data, function(i, obj){
							combobox.append($('<option>').text(obj.text).attr('value', obj.id));
						});	
					//alert ('sukses');
					}
				});
}



 function fillJsonData(edata) {
   $.each(edata, function(name, val) {
            //alert (name);
            var el = $('[name="' + name + '"]');
            var  type = el.prop('type');
			//  alert(el.prop('name')+'-'+el.prop('type'));
          switch (type) {
            case 'text':
               if (el.prop('id')=='userid'){
			    }
			  else {
				el.val(val);
				}
			  break;
			 case 'textarea':
               	el.val(val);
			   break;	
			case 'hidden':
               if (el.prop('id')=='userid'){
			    }
			  else {
				el.val(val);
				}
				break;	
			
			case 'checkbox':
				if (val=="1"){

					el.prop('checked',true);
				}else{
				 el.prop('checked',false);
				}
                break;
            case 'radio':
                //$el.filter('[value="' + val + '"]').attr('checked', 'checked');
                break;
            case 'select-one':
                	var  clas=el.prop('class');
					if (clas.indexOf("select2") >= 0){
					  el.val(val).change();
					  }
					 else {
					   el.val(val);
					 }
					 break;      
					 
			default:
			   // if (el.tagName=='TEXTAREA') {
			   // 	 el.val(val);
			   // }
			  break;	
		 }
     });
 }

function EntryState(status)
			{
			  if (status==false){	
		 	    $("#frm-entry").fadeOut(); 	
			    $("#frm-list").fadeIn();}
			  else {
				  $("#frm-list").fadeOut();
	 			 $("#frm-entry").fadeIn();
			  }			 
}


function clearForm(myForm) {
    $(myForm + ' input,'+ myForm + ' select,'+ myForm + ' textarea' ).each(
		function(index){
			var el=$(this);
			var type=el.prop('type').toLowerCase();
			var clas=el.prop('class');
			//alert(el.prop('name')+'-'+el.prop('type')+'-'+el.prop('class'));
		    switch(type) {
		
			case "text":
			    if (el.prop('id')=='userid'){
			  }
				else {	  
		          el.val("").removeAttr( "disabled" );
				}
			  break;
			case "password":
			  el.val("").removeAttr( "disabled" );
			  break;
			case "textarea":
			 
			  el.val("").removeAttr( "disabled" );
			  break;
			case "hidden":
			  //alert(el.prop('id'));
			  if (el.prop('id')=='userid'){
			  }
				else {	  
		          el.val("").removeAttr( "disabled" );
				}
			   break;
			case "radio":
			case "checkbox":
				if (el.checked) {
				  el.checked = false;
			  }
			  break;
		
			case "select-one":
					if (clas.indexOf("select2") >= 0){
					  el.val("").removeAttr( "disabled" ).change();}
					 else {
					   el.val("").removeAttr( "disabled" );
					 }
					 break;
			case "select-multi":
					//	el.selectedIndex = -1;
			  break;
		
			case "button":
			 break;
			default:
			  // if (el.tagName=='TEXTAREA') {
			  //  	el.val("").removeAttr( "disabled" );
			  //  }
			   //el.val("").removeAttr( "disabled" );
			  break;
 		  }
		
		});
}

function eRowData()
{				
		    table = $('#listTable').DataTable();
			var a=table.rows('.selected').data().length;
			//alert( table.rows('.selected').data().length);
			if( a>0)
			  {
			    var d=table.row('.selected').data();
			    a=( d[0] );
			  }
			  else {//return( a );
			   a=-1;
			   alert("Please Select a Row!")
			   waitingDialog.hide();
			  }
			  return a;
}


function showModals_( id ,state,vurl)			
	{
				//alert($('#userid').val());
				waitingDialog.show();
				clearData();
				if( id )
				{
					$.ajax({
						type: "POST",
						//url: "http://10.20.24.9:8080/datatablesku/crud.php",
						url: vurl,
						dataType: 'json',
						data: {id:id,type:"get"},
						success: function(res) {
						setModalData_( res,state );
						}
					});
				}
				else
				{
					EntryState(true);
					$("#status-label").html('<span class="label label-info">New</span>');
					$("#btnSaved .labelbutton").text('Save');
					$("#type").val("new"); 
					waitingDialog.hide();
				}
			}
			
	function setModalData_( data,state )
		{
				if (state=='edit'){
				 $("#status-label").html('<span class="label label-info">Edit</span>');}
				else{$("#status-label").html('<span class="label label-info">Delete</span>');
				}
				$("#type").val(state);
				fillJsonData(data);
				if (state=='edit'){
				    $("#btnSaved .labelbutton").text('Save');
				  }
				else {
				   $("#btnSaved .labelbutton").text('Delete');
				   $("#removeWarning").show();
				  }						
				  //show_picture(data.img_file);
				  EntryState(true);
				  waitingDialog.hide();
			}
			

function clearData_(){
				$("#removeWarning").hide();
				 clearForm("#form-data");
				 $("#frm-entry").validate().resetForm();
				 $("#frm-entry").removeClass("has-error");
}

function submitData_(vurl,vdata){
					var formData = vdata
					waitingDialog.show();
									$.ajax({
										type: "POST",
										url: vurl,
										dataType: 'json',										
										data: formData,
										success: function(data) {
																		
											if (data.status == "OK"){
													if (data.param1) {
														upload_picture(data.id);
													     
													}
												swal("", data.message, "success");
												EntryState(false);
												ListTable.ajax.reload(); 
												waitingDialog.hide();
												
											 }
											else{
											  swal("", data.message, "error"); 
											  waitingDialog.hide();
											}
										}
									});
 }
 
 // Ajax Sincrhonous ======
 function getdata_by_id(id,tblname)			
	{
				var result;
					$.ajax({
						type: "POST",
						async: false,
						//cache: false,
						url: "../../fwkClass/fwk_getdata.php",
						dataType: 'json',
						data: {id:id,tblname:tblname},
						success: function(res) {
						  //alert(res[0].credit_days);
						  result=res;
						}
					});


			return result;		
	 }

function getdata(criteria,tblname)			
	{
				var result;
					$.ajax({
						type: "POST",
						async: false,
						//cache: false,
						url: "../../fwkClass/fwk_getdata_by_criteria.php",
						dataType: 'json',
						data: {criteria:criteria,tblname:tblname},
						success: function(res) {
						  //alert(res[0].credit_days);
						  result=res;
						}
					});


			return result;		
	 } 
	 



	  function getid(vurl)			
	{
				var result;
					$.ajax({
						type: "POST",
						async: false,
						//cache: false,
						url: vurl,
						dataType: 'json',
						data: {type:'getid'},
						success: function(res) {
						  result=res.uoid;
						}
					});


			return result;		
	 }


 function Created_lookupItem(){

var vhtml="";
//vhtml = "    div class='modal fade' id='myModals' tabindex='-1' role='dialog' aria-labelledby='myModalLabel' aria-hidden='true'>";
vhtml +="			<div class='modal-dialog'>";
vhtml +="				<div class='modal-content'>";
vhtml +="					<div class='modal-header'>";
vhtml +="					    <input type='hidden' class='form-control' id='idx' text=''>";
vhtml +="						<button type='button' class='close' data-dismiss='modal' aria-label='Close'><span aria-hidden='true'>&times;</span></button>";
vhtml +="						<h4 class='modal-title' id='myModalLabel'>Add Item</h4>";
vhtml +="					</div>";
vhtml +="					<div class='modal-body'>	";				    
vhtml +="						<table id='ItemTable'  cellpadding='0' cellspacing='0' border='0' class='display' width='100%'>";
vhtml +="						<thead>";
vhtml +="							<tr>";
vhtml +="							    <th>id</th>";
vhtml +="								<th>ItemCode</th>";
vhtml +="								<th>ItemName</th>";
vhtml +="								<th>Uom</th>";
vhtml +="							</tr>";
vhtml +="						</thead>";
vhtml +="						<tbody id='itemlookup'>";
vhtml +="						</tbody>";
vhtml +="					  </table>	";											
vhtml +="					</div>";
vhtml +="					<div class='modal-footer'>";
vhtml +="						<button type='button' onClick='SetData()' class='btn btn-default' data-dismiss='modal'>Ok</button>";
vhtml +="						<button type='button' class='btn btn-default' data-dismiss='modal'>Close</button>";
vhtml +="					</div>";
vhtml +="				</div>";
vhtml +="			</div>";
//vhtml +="		</div> ";


$("#lookup_item").html(vhtml);

ItemTable = $('#ItemTable').DataTable( {
					"processing": true,
					"serverSide": true,
					 "ajax": {
					 "url": "item_list.php",
					//"type": "POST",
					 "data": function ( data ) {
					          //data.customer = cutomer;
							  //data.t2 = $('#tanggal2').val();
							  //data.type_sla = $('#sla_type_filter option:selected').text();						
						}
					},
					
					"columnDefs": [
					{ "targets": 0, "visible": false }
					]
				} );

				
				$('#ItemTable tbody').on( 'click', 'tr', function () {
					if ( $(this).hasClass('selected') ) {
						$(this).removeClass('selected');
					}
					else {
						ItemTable.$('tr.selected').removeClass('selected');
						$(this).addClass('selected');
					}
				} );
			
				$('#ItemTable').removeClass( 'display' ).addClass('table table-striped table-bordered');				
				


 }

 function Created_lookupAR(){

var vhtml="";
//vhtml = "    div class='modal fade' id='myModals' tabindex='-1' role='dialog' aria-labelledby='myModalLabel' aria-hidden='true'>";
vhtml +="			<div class='modal-dialog'>";
vhtml +="				<div class='modal-content'>";
vhtml +="					<div class='modal-header'>";
vhtml +="					    <input type='hidden' class='form-control' id='idx' text=''>";
vhtml +="						<button type='button' class='close' data-dismiss='modal' aria-label='Close'><span aria-hidden='true'>&times;</span></button>";
vhtml +="						<h4 class='modal-title' id='myModalLabel'>Add Item</h4>";
vhtml +="					</div>";
vhtml +="					<div class='modal-body'>	";				    
vhtml +="						<table id='ItemTable'  cellpadding='0' cellspacing='0' border='0' class='display' width='100%'>";
vhtml +="						<thead>";
vhtml +="							<tr>";
vhtml +="							    <th>id</th>";
vhtml +="								<th>Tr No</th>";
vhtml +="								<th>Tr Date</th>";
vhtml +="								<th>Inv No</th>";
vhtml +="								<th>Inv Date</th>";
vhtml +="								<th>Cust</th>";
vhtml +="								<th>Currency</th>";
vhtml +="								<th>Amount</th>";
vhtml +="							</tr>";
vhtml +="						</thead>";
vhtml +="						<tbody id='itemlookup'>";
vhtml +="						</tbody>";
vhtml +="					  </table>	";											
vhtml +="					</div>";
vhtml +="					<div class='modal-footer'>";
vhtml +="						<button type='button' onClick='SetData()' class='btn btn-default' data-dismiss='modal'>Ok</button>";
vhtml +="						<button type='button' class='btn btn-default' data-dismiss='modal'>Close</button>";
vhtml +="					</div>";
vhtml +="				</div>";
vhtml +="			</div>";
//vhtml +="		</div> ";


$("#lookup_item").html(vhtml);

ItemTable = $('#ItemTable').DataTable( {
					"processing": true,
					"serverSide": true,
					 "ajax": {
					 "url": "ar_list.php",
					//"type": "POST",
					 "data": function ( data ) {
					          //data.t1 = $('#tanggal1').val();
							  //data.t2 = $('#tanggal2').val();
							  //data.type_sla = $('#sla_type_filter option:selected').text();						
						}
					},
					
					"columnDefs": [
					{ "targets": 0, "visible": false }
					]
				} );

				
				$('#ItemTable tbody').on( 'click', 'tr', function () {
					if ( $(this).hasClass('selected') ) {
						$(this).removeClass('selected');
					}
					else {
						ItemTable.$('tr.selected').removeClass('selected');
						$(this).addClass('selected');
					}
				} );
			
				$('#ItemTable').removeClass( 'display' ).addClass('table table-striped table-bordered');				
				


 }

 function Created_lookupAP(){

var vhtml="";
//vhtml = "    div class='modal fade' id='myModals' tabindex='-1' role='dialog' aria-labelledby='myModalLabel' aria-hidden='true'>";
vhtml +="			<div class='modal-dialog'>";
vhtml +="				<div class='modal-content'>";
vhtml +="					<div class='modal-header'>";
vhtml +="					    <input type='hidden' class='form-control' id='idx' text=''>";
vhtml +="						<button type='button' class='close' data-dismiss='modal' aria-label='Close'><span aria-hidden='true'>&times;</span></button>";
vhtml +="						<h4 class='modal-title' id='myModalLabel'>Add Item</h4>";
vhtml +="					</div>";
vhtml +="					<div class='modal-body'>	";				    
vhtml +="						<table id='ItemTable'  cellpadding='0' cellspacing='0' border='0' class='display' width='100%'>";
vhtml +="						<thead>";
vhtml +="							<tr>";
vhtml +="							    <th>id</th>";
vhtml +="								<th>Tr No</th>";
vhtml +="								<th>Tr Date</th>";
vhtml +="								<th>Inv No</th>";
vhtml +="								<th>Inv Date</th>";
vhtml +="								<th>Supp</th>";
vhtml +="								<th>Currency</th>";
vhtml +="								<th>Amount</th>";
vhtml +="							</tr>";
vhtml +="						</thead>";
vhtml +="						<tbody id='itemlookup'>";
vhtml +="						</tbody>";
vhtml +="					  </table>	";											
vhtml +="					</div>";
vhtml +="					<div class='modal-footer'>";
vhtml +="						<button type='button' onClick='SetData()' class='btn btn-default' data-dismiss='modal'>Ok</button>";
vhtml +="						<button type='button' class='btn btn-default' data-dismiss='modal'>Close</button>";
vhtml +="					</div>";
vhtml +="				</div>";
vhtml +="			</div>";
//vhtml +="		</div> ";


$("#lookup_item").html(vhtml);

ItemTable = $('#ItemTable').DataTable( {
					"processing": true,
					"serverSide": true,
					 "ajax": {
					 "url": "ap_list.php",
					//"type": "POST",
					 "data": function ( data ) {
					          //data.t1 = $('#tanggal1').val();
							  //data.t2 = $('#tanggal2').val();
							  //data.type_sla = $('#sla_type_filter option:selected').text();						
						}
					},
					
					"columnDefs": [
					{ "targets": 0, "visible": false }
					]
				} );

				
				$('#ItemTable tbody').on( 'click', 'tr', function () {
					if ( $(this).hasClass('selected') ) {
						$(this).removeClass('selected');
					}
					else {
						ItemTable.$('tr.selected').removeClass('selected');
						$(this).addClass('selected');
					}
				} );
			
				$('#ItemTable').removeClass( 'display' ).addClass('table table-striped table-bordered');				
				


 }



 $(function(){

  $('.number-only').keypress(function(e) {
	if(isNaN(this.value+""+String.fromCharCode(e.charCode))) return false;
  })
  .on("cut copy paste",function(e){
	e.preventDefault();
  });

});
 jQuery('.numbersOnly').keyup(function () { 
    this.value = this.value.replace(/[^0-9\.]/g,'');
});


//  $(document).ready(function(){
//   $('input.number').keyup(function(event){
//       // skip for arrow keys
//       if(event.which >= 37 && event.which <= 40){
//           event.preventDefault();
//       }
      
//       console.log(num2);
//       var $this = $(this);
//       var num = $this.val().replace(/,/gi, "").split("").reverse().join("");
      
//       var num2 = RemoveRougeChar(num.replace(/(.{3})/g,"$1,").split("").reverse().join(""));
      
      
//       // the following line has been simplified. Revision history contains original.
//       $this.val(num2);
//   });
// });

function RemoveRougeChar(convertString){
    
    
    if(convertString.substring(0,1) == ","){
        
        return convertString.substring(1, convertString.length)            
        
    }
    return convertString;
    
}
function clear_autonumeric(){
	//var form = $(this);
    $('input').each(function(i){
        var self = $(this);
        try{
            var v = self.autoNumeric('get');
            //self.autoNumeric('destroy');
            //self.removeAttr('data-autonumeric');
            //self.removeData('autonumeric');
            self.val(v);
        }catch(err){
            console.log("Not an autonumeric field: " + self.attr("name"));
        }
    });
}

function isEmpty(value) {
  return typeof value == 'string' && !value.trim() || typeof value == 'undefined' || value === null;
}

// variable tanggal
function getDate1(vdate){

	var dsplit=vdate.split("-");
	//month=parseInt(dsplit[1],10)-1;
	//day=parseInt(dsplit[2],10);
	//year=parsInt(dsplit[0],10);
	month=dsplit[1]-1;
	day=dsplit[2];
	year=dsplit[0];
	d= new Date(year,month,day);
	return d;
}

// variable string
function fmtDate1(vdate){
	month=(vdate.getMonth()+1).toString();
	day=vdate.getDate().toString();
	year=vdate.getFullYear().toString();
	if (month.length<2)month='0'+month;
	if (day.length<2)day='0'+day;
	return [year,month,day].join("-");
	//return (vdate.getFullYear().toString()+"/"+(vdate.getMonth()+1).toString()+"/"+vdate.getDate().toString());
}