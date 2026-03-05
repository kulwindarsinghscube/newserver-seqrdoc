/**
 * PDFAnnotate v1.0.0
 * Author: Ravisha Heshan
 * https://github.com/RavishaHesh/PDFJsAnnotations
 */
var ids = 0;  
var PDFAnnotate = function(container_id, url, action='', jsonfile='') { 
	this.number_of_pages = 0;
	this.pages_rendered = 0;
	this.active_tool = 1; // 1 - Free hand, 2 - Text, 3 - Arrow, 4 - Rectangle
	this.fabricObjects = [];
	this.color = '#212121';
	this.placer_color = '#007bff';
	this.borderColor = '#000000';
	this.borderSize = 1;
	this.font_size = 16;
	this.active_canvas = 0;
	this.container_id = container_id;
	this.url = url;
	this.rectObjs = [];
	var inst = this;
    
	var loadingTask = PDFJS.getDocument(this.url);
	loadingTask.promise.then(function (pdf) {
	    var scale = 1;
	    inst.number_of_pages = pdf.pdfInfo.numPages;
        
	    for (var i = 1; i <= pdf.pdfInfo.numPages; i++) {
            document.getElementById("msg").innerHTML = "<img src='../pdf2pdf/loading.gif' width='30' height='30'  />";
	        pdf.getPage(i).then(function (page) {
	            var viewport = page.getViewport(scale);
                var canvas = document.createElement('canvas');
	            document.getElementById(inst.container_id).appendChild(canvas);
	            canvas.className = 'pdf-canvas';
	            canvas.height = viewport.height;
	            canvas.width = viewport.width;
	            context = canvas.getContext('2d');

	            var renderContext = {
	                canvasContext: context,
	                viewport: viewport
	            };
	            var renderTask = page.render(renderContext);
	            renderTask.then(function () {
	                $('.pdf-canvas').each(function (index, el) {
	                    $(el).attr('id', 'page-' + (index + 1) + '-canvas');
	                });
	                inst.pages_rendered++;
	                if (inst.pages_rendered == inst.number_of_pages){                         
                        inst.is_Action = action;
                        inst.json_File = jsonfile;
                        inst.initFabric();
                        document.getElementById("msg").innerHTML = "Page 1";
                    }    
	            });
	        });

	    }        
	}, function (reason) {
	    console.error(reason);
	});
	
    this.initFabric = function () { 
		var inst = this; 
        
	    $('#' + inst.container_id + ' canvas').each(function (index, el) {
	        var background = el.toDataURL("image/png");
	        var fabricObj = new fabric.Canvas(el.id, {
	            freeDrawingBrush: {
	                width: 1,
	                color: inst.color
	            }
	        });
            
            fabricObj.on('object:scaling', function(){
                var obj = fabricObj.getActiveObject(),
                width = obj.width,
                height = obj.height,
                scaleX = obj.scaleX,
                scaleY = obj.scaleY;              
                obj.set({
                    width : parseFloat(width * scaleX),
                    height : parseFloat(height * scaleY),
                    scaleX: 1,
                    scaleY: 1
                });
            });    

            fabricObj.on('mouse:down', function(options) {
                
                if(fabricObj.getActiveObject() != null)   
                {                    
                    if(fabricObj.getActiveObject().placer_type == "Ghost Image"){
                        fabricObj.getActiveObject().hasControls = false;
                    }
                    if(fabricObj.getActiveObject().type == "rect"){
                        val_name=fabricObj.getActiveObject().name;
                        document.getElementById('RectBox').value = val_name;  
                        val_id=fabricObj.getActiveObject().id;
                        document.getElementById('RectBoxId').value = val_id;
                        val_nature=fabricObj.getActiveObject().nature;
                        document.getElementById('nature').value = val_nature;
                        new_coord = fabricObj.getActiveObject().calcCoords();
                        document.getElementById('coord-list').value=new_coord.tl.x + ',' + new_coord.tl.y + ',' + new_coord.br.x + ',' + new_coord.br.y;
                        //val_width=fabricObj.getActiveObject().width;
                        val_width=(fabricObj.getActiveObject().width).toFixed(2);
                        document.getElementById('rect-width').value = val_width;
                        //val_height=fabricObj.getActiveObject().height;
                        val_height=(fabricObj.getActiveObject().height).toFixed(2);                        
                        document.getElementById('rect-height').value = val_height;
                        document.getElementById('ghost_degree_angle').value = fabricObj.getActiveObject().degree_angle;
                        document.getElementById('degree_angle').value = fabricObj.getActiveObject().degree_angle; 
                        document.getElementById('color_selector').value = fabricObj.getActiveObject().font_color; 
                        document.getElementById('opacity_val').value = fabricObj.getActiveObject().opacity_val; 
                        document.getElementById('image_path').value = fabricObj.getActiveObject().image_path; 
                        $('#placer_font_size').attr("readonly", false);                         
                    }
                    if(fabricObj.getActiveObject().nature == "placer"){
                        $("#placer_elements").show();
                        var sourceul = document.getElementById("sortable");    
                        sourceul.innerHTML='';
                        //$("#sortable li").remove();
                        var select_source = document.getElementById("source_selector");
                        $("#source_selector option").remove();
                        var sources = [];
                        sources.push('Select a Source');
                        sources.push('Current DateTime');
                        fabricObj.forEachObject(function(obj){
                            if(obj.nature=="extractor"){
                                sources.push(obj.name);
                                //$("#source_selector").append("<option value = '"+obj.name+"'>"+obj.name+"</option>")
                            }
                        });                        
                        
                        for (var i = 0; i < sources.length; i++) { 
                            var optn = sources[i]; 
                            var el = document.createElement("option"); 
                            el.textContent = optn; 
                            if(optn=="Select a Source"){
                                el.value = ''; 
                            }else{
                                el.value = optn; 
                            }
                            select_source.appendChild(el); 
                            if(optn != "Select a Source" && optn != "Current DateTime"){
                            var sourceli = document.createElement("li");
                            //sourceli.appendChild(document.createTextNode(optn));
                            sourceli.setAttribute("class", "ui-state-default"); // added line
                            sourceli.innerHTML = "<input type='checkbox' class='srName' value='{^"+optn+"^}' /> "+optn;  
                            sourceul.appendChild(sourceli);  
                            }                            
                            
                        }                         
                        select_source.value = fabricObj.getActiveObject().get("source");                         
                        var select_PlacerType = document.getElementById("placer_type");
                        select_PlacerType.value = fabricObj.getActiveObject().get("placer_type");  
                        var placer_type=document.getElementById('placer_type').value;
                        var PlacerFontName = document.getElementById("placer_font");
                        PlacerFontName.value = fabricObj.getActiveObject().get("fontName");
                        /*var PlacerFontBold = document.getElementById("placer_font_bold");
                        if(fabricObj.getActiveObject().get("fontBold")=='bold'){ 
                            PlacerFontBold.checked = true; 
                        }else{ 
                            PlacerFontBold.checked = false; 
                        } 
                        var PlacerFontItalic = document.getElementById("placer_font_italic");
                        if(fabricObj.getActiveObject().get("fontItalic")=='italic'){ 
                            PlacerFontItalic.checked = true; 
                        }else{ 
                            PlacerFontItalic.checked = false; 
                        } */
                        var PlacerFontUnderline = document.getElementById("placer_font_underline");
                        if(fabricObj.getActiveObject().get("fontUnderline")=='underline'){ 
                            PlacerFontUnderline.checked = true; 
                        }else{ 
                            PlacerFontUnderline.checked = false; 
                        }
						//var labelPlace = document.getElementById("metadata_label");
						//var labelValue = document.getElementById("metadata_value");
						var QrPlace = document.getElementById("qr_show");
                        if(fabricObj.getActiveObject().get("qrPlace")=='show'){ 
                            QrPlace.checked = true; 							
                        }else{ 
                            QrPlace.checked = false;                             
                        }	
						/*var BcPlace = document.getElementById("blockchain_show");
                        if(fabricObj.getActiveObject().get("bcPlace")=='use'){ 
                            BcPlace.checked = true;
                            labelPlace.style.display = "block";
							labelValue.style.display = "block";
                        }else{ 
                            BcPlace.checked = false; 
                            labelPlace.style.display = "none";
							labelValue.style.display = "none";
                        }						
                        labelPlace.value = fabricObj.getActiveObject().get("labelPlace");						
                        labelValue.value = fabricObj.getActiveObject().get("labelValue");*/
                        
                        var ScPlace = document.getElementById("barcode_sc"); /*Source Content*/
                        if(fabricObj.getActiveObject().get("barcodeContent")=='Source Content'){ 
                            ScPlace.checked = true;
                        }else{ 
                            ScPlace.checked = false;
                        }
                        var TabPlace = document.getElementById("barcode_tab"); /*Text at Bottom*/
                        if(fabricObj.getActiveObject().get("barcodeContentPosition")=='Text at Bottom'){ 
                            TabPlace.checked = true;
                        }else{ 
                            TabPlace.checked = false;
                        }
        
						
                        var PlacerFontSize = document.getElementById("placer_font_size");
                        PlacerFontSize.value = fabricObj.getActiveObject().get("fontSize");                        
                        var QrDetails = document.getElementById("qr_details");
                        QrDetails.value = fabricObj.getActiveObject().get("qr_details");
                        var ghost_words = document.getElementById("ghost_words");
                        ghost_words.value = fabricObj.getActiveObject().get("ghost_words");
                        var select_PlacerDisplay = document.getElementById("placer_display");
                        select_PlacerDisplay.value = fabricObj.getActiveObject().get("placer_display"); 
                        var select_QrPosition = document.getElementById("qr_position");
                        select_QrPosition.value = fabricObj.getActiveObject().get("qr_position");
                        var line_height = document.getElementById("line_height");
                        line_height.value = fabricObj.getActiveObject().get("lineHeight");
                        ShowHideAttributes(placer_type); 
                        $("#extractor_elements").hide();
                    }else{
                        $("#placer_elements").hide();
                        $("#extractor_elements").show();
                        document.getElementById('clone_ep').value = '';
                        document.getElementById('placer_type').value = '';
                        //document.getElementById('placer_display').value = '';
                        var labelPlace = document.getElementById("metadata_label");
						var labelValue = document.getElementById("metadata_value");
						var BcPlace = document.getElementById("blockchain_show"); 
                        if(fabricObj.getActiveObject().get("bcPlace")=='use'){ 
                            BcPlace.checked = true;
                            labelPlace.style.display = "block";
                            labelValue.style.display = "block";
                        }else{
                            BcPlace.checked = false;
                            labelPlace.style.display = "none";
                            labelValue.style.display = "none";
                        }                        
                        $("#blockchain_show_div").show();
                        $("#blockchain_show").show();
                        labelPlace.value = fabricObj.getActiveObject().get("labelPlace");						
                        labelValue.value = fabricObj.getActiveObject().get("labelValue");

                        // Text Verification customzation
                        var verification_type = $('input[name="verification_type"]:checked').val();
                        if(verification_type == 1){
                            $("#verification_text_show_div").show();
                            $("#verification_text_show").show();
                        }


                        var labelPlace = document.getElementById("verification_text_label");
                        var labelValue = document.getElementById("verification_text_value");
                        var BcPlace = document.getElementById("verification_text_show"); 
                        if(fabricObj.getActiveObject().get("text_verification")=='use'){ 
                            BcPlace.checked = true;
                            labelPlace.style.display = "block";
                            labelValue.style.display = "block";
                        }else{
                            BcPlace.checked = false;
                            labelPlace.style.display = "none";
                            labelValue.style.display = "none";
                        }    
                        
                        labelPlace.value = fabricObj.getActiveObject().get("text_verification_lable");                      
                        labelValue.value = fabricObj.getActiveObject().get("text_verification_value"); 

                    }
                }else{
                    panel();                    
                }
                
            });   
            
            document.onkeydown = function(e) { 
                var fabricObjs = inst.fabricObjects[inst.active_canvas]; 
                switch (e.keyCode) {
                  case 38:  // Up arrow                      
                      if(fabricObjs.getActiveObject()){                        
                        fabricObjs.getActiveObject().top -= 1;
                        fabricObjs.getActiveObject().setCoords();
                        fabricObjs.renderAll();
                        e.preventDefault();
                      }                      
                    break;
                  case 40:  // Down arrow                      
                      if(fabricObjs.getActiveObject()){
                        fabricObjs.getActiveObject().top += 1;
                        fabricObjs.getActiveObject().setCoords();
                        fabricObjs.renderAll();
                        e.preventDefault();
                      }                      
                    break;
                  case 37:  // Left arrow  
                      if(fabricObjs.getActiveObject()){
                        fabricObjs.getActiveObject().left -= 1; 
                        fabricObjs.getActiveObject().setCoords();
                        fabricObjs.renderAll();    
                        e.preventDefault();    
                      }
                    break;
                  case 39:  // Right arrow  
                      if(fabricObjs.getActiveObject()){
                        fabricObjs.getActiveObject().left += 1; 
                        fabricObjs.getActiveObject().setCoords();
                        fabricObjs.renderAll();
                        e.preventDefault();
                      }
                    break;
                  /*case 46:  // delete                
                        if(fabricObj.getActiveObject().type == "rect")
                        {  
                            if (confirm('Do you want to delete a selected rectangle?')){
                                panel();
                                fabricObj.remove(fabricObj.getActiveObject());
                            }                            
                        }
                    break;*/
                }                
            }; 
            
	        inst.fabricObjects.push(fabricObj);
	        fabricObj.setBackgroundImage(background, fabricObj.renderAll.bind(fabricObj));
	        $(fabricObj.upperCanvasEl).click(function (event) {
	            inst.active_canvas = index;
	            inst.fabricClickHandler(event, fabricObj);
	        });
	    });       
        if(inst.is_Action =='edit'){ 
            $.getJSON(inst.json_File, function(data){ 
                var data=data;
                total_pages=data.length;
                ids_arr = [];
                for(p = 0; p < total_pages; p++) {                 
                    var total_objects=data[p].objects.length; 
                    var i; 
                    for(i = 0; i < total_objects; i++) {
                        nature=data[p].objects[i].nature; 
                        id=data[p].objects[i].id;
                        page_no=data[p].objects[i].page_no;
                        name=data[p].objects[i].name; 
                        left_margin=data[p].objects[i].left; 
                        top_margin=data[p].objects[i].top; 
                        width=data[p].objects[i].width; 
                        height=data[p].objects[i].height; 
                        ids_arr.push(id);
                        if(nature == 'extractor'){ 
                            pdf.setColor('rgba(255, 0, 0, 0.3)');
                            pdf.setBorderColor('blue');
                            //pdf.id(id);
                            //pdf.nature(nature);
                            //pdf.name(name);         
                            bcPlace=data[p].objects[i].bcPlace; 
							labelPlace=data[p].objects[i].labelPlace;
							labelValue=data[p].objects[i].labelValue;
                            text_verification=data[p].objects[i].text_verification; 
                            text_verification_lable=data[p].objects[i].text_verification_lable;
                            text_verification_value=data[p].objects[i].text_verification_value; 
                            pdf.drawRectangle(id, name, nature, left_margin, top_margin, width, height, p, page_no, bcPlace, labelPlace, labelValue,text_verification,text_verification_lable,text_verification_value);         
                        }   
                        else if(nature == 'placer'){ 
                            source=data[p].objects[i].source; 
                            fontName=data[p].objects[i].fontName; 
                            fontBold=data[p].objects[i].fontBold; 
                            fontItalic=data[p].objects[i].fontItalic; 
                            fontUnderline=data[p].objects[i].fontUnderline; 
                            fontSize=data[p].objects[i].fontSize; 
                            placer_type=data[p].objects[i].placer_type; 
                            print_words=data[p].objects[i].ghost_words; 
                            placer_display=data[p].objects[i].placer_display; 
                            qr_details=data[p].objects[i].qr_details;                 
                            qr_position=data[p].objects[i].qr_position;                 
                            degree_angle=data[p].objects[i].degree_angle;                 
                            font_color=data[p].objects[i].font_color;                 
                            opacity_val=data[p].objects[i].opacity_val;                 
                            image_path=data[p].objects[i].image_path;
                            angle=data[p].objects[i].angle;        
                            lineHeight=data[p].objects[i].lineHeight;
                            qrPlace=data[p].objects[i].qrPlace;
							/*bcPlace=data[p].objects[i].bcPlace;
							labelPlace=data[p].objects[i].labelPlace;
							labelValue=data[p].objects[i].labelValue;*/
							barcodeContent=data[p].objects[i].barcodeContent;
							barcodeContentPosition=data[p].objects[i].barcodeContentPosition;
                            pdf.setColor('rgba(0, 181, 204, 0.3)');
                            pdf.setBorderColor('blue');
                            //pdf.drawPlacerRectangle(id, name, nature, left_margin, top_margin, width, height, source, fontName, fontBold, fontItalic, fontUnderline, fontSize, placer_type, placer_display, qr_details, p, page_no, print_words, qr_position, degree_angle, font_color, opacity_val, image_path, angle, lineHeight, qrPlace, bcPlace, labelPlace, labelValue, barcodeContent, barcodeContentPosition);
                            pdf.drawPlacerRectangle(id, name, nature, left_margin, top_margin, width, height, source, fontName, fontBold, fontItalic, fontUnderline, fontSize, placer_type, placer_display, qr_details, p, page_no, print_words, qr_position, degree_angle, font_color, opacity_val, image_path, angle, lineHeight, qrPlace, barcodeContent, barcodeContentPosition);
                        }
                    } 
                }
                //var ids = Math.max(...ids_arr)+1; 
                //var ids = Math.max.apply(null, ids_arr)+1;
            }).fail(function(){
                console.log("An error has occurred while calling json...");
            });
        }
	}
    
	this.fabricClickHandler = function(event, fabricObj) { 
		var inst = this; 
        val_page = inst.active_canvas
        document.getElementById('msg').innerHTML = "Page "+(parseInt(val_page)+parseInt(1));
	    if (inst.active_tool == 2) { 
	        var text = new fabric.IText('Sample text', {
	            left: event.clientX - fabricObj.upperCanvasEl.getBoundingClientRect().left,
	            top: event.clientY - fabricObj.upperCanvasEl.getBoundingClientRect().top,
	            fill: inst.color,
	            fontSize: inst.font_size,
	            selectable: true
	        });
	        fabricObj.add(text);
	        inst.active_tool = 0;
	    }
	}
    //window.addEventListener('keydown',this.check,false);
   
}

function panel(){
    document.getElementById('RectBox').value = ''; 
    document.getElementById('RectBoxId').value = '';
    document.getElementById('coord-list').value = '';
    document.getElementById('nature').value = '';
    document.getElementById('rect-width').value = '';
    document.getElementById('rect-height').value = '';    
    document.getElementById('clone_ep').value = '';
    document.getElementById('placer_type').value = '';
    document.getElementById('placer_display').value = '';    
    document.getElementById('image_path').value = '';    
    //document.getElementById('qr_position').value = '';    
    $("#placer_elements").hide();
}

PDFAnnotate.prototype.check = function (e) {
    //alert(e.keyCode);    
}

PDFAnnotate.prototype.enableSelector = function () {
	var inst = this;
	inst.active_tool = 0;
	if (inst.fabricObjects.length > 0) {
	    $.each(inst.fabricObjects, function (index, fabricObj) {
	        fabricObj.isDrawingMode = false;
	    });
	}
}

PDFAnnotate.prototype.enablePencil = function () {
	var inst = this;
	inst.active_tool = 1;
	if (inst.fabricObjects.length > 0) {
	    $.each(inst.fabricObjects, function (index, fabricObj) {
	        fabricObj.isDrawingMode = true;
	    });
	}
}

PDFAnnotate.prototype.enableAddText = function () {
	var inst = this;
	inst.active_tool = 2;
	if (inst.fabricObjects.length > 0) {
	    $.each(inst.fabricObjects, function (index, fabricObj) {
	        fabricObj.isDrawingMode = false;
	    });
	}
}

PDFAnnotate.prototype.enableRectangle = function (id_val='') {
    panel();
    var inst = this;
	var fabricObj = inst.fabricObjects[inst.active_canvas];
	inst.active_tool = 4;
	if (inst.fabricObjects.length > 0) {
		$.each(inst.fabricObjects, function (index, fabricObj) {
			fabricObj.isDrawingMode = false;
		});
	}
    if(id_val==''){
        idv=ids++;
    }else{
        idv=id_val;    
    }
    
	var rect = new fabric.Rect({		
        id:idv,        
        width: 100,
		height: 100,
		fill: inst.color,
		stroke: inst.borderColor,
		//strokeSize: inst.borderSize,
		hasRotatingPoint: false,
        cornerColor: '#e75480',
        cornerSize: 6,
        transparentCorners: false,
        strokeWidth: 0.1,
	});
    rect.toObject = (function(toObject) {
        return function() {
            return fabric.util.object.extend(toObject.call(this), {
            id: this.id,
            page_no: this.page_no,
            name: this.name,
            nature: this.nature,
            bcPlace: this.bcPlace,
			labelPlace: this.labelPlace,
			labelValue: this.labelValue,
            text_verification: this.text_verification,
            text_verification_lable: this.text_verification_lable,
            text_verification_value: this.text_verification_value, 
        });
      };
    })(rect.toObject);    
	inst.rectObjs.push(rect);
	fabricObj.add(rect);
    rect.id = idv;
    rect.page_no = inst.active_canvas;
    rect.name = 'extract_rect_'+idv; 
    rect.nature = 'extractor';
	rect.bcPlace = "";
	rect.labelPlace = "";
	rect.labelValue = "";
    rect.text_verification = "";
    rect.text_verification_lable = "";
    rect.text_verification_value = "";
    
}

PDFAnnotate.prototype.drawRectangle = function (id,name,nature,left_margin,top_margin,width_val,height_val,canvas_no,page_no, bcPlace='', labelPlace='', labelValue='',text_verification='',text_verification_lable='',text_verification_value='') {
	var inst = this;
	var fabricObj = inst.fabricObjects[canvas_no];
	inst.active_tool = 4;
	if (inst.fabricObjects.length > 0) {
		$.each(inst.fabricObjects, function (index, fabricObj) {
			fabricObj.isDrawingMode = false;
		});
	}

	var rect = new fabric.Rect({
		id:id,
        left:left_margin,
        top:top_margin,
        width: width_val,
		height: height_val,
		fill: inst.color,
		stroke: inst.borderColor,
		//strokeSize: inst.borderSize,
		hasRotatingPoint: false,
        cornerColor: '#e75480',
        cornerSize: 6,
        transparentCorners: false,
        strokeWidth: 0.1,
	});
    rect.toObject = (function(toObject) {
        return function() {
            return fabric.util.object.extend(toObject.call(this), {
            id: this.id,
            page_no: this.page_no,
            name: this.name,
            nature: this.nature,
            bcPlace: this.bcPlace,              
            labelPlace: this.labelPlace,              
            labelValue: this.labelValue,
            text_verification: this.text_verification,
            text_verification_lable: this.text_verification_lable,
            text_verification_value: this.text_verification_value, 
        });
      };
    })(rect.toObject);    
	inst.rectObjs.push(rect);
	fabricObj.add(rect);
    rect.id = id;
    rect.page_no = page_no;
    rect.name = name; 
    rect.nature = nature;
	rect.bcPlace = bcPlace;
	rect.labelPlace = labelPlace;
	rect.labelValue = labelValue;
    rect.text_verification = text_verification;
    rect.text_verification_lable = text_verification_lable;
    rect.text_verification_value = text_verification_value;
    fabricObj.setActiveObject(rect);
    fabricObj.renderAll();
    
}

PDFAnnotate.prototype.enablePlaceRectangle = function (id_val='') {

    panel();
    var inst = this;
	var fabricObj = inst.fabricObjects[inst.active_canvas];
	inst.active_tool = 4;
	if (inst.fabricObjects.length > 0) {
		$.each(inst.fabricObjects, function (index, fabricObj) {
			fabricObj.isDrawingMode = false;
		});
	}
    if(id_val==''){
        idv=ids++;
    }else{
        idv=id_val;    
    }
	var rect = new fabric.Rect({
		id:idv,
        width: 100,
		height: 100,
		fill: inst.color,
		stroke: inst.borderColor,
		//strokeSize: inst.borderSize,
		hasRotatingPoint: false,
        cornerColor: 'blue',
        cornerSize: 6,
        transparentCorners: false,
        strokeWidth: 0.1,
	});
    rect.toObject = (function(toObject) {
        return function() {
            return fabric.util.object.extend(toObject.call(this), {
            id: this.id,
            page_no: this.page_no,
            name: this.name,
            source: this.source,
            nature: this.nature,
            fontName: this.fontName,
            fontBold: this.fontBold,
            fontItalic: this.fontItalic,
            fontUnderline: this.fontUnderline,            
            fontSize: this.fontSize,
            placer_type: this.placer_type,
            ghost_words: this.ghost_words,
            placer_display: this.placer_display,
            qr_details: this.qr_details,
            qr_position: this.qr_position,
            degree_angle: this.degree_angle,
            font_color: this.font_color,
            opacity_val: this.opacity_val,
            image_path: this.image_path,
            lineHeight: this.lineHeight,
			qrPlace: this.qrPlace,
			barcodeContent: this.barcodeContent,
			barcodeContentPosition: this.barcodeContentPosition,
        });
      };
    })(rect.toObject);    
	inst.rectObjs.push(rect);
	fabricObj.add(rect);
    rect.id = idv;
    rect.page_no = inst.active_canvas;
    rect.name = 'place_rect_'+idv; 
    rect.source = '';
    rect.nature = 'placer';
    rect.fontName = '';
    rect.fontBold = '';
    rect.fontItalic = '';
    rect.fontUnderline = '';    
    rect.fontSize = 10;
    rect.placer_type = "";
    rect.ghost_words = '';
    rect.placer_display = "";
    rect.qr_details = "";
    rect.qr_position = "";
    rect.degree_angle = "";
    rect.font_color = "";
    rect.opacity_val = "";
    rect.image_path = "";
    rect.lineHeight = ""; 
	rect.qrPlace = "";
	rect.barcodeContent = "";
	rect.barcodeContentPosition = "";
}

PDFAnnotate.prototype.drawPlacerRectangle = function (id,name,nature,left_margin,top_margin,width_val,height_val,source, fontName, fontBold, fontItalic, fontUnderline, fontSize,placer_type,placer_display,qr_details,canvas_no,page_no,print_words='', qr_position='', degree_angle='', font_color='', opacity_val='', image_path='', angle='0', lineHeight="", qrPlace='', barcodeContent='', barcodeContentPosition='') { 
	var inst = this;
	var fabricObj = inst.fabricObjects[canvas_no];
	inst.active_tool = 4;
	if (inst.fabricObjects.length > 0) {
		$.each(inst.fabricObjects, function (index, fabricObj) {
			fabricObj.isDrawingMode = false;
		});
	}
	var rect = new fabric.Rect({
		id:id,
        left:left_margin,
        top:top_margin,
        width: width_val,
		height: height_val,
		fill: inst.color,
		stroke: inst.borderColor,
		//strokeSize: inst.borderSize,
		hasRotatingPoint: false,
        cornerColor: 'blue',
        cornerSize: 6,
        transparentCorners: false,
        strokeWidth: 0.1,
        angle: parseInt(angle),
	});
    rect.toObject = (function(toObject) {
        return function() {
            return fabric.util.object.extend(toObject.call(this), {
            id: this.id,
            page_no: this.page_no,
            name: this.name,
            source: this.source,
            nature: this.nature,
            fontName: this.fontName,
            fontBold: this.fontBold,
            fontItalic: this.fontItalic,
            fontUnderline: this.fontUnderline,
            fontSize: this.fontSize,
            placer_type: this.placer_type,
            ghost_words: this.ghost_words,
            placer_display: this.placer_display,
            qr_details: this.qr_details,
            qr_position: this.qr_position,
            degree_angle: this.degree_angle,
            font_color: this.font_color,
            opacity_val: this.opacity_val,        
            image_path: this.image_path,        
            lineHeight: this.lineHeight,        
            qrPlace: this.qrPlace,
			barcodeContent: this.barcodeContent,
			barcodeContentPosition: this.barcodeContentPosition,           
        });
      };
    })(rect.toObject);    
	inst.rectObjs.push(rect);
	fabricObj.add(rect);
    rect.id = id;
    rect.page_no = page_no;
    rect.name = name; 
    rect.source = source;
    rect.nature = nature;
    rect.fontName = fontName;
    rect.fontBold = fontBold;
    rect.fontItalic = fontItalic;
    rect.fontUnderline = fontUnderline;
    rect.fontSize = fontSize;
    rect.placer_type = placer_type;
    rect.ghost_words = print_words;
    rect.placer_display = placer_display;
    rect.qr_details = qr_details;
    rect.qr_position = qr_position;
    rect.degree_angle = degree_angle;
    rect.font_color = font_color;
    rect.opacity_val = opacity_val;    
    rect.image_path = image_path;   
    rect.lineHeight = lineHeight;
	rect.qrPlace = qrPlace;
	rect.barcodeContent = barcodeContent;
	rect.barcodeContentPosition = barcodeContentPosition;
    fabricObj.setActiveObject(rect);
    fabricObj.renderAll();
    
}

PDFAnnotate.prototype.enableGhostRectangle = function (fontsize,id_val='',print_words='') {
	panel();
    var inst = this;
	var fabricObj = inst.fabricObjects[inst.active_canvas];
	inst.active_tool = 4;
	if (inst.fabricObjects.length > 0) {
		$.each(inst.fabricObjects, function (index, fabricObj) {
			fabricObj.isDrawingMode = false;
		});
	}
    
    if(id_val==''){
        idv=ids++;
    }else{
        idv=id_val;    
    }    
    /*
    if(fontsize==10){
        w=78; h=14; // For 1 alphabet, char box size 15.6 
    }else if(fontsize==11){
        w=99; h=20; // 19.8 
    }else if(fontsize==12){
        w=117; h=23; // 23.4 
    }else if(fontsize==13){
        w=146; h=29; //29.2 
    }else if(fontsize==14){
        w=181; h=35; // 36.2 
    }else if(fontsize==15){
        w=202; h=40; // 40.4 
    }
    */
    print_words=parseInt(print_words);
    if(fontsize==10){
        img_width=Math.round(parseFloat(15.6) * print_words); 
        w=img_width; h=14; 
    }else if(fontsize==11){
        img_width=Math.round(parseFloat(19.8) * print_words);
        w=img_width; h=20; 
    }else if(fontsize==12){
        img_width=Math.round(parseFloat(23.4) * print_words);
        w=img_width; h=23; 
    }else if(fontsize==13){
        img_width=Math.round(parseFloat(29.2) * print_words); 
        w=img_width; h=29; 
    }else if(fontsize==14){
        img_width=Math.round(parseFloat(36.2) * print_words); 
        w=img_width; h=35; 
    }else if(fontsize==15){
        img_width=Math.round(parseFloat(40.4) * print_words); 
        w=img_width; h=40; 
    }else if(fontsize==16){
        img_width=Math.round(parseFloat(30.2) * print_words);
        w=img_width; h=29; //29.2
    }else if(fontsize==17){
        img_width=Math.round(parseFloat(30.2) * print_words);
        w=img_width; h=29; 
    }
    
	var rect = new fabric.Rect({
		id:idv,
        width: w,
		height: h,
		fill: inst.color,
		stroke: inst.borderColor,
		//strokeSize: inst.borderSize,
		hasRotatingPoint: false,
        cornerColor: 'blue',
        cornerSize: 6,
        transparentCorners: false,
        strokeWidth: 0.1,
	});
    rect.toObject = (function(toObject) {
        return function() {
            return fabric.util.object.extend(toObject.call(this), {
            id: this.id,
            page_no: this.page_no,
            name: this.name,
            source: this.source,
            nature: this.nature,
            fontName: this.fontName,
            fontBold: this.fontBold,
            fontItalic: this.fontItalic,
            fontUnderline: this.fontUnderline,            
            fontSize: this.fontSize,
            placer_type: this.placer_type,
            ghost_words: this.ghost_words,
            placer_display: this.placer_display,
            qr_details: this.qr_details,
            qr_position: this.qr_position,
            degree_angle: this.degree_angle,
            font_color: this.font_color,
            opacity_val: this.opacity_val,            
            image_path: this.image_path,   
            lineHeight: this.lineHeight,
            qrPlace: this.qrPlace,    
            bcPlace: this.bcPlace,    
            labelPlace: this.labelPlace,    
            labelValue: this.labelValue,
			barcodeContent: this.barcodeContent,
			barcodeContentPosition: this.barcodeContentPosition,    
        });
      };
    })(rect.toObject);    
	inst.rectObjs.push(rect);
	fabricObj.add(rect);
    rect.id = idv;
    rect.page_no = inst.active_canvas;
    rect.name = 'place_rect_'+idv; 
    rect.source = '';
    rect.nature = 'placer';
    rect.fontName = '';
    rect.fontBold = '';
    rect.fontItalic = '';
    rect.fontUnderline = '';    
    rect.fontSize = fontsize;
    rect.placer_type = "Ghost Image";
    rect.ghost_words = print_words;
    rect.placer_display = "";
    rect.qr_details = "";
    rect.qr_position = "";
    rect.degree_angle = 0;
    rect.font_color = "";
    rect.opacity_val = "";    
    rect.image_path = "";   
    rect.lineHeight = "";  
	rect.qrPlace = "";	
	rect.bcPlace = "";	
	rect.labelPlace = "";	
	rect.labelValue = "";	
	rect.barcodeContent = "";
	rect.barcodeContentPosition = "";
}


PDFAnnotate.prototype.changeRectBoxName = function (RectBox) {
	var inst = this;
    var fabricObj = inst.fabricObjects[inst.active_canvas];
    var activeObject = inst.fabricObjects[inst.active_canvas].getActiveObject();     
    activeObject.set("name",RectBox);	
}

PDFAnnotate.prototype.changeRectBoxId = function (RectBoxId) {
	var inst = this;
    var fabricObj = inst.fabricObjects[inst.active_canvas];
    var activeObject = inst.fabricObjects[inst.active_canvas].getActiveObject();     
    activeObject.set("id",RectBoxId);	 
}

PDFAnnotate.prototype.changeRectBoxCoords = function (RectBoxCoords) {
	var inst = this;
    var fabricObj = inst.fabricObjects[inst.active_canvas];
    var activeObject = inst.fabricObjects[inst.active_canvas].getActiveObject();    
    
    /*
    var res = RectBoxCoords.split(",");    
    activeObject.set("left",parseFloat(res[0]));	
    activeObject.set("top",parseFloat(res[1]));	
    activeObject.set("width",parseFloat(res[2]));	
    activeObject.set("height",parseFloat(res[3]));	
    //fabricObj.renderAll();   
    */
}

PDFAnnotate.prototype.changeRectWidth = function (RectBoxWidth) {
	var inst = this;
    var fabricObj = inst.fabricObjects[inst.active_canvas];
    var activeObject = inst.fabricObjects[inst.active_canvas].getActiveObject();    
    if(activeObject.placer_type != "Ghost Image"){
        activeObject.set("width",parseFloat(RectBoxWidth)); 
        activeObject.setCoords();
    }
}

PDFAnnotate.prototype.changeRectHeight = function (RectBoxHeight) {
	var inst = this;
    var fabricObj = inst.fabricObjects[inst.active_canvas];
    var activeObject = inst.fabricObjects[inst.active_canvas].getActiveObject();  
    if(activeObject.placer_type != "Ghost Image"){
        activeObject.set("height",parseFloat(RectBoxHeight));     
        activeObject.setCoords();
    }
}

PDFAnnotate.prototype.changeSourceName = function (SourceName) {
	var inst = this;
    var fabricObj = inst.fabricObjects[inst.active_canvas];
    var activeObject = inst.fabricObjects[inst.active_canvas].getActiveObject();     
    activeObject.set("source",SourceName);   
    
    /*
    var object = null;
    objects = fabricObj.getObjects();
    for (var i = 0, len = fabricObj.size(); i < len; i++) {
        if (objects[i].name && objects[i].name === SourceName) {
          object = objects[i];
          break;
        }
    }    
    */
    //alert(JSON.stringify(object));
    
}

PDFAnnotate.prototype.changePlacerType = function (placer_type) { 
	var inst = this;
    var fabricObj = inst.fabricObjects[inst.active_canvas];
    var activeObject = inst.fabricObjects[inst.active_canvas].getActiveObject();   
    activeObject.set("placer_type",placer_type); 
    ShowHideAttributes(placer_type);
}

PDFAnnotate.prototype.changePlacerFont = function (PlacerFontSize) { 
	var inst = this;
    var fabricObj = inst.fabricObjects[inst.active_canvas];
    var activeObject = inst.fabricObjects[inst.active_canvas].getActiveObject();
    activeObject.set("fontSize",parseFloat(PlacerFontSize)); 
}

PDFAnnotate.prototype.changePlacerFontName = function (PlacerFont) { 
	var inst = this;
    var fabricObj = inst.fabricObjects[inst.active_canvas];
    var activeObject = inst.fabricObjects[inst.active_canvas].getActiveObject();
    activeObject.set("fontName",PlacerFont); 
}

PDFAnnotate.prototype.changePlacerFontBold = function (PlacerFontBold) { 
	var inst = this;
    var fabricObj = inst.fabricObjects[inst.active_canvas];
    var activeObject = inst.fabricObjects[inst.active_canvas].getActiveObject();
    activeObject.set("fontBold",PlacerFontBold); 
}

PDFAnnotate.prototype.changePlacerFontItalic = function (PlacerFontItalic) { 
	var inst = this;
    var fabricObj = inst.fabricObjects[inst.active_canvas];
    var activeObject = inst.fabricObjects[inst.active_canvas].getActiveObject();
    activeObject.set("fontItalic",PlacerFontItalic); 
}

PDFAnnotate.prototype.changePlacerFontUnderline = function (PlacerFontUnderline) { 
	var inst = this;
    var fabricObj = inst.fabricObjects[inst.active_canvas];
    var activeObject = inst.fabricObjects[inst.active_canvas].getActiveObject();
    activeObject.set("fontUnderline",PlacerFontUnderline); 
}

PDFAnnotate.prototype.changeQrPlace = function (QrPlace) { 
	var inst = this;
    var fabricObj = inst.fabricObjects[inst.active_canvas];
    var activeObject = inst.fabricObjects[inst.active_canvas].getActiveObject();
    activeObject.set("qrPlace",QrPlace); 
}

PDFAnnotate.prototype.changeBlockchainPlace = function (BcPlace,checkFlag) { 
	var inst = this;
    var fabricObj = inst.fabricObjects[inst.active_canvas];
    var activeObject = inst.fabricObjects[inst.active_canvas].getActiveObject();
    activeObject.set("bcPlace",BcPlace); 
	var setMetaVal='{^'+fabricObj.getActiveObject().get("name")+'^}'; 
	if (checkFlag == true){
        document.getElementById("metadata_value").value=setMetaVal; 	
        activeObject.set("labelValue",setMetaVal); 
    }else{
        document.getElementById("metadata_value").value=''; 	
        activeObject.set("labelValue",''); 
    }
}

PDFAnnotate.prototype.changePlacerMetaLabel = function (PlacerLabel) { 
	var inst = this;
    var fabricObj = inst.fabricObjects[inst.active_canvas];
    var activeObject = inst.fabricObjects[inst.active_canvas].getActiveObject();
    activeObject.set("labelPlace",PlacerLabel); 
}

PDFAnnotate.prototype.changePlacerMetaValue = function (PlacerValue) { 
	var inst = this;
    var fabricObj = inst.fabricObjects[inst.active_canvas];
    var activeObject = inst.fabricObjects[inst.active_canvas].getActiveObject();
    activeObject.set("labelValue",PlacerValue); 
}

PDFAnnotate.prototype.BarcodeSource = function (ScPlace) { 
	var inst = this;
    var fabricObj = inst.fabricObjects[inst.active_canvas];
    var activeObject = inst.fabricObjects[inst.active_canvas].getActiveObject();
    activeObject.set("barcodeContent",ScPlace); 
}

PDFAnnotate.prototype.BarcodeTextAlignment = function (TabPlace) { 
	var inst = this;
    var fabricObj = inst.fabricObjects[inst.active_canvas];
    var activeObject = inst.fabricObjects[inst.active_canvas].getActiveObject();
    activeObject.set("barcodeContentPosition",TabPlace); 
}

PDFAnnotate.prototype.changeQrDetails = function (QrDetails) {
    var inst = this;
    var fabricObj = inst.fabricObjects[inst.active_canvas];
    var activeObject = inst.fabricObjects[inst.active_canvas].getActiveObject();
    activeObject.set("qr_details",QrDetails); 
}

PDFAnnotate.prototype.changePlacerDisplay = function (PlacerDisplay) {
	var inst = this;
    var fabricObj = inst.fabricObjects[inst.active_canvas];
    var activeObject = inst.fabricObjects[inst.active_canvas].getActiveObject();     
    activeObject.set("placer_display",PlacerDisplay); 
}

PDFAnnotate.prototype.changeQrPosition = function (QrPosition) {
	var inst = this;
    var fabricObj = inst.fabricObjects[inst.active_canvas];
    var activeObject = inst.fabricObjects[inst.active_canvas].getActiveObject();   
    activeObject.set("qr_position",QrPosition); 
}

PDFAnnotate.prototype.changePlacerColor = function (PlacerColor) {
	var inst = this;
    var fabricObj = inst.fabricObjects[inst.active_canvas];
    var activeObject = inst.fabricObjects[inst.active_canvas].getActiveObject();   
    activeObject.set("font_color",PlacerColor); 
}

PDFAnnotate.prototype.changeLineHeight = function (PlacerHeight) {
	var inst = this;
    var fabricObj = inst.fabricObjects[inst.active_canvas];
    var activeObject = inst.fabricObjects[inst.active_canvas].getActiveObject();   
    activeObject.set("lineHeight",parseInt(PlacerHeight)); 
}

PDFAnnotate.prototype.changePlacerOpacity = function (PlacerOpacity) { 
	var inst = this;
    var fabricObj = inst.fabricObjects[inst.active_canvas];
    var activeObject = inst.fabricObjects[inst.active_canvas].getActiveObject();
    activeObject.set("opacity_val",PlacerOpacity); 
}

PDFAnnotate.prototype.changePlacerAngle = function (PlacerAngle) {
	var inst = this;
    var fabricObj = inst.fabricObjects[inst.active_canvas];
    var activeObject = inst.fabricObjects[inst.active_canvas].getActiveObject();   
    activeObject.set("degree_angle",parseInt(PlacerAngle)); 
}

PDFAnnotate.prototype.changeGhostPlacerAngle = function (PlacerAngle) {	    
    var inst = this;
    var fabricObj = inst.fabricObjects[inst.active_canvas];
    var activeObject = inst.fabricObjects[inst.active_canvas].getActiveObject();   
    activeObject.set("degree_angle",parseInt(PlacerAngle)); 
    //activeObject.angle = parseInt(PlacerAngle);   
    activeObject.setAngle(parseInt(PlacerAngle)); 
    activeObject.animate('angle', parseInt(PlacerAngle), {
      onChange: fabricObj.renderAll.bind(fabricObj)
    });    
    //fabricObj.deactivateAll();   
    //fabricObj.discardActiveObject();     
    //fabricObj.renderAll();
    //fabricObj.setActiveObject(activeObject);
}

PDFAnnotate.prototype.changeImageName = function (img_name) { 
	var inst = this;
    var fabricObj = inst.fabricObjects[inst.active_canvas];
    var activeObject = inst.fabricObjects[inst.active_canvas].getActiveObject();
    activeObject.set("image_path",img_name); 
}

PDFAnnotate.prototype.createClone = function (clone_ep, id_val='') { 
	var inst = this;
    var fabricObj = inst.fabricObjects[inst.active_canvas];
    var activeObject = inst.fabricObjects[inst.active_canvas].getActiveObject();     
    if(activeObject.nature == 'extractor'){
        if(clone_ep == '' || clone_ep == 'extractor'){ 
            clone_nature = 'extractor'; 
            clone_name = 'extract_rect_'; 
            corner_color='#e75480';
            bg_color='rgba(255, 0, 0, 0.3)';
            flag='extractor';
        }
        else{ 
            clone_nature = 'placer';
            clone_name = 'place_rect_'; 
            corner_color='blue';    
            bg_color='rgba(0, 181, 204, 0.3)';
            flag='placer';
        }
    }
    else if(activeObject.nature == 'placer'){
        if(clone_ep == '' || clone_ep == 'placer'){
            clone_nature = 'placer'; 
            clone_name = 'place_rect_'; 
            corner_color='blue';
            bg_color='rgba(0, 181, 204, 0.3)';
            flag='placer';
        }
        else{
            clone_nature = 'extractor'; 
            clone_name = 'extract_rect_'; 
            corner_color='#e75480'; 
            bg_color='rgba(255, 0, 0, 0.3)';
            flag='extractor';
        }
    } 
    if(id_val==''){
        idv=ids++;
    }else{
        idv=id_val;    
    }    
    activeObject.clone(function(clone) {
        fabricObj.discardActiveObject();
        fabricObj.add(clone.set({
            id:idv,
            left: activeObject.left, 
            top: activeObject.top,
            evented: true,
            fill: bg_color,
            stroke: inst.borderColor,
            //strokeSize: inst.borderSize,
            hasRotatingPoint: false,
            cornerColor: corner_color,
            cornerSize: 6,
            transparentCorners: false,
            strokeWidth: 0.1,     
            //active: true,
        }));

        if(flag == 'extractor'){
            clone.toObject = (function(toObject) {
                return function() {
                    return fabric.util.object.extend(toObject.call(this), {
                    id: this.id,
                    page_no: this.page,
                    name: this.name, 
                    nature: this.nature
                });
              };
            })(clone.toObject);
        }else{
            clone.toObject = (function(toObject) {
                return function() {
                    return fabric.util.object.extend(toObject.call(this), {
                    id: this.id,
                    page_no: this.page,
                    name: this.name, 
                    source: this.source,
                    nature: this.nature,
                    fontName: this.fontName,
                    fontBold: this.fontBold,
                    fontItalic: this.fontItalic,
                    fontUnderline: this.fontUnderline,                    
                    fontSize: this.fontSize,
                    placer_type: this.placer_type,  
                    placer_display: this.placer_display,                    
                    qr_details: this.qr_details,                    
                    qr_position: this.qr_position,    
                    degree_angle: this.degree_angle,
                    font_color: this.font_color,
                    opacity_val: this.opacity_val,                    
                    image_path: this.image_path,                    
                    lineHeight: this.lineHeight,                
                    qrPlace: this.qrPlace,                
                    /*bcPlace: this.bcPlace,                
                    labelPlace: this.labelPlace,                
                    labelValue: this.labelValue,*/       
                    barcodeContent: this.barcodeContent,       
                    barcodeContentPosition: this.barcodeContentPosition,       
                });
              };
            })(clone.toObject);
        }
        
        clone.id = idv;
        clone.page = inst.active_canvas;
        clone.name = clone_name+idv;         
        clone.nature = clone_nature; //activeObject.nature; 
        clone.source = activeObject.name;
        clone.fontName = '';
        clone.fontBold = '';
        clone.fontItalic = '';
        clone.fontUnderline = '';        
        clone.fontSize = 10;
        clone.placer_type = "";        
        clone.placer_display = "";        
        clone.qr_details = "";        
        clone.qr_position = "";     
        clone.degree_angle = "";
        clone.font_color = "";
        clone.opacity_val = "";        
        clone.image_path = "";   
        clone.lineHeight = "";
		clone.qrPlace = "";
		/*clone.bcPlace = "";
		clone.labelPlace = "";
		clone.labelValue = "";*/
		clone.barcodeContent = "";
		clone.barcodeContentPosition = "";
        //clone.set('active', true);
        inst.rectObjs.push(clone);
        //fabricObj.trigger("clone:statechange");
        fabricObj.setActiveObject(clone);
        fabricObj.renderAll();        
    });    
    
    //fabricObj.sendToBack(activeObject);           
    fabricObj.discardActiveObject();
    fabricObj.renderAll();
    

}

PDFAnnotate.prototype.removeBackgroundImage = function () {
	var inst = this;
    var fabricObj = inst.fabricObjects[inst.active_canvas];
	fabricObj.backgroundImage = null;
}

PDFAnnotate.prototype.enableAddArrow = function () {
	var inst = this;
	inst.active_tool = 3;
	if (inst.fabricObjects.length > 0) {
	    $.each(inst.fabricObjects, function (index, fabricObj) {
	        fabricObj.isDrawingMode = false;
	        new Arrow(fabricObj, inst.color, function () {
	            inst.active_tool = 0;
	        });
	    });
	}
}

PDFAnnotate.prototype.deleteSelectedObject = function () {
	var inst = this;
	var activeObject = inst.fabricObjects[inst.active_canvas].getActiveObject();
    var fabricObj = inst.fabricObjects[inst.active_canvas];
    
	if (activeObject)
	{
	    if (confirm('Are you sure?')) {            
            objects = inst.rectObjs;
            for (var i = 0, len = fabricObj.size(); i < len; i++) {
                if (objects[i].id && objects[i].id === activeObject.id) {
                  objects.splice(i, 1);
                  break;
                } 
            }    
            inst.fabricObjects[inst.active_canvas].remove(activeObject);     
            document.getElementById('RectBox').value = ''; 
            document.getElementById('RectBoxId').value = '';
            document.getElementById('coord-list').value = '';
            document.getElementById('nature').value = '';
            document.getElementById('clone_ep').value = '';
            document.getElementById('placer_type').value = '';
            //document.getElementById('placer_display').value = '';
            $("#placer_elements").hide();    
        }
	}
    
}

PDFAnnotate.prototype.savePdf = function () {
	var inst = this;
	var doc = new jsPDF();
	$.each(inst.fabricObjects, function (index, fabricObj) {
	    if (index != 0) {
	        doc.addPage();
	        doc.setPage(index + 1);
	    }
	    doc.addImage(fabricObj.toDataURL(), 'png', 0, 0);
	});
	doc.save('sample.pdf');
}

PDFAnnotate.prototype.setBrushSize = function (size) {
	var inst = this;
	$.each(inst.fabricObjects, function (index, fabricObj) {
	    fabricObj.freeDrawingBrush.width = size;
	});
}

PDFAnnotate.prototype.setColor = function (color) {
	var inst = this;
	inst.color = color;
	$.each(inst.fabricObjects, function (index, fabricObj) {
        fabricObj.freeDrawingBrush.color = color;
    });
}

PDFAnnotate.prototype.setBorderColor = function (color) {
	var inst = this;
	inst.borderColor = color;
}

PDFAnnotate.prototype.setFontSize = function (size) {
	this.font_size = size;
}

PDFAnnotate.prototype.setBorderSize = function (size) {
	this.borderSize = size;
}

PDFAnnotate.prototype.clearActivePage = function () {
	var inst = this;
	var fabricObj = inst.fabricObjects[inst.active_canvas];
	var bg = fabricObj.backgroundImage;
	if (confirm('Are you sure?')) {
	    fabricObj.clear();
	    fabricObj.setBackgroundImage(bg, fabricObj.renderAll.bind(fabricObj));
	}
}

PDFAnnotate.prototype.serializePdf = function() {
	var inst = this;
	console.log(inst.rectObjs);
	console.log(inst.rectObjs[0].calcCoords());
	return JSON.stringify(inst.fabricObjects, null, 4);
}

PDFAnnotate.prototype.loadFromJSON = function(jsonData) {
	var inst = this;
	$.each(inst.fabricObjects, function (index, fabricObj) {
		if (jsonData.length > index) {
			fabricObj.loadFromJSON(jsonData[index])
		}
	})
}

PDFAnnotate.prototype.sizePdf = function() {
	var inst = this;
    var fabricObj = inst.fabricObjects[inst.active_canvas];
	return fabricObj.size();
}


PDFAnnotate.prototype.changeQrMetaDataPlace = function (BcPlace,checkFlag) { 
    
    var inst = this;
    var fabricObj = inst.fabricObjects[inst.active_canvas];
    var activeObject = inst.fabricObjects[inst.active_canvas].getActiveObject();
    activeObject.set("text_verification",BcPlace); 

  
    var setMetaVal='{^'+fabricObj.getActiveObject().get("name")+'^}'; 
    if (checkFlag == true){
        var count  = pdf.rectObjs.reduce((count, obj) => {
            if (obj.text_verification && obj.text_verification === 'use') {
                count++;
            }
            return count;
        }, 0);
        if(count > 10){
            alert("Only 10 fields allowed for text verification")
            var checkBox = document.getElementById("verification_text_show");
            // Get the output
            var metadata_label = document.getElementById("verification_text_label");
            var metadata_value = document.getElementById("verification_text_value");
            metadata_label.style.display = "none";
            metadata_value.style.display = "none";
            checkBox.checked = false;
            // return false;
        }else{
            document.getElementById("verification_text_value").value=setMetaVal;    
            activeObject.set("text_verification_value",setMetaVal);
        }
       
    }else{
        document.getElementById("verification_text_value").value='';    
        activeObject.set("text_verification_value",''); 
    }
    // $.getJSON(inst.json_File, function(data){ 
    //     var data=data;
      

    // })

    console.log(pdf.rectObjs);
}


PDFAnnotate.prototype.changeQrPlacerMetaLabel = function (PlacerLabel) { 
    var inst = this;
    var fabricObj = inst.fabricObjects[inst.active_canvas];
    var activeObject = inst.fabricObjects[inst.active_canvas].getActiveObject();
    activeObject.set("text_verification_lable",PlacerLabel); 
    // console.log("Lable:",PlacerLabel);
}

PDFAnnotate.prototype.changeQrPlacerMetaValue = function (PlacerValue) { 
    var inst = this;
    var fabricObj = inst.fabricObjects[inst.active_canvas];
    var activeObject = inst.fabricObjects[inst.active_canvas].getActiveObject();
    activeObject.set("text_verification_value",PlacerValue);
    // console.log("Lable:",PlacerValue);
    // console.log(activeObject,PlacerValue); 
}


function ShowHideAttributes(placer_type){
    var staticPlaceholder="Write static text here.";
    var sourcePlaceholder="Click 'Source' button to add multiple sources.";
    if(placer_type == ''){                             
        $("#qr_position").hide();  
        $("#source-link").hide();     
        $("#qr_details").hide();                            
        $("#placer_font").hide();
        $("#placer_font_underline").hide();
        $("#text_underline").hide();
        $("#font-div").hide();
        $('#placer_font_size').hide();
        $("#placer_display").hide();
        $("#ghost_words").hide();
        $("#ghost_chars").hide();                            
        $("#color_selector").hide();    
        $("#ghost_degree_angle").hide(); 
        $("#degree_angle").hide();  
        $("#opacity_val").hide();     
        $('#placer_type').attr("disabled", false);   
        $("#select_image").hide();
        $("#addData").hide(); 
        $("#line_height").hide();
		$("#qr_show").hide();
		$("#qr_show_div").hide();
		$("#blockchain_show").hide();
		$("#blockchain_show_div").hide();
		$("#barcode_show_div").hide();
    }
    if(placer_type == 'QR Default'){ 
        $("#qr_position").show(); // Position drop-down
        $("#source-link").hide(); // Source Link
        $("#qr_details").hide(); // Textarea                            
        $("#placer_font").hide(); //Font drop-down
        $("#placer_font_underline").hide(); //tick checkbox
        $("#text_underline").hide(); //U sign text
        $("#font-div").hide(); // Font Size text
        $('#placer_font_size').hide(); // Font size input
        $("#placer_display").hide(); // Align drop-down
        $("#ghost_words").hide(); // Ghost Characters input
        $("#ghost_chars").hide(); // Characters text                            
        $("#color_selector").hide(); // Colour drop-down
        $("#ghost_degree_angle").hide(); 
        $("#degree_angle").hide(); // Degree angle input
        $("#opacity_val").hide(); // Opacity drop-down   
        $('#placer_type').attr("disabled", false);  
        $("#select_image").hide();
        $("#addData").hide(); 
        $("#line_height").hide();
		$("#qr_show").show();
		$("#qr_show_div").show();
		$("#blockchain_show").hide();
		$("#blockchain_show_div").hide();
		$("#barcode_show_div").hide();
    }
    if(placer_type == 'QR Dynamic'){ 
        $("#qr_position").show();   
        $("#source-link").show();
        $("#qr_details").show();                            
        $("#placer_font").hide();
        $("#placer_font_underline").hide();
        $("#text_underline").hide();
        $("#font-div").hide();
        $('#placer_font_size').hide();
        $("#placer_display").hide();
        $("#ghost_words").hide();
        $("#ghost_chars").hide();                            
        $("#color_selector").hide();    
        $("#ghost_degree_angle").hide(); 
        $("#degree_angle").hide();  
        $("#opacity_val").hide();     
        $('#placer_type').attr("disabled", false);   
        $("#select_image").hide();
        $("#addData").hide();    
        $("#qr_details").attr("placeholder", sourcePlaceholder);
        $("#line_height").hide();
		$("#qr_show").show();
		$("#qr_show_div").show();
		$("#blockchain_show").hide();
		$("#blockchain_show_div").hide();
        $("#barcode_show_div").hide();
    }
    if(placer_type == 'QR Invisible Plain Text'){ 
        $("#qr_position").hide();   
        $("#source-link").show();
        $("#qr_details").show();                            
        $("#placer_font").show();
        $("#placer_font_underline").hide();
        $("#text_underline").hide();
        $("#font-div").show();
        $('#placer_font_size').show();
        $("#placer_display").show();
        $("#ghost_words").hide();
        $("#ghost_chars").hide();                            
        $("#color_selector").hide();    
        $("#ghost_degree_angle").hide(); 
        $("#degree_angle").hide();  
        $("#opacity_val").hide();     
        $('#placer_font_size').attr("readonly", false);
        $('#placer_type').attr("disabled", false);
        $("#select_image").hide();
        $("#addData").hide();
        $("#qr_details").attr("placeholder", sourcePlaceholder);
        $("#line_height").hide();
		$("#qr_show").hide();
		$("#qr_show_div").hide();
		$("#blockchain_show").hide();
		$("#blockchain_show_div").hide();
        $("#barcode_show_div").hide();
    }
    if(placer_type == 'QR Plain Text'){ 
        $("#qr_position").hide();   
        $("#source-link").show();
        $("#qr_details").show();                            
        $("#placer_font").show();
        $("#placer_font_underline").hide();
        $("#text_underline").hide();
        $("#font-div").show();
        $('#placer_font_size').show();
        $("#placer_display").show();
        $("#ghost_words").hide();
        $("#ghost_chars").hide();                            
        $("#color_selector").hide();    
        $("#ghost_degree_angle").hide(); 
        $("#degree_angle").hide();  
        $("#opacity_val").hide(); 
        $('#placer_font_size').attr("readonly", false);
        $('#placer_type').attr("disabled", false); 
        $("#select_image").hide();
        $("#addData").hide();
        $("#qr_details").attr("placeholder", sourcePlaceholder);
        $("#line_height").hide();
		$("#qr_show").hide();
		$("#qr_show_div").hide();
		$("#blockchain_show").hide();
		$("#blockchain_show_div").hide();
		$("#barcode_show_div").hide();
    }
    if(placer_type == 'Barcode'){ 
        $("#qr_position").hide();   
        // $("#source-link").hide();
        // $("#qr_details").hide();                            
        $("#placer_font").hide();
        $("#placer_font_underline").hide();
        $("#text_underline").hide();
        $("#font-div").show();
        $('#placer_font_size').show();
        $("#placer_display").hide();
        $("#ghost_words").hide();
        $("#ghost_chars").hide();                            
        $("#color_selector").hide();    
        $("#ghost_degree_angle").hide(); 
        $("#degree_angle").hide();  
        $("#opacity_val").hide(); 
        $('#placer_type').attr("disabled", false);
        $("#select_image").hide();
        $("#addData").hide();
        $("#line_height").hide();
		$("#qr_show").hide();
		$("#qr_show_div").hide();
		$("#blockchain_show").hide();
		$("#blockchain_show_div").hide();
		$("#barcode_show_div").show();
        
        var hostname = window.location.hostname;
        var parts = hostname.split('.');
        var subdomain = parts.length > 2 ? parts[0] : '';
        
        if (subdomain == 'imcc') {
            $("#qr_details").show(); 
            $("#qr_details").attr("placeholder", sourcePlaceholder);
            $("#source-link").show();
        } else {
            $("#source-link").hide();
            $("#qr_details").hide();  
        }

        // $("#source-link").hide();
        // $("#qr_details").hide();  


    }	
    if(placer_type == 'Micro Line'){ 
        $("#qr_position").hide();   
        $("#source-link").show();
        $("#qr_details").show();                            
        $("#placer_font").show();
        $("#placer_font_underline").show();
        $("#text_underline").hide();
        $("#font-div").show();
        $('#placer_font_size').show();
        $("#placer_display").show();
        $("#ghost_words").hide();
        $("#ghost_chars").hide();                            
        $("#color_selector").show();    
        $("#ghost_degree_angle").hide(); 
        $("#degree_angle").hide();  
        $("#opacity_val").hide();
        $('#placer_font_size').attr("readonly", false);
        $('#placer_type').attr("disabled", false);
        $("#select_image").hide();
        $("#addData").hide();
        $("#qr_details").attr("placeholder", sourcePlaceholder);
        $("#line_height").hide();
		$("#qr_show").hide();
		$("#qr_show_div").hide();
		$("#blockchain_show").hide();
		$("#blockchain_show_div").hide();
		$("#barcode_show_div").hide();
    }
    if(placer_type == 'Invisible'){ 
        $("#qr_position").hide();   
        $("#source-link").show();
        $("#qr_details").show();                            
        $("#placer_font").show();
        $("#placer_font_underline").hide();
        $("#text_underline").hide();
        $("#font-div").show();
        $('#placer_font_size').show();
        $("#placer_display").show();
        $("#ghost_words").hide();
        $("#ghost_chars").hide();                            
        $("#color_selector").hide();    
        $("#ghost_degree_angle").hide(); 
        $("#degree_angle").hide();  
        $("#opacity_val").hide(); 
        $('#placer_font_size').attr("readonly", false);
        $('#placer_type').attr("disabled", false);
        $("#select_image").hide();
        $("#addData").hide();
        $("#qr_details").attr("placeholder", sourcePlaceholder);
        $("#line_height").hide();
		$("#qr_show").hide();
		$("#qr_show_div").hide();
		$("#blockchain_show").hide();
		$("#blockchain_show_div").hide();
		$("#barcode_show_div").hide();
    }
    if(placer_type == 'Invisible Image'){ 
        $("#qr_position").hide();   
        $("#source-link").hide();
        $("#qr_details").hide();                            
        $("#placer_font").hide();
        $("#placer_font_underline").hide();
        $("#text_underline").hide();
        $("#font-div").hide();
        $('#placer_font_size').hide();
        $("#placer_display").show();
        $("#ghost_words").hide();
        $("#ghost_chars").hide();                            
        $("#color_selector").hide();    
        $("#ghost_degree_angle").hide(); 
        $("#degree_angle").hide();  
        $("#opacity_val").hide(); 
        $('#placer_font_size').attr("readonly", false);
        $('#placer_type').attr("disabled", false);
        $("#select_image").hide();
        $("#addData").hide();
        $("#line_height").hide();
		$("#qr_show").hide();
		$("#qr_show_div").hide();
		$("#blockchain_show").hide();
		$("#blockchain_show_div").hide();
		$("#barcode_show_div").hide();
    }
    if(placer_type == 'Plain Text'){ 
        $("#qr_position").hide();   
        $("#source-link").show();
        $("#qr_details").show();                            
        $("#placer_font").show();
        $("#placer_font_underline").hide();
        $("#text_underline").hide();
        $("#font-div").show();
        $('#placer_font_size').show();
        $("#placer_display").show();
        $("#ghost_words").hide();
        $("#ghost_chars").hide();                            
        $("#color_selector").show();    
        $("#ghost_degree_angle").hide(); 
        $("#degree_angle").hide();  
        $("#opacity_val").hide(); 
        $('#placer_font_size').attr("readonly", false);
        $('#placer_type').attr("disabled", false); 
        $("#select_image").hide();
        $("#addData").hide(); 
        $("#qr_details").attr("placeholder", sourcePlaceholder);
        $("#line_height").hide();
		$("#qr_show").hide();
		$("#qr_show_div").hide();
		$("#blockchain_show").hide();
		$("#blockchain_show_div").hide();
		$("#barcode_show_div").hide();
    }
    if(placer_type == 'Static Text'){ 
        $("#qr_position").hide();   
        $("#source-link").hide();
        $("#qr_details").show();                            
        $("#placer_font").show();
        $("#placer_font_underline").show();
        $("#text_underline").show();
        $("#font-div").show();
        $('#placer_font_size').show();
        $("#placer_display").show();
        $("#ghost_words").hide();
        $("#ghost_chars").hide();                            
        $("#color_selector").show();    
        $("#ghost_degree_angle").hide(); 
        $("#degree_angle").hide();  
        $("#opacity_val").show(); 
        $('#placer_font_size').attr("readonly", false);
        $('#placer_type').attr("disabled", false); 
        $("#select_image").hide();
        $("#addData").hide();
        $("#qr_details").attr("placeholder", staticPlaceholder);
        $("#line_height").hide();
		$("#qr_show").hide();
		$("#qr_show_div").hide();
		$("#blockchain_show").hide();
		$("#blockchain_show_div").hide();
		$("#barcode_show_div").hide();
    }
    if(placer_type == 'Common Static Text'){ 
        $("#qr_position").hide();   
        $("#source-link").hide();
        $("#qr_details").show();                            
        $("#placer_font").show();
        $("#placer_font_underline").show();
        $("#text_underline").show();
        $("#font-div").show();
        $('#placer_font_size').show();
        $("#placer_display").show();
        $("#ghost_words").hide();
        $("#ghost_chars").hide();                            
        $("#color_selector").show();    
        $("#ghost_degree_angle").hide(); 
        $("#degree_angle").hide();  
        $("#opacity_val").show(); 
        $('#placer_font_size').attr("readonly", false);
        $('#placer_type').attr("disabled", false); 
        $("#select_image").hide();
        $("#addData").hide();
        $("#qr_details").attr("placeholder", staticPlaceholder);
        $("#line_height").hide();
		$("#qr_show").hide();
		$("#qr_show_div").hide();
		$("#blockchain_show").hide();
		$("#blockchain_show_div").hide();
		$("#barcode_show_div").hide();
    }
    if(placer_type == 'Image'){ 
        $("#qr_position").hide();   
        $("#source-link").hide();
        $("#qr_details").hide();                            
        $("#placer_font").hide();
        $("#placer_font_underline").hide();
        $("#text_underline").hide();
        $("#font-div").hide();
        $('#placer_font_size').hide();
        $("#placer_display").hide();
        $("#ghost_words").hide();
        $("#ghost_chars").hide();                            
        $("#color_selector").hide();    
        $("#ghost_degree_angle").hide(); 
        $("#degree_angle").hide();  
        $("#opacity_val").hide(); 
        $('#placer_type').attr("disabled", false);
        $("#select_image").show();
        $("#addData").show();
        $("#line_height").hide();
		$("#qr_show").hide();
		$("#qr_show_div").hide();
		$("#blockchain_show").hide();
		$("#blockchain_show_div").hide();
		$("#barcode_show_div").hide();
    }
    if(placer_type == 'UV Image'){ 
        $("#qr_position").hide();   
        $("#source-link").hide();
        $("#qr_details").hide();                            
        $("#placer_font").hide();
        $("#placer_font_underline").hide();
        $("#text_underline").hide();
        $("#font-div").hide();
        $('#placer_font_size').hide();
        $("#placer_display").hide();
        $("#ghost_words").hide();
        $("#ghost_chars").hide();                            
        $("#color_selector").hide();    
        $("#ghost_degree_angle").hide(); 
        $("#degree_angle").hide();  
        $("#opacity_val").hide(); 
        $('#placer_type').attr("disabled", false);
        $("#select_image").show();
        $("#addData").show();
        $("#line_height").hide();
		$("#qr_show").hide();
		$("#qr_show_div").hide();
		$("#blockchain_show").hide();
		$("#blockchain_show_div").hide();
		$("#barcode_show_div").hide();
    }
    if(placer_type == 'Watermark Text'){ 
        $("#qr_position").hide();   
        $("#source-link").show();
        $("#qr_details").show();                            
        $("#placer_font").show();
        $("#placer_font_underline").hide();
        $("#text_underline").hide();
        $("#font-div").show();
        $('#placer_font_size').show();
        $("#placer_display").show();
        $("#ghost_words").hide();
        $("#ghost_chars").hide();                            
        $("#color_selector").show();    
        $("#ghost_degree_angle").hide(); 
        $("#degree_angle").show();  
        $("#opacity_val").show(); 
        $('#placer_font_size').attr("readonly", false);
        $('#placer_type').attr("disabled", false); 
        $("#select_image").hide();
        $("#addData").hide();
        $("#qr_details").attr("placeholder", sourcePlaceholder);
        $("#line_height").hide();
		$("#qr_show").hide();
		$("#qr_show_div").hide();
		$("#blockchain_show").hide();
		$("#blockchain_show_div").hide();
		$("#barcode_show_div").hide();
    }
    if(placer_type == 'Watermark Multi Lines'){ 
        $("#qr_position").show();   
        $("#source-link").show();
        $("#qr_details").show();                            
        $("#placer_font").show();
        $("#placer_font_underline").hide();
        $("#text_underline").hide();
        $("#font-div").show();
        $('#placer_font_size').show();
        $("#placer_display").show();
        $("#ghost_words").hide();
        $("#ghost_chars").hide();                            
        $("#color_selector").show();  
        $("#ghost_degree_angle").hide();     
        $("#degree_angle").show();  
        $("#opacity_val").show(); 
        $('#placer_font_size').attr("readonly", false);
        $('#placer_type').attr("disabled", false); 
        $("#select_image").hide();
        $("#addData").hide();
        $("#qr_details").attr("placeholder", sourcePlaceholder);    
        $("#line_height").show();
		$("#qr_show").hide();
		$("#qr_show_div").hide();
		$("#blockchain_show").hide();
		$("#blockchain_show_div").hide();
		$("#barcode_show_div").hide();
    }
    if(placer_type == 'Ghost Image'){ 
        $("#qr_position").hide();   
        $("#source-link").hide();
        $("#qr_details").hide();                            
        $("#placer_font").hide();
        $("#placer_font_underline").hide();
        $("#text_underline").hide();
        $("#font-div").show();
        $('#placer_font_size').show();
        $("#placer_display").hide();
        $("#ghost_words").show();
        $("#ghost_chars").show();                            
        $("#color_selector").hide();    
        $("#ghost_degree_angle").show();  
        $("#degree_angle").hide();  
        $("#opacity_val").hide(); 
        $('#placer_font_size').attr("readonly", true);
        $('#placer_type').attr("disabled", true);
        $("#select_image").hide();
        $("#addData").hide();
        $("#line_height").hide();
		$("#qr_show").hide();
		$("#qr_show_div").hide();
		$("#blockchain_show").hide();
		$("#blockchain_show_div").hide();
		$("#barcode_show_div").hide();
    }     
}