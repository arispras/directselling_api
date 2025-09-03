<!DOCTYPE html>
<html>

<head>
	<meta charset="utf-8">
	<title>Map with range selection for areas</title>
	<style type="text/css">
		.modal-body {
			max-height: calc(100vh - 210px);
			overflow-y: auto;
		}

		body {
			color: #5d5d5d;
			font-family: Helvetica, Arial, sans-serif;
		}

		h1 {
			font-size: 30px;
			margin: auto;
			margin-top: 50px;
		}

		/*.container {
            max-width: 800px;
            margin: auto;
        }*/


		.container {
			width: 90%;
			overflow: hidden;
			min-width: 700px;
			max-width: 1200px;
			margin-left: auto;
			margin-right: auto;
		}

		.slider {
			margin: 20px 20px 60px 20px;
		}

		/* Specific mapael css class are below
         * 'mapael' class is added by plugin
        */

		/*.mapael .map {
            position: relative;
        }
*/

		.mapael .map {
			margin-right: 228px;
			overflow: hidden;
			position: relative;
			/*background-color: #232323;*/
			background-color: #2f4f4f;
			border-radius: 5px;
		}

		.mapael .mapTooltip {
			position: absolute;
			background-color: #fff;
			moz-opacity: 0.70;
			opacity: 0.70;
			filter: alpha(opacity=70);
			border-radius: 10px;
			padding: 10px;
			z-index: 1000;
			max-width: 200px;
			display: none;
			color: #343434;
		}

		/* For all zoom buttons */
		.mapael .zoomButton {
			background-color: #fff;
			border: 1px solid #ccc;
			color: #000;
			width: 15px;
			height: 15px;
			line-height: 15px;
			text-align: center;
			border-radius: 3px;
			cursor: pointer;
			position: absolute;
			top: 0;
			font-weight: bold;
			left: 10px;

			-webkit-user-select: none;
			-khtml-user-select: none;
			-moz-user-select: none;
			-o-user-select: none;
			user-select: none;
		}

		/* Reset Zoom button first */
		.mapael .zoomReset {
			top: 10px;
		}

		/* Then Zoom In button */
		.mapael .zoomIn {
			top: 30px;
		}

		/* Then Zoom Out button */
		.mapael .zoomOut {
			top: 50px;
		}

		.rightPanel {
			float: right;
			width: 223px;
			border-radius: 5px;
			margin-left: 5px;
		}
	</style>

	<!-- <link href="../../plugins/mapael/js/nouislider.min.css" rel="stylesheet">

    <script src="../../plugins/mapael/js/nouislider.min.js" charset="utf-8"></script>

    <script src="../../plugins/jQuery/jquery-2.2.3.min.js"></script>  
    <link rel="stylesheet" href="../../bootstrap/css/bootstrap.min.css">
       <script src="../../bootstrap/js/bootstrap.min.js"></script>      
     <script type="text/javascript" language="javascript" src="../../plugins/jQuery/common.js"></script>   
    <script src="../../plugins/mapael/js/jquery.mousewheel.min.js"
            charset="utf-8"></script>
    <script src="../../plugins/mapael/js/raphael.min.js" charset="utf-8"></script>
    <script src="../../plugins/mapael/js/jquery.mapael.js" charset="utf-8"></script>
    <script src="../../plugins/mapael/js/maps/sbne.js" charset="utf-8"></script>
    <script src="../../plugins/jQueryUI/jquery-ui.min.js"></script> 
            <script type="text/javascript" language="javascript" src="../../plugins/select2/select2.full.min.js"></script>  
        <link rel="stylesheet" type="text/css" href="../../plugins/select2/select2.min.css">
        <script type="text/javascript" src="../../fwkClass/JS/fwkglobal.js"></script>
   
    <link rel="stylesheet" type="text/css" href="../../plugins/jQueryUI/jquery-ui.css">

     <link rel="stylesheet" type="text/css" href="../../plugins/datatables/dataTables.bootstrap.css"> -->

	<link href="<?php echo base_url(); ?>plugins/mapael/js/nouislider.min.css" rel="stylesheet">
	<script src="<?php echo base_url(); ?>plugins/mapael/js/nouislider.min.js" charset="utf-8"></script>
	<script src="<?php echo base_url(); ?>plugins/jQuery/jquery-2.2.3.min.js"></script>
	<link rel="stylesheet" href="<?php echo base_url(); ?>plugins/bootstrap/css/bootstrap.min.css">
	<script src="<?php echo base_url(); ?>plugins/bootstrap/js/bootstrap.min.js"></script>
	<script type="text/javascript" language="javascript" src="<?php echo base_url(); ?>plugins/jQuery/common.js"></script>

	<script src="<?php echo base_url(); ?>plugins/mapael/js/jquery.mousewheel.min.js" charset="utf-8"></script>
	<script src="<?php echo base_url(); ?>plugins/mapael/js/raphael.min.js" charset="utf-8"></script>
	<script src="<?php echo base_url(); ?>plugins/mapael/js/jquery.mapael.js" charset="utf-8"></script>
	<!-- <script src="<?php echo base_url(); ?>plugins/mapael/js/maps/sbne.js" charset="utf-8"></script> -->
	<script src="<?php echo base_url(); ?>plugins/mapael/js/maps/sbne.js" charset="utf-8"></script>
	<script src="<?php echo base_url(); ?>plugins/jQueryUI/jquery-ui.min.js"></script>
	<script type="text/javascript" language="javascript" src="<?php echo base_url(); ?>plugins/select2/select2.full.min.js"></script>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>plugins/select2/select2.min.css">
	<script type="text/javascript" src="<?php echo base_url(); ?>plugins/fwkglobal.js"></script>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>plugins/jQueryUI/jquery-ui.css">
	<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>plugins/datatables/dataTables.bootstrap.css">
	<script type="text/javascript">
		$(document).ready(function() {
			$('#btnRefresh').click(function() {

				varperiode = $('#periode').val();
				getdata(varperiode);

			});
			$('#param').hide();
			// fillcombo($('#periode'),'distinct periode','periode','setup_periodeakuntansi','' );
			$('#periode').empty();
			$('#periode').append($('<option>').text("<Select>").attr('value', ""));
			// $('#periode').append($('<option>').text('Agustus 2022').attr('value', '2022-08'));
			// $('#periode').append($('<option>').text('September 2022').attr('value', '2022-09'));
			// $('#periode').append($('<option>').text('Oktober 2022').attr('value', '2022-10'));
			// $('#periode').append($('<option>').text('November 2022').attr('value', '2022-11'));
			// $('#periode').append($('<option>').text('Desember 2022').attr('value', '2022-12'));
			$('#periode').append($('<option>').text('Januari 2023').attr('value', '2023-01'));
			$('#periode').append($('<option>').text('Februari 2023').attr('value', '2023-02'));
			$('#periode').append($('<option>').text('Maret 2023').attr('value', '2023-03'));
			$('#periode').append($('<option>').text('April 2023').attr('value', '2023-04'));
			$('#periode').append($('<option>').text('Mei 2023').attr('value', '2023-05'));
			$('#periode').append($('<option>').text('Juni 2023').attr('value', '2023-06'));
			$('#periode').append($('<option>').text('Juli 2023').attr('value', '2023-07'));
			$('#periode').append($('<option>').text('Agustus 2023').attr('value', '2023-08'));
			$('#periode').append($('<option>').text('September 2023').attr('value', '2023-09'));
			$('#periode').append($('<option>').text('Oktober 2023').attr('value', '2023-10'));
			$('#periode').append($('<option>').text('November 2023').attr('value', '2023-11'));
			$('#periode').append($('<option>').text('Desember 2023').attr('value', '2023-12'));

			// $.each(data, function(i, obj){
			// 		combobox.append($('<option>').text(obj.text).attr('value', obj.id));
			// 	});	
			// }

			$('#periode').select2({
				width: '100%',
				placeholder: "Bulan ini",
				allowClear: true
			});
			$('#content-detail').hide();
			$('#content-detail').dialog({
				autoOpen: false,
				buttons: {
					OK: function() {
						$(this).dialog("close");
					}
				},
				title: "Rincian Blok",
				hide: "puff",
				show: "slide",
				height: 500,
				width: 500,
				position: {
					my: "center",
					at: "top"
				}
			});
			getdata();
			$('#param').show();
		});

		function getdata(periode) {
			periode = (periode !== undefined) ? periode : "";
			waitingDialog.show();
			$.getJSON("<?php echo base_url(); ?>api/mapPemupukanSbne/retrieve_data?periode=" + periode, function(data) {
				// Success
				var areas = {};
				$.each(data, function(id, elem) {
					//alert (elem.kodeblok);
					var fmtkg = elem.kg;
					kilogram = Number.parseFloat(fmtkg).toFixed(2);
					var fmtyph = elem.yph;
					yph = Number.parseFloat(fmtyph).toFixed(2);
					//fmtkg.toFixed(2);
					var txt = elem.kodeblok;
					var txtlabel = txt.substr(txt.length - 4);
					var area = {};
					//area.value = elem.jumlahpokok;
					area.value = elem.kg;
					area.href = "#";
					area.tooltip = {
						content: "<span style='font-weight:bold;'>" +
							elem.kodeblok +
							"</span>" +
							"<br/>SPH: " + elem.sph +
							"<br/>Produksi(Kg): " + (kilogram) +
							"<br/>Produksi(Jjg): " + elem.jjg +
							"<br/>YPH: " + yph
					};
					area.text = {
						content: txtlabel,
						attrs: {
							"font-size": 8
						}
					}
					areas[elem.kodeblok] = area;
				});

				// Create map
				// ==============  START MAP  ==========================================================================
				$sbne = $(".sbne");
				$sbne.mapael({
					map: {
						name: "sbne",
						zoom: {
							enabled: true,
							maxLevel: 10
						},
						defaultArea: {
							attrs: {
								stroke: "#fff",
								"stroke-width": 1
							},
							attrsHover: {
								"stroke-width": 2
							},
							eventHandlers: {
								click: function(e, id, mapElem, textElem) {
									varperiode = $('#periode').val();
									showdetail(id, varperiode);
								}
							}
						}

					},

					legend: {
						area: {
							title: "Produksi (Kg)",
							marginBottom: 7,
							slices: [{
									max: 0,
									attrs: {
										fill: "#8B0000"
									},
									label: "Tidak ada Panen"
								},
								{
									min: 1,
									max: 10000.0001,
									attrs: {
										fill: "#97e766"
									},
									label: " 1 s/d 10000"
								},
								{
									min: 10001,
									max: 29999.9999,
									attrs: {
										fill: "#7fd34d"
									},
									label: "> 10000 and < 30000"
								},
								{
									min: 30001,
									max: 49999.9999,
									attrs: {
										fill: "#5faa32"
									},
									label: "> 30000  and < 50000 "
								},
								{
									min: 50000,
									attrs: {
										fill: "#3f7d1a"
									},
									label: ">= 50000 "
								}
							]
						}
					},
					areas: areas
				});

				slider = noUiSlider.create($(".slider")[0], {
					start: [0, 100000],
					step: 100,
					connect: true,
					orientation: 'horizontal',
					range: {
						'min': 0,
						'max': 100000
					},
					pips: {
						mode: 'range',
						density: 2
					}
				});

				slider.on('set', function(values) {
					var opt = {
						animDuration: 500,
						hiddenOpacity: 0.1,
						ranges: {
							area: {
								min: parseInt(values[0]),
								max: parseInt(values[1])
							}
						}
					};
					$(".mapcontainer").trigger("showElementsInRange", [opt]);
					$(".values").text(" Area dengan Produksi(Kg) antara " + parseInt(values[0]) + " dan " + parseInt(values[1]) + " ");
				});

				$(slider).trigger("set");

			});

			// ======================== END MAP =================================================================   


			function showdetail(blok, periode) {
				periode = (periode !== undefined) ? periode : "";
				waitingDialog.show();
				$('iframe').attr('src', '<?php echo base_url(); ?>api/mapPemupukanSbne/show_detail?blok=' + blok + '&periode=' + periode);
				$('#content-detail').dialog('open');
				waitingDialog.hide();
			}

			/* }).fail(function() {
			     // Error
			     $(".mapcontainer span").html("Failed to load JSON data").css({"color":"red"});*/

			waitingDialog.hide();


		}
	</script>

</head>

<body>


	<div class="container">
		<h3>MAP BERDASARKAN PEMUPUKAN SBNE</h3>

		<div class="sbne">
			<div class="rightPanel">
				<div class="slider"> </div>
				<p class="values"></p>
				<div class="mapcontainer">
					<div class="areaLegend"></div>
					<div class="plotLegend"></div>
				</div>
				<br><br>
				<div id="param" class="form-group">
					<label for="periode" class="col-sm-4 control-label">Periode:</label>
					<div class="col-sm-8">
						<select id="periode" name="periode" class="periode"> </select>
						<button id="btnRefresh" type="button" class="btn btn-primary btn-sm">
							<span class="glyphicon glyphicon-refresh"></span><span class="labelbutton">Refresh Data</span>
						</button>
					</div>
					<div class="col-sm-8">
					</div>
				</div>
			</div>
			<div class="map"> <span>Wait ... </span></div>
			<div style="clear: both;"></div>
		</div>

	</div>


	<div id="content-detail">
		<iframe src="" height="100%" width="100%"></iframe>
	</div>
</body>

</html>
