//var pdf = new PDFAnnotate('pdf-container', 'pdf.pdf');

//console.log(config.routes.templatemaker);
var pdf = '';
var isLoaded = false; 
var action= document.getElementById('action_temp').value;
/*getParameterByName('action');*/ 
if(action == 'edit'){
   /* var rid = getParameterByName('id'); 
    var template_name = getParameterByName('temp'); 
    var pdf_page = getParameterByName('pdf_page'); 
    var file_name = getParameterByName('file');*/
    var rid =document.getElementById('edit_id').value; 
    var template_name = document.getElementById('edit_temp').value; 
    var pdf_page = document.getElementById('edit_pdf_page').value; 
    var file_name = document.getElementById('edit_file').value; 

    var verification_type = document.querySelector('input[name="verification_type"]:checked')?.value;
    console.log(pdf_page);
    var pdf = new PDFAnnotate('pdf-container', '../'+config.subdomain+'/'+file_name, 'edit', '../'+config.subdomain+'/documents/'+template_name+'.json');
    //ORG//var pdf = new PDFAnnotate('pdf-container', '../'+config.subdomain+'/'+file_name, 'edit', '../'+config.subdomain+'/documents/'+template_name+'.json');
    
    $('#template_id').val(rid);
    $('input[name="template-name"]').val(template_name);
    $('input[name="template-name"]').attr("readonly", "readonly");
    $('input[name="verification_type"]').attr('disabled', true);
    document.getElementById("pdf_page").value=pdf_page;
    $('#pdf_page').attr("disabled", true);
    $.getJSON('../'+config.subdomain+'/documents/'+template_name+'.json', function(data){
        total_pages=data.length;
        ids_arr = [];
        for(p = 0; p < total_pages; p++) { 
            var total_objects=data[p].objects.length;         
            var i;
            for(i = 0; i < total_objects; i++) {
                id=data[p].objects[i].id;
                ids_arr.push(id);
            } 
        }        
        //var ids = Math.max(...ids_arr)+1; 
        var ids = Math.max.apply(null, ids_arr)+1;
        document.getElementById('unique-id').value = ids;
    }).fail(function(){
        alert("An error has occurred while calling json.");
    }); 
}else{
    document.getElementById('unique-id').value = 1;
}

function getParameterByName(name, url = window.location.href) {
    name = name.replace(/[\[\]]/g, '\\$&');
    var regex = new RegExp('[?&]' + name + '(=([^&#]*)|&|#|$)'),
        results = regex.exec(url);
    if (!results) return null;
    if (!results[2]) return '';
    return decodeURIComponent(results[2].replace(/\+/g, ' '));
}

document.querySelector("#pdf-upload").addEventListener("change", function(e) {  
    $(".canvas-container").each(function(){
        $(this).remove();
    })
    var file = e.target.files[0]
    
    if (file.type != "application/pdf") {
      alert(file.name + " is not a pdf file.")
      return
    }
    var fileReader = new FileReader();
  
    fileReader.onload = function() {
      //$('input[name="template-name"]').removeAttr("readonly");
      var typedarray = new Uint8Array(this.result);
      pdf = new PDFAnnotate('pdf-container', typedarray);
      isLoaded = true;  
    }
    fileReader.readAsArrayBuffer(file); 
    
});

function enableSelector(event) {
    event.preventDefault();
    var element = ($(event.target).hasClass('tool-button')) ? $(event.target) : $(event.target).parents('.tool-button').first();
    $('.tool-button.active').removeClass('active');
    $(element).addClass('active');
    pdf.enableSelector();
}

function enablePencil(event) {
    event.preventDefault();
    var element = ($(event.target).hasClass('tool-button')) ? $(event.target) : $(event.target).parents('.tool-button').first();
    $('.tool-button.active').removeClass('active');
    $(element).addClass('active');
    pdf.enablePencil();
}

function enableAddText(event) {
    event.preventDefault();
    var element = ($(event.target).hasClass('tool-button')) ? $(event.target) : $(event.target).parents('.tool-button').first();
    $('.tool-button.active').removeClass('active');
    $(element).addClass('active');
    pdf.enableAddText();
}

function enableAddArrow(event) {
    event.preventDefault();
    var element = ($(event.target).hasClass('tool-button')) ? $(event.target) : $(event.target).parents('.tool-button').first();
    $('.tool-button.active').removeClass('active');
    $(element).addClass('active');
    pdf.enableAddArrow();
}

function enableRectangle(event) { 
    event.preventDefault();
    $("#placer_elements").hide();
    var element = ($(event.target).hasClass('tool-button')) ? $(event.target) : $(event.target).parents('.tool-button').first();
    $('.tool-button.active').removeClass('active');
    $(element).addClass('active');
    id=document.getElementById('unique-id').value;
    document.getElementById('unique-id').value=parseInt(id)+parseInt(1);
    $('#placer_font_size').attr("readonly", false);
    pdf.setColor('rgba(255, 0, 0, 0.3)');
    pdf.setBorderColor('blue');
    pdf.enableRectangle(id);    
}

function enablePlaceRectangle(event) { 
    event.preventDefault();
    $("#placer_elements").hide();
    var element = ($(event.target).hasClass('tool-button')) ? $(event.target) : $(event.target).parents('.tool-button').first();
    $('.tool-button.active').removeClass('active');
    $(element).addClass('active');
    id=document.getElementById('unique-id').value;
    document.getElementById('unique-id').value=parseInt(id)+parseInt(1);
    $('#placer_font_size').attr("readonly", false);
    pdf.setColor('rgba(0, 181, 204, 0.3)');
    pdf.setBorderColor('blue');
    pdf.enablePlaceRectangle(id);    
}

function enableGhostRectangle(event,fontsize) { 
    event.preventDefault();
    $("#placer_elements").hide();
    var element = ($(event.target).hasClass('tool-button')) ? $(event.target) : $(event.target).parents('.tool-button').first();
    $('.tool-button.active').removeClass('active');
    $(element).addClass('active');
    id=document.getElementById('unique-id').value;
    document.getElementById('unique-id').value=parseInt(id)+parseInt(1);   
    $('#placer_font_size').attr("readonly", true);
    pdf.setColor('rgba(0, 181, 204, 0.3)');
    pdf.setBorderColor('blue');
    print_words=document.getElementById('print_words').value;
    pdf.enableGhostRectangle(fontsize,id,print_words);    
}

function deleteSelectedObject() {
    event.preventDefault();
    pdf.deleteSelectedObject();
}

function savePDF() {
    pdf.savePdf();
}

function changeRectBoxId()
{
    var RectBoxId=document.getElementById('RectBoxId').value;
    pdf.changeRectBoxId(RectBoxId);
}

function changeRectBoxName()
{
    var RectBox=document.getElementById('RectBox').value;
    pdf.changeRectBoxName(RectBox);
}

function createClone()
{
    var clone_ep=document.getElementById('clone_ep').value;
    id=document.getElementById('unique-id').value;
    document.getElementById('unique-id').value=parseInt(id)+parseInt(1);
    pdf.createClone(clone_ep,id);
}

function changeRectBoxCoords()
{
    var RectBoxCoords=document.getElementById('coord-list').value;
    pdf.changeRectBoxCoords(RectBoxCoords);    
}

function changeRectWidth()
{
    var RectBoxWidth=document.getElementById('rect-width').value;
    pdf.changeRectWidth(RectBoxWidth);    
}

function changeRectHeight()
{
    var RectBoxHeight=document.getElementById('rect-height').value;
    pdf.changeRectHeight(RectBoxHeight);    
}

function changeSourceName()
{
    var SourceName=document.getElementById('source_selector').value;
    pdf.changeSourceName(SourceName);
}

function changePlacerType()
{
    var PlacerType=document.getElementById('placer_type').value;
    if(PlacerType == "Image"){
        img_name=$('#image_path').val();
        $.ajax({
        url: config.routes.imagelist,
        type: 'post',
        data: {_token:config.csrf_token,img_name: img_name},
        success: function(response){ 
          $('#imgModal .modal-body').html(response);
          $('#imgModal').modal({backdrop: 'static', keyboard: false, show: true}); 
        }
        });  
    }
    pdf.changePlacerType(PlacerType);
}
 
function changePlacerFont()
{
    var PlacerFontSize=document.getElementById('placer_font_size').value;
    pdf.changePlacerFont(PlacerFontSize);
}

function changeQrDetails()
{
    var QrDetails=document.getElementById('qr_details').value;
    pdf.changeQrDetails(QrDetails);
}

function changePlacerDisplay()
{
    var PlacerDisplay=document.getElementById('placer_display').value;
    pdf.changePlacerDisplay(PlacerDisplay);
}

function changeQrPosition()
{
    var QrPosition=document.getElementById('qr_position').value;
    pdf.changeQrPosition(QrPosition);
}

function changePlacerColor()
{
    var PlacerColor=document.getElementById('color_selector').value;
    pdf.changePlacerColor(PlacerColor);
}

function changeLineHeight()
{
    var LineHeight=document.getElementById('line_height').value;
    pdf.changeLineHeight(LineHeight);    
}

function changePlacerAngle()
{
    var PlacerAngle=document.getElementById('degree_angle').value;
    if(PlacerAngle === null || PlacerAngle === '' || isNaN(PlacerAngle) === true){ 
        pdf.changePlacerAngle(0);
    }else{
        pdf.changePlacerAngle(PlacerAngle);
    }
}

function changeGhostPlacerAngle()
{
    var PlacerAngle=document.getElementById('ghost_degree_angle').value;
    if(PlacerAngle === null || PlacerAngle === '' || isNaN(PlacerAngle) === true){ 
        pdf.changeGhostPlacerAngle(0);
    }else{
        pdf.changeGhostPlacerAngle(PlacerAngle);
    }
}

function changePlacerOpacity()
{
    var PlacerOpacity=document.getElementById('opacity_val').value;
    pdf.changePlacerOpacity(PlacerOpacity);
}

function changePlacerFontName()
{
    var PlacerFont=document.getElementById('placer_font').value;
    pdf.changePlacerFontName(PlacerFont);
}

function changePlacerFontBold()
{
    var PlacerFont=document.getElementById('placer_font_bold');
    if(PlacerFont.checked) {
        PlacerFontBold = PlacerFont.value;
    }else{
        PlacerFontBold = '';
    }
    pdf.changePlacerFontBold(PlacerFontBold);
}

function changePlacerFontItalic()
{
    var PlacerFont=document.getElementById('placer_font_italic');
    if(PlacerFont.checked) {
        PlacerFontItalic = PlacerFont.value;
    }else{
        PlacerFontItalic = '';
    }
    pdf.changePlacerFontItalic(PlacerFontItalic);
}

function changePlacerFontUnderline()
{
    var PlacerFont=document.getElementById('placer_font_underline');
    if(PlacerFont.checked) {
        PlacerFontUnderline = PlacerFont.value;
    }else{
        PlacerFontUnderline = '';
    }
    pdf.changePlacerFontUnderline(PlacerFontUnderline);
}

function changeQrPlace()
{
    var PlacerQr=document.getElementById('qr_show');
    if(PlacerQr.checked) {
        QrPlace = PlacerQr.value;
    }else{
        QrPlace = '';
    }
    pdf.changeQrPlace(QrPlace);
}

function changeBlockchainPlace()
{
	// Get the checkbox
	var checkBox = document.getElementById("blockchain_show");
	// Get the output
	var metadata_label = document.getElementById("metadata_label");
	var metadata_value = document.getElementById("metadata_value");
	// If the checkbox is checked, display the output
	if (checkBox.checked == true){
		metadata_label.style.display = "block";
		metadata_value.style.display = "block";
	} else {
		metadata_label.style.display = "none";
		metadata_value.style.display = "none";
		metadata_label.value='';
		metadata_value.value='';
	}
    var PlacerBc=document.getElementById('blockchain_show');
    if(PlacerBc.checked) {
        BcPlace = PlacerBc.value;
    }else{
        BcPlace = '';
    }
    pdf.changeBlockchainPlace(BcPlace,checkBox.checked);
}

function changePlacerMetaLabel()
{
    var PlacerLabel=document.getElementById('metadata_label').value; 
    pdf.changePlacerMetaLabel(PlacerLabel);
}

function changePlacerMetaValue()
{
    var PlacerValue=document.getElementById('metadata_value').value;
    pdf.changePlacerMetaValue(PlacerValue);
}

function BarcodeSource()
{
    var SourceName=document.getElementById('source_selector');
    var BarcodeSc=document.getElementById('barcode_sc');
    if(BarcodeSc.checked) {        
        if(SourceName.value==''){ 
            BarcodeSc.checked = false;
            alert("Please Select a Source."); 
            return false;
        }
        ScPlace = BarcodeSc.value;
    }else{
        ScPlace = '';
        SourceName.selectedIndex = "0";
    }
    pdf.BarcodeSource(ScPlace);
}

function BarcodeTextAlignment()
{
    var BarcodeTab=document.getElementById('barcode_tab');
    if(BarcodeTab.checked) {
        TabPlace = BarcodeTab.value;
    }else{
        TabPlace = '';
    }
    pdf.BarcodeTextAlignment(TabPlace);
}

function isNaN(x) {
   return x !== x;
};
function savePDFCoord(){       
    //pdf.removeBackgroundImage();
    //var files = document.getElementById("pdf-upload").files;
    //alert(files[0]);
    var template_id=$('#template_id').val();
    var source_selector=$('#source_selector').val();
    var pdfData = pdf.serializePdf();    
    var form_data = new FormData($("#form")[0]);    
    var new_coord = '';
    var placer_coord = '';
    var template_name = $("#template-name").val();
    if(template_name == '') {
        alert('Please enter name for template.');
        return false;
    } 
    var pdf_page = $("#pdf_page").val();
    if(pdf_page == '') {
        alert('Please select page.');
        return false;
    }
    //$("textarea#pdf_json").text(pdfData); 
    form_data.append('template_id',template_id);
    form_data.append('template_name',template_name); 
    form_data.append('pdf_page',pdf_page); 
    form_data.append('pdf_data',pdfData); 
    var verification_type = document.querySelector('input[name="verification_type"]:checked')?.value;
    form_data.append('verification_type',verification_type);
    var results = [];
    var extractorGroup = [];
    var placerGroup = [];
    var placerTypes = [];
    var extractorNameGroup = [];
    var AllNameGroupCheck = [];
    var extractorNameGroupCheck = [];
    var placerNameGroupCheck = [];
    var placerQrGroup = []; //QR Details
    var extractorCoords = {};
    var object = null;
    
    for(co in pdf.rectObjs){
        nature = pdf.rectObjs[co].nature;
        if(nature == 'extractor'){
            source_name= pdf.rectObjs[co].name;
            get_coord = pdf.rectObjs[co].calcCoords();
            get_coords = get_coord.tl.x + ',' + get_coord.tl.y + ',' + get_coord.br.x + ',' + get_coord.br.y;   
            extractorCoords[source_name] = get_coords;            
        }    
    }
    
    source_count=0;
    for(key in extractorCoords){
        //alert(key +"|"+extractorCoords[key]);
        if(extractorCoords.hasOwnProperty(key)) {
            source_count++;
        }       
    }
    bcflag_count=0;
	for(bc_flag in pdf.rectObjs){
        nature = pdf.rectObjs[bc_flag].nature;
        if(nature == 'placer'){
            if(pdf.rectObjs[bc_flag].bcPlace == 'use'){
				bcflag_count++;
			}
        }    
    }
	if(bcflag_count>5){
		alert("Block Chain Metadata count should be 5.");
		return false;
	}

    // Text Verification condition added by rohit 07/02/2025 
    tvflag_count=0;
    for(tv_flag in pdf.rectObjs){
        nature = pdf.rectObjs[tv_flag].nature;
        if(nature == 'extractor'){
            if(pdf.rectObjs[tv_flag].text_verification == 'use'){
                tvflag_count++;
            }
        }    
    }
    // Text Verification condition added by rohit 07/02/2025
    // console.log(tvflag_count);

    if(tvflag_count>10){
        alert("Text Verification Metadata count should not be more than 10.");
        return false;
    }

    for(i in pdf.rectObjs){
        AllNameGroupCheck.push(pdf.rectObjs[i].name);
        nature = pdf.rectObjs[i].nature;
        if(nature == 'extractor'){
            var extractorData = {};
            new_coord = pdf.rectObjs[i].calcCoords();
            new_coords = new_coord.tl.x + ',' + new_coord.tl.y + ',' + new_coord.br.x + ',' + new_coord.br.y;
            extractorData['id'] = pdf.rectObjs[i].id;
            extractorData['page_no'] = pdf.rectObjs[i].page_no;
            extractorData['name'] = pdf.rectObjs[i].name;
            extractorData['nature'] = pdf.rectObjs[i].nature;
            extractorData['coords'] = new_coords;
			extractorData['blockchain_flag'] = pdf.rectObjs[i].bcPlace;
			extractorData['metadata_label'] = pdf.rectObjs[i].labelPlace;
			extractorData['metadata_value'] = pdf.rectObjs[i].labelValue;

            extractorData['text_verification'] = pdf.rectObjs[i].text_verification; 
            extractorData['text_verification_lable'] = pdf.rectObjs[i].text_verification_lable;
            extractorData['text_verification_value'] = pdf.rectObjs[i].text_verification_value;
            
            extractorGroup.push(extractorData);             
            extractorNameGroup.push(pdf.rectObjs[i].name);  
            extractorNameGroupCheck.push(pdf.rectObjs[i].name);            
            //form_data.append('boxes[]',new_coord.tl.x + ',' + new_coord.tl.y + ',' + new_coord.br.x + ',' + new_coord.br.y);
            if(pdf.rectObjs[i].name == ''){
                alert("Please enter extactor name.");
                return false; 
            }
            if(pdf.rectObjs[i].bcPlace == 'use' && pdf.rectObjs[i].labelPlace==''){
                alert("Please enter a Metadata Label.");
                return false; 
            }
        }else if(nature == 'placer'){
            placerNameGroupCheck.push(pdf.rectObjs[i].name);  
            var placerData = {};
            var sourceData = {};
            placer_coord = pdf.rectObjs[i].calcCoords();
            placer_coords = placer_coord.tl.x + ',' + placer_coord.tl.y + ',' + placer_coord.br.x + ',' + placer_coord.br.y;            
            placerData['id'] = pdf.rectObjs[i].id;
            placerData['page_no'] = pdf.rectObjs[i].page_no;
            placerData['name'] = pdf.rectObjs[i].name;
            placerData['source'] = pdf.rectObjs[i].source;
            placerData['nature'] = pdf.rectObjs[i].nature;
            placerData['coords'] = placer_coords;
            placerData['placer_font_name'] = pdf.rectObjs[i].fontName;
            placerData['placer_font_bold'] = pdf.rectObjs[i].fontBold;
            placerData['placer_font_italic'] = pdf.rectObjs[i].fontItalic;
            placerData['placer_font_underline'] = pdf.rectObjs[i].fontUnderline;            
            placerData['placer_font_size'] = pdf.rectObjs[i].fontSize;
            placerData['placer_type'] = pdf.rectObjs[i].placer_type;
            placerData['ghost_words'] = pdf.rectObjs[i].ghost_words;
            placerData['placer_display'] = pdf.rectObjs[i].placer_display;
            placerData['qr_details'] = pdf.rectObjs[i].qr_details;            
            placerData['qr_position'] = pdf.rectObjs[i].qr_position;            
            placerData['degree_angle'] = pdf.rectObjs[i].degree_angle;            
            placerData['font_color'] = pdf.rectObjs[i].font_color;            
            placerData['opacity_val'] = pdf.rectObjs[i].opacity_val;            
            placerData['image_path'] = pdf.rectObjs[i].image_path;   
            placerData['line_height'] = pdf.rectObjs[i].lineHeight;
			placerData['qr_place'] = pdf.rectObjs[i].qrPlace;
			/*placerData['blockchain_flag'] = pdf.rectObjs[i].bcPlace;
			placerData['metadata_label'] = pdf.rectObjs[i].labelPlace;
			placerData['metadata_value'] = pdf.rectObjs[i].labelValue;*/
            placerData['left'] = pdf.rectObjs[i].left;            
            placerData['top'] = pdf.rectObjs[i].top; 
            placerData['barcode_content'] = pdf.rectObjs[i].barcodeContent; 
            placerData['barcode_content_position'] = pdf.rectObjs[i].barcodeContentPosition; 

            if(pdf.rectObjs[i].name == ''){
                alert("Please enter placer name.");
                return false; 
            }    
            if((pdf.rectObjs[i].placer_type == 'QR Default' || pdf.rectObjs[i].placer_type == 'QR Dynamic') && pdf.rectObjs[i].source==''){
                alert("Please select a source of QR.");
                return false; 
            }  
            if(pdf.rectObjs[i].placer_type == 'QR Invisible Plain Text' && (pdf.rectObjs[i].source=='' && pdf.rectObjs[i].qr_details=='')){
                alert("Please select a source of QR Invisible Plain Text or Add Combination of Sources.");
                return false; 
            }
            if(pdf.rectObjs[i].placer_type == 'QR Plain Text' && (pdf.rectObjs[i].source=='' && pdf.rectObjs[i].qr_details=='')){
                alert("Please select a source of QR Plain Text or Add Combination of Sources.");
                return false; 
            }
            if(pdf.rectObjs[i].placer_type == 'Micro Line' && (pdf.rectObjs[i].source=='' && pdf.rectObjs[i].qr_details=='')){
                alert("Please select a source of Micro Line or Add Combination of Sources.");
                return false; 
            }                   
            if(pdf.rectObjs[i].placer_type == 'Invisible' && (pdf.rectObjs[i].source=='' && pdf.rectObjs[i].qr_details=='')){
                alert("Please select a source of Invisible or Add Combination of Sources.");
                return false; 
            }
            if(pdf.rectObjs[i].placer_type == 'Plain Text' && (pdf.rectObjs[i].source=='' && pdf.rectObjs[i].qr_details=='')){
                alert("Please select a source of Plain Text or Add Combination of Sources.");
                return false; 
            }
            if(pdf.rectObjs[i].placer_type == 'Static Text' && pdf.rectObjs[i].qr_details==''){
                alert("Please add static text.");
                return false; 
            }
            /*if(pdf.rectObjs[i].placer_type == 'Static Text' && (pdf.rectObjs[i].degree_angle === null || pdf.rectObjs[i].degree_angle === '' || isNaN(pdf.rectObjs[i].degree_angle) === true)){
                alert("Please enter Degree Angle of Static Text.");
                return false; 
            }*/
            if(pdf.rectObjs[i].placer_type == 'Static Text' && pdf.rectObjs[i].opacity_val==''){
                alert("Please select Opacity of Static Text.");
                return false; 
            }
            if(pdf.rectObjs[i].placer_type == 'Common Static Text' && pdf.rectObjs[i].qr_details==''){
                alert("Please add static text.");
                return false; 
            }
            /*if(pdf.rectObjs[i].placer_type == 'Common Static Text' && (pdf.rectObjs[i].degree_angle === null || pdf.rectObjs[i].degree_angle === '' || isNaN(pdf.rectObjs[i].degree_angle) === true)){
                alert("Please enter Degree Angle of Static Text-on each page.");
                return false; 
            }*/
            if(pdf.rectObjs[i].placer_type == 'Common Static Text' && pdf.rectObjs[i].opacity_val==''){
                alert("Please select Opacity of Static Text-on each page.");
                return false; 
            }
            if(pdf.rectObjs[i].placer_type == 'Watermark Text' && (pdf.rectObjs[i].source=='' && pdf.rectObjs[i].qr_details=='')){
                alert("Please select a source of Watermark Text or Add Combination of Sources.");
                return false; 
            }
            if(pdf.rectObjs[i].placer_type == 'Watermark Text' && (pdf.rectObjs[i].degree_angle === null || pdf.rectObjs[i].degree_angle === '' || isNaN(pdf.rectObjs[i].degree_angle) === true)){
                alert("Please enter Degree Angle of Watermark Text.");
                return false; 
            }
            if(pdf.rectObjs[i].placer_type == 'Watermark Text' && pdf.rectObjs[i].opacity_val==''){
                alert("Please select Opacity of Watermark Text.");
                return false; 
            }
            if(pdf.rectObjs[i].placer_type == 'Watermark Multi Lines' && (pdf.rectObjs[i].source=='' && pdf.rectObjs[i].qr_details=='')){
                alert("Please select a source of Watermark Multi Lines Background or Add Combination of Sources.");
                return false; 
            }
            if(pdf.rectObjs[i].placer_type == 'Watermark Multi Lines' && (pdf.rectObjs[i].degree_angle === null || pdf.rectObjs[i].degree_angle === '' || isNaN(pdf.rectObjs[i].degree_angle) === true)){
                alert("Please enter Degree Angle of Watermark Multi Lines Background.");
                return false; 
            }
            if(pdf.rectObjs[i].placer_type == 'Watermark Multi Lines' && pdf.rectObjs[i].opacity_val==''){
                alert("Please select Opacity of Watermark Multi Lines Background.");
                return false; 
            }
            if(pdf.rectObjs[i].placer_type == 'Ghost Image' && pdf.rectObjs[i].source==''){
                alert("Please select a source of Ghost Image.");
                return false; 
            }

            //var searchString = pdf.rectObjs[i].source;
            //allList.indexOf(searchString);   
            sourceData['page_no'] = pdf.rectObjs[i].page_no;
            sourceData['placer'] = pdf.rectObjs[i].name;
            sourceData['width'] = pdf.rectObjs[i].width;
            sourceData['height'] = pdf.rectObjs[i].height;
            sourceData['placer_coords'] = placer_coords;
            sourceData['placer_font_name'] = pdf.rectObjs[i].fontName;
            sourceData['placer_font_bold'] = pdf.rectObjs[i].fontBold;
            sourceData['placer_font_italic'] = pdf.rectObjs[i].fontItalic;
            sourceData['placer_font_underline'] = pdf.rectObjs[i].fontUnderline;
            sourceData['placer_font_size'] = pdf.rectObjs[i].fontSize;
            sourceData['placer_type'] = pdf.rectObjs[i].placer_type;
            sourceData['ghost_words'] = pdf.rectObjs[i].ghost_words;
            sourceData['placer_display'] = pdf.rectObjs[i].placer_display;
            sourceData['qr_details'] = pdf.rectObjs[i].qr_details;
            sourceData['qr_position'] = pdf.rectObjs[i].qr_position;
            sourceData['source'] = pdf.rectObjs[i].source;
            sourceData['degree_angle'] = pdf.rectObjs[i].degree_angle;            
            sourceData['font_color'] = pdf.rectObjs[i].font_color;            
            sourceData['opacity_val'] = pdf.rectObjs[i].opacity_val;   
            sourceData['image_path'] = pdf.rectObjs[i].image_path;  
            sourceData['line_height'] = pdf.rectObjs[i].lineHeight;  
			sourceData['qr_place'] = pdf.rectObjs[i].qrPlace;
			/*sourceData['blockchain_flag'] = pdf.rectObjs[i].bcPlace;
			sourceData['metadata_label'] = pdf.rectObjs[i].labelPlace;
			sourceData['metadata_value'] = pdf.rectObjs[i].labelValue;*/
            sourceData['left'] = pdf.rectObjs[i].left;            
            sourceData['top'] = pdf.rectObjs[i].top; 
            sourceData['barcode_content'] = pdf.rectObjs[i].barcodeContent; 
            sourceData['barcode_content_position'] = pdf.rectObjs[i].barcodeContentPosition;
            
            for(key in extractorCoords){
                if(key == pdf.rectObjs[i].source) {
                    //alert(key +"|"+extractorCoords[key] +"|"+pdf.rectObjs[i].source);
                    sourceData['source_coords'] = extractorCoords[key];
                }       
            } 
            results.push(sourceData);
            
            placerGroup.push(placerData);
            if(pdf.rectObjs[i].qr_details != '' && (pdf.rectObjs[i].placer_type != 'Static Text' && pdf.rectObjs[i].placer_type != 'Common Static Text')){
                placerQrGroup.push(pdf.rectObjs[i].qr_details);
            }
            placerTypes.push(pdf.rectObjs[i].placer_type);
            //form_data.append('placer_boxes[]',placer_coord.tl.x + ',' + placer_coord.tl.y + ',' + placer_coord.br.x + ',' + placer_coord.br.y);
        }
    }

            form_data.append('extractor_boxes',JSON.stringify(extractorGroup));
            form_data.append('placer_boxes',JSON.stringify(placerGroup));
            form_data.append('ep_boxes',JSON.stringify(results));
            form_data.append('_token',config.csrf_token);  
         
    
    var duplicatesExtractor = extractorNameGroupCheck.reduce(function(acc, el, i, arr) {
      if (arr.indexOf(el) !== i && acc.indexOf(el) < 0) acc.push(el); return acc;
    }, []);
    if(duplicatesExtractor.length > 0){
        //console.log(duplicatesExtractor);
        for (var i = 0; i < duplicatesExtractor.length; i++) {
            alert(duplicatesExtractor[i]+' is duplicated extractor name.');
        }
        return false;
    }
      
    var duplicatesPlacer = placerNameGroupCheck.reduce(function(acc, el, i, arr) {
      if (arr.indexOf(el) !== i && acc.indexOf(el) < 0) acc.push(el); return acc;
    }, []);
    if(duplicatesPlacer.length > 0){
        //console.log(duplicatesPlacer);
        for (var i = 0; i < duplicatesPlacer.length; i++) {
            alert(duplicatesPlacer[i]+' is duplicated placer name.');
        }
        return false;
    }
    
    var duplicatesAllName = AllNameGroupCheck.reduce(function(acc, el, i, arr) {
      if (arr.indexOf(el) !== i && acc.indexOf(el) < 0) acc.push(el); return acc;
    }, []);
    if(duplicatesAllName.length > 0){
        //console.log(duplicatesAllName);
        for (var i = 0; i < duplicatesAllName.length; i++) {
            alert(duplicatesAllName[i]+' is duplicated name for extractor and placer.');
        }
        return false;
    }    
    
    var count_qr=placerQrGroup.length;
    if(count_qr > 0){
        //alert(count_qr+"\n"+placerQrGroup); source_name=qrString.split('^')[0];
        en_count=extractorNameGroup.length; //Count of extractor names
        for (q = 0; q < count_qr; q++) {
            qrString = placerQrGroup[q].replace(/\{|\}/gi, ''); //replace curly brackets            
            qr_details_split = qrString.split("\n");    
            existed=qr_details_split.length;            
            var final = extractorNameGroup.filter(function(item) {
              for (var i = 0; i < qr_details_split.length; i++) {
                //alert(qr_details_split[i]+" | "+item);
                if (qr_details_split[i].split('^')[1] === item) return true;
              }
              return false;
            })            
            matched=final.length;            
            //alert(existed+" | "+matched);
            if (existed != matched){
                alert("Combination of Source doesn't match with extractor name.");
                return false; 
            }            
        }        
    }    
    const foundQrPlacerType = placerTypes.find(element => element == 'QR Default' || element == 'QR Dynamic' );
    if(typeof foundQrPlacerType === 'undefined'){
        alert("Please add QR.");
        return false; 
    }
        
    $.ajax({
        url: config.routes.createtemplate,//"../pdf2pdf/pdfupload.php",
        type: "POST",
        data:  form_data,
        contentType: false,
        cache: false,
        processData:false,
        //beforeSend : function() { $("#show-msg").show(); $('#show-msg').html('Please Wait...'); },
        success: function(Result) { 
            //console.log(Result)
            var data = /*JSON.parse(*/Result/*)*/;
            //alert(data.rstatus);
            if(data.rstatus=='invalid') {
                // invalid file format.
                alert('Invalid File');
                return false;
            } 
            else if(data.rstatus=='exist') {
                alert('Template name already exists.');
                return false;
            }
            else if(data.rstatus=='edit') {
                //$("#show-msg").hide();
                alert('Succefully edited');
            } 
            else if(data.rstatus=='insert') {
                //$("#show-msg").hide();
                alert('Succefully saved');
                //$("#form")[0].reset(); 
                $('#template_id').val(data.id);
                $('input[name="template-name"]').attr("readonly", "readonly");
                $('#pdf_page').attr("disabled", true);
                $('#pdf-upload').val('');
            }  
            else {
                alert('Error');
                return false;
            }            
        },
        error: function(e) {
            console.error(e);
        },
        complete: function (data) {
           /* $('#pdf-upload').val('');*/ // this will reset the form fields
        }  
        
    });
}

function clearPage() {
    pdf.clearActivePage();
}

function showPdfData() {
    var string = pdf.serializePdf();
    $('#dataModal .modal-body pre').first().text(string);
    PR.prettyPrint();
    $('#dataModal').modal({backdrop: 'static', keyboard: false});
}

$(function () {
    $('.color-tool').click(function () {
        $('.color-tool.active').removeClass('active');
        $(this).addClass('active');
        color = $(this).get(0).style.backgroundColor;
        pdf.setColor(color);
    });

    $('#brush-size').change(function () {
        var width = $(this).val();
        pdf.setBrushSize(width);
    });

    $('#font-size').change(function () {
        var font_size = $(this).val();
        pdf.setFontSize(font_size);
    });
});

// Restricts input for the given textbox to the given inputFilter.
function setInputFilter(textbox, inputFilter) {
  if(textbox != null){
  ["input", "keydown", "keyup", "mousedown", "mouseup", "select", "contextmenu", "drop"].forEach(function(event) {
    textbox.addEventListener(event, function() {
      if (inputFilter(this.value)) {
        this.oldValue = this.value;
        this.oldSelectionStart = this.selectionStart;
        this.oldSelectionEnd = this.selectionEnd;
      } else if (this.hasOwnProperty("oldValue")) {
        this.value = this.oldValue;
        this.setSelectionRange(this.oldSelectionStart, this.oldSelectionEnd);
      } else {
        this.value = "";
      }
    });
  });
  }
}
/* 
// Install input filters.
setInputFilter(document.getElementById("intTextBox"), function(value) {
  return /^-?\d*$/.test(value); });
setInputFilter(document.getElementById("uintTextBox"), function(value) {
  return /^\d*$/.test(value); });
setInputFilter(document.getElementById("intLimitTextBox"), function(value) {
  return /^\d*$/.test(value) && (value === "" || parseInt(value) <= 500); });
setInputFilter(document.getElementById("floatTextBox"), function(value) {
  return /^-?\d*[.,]?\d*$/.test(value); });
setInputFilter(document.getElementById("currencyTextBox"), function(value) {
  return /^-?\d*[.,]?\d{0,2}$/.test(value); });
setInputFilter(document.getElementById("latinTextBox"), function(value) {
  return /^[a-z]*$/i.test(value); });
setInputFilter(document.getElementById("hexTextBox"), function(value) {
  return /^[0-9a-f]*$/i.test(value); });
*/  
//setInputFilter(document.getElementById("RectBox"), function(value) { return /^[a-zA-Z0-9_]+$/.test(value); });
setInputFilter(document.getElementById("RectBox"), function(value) { return /^[a-zA-Z0-9-_]*$/i.test(value);});
setInputFilter(document.getElementById("rect-width"), function(value) { return /^-?\d*[.]?\d*$/.test(value); });
setInputFilter(document.getElementById("rect-height"), function(value) { return /^-?\d*[.]?\d*$/.test(value); });  
setInputFilter(document.getElementById("placer_font_size"), function(value) { return /^-?\d*[.]?\d*$/.test(value); });  
setInputFilter(document.getElementById("RectBoxId"), function(value) { return /^-?\d*[.]?\d*$/.test(value); });    
setInputFilter(document.getElementById("degree_angle"), function(value) { return /^-?\d*[.]?\d*$/.test(value); });
  
$(document).ready(function(){
    $.ajaxSetup({ cache: false });  
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
    
    function refreshProgress(filename='') {        
        src='../'+config.subdomain+'/processed_pdfs/'+filename        
        var xhr = new XMLHttpRequest();
        xhr.onreadystatechange = function () {
            if (this.readyState === this.DONE) {
                if (xhr.status === 200) {
                    readTextFile(src,function(allText){
                        if(allText){
                            const obj = JSON.parse(allText);
                            var percent=obj.percent;
                            var msg=obj.message;
                            $("#progress-file").html('<div class="bar" style="width:' + percent + '%">' + percent + '%</div>');
                            $("#message-file").html(msg);
                            if (percent == 100) {       
                                window.clearInterval(timer);
                                var beginning_time=obj.beginning_time;
                                var ending_time=obj.ending_time;
                                var exec_time=obj.exec_time;
                                var hms_time=obj.hms_time;
                                var page_time=obj.page_time;
                                var pages_processed=obj.pages_processed;
                                time_msg= "<div class='py-1'></div><table class='table table-bordered'><tbody><tr><td width='50%'>Pages Processed</td><td>"+pages_processed+"</td></tr><tr><td>Start Time</td><td>"+beginning_time+"</td></tr><tr><td>End Time</td><td>"+ending_time+"</td></tr><tr><td>Execution Time</td><td>"+hms_time+"</td></tr><tr><td>Time Per Page</td><td>"+page_time+"</td></tr></tbody></table>"; 
                                $("#single-file").html(time_msg);                                
                                timer = window.setInterval(completed(filename), 1000);
                            }  
                        }    
                    });  
                    
                } else {
                    return false;
                }
            }
        }
        xhr.timeout = 5000;           // TIMEOUT SET TO PREFERENCE (5 SEC)
        xhr.open('HEAD', src, true);
        xhr.send(null);               // VERY IMPORTANT        
    }

    function refreshProgressMulti(filename='') {        
        src='../'+config.subdomain+'/processed_pdfs/'+filename        
        var xhr = new XMLHttpRequest();
        xhr.onreadystatechange = function () {
            if (this.readyState === this.DONE) {
                if (xhr.status === 200) {
                    readTextFile(src,function(allText){
                        if(allText){
                            const obj = JSON.parse(allText);
                            var percent=obj.percent;
                            var msg=obj.message;
                            $("#progress-folder").html('<div class="bar" style="width:' + percent + '%">' + percent + '%</div>');
                            $("#message-folder").html(msg);
                            if (percent == 100) {       
                                window.clearInterval(timer);
                                var beginning_time=obj.beginning_time;
                                var ending_time=obj.ending_time;
                                var exec_time=obj.exec_time;
                                var hms_time=obj.hms_time;
                                var page_time=obj.page_time;
                                var pages_processed=obj.pages_processed;
                                time_msg= "<div class='py-1'></div><table class='table table-bordered'><tbody><tr><td width='25%'>Pages Processed</td><td>"+pages_processed+"</td></tr><tr><td>Start Time</td><td>"+beginning_time+"</td></tr><tr><td>End Time</td><td>"+ending_time+"</td></tr><tr><td>Execution Time</td><td>"+hms_time+"</td></tr><tr><td>Time Per Page</td><td>"+page_time+"</td></tr></tbody></table>"; 
                                $("#multi-file").html(time_msg);                                
                                timer = window.setInterval(completed(filename), 1000);
                            }  
                        }    
                    });  
                    
                } else {
                    return false;
                }
            }
        }
        xhr.timeout = 5000;           // TIMEOUT SET TO PREFERENCE (5 SEC)
        xhr.open('HEAD', src, true);
        xhr.send(null);               // VERY IMPORTANT        
    }
    
    function completed(progress_file) {
        //$("#message-file").html("Completed");
        $.ajax({
            url: config.routes.createtextfile,
            type:"POST",
            data:{'_token':config.csrf_token,"file":progress_file,"status":"end"},
            success:function(data){}
        });          
        window.clearInterval(timer);
    }
    
    function wait(ms){
       var start = new Date().getTime();
       var end = start;
       while(end < start + ms) {
         end = new Date().getTime();
      }
    }    
    
    // preview and generate
    $('#previewPdf').on('change',function(){

       var value = $('#previewPdfValue').val();

       if(value==1){
          $('#previewPdfValue').val(0);
          $('#previewPdfCheckbox').hide();
          $('#generate_title').html('Generate Live PDF:');
       }else{
          $('#previewPdfValue').val(1);
          $('#previewPdfCheckbox').show();
          $('#generate_title').html('PDF Preview:');
       }

    });
        
    $('#btn_upload').click(function(){        
        var template_id=$('#template_id').val();
        var pdf_page=$('#pdf_page').val();
        var progress_file=Math.floor(Math.random() * Math.floor(Math.random() * Date.now()))+".txt";         
        var files = $('#file_process')[0].files[0];
        if(typeof files === 'undefined'){ alert("Please select PDF."); return false;}
        $.ajax({
            url: config.routes.createtextfile,
            type:"POST",
            data:{"_token":config.csrf_token,"file":progress_file,"status":"start"},
            success:function(data){}
        });         
        wait(1000);  //1 second in milliseconds
        $('#progress-file').show();
        var fd = new FormData();
        fd.append('file',files);
        fd.append('template_id',template_id);
        fd.append('pdf_page',pdf_page);
        fd.append('progress_file',progress_file);
        fd.append('_token',config.csrf_token);
        $.ajax({
            url: config.routes.processpdf,
            type: 'post',
            data: fd,
            contentType: false,
            processData: false,
            beforeSend : function() {
                $('#btn_upload').hide();
                $('#preview').html('Processing...');
            },      
            xhr: function() {
                var xhr = $.ajaxSettings.xhr();
                xhr.upload.onprogress = function(e) {
                    console.log(Math.floor(e.loaded / e.total *100) + '%');
                };
                return xhr;
            },
            success: function(response){
                var res = eval('('+response+')');                
                if(res.type == 'Success'){                    
                    $('#preview').fadeIn().html('Completed');
                    $('#preview').html("<a href='../"+config.subdomain+"/"+res.dlink+"' target='_blank' style='color: #0056b3;'>Download</a>");
                }
                else if(res.type == 'Duplicates'){                    
                    $('#preview').html(res.msg+"<br /><input type='button' class='btn btn-primary' data-file='"+res.filename+"' data-templateid='"+res.template_id+"' data-pdfpage='"+res.pdf_page+"' data-unids='"+res.unids+"' data-progressfile='"+res.progressfile+"' value='Proceed' id='btn_proceed'>");
                    $('#btn_upload').hide();
                    $('#progress-file').hide();
                    $('#message-file').hide();
                    $('#progress-file').html('');
                    $('#message-file').html('');
                    return false;
                }
                else if(res.type == 'Over Limit'){                    
                    $('#preview').html(res.msg);
                    $('#btn_upload').hide();
                    $('#progress-file').hide();
                    $('#message-file').hide();
                    $('#progress-file').html('');
                    $('#message-file').html('');
                    completed(progress_file);
                    return false;
                } 
				else if(res.type == 'Empty Extractor'){                    
                    $('#preview').html(res.msg);
                    $('#btn_upload').hide();
                    $('#progress-file').hide();
                    $('#message-file').hide();
                    $('#progress-file').html('');
                    $('#message-file').html('');
                    completed(progress_file);
                    return false;
                } 
                else{
                  alert('file not uploaded');
                }
            },
            complete: function (data) {
                $('#form_process')[0].reset(); // this will reset the form fields
            }
        });
        // Refresh the progress bar every 1 second.
        timer = window.setInterval(function(){refreshProgress(progress_file);}, 1000);
    });

    $(document.body).on('click', "#btn_proceed", function(){    
        var template_id=$(this).data('templateid');
        var pdf_page=$(this).data('pdfpage');        
        var file=$(this).data('file'); 
        var unids=$(this).data('unids'); 
        var progress_file=$(this).data('progressfile');
        $('#progress-file').show();
        $('#message-file').show();
        $.ajax({
            url: config.routes.processpdfagain,
            type: 'post',
            data: {_token:config.csrf_token,file:file, template_id:template_id, pdf_page:pdf_page, unids:unids, progressfile:progress_file},
            beforeSend : function() {
                $('#preview').html('Processing...');
            }, 
            success: function(response){
                var res = eval('('+response+')');                
                if(res.type == 'Success'){                    
                    $('#preview').fadeIn().html('Completed');
                    $('#preview').html("<a href='../"+config.subdomain+"/"+res.dlink+"' target='_blank' style='color: #0056b3;' >Download</a>");
                }
                else{
                  alert('Error');
                }
            }
        }); 
        // Refresh the progress bar every 1 second.
        //timer = window.setInterval(function(){refreshProgress(progress_file);}, 1000); 
    });      
    
    $('#btn_folder').click(function(){ 
        var template_id=$('#template_id').val();
        var pdf_page=$('#pdf_page').val(); 
        var folder_name=$('#folder-process').val(); 
        var progress_file=Math.floor(Math.random() * Math.floor(Math.random() * Date.now()))+".txt";
        var fd = new FormData(); 
        var totalfiles = document.getElementById('folderprocess').files.length; 
        if(folder_name == ''){ alert("Please select folder."); return false;}
        if(folder_name == 'Auto Create'){
            if(totalfiles == 0){ alert("Please select PDF."); return false;}
            for (var index = 0; index < totalfiles; index++) {
              fd.append("files[]", document.getElementById('folderprocess').files[index]);
            }
        }
        $.ajax({
            url: config.routes.createtextfile,
            type:"POST",
            data:{"_token":config.csrf_token,"file":progress_file,"status":"start"},
            success:function(data){}
        });         
        wait(1000);  //1 second in milliseconds
        $('#progress-folder').show();
        fd.append('template_id',template_id);
        fd.append('pdf_page',pdf_page);
        fd.append('folder_name',folder_name);
        fd.append('progress_file',progress_file);
        fd.append('_token',config.csrf_token);
        $.ajax({
            url: config.routes.processpdf,
            type: 'post',
            data: fd,
            contentType: false,
            processData: false,
            beforeSend : function() {
                $('#preview2').html('Processing...');
            },      
            xhr: function() {
                var xhr = $.ajaxSettings.xhr();
                xhr.upload.onprogress = function(e) {
                    console.log(Math.floor(e.loaded / e.total *100) + '%');
                };
                return xhr;
            },
            success: function(response){
                var res = eval('('+response+')');                
                if(res.type == 'Success'){                    
                    $('#preview2').fadeIn().html('Completed');
                    $('#preview2').html("<a href='../"+config.subdomain+"/"+res.dlink+"' target='_blank' style='color: #0056b3;' >Download</a>");
                }
                else if(res.type == 'Duplicates'){                    
                    $('#preview2').html(res.msg+"<br /><input type='button' class='btn btn-primary' data-folder='"+res.folder+"' data-file='"+res.filename+"' data-templateid='"+res.template_id+"' data-pdfpage='"+res.pdf_page+"' data-unids='"+res.unids+"' data-progressfile='"+res.progressfile+"' value='Proceed' id='folder_btn_proceed'>");
                    $('#btn_folder').hide();
                    $('#progress-folder').hide();
                    $('#message-folder').hide();
                    $('#progress-folder').html('');
                    $('#message-folder').html('');
                }
                else if(res.type == 'Over Limit'){                    
                    $('#preview').html(res.msg);
                    $('#btn_folder').hide();
                    $('#progress-folder').hide();
                    $('#message-folder').hide();
                    $('#progress-folder').html('');
                    $('#message-folder').html('');
                    completed(progress_file);
                    return false;
                } 
                else{
                  alert('Error');
                }
            },
            complete: function (data) {
                $('#folder_process')[0].reset(); // this will reset the form fields
            }
        });
        // Refresh the progress bar every 1 second.
        timer = window.setInterval(function(){refreshProgressMulti(progress_file);}, 1000);
    });  

    $(document.body).on('click', "#folder_btn_proceed", function(){    
        var template_id=$(this).data('templateid');
        var pdf_page=$(this).data('pdfpage');        
        var folder=$(this).data('folder'); 
        var file=$(this).data('file'); 
        var unids=$(this).data('unids'); 
        var progressfile=$(this).data('progressfile'); 
        $('#progress-folder').show();
        $('#message-folder').show();
        $.ajax({
            url: config.routes.processpdfagain,
            type: 'post',
            data: {_token:config.csrf_token,folder:folder, file:file, template_id:template_id, pdf_page:pdf_page, unids:unids, progressfile:progressfile},
            beforeSend : function() {
                $('#preview2').html('Processing...');
            }, 
            success: function(response){
                var res = eval('('+response+')');                
                if(res.type == 'Success'){                    
                    $('#preview2').fadeIn().html('Completed');
                    $('#preview2').html("<a href='../"+config.subdomain+"/"+res.dlink+"' target='_blank' style='color: #0056b3;' >Download</a>");
                }
                else{
                  alert('Error');
                }
            }
        }); 
        // Refresh the progress bar every 1 second.
        //timer = window.setInterval(function(){refreshProgressMulti(progressfile);}, 1000); 
    });         
  
    $('#process_close').click(function(){
        //$('#preview').html('');
    });  
    $(document.body).on('change', "#folder-process", function(){    
        var name = $(this).val();
        if(name == "Auto Create"){$("#folderprocess").show();}else{$("#folderprocess").hide();}
    });    
    $(document.body).on('click', "input[name$='img_name']", function(){    
        var img_name = $(this).val();
        $("#image_path").val(img_name);
        pdf.changeImageName(img_name);
    });  
    $(document.body).on('click', "#select_image", function(){    
        img_name=$('#image_path').val();
        $.ajax({
        url: config.routes.imagelist,
        type: 'post',
        data: {_token:config.csrf_token,img_name: img_name},
        success: function(response){ 
          $('#imgModal .modal-body').html(response);
          $('#imgModal').modal({backdrop: 'static', keyboard: false, show: true}); 
        }
        });  
    });  
    
	$(document.body).on('click', "#process", function(){    
		$('.error').hide();          
        $('#preview').html('');
        $('#preview2').html('');
        $('#btn_upload').show();
        $('#btn_folder').show();
        pdf_page = $("#pdf_page").val();
        $('#preview').html('');
        $('#preview2').html('');        
        if(pdf_page=="Single"){
            $('#progress-file').hide('');
            $('#progress-file').html('');
            $('#message-file').html('');
            $('#single-file').html(''); 
            $('#form_process')[0].reset();
            $('#uploadModal').modal({backdrop: 'static', keyboard: false, show: true});
        }
        if(pdf_page=="Multi"){
            $('#progress-folder').hide('');
            $('#progress-folder').html('');
            $('#message-folder').html('');
            $('#multi-file').html('');  
            $("#folderprocess").hide();
            $('#folder_process')[0].reset();
            $('#folderModal').modal({backdrop: 'static', keyboard: false, show: true});
        }
	});	 
    
    $('#RegForm').validate({        
        rules: {
            name: {required: true},
            file: {
                required: function(){
                    return $("#record_id").val() == "0";
                }
            },
            publish_id: {required: true}        
        },
		messages: {
			name: "Please enter a name.",
            file: {
				required: function(){
                    return "Please upload image.";
                }
			},
            publish_id: "Please select active option."
		},
        errorElement: "em",
		errorPlacement: function ( error, element ) {
			// Add the `help-block` class to the error element
			error.addClass( "help-block" );
			if ( element.prop( "type" ) === "checkbox" ) {
				error.insertAfter( element.parent( "label" ) );
			} else {
				error.insertAfter( element );
			}
			if (element.prop( "type" ) === "radio" ) 
			{
				error.insertAfter( element.parents('.myradio') );
			}
			else 
			{ // This is the default behavior 
				error.insertAfter( element );
			}
		},
		highlight: function ( element, errorClass, validClass ) {
			$( element ).parents( ".col-md-6" ).addClass( "has-error" ).removeClass( "has-success" );
			$( element ).parents( ".col-md-12" ).addClass( "has-error" ).removeClass( "has-success" );
		},
		unhighlight: function (element, errorClass, validClass) {
			$( element ).parents( ".col-md-6" ).addClass( "has-success" ).removeClass( "has-error" );
			$( element ).parents( ".col-md-12" ).addClass( "has-success" ).removeClass( "has-error" );
		},
        submitHandler: function (form,event) { 
            event.preventDefault();
            $(".submit").hide();	
            record_id=$("#record_id").val(); 
            name_check=$("#name_check").val(); 
            filename_check=$("#filename_check").val(); 
            if (record_id=='0'){
                url_page=config.routes.imagesave;
            }else{
                url_page=config.routes.imageedit;
            }
            var name = $('#name').val();            
            var publish_id = $('#publish_id').val(); 
            var file_data = $('#file').prop('files')[0]; 
            var form_data = new FormData();                  
            form_data.append('name', name);			
            form_data.append('publish_id', publish_id);			
            form_data.append('file', file_data);	
            form_data.append('record_id', record_id);			
            form_data.append('name_check', name_check);			
            form_data.append('filename_check', filename_check);
            form_data.append('_token',config.csrf_token); 
            $.ajax({
				url: url_page, 
				type: 'post',
				//data: $("#RegForm").serialize(),
                data: form_data,
                contentType: false,
                cache: false,
                processData:false,                
                success: function (response) {
					var res = eval('('+response+')');
					if(res.type == "success")
					{
						$('#modalForm').modal('hide'); 
                        alert(res.message);
                        $(".submit").show();
					}
					else
					{
						alert(res.message);
						$(".submit").show();
					}
					event.preventDefault();
				},
                error: function(e) {
                    console.error(e);
                },
                complete: function (data) {
                    $('#RegForm').val(''); // this will reset the form fields
                }
			});			
			
			
        }
    });    

    // File type validation
    $("#file").change(function() {
        var file = this.files[0];
        var fileType = file.type;
        var match = ['application/pdf', 'application/msword', 'application/vnd.ms-office', 'image/jpeg', 'image/png', 'image/jpg'];
        if(!((fileType == match[3]) || (fileType == match[4]) || (fileType == match[5]))){
            alert('Sorry, only JPG, JPEG, & PNG files are allowed to upload.');
            $("#file").val('');
            return false;
        }
    });     
    
    $("#addData").click(function(){    
		$('.error').hide();
        $("#modal-head").html('Add Image');
        $("#record_id").val('0');
        $("#name").val('');
        $("#file").val('');
        $("#publish_id").val(1);
        $("#name_check").val('');
        $("#filename_check").val('');
        $('#modalForm').modal({backdrop: 'static', keyboard: false, show: true});
	});    
    
	$(document.body).on('click', "#source-link", function(){    
		$('#sourceModal').modal({backdrop: 'static', keyboard: false, show: true});
	});	  
    $(document.body).on('click', ".srName", function(){    
		var name = $(this).val();
        var newtxt = '';
        var chkbx = document.getElementsByClassName('srName');
        //var chkbx = document.getElementsByTagName('input');
        for(var i = 0; i < chkbx.length; i ++) {
            if(chkbx[i].type == 'checkbox' && chkbx[i].checked === true) { 
                if(newtxt.length !== 0) {
                    newtxt += '\n';
                }
                newtxt += chkbx[i].value;
            }
        }
        $("#qr_details").val(newtxt);
        changeQrDetails();
	});	
	$(document.body).on('click', "#pdf_preview", function(){    
		$('#preview_wait').html('');
        template_id=$("#template_id").val();
		pdf_page=$("#pdf_page").val(); 
        if(template_id > 0)
        {
			$.ajax({
				url: config.routes.pdfPreview,
				type: 'post',
				data: {_token:config.csrf_token, template_id:template_id, pdf_page:pdf_page},
				beforeSend : function() {
					$('#preview_wait').html('Wait');
				}, 
				success: function(response){
					var res = eval('('+response+')');                
					if(res.type == 'Success'){                    
						$('#preview_wait').html("<a href='../"+config.subdomain+"/"+res.dlink+"' target='_blank' style='color: white;'>View</a>");
					}
					else{
					  alert(res.dlink);
					}
				}
			});                 
            
        }else{
            alert('Template has not been saved.');
        }        
	});	
    $( function() {
    $( "#sortable" ).sortable();
    $( "#sortable" ).disableSelection();
    } );    
  
});

function changeQrMetaDataPlace()
{
    
    // Get the checkbox 
    var checkBox = document.getElementById("verification_text_show");
    // Get the output
    var metadata_label = document.getElementById("verification_text_label");
    var metadata_value = document.getElementById("verification_text_value");
    // If the checkbox is checked, display the output
    if (checkBox.checked == true){
        metadata_label.style.display = "block";
        metadata_value.style.display = "block";
    } else {
        metadata_label.style.display = "none";
        metadata_value.style.display = "none";
        metadata_label.value='';
        metadata_value.value='';
    }
    var PlacerBc=document.getElementById('verification_text_show');
    if(PlacerBc.checked) {
        BcPlace = PlacerBc.value;
    }else{
        BcPlace = '';
    }
    pdf.changeQrMetaDataPlace(BcPlace,checkBox.checked);
}

function changeQrPlacerMetaLabel()
{
    
    var checkBox = document.getElementById("verification_text_show");
    var PlacerLabel=document.getElementById('verification_text_label').value; 
    // alert(PlacerLabel)
    pdf.changeQrPlacerMetaLabel(PlacerLabel,checkBox.checked);
}

function changeQrPlacerMetaValue()
{
    var PlacerValue=document.getElementById('verification_text_value').value;
    pdf.changeQrPlacerMetaValue(PlacerValue,checkBox.checked);
}
