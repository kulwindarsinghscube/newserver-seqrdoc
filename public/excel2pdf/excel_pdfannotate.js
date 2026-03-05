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
        var scale = 1; //1
        inst.number_of_pages = pdf.pdfInfo.numPages;        

        for (var i = 1; i <= pdf.pdfInfo.numPages; i++) {
            document.getElementById("msg").innerHTML = "<img src='../excel2pdf/loading.gif' width='30' height='30'  />";
            pdf.getPage(i).then(function (page) {
                var viewport = page.getViewport(scale);  
                var outputScale = window.devicePixelRatio || 1;    
                var canvas = document.createElement('canvas');
                context = canvas.getContext('2d');
                document.getElementById(inst.container_id).appendChild(canvas);
                canvas.className = 'pdf-canvas';
                /*canvas.height = viewport.height;
                canvas.width = viewport.width;    
                var renderContext = {
                    canvasContext: context,
                    viewport: viewport
                };*/
                canvas.width = Math.floor(viewport.width * outputScale);
                canvas.height = Math.floor(viewport.height * outputScale);
                canvas.style.width = Math.floor(viewport.width) + "px";
                canvas.style.height =  Math.floor(viewport.height) + "px";

                var transform = outputScale !== 1
                  ? [outputScale, 0, 0, outputScale, 0, 0]
                  : null;
                var renderContext = {
                  canvasContext: context,
                  transform: transform,
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
                },
                //enableRetinaScaling: false
            });

fabricObj.on("object:selected", function (e) {
  if (e.target) {
    e.target.bringToFront();
    this.renderAll();
  }
});
var _prevActive = 0;
var _layer = 0;
fabric.util.addListener(fabricObj.upperCanvasEl, "dblclick", function (e) {
    var _canvas = fabricObj;
    //current mouse position
    var _mouse = _canvas.getPointer(e);
    //active object (that has been selected on click)
    var _active = _canvas.getActiveObject();
    //possible dblclick targets (objects that share mousepointer)
    var _targets = _canvas.getObjects().filter(function (_obj) {
        return _obj.containsPoint(_mouse) && !_canvas.isTargetTransparent(_obj, _mouse.x, _mouse.y);
    });
    
    _canvas.deactivateAll();
      
    //new top layer target
    if (_prevActive !== _active) {
        //try to go one layer below current target
        _layer = Math.max(_targets.length-2, 0);
    }
    //top layer target is same as before
    else {
        //try to go one more layer down
        _layer = --_layer < 0 ? Math.max(_targets.length-2, 0) : _layer;
    }

    //get obj on current layer
    var _obj = _targets[_layer];

    if (_obj) {
        _prevActive = _obj;
        _obj.bringToFront();
        _canvas.setActiveObject(_obj).renderAll();
    }
});
            
            fabricObj.on('object:scaling', function(){
                console.log('scaling');
                var obj = fabricObj.getActiveObject(),
                width = obj.width,
                height = obj.height,
                scaleX = obj.scaleX,
                scaleY = obj.scaleY;   

                // console.log(width); 
                // console.log(typeof width);          
                // console.log(scaleX);

                var widthStr = String(width);
                var heightStr = String(height);
                var scaleXStr = String(scaleX);
                var scaleYStr = String(scaleY);

                if (widthStr.indexOf('.') >= 0) {
                    // Split the number into two parts, before and after the decimal point
                    var parts = widthStr.split('.');
                    if (parts[1].length > 2) {
                        // Limit to two decimal places
                        parts[1] = parts[1].substring(0, 2);
                    }
                    // Join the parts back together
                    width = parts[0] + '.' + parts[1];
                }

                if (scaleXStr.indexOf('.') >= 0) {
                    // Split the number into two parts, before and after the decimal point
                    var partsV1 = scaleXStr.split('.');
                    if (partsV1[1].length > 2) {
                        // Limit to two decimal places
                        partsV1[1] = partsV1[1].substring(0, 2);
                    }
                    // Join the partsV1 back together
                    scaleX = partsV1[0] + '.' + partsV1[1];
                }


                if (heightStr.indexOf('.') >= 0) {
                    // Split the number into two parts, before and after the decimal point
                    var partsV2 = heightStr.split('.');
                    if (partsV2[1].length > 2) {
                        // Limit to two decimal places
                        partsV2[1] = partsV2[1].substring(0, 2);
                    }
                    // Join the partsV2 back together
                    height = partsV2[0] + '.' + partsV2[1];
                }

                if (scaleYStr.indexOf('.') >= 0) {
                    // Split the number into two parts, before and after the decimal point
                    var partsV3 = scaleYStr.split('.');
                    if (partsV3[1].length > 2) {
                        // Limit to two decimal places
                        partsV3[1] = partsV3[1].substring(0, 2);
                    }
                    // Join the partsV3 back together
                    scaleY = partsV3[0] + '.' + partsV3[1];
                }

                
                // console.log(width); 
                // console.log(typeof width);          
                // console.log(scaleX);


                // console.log(parseFloat(width) * parseFloat(scaleX));           
                obj.set({
                    width : parseFloat(width * scaleX),
                    height : parseFloat(height * scaleY),
                    scaleX: 1,
                    scaleY: 1
                });
            });    

            fabricObj.on('mouse:down', function(options) {
                // alert('test');
                //fabricObj.perPixelTargetFind = true;
                if(fabricObj.getActiveObject() != null)   
                {                    
                    if(fabricObj.getActiveObject().placer_type == "Ghost Image"){
                        fabricObj.getActiveObject().hasControls = false;
                        PlacerAngle=fabricObj.getActiveObject().angle;
                        fabricObj.getActiveObject().angle = parseInt(PlacerAngle);
                    }
                    if(fabricObj.getActiveObject().type == "line"){
                        val_name=fabricObj.getActiveObject().name;
                        document.getElementById('RectBox').value = val_name;  
                        val_id=fabricObj.getActiveObject().id;
                        document.getElementById('RectBoxId').value = val_id;
                        val_nature=fabricObj.getActiveObject().nature;
                        document.getElementById('nature').value = val_nature;
                        new_coord = fabricObj.getActiveObject().calcCoords();
                        document.getElementById('coord-list').value=new_coord.tl.x + ',' + new_coord.tl.y + ',' + new_coord.br.x + ',' + new_coord.br.y;
                        val_width=fabricObj.getActiveObject().width;
                        document.getElementById('rect-width').value = val_width;
                        val_height=fabricObj.getActiveObject().height;
                        document.getElementById('rect-height').value = val_height;
                        document.getElementById('degree_angle').value = fabricObj.getActiveObject().degree_angle; 
                        document.getElementById('color_selector').value = fabricObj.getActiveObject().font_color; 
                        document.getElementById('opacity_val').value = fabricObj.getActiveObject().opacity_val; 
                        document.getElementById('image_path').value = fabricObj.getActiveObject().image_path; 
                        $('#placer_font_size').attr("readonly", false);                         
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
                        val_width=fabricObj.getActiveObject().width;
                        document.getElementById('rect-width').value = val_width;
                        val_height=fabricObj.getActiveObject().height;
                        document.getElementById('rect-height').value = val_height;
                        document.getElementById('degree_angle').value = fabricObj.getActiveObject().degree_angle; 
                        document.getElementById('color_selector').value = fabricObj.getActiveObject().font_color; 
                        document.getElementById('opacity_val').value = fabricObj.getActiveObject().opacity_val; 
                        document.getElementById('image_path').value = fabricObj.getActiveObject().image_path; 
                        $('#placer_font_size').attr("readonly", false);                         
                    }
                    if(fabricObj.getActiveObject().nature == "placer"){                        
                        $("#placer_elements").show();
                        var template_name = $("#template-name").val();
                        
                        var urlProtocolV1 =  location.protocol; 
                        var baseUrlV1 = location.host;

                        var subdomain = baseUrlV1.split('.')[0];
                        // alert(subdomain);

                        src=urlProtocolV1+'//'+baseUrlV1+'/'+subdomain+'/excel2pdf/processed_pdfs/excel/'+template_name+'.txt';
                        var sourceul = document.getElementById("sortable");    
                        sourceul.innerHTML='';
                        // console.log(src);
                        //$("#sortable li").remove();
                        var select_source = document.getElementById("source_selector");
                        $("#source_selector option").remove();
                        var sources = [];
                        sources.push('Select a Source');
                        sources.push('Current DateTime');                        
                        readTextFile(src,function(allText){
                            if(allText){

                                const obj = JSON.parse(allText);   
                                ColnLen = Object.keys(obj).length;
                                // console.log(ColnLen);
                                for(var k in obj) {
                                    sources.push(obj[k]);
                                    // console.log(obj[k]);
                                    // $("#source_selector").append("<option value = '"+obj[k]+"'>"+obj[k]+"</option>")
                                }                                
                            }    
                        });
                        
                        /*
                        var sources = [];
                        sources.push('Select a Source');
                        sources.push('Current DateTime');
                        fabricObj.forEachObject(function(obj){
                            if(obj.nature=="extractor"){
                                sources.push(obj.name);
                                //$("#source_selector").append("<option value = '"+obj.name+"'>"+obj.name+"</option>")
                            }
                        });                        
                        */
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


                        var QrPlace = document.getElementById("qr_show");
                        if(fabricObj.getActiveObject().get("qrPlace")=='show'){ 
                            QrPlace.checked = true;                             
                        }else{ 
                            QrPlace.checked = false;                             
                        }


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

                        var sampleText = document.getElementById("sample_text");
                        sampleText.value = fabricObj.getActiveObject().get("sample_text");

                        var ghost_words = document.getElementById("ghost_words");
                        ghost_words.value = fabricObj.getActiveObject().get("ghost_words");
                        var select_PlacerDisplay = document.getElementById("placer_display");
                        select_PlacerDisplay.value = fabricObj.getActiveObject().get("placer_display"); 
                        var select_QrPosition = document.getElementById("qr_position");
                        // select_QrPosition.value = fabricObj.getActiveObject().get("qr_position");
                        var line_height = document.getElementById("line_height");
                        line_height.value = fabricObj.getActiveObject().get("lineHeight");
                            
                        ShowHideAttributes(placer_type); 
                        PlacerAngle=fabricObj.getActiveObject().angle;
                        fabricObj.getActiveObject().angle = parseInt(PlacerAngle);
                    }else{
                        document.getElementById('clone_ep').value = '';
                        document.getElementById('placer_type').value = '';
                        //document.getElementById('placer_display').value = '';
                        $("#placer_elements").hide();
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
                        objType=data[p].objects[i].type
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
                            pdf.drawRectangle(objType, id, name, nature, left_margin, top_margin, width, height, p, page_no);                          
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
                            sample_text=data[p].objects[i].sample_text;                 
                            qr_position=data[p].objects[i].qr_position;                 
                            degree_angle=data[p].objects[i].degree_angle;                 
                            font_color=data[p].objects[i].font_color;                 
                            opacity_val=data[p].objects[i].opacity_val;                 
                            image_path=data[p].objects[i].image_path;
                            lineHeight=data[p].objects[i].lineHeight;     
                            
                            qrPlace=data[p].objects[i].qrPlace;
                            
                            barcodeContent=data[p].objects[i].barcodeContent;
                            barcodeContentPosition=data[p].objects[i].barcodeContentPosition;

                            pdf.setColor('rgba(0, 181, 204, 0.3)');
                            pdf.setBorderColor('blue');
                            pdf.drawPlacerRectangle(id, name, nature, left_margin, top_margin, width, height, source, fontName, fontBold, fontItalic, fontUnderline, fontSize, placer_type, placer_display, qr_details,sample_text, p, page_no, print_words, qr_position, degree_angle, font_color, opacity_val, image_path,lineHeight,qrPlace, barcodeContent, barcodeContentPosition);
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

function readTextFile(file,callback)
{
    var rawFile = new XMLHttpRequest();
    rawFile.open("GET", file, false);
    rawFile.onreadystatechange = function ()
    {
        if(rawFile.readyState === 4)
        {
            if(rawFile.status === 200 || rawFile.status == 0)
            {
                var allText = rawFile.responseText;
                if(callback) callback(allText);
            }
        }
    }
    rawFile.send(null);
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

PDFAnnotate.prototype.enableRectangle = function (id_val='',objectType) {
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
        strokeWidth: 1, //0.1
    });
    rect.toObject = (function(toObject) {
        return function() {
            return fabric.util.object.extend(toObject.call(this), {
            id: this.id,
            page_no: this.page_no,
            name: this.name,
            nature: this.nature
        });
      };
    })(rect.toObject);    
    inst.rectObjs.push(rect);
    fabricObj.add(rect);
    rect.id = idv;
    rect.page_no = inst.active_canvas;
    rect.name = 'extract_rect_'+idv; 
    rect.nature = 'extractor';
    */
    //[20, 1, 20, 160] v  [13, 100, 160, 100] h  start x-coordinate, start y-coordinate, end x-coordinate and end y-coordinate
    if(objectType=="Verticle"){
        var line = new fabric.Line([ 20, 1, 20, 160 ], { 
            id:idv,
            stroke: '#006400',
            hasRotatingPoint: false,
            strokeWidth: 1
        });
    }else{
        var line = new fabric.Line([ 13, 100, 160, 100 ], { 
            id:idv,
            stroke: '#006400',
            hasRotatingPoint: false,
            strokeWidth: 1
        });    
    }
    line.toObject = (function(toObject) {
        return function() {
            return fabric.util.object.extend(toObject.call(this), {
            id: this.id,
            page_no: this.page_no,
            name: this.name,
            nature: this.nature
        });
      };
    })(line.toObject);    
    inst.rectObjs.push(line);
    fabricObj.add(line);
    line.id = idv;
    line.page_no = inst.active_canvas;
    line.name = 'extract_rect_'+idv; 
    line.nature = 'extractor';
    
}

PDFAnnotate.prototype.drawRectangle = function (objType,id,name,nature,left_margin,top_margin,width_val,height_val,canvas_no,page_no) {
    var inst = this;
    var fabricObj = inst.fabricObjects[canvas_no];
    inst.active_tool = 4;
    if (inst.fabricObjects.length > 0) {
        $.each(inst.fabricObjects, function (index, fabricObj) {
            fabricObj.isDrawingMode = false;
        });
    }
    if(objType=="rect"){
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
                nature: this.nature
            });
          };
        })(rect.toObject);    
        inst.rectObjs.push(rect);
        fabricObj.add(rect);
        rect.id = id;
        rect.page_no = page_no;
        rect.name = name; 
        rect.nature = nature;
        fabricObj.setActiveObject(rect);
        fabricObj.renderAll();
    }else{
        if(width_val > height_val){
            //Horizontal Line
            var rect = new fabric.Line( [left_margin,top_margin,width_val+left_margin,top_margin],{
                id:id, 
                stroke: "#006400",
                hasRotatingPoint: false,
                strokeWidth: 1          
            });
        }else{
            //Verticle Line
            var rect = new fabric.Line( [left_margin, top_margin, left_margin, height_val+top_margin],{
                id:id, 
                stroke: "#006400",
                hasRotatingPoint: false,
                strokeWidth: 1        
            });
        }
        rect.toObject = (function(toObject) {
            return function() {
                return fabric.util.object.extend(toObject.call(this), {
                id: this.id,
                page_no: this.page_no,
                name: this.name,
                nature: this.nature
            });
          };
        })(rect.toObject);    
        inst.rectObjs.push(rect);
        fabricObj.add(rect);
        rect.id = id;
        rect.page_no = page_no;
        rect.name = name; 
        rect.nature = nature;
        fabricObj.setActiveObject(rect);
        fabricObj.renderAll();        
    }
        
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
            sample_text: this.sample_text,
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
    rect.sample_text = "";
    rect.qr_position = "";
    rect.degree_angle = 0;
    rect.font_color = "";
    rect.opacity_val = "";
    rect.image_path = "";  
    rect.lineHeight = ""; 
    rect.qrPlace = "";
    rect.barcodeContent = "";
    rect.barcodeContentPosition = "";
}

PDFAnnotate.prototype.drawPlacerRectangle = function (id,name,nature,left_margin,top_margin,width_val,height_val,source, fontName, fontBold, fontItalic, fontUnderline, fontSize,placer_type,placer_display,qr_details,sample_text,canvas_no,page_no,print_words='', qr_position='', degree_angle='', font_color='', opacity_val='', image_path='',lineHeight="", qrPlace='', barcodeContent='', barcodeContentPosition='') { 
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
            sample_text: this.sample_text,
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
    rect.sample_text = sample_text;
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
            sample_text: this.sample_text,
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
    rect.fontSize = fontsize;
    rect.placer_type = "Ghost Image";
    rect.ghost_words = print_words;
    rect.placer_display = "";
    rect.qr_details = "";
    rect.sample_text = "Ghost Image";
    rect.qr_position = "";
    rect.degree_angle = 0;
    rect.font_color = "";
    rect.opacity_val = "";    
    rect.image_path = "";  
    rect.lineHeight = "";   
    rect.qrPlace = "";  
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

PDFAnnotate.prototype.changePlacerTypeV1 = function (placer_type) { 

    var fabricObj = this.fabricObjects[this.active_canvas];
    // Ensure the Fabric.js canvas exists
    if (!fabricObj) {
        console.error("Fabric object not initialized.");
        return;
    }

    var activeObject = fabricObj.getActiveObject();

    // If no active object, try selecting the last added object
    if (!activeObject && fabricObj.getObjects().length > 0) {
        activeObject = fabricObj.getObjects()[fabricObj.getObjects().length - 1];
        fabricObj.setActiveObject(activeObject);
        fabricObj.renderAll(); // Ensure UI updates
    }

    // If still no object, log an error and return
    if (!activeObject) {
        console.error("No active object found. Unable to set placer_type.");
        return;
    }

    activeObject.set("placer_type", placer_type);
    
    x = 30;
    y = 30;
    // Set custom X and Y if provided
    if (x !== null) activeObject.set("left", x);
    if (y !== null) activeObject.set("top", y);

   

    activeObject.set("fill", 'rgba(0, 181, 204, 0.3)');
    activeObject.set("cornerSize", 6);
    // Update canvas
    fabricObj.renderAll();
    ShowHideAttributes(placer_type);
};

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


PDFAnnotate.prototype.changeSampleText = function (sampleText) {
    var inst = this;
    var fabricObj = inst.fabricObjects[inst.active_canvas];
    var activeObject = inst.fabricObjects[inst.active_canvas].getActiveObject();
    activeObject.set("sample_text",sampleText); 
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
            stroke_width=1;
            strok_color=inst.borderColor; //"black"
        }
        else{ 
            clone_nature = 'placer';
            clone_name = 'place_rect_'; 
            corner_color='blue';    
            bg_color='rgba(0, 181, 204, 0.3)';
            flag='placer';
            stroke_width=0.1;
            strok_color=inst.borderColor;
        }
    }
    else if(activeObject.nature == 'placer'){
        if(clone_ep == '' || clone_ep == 'placer'){
            clone_nature = 'placer'; 
            clone_name = 'place_rect_'; 
            corner_color='blue';
            bg_color='rgba(0, 181, 204, 0.3)';
            flag='placer';
            stroke_width=0.1;
            strok_color=inst.borderColor;
        }
        else{
            clone_nature = 'extractor'; 
            clone_name = 'extract_rect_'; 
            corner_color='#e75480'; 
            bg_color='rgba(255, 0, 0, 0.3)';
            flag='extractor';
            stroke_width=1;
            strok_color=inst.borderColor; //"black"
        }
    } 
    if(id_val==''){
        idv=ids++;
    }else{
        idv=id_val;    
    }    
    activeObject.clone(function(clone) {
        fabricObj.discardActiveObject();
        if(activeObject.type == 'line'){
            fabricObj.add(clone.set({
                id:idv,
                left: activeObject.left, 
                top: activeObject.top,
                evented: true,            
                stroke: strok_color,            
                hasRotatingPoint: false,
                //fill: bg_color,
                //cornerColor: corner_color,
                //cornerSize: 6,
                //transparentCorners: false,
                strokeWidth: stroke_width,     
                //active: true,
                //strokeSize: inst.borderSize,
            }));
        }else{
            fabricObj.add(clone.set({
                id:idv,
                left: activeObject.left, 
                top: activeObject.top,
                evented: true,            
                stroke: strok_color,            
                hasRotatingPoint: false,
                fill: bg_color,
                cornerColor: corner_color,
                cornerSize: 6,
                transparentCorners: false,
                strokeWidth: stroke_width,     
                //active: true,
                //strokeSize: inst.borderSize,
            }));
        }
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
                    sample_text: this.sample_text,                    
                    qr_position: this.qr_position,    
                    degree_angle: this.degree_angle,
                    font_color: this.font_color,
                    opacity_val: this.opacity_val,                    
                    image_path: this.image_path,
                    lineHeight: this.lineHeight            
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
        clone.sample_text = "";        
        clone.qr_position = "";     
        clone.degree_angle = 0;
        clone.font_color = "";
        clone.opacity_val = "";        
        clone.image_path = "";
        clone.lineHeight = "";  
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

PDFAnnotate.prototype.enableZoomIn = function () {
    var inst = this;
    var page = inst.active_canvas;
    var myState = {
        currentPage: page,
        zoom: 1
    }    
    var fabricObj = inst.fabricObjects[inst.active_canvas];
    //if(myState.pdf == null) return;
    myState.zoom += 0.5;
    fabricObj.renderAll();
}

PDFAnnotate.prototype.enableZoomOut = function () {
    var inst = this;
    var page = inst.active_canvas;
    var myState = {
        currentPage: page,
        zoom: 1
    }    
    var fabricObj = inst.fabricObjects[inst.active_canvas];
    //if(myState.pdf == null) return;
    myState.zoom -= 0.5;
    fabricObj.renderAll();  
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

function ShowHideAttributes(placer_type){
    var staticPlaceholder="Write static text here.";
    var sourcePlaceholder="Click 'Source' button to add multiple sources.";
    if(placer_type == ''){                             
        $("#qr_position").hide();  
        $("#source-link").hide();     
        $("#qr_details").hide();                            
        $("#sample_text").hide();                            
        $("#placer_font").hide();
        $("#placer_font_underline").hide();
        $("#text_underline").hide();
        $("#font-div").hide();
        $('#placer_font_size').hide();
        $("#placer_display").hide();
        $("#ghost_words").hide();
        $("#ghost_chars").hide();                            
        $("#color_selector").hide();    
        $("#degree_angle").hide();  
        $("#opacity_val").hide();     
        $('#placer_type').attr("disabled", false);   
        $("#select_image").hide();
        $("#addData").hide(); 
        $("#line_height").hide();

        $("#ghost_degree_angle").hide(); 
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
        $("#sample_text").hide(); // Textarea                            
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
        $("#sample_text").show();                            
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
        $("#sample_text").show();                            
        $("#placer_font").show();
        $("#placer_font_underline").hide();
        $("#text_underline").hide();
        $("#font-div").show();
        $('#placer_font_size').show();
        $("#placer_display").show();
        $("#ghost_words").hide();
        $("#ghost_chars").hide();                            
        $("#color_selector").hide();    
        $("#degree_angle").hide();  
        $("#opacity_val").hide();     
        $('#placer_font_size').attr("readonly", false);
        $('#placer_type').attr("disabled", false);
        $("#select_image").hide();
        $("#addData").hide();
        $("#line_height").hide();
        $("#qr_details").attr("placeholder", sourcePlaceholder);
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
        $("#sample_text").show();                            
        $("#placer_font").show();
        $("#placer_font_underline").hide();
        $("#text_underline").hide();
        $("#font-div").show();
        $('#placer_font_size').show();
        $("#placer_display").show();
        $("#ghost_words").hide();
        $("#ghost_chars").hide();                            
        $("#color_selector").hide();    
        $("#degree_angle").hide();  
        $("#opacity_val").hide(); 
        $('#placer_font_size').attr("readonly", false);
        $('#placer_type').attr("disabled", false); 
        $("#select_image").hide();
        $("#addData").hide();
        $("#line_height").hide();
        $("#qr_details").attr("placeholder", sourcePlaceholder);
        $("#sample_text").attr("placeholder", 'Enter Sample Text');
        $("#qr_show").hide();
        $("#qr_show_div").hide();
        $("#blockchain_show").hide();
        $("#blockchain_show_div").hide();
        $("#barcode_show_div").hide();
    }

    if(placer_type == 'Barcode'){ 
        $("#qr_position").hide();   
        $("#source-link").hide();
        $("#qr_details").hide();                            
        $("#sample_text").hide();                            
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
    }

    if(placer_type == 'Micro Line'){ 
        $("#qr_position").hide();   
        $("#source-link").show();
        $("#qr_details").show();                            
        $("#sample_text").show();                            
        $("#placer_font").hide();
        $("#placer_font_underline").hide();
        $("#text_underline").hide();
        $("#font-div").show();
        $('#placer_font_size').show();
        $("#placer_display").show();
        $("#ghost_words").hide();
        $("#ghost_chars").hide();                            
        $("#color_selector").show();    
        $("#degree_angle").hide();  
        $("#opacity_val").hide();
        $('#placer_font_size').attr("readonly", false);
        $('#placer_type').attr("disabled", false);
        $("#select_image").hide();
        $("#addData").hide();
        $("#line_height").hide();
        $("#qr_details").attr("placeholder", sourcePlaceholder);
        $("#sample_text").attr("placeholder", 'Enter sample Text');
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
        $("#sample_text").show();                            
        $("#placer_font").show();
        $("#placer_font_underline").hide();
        $("#text_underline").hide();
        $("#font-div").show();
        $('#placer_font_size').show();
        $("#placer_display").show();
        $("#ghost_words").hide();
        $("#ghost_chars").hide();                            
        $("#color_selector").hide();    
        $("#degree_angle").hide();  
        $("#opacity_val").hide(); 
        $('#placer_font_size').attr("readonly", false);
        $('#placer_type').attr("disabled", false);
        $("#select_image").hide();
        $("#addData").hide();
        $("#line_height").hide();
        $("#qr_details").attr("placeholder", sourcePlaceholder);
        $("#sample_text").attr("placeholder", 'Enter Sample Text');
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
        $("#sample_text").hide();                            
        $("#placer_font").hide();
        $("#placer_font_underline").hide();
        $("#text_underline").hide();
        $("#font-div").hide();
        $('#placer_font_size').hide();
        $("#placer_display").show();
        $("#ghost_words").hide();
        $("#ghost_chars").hide();                            
        $("#color_selector").hide();    
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

    if(placer_type == 'Dynamic Image'){ 
        $("#qr_position").hide();   
        $("#source-link").hide();
        $("#qr_details").hide();                            
        $("#sample_text").hide();                            
        $("#placer_font").hide();
        $("#placer_font_underline").hide();
        $("#text_underline").hide();
        $("#font-div").hide();
        $('#placer_font_size').hide();
        $("#placer_display").show();
        $("#ghost_words").hide();
        $("#ghost_chars").hide();                            
        $("#color_selector").hide();    
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
        $("#source-link").hide();
        $("#qr_details").hide();                            
        $("#sample_text").show();                            
        $("#placer_font").show();
        $("#placer_font_underline").hide();
        $("#text_underline").hide();
        $("#font-div").show();
        $('#placer_font_size').show();
        $("#placer_display").show();
        $("#ghost_words").hide();
        $("#ghost_chars").hide();                            
        $("#color_selector").show();    
        $("#degree_angle").hide();  
        $("#opacity_val").hide(); 
        $('#placer_font_size').attr("readonly", false);
        $('#placer_type').attr("disabled", false); 
        $("#select_image").hide();
        $("#addData").hide(); 
        $("#line_height").hide();
        // $("#qr_details").attr("placeholder", sourcePlaceholder);
        $("#sample_text").attr("placeholder", 'Enter Sample text');

        $("#qr_show").hide();
        $("#qr_show_div").hide();
        $("#blockchain_show").hide();
        $("#blockchain_show_div").hide();
        $("#barcode_show_div").hide();
    }
    if(placer_type == 'Static Text'){ 
        $("#qr_position").hide();   
        $("#source-link").hide();
        $("#qr_details").hide();                            
        $("#sample_text").show();                            
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
        $("#degree_angle").show();  
        $("#opacity_val").show(); 
        $('#placer_font_size').attr("readonly", false);
        $('#placer_type').attr("disabled", false); 
        $("#select_image").hide();
        $("#addData").hide();
        $("#line_height").hide();
        // $("#qr_details").attr("placeholder", staticPlaceholder);
        $("#sample_text").attr("placeholder", staticPlaceholder);
        $("#qr_show").hide();
        $("#qr_show_div").hide();
        $("#blockchain_show").hide();
        $("#blockchain_show_div").hide();
        $("#barcode_show_div").hide();
    }
    if(placer_type == 'Common Static Text'){ 
        $("#qr_position").hide();   
        $("#source-link").hide();
        $("#qr_details").hide();                            
        $("#sample_text").show();                            
        $("#placer_font").show();
        $("#placer_font_underline").show();
        $("#text_underline").show();
        $("#font-div").show();
        $('#placer_font_size').show();
        $("#placer_display").show();
        $("#ghost_words").hide();
        $("#ghost_chars").hide();                            
        $("#color_selector").show();    
        $("#degree_angle").hide();  
        $("#opacity_val").show(); 
        $('#placer_font_size').attr("readonly", false);
        $('#placer_type').attr("disabled", false); 
        $("#select_image").hide();
        $("#addData").hide();
        $("#line_height").hide();
        // $("#qr_details").attr("placeholder", staticPlaceholder);
        $("#sample_text").attr("placeholder", 'Enter Sample Text');
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
        $("#sample_text").hide();                            
        $("#placer_font").hide();
        $("#placer_font_underline").hide();
        $("#text_underline").hide();
        $("#font-div").hide();
        $('#placer_font_size').hide();
        $("#placer_display").hide();
        $("#ghost_words").hide();
        $("#ghost_chars").hide();                            
        $("#color_selector").hide();    
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
        $("#sample_text").show();                            
        $("#placer_font").show();
        $("#placer_font_underline").hide();
        $("#text_underline").hide();
        $("#font-div").show();
        $('#placer_font_size').show();
        $("#placer_display").show();
        $("#ghost_words").hide();
        $("#ghost_chars").hide();                            
        $("#color_selector").show();    
        $("#degree_angle").show();  
        $("#opacity_val").show(); 
        $('#placer_font_size').attr("readonly", false);
        $('#placer_type').attr("disabled", false); 
        $("#select_image").hide();
        $("#addData").hide();
        $("#line_height").hide();
        $("#qr_details").attr("placeholder", sourcePlaceholder);
        $("#sample_text").attr("placeholder", 'Enter Sample Text');
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
        $("#sample_text").show();                            
        $("#placer_font").show();
        $("#placer_font_underline").hide();
        $("#text_underline").hide();
        $("#font-div").show();
        $('#placer_font_size').show();
        $("#placer_display").show();
        $("#ghost_words").hide();
        $("#ghost_chars").hide();                            
        $("#color_selector").show();    
        $("#degree_angle").show();  
        $("#opacity_val").show(); 
        $('#placer_font_size').attr("readonly", false);
        $('#placer_type').attr("disabled", false); 
        $("#select_image").hide();
        $("#addData").hide();
        $("#line_height").show();
        $("#qr_details").attr("placeholder", sourcePlaceholder);    
        $("#sample_text").attr("placeholder", 'Enter Sample Text');
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
        $("#sample_text").show();                            
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
        $('#placer_type').attr("disabled", false);
        $("#select_image").hide();
        $("#addData").hide();
        $("#line_height").hide();
        $("#qr_show").hide();
        $("#qr_show_div").hide();
        $("#blockchain_show").hide();
        $("#blockchain_show_div").hide();
        $("#barcode_show_div").hide();
        $("#sample_text").attr("placeholder", 'Enter Sample Text');
    } 
    if(placer_type == 'Box'){ 
        $("#qr_position").hide();   
        $("#source-link").hide();
        $("#qr_details").hide();                            
        $("#sample_text").hide();                            
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
        $("#degree_angle").hide();  
        $("#opacity_val").hide(); 
        $("#line_height").hide(); 
        $('#placer_font_size').attr("readonly", true);
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
}