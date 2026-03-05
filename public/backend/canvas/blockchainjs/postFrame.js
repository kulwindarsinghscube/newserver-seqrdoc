window.postFrameMessage = function ($cmd, $id, $x='', $y='', $width='', $height='',$lock_index='', $security_type='', $fontId='', $fontSize='', $fontStyle='',$fontCase='', $text='', $color='',$excolor='', $align='',$image='',$template_name='',$angle='',$line_gap='',$length='',$uv_percentage='',$is_repeat='',$infinite_height='',$include_image,$grey_scale='',$water_mark='',$uvImage='',$transparentImage='',$text_opicity='',$visible='',$visible_varification='',$combo_qr_text='',$is_meta_data='',$field_metadata_label='',$field_metadata_value='',$refresh='true')
{
	var str = "cmd="+$cmd+", id="+$id+", x="+$x+", y="+$y+", width="+$width+", height="+$height+",lock_index="+$lock_index+", security_type="+$security_type+", font="+$fontId+", fontSize="+$fontSize+", fontStyle="+$fontStyle+", fontCase="+$fontCase+", text="+$text+", color="+$color+",excolor="+$excolor+", align="+$align+",image="+$image+",template_name="+$template_name+",angle="+$angle+",line_gap="+$line_gap+",length="+$length+",uv_percentage="+$uv_percentage+",is_repeat="+$is_repeat+",infinite_height="+$infinite_height+",include_image="+$include_image+",grey_scale="+$grey_scale+",water_mark="+$water_mark+",is_uv_image="+$uvImage+",is_transparent_image="+$transparentImage+",text_opicity="+$text_opicity+",visible="+$visible+",visible_varification="+$visible_varification+",combo_qr_text="+$combo_qr_text+",is_meta_data="+$is_meta_data+",field_metadata_label="+$field_metadata_label+",field_metadata_value="+$field_metadata_value+",refresh="+$refresh;

//console.log(str);
	
	/* ------------------------------------
	 * Default Height width
	 */
	var defaultAntiCopyWidth = 50,
		defaultAntiCopyheight = 10,
		defaultGhostImgWidth = 30,
		defaultGhostImgHeight = 10,
		defaultBarcodeWidth = 40,
		defaultBarcodeHeight = 10;
	// ------------------------------------

	if($cmd == "update") {
		objList.forEach(function (item, i) {


			if(item.objID == $id) {
				if($x !== '')
					item.objX = $x * certRatioX;
				if($y !== '')
					item.objY = $y * certRatioY;
				if($id == 1) { // QR Code
			// 		console.log(item.objSrc)
			// console.log($image)
					item.objHeight = $height  * certRatioY;
					item.objWidth = $width * certRatioX;
					item.lock_index = $lock_index;
					if($include_image != ''){
						item.include_image = $include_image;
					}else{
						item.include_image = $include_image;

					}
					if($image != ''){
						item.image = $image;
						qrImageNew.src = $image;
					}
				}
				if($id == 2) { // Print serial
					item.objHeight = $height  * certRatioY;
					item.objWidth = $width * certRatioX;
					item.lock_index = $lock_index;

				}
				item.security_type = $security_type;
				if($security_type == 'Normal'){
					item.objType = "normaltext";
					if($width !== '')
						item.objWidth = $width * certRatioX;
					if($height !== '')
						item.objHeight = $height * certRatioY;
					if($fontId !== '')
						item.objFontId = $fontId;
					if($fontSize !== '')
						item.objFontSize = $fontSize ;
					if($fontStyle !== ''){
						item.objFontStyle = $fontStyle;
					}else{
						item.objFontStyle = $fontStyle;

					}
					item.objText = $text;
					if($color != '')
						item.objFontColor = $color;
					if($align != '')
						item.objJustify = $align;
					if($lock_index != '')
						item.lock_index = $lock_index;
					if($infinite_height != '')
						item.infinite_height = $infinite_height;
				}else if($security_type == 'Florescent' || $security_type == 'Static Text') {
					item.objType = "text";
					if($width !== '')
						item.objWidth = $width * certRatioX;
					if($height !== '')
						item.objHeight = $height * certRatioY;
					if($fontId !== '')
						item.objFontId = $fontId;
					if($fontSize !== '')
						item.objFontSize = $fontSize ;
					if($fontStyle !== ''){
						item.objFontStyle = $fontStyle;
					}else{
						item.objFontStyle = $fontStyle;

					}
					item.objText = $text;
					if($color != '')
						item.objFontColor = $color;
					if($align != '')
						item.objJustify = $align;
					if($lock_index != '')
						item.lock_index = $lock_index;
				}else if($security_type == 'Invisible'){
					item.objType = "text";
					if($width !== '')
						item.objWidth = $width * certRatioX;
					if($height !== '')
						item.objHeight = $height * certRatioY;
					if($fontId !== '')
						item.objFontId = $fontId;
					if($fontSize !== '')
						item.objFontSize = $fontSize ;
					if($fontStyle !== ''){
						item.objFontStyle = $fontStyle;
					}else{
						item.objFontStyle = $fontStyle;

					}
					item.objText = $text;
					if($color != '')
						item.objFontColor = $excolor;
					if($align != '')
						item.objJustify = $align;
					if($lock_index != '')
						item.lock_index = $lock_index;
				} else if($security_type == 'Anti-Copy') {
					item.objSrc = anticopyImage;
					item.objType = "image";
					if($width <= 10)
						$width = defaultAntiCopyWidth;
					if($height <= 10)
						$height = defaultAntiCopyheight;
					item.objWidth = $width * certRatioX;
					item.objHeight = $height * certRatioY;
					if($lock_index != '')
						item.lock_index = $lock_index;

				} else if($security_type == 'Ghost Image') {
					item.objSrc = ghostImage;
					item.objType = "image";
					if($width <= 10)
						$width = defaultGhostImgWidth ;
					if($height <= 10)
						$height = defaultGhostImgHeight;
					item.objWidth = $width * certRatioX;
					item.objHeight = $height * certRatioY;
					if($lock_index != '')
						item.lock_index = $lock_index;
				} else if($security_type == '1D Barcode') {
					item.objSrc = barcodeImage;
					item.objType = "Idimage";
					if($width <= 10)
						$width = defaultBarcodeWidth;
					if($height <= 10)
						$height = defaultBarcodeHeight;
					item.objWidth = $width * certRatioX;
					item.objHeight = $height * certRatioY;
					if($lock_index != '')
						item.lock_index = $lock_index;
				}else if($security_type == 'Qr Code') {
					if($image != '' && $image != 'null' && $image != null){
						item.image = $image;
						qrCodeImage.src = $image;
					}else{
						qrCodeImage.src = "QR.png";
					}
					item.objType = "qrCode";
					if($width <= 10)
						$width = defaultBarcodeWidth;
					if($height <= 10)
						$height = defaultBarcodeHeight;
					item.objHeight = ($height * certRatioY) / certRatioY ;
					item.objWidth = ($width * certRatioX) / certRatioX ;
					if($lock_index != '')
						item.lock_index = $lock_index;
					if($include_image != ''){
						item.include_image = $include_image;
					}
				}
				
				// get values when update
				 else if($security_type == 'Micro Text Border' || $security_type == 'Static Microtext Border') {


					item.objType = "microTextBorder";
					// console.log('$width');
					// console.log($width);
					// console.log('$width');
					if($width !== "")
					item.objWidth =($width * certRatioX) / certRatioX ;
					if($height !== "")
					item. objHeight = ($height * certRatioY) / certRatioY ;
					if($fontId !== '')
						item.objFontId = $fontId;
					if($fontSize !== '')
						item.objFontSize = $fontSize ;
					if($fontStyle !== ''){
						item.objFontStyle = $fontStyle;
					}else{
						item.objFontStyle = $fontStyle;

					}
					item.objText = $text;
					if($color != '')
						item.objFontColor = $color;
					if($align != '')
						item.objJustify = $align;
					if($lock_index != '')
						item.lock_index = $lock_index;
				}else if($security_type == "Dynamic Image" || $security_type == "Static Image"){
					item.objType = "customImage";
					item.formType = $cmd;
					item.objHeight = ($height * certRatioY) / certRatioY ;
					item.objWidth = ($width * certRatioX) / certRatioX ;
					item.image = $image;
					item.objID = $id;
					item.template_name = $template_name;
					if($lock_index != '')
						item.lock_index = $lock_index;
					// if($grey_scale != '')
					item.grey_scale = $grey_scale;
					item.is_uv_image = $uvImage;
					item.is_transparent_image = $transparentImage;
				}else if($security_type == "Invisible Image"){

					item.objType = "invisibleImage";
					item.objHeight = ($height * certRatioY) / certRatioY ;
					item.objWidth = ($width * certRatioX) / certRatioX ;

					item.image = $image;
					item.template_name = $template_name;
					if($lock_index != '')
						item.lock_index = $lock_index;
				} else if($security_type == "UV Repeat line"){

					item.objType = "uvRepeatLine";
					if($width !== '')
						item.objWidth = $width * certRatioX;
					if($height !== '')
						item.objHeight = $height * certRatioY;
					if($fontId !== '')
						item.objFontId = $fontId;
					if($fontSize !== '')
						item.objFontSize = $fontSize ;
					if($fontStyle !== ''){
						item.objFontStyle = $fontStyle;
					}else{
						item.objFontStyle = $fontStyle;

					}
					item.objText = $text;
					if($color != '')
						item.objFontColor = $excolor;
					if($align != '')
						item.objJustify = $align;
					if($angle != '')
						item.angle = $angle;
					if($lock_index != '')
						item.lock_index = $lock_index;


				}else if($security_type == "Security line"){

					item.objType = "securityline";
					if($width !== '')
						item.objWidth = $width * certRatioX;
					if($height !== '')
						item.objHeight = $height * certRatioY;
					if($fontId !== '')
						item.objFontId = $fontId;
					if($fontSize !== '')
						item.objFontSize = $fontSize ;
					if($fontStyle !== ''){
						item.objFontStyle = $fontStyle;
					}else{
						item.objFontStyle = $fontStyle;

					}
					item.objText = $text;
					if($color != '')
						item.objFontColor = $color;
					if($align != '')
						item.objJustify = $align;
					if($angle != '')
						item.angle = $angle;
					if($lock_index != '')
						item.lock_index = $lock_index;
					if($text_opicity != '')
						item.text_opicity = $text_opicity;

					item.field_extra_font_case = $fontCase;

				}else if($security_type == "Micro line"){

					item.objType = "microLine";
					if($width !== '')
						item.objWidth = $width * certRatioX;
					if($height !== '')
						item.objHeight = $height * certRatioY;
					if($fontId !== '')
						item.objFontId = $fontId;
					if($fontSize !== '')
						item.objFontSize = $fontSize ;
					if($fontStyle !== ''){
						item.objFontStyle = $fontStyle;
					}else{
						item.objFontStyle = $fontStyle;

					}
					item.objText = $text;
					if($color != '')
						item.objFontColor = $color;
					if($align != '')
						item.objJustify = $align;
					if($angle != '')
						item.angle = $angle;
					// if($is_repeat != '')
						item.is_repeat = $is_repeat;
					if($lock_index != '')
						item.lock_index = $lock_index;

				}else if($security_type == "UV Repeat Fullpage"){

					item.objType = "uvRepeatFullpage";
					if($width !== '')
						item.objWidth = $width * certRatioX;
					if($height !== '')
						item.objHeight = $height * certRatioY;
					if($fontId !== '')
						item.objFontId = $fontId;
					if($fontSize !== '')
						item.objFontSize = $fontSize ;
					if($fontStyle !== ''){
						item.objFontStyle = $fontStyle;
					}else{
						item.objFontStyle = $fontStyle;

					}
					item.objText = $text;
					if($color != '')
						item.objFontColor = $excolor;
					if($align != '')
						item.objJustify = $align;
					if($angle != '')
						item.angle = $angle;
					if($line_gap != '')
						item.line_gap = $line_gap
					if($lock_index != '')
						item.lock_index = $lock_index;

				}
				
				else if($security_type == "2D Barcode"){
					item.objType = "2D Barcode";
					item.formType = $cmd;
					item.objHeight = ($height * certRatioY) / certRatioY ;
					item.objWidth = ($width * certRatioX) / certRatioX ;
					item.image = $image;
					item.objID = $id;
					item.template_name = $template_name;
					if($lock_index != '')
						item.lock_index = $lock_index;
				}
				
			}
		});
	}
	else if($cmd == "remove") {
		if($id > 2) {
			var len = objList.length;

			objList.splice($id, 1);
			// console.log(objList);
			for (var i = $id; i < len - 1; i++)
				objList[i].objID = i;
			draw();
		}
	}
	else if($cmd == "add") {


		$id = objList.length;
		var obj;

		if($security_type == "Normal"){

			obj = {objSrc: '', objType: "normaltext",security_type:"Normal", objDrag: true, objDelete: true, objID: $id, objX: $x  * certRatioX, objY: $y * certRatioY, objWidth: $width * certRatioX, objHeight: $height * certRatioY, objFontId: $fontId, objFontSize: $fontSize, objFontColor: $color, objFontStyle: $fontStyle, objText: $text, objJustify: $align, newX: 0, objRealWidth: 0, objRealHeight: 0,lock_index:$lock_index,infinite_height:$infinite_height,is_meta_data:$is_meta_data};

		}else if($security_type == "Florescent" || $security_type == "Static Text") {
			obj = {objSrc: '', objType: "text",security_type:"Static Text", objDrag: true, objDelete: true, objID: $id, objX: $x  * certRatioX, objY: $y * certRatioY, objWidth: $width * certRatioX, objHeight: $height * certRatioY, objFontId: $fontId, objFontSize: $fontSize, objFontColor: $color, objFontStyle: $fontStyle, objText: $text, objJustify: $align, newX: 0, objRealWidth: 0, objRealHeight: 0,lock_index:$lock_index,is_meta_data:$is_meta_data};
		}else if( $security_type == "Invisible"){
			obj = {objSrc: '', objType: "text",security_type:"Invisible", objDrag: true, objDelete: true, objID: $id, objX: $x  * certRatioX, objY: $y * certRatioY, objWidth: $width * certRatioX, objHeight: $height * certRatioY, objFontId: $fontId, objFontSize: $fontSize, objFontColor: $excolor, objFontStyle: $fontStyle, objText: $text, objJustify: $align, newX: 0, objRealWidth: 0, objRealHeight: 0,lock_index:$lock_index};
		} else if($security_type == "Security line"){
			obj = {objSrc: '', objType: "securityline",security_type:"Security line", objDrag: true, objDelete: true, objID: $id, objX: $x  * certRatioX, objY: $y * certRatioY, objWidth: $width * certRatioX, objHeight: $height * certRatioY, objFontId: $fontId, objFontSize: $fontSize, objFontColor: $color, objFontStyle: $fontStyle, objText: $text, objJustify: $align, newX: 0, objRealWidth: 0, objRealHeight: 0,angle:$angle,lock_index:$lock_index,text_opicity:$text_opicity,field_extra_font_case:$fontCase};
		}
		else if($security_type == "Micro line") {
			obj = {objSrc: '', objType: "microLine",security_type:"Micro line", objDrag: true, objDelete: true, objID: $id, objX: $x  * certRatioX, objY: $y * certRatioY, objWidth: $width * certRatioX, objHeight: $height, objFontId: $fontId, objFontSize: 1,  objFontColor: $color, objFontStyle: $fontStyle, objText: $text, objJustify: $align, newX: 0, objRealWidth: 0, objRealHeight: 0,angle:$angle,is_repeat:$is_repeat,lock_index:$lock_index};
		} else if($security_type == "Ghost Image") {

			if($width <= 10)
				$width = defaultGhostImgWidth ;
			if($height <= 10)
				$height = defaultGhostImgHeight;
			obj = {objSrc: ghostImage, objType: "image",security_type:"Ghost Image", objDrag: true, objDelete: true, objID: $id, objX: $x  * certRatioX, objY: $y * certRatioY, objWidth: $width * certRatioX, objHeight: $height * certRatioY, objFontId: $fontId, objFontSize: $fontSize, objFontStyle: $fontStyle, objText: $text, objJustify: $align, newX: 0, objRealWidth: 0, objRealHeight: 0,lock_index:$lock_index};
		} else if($security_type == "Anti-Copy") {
			if($width <= 10)
				$width = defaultAntiCopyWidth;
			if($height <= 10)
				$height = defaultAntiCopyheight;
			obj = {objSrc: anticopyImage, objType: "image",security_type:"Anti-Copy", objDrag: true, objDelete: true, objID: $id, objX: $x  * certRatioX, objY: $y * certRatioY, objWidth: $width * certRatioX, objHeight: $height * certRatioY, objFontId: $fontId, objFontSize: $fontSize, objFontStyle: $fontStyle, objText: $text, objJustify: $align, newX: 0, objRealWidth: 0, objRealHeight: 0,lock_index:$lock_index};
		} else if($security_type == "1D Barcode") {

			if($width <= 10)
				$width = defaultBarcodeWidth;
			if($height <= 10)
				$height = defaultBarcodeHeight;
			obj = {objSrc: barcodeImage, objType: "Idimage",security_type:"1D Barcode", objDrag: true, objDelete: true, objID: $id, objX: $x  * certRatioX, objY: $y * certRatioY, objWidth: $width * certRatioX, objHeight: $height * certRatioY, objFontId: $fontId, objFontSize: $fontSize, objFontStyle: $fontStyle, objText: $text, objJustify: $align, newX: 0, objRealWidth: 0, objRealHeight: 0,lock_index:$lock_index};
		} else if($security_type == "Qr Code") {
			//console.log('hiii')
			if($width <= 10)
				$width = defaultBarcodeWidth;
			if($height <= 10)
				$height = defaultBarcodeHeight;
			obj = {objSrc: '', objType: "qrCode",security_type:"Qr Code", objDrag: true, objDelete: true, objID: $id, objX: $x  * certRatioX, objY: $y * certRatioY, objWidth: $width * certRatioX, objHeight: $height * certRatioY, objFontId: $fontId, objFontSize: $fontSize, objFontStyle: $fontStyle, objText: $text, objJustify: $align, newX: 0, objRealWidth: 0, objRealHeight: 0,lock_index:$lock_index,include_image:$include_image,image:$image,combo_qr_text:$combo_qr_text};
		}
		
		//store value when add
		else if($security_type == 'Static Microtext Border') {


			obj = {objSrc: '', objType: "microTextBorder",security_type:"Static Microtext Border", objDrag: true, objDelete: true, objID: $id, objX: $x  * certRatioX, objY: $y * certRatioY, objWidth: ($width * certRatioX) / certRatioX , objHeight: ($height * certRatioY) / certRatioY , objFontId: $fontId, objFontSize: 1.3,  objFontColor: $color, objFontStyle: $fontStyle, objText: $text, objJustify: $align, newX: 0, objRealWidth:0, objRealHeight: 0,lock_index:$lock_index};
		}else if($security_type == "Micro Text Border") {


			obj = {objSrc: '', objType: "microTextBorder",security_type:"Micro Text Border", objDrag: true, objDelete: true, objID: $id, objX: $x  * certRatioX, objY: $y * certRatioY, objWidth: ($width * certRatioX) / certRatioX , objHeight: ($height * certRatioY) / certRatioY , objFontId: $fontId, objFontSize: 1.3,  objFontColor: $color, objFontStyle: $fontStyle, objText: $text, objJustify: $align, newX: 0, objRealWidth:0, objRealHeight: 0,lock_index:$lock_index};
		}else if($security_type == "Dynamic Image"){

			obj = {objSrc: '', objType: "customImage",security_type:"Dynamic Image", objDrag: true, objDelete: true, objID: $id, objX: $x  * certRatioX, objY: $y * certRatioY, objWidth: ($width * certRatioX) / certRatioX , objHeight:  ($height  * certRatioY) / certRatioY , objFontId: $fontId, objFontSize: 1.3,  objFontColor: $color, objFontStyle: $fontStyle, objText: $text, objJustify: $align, newX: 0, objRealWidth:0, objRealHeight: 0,image:$image,template_name:$template_name,formType:$cmd,lock_index:$lock_index,grey_scale:$grey_scale,is_uv_image:$uvImage,is_transparent_image:$transparentImage};

		}else if($security_type == "Static Image"){

			obj = {objSrc: '', objType: "customImage",security_type:"Static Image", objDrag: true, objDelete: true, objID: $id, objX: $x  * certRatioX, objY: $y * certRatioY, objWidth: ($width * certRatioX) / certRatioX , objHeight:  ($height  * certRatioY) / certRatioY , objFontId: $fontId, objFontSize: 1.3,  objFontColor: $color, objFontStyle: $fontStyle, objText: $text, objJustify: $align, newX: 0, objRealWidth:0, objRealHeight: 0,image:$image,template_name:$template_name,formType:$cmd,lock_index:$lock_index,grey_scale:$grey_scale,is_uv_image:$uvImage,is_transparent_image:$transparentImage};

		}else if($security_type == "UV Repeat line"){

			obj = {objSrc: '', objType: "uvRepeatLine",security_type:"UV Repeat line", objDrag: true, objDelete: true, objID: $id, objX: $x  * certRatioX, objY: $y * certRatioY, objWidth: $width * certRatioX, objHeight: $height * certRatioY, objFontId: $fontId, objFontSize: $fontSize, objFontColor: $excolor, objFontStyle: $fontStyle, objText: $text, objJustify: $align, newX: 0, objRealWidth: 0, objRealHeight: 0,angle:$angle,lock_index:$lock_index};

		} else if($security_type == "UV Repeat Fullpage"){
			obj = {objSrc: '', objType: "uvRepeatFullpage",security_type:"UV Repeat Fullpage", objDrag: true, objDelete: true, objID: $id, objX: $x  * certRatioX, objY: $y * certRatioY, objWidth: $width * certRatioX, objHeight: $height * certRatioY, objFontId: $fontId, objFontSize: $fontSize, objFontColor: $excolor, objFontStyle: $fontStyle, objText: $text, objJustify: $align, newX: 0, objRealWidth: 0, objRealHeight: 0,angle:$angle,line_gap:$line_gap,lock_index:$lock_index};
		}else if($security_type == "Invisible Image"){

			obj = {objSrc: '', objType: "invisibleImage",security_type:"Invisible Image", objDrag: true, objDelete: true, objID: $id, objX: $x  * certRatioX, objY: $y * certRatioY, objWidth: ($width * certRatioY) / certRatioY, objHeight:($height * certRatioY) / certRatioY, objFontId: $fontId, objFontSize: 1.3,  objFontColor: $color, objFontStyle: $fontStyle, objText: $text, objJustify: $align, newX: 0, objRealWidth:0, objRealHeight: 0,image:$image,template_name:$template_name,lock_index:$lock_index};

		}
		
		
		else if($security_type == "2D Barcode"){
			obj = {objSrc: '', objType: "2D Barcode",security_type:"2D Barcode", objDrag: true, objDelete: true, objID: $id, objX: $x  * certRatioX, objY: $y * certRatioY, objWidth: ($width * certRatioY) / certRatioX, objHeight:($height * certRatioY) / certRatioY, objFontId: $fontId, objFontSize: 1.3,  objFontColor: $color, objFontStyle: $fontStyle, objText: $text, objJustify: $align, newX: 0, objRealWidth:0, objRealHeight: 0,image:$image,template_name:$template_name,lock_index:$lock_index};
		}
		
		else {
			// console.log($security_type);
			return;
		}
		// add new element into array

		objList.push(obj);
	}

	if($refresh) {
		draw();
	}
}

var temp_obj = [];
var invisibleImage_obj = [];
function draw(withAnchors, withBorders)
{	
	
	context.clearRect(0, 0, canvas.width, canvas.height);

	var horizontal_array = [];
	var h_key = 0;
	var default_array = [];
	var d_key = 0;
	var vertical_array = [];
	var v_key = 0;
	var microline_width = [];
	var m_key = 0;


	objList.forEach(function (item, i) {
		if(item.objType == "image" && item.objSrc != '') {
			context.globalAlpha = 1;
			
			for(var i = 0; i < 1000; i++);
			context.drawImage(item.objSrc, item.objX, item.objY, item.objWidth, item.objHeight);
			if(selObjId == item.objID) {
				if(isDown){
					canvas.style.cursor= 'move';
				}
				else if(isCtrl == true){
					canvas.style.cursor= 'pointer';
					context.strokeStyle = 'green';
					context.strokeRect(item.objX, item.objY, item.objWidth, item.objHeight);
				}else{

					canvas.style.cursor= 'pointer';
					context.strokeStyle = 'blue';
					context.strokeRect(item.objX, item.objY, item.objWidth, item.objHeight);
				} 
			}
		}
		else if(item.objType == "Idimage" && item.objSrc != '') {
			context.globalAlpha = 1;
			for(var i = 0; i < 1000; i++);
			context.drawImage(item.objSrc, item.objX, item.objY, item.objWidth, item.objHeight);
			if(selObjId == item.objID) {
				if(isDown){
					canvas.style.cursor= 'move';
				}
				else if(isCtrl == true){
					canvas.style.cursor= 'pointer';
					context.strokeStyle = 'green';
					context.strokeRect(item.objX, item.objY, item.objWidth, item.objHeight);
				}else{
					canvas.style.cursor= 'pointer';
					context.strokeStyle = 'blue';
					context.strokeRect(item.objX, item.objY, item.objWidth, item.objHeight);
				}
			}
		}
		else if(item.objType == "Qrimage") {
			context.globalAlpha = 1;
			for(var i = 0; i < 10000; i++);
			if(item.include_image == '1'){
				//after image upload complete display image inside qr code
				// console.log(qrImage);
				if(qrImage.complete){
					
					if(item.image != undefined){
						qrImage.src = item.image;
					}
					context.drawImage(qrImage,0,0,qrImage.width,qrImage.height,item.objX,item.objY,item.objWidth,item.objHeight);
					
				}else{
					/*console.log(item.image);*/
					qrImage.src = item.image;
						qrImage.onload = function(){
						imageWidth = item.objWidth;
						imageHeight = item.objHeight;
						imageRight = item.objX + item.objWidth;
						imageBottom = item.objY + item.objHeight;
						context.drawImage(qrImage,0,0,qrImage.width,qrImage.height,item.objX,item.objY,item.objWidth,item.objHeight);
					}
				}
				
			}else{
				context.drawImage(item.objSrc, item.objX, item.objY, item.objWidth, item.objHeight);

			}
			if(selObjId == item.objID) {
				if(isDown){
					canvas.style.cursor= 'move';
				}
				else if(isCtrl  == true){
					canvas.style.cursor= 'pointer';
					context.strokeStyle = 'green';
					context.strokeRect(item.objX, item.objY, item.objWidth, item.objHeight);
				}else{

					canvas.style.cursor= 'pointer';
					context.strokeStyle = 'blue';
					context.strokeRect(item.objX, item.objY, item.objWidth, item.objHeight);
				} 
			}
		}else if(item.objType == "qrCode") {
			context.globalAlpha = 1;
			if(item.image != ''){
				qrCodeImage.src = item.image
			}
			if(item.include_image == 1){

				context.drawImage(qrCodeImage, item.objX, item.objY, item.objWidth, item.objHeight);

			}else{
				
				for(var i = 0; i < 1000; i++);
				context.drawImage(qrCodeImage, item.objX, item.objY, item.objWidth, item.objHeight);
			}
			drawDragAnchor(item.objX,item.objY);
			drawDragAnchor(item.objX + item.objWidth,item.objY);
			drawDragAnchor(item.objX + item.objWidth,item.objY + item.objHeight);
			drawDragAnchor(item.objX,item.objHeight + item.objY);
			if(selObjId == item.objID) {
				if(isDown){
					canvas.style.cursor= 'move';
				}
				else if(isCtrl){
					canvas.style.cursor= 'pointer';
					context.strokeStyle = 'green';
					context.strokeRect(item.objX, item.objY, item.objWidth, item.objHeight);
				}else{
					canvas.style.cursor= 'pointer';
					context.strokeStyle = 'blue';
					context.strokeRect(item.objX, item.objY, item.objWidth, item.objHeight);
				}
			}
		}
		else if(item.objType == 'normaltext'){
			context.globalAlpha = 1;
			var fname = getFont(item.objFontId, item.objFontStyle), italic = '', bold = '';

			// console.log("fname");
			// console.log(fname);
			if(item.objFontStyle.search('B') != -1)
				bold = 'bold ';
			if(item.objFontStyle.search('I') != -1)
				italic = 'italic ';
			context.fillStyle = "#" + item.objFontColor;
			context.font = italic + bold + item.objFontSize + "px " + fname;
			// console.log(context.font);

			// Some delay to load font
			for(var i = 0; i < 1000; i++);
			context.textBaseline = 'top';
			var obj = context.measureText(item.objText);
			item.objRealWidth = Math.ceil(obj.width);
			item.objRealHeight = parseInt(item.objFontSize);
			var text = item.objText;
			var lineHeight = 15;
			var x = item.objX;
			var y = item.objY;
			var maxWidth = item.objWidth;
			var realWidth = item.objRealWidth;
			var realHeight = item.objRealHeight;

			var words = text.split(' ');

			var line = '';

			for (var n=0;n < words.length;n++) {

				var testLine =  line+words[n] +  ' ';
				var matrics = context.measureText(testLine);
				var testWidth = matrics.width;

				if(testWidth > maxWidth && n > 0){

					context.fillText(line,x,y);
					line = words[n] + ' ';
					y += lineHeight;
					item.objRealHeight += 15;
					item.objRealWidth /= 1.4;
				}else{
					line = testLine;
				}
			}

			// console.log(line);
			if(item.objJustify == 'C') {
				if(item.objWidth != 0) {
					item.newX = item.objX + (item.objWidth/2) - (item.objRealWidth/2);
				}
				else {
					item.newX = (canvas.width/2) - (item.objRealWidth/2);
				}
				context.fillText(line, item.newX, y);
				if(selObjId == item.objID) {
					if(isDown){
						canvas.style.cursor = 'move';
					}
					else if (isCtrl){
						canvas.style.cursor = 'pointer';
						context.strokeStyle = 'green';
						context.strokeRect(item.objX-padding, item.objY-padding, maxWidth+2*padding, item.objRealHeight+2*padding);	
					}else{
						canvas.style.cursor = 'pointer';
						context.strokeStyle = 'blue';
						context.strokeRect(item.objX-padding, item.objY-padding, maxWidth+2*padding, item.objRealHeight+2*padding);
					}
				}
			} else if(item.objJustify == 'L') {
				
				context.fillText(line, item.objX, y);
				if(selObjId == item.objID) {

					if(isDown){
						canvas.style.cursor = 'move';
					}else if(isCtrl){

						canvas.style.cursor = 'pointer';
						context.strokeStyle = 'green';
						context.strokeRect(item.objX-padding, item.objY-padding, maxWidth+2*padding, item.objRealHeight+2*padding);
					}else{

						canvas.style.cursor = 'pointer';
						context.strokeStyle = 'blue';
						context.strokeRect(item.objX-padding, item.objY-padding, maxWidth+2*padding, item.objRealHeight+2*padding);
					}
				}
			} else if(item.objJustify == 'R') {
				item.newX = item.objX + (maxWidth - item.objRealWidth) + padding;
				context.fillText(line, item.newX, y);

				if(selObjId == item.objID) {

					if(isDown){
						canvas.style.cursor = 'move';
					}else if(isCtrl){

						canvas.style.cursor = 'pointer';
						context.strokeStyle = 'green';
						context.strokeRect(item.objX-padding, item.objY-padding, maxWidth+2*padding, item.objRealHeight+2*padding);
					}else{

						canvas.style.cursor = 'pointer';
						context.strokeStyle = 'blue';
						context.strokeRect(item.objX-padding, item.objY-padding, maxWidth+2*padding, item.objRealHeight+2*padding);
					}
				}
			}else if(item.objJustify == 'J') {
				context.fillText(line, item.objX, y);
				if(selObjId == item.objID) {

					if(isDown){
						canvas.style.cursor = 'move';
					}else if(isCtrl){

						canvas.style.cursor = 'pointer';
						context.strokeStyle = 'green';
						context.strokeRect(item.objX-padding, item.objY-padding, maxWidth+2*padding, item.objRealHeight+2*padding);
					}else{

						canvas.style.cursor = 'pointer';
						context.strokeStyle = 'blue';
						context.strokeRect(item.objX-padding, item.objY-padding, maxWidth+2*padding, item.objRealHeight+2*padding);
					}
				}
			}
		}
		else if(item.objType == "text") {

			
			context.globalAlpha = 1;
			var fname = getFont(item.objFontId, item.objFontStyle), italic = '', bold = '';
			// console.log(fname);
			if(item.objFontStyle.search('B') != -1)
				bold = 'bold ';
			if(item.objFontStyle.search('I') != -1)
				italic = 'italic ';
			context.fillStyle = "#" + item.objFontColor;
			context.font = italic + bold + item.objFontSize + "px " + fname;
			// console.log('text');
			//console.log(context.font);
			// Some delay to load font
			for(var i = 0; i < 1000; i++);
			context.textBaseline = 'top';
			var obj = context.measureText(item.objText);
			item.objRealWidth = Math.ceil(obj.width);
			item.objRealHeight = parseInt(item.objFontSize);
			var text = item.objText;
			var lineHeight = 15;
			var x = item.objX;
			var y = item.objY;
			var maxWidth = item.objWidth;
			var realWidth = item.objRealWidth;
			var realHeight = item.objRealHeight;

			var words = text.split(' ');

			var line = '';

			for (var n=0;n < words.length;n++) {

				var testLine =  line+words[n] +  ' ';
				var matrics = context.measureText(testLine);
				var testWidth = matrics.width;

				if(testWidth > maxWidth && n > 0){

					context.fillText(line,x,y);
					line = words[n] + ' ';
					y += lineHeight;
					item.objRealHeight += 15;
					item.objRealWidth /= 1.4;
				}else{
					line = testLine;
				}
			}


			if(item.objJustify == 'C') {
				if(item.objWidth != 0) {
					item.newX = item.objX + (item.objWidth/2) - (item.objRealWidth/2);
				}
				else {
					item.newX = (canvas.width/2) - (item.objRealWidth/2);
				}
				context.fillText(line, item.newX, y);
				if(selObjId == item.objID) {
					if(isDown){
						canvas.style.cursor = 'move';
					} else if(isCtrl){
						canvas.style.cursor = 'pointer';
						context.strokeStyle = 'green';
						context.strokeRect(item.objX-padding, item.objY-padding, maxWidth+2*padding, item.objRealHeight+2*padding);
					} else{
						canvas.style.cursor = 'pointer';
						context.strokeStyle = 'blue';
						context.strokeRect(item.objX-padding, item.objY-padding, maxWidth+2*padding, item.objRealHeight+2*padding);
					}	
				}
			} else if(item.objJustify == 'L') {
				context.fillText(line, item.objX, y);
				if(selObjId == item.objID) {
					if(isDown){
						canvas.style.cursor = 'move';
					} else if(isCtrl){
						canvas.style.cursor = 'pointer';
						context.strokeStyle = 'green';
						context.strokeRect(item.objX-padding, item.objY-padding, maxWidth+2*padding, item.objRealHeight+2*padding);
					} else{
						canvas.style.cursor = 'pointer';
						context.strokeStyle = 'blue';
						context.strokeRect(item.objX-padding, item.objY-padding, maxWidth+2*padding, item.objRealHeight+2*padding);
					}	
				}
			} else if(item.objJustify == 'R') {
				item.newX = item.objX + (maxWidth - item.objRealWidth) + padding;
				context.fillText(line, item.newX, y);

				if(selObjId == item.objID) {
					if(isDown){
						canvas.style.cursor = 'move';
					} else if(isCtrl){
						canvas.style.cursor = 'pointer';
						context.strokeStyle = 'green';
						context.strokeRect(item.objX-padding, item.objY-padding, maxWidth+2*padding, item.objRealHeight+2*padding);
					} else{
						canvas.style.cursor = 'pointer';
						context.strokeStyle = 'blue';
						context.strokeRect(item.objX-padding, item.objY-padding, maxWidth+2*padding, item.objRealHeight+2*padding);
					}	
				}
			}else if(item.objJustify == 'J') {
				context.fillText(line, item.objX, y);
				if(selObjId == item.objID) {
					if(isDown){
						canvas.style.cursor = 'move';
					} else if(isCtrl){
						canvas.style.cursor = 'pointer';
						context.strokeStyle = 'green';
						context.strokeRect(item.objX-padding, item.objY-padding, maxWidth+2*padding, item.objRealHeight+2*padding);
					} else{
						canvas.style.cursor = 'pointer';
						context.strokeStyle = 'blue';
						context.strokeRect(item.objX-padding, item.objY-padding, maxWidth+2*padding, item.objRealHeight+2*padding);
					}	
				}
			}
		}
		

		else if(item.objType == "microTextBorder"){
			context.globalAlpha = 1;

			var fname = getFont(item.objFontId, item.objFontStyle), italic = '', bold = '';

			if(item.objFontStyle.search('B') != -1)
				bold = 'bold ';
			if(item.objFontStyle.search('I') != -1)
				italic = 'italic ';
			context.fillStyle = "#" + item.objFontColor;
			context.font = italic + bold + item.objFontSize + "px " + fname;
			// Some delay to load fonts
			for(var i = 0; i < 1000; i++);
			context.textBaseline = 'top';
			var obj = context.measureText(item.objText);
			item.objRealWidth = Math.ceil(obj.width);
			item.objRealHeight = parseInt(item.objFontSize);
			var w = context.measureText(item.objText).width;
			default_array[d_key] = w;

			localStorage.setItem("defaultvalue",JSON.stringify(default_array));
			d_key++;

			context.save();
			var message = [];
			// Repeate Text upto width
			for(i=1;i<=1000;i++){

				if(i*w > item.objWidth){
					break;
				}
				message[i] = item.objText;
			}
			var messageString = message.toString();
			var removeComma = messageString.split(',').join('');
			var hw = context.measureText(removeComma).width;

			horizontal_array[h_key] = hw;

			localStorage.setItem('horizontal_value',JSON.stringify(horizontal_array));
			h_key++;

			context.fillText(removeComma,item.objX, item.objY);
			// Repeate Text Upto Height
			var firstVertical = [];
			for(i=1;i<=1000;i++){
				if(i*w > item.objHeight){
					break;
				}
				firstVertical[i] = item.objText;
			}
			var firstVerticalString = firstVertical.toString();
			var removeCommaVertical = firstVerticalString.split(',').join('');
			context.translate(item.objX + item.objY,item.objY);
			context.rotate( Math.PI / 2 );
			context.fillText(removeCommaVertical,0, item.objY);
			var vw = context.measureText(removeCommaVertical).width;

			vertical_array[v_key] = vw;
			localStorage.setItem("vertical_value",JSON.stringify(vertical_array));
			v_key++;

			context.translate(0,item.objY);
			context.rotate( 3 * Math.PI / 2 );
			context.fillText(removeComma,0,vw);

			context.translate(hw,0);
			context.rotate( Math.PI / 2 );
			context.fillText(removeCommaVertical,0,0);

			context.restore();
			drawDragAnchor(item.objX,item.objY);
			drawDragAnchor(item.objX + item.objWidth,item.objY);
			drawDragAnchor(item.objX + item.objWidth,item.objY + item.objHeight);
			drawDragAnchor(item.objX,item.objHeight + item.objY);
			if(selObjId == item.objID) {

				if(isDown)

					canvas.style.cursor = 'move';
				else if (isCtrl) {
					canvas.style.cursor = 'pointer';
					context.strokeStyle = 'green';
					context.strokeRect(item.objX, item.objY, hw, vw);
				}else{
					canvas.style.cursor = 'pointer';
					context.strokeStyle = 'blue';
					context.strokeRect(item.objX, item.objY, hw, vw);
				}
			}
		}else if(item.objType == "customImage"){
		
			
			var template_name = item.template_name
			item.template_name = template_name.replace(' ','',template_name)
			context.globalAlpha = 1;
			if(item.include_image == undefined){
				item.include_image = 1
			}
			// console.log("hi");
			if(item.is_uv_image == 1){
				var uvImage = new Image();
				
				var itemImage = item.image;
				var name = itemImage.split('.');
				/*var file_name = name[0];	
				var extension = name[1];*/
				/*console.log('ABC'+itemImage.split('.').pop());	
				console.log(itemImage.split(".").slice(0,-1).join(".") || itemImage + "");

				 console.log('ABC'+itemImage);*/
				 /*Updated by Mandar*/
				 var file_name = itemImage.split(".").slice(0,-1).join(".") || itemImage + "";	
				var extension = itemImage.split('.').pop();
				if(item.template_name){
						// if template name available
					
					uvImage.src = config+'/'+item.template_name+'/'+file_name+'_uv.'+extension;
				}else{
					// if template name not available
					uvImage.src = config_static+'/templates/customImages/'+file_name+'_uv.'+extension;
				}


				if(uvImage.complete){

					context.drawImage(uvImage,0,0,uvImage.width,uvImage.height,item.objX,item.objY,item.objWidth,item.objHeight);
						drawDragAnchor(item.objX,item.objY);
						drawDragAnchor(item.objX + item.objWidth,item.objY);
						drawDragAnchor(item.objX + item.objWidth,item.objY + item.objHeight);
						drawDragAnchor(item.objX,item.objHeight + item.objY);
				}else{
					uvImage.onload = function(){
					imageWidth = item.objWidth;
					imageHeight = item.objHeight;
					imageRight = item.objX + item.objWidth;
					imageBottom = item.objY + item.objHeight;
					context.drawImage(uvImage,0,0,uvImage.width,uvImage.height,item.objX,item.objY,item.objWidth,item.objHeight);
					}
				}
				temp_obj.push(uvImage);
				context.restore();
			}else{
				if(item.grey_scale != 0){
					var temp_name = new Image();
					// console.log(item.template_name);
					if(item.template_name){
						// if template name available
						temp_name.src = config+'/'+item.template_name+'/'+item.image;
					}else{

						// if template name not available
						temp_name.src = config_static+'/templates/customImages/'+item.image+'';
					}
					if(temp_name.complete){

						context.drawImage(temp_name,0,0,temp_name.width,temp_name.height,item.objX,item.objY,item.objWidth,item.objHeight);
						drawDragAnchor(item.objX,item.objY);
						drawDragAnchor(item.objX + item.objWidth,item.objY);
						drawDragAnchor(item.objX + item.objWidth,item.objY + item.objHeight);
						drawDragAnchor(item.objX,item.objHeight + item.objY);
					}else{
						// console.log('load Image');
							temp_name.onload = function(){
							imageWidth = item.objWidth;
							imageHeight = item.objHeight;
							imageRight = item.objX + item.objWidth;
							imageBottom = item.objY + item.objHeight;
							context.drawImage(temp_name,0,0,temp_name.width,temp_name.height,item.objX,item.objY,item.objWidth,item.objHeight);
						}
					}

					temp_obj.push(temp_name);
					context.restore();
				}else{
					var grey_image = new Image();
					var itemImage = item.image;
					var name = itemImage.split('.');
					/*var file_name = name[0];	
					var extension = name[1];*/
					/*Updated by Mandar*/
				 var file_name = itemImage.split(".").slice(0,-1).join(".") || itemImage + "";	
				var extension = itemImage.split('.').pop();
					// grey_image.src = config+'/customImages/'+item.image+'';
					if(item.template_name){
						// if template name available
						grey_image.src = config+'/'+item.template_name+'/'+file_name+'_bw.'+extension;
					}else{
						// if template name not available
						grey_image.src = config_static+'/templates/customImages/'+file_name+'_bw.'+extension;
					}
					if(grey_image.complete){
						//	console.log(grey_image);
							var width = item.objWidth;
				            var height = item.objHeight;
				            context.drawImage(grey_image,0,0,grey_image.width,grey_image.height,item.objX,item.objY,item.objWidth,item.objHeight);

			            	drawDragAnchor(item.objX,item.objY);
							drawDragAnchor(item.objX + item.objWidth,item.objY);
							drawDragAnchor(item.objX + item.objWidth,item.objY + item.objHeight);
							drawDragAnchor(item.objX,item.objHeight + item.objY);
		        	}else{
		        		grey_image.onload = function(){

							imageWidth = item.objWidth;
							imageHeight = item.objHeight;
							imageRight = item.objX + item.objWidth;
							imageBottom = item.objY + item.objHeight;
							context.drawImage(grey_image,0,0,grey_image.width,grey_image.height,item.objX,item.objY,item.objWidth,item.objHeight);
			        	}
		        	}

		        	temp_obj.push(grey_image);
					context.restore();
				}
			}
			if(selObjId == item.objID) {
				if(isDown)
					canvas.style.cursor= 'move';
				else if (isCtrl) {
					canvas.style.cursor= 'pointer';
					context.strokeStyle = 'green';
					context.strokeRect(item.objX, item.objY, item.objWidth, item.objHeight);
				}else{
					canvas.style.cursor= 'pointer';
					context.strokeStyle = 'blue';
					context.strokeRect(item.objX, item.objY, item.objWidth, item.objHeight);
				}
				
			}
		}else if(item.objType == "invisibleImage"){
			context.globalAlpha = 1;
			var img = new Image();

			if(item.template_name){
				// if template name available
				img.src = '../uploads/'+item.template_name+'/'+item.image;
			}else{
				// if template name not available
				img.src = '../uploads/customImages/'+item.image+'';
			}

			if(img.complete){
				// draw image from height and width

				context.drawImage(img,0,0,img.width,img.height,item.objX,item.objY,item.objWidth,item.objHeight);

				context.fillStyle = '#FFFF00';
				context.fillRect(item.objX, item.objY, item.objWidth, item.objHeight);
				drawDragAnchor(item.objX,item.objY);
				drawDragAnchor(item.objX + item.objWidth,item.objY);
				drawDragAnchor(item.objX + item.objWidth,item.objY + item.objHeight);
				drawDragAnchor(item.objX,item.objHeight + item.objY);
			}else{
				img.onload = function(){

					imageWidth = item.objWidth;
					imageHeight = item.objHeight;
					imageRight = item.objX + item.objWidth;
					imageBottom = item.objY + item.objHeight;
					context.drawImage(img,0,0,img.width,img.height,item.objX,item.objY,item.objWidth,item.objHeight);
					context.fillStyle = '#FFFF00';
					context.fillRect(item.objX, item.objY, item.objWidth, item.objHeight);
				}
			}
			invisibleImage_obj.push(img);
			context.restore();
			if(selObjId == item.objID) {
				if(isDown){
					canvas.style.cursor= 'move';
				}
				else if (isCtrl) {
					canvas.style.cursor= 'pointer';
					context.strokeStyle = 'green';
					context.strokeRect(item.objX, item.objY, item.objWidth, item.objHeight);
				}else{
					canvas.style.cursor= 'pointer';
					context.strokeStyle = 'blue';
					context.strokeRect(item.objX, item.objY, item.objWidth, item.objHeight);
				}	
			}
		}else if(item.objType == "uvRepeatLine"){

			context.globalAlpha = 1;
			var fname = getFont(item.objFontId, item.objFontStyle), italic = '', bold = '';

			if(item.objFontStyle.search('B') != -1)
				bold = 'bold ';
			if(item.objFontStyle.search('I') != -1)
				italic = 'italic ';
			context.fillStyle = "#" + item.objFontColor;
			context.font = italic + bold + item.objFontSize + "px " + fname;
			// Some delay to load font
			for(var i = 0; i < 1000; i++);
			context.textBaseline = 'top';
			var obj = context.measureText(item.objText);
			item.objRealWidth = Math.ceil(obj.width);
			item.objRealHeight = parseInt(item.objFontSize);



			if(item.objRealWidth < 50)
				item.objRealWidth = 50;
			if(item.objRealHeight < 10)
				item.objRealHeight = 10;


			if(item.objJustify == 'C') {

				item.newX = item.objX + (item.objWidth/2) - (item.objRealWidth/2);

				context.save();
				context.translate(item.newX,item.objY);
				context.rotate(item.angle * Math.PI / -180);
				context.fillText(item.objText,0, 0);

				if(selObjId == item.objID) {
					if(isDown)
						canvas.style.cursor = 'move';
					else if (isCtrl) {
						canvas.style.cursor = 'pointer';
						context.strokeStyle = 'green';
						context.strokeRect(0 - ((item.objWidth/2) - (item.objRealWidth/2) + padding), -5, item.objWidth+2*padding, item.objRealHeight+2*padding);
					}else {
						canvas.style.cursor = 'pointer';
						context.strokeStyle = 'blue';
						context.strokeRect(0 - ((item.objWidth/2) - (item.objRealWidth/2) + padding), -5, item.objWidth+2*padding, item.objRealHeight+2*padding);
					}
				}
				context.restore();
			} else if(item.objJustify == 'L' || item.objJustify == 'J') {
				context.save();
				context.translate(item.objX - padding,item.objY);
				context.rotate(item.angle * Math.PI / -180);
				context.fillText(item.objText,0, 0);
				if(selObjId == item.objID) {
					if(isDown){

						canvas.style.cursor = 'move';
					}
					else if (isCtrl) {
						canvas.style.cursor = 'pointer';
						context.strokeStyle = 'green';

						context.strokeRect(0, -5, item.objWidth+2*padding, item.objRealHeight+2*padding);
					}else {
						canvas.style.cursor = 'pointer';
						context.strokeStyle = 'blue';
						context.strokeRect(0, -5, item.objWidth+2*padding, item.objRealHeight+2*padding);
					}	
				}
				context.restore();

			} else if(item.objJustify == 'R') {
				item.newX = item.objX + (item.objWidth - item.objRealWidth) + padding;
				context.save();
				context.translate(item.newX,item.objY);
				context.rotate(item.angle * Math.PI / -180);
				context.fillText(item.objText,0, 0);


				if(selObjId == item.objID) {
					if(isDown)
						canvas.style.cursor = 'move';
					else if (isCtrl) {

						canvas.style.cursor = 'pointer';
						context.strokeStyle = 'green';
						context.strokeRect(0 - ((item.objWidth - item.objRealWidth) + 2*padding), -5, item.objWidth+2*padding, item.objRealHeight+2*padding);
					}else{
						canvas.style.cursor = 'pointer';
						context.strokeStyle = 'blue';
						context.strokeRect(0 - ((item.objWidth - item.objRealWidth) + 2*padding), -5, item.objWidth+2*padding, item.objRealHeight+2*padding);
					}

				}
				context.restore();
			}
		}
		else if(item.objType == "securityline") {

			// console.log(item.objText+'  '+item.field_extra_font_case);
			var fname = getFont(item.objFontId, item.objFontStyle), italic = '', bold = '';

			if(item.field_extra_font_case==1){
				item.objText=item.objText.toUpperCase();
			}
			if(item.objFontStyle.search('B') != -1)
				bold = 'bold ';
			if(item.objFontStyle.search('I') != -1)
				italic = 'italic ';
			context.fillStyle = "#" + item.objFontColor;
			context.font = italic + bold + item.objFontSize + "px " + fname;
			// Some delay to load font
			for(var i = 0; i < 1000; i++);
			context.textBaseline = 'top';
			var obj = context.measureText(item.objText);
			item.objRealWidth = Math.ceil(obj.width);
			item.objRealHeight = parseInt(item.objFontSize);
			


			if(item.objRealWidth < 50)
				item.objRealWidth = 50;
			if(item.objRealHeight < 10)
				item.objRealHeight = 10;
			context.globalAlpha = item.text_opicity;
			if(item.objJustify == 'C') {

				item.newX = item.objX + (item.objWidth/2) - (item.objRealWidth/2);

				context.save();
				context.translate(item.newX,item.objY);
				context.rotate(item.angle * Math.PI / -180);
				context.fillText(item.objText,0, 0);

				if(selObjId == item.objID) {
					if(isDown)
						canvas.style.cursor = 'move';
					else if (isCtrl) {
						canvas.style.cursor = 'pointer';
						context.strokeStyle = 'green';
						context.strokeRect(0 - ((item.objWidth/2) - (item.objRealWidth/2) + padding), -5, item.objWidth+2*padding, item.objRealHeight+2*padding);
					}else {
						canvas.style.cursor = 'pointer';
						context.strokeStyle = 'blue';
						context.strokeRect(0 - ((item.objWidth/2) - (item.objRealWidth/2) + padding), -5, item.objWidth+2*padding, item.objRealHeight+2*padding);
					}
				}
				context.restore();
			} else if(item.objJustify == 'L' || item.objJustify == 'J') {
				context.save();
				context.translate(item.objX - padding,item.objY);
				context.rotate(item.angle * Math.PI / -180);
				context.fillText(item.objText,0, 0);
				if(selObjId == item.objID) {
					if(isDown)

						canvas.style.cursor = 'move';
					else if (isCtrl) {
						canvas.style.cursor = 'pointer';
						context.strokeStyle = 'green';

						context.strokeRect(0, -5, item.objWidth+2*padding, item.objRealHeight+2*padding);
					}else{
						canvas.style.cursor = 'pointer';
						context.strokeStyle = 'blue';

						context.strokeRect(0, -5, item.objWidth+2*padding, item.objRealHeight+2*padding);
					}
				}
				context.restore();

			} else if(item.objJustify == 'R') {
				item.newX = item.objX + (item.objWidth - item.objRealWidth) + padding;
				context.save();
				context.translate(item.newX,item.objY);
				context.rotate(item.angle * Math.PI / -180);
				context.fillText(item.objText,0, 0);


				if(selObjId == item.objID) {
					if(isDown)
						canvas.style.cursor = 'move';
					else if (isCtrl) {
						canvas.style.cursor = 'pointer';
						context.strokeStyle = 'green';

						context.strokeRect(0 - ((item.objWidth - item.objRealWidth) + 2*padding), -5, item.objWidth+2*padding, item.objRealHeight+2*padding);
					}else{
						canvas.style.cursor = 'pointer';
						context.strokeStyle = 'blue';

						context.strokeRect(0 - ((item.objWidth - item.objRealWidth) + 2*padding), -5, item.objWidth+2*padding, item.objRealHeight+2*padding);
					}
				}
				context.restore();
			}

		}else if(item.objType == "microLine") {

			
			context.globalAlpha = 1;
			var fname = getFont(item.objFontId, item.objFontStyle), italic = '', bold = '';

			if(item.objFontStyle.search('B') != -1)
				bold = 'bold ';
			if(item.objFontStyle.search('I') != -1)
				italic = 'italic ';
			context.fillStyle = "#" + item.objFontColor;
			context.font = italic + bold + item.objFontSize + "px " + fname;
			// Some delay to load font
			for(var i = 0; i < 1000; i++);
			context.textBaseline = 'top';
			var obj = context.measureText(item.objText);

			var objRealWidth = Math.ceil(obj.width);
			var objRealHeight = parseInt(item.objFontSize);


			var mw = obj.width;

			microline_width[m_key] = mw;
			localStorage.setItem("microline_width",JSON.stringify(microline_width));
			m_key++;

			// console.log(objRealWidth);

			if(item.objRealWidth < 50)
				item.objRealWidth = 50;
			if(item.objRealHeight < 10)
				item.objRealHeight = 10;


			// item.objWidth = parseInt(item.objWidth)

			if(item.objJustify == 'C') {

				item.newX = item.objX + (item.objWidth/2) - (item.objRealWidth/2);

				context.save();
				context.translate(item.newX,item.objY);
				context.rotate(item.angle * Math.PI / -180);

				if(item.is_repeat == "1" || item.is_repeat == 1)
				{
					var repeat_line = [];
					for(i=1;i<=1000;i++){
						if(i*objRealWidth > item.objWidth){
						
							var wd = i * objRealWidth;
							var last_width = wd - objRealWidth;
							var extraWidth = item.objWidth - last_width;
							var stringLength = item.objText.length;
							var extraCharacter = parseInt(stringLength * extraWidth / objRealWidth);
							repeat_line[i]  = item.objText.substr(0,extraCharacter);
							break;
						}
						repeat_line[i] = item.objText;
					}
					var toString = repeat_line.toString();
					var removeComma = toString.split(',').join('');
					context.fillText(removeComma,0, 0);
				}else{
					context.fillText(item.objText, 0, 0);
				}

				if(selObjId == item.objID) {
					if(isDown)
						canvas.style.cursor = 'move';
					else if (isCtrl) {
						canvas.style.cursor = 'pointer';
						context.strokeStyle = 'green';
						context.strokeRect(0 - ((item.objWidth/2) - (item.objRealWidth/2) - padding), -5, item.objWidth+2*padding, item.objRealHeight+2*padding);
					}
					else{
						canvas.style.cursor = 'pointer';
						context.strokeStyle = 'blue';
						context.strokeRect(-5, -5, item.objWidth+2*padding, item.objRealHeight+2*padding);
					}
				}
				context.restore();
			} else if(item.objJustify == 'L' || item.objJustify == 'J') {
				context.save();
				context.translate(item.objX - padding,item.objY);
				context.rotate(item.angle * Math.PI / -180);

				if(item.is_repeat == 1)
				{

					var repeat_line = [];
					for(i=1;i<=1000;i++){

						if(i*mw > item.objWidth){
							var wd = i * mw;
							var last_width = wd - mw;
							var extraWidth = item.objWidth - last_width;
							var stringLength = item.objText.length;
							var extraCharacter = parseInt(stringLength * extraWidth / mw);
							repeat_line[i]  = item.objText.substr(0,extraCharacter);
							break;
						}
						repeat_line[i] = item.objText;
					}
					var toString = repeat_line.toString();
					var removeComma = toString.split(',').join('');
					context.fillText(removeComma,0, 0);
				}else{
					context.fillText(item.objText, 0, 0);
				}
				if(selObjId == item.objID) {
					if(isDown){
						canvas.style.cursor = 'move';
					}else if (isCtrl) {
						canvas.style.cursor = 'pointer';
						context.strokeStyle = 'green';
						context.strokeRect(0, -5, item.objWidth+2*padding, item.objRealHeight+2*padding);
					}else{
						canvas.style.cursor = 'pointer';
						context.strokeStyle = 'blue';
						context.strokeRect(0, -5, item.objWidth+2*padding, item.objRealHeight+2*padding);
					}								
				}
				context.restore();
			} else if(item.objJustify == 'R') {
				context.save();
				item.newX = item.objX + (item.objWidth - item.objRealWidth) + padding;
				context.translate(item.newX,item.objY);
				context.rotate(item.angle * Math.PI / -180);
				context.fillText(item.objText,0, 0);

				if(selObjId == item.objID) {
					if(isDown)
						canvas.style.cursor = 'move';
					else if (isCtrl) {

						canvas.style.cursor = 'pointer';
						context.strokeStyle = 'green';
						context.strokeRect(0 - ((item.objWidth/2) - (item.objRealWidth/2) + padding), -5, item.objWidth+2*padding, item.objRealHeight+2*padding);
					}else{
						canvas.style.cursor = 'pointer';
						context.strokeStyle = 'blue';
						context.strokeRect(0 - ((item.objWidth/2) - (item.objRealWidth/2) + padding), -5, item.objWidth+2*padding, item.objRealHeight+2*padding);
					}
				}
				context.restore();
			}

		}else if(item.objType == "uvRepeatFullpage") {

			context.globalAlpha = 1;
			var fname = getFont(item.objFontId, item.objFontStyle), italic = '', bold = '';

			if(item.objFontStyle.search('B') != -1)
				bold = 'bold ';
			if(item.objFontStyle.search('I') != -1)
				italic = 'italic ';
			context.fillStyle = "#" + item.objFontColor;
			context.font = italic + bold + item.objFontSize + "px " + fname;
			// Some delay to load font
			for(var i = 0; i < 1000; i++);
			context.textBaseline = 'top';
			var obj = context.measureText(item.objText);
			item.objRealWidth = Math.ceil(obj.width);
			item.objRealHeight = parseInt(item.objFontSize);

			var textHeight = item.line_gap * 2;
			var objectWidth = Math.ceil(context.measureText(item.objText).width);
			var text = new Array(objectWidth * 2).join(item.objText + ' ');

			if(item.objRealWidth < 50)
				item.objRealWidth = 50;
			if(item.objRealHeight < 10)
				item.objRealHeight = 10;


			item.newX = item.objX + (item.objWidth/2) - (item.objRealWidth/2);

			context.save();
			context.rotate(item.angle * Math.PI / -180);
			for(i=0;i<canvas.width / item.objFontSize;i++){
				context.fillText(text,-(i * textHeight),i * textHeight);
			}
			context.restore();
			
			if(selObjId == item.objID) {
				if(isDown){
					canvas.style.cursor = 'move';
				} else if(isCtrl){
					canvas.style.cursor = 'pointer';
					context.strokeStyle = 'green';
					context.strokeRect(item.objX, item.objY, item.objRealWidth+2*padding, item.objRealHeight+2*padding);
				}
				else{
					canvas.style.cursor = 'pointer';
					context.strokeStyle = 'blue';
					context.strokeRect(item.objX, item.objY, item.objRealWidth+2*padding, item.objRealHeight+2*padding);
				}
			}

		}
		
		else if(item.objType == "2D Barcode"){
			context.globalAlpha = 1;
			var temp_name = new Image();

			if(item.template_name){
				// if template name available
				temp_name.src = 'uploads/'+item.template_name+'/'+item.image;
			}else{
				// if template name not available
				temp_name.src = 'uploads/customImages/'+item.image+'';
			}

			temp_name.src = config_default+"/2dcode.png";
			if(temp_name.complete){

				context.drawImage(temp_name,0,0,temp_name.width,temp_name.height,item.objX,item.objY,item.objWidth,item.objHeight);
				drawDragAnchor(item.objX,item.objY);
				drawDragAnchor(item.objX + item.objWidth,item.objY);
				drawDragAnchor(item.objX + item.objWidth,item.objY + item.objHeight);
				drawDragAnchor(item.objX,item.objHeight + item.objY);
			}else{
					temp_name.onload = function(){
					imageWidth = item.objWidth;
					imageHeight = item.objHeight;
					imageRight = item.objX + item.objWidth;
					imageBottom = item.objY + item.objHeight;
					context.drawImage(temp_name,0,0,temp_name.width,temp_name.height,item.objX,item.objY,item.objWidth,item.objHeight);
				}
			}

			temp_obj.push(temp_name);
			context.restore();
			if(selObjId == item.objID) {
				if(isDown){
					canvas.style.cursor= 'move';
				}else if (isCtrl) {
					canvas.style.cursor= 'pointer';
					context.strokeStyle = 'green';
					context.strokeRect(item.objX, item.objY, item.objWidth, item.objHeight);
				}
				else{
					canvas.style.cursor= 'pointer';
					context.strokeStyle = 'blue';
					context.strokeRect(item.objX, item.objY, item.objWidth, item.objHeight);
				}
			}
		}
		
	});
}

//get the font of text
function getFont($id, $style)
{
	var len = fontList.length;

	for(var i = 0; i < len; i++)
	{
		if(fontList[i].id == $id) {
			if($style == '')
				return fontList[i].font_filename;
			else if($style == 'B')
				return fontList[i].font_filename;
			else if($style == 'I')
				return fontList[i].font_name;
			else if($style == 'BI')
				return fontList[i].font_name;
		}
	}
	return 'Arial';
}

//on image drag n drop
function drawDragAnchor(x,y){
	var resizerRadius = 8;
	context.beginPath();
	context.arc(x,y, 8,0,Math.PI * 2,false);
	context.fill();
}

