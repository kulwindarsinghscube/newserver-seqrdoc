@extends('admin.layout.canvaslayout')
@section('style')
	<link rel="stylesheet" href="{{asset('backend/canvas/css/canvasmaker.css')}}">
	<style type="text/css">
		
		<?php

			$domain =$_SERVER['HTTP_HOST'];
			$subdomain = explode('.', $domain);
			//$domain_name = $subdomain[0];
			$domain_name = "";
			if(isset($FONTS)){
				foreach ($FONTS as $key => $font) {
					$fname = $font['font_filename'];
					echo "@font-face {" ;
					echo "font-family: '{$fname}';";
					echo "src: url('https://{$domain}/{$domain_name}/backend/canvas/fonts/{$font['font_filename_N']}') format('truetype')";
					echo "}";
					if($font['font_filename_B'] != '') {
						/*
						 * Style Bold
						 */
						$fname = str_replace(' ', '_', $font['font_name']) . '_B';
						echo "@font-face {";
						echo "font-family: '{$fname}';";
						echo "src: url('https://{$domain}/{$domain_name}/backend/canvas/fonts/{$font['font_filename_B']}') format('truetype')";
						echo "font-weight: bold;";
						echo "}";
					}
					if($font['font_filename_I'] != '') {
						/*
						 * Style Italic
						 */
						$fname = str_replace(' ', '_', $font['font_name']) . '_I';
						echo "@font-face {";
						echo "font-family: '{$fname}';";
						echo "src: url('../../../backend/canvas/fonts/{$font['font_filename_I']}') format('truetype')";
						echo "font-weight: bold;";
						echo "}";
					}
					if($font['font_filename_BI'] != '') {
						/*
						 * Style Italic
						 */
						$fname = str_replace(' ', '_', $font['font_name']) . '_BI';
						echo "@font-face {";
						echo "font-family: '{$fname}';";
						echo "src: url('../../../backend/canvas/fonts/{$font['font_filename_BI']}') format('truetype')";
						echo "font-weight: bold;";
						echo "}";
					}
				}
			}
		?>
	</style>
@stop
<body>
	<div>
	    <canvas class="certClass" id="certCanvas" height="990" width="700" style="border : solid 1px black">		
		</canvas>

	</div>
	
</body>


@section('script')
	<script type="text/javascript">
	
	console.log('iframe')
	//get fonts 
	var fonts = '<?= $FONTS?>'
	console.log(fonts);
	var fontList = JSON.parse(fonts)
	
	//get background template list
	var background_template = '<?= $BGTEMPLATE?>'
	var bgList = JSON.parse(background_template)
	console.log(bgList);
	//bg upload path
	var bg_upload_path  = '<?= $bg_upload_path?>';
	<?php
		if(isset($systemConfig['sandboxing']) && $systemConfig['sandboxing'] == 1){
	?>
		var config  = '<?= $config?>/sandbox';
    <?php
    	}
    	else{
    ?>
    	var config = '<?= $config?>';
    <?php
    	}
    ?> 
	var config_default  = '<?= $config_default?>';
	//console.log(config_default);
	var config_static  = '<?= $config_static?>';
	

	</script>
	<script type="text/javascript" src="{{asset('backend/canvas/js/postFrame.js')}}"></script>
	<script type="text/javascript" src="{{asset('backend/canvas/js/jquery.ruler.js')}}"></script>
	<script type="text/javascript">


		//make canvas
		var canvas = document.getElementById("certCanvas"),
		canvasOffset = { left:canvas.offsetLeft, top:canvas.offsetTop },
		context = canvas.getContext("2d"),
		offsetX = canvasOffset.left,
		offsetY = canvasOffset.top,
		clickCoords, clickCoordsX, clickCoordsY,
		objIndex = -1,
		lastX = 0, lastY = 0,            /* previous mouse coordinates */
		lastWidth = 0, lastHeight = 0,   /* relative mouse offset*/
		isDown = false,
		isCtrl = false,
		selObjId = -1,
		padding = 5,
		selObjNeedRedraw = false,
		draggingResizer = {
		    x: 0,
		    y: 0
		},
		imageWidth,imageHeight,imageRight,imageBottom,
		imageX,imageY,
		draggingImage = false,	
		/* ------------------------------------------------
		 * Security images
		 */
		bgImage = new Image(),
		qrImage = new Image(),
		idImage = new Image(),
		barcodeImage = new Image(),
		anticopyImage = new Image(),
		ghostImage = new Image(),
		qrImageNew = new Image(),
		qrCodeImage = new Image(),
		/*
		 * ------------------------------------------------
		 */

		certRatioX = canvas.width / 210,
		

		certRatioY = canvas.height / 297;
		
console.log(certRatioY);
		window.certRatioX = certRatioX;
		window.certRatioY = certRatioY;

		bgImage.src = "";
		qrImage.src = config_default+'/QR.png';
		idImage.src = config_default+'/ID.png';
		barcodeImage.src = config_default+'/barcode.png';
		anticopyImage.src = config_default+'/copy.png';
		ghostImage.src = config_default+'/ghost.png';

		var objList = [
			{objSrc: bgImage, objType: "image",security_type:'Bg',objDrag: false, objDelete: false, objID: null, objX: 0, objY: 0, objWidth: canvas.width, objHeight: canvas.height, objFontId: "", objFontSize: 0, objFontStyle: "", objFontColor: "000000", objText: "", objJustify: 'L', newX: 0, objRealWidth: 0, objRealHeight: 0},
			{objSrc: qrImage, objType: "Qrimage",security_type:'default QR',objDrag: true, objDelete: false, objID: 1, objX: 10, objY: 150, objWidth: 12 * certRatioX, objHeight: 12 * certRatioY, objFontId: "", objFontSize: 0, objFontStyle: "", objFontColor: "000000", objText: "", objJustify: 'L', newX: 0, objRealWidth: 0, objRealHeight: 0},
			{objSrc: idImage, objType: "image",security_type:'default ID', objDrag: true, objDelete: false, objID: 2, objX: 143 * certRatioX, objY: 35 * certRatioY, objWidth: 55 * certRatioX, objHeight: 12 * certRatioY, objFontId: "", objFontSize: 0, objFontStyle: "", objFontColor: "000000", objText: "", objJustify: 'L', newX: 0, objRealWidth: 0, objRealHeight: 0},
		];

		window.setBackground = function ($id = 0, $width, $height)
		{
			
			console.log($width);
			if($width == '420')
			{
				var h = ($height * (989 + 300)) / $width;
				canvas.width = 989 + 300;
			}else{
				var h = ($height * 700) / $width;

			}

			canvas.height = h;
			
			certRatioX = canvas.width / $width,
			certRatioY = canvas.height / $height;

			if($id == 0)
			{
				objList[0].objSrc = '';
			}
			else {
				bgList.forEach(function (item, i) {
					if(item.id == $id) {
						bgImage.src = bg_upload_path+'/' + item.image_path;
						objList[0].objSrc = bgImage;
					
						objList[0].objWidth =  canvas.width;
					
						objList[0].objHeight = canvas.height;
							
						bgImage.onload = function () {
							draw();
						}
					}
				});
			}
		}

		//for refreshing canvas call draw method that redraw canvas
		window.refreshCanvas = function () 
		{

			draw();
		}

		//display rulers for canvas body
		$(function() {
		    $('body').ruler({
		        container: document.querySelector('#certCanvas'),// reference to DOM element to apply rulers on
		        rulerHeight: 15, // thickness of ruler
		        fontFamily: 'arial',// font for points
		        fontSize: '7px', 
		        strokeStyle: 'black',
		        lineWidth: 1,
		        enableMouseTracking: false,
		        enableToolTip: true
		    });    
		});

		//inside canvas, on mousemove call mousemove method
		canvas.addEventListener('mousemove', function (event) {
			mouseMove(event);
		});

		// var ctrlArray = [];
		function mouseMove(event)
		{	
			var temp_index = event.view.objIndex;

			var mouseX, mouseY;
			mouseX = parseInt(event.pageX - offsetX);
		    mouseY = parseInt(event.pageY - offsetY);
		    
		   if(draggingResizer > -1 && draggingImage != false){
				
				var item = objList[objIndex];
				if(item != undefined){
				    switch (draggingResizer) {
				        case 0:
				            //top-left
				            item.objX = mouseX;
				            item.objWidth = imageRight - mouseX;
				            item.objY = mouseY;
				            item.objHeight = imageBottom - mouseY;
				            break;
				        case 1:
				            //top-right
				            item.objY = mouseY;
				            item.objWidth = mouseX - item.objX;
				            item.objHeight = imageBottom - mouseY;
				            break;
				        case 2:
				            //bottom-right
				            item.objWidth = mouseX - item.objX;
				            item.objHeight = mouseY - item.objY;
				            break;
				        case 3:
				            //bottom-left
				            item.objX = mouseX;
				            item.objWidth = imageRight - mouseX;
				            item.objHeight = mouseY - item.objY;
				            break;
				    }	

				    // set the image right and bottom
				    imageRight = item.objX + item.objWidth;
				    imageBottom = item.objY + item.objHeight;
		    		draw(true, false);
				}
			    // redraw the image with resizing anchors
			}else{

				selObjId = getSelectedObj(mouseX, mouseY);

			
				if(!isDown) {
					/* -----------------------------------------
					 * Check every object for hover effect
					 */

					selObjId = getSelectedObj(mouseX, mouseY);
					
					canvas.style.cursor= 'default';
					if(selObjId != "No")
					{
						
						
						selObjNeedRedraw = true;
						draw();
					} else if(selObjNeedRedraw) {

						
						
						
						draw();
					} 
					return;
				}
				var item = objList[objIndex];
				if(Math.abs(lastX - mouseX) > 2 || Math.abs(lastY - mouseY) > 2)
				{
					
					item.objX = mouseX - lastWidth;
					item.objY = mouseY - lastHeight;
					draw();
					lastX = mouseX;
					lastY = mouseY;
				}
			}
		}

		//for selecting object
		window.getSelectedObj = function ($mouseX, $mouseY)
		{

			var idx = 'No';

			objList.forEach(function (item, i) {
				
				
				if(item.objType == "image") {

					if(item.objDrag && $mouseX > item.objX && $mouseX < item.objX + item.objWidth && $mouseY > item.objY && $mouseY < item.objY + item.objHeight) {
						idx = i;
						return i ;
					}
				} else if(item.objType == "Idimage") {

					if(item.objDrag && $mouseX > item.objX && $mouseX < item.objX + item.objWidth && $mouseY > item.objY && $mouseY < item.objY + item.objHeight) {
						idx = i;
						return i ;
					}
				}else if(item.objType == "Qrimage") {

					if(item.objDrag && $mouseX > item.objX && $mouseX < item.objX + item.objWidth && $mouseY > item.objY && $mouseY < item.objY + item.objHeight) {
						idx = i;
						return i ;
					}
				}else if(item.objType == "qrCode"){

					if(item.objDrag && $mouseX > item.objX && $mouseX < item.objX + item.objWidth && $mouseY > item.objY && $mouseY < item.objY + item.objHeight) {
						idx = i;
						return i ;
					}
				} else if(item.objType == "text" && item.objJustify == "L") {
					if(item.objDrag && $mouseX > item.objX-padding && $mouseX < item.objX + item.objRealWidth+2*padding && $mouseY > item.objY-padding && $mouseY < item.objY + item.objRealHeight+2*padding) {
						idx = i;
						return i;
					}
				} else if(item.objType == "text" && item.objJustify == "C") {
					if(item.objDrag && $mouseX > item.newX-padding && $mouseX < item.newX + item.objRealWidth+2*padding && $mouseY > item.objY-padding && $mouseY < item.objY + item.objRealHeight+2*padding) {
						idx = i;
						return i ;
					}
				} else if(item.objType == "text" && item.objJustify == "R") {
					if(item.objDrag && $mouseX > item.newX-padding && $mouseX < item.objX + item.objRealWidth+2*padding && $mouseY > item.objY-padding && $mouseY < item.objY + item.objRealHeight+2*padding) {
						idx = i;
						return i ;
					}
				}else if(item.objType == "text" && item.objJustify == "J") {
					if(item.objDrag && $mouseX > item.newX-padding && $mouseX < item.objX + item.objRealWidth+2*padding && $mouseY > item.objY-padding && $mouseY < item.objY + item.objRealHeight+2*padding) {
						idx = i;
						return i ;
					}
				}
				else if(item.objType == "normaltext" && item.objJustify == "L") {
					if(item.objDrag && $mouseX > item.objX-padding && $mouseX < item.objX + item.objWidth+2*padding && $mouseY > item.objY-padding && $mouseY < item.objY + item.objRealHeight+2*padding) {
						idx = i;
						return i;
					}
				} else if(item.objType == "normaltext" && item.objJustify == "C") {
					if(item.objDrag && $mouseX > item.objX-padding && $mouseX < item.objX + item.objWidth+2*padding && $mouseY > item.objY-padding && $mouseY < item.objY + item.objRealHeight+2*padding) {
						idx = i;
						return i ;
					}
				} else if(item.objType == "normaltext" && item.objJustify == "R") {
					if(item.objDrag && $mouseX > item.objX-padding && $mouseX < item.objX + item.objWidth+2*padding && $mouseY > item.objY-padding && $mouseY < item.objY + item.objRealHeight+2*padding) {
						idx = i;
						return i ;
					}
				}else if(item.objType == "normaltext" && item.objJustify == "J") {
					if(item.objDrag && $mouseX > item.objX-padding && $mouseX < item.objX + item.objWidth+2*padding && $mouseY > item.objY-padding && $mouseY < item.objY + item.objRealHeight+2*padding) {
						idx = i;
						return i;
					}
				}
			 
				else if(item.objType == "securityline" && item.objJustify == "L") {
					if(item.objDrag && $mouseX > item.objX-padding && $mouseX < item.objX + item.objRealWidth+2*padding && $mouseY > item.objY-padding && $mouseY < item.objY + item.objRealHeight+2*padding) {
						idx = i;
						return i;
					}
				} else if(item.objType == "securityline" && item.objJustify == "C") {
					if(item.objDrag && $mouseX > item.newX-padding && $mouseX < item.newX + item.objRealWidth+2*padding && $mouseY > item.objY-padding && $mouseY < item.objY + item.objRealHeight+2*padding) {
						idx = i;
						return i ;
					}
				} else if(item.objType == "securityline" && item.objJustify == "R") {
					if(item.objDrag && $mouseX > item.newX-padding && $mouseX < item.objX + item.objRealWidth+2*padding && $mouseY > item.objY-padding && $mouseY < item.objY + item.objRealHeight+2*padding) {
						idx = i;
						return i ;
					}
				} else if(item.objType == "securityline" && item.objJustify == "J") {
					if(item.objDrag && $mouseX > item.objX-padding && $mouseX < item.objX + item.objRealWidth+2*padding && $mouseY > item.objY-padding && $mouseY < item.objY + item.objRealHeight+2*padding) {
						idx = i;
						return i;
					}
				}
				else if(item.objType == "microLine" && item.objJustify == "L") {
					if(item.objDrag && $mouseX > item.objX-padding && $mouseX < item.objX + item.objRealWidth+2*padding && $mouseY > item.objY-padding && $mouseY < item.objY + item.objRealHeight+2*padding) {
						idx = i;
						return i;
					}
				} else if(item.objType == "microLine" && item.objJustify == "C") {
					if(item.objDrag && $mouseX > item.newX-padding && $mouseX < item.newX + item.objRealWidth+2*padding && $mouseY > item.objY-padding && $mouseY < item.objY + item.objRealHeight+2*padding) {
						idx = i;
						return i ;
					}
				} else if(item.objType == "microLine" && item.objJustify == "R") {
					if(item.objDrag && $mouseX > item.newX-padding && $mouseX < item.objX + item.objRealWidth+2*padding && $mouseY > item.objY-padding && $mouseY < item.objY + item.objRealHeight+2*padding) {
						idx = i;
						return i ;
					}
				} else if(item.objType == "microLine" && item.objJustify == "J") {
					if(item.objDrag && $mouseX > item.newX-padding && $mouseX < item.objX + item.objRealWidth+2*padding && $mouseY > item.objY-padding && $mouseY < item.objY + item.objRealHeight+2*padding) {
						idx = i;
						return i ;
					}
				}
				else if(item.objType == "microTextBorder" && item.objJustify == "L") {
					if(item.objDrag && $mouseX > item.objX && $mouseX < item.objX + item.objWidth && $mouseY > item.objY && $mouseY < item.objY + item.objHeight) {
						idx = i;
						return i ;
					}
				} else if(item.objType == "microTextBorder" && item.objJustify == "C") {

					if(item.objDrag && $mouseX > item.objX && $mouseX < item.objX + item.objWidth && $mouseY > item.objY && $mouseY < item.objY + item.objHeight) {
						idx = i;
						return i ;
					}
				} else if(item.objType == "microTextBorder" && item.objJustify == "R") {
					if(item.objDrag && $mouseX > item.objX && $mouseX < item.objX + item.objWidth && $mouseY > item.objY && $mouseY < item.objY + item.objHeight) {
						idx = i;
						return i ;
					}
				} else if(item.objType == "customImage") {
					if(item.objDrag && $mouseX > item.objX && $mouseX < item.objX + (item.objWidth * 1.18) /  1.18&& $mouseY > item.objY && $mouseY < item.objY + item.objHeight) {
						idx = i;
						return i ;
					}
				} else if(item.objType == "uvRepeatLine" && item.objJustify == "L") {
					if(item.objDrag && $mouseX > item.objX-padding && $mouseX < item.objX + item.objRealWidth+2*padding && $mouseY > item.objY-padding && $mouseY < item.objY + item.objRealHeight+2*padding) {
						idx = i;
						return i;
					}
				} else if(item.objType == "uvRepeatLine" && item.objJustify == "C") {
					if(item.objDrag && $mouseX > item.newX-padding && $mouseX < item.newX + item.objRealWidth+2*padding && $mouseY > item.objY-padding && $mouseY < item.objY + item.objRealHeight+2*padding) {
						idx = i;
						return i ;
					}
				} else if(item.objType == "uvRepeatLine" && item.objJustify == "R") {
					if(item.objDrag && $mouseX > item.newX-padding && $mouseX < item.objX + item.objRealWidth+2*padding && $mouseY > item.objY-padding && $mouseY < item.objY + item.objRealHeight+2*padding) {
						idx = i;
						return i ;
					}
				} else if(item.objType == "uvRepeatLine" && item.objJustify == "J") {
					if(item.objDrag && $mouseX > item.objX-padding && $mouseX < item.objX + item.objRealWidth+2*padding && $mouseY > item.objY-padding && $mouseY < item.objY + item.objRealHeight+2*padding) {
						idx = i;
						return i;
					}
				}else if(item.objType == "uvRepeatFullpage" && item.objJustify == "L") {
					if(item.objDrag && $mouseX > item.objX-padding && $mouseX < item.objX + item.objRealWidth+2*padding && $mouseY > item.objY-padding && $mouseY < item.objY + item.objRealHeight+2*padding) {
						idx = i;
						return i;
					}
				} else if(item.objType == "uvRepeatFullpage" && item.objJustify == "C") {
					if(item.objDrag && $mouseX > item.newX-padding && $mouseX < item.newX + item.objRealWidth+2*padding && $mouseY > item.objY-padding && $mouseY < item.objY + item.objRealHeight+2*padding) {
						idx = i;
						return i ;
					}
				} else if(item.objType == "uvRepeatFullpage" && item.objJustify == "R") {
					if(item.objDrag && $mouseX > item.newX-padding && $mouseX < item.objX + item.objRealWidth+2*padding && $mouseY > item.objY-padding && $mouseY < item.objY + item.objRealHeight+2*padding) {
						idx = i;
						return i ;
					}
				} else if(item.objType == "uvRepeatFullpage" && item.objJustify == "J") {
					if(item.objDrag && $mouseX > item.objX-padding && $mouseX < item.objX + item.objRealWidth+2*padding && $mouseY > item.objY-padding && $mouseY < item.objY + item.objRealHeight+2*padding) {
						idx = i;
						return i;
					}
				} else if(item.objType == "invisibleImage") {
					if(item.objDrag && $mouseX > item.objX && $mouseX < item.objX + item.objWidth && $mouseY > item.objY && $mouseY < item.objY + item.objHeight) {
						idx = i;
						return i ;
					}
				}

				else if(item.objType == "2D Barcode") {
					if(item.objDrag && $mouseX > item.objX && $mouseX < item.objX + item.objWidth && $mouseY > item.objY && $mouseY < item.objY + item.objHeight) {
						idx = i;
						return i ;
					}
				}
			});
			return idx;
		}


		//on mouse down call mousedown method
		canvas.addEventListener('mousedown', function (event) {
			mouseDown(event);
		});

		function mouseDown(event)
		{
			var mouseX, mouseY;

			event = event || window.event;
		
			// check if right mouse button is clicked
			if ("buttons" in event) {
				
				if(event.buttons != 1)
					return; 
			}
			else {

				button = event.which || event.button;
				if(button != 1)
					return;
			}

			lastX = mouseX = parseInt(event.pageX - offsetX);
			lastY = mouseY = parseInt(event.pageY - offsetY);
			
			objList.forEach(function (item, i) {
				
				
				if(item.objType == "image" && item.lock_index == "unlock") {
					
					
					
					if(item.objDrag && mouseX > item.objX && mouseX < item.objX + item.objWidth && mouseY > item.objY && mouseY < item.objY + item.objHeight) {
						
						
						isDown = true;
						draggingImage = false;
						objIndex = i;
						lastWidth = mouseX - item.objX;
						lastHeight = mouseY - item.objY;
						canvas.style.cursor= 'move';
						return;
					}
					
				}else if(item.objType == "Idimage" && item.lock_index == "unlock") {


					if(item.objDrag && mouseX > item.objX && mouseX < item.objX + item.objWidth && mouseY > item.objY && mouseY < item.objY + item.objHeight) {
						
						
						isDown = true;
						draggingImage = false;
						objIndex = i;
						lastWidth = mouseX - item.objX;
						lastHeight = mouseY - item.objY;
						canvas.style.cursor= 'move';
						return;
					}
					
				}else if(item.objType == "Qrimage" && item.lock_index == "unlock") {
					if(item.objDrag && mouseX > item.objX && mouseX < item.objX + item.objWidth && mouseY > item.objY && mouseY < item.objY + item.objHeight) {

						
						
						isDown = true;
						draggingImage = false;
						objIndex = i;
						lastWidth = mouseX - item.objX;
						lastHeight = mouseY - item.objY;
						canvas.style.cursor= 'move';
						return;
					}
					
				}  else if(item.objType == "qrCode" && item.lock_index == "unlock") {
					if(item.objDrag && mouseX > item.objX && mouseX < item.objX + item.objWidth && mouseY > item.objY && mouseY < item.objY + item.objHeight) {
						
						isDown = true;
						draggingImage = false;
						objIndex = i;
						lastWidth = mouseX - item.objX;
						lastHeight = mouseY - item.objY;
						canvas.style.cursor= 'move';
						return;
					}
					draggingResizer = anchorHitTest(mouseX, mouseY);
					if(draggingResizer == -1){
						
						draggingImage = false;
						return;	
					}else{
						isDown = true;
						draggingImage = true;
						return;	
					}
					
				} else if(item.objType == "text" && item.objJustify == "L" && item.lock_index == "unlock") {
					if(item.objDrag && mouseX > item.objX-padding && mouseX < item.objX + item.objWidth+2*padding && mouseY > item.objY-padding && mouseY < item.objY + item.objRealHeight+2*padding) {
						
						isDown = true;
						draggingImage = false;
						objIndex = i;
						lastWidth = mouseX - item.objX;
						lastHeight = mouseY - item.objY;
						canvas.style.cursor= 'move';
						return;
					}
					
				} else if(item.objType == "text" && item.objJustify == "C"  && item.lock_index == "unlock") {
					if(item.objDrag && mouseX > item.objX-padding && mouseX < item.objX + item.objWidth+2*padding && mouseY > item.objY-padding && mouseY < item.objY + item.objRealHeight+2*padding) {
						
						isDown = true;
						draggingImage = false;
						objIndex = i;
						lastWidth = mouseX - item.objX;
						lastHeight = mouseY - item.objY;
						canvas.style.cursor= 'move';
						return;
					}
					
				} else if(item.objType == "text" && item.objJustify == "R"  && item.lock_index == "unlock") {
					if(item.objDrag && mouseX > item.objX-padding && mouseX < item.objX + item.objWidth+2*padding && mouseY > item.objY-padding && mouseY < item.objY + item.objRealHeight+2*padding) {
						
						isDown = true;
						draggingImage = false;
						objIndex = i;
						lastWidth = mouseX - item.objX;
						lastHeight = mouseY - item.objY;
						canvas.style.cursor = 'move';
						return;
					}
					
				}else if(item.objType == "text" && item.objJustify == "J" && item.lock_index == "unlock") {
					if(item.objDrag && mouseX > item.objX-padding && mouseX < item.objX + item.objWidth+2*padding && mouseY > item.objY-padding && mouseY < item.objY + item.objRealHeight+2*padding) {
						
						isDown = true;
						draggingImage = false;
						objIndex = i;
						lastWidth = mouseX - item.objX;
						lastHeight = mouseY - item.objY;
						canvas.style.cursor= 'move';
						return;
					}
					
				}
				else if(item.objType == "normaltext" && item.objJustify == "L" && item.lock_index == "unlock") {
					if(item.objDrag && mouseX > item.objX-padding && mouseX < item.objX + item.objWidth+2*padding && mouseY > item.objY-padding && mouseY < item.objY + item.objRealHeight+2*padding) {
						
						isDown = true;
						draggingImage = false;
						objIndex = i;
						lastWidth = mouseX - item.objX;
						lastHeight = mouseY - item.objY;
						canvas.style.cursor= 'move';
						return;
					}
					
				} else if(item.objType == "normaltext" && item.objJustify == "C"  && item.lock_index == "unlock") {
					if(item.objDrag && mouseX > item.objX-padding && mouseX < item.objX + item.objWidth+2*padding && mouseY > item.objY-padding && mouseY < item.objY + item.objRealHeight+2*padding) {
						
						isDown = true;
						draggingImage = false;
						objIndex = i;
						lastWidth = mouseX - item.objX;
						lastHeight = mouseY - item.objY;
						canvas.style.cursor= 'move';
						return;
					}
					
				} else if(item.objType == "normaltext" && item.objJustify == "R"  && item.lock_index == "unlock") {
					if(item.objDrag && mouseX > item.objX-padding && mouseX < item.objX + item.objWidth+2*padding && mouseY > item.objY-padding && mouseY < item.objY + item.objRealHeight+2*padding) {
						
						isDown = true;
						draggingImage = false;
						objIndex = i;
						lastWidth = mouseX - item.objX;
						lastHeight = mouseY - item.objY;
						canvas.style.cursor = 'move';
						return;
					}
					
				}else if(item.objType == "normaltext" && item.objJustify == "J" && item.lock_index == "unlock") {
					if(item.objDrag && mouseX > item.objX-padding && mouseX < item.objX + item.objWidth+2*padding && mouseY > item.objY-padding && mouseY < item.objY + item.objRealHeight+2*padding) {
						
						isDown = true;
						draggingImage = false;
						objIndex = i;
						lastWidth = mouseX - item.objX;
						lastHeight = mouseY - item.objY;
						canvas.style.cursor= 'move';
						return;
					}
					
				}
				
				// get x,y axis and height width of security type and draw line
				else if(item.objType == "securityline" && item.objJustify == "L" && item.lock_index == "unlock") {
					if(item.objDrag && mouseX > item.objX-padding && mouseX < item.objX + item.objWidth+2*padding && mouseY > item.objY-padding && mouseY < item.objY + item.objRealHeight+2*padding) {
						
						isDown = true;
						draggingImage = false;
						objIndex = i;
						lastWidth = mouseX - item.objX;
						lastHeight = mouseY - item.objY;
						canvas.style.cursor= 'move';
						return;
					}
					
					
				} else if(item.objType == "securityline" && item.objJustify == "C" && item.lock_index == "unlock") {
					if(item.objDrag && mouseX > item.objX-padding && mouseX < item.objX + item.objWidth+2*padding && mouseY > item.objY-padding && mouseY < item.objY + item.objRealHeight+2*padding) {
						
						isDown = true;
						draggingImage = false;
						objIndex = i;
						lastWidth = mouseX - item.objX;
						lastHeight = mouseY - item.objY;
						canvas.style.cursor= 'move';
						return;
					}
					
					
				} else if(item.objType == "securityline" && item.objJustify == "R" && item.lock_index == "unlock") {
					if(item.objDrag && mouseX > item.objX-padding && mouseX < item.objX + item.objWidth+2*padding && mouseY > item.objY-padding && mouseY < item.objY + item.objRealHeight+2*padding) {
						
						isDown = true;
						draggingImage = false;
						objIndex = i;
						lastWidth = mouseX - item.objX;
						lastHeight = mouseY - item.objY;
						canvas.style.cursor = 'move';
						return;
					}	
				}
				else if(item.objType == "securityline" && item.objJustify == "J" && item.lock_index == "unlock") {
					if(item.objDrag && mouseX > item.objX-padding && mouseX < item.objX + item.objWidth+2*padding && mouseY > item.objY-padding && mouseY < item.objY + item.objRealHeight+2*padding) {
						
						isDown = true;
						draggingImage = false;
						objIndex = i;
						lastWidth = mouseX - item.objX;
						lastHeight = mouseY - item.objY;
						canvas.style.cursor = 'move';
						return;
					}	
				}
				else if(item.objType == "microLine" && item.objJustify == "L" && item.lock_index == "unlock") {
					if(item.objDrag && mouseX > item.objX-padding && mouseX < item.objX + item.objRealWidth+2*padding && mouseY > item.objY-padding && mouseY < item.objY + item.objRealHeight+2*padding) {
						
						isDown = true;
						draggingImage = false;
						objIndex = i;
						lastWidth = mouseX - item.objX;
						lastHeight = mouseY - item.objY;
						canvas.style.cursor= 'move';
						return;
					}
					
					
				} else if(item.objType == "microLine" && item.objJustify == "C" && item.lock_index == "unlock") {
					if(item.objDrag && mouseX > item.newX-padding && mouseX < item.newX + item.objRealWidth+2*padding && mouseY > item.objY && mouseY < item.objY-padding + item.objRealHeight+2*padding) {
						
						isDown = true;
						draggingImage = false;
						objIndex = i;
						lastWidth = mouseX - item.objX;
						lastHeight = mouseY - item.objY;
						canvas.style.cursor= 'move';
						return;
					}
					
					
				} else if(item.objType == "microLine" && item.objJustify == "R" && item.lock_index == "unlock") {
					if(item.objDrag && mouseX > item.newX-padding && mouseX < item.objX + item.objRealWidth+2*padding && mouseY > item.objY-padding && mouseY < item.objY + item.objRealHeight+2*padding) {
						
						isDown = true;
						draggingImage = false;
						objIndex = i;
						lastWidth = mouseX - item.objX;
						lastHeight = mouseY - item.objY;
						canvas.style.cursor = 'move';
						return;
					}
					
				}else if(item.objType == "microLine" && item.objJustify == "J" && item.lock_index == "unlock") {
					if(item.objDrag && mouseX > item.newX-padding && mouseX < item.objX + item.objRealWidth+2*padding && mouseY > item.objY-padding && mouseY < item.objY + item.objRealHeight+2*padding) {
						
						isDown = true;
						draggingImage = false;
						objIndex = i;
						lastWidth = mouseX - item.objX;
						lastHeight = mouseY - item.objY;
						canvas.style.cursor = 'move';
						return;
					}
					
				}
				else if(item.objType == "microTextBorder" && item.objJustify == "L" && item.lock_index == "unlock") {
					if(item.objDrag && mouseX > item.objX && mouseX < item.objX + item.objWidth && mouseY > item.objY && mouseY < item.objY + item.objHeight) {
						
						isDown = true;
						draggingImage = false;
						objIndex = i;
						lastWidth = mouseX - item.objX;
						lastHeight = mouseY - item.objY;
						canvas.style.cursor= 'move';
						return;
					}
					draggingResizer = anchorHitTest(mouseX, mouseY);
					if(draggingResizer == -1){
						
						draggingImage = false;
						return;	
					}else{
						isDown = true;
						draggingImage = true;
						return;	
					}
				} else if(item.objType == "microTextBorder" && item.objJustify == "C" && item.lock_index == "unlock") {
					if(item.objDrag && mouseX > item.objX && mouseX < item.objX + item.objWidth && mouseY > item.objY && mouseY < item.objY + item.objHeight) {
						
						isDown = true;
						draggingImage = false;
						objIndex = i;
						lastWidth = mouseX - item.objX;
						lastHeight = mouseY - item.objY;
						canvas.style.cursor= 'move';
						return;
					}
					draggingResizer = anchorHitTest(mouseX, mouseY);
					if(draggingResizer == -1){
						
						draggingImage = false;
						return;	
					}else{
						isDown = true;
						draggingImage = true;
						return;	
					}
				} else if(item.objType == "microTextBorder" && item.objJustify == "R" && item.lock_index == "unlock") {
					if(item.objDrag && mouseX > item.objX && mouseX < item.objX + item.objWidth && mouseY > item.objY && mouseY < item.objY + item.objHeight) {
						
						isDown = true;
						draggingImage = false;
						objIndex = i;
						lastWidth = mouseX - item.objX;
						lastHeight = mouseY - item.objY;
						canvas.style.cursor= 'move';
						return;
					}
					draggingResizer = anchorHitTest(mouseX, mouseY);
					if(draggingResizer == -1){
						
						draggingImage = false;
						return;	
					}else{
						isDown = true;
						draggingImage = true;
						return;	
					}
				} else if(item.objType == "customImage" && item.lock_index == "unlock") {
					
					if(item.objDrag && mouseX > item.objX && mouseX < item.objX + item.objWidth && mouseY > item.objY && mouseY < item.objY + item.objHeight) {
						
						isDown = true;
						draggingImage = false;
						objIndex = i;
						lastWidth = mouseX - item.objX;
						lastHeight = mouseY - item.objY;
						canvas.style.cursor= 'move';
						return;
					}
					draggingResizer = anchorHitTest(mouseX, mouseY);
					if(draggingResizer == -1){
						
						draggingImage = false;
						return;	
					}else{
						isDown = true;
						draggingImage = true;
						return;	
					}
					
				} 
				else if(item.objType == "invisibleImage" && item.lock_index == "unlock") {
					if(item.objDrag && mouseX > item.objX && mouseX < item.objX + item.objWidth && mouseY > item.objY && mouseY < item.objY + item.objHeight) {
						
						isDown = true;
						draggingImage = false;
						objIndex = i;
						lastWidth = mouseX - item.objX;
						lastHeight = mouseY - item.objY;
						canvas.style.cursor= 'move';
						return;
					}
					
				} 
				else if(item.objType == "uvRepeatLine" && item.objJustify == "L" && item.lock_index == "unlock"){

					if(item.objDrag && mouseX > item.objX-padding && mouseX < item.objX + item.objWidth+2*padding && mouseY > item.objY-padding && mouseY < item.objY + item.objRealHeight+2*padding) {
						
						isDown = true;
						objIndex = i;
						draggingImage = false;
						lastWidth = mouseX - item.objX;
						lastHeight = mouseY - item.objY;
						canvas.style.cursor= 'move';
						return;
					}
				}
				else if(item.objType == "uvRepeatLine" && item.objJustify == "C" && item.lock_index == "unlock"){

					if(item.objDrag && mouseX > item.objX-padding && mouseX < item.objX + item.objWidth+2*padding && mouseY > item.objY-padding && mouseY < item.objY + item.objRealHeight+2*padding) {
						
						isDown = true;
						objIndex = i;
						draggingImage = false;
						lastWidth = mouseX - item.objX;
						lastHeight = mouseY - item.objY;
						canvas.style.cursor= 'move';
						return;
					}
				}
				else if(item.objType == "uvRepeatLine" && item.objJustify == "R" && item.lock_index == "unlock"){

					if(item.objDrag && mouseX > item.objX-padding && mouseX < item.objX + item.objWidth+2*padding && mouseY > item.objY-padding && mouseY < item.objY + item.objRealHeight+2*padding) {
						
						isDown = true;
						objIndex = i;
						draggingImage = false;
						lastWidth = mouseX - item.objX;
						lastHeight = mouseY - item.objY;
						canvas.style.cursor= 'move';
						return;
					}
				}
				else if(item.objType == "uvRepeatLine" && item.objJustify == "J" && item.lock_index == "unlock"){

					if(item.objDrag && mouseX > item.objX-padding && mouseX < item.objX + item.objWidth+2*padding && mouseY > item.objY-padding && mouseY < item.objY + item.objRealHeight+2*padding) {
						
						isDown = true;
						objIndex = i;
						draggingImage = false;
						lastWidth = mouseX - item.objX;
						lastHeight = mouseY - item.objY;
						canvas.style.cursor= 'move';
						return;
					}
				}
				else if(item.objType == "uvRepeatFullpage" && item.lock_index == "unlock"){

					if(item.objDrag && mouseX > item.objX-padding && mouseX < item.objX + item.objRealWidth+2*padding && mouseY > item.objY-padding && mouseY < item.objY + item.objRealHeight+2*padding) {
						
						isDown = true;
						objIndex = i;
						draggingImage = false;
						lastWidth = mouseX - item.objX;
						lastHeight = mouseY - item.objY;
						canvas.style.cursor= 'move';
						return;
					}
				}
				
				else if(item.objType == "2D Barcode" && item.lock_index == "unlock"){
					
					if(item.objDrag && mouseX > item.objX && mouseX < item.objX + item.objWidth && mouseY > item.objY && mouseY < item.objY + item.objHeight) {

						
						isDown = true;
						draggingImage = false;
						objIndex = i;
						lastWidth = mouseX - item.objX;
						lastHeight = mouseY - item.objY;
						canvas.style.cursor= 'move';
						return;
					}
					
					draggingResizer = anchorHitTest(mouseX, mouseY);
					
					if(draggingResizer != -1){
						isDown = true;
						draggingImage = true;
						return;	
					}else{

						draggingImage = false;
						return;
					}
				}
				
			});
		}
		window.addEventListener('keydown',function(event){
				
				var item = objList[objIndex];
				if (ctrlArray.length > 0) {

					ctrlArray.forEach(function(selectedIds) {
						
						var selectItem = objList[selectedIds];
						if(selectItem != undefined){

							switch(event.keyCode) {
						        case 37:
						            	
						            // left key pressed	
						            selectItem.objX -= 2;
						            break;
						        case 38:
						        	selectItem.objY -= 2;
						            // up key pressed
						            break;
						        case 39:
						        	selectItem.objX += 2;
						            // right key pressed
						            break;
						        case 40:
						        	selectItem.objY += 2;
						            // down key pressed
						            break;  
						    }  
						    event.preventDefault(); 
						}
						
						draw(true,false);
						window.top.updateField(selectedIds, selectItem.objX / certRatioX,selectItem.objY / certRatioY);
					});
				}else{

					if(item != undefined){

						switch(event.keyCode) {
					        case 37:
					            	
					            // left key pressed	
					            item.objX -= 2;
					            break;
					        case 38:
					        	item.objY -= 2;
					            // up key pressed
					            break;
					        case 39:
					        	item.objX += 2;
					            // right key pressed
					            break;
					        case 40:
					        	item.objY += 2;
					            // down key pressed
					            break;  
					    }  
					    event.preventDefault(); 
					}
					
					draw(true,false);
					window.top.updateField(objIndex, item.objX / certRatioX,item.objY / certRatioY);
				}
				
			})
		//mouse up for drag n drop image/qr code
		var ctrlArray = [];
		canvas.addEventListener('mouseup', function (event) {
			var mouseX, mouseY;
			if(!isDown) return;
			
			var item = objList[objIndex];
			var cntrlIsPressed = false;
			if(event.ctrlKey){
			    var cntrlIsPressed = true;
			}
			if (cntrlIsPressed == true) {
				
				isCtrl = true;
				var checkExist =  ctrlArray.indexOf(objIndex);
				if(checkExist == -1){

					ctrlArray.push(objIndex);
					window.top.showFieldDialogCtrl(ctrlArray);
					isDown = false;
				}

			}else{
				isCtrl = false;
				if(draggingImage == false){
					isDown = false;
					mouseX = parseInt(event.pageX - offsetX - lastWidth);
					mouseY = parseInt(event.pageY - offsetY - lastHeight);

					item.objX = mouseX;
					item.objY = mouseY;
					
					mouseX = Math.ceil(mouseX / certRatioX);
					mouseY = Math.ceil(mouseY / certRatioY);
					draw(true,false);

					canvas.style.cursor = 'pointer';
				
					
					window.top.updateField(objIndex, mouseX, mouseY);
					window.top.showFieldDialog(objIndex,item.security_type);
				}else{
					
					
					draggingResizer = -1;
					isDown = false;
					draggingImage = false;	
					var width = Math.floor(item.objWidth);
					var height = Math.floor(item.objHeight);
					
					
					window.top.updateField(objIndex,'','',width,height);
				}
				ctrlArray = [];
			}
			
			
		});

		function anchorHitTest(x, y) {
			
			
			var returnValue = -1;
			var item = objList[objIndex];
			if(item != undefined){
				var dx, dy;
			 	var rr = 8 * 8;
			    // top-left
			    dx = x - item.objX;
			    dy = y - item.objY;
			    if (dx * dx + dy * dy <= rr) {
			    	
			    	returnValue = 0;
			    }
			    // top-right
			    dx = x - (item.objX + item.objWidth);
			    dy = y - item.objY;
			    if (dx * dx + dy * dy <= rr) {
			    	// alert(1);
			    	returnValue = 1;
			    }
			    // bottom-right
			    dx = x - (item.objX + item.objWidth);
			    dy = y - (item.objY + item.objHeight);
			    if (dx * dx + dy * dy <= rr) {
			    	returnValue = 2;
			    }
			    // bottom-left
			    dx = x - item.objX;
			    dy = y - (item.objY + item.objHeight);
			    if (dx * dx + dy * dy <= rr) {
			    	returnValue = 3;
			    }
			}else{
				
				return returnValue;
			  	
			}
			return (returnValue);
		}
		
	</script>
@stop