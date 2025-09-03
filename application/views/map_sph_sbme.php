<!DOCTYPE html>
<html>

<head>
	<meta charset="utf-8">
	<title>Map</title>
	<style type="text/css">
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
			background-color: #00FFFF;
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

	<link href="<?php echo base_url(); ?>plugins/mapael/js/nouislider.min.css" rel="stylesheet">
	<script src="<?php echo base_url(); ?>plugins/mapael/js/nouislider.min.js" charset="utf-8"></script>
	<script src="<?php echo base_url(); ?>plugins/jQuery/jquery-2.2.3.min.js"></script>
	<script src="<?php echo base_url(); ?>plugins/mapael/js/jquery.mousewheel.min.js" charset="utf-8"></script>
	<script src="<?php echo base_url(); ?>plugins/mapael/js/raphael.min.js" charset="utf-8"></script>
	<script src="<?php echo base_url(); ?>plugins/mapael/js/jquery.mapael.js" charset="utf-8"></script>
	<script src="<?php echo base_url(); ?>plugins/mapael/js/maps/sbme.js" charset="utf-8"></script>
	<script src="<?php echo base_url(); ?>plugins/jQueryUI/jquery-ui.min.js"></script>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>plugins/jQueryUI/jquery-ui.css">

	<script type="text/javascript">
		$(function() {
			$.getJSON("<?php echo base_url(); ?>api/mapSph/retrieve_data", function(t) {
				var e = {};
				$.each(t, function(t, a) {

					var n = a.kodeblok,
						o = n.substr(n.length - 4),
						i = {};
					console.log(a);
					i.value = a.sph, i.href = "#", i.tooltip = {
						content: "<span style='font-weight:bold;'>" + a.kodeblok + "</span><br/>SPH: " + a.sph + "<br/>Status: " + a.statusblok
					}, i.text = {
						content: o,
						attrs: {
							"font-size": 8
						}
					}, e[a.kodeblok] = i
				}), $sbme = $(".sbme"), $sbme.mapael({
					map: {
						name: "sbme",
						zoom: {
							enabled: !0,
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
								click: function(t, e, a, n) {
									var o;
									o = e, $("iframe").attr("src", "<?php echo base_url(); ?>api/mapSph/show_detail?blok=" + o), $("#content-detail").dialog("open")
								}
							}
						}
					},
					legend: {
						area: {
							title: "SPH",
							marginBottom: 7,

							slices: [

								{

									max: 34,
									attrs: {
										fill: "#ff1a1a"
									},
									label: " =< 34 "
								},
								{
									min: 34.001,
									max: 68,
									attrs: {
										fill: "#ff751a"
									},
									label: ">= 34.1 and <=68"
								},
								{
									min: 68.001,
									max: 102,
									attrs: {
										fill: "#ffd11a"
									},
									label: ">= 68.1 and <=102"
								},
								{
									min: 102.001,
									max: 136,
									attrs: {
										fill: "#00e600"
									},
									label: ">= 102.1 and <=136"
								},

								{
									min: 136.001,
									attrs: {
										fill: "#008000"
									},
									label: "> 136 "
								}
							]

						}
					},
					areas: e
				}), slider = noUiSlider.create($(".slider")[0], {
					start: [0, 300],
					step: 5,
					connect: !0,
					orientation: "horizontal",
					range: {
						min: 0,
						max: 300
					},
					pips: {
						mode: "range",
						density: 2
					}
				}), slider.on("set", function(t) {
					var e = {
						animDuration: 500,
						hiddenOpacity: .1,
						ranges: {
							area: {
								min: parseInt(t[0]),
								max: parseInt(t[1])
							}
						}
					};
					$(".mapcontainer").trigger("showElementsInRange", [e]), $(".values").text(" Area dengan SPH antara " + parseInt(t[0]) + " dan " + parseInt(t[1]) + " ")
				}), $(slider).trigger("set")
			}), $("#content-detail").hide(), $("#content-detail").dialog({
				autoOpen: !1,
				buttons: {
					OK: function() {
						$(this).dialog("close")
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
			})
		}).fail(function() {
			console.log('error');
			$(".mapcontainer span").html("Failed to load JSON data").css({
				color: "red"
			})
		})
		;
	</script>

</head>

<body>
	<!-- <div class="container">

    <h1>MAPS PT. BIMA PALMA NUGRAHA  BERDASARKAN POKOK</h1>

    <div class="slider">
    </div>

    <p class="values"></p>

    <div class="mapcontainer">
        <div class="map">
            <span>Wait....</span>
        </div>
        <div class="areaLegend">
            <span></span>
        </div>
    </div>

 
</div> -->

	<div class="container">
		<h3>PETA BLOK SPH SBME</h3>

		<div class="sbme">
			<div class="rightPanel">

				<div class="slider">

				</div>
				<p class="values"></p>
				<div class="mapcontainer">
					<div class="areaLegend"></div>
					<div class="plotLegend"></div>
				</div>
			</div>
			<div class="map"></div>
			<div style="clear: both;"></div>
		</div>

	</div>


	<div id="content-detail">
		<iframe src="" height="100%" width="100%"></iframe>
	</div>
</body>

</html>
