//for setting fonstsize from canvas to pdf
var pointToPixel = 1.3;
var previousBgId = 0;
previousWidth = $('#template_width').val();
previousHeight = $('#template_height').val();

//template field print with background on change this
$('#background_print').on('change',function(){

	var value = $('#print_with_background').val();
	if(value == '' || value == 0){
		$('#print_with_background').val(1);
		$('#print_with_background').attr('checked');
	}else{
		$('#print_with_background').val(0);
	}
});


//upload data to block chain on change this
$('#is_block_chain_template').on('change',function(){

	var value = $('#is_block_chain_template').val();

	if(value == '0' || value == 0){
		//console.log('A'+value);
		$('#is_block_chain_template').val(1);
		$('#is_block_chain').val(1);
		$('#is_block_chain').attr('checked');
		$('#bcDocumentDescriptionDiv').show();
		$('#bcDocumentTypeDiv').show();
		
	}else{
		//console.log('B'+value);
		$('#is_block_chain_template').val(0);
		$('#is_block_chain').val(0);
		$('#bcDocumentDescriptionDiv').hide();
		$('#bcDocumentTypeDiv').hide();
	}
});

//while refreshing canvas field reset

function resetFields()
{
	
	var myframe = document.getElementById("myframe");


	if($('#lock_index').val() == 'lock'){
		$('#btnlockElement').hide();
		$('#btnunlockElement').show();
	}else{
	 	$('#btnlockElement').show();
	 	$('#btnunlockElement').hide();
	}
	$('#tmp_height').hide();
	$('#tmp_width').hide();
	if($('#template_size').val() == 'Custom'){
		$('#tmp_height').show();
		$('#tmp_width').show();
	}
	// set iframe
	// console.log('reset');

	var h = ($('#template_height').val() * 700) / $('#template_width').val();
	myframe.height = h + 15;

	// Set the Background
	// if(myframe.contentWindow.setBackground()){
		// var dd = $.isFunction(setBackground)
		// if($.fn.setBackground){
			//console.log($('#bg_template_id').val());
			//console.log($('#template_width').val());
			//console.log($('#template_height').val());
		myframe.contentWindow.setBackground($('#bg_template_id').val(), $('#template_width').val(), $('#template_height').val());
		// }else{
		// 	console.log('ddsds');

		// }

	// delay
	for(var i = 0; i < 5000; i++);
	// init some fields
	if($('#field_qr_x').val() === '')
	 	{$('#field_qr_x').val(10); $('#field_qr_y').val(18); $('#field_qr_width').val(32); $('#field_qr_height').val(32);}
	if($('#field_id_y').val() === '')
		{$('#field_id_x').val(143); $('#field_id_y').val(35); $('#field_id_width').val(55); $('#field_id_height').val(12);}
	myframe.contentWindow.postFrameMessage("update", 1, $('#field_qr_x').val(), $('#field_qr_y').val(), $('#field_qr_width').val(), $('#field_qr_height').val(),'unlock','','','','','','','','',$('#field_qr_image').val(),'','','','','','','',$('#field_qr_image_chk').val(),'');
	myframe.contentWindow.postFrameMessage("update", 2, $('#field_id_x').val(), $('#field_id_y').val(), $('#field_id_width').val(), $('#field_id_height').val(),'unlock','','','','','','','','','','','','','','','','','','','','','',$('#field_id_visible').val(),$('#visible_varification').val(),$('#combo_qr_text').val());

	var count = $('[name="field_extra_name[]"]').length;
	//	console.log( $('[name="field_extra_font_case[]"]'));
	
	for(var i = 0; i < count; i++)
	{ 


		if($('[name="field_sample_text[]"]')[i].getAttribute('value') != ''){
			$text = $('[name="field_sample_text[]"]')[i].getAttribute('value');
		}else{
			$text = $('[name="field_extra_name[]"]')[i].getAttribute('value');
		}
		var strposDouble  = $text.indexOf('"');
		var strposSingle  = $text.indexOf("'");
		
		
		if(strposDouble == 0){
		   $text = ""+$text+"";
		}else if(strposSingle == 0){
		   $text = ''+$text+'';
		}
		//console.log($text);
		myframe.contentWindow.postFrameMessage("add", i+3, $('[name="field_extra_x[]"]')[i].getAttribute('value'), 
		    $('[name="field_extra_y[]"]')[i].getAttribute('value'), 
		    $('[name="field_extra_width[]"]')[i].getAttribute('value'), 
		    $('[name="field_extra_height[]"]')[i].getAttribute('value'),
		    $('[name="field_lockIndex[]"]')[i].getAttribute('value'), 
		    $('[name="field_extra_security_type[]"]')[i].getAttribute('value'), 
		    $('[name="field_extra_font_id[]"]')[i].getAttribute('value'), 
		    $('[name="field_extra_font_size[]"]')[i].getAttribute('value') * pointToPixel, 
		    $('[name="field_extra_font_style[]"]')[i].getAttribute('value'), 
		    $('[name="field_extra_font_case[]"]')[i].getAttribute('value'), 
		    $text,
		    $('[name="field_extra_font_color[]"]')[i].getAttribute('value'),
		    $('[name="font_color_extra[]"]')[i].getAttribute('value'), 
		    $('[name="field_extra_text_align[]"]')[i].getAttribute('value'),
		    
		    // send data to template model.php 
		    $('[name="field_image1[]"]')[i].getAttribute('value'),
		    $('#template_id').val(),
		    $('[name="angle[]"]')[i].getAttribute('value'),
		    $('[name="line_gap[]"]')[i].getAttribute('value'),
		    $('[name="length[]"]')[i].getAttribute('value'),
		    $('[name="uv_percentage[]"]')[i].getAttribute('value'),
		    $('[name="is_repeat[]"]')[i].getAttribute('value'),
		    $('[name="infinite_height[]"]')[i].getAttribute('value'),
		    $('[name="include_image[]"]')[i].getAttribute('value'),
		    $('[name="grey_scale[]"]')[i].getAttribute('value'),
		    $('[name="water_mark[]"]')[i].getAttribute('value'),
		    $('[name="is_uv_image[]"]')[i].getAttribute('value'),
		    $('[name="is_transparent_image[]"]')[i].getAttribute('value'),
		    $('[name="text_opicity[]"]')[i].getAttribute('value'),
		    $('[name="visible[]"]')[i].getAttribute('value'),
		    $('[name="visible_varification[]"]')[i].getAttribute('value'),
			$('[name="combo_qr_text[]"]')[i].getAttribute('value'),
			$('[name="is_meta_data[]"]')[i].getAttribute('value'),
			$('[name="field_metadata_label[]"]')[i].getAttribute('value'),
			$('[name="field_metadata_value[]"]')[i].getAttribute('value'),
		false
		);
	}
	myframe.contentWindow.refreshCanvas();
}
$(document).ready(function () {

	// $(document).ready(function(){
	// 	// console.log('hiss');
	// 	setTimeout(function () {resetFields(); }, 1500);
	// })
    if($('#template_width').val() == '')
    	$('#template_width').val(210);
    if($('#template_height').val() == '')
    	$('#template_height').val(297);

    previousBgId = $('#bg_template_id').val();
    previousWidth = $('#template_width').val();
    previousHeight = $('#template_height').val();

	showHeightWidth();
    

    $("#fldInfoDlg").on("hide.bs.modal", function () {
        removeNewField();
    });

    $('#btnModalCancel').click(function (event) {
		event.preventDefault();
		removeNewField();
		$('#fldInfoDlg').modal('hide');
    });

    $('#template_width, #template_height').change(function () {
		if($('#bg_template_id').val() == 0) {
			if($('#template_width').val() < 105) {
				$('#template_width').val(previousWidth);
				toastr["error"]("Can't change the width below 105(mm)!"); 
			} else if($('#template_width').val() > 594) {
				$('#template_width').val(previousWidth);
				toastr["error"]("Can't change the width above 594(mm)!"); 
			} else if($('#template_height').val() < 148) {
				$('#template_height').val(previousHeight);
				toastr["error"]("Can't change the height below 148(mm)!");            
			} else if($('#template_height').val() > 841) {
				$('#template_height').val(previousHeight);
				toastr["error"]("Can't change the width below 841(mm)!");           
			} 
			if(!checkFieldsinside($('#template_width').val(), $('#template_height').val()))
			{
			toastr["error"]("Can't change the height/width as some fields will be outside the boundary!");
			$('#template_width').val(previousWidth);
			$('#template_height').val(previousHeight);  
			return;
		}
      }
      previousWidth = $('#template_width').val();
      previousHeight = $('#template_height').val();

      myframe.contentWindow.setBackground($('#bg_template_id').val(), $('#template_width').val(), $('#template_height').val());
      // set iframe
      // var h = ($('#template_height').val() * 700) / $('#template_width').val();
      var h = $('#template_height').val();
      myframe.height = h + 15;
      updateAllFields(false);
      for(var i = 0; i < 5000; i++);
      myframe.contentWindow.refreshCanvas();
    });
    $('#template_size').change(function(event){

		// var myframe = document.getElementById('myframe');
		var template_size = $('#template_size').val();
		

		if (template_size == 'A3') {
			$('#template_width').val(297);
			$('#template_height').val(420);
			$('#tmp_height').hide();
			$('#tmp_width').hide();
			// $("#certCanvas").attr('height',420);
			// $("#certCanvas").attr('width',297);
			myframe.contentWindow.setBackground($('#bg_template_id').val(), $('#template_width').val(), $('#template_height').val(),'A3');
			var h = ($('#template_height').val() * 700) / $('#template_width').val();
			// var h = $('#template_height').val();
			myframe.height = h + 15;
			updateAllFields(true);
			for(var i = 0; i < 5000; i++);
			myframe.contentWindow.refreshCanvas();

		}else if(template_size == 'A4'){
			$('#template_width').val(210);
			$('#template_height').val(297);
			$('#tmp_height').hide();
			$('#tmp_width').hide();
			myframe.contentWindow.setBackground($('#bg_template_id').val(), $('#template_width').val(), $('#template_height').val(),'A4');
			var h = ($('#template_height').val() * 700) / $('#template_width').val();
			myframe.height = h + 15;
			updateAllFields(true);
			for(var i = 0; i < 5000; i++);
			myframe.contentWindow.refreshCanvas();

		}else if (template_size == 'A5') {

			// console.log(template_size);
			$('#template_width').val(148);
			$('#template_height').val(210);
			$('#tmp_height').hide();
			$('#tmp_width').hide();
			myframe.contentWindow.setBackground($('#bg_template_id').val(), $('#template_width').val(), $('#template_height').val(),'A5');
			var h = ($('#template_height').val() * 700) / $('#template_width').val();
			// var h = $('#template_height').val();
			myframe.height = h + 15;
			updateAllFields(true);
			for(var i = 0; i < 5000; i++);
			myframe.contentWindow.refreshCanvas();

		}else if (template_size == 'Custom') {

			$('#template_width').val(210);
			$('#template_height').val(297);
			$('#tmp_height').show();
			$('#tmp_width').show();
			myframe.contentWindow.setBackground($('#bg_template_id').val(), $('#template_width').val(), $('#template_height').val());
			var h = ($('#template_height').val() * 700) / $('#template_width').val();
			myframe.height = h + 15;
			updateAllFields(true);
			for(var i = 0; i < 5000; i++);
			myframe.contentWindow.refreshCanvas();
		}
		
	})


});


//on right side icon click display typography content
$(function(){
		
	$("#main_tab_sidebar span:eq(0)").css("background","#212529");
	$("#main_tab_sidebar span").css("border-bottom","none");
	$(this).css("border-bottom","none");

 	$("#main_tab_sidebar span").click(function(){

		var pos = $(this).index();

		var ans = $("para tab_p:eq("+pos+")").is(":visible");

		if(ans==false)
		{
			$(".tab_p").fadeOut();
			$("#para .tab_p:eq("+pos+")").fadeIn();
			$("#main_tab_sidebar span").css("background","#333");
			$(this).css({
			'background':'#212529'
			});
		}

 	});

});

//property field changes according to x-y axis
window.updateField = function ($idx, $x='', $y='', $width='', $height='', $security_type='', $font_id='', $font_size='', $font_case='', $text='',$lock_index)
{
	//console.log($x);
	//console.log($y);
	if(localStorage.getItem('defaultvalue') != null){
		var defaultvalue = localStorage.getItem('defaultvalue');


		defaultvalue_json = JSON.parse(defaultvalue);
		str_default = defaultvalue_json.toString();
		$('[name="field_sample_text_width[]"]').val(str_default);

		var vertical_value = localStorage.getItem('vertical_value')

		vertical_value_json = JSON.parse(vertical_value);

		str_vertical = vertical_value_json.toString();

		$('[name="field_sample_text_vertical_width[]"]').val(str_vertical);

		var horizontal_value = localStorage.getItem('horizontal_value')

		horizontal_value_json = JSON.parse(horizontal_value);

		str_horizontal = horizontal_value_json.toString();

		$('[name="field_sample_text_horizontal_width[]"]').val(str_horizontal);
	}
	if(localStorage.getItem('microline_width') != null){

		var microline_width = localStorage.getItem('microline_width');

		microlineWidth_json = JSON.parse(microline_width);
		str_microline_width = microlineWidth_json.toString();
		$('[name="microline_width[]"]').val(str_microline_width);
	}
	window.newField = false;
	if($idx == '')
		return;
	if($idx == 1) {
		if($x !== ''){

			$('#field_qr_x').val(parseInt($x));
		}
		if($y !== '')
			$('#field_qr_y').val(parseInt($y));
	} else if($idx == 2) {
		if($x !== '')
			$('#field_id_x').val(parseInt($x));
		if($y !== '')
			$('#field_id_y').val(parseInt($y));
	} else if($idx > 2) {
		$idx -= 3;


		if($x !== '')
			$('[name="field_extra_x[]"]')[$idx].setAttribute('value', parseInt($x));
		if($y !== '')
			$('[name="field_extra_y[]"]')[$idx].setAttribute('value', parseInt($y));

		if($width !== ''){
			$('#field_extra_width').attr('value',$width);
			$('[name="field_extra_width[]"]')[$idx].setAttribute('value',$width);
		}if($height !== ''){
			$('#field_extra_height').attr('value',$height);
			$('[name="field_extra_height[]"]')[$idx].setAttribute('value',$height);
		}
	}
}

window.showFieldDialog = function ($id,$security_type)
{
	
	if ($security_type == undefined || $security_type == 'undefined') {

		$('#field_dialog').hide();
	}else{
		$('#field_dialog').show();

	}
	var i = $id - 3;

	getFieldPanel($id);
	jscolor();
	$('#extra_color_field').hide();

	//qr upload image hide default
	$('.qr_image_src').hide();
	// set select elements
	if(i >= 0) {
		

		$('#field_extra_security_type').val($('[name="field_extra_security_type[]"]')[i].getAttribute('value'));
		$('#field_extra_font_size').val($('[name="field_extra_font_size[]"]')[i].getAttribute('value'));
		$('#field_extra_font_id').val($('[name="field_extra_font_id[]"]')[i].getAttribute('value'));
		$('#uv_percentage').val($('[name="uv_percentage[]"]')[i].getAttribute('value'));
		$('#text_opicity').val($('[name="text_opicity[]"]')[i].getAttribute('value'));
	}else if(i == -2){
		var field_image_data = $('#field_qr_image').attr('value');


		var template_name = $('#template_name').val();
		if(template_name != ''){

			$('.qr_image_src').attr('src',field_image_data);

		}else{

			$('.qr_image_src').attr('src',field_image_data);
		}
	}

	//micro text repeat click
	$('#is_repeat').click(function(){
		if($(this).prop("checked") == true){
			$('#is_repeat').val(1);
			$('#widthRow').show();
			$('#field_extra_width').val(100);

			var i = $id - 3;
			var field_sample_text = $('#field_sample_text').val();
			var width = $('[name="field_extra_width[]"]')[i].getAttribute('value');
			$('[name="is_repeat[]"]')[i].setAttribute('value',$('#is_repeat').val());
			setValueOnKeyUp($id,$security_type);

		}else if($(this).prop("checked") == false){
			$('#is_repeat').val(0);
			$('#widthRow').hide();
			$('#field_extra_width').val(0);

			var i = $id - 3;
			var field_sample_text = $('#field_sample_text').val();
			var width = $('[name="field_extra_width[]"]')[i].getAttribute('value');
			var angle = $('[name="angle[]"]')[i].getAttribute('value');
			$('[name="is_repeat[]"]')[i].setAttribute('value',$('#is_repeat').val());
			setValueOnKeyUp($id,$security_type);
		}
	})
	if($('#is_repeat').val() == 1){
		$('#is_repeat').attr('checked','checked');
		// $('#field_extra_width').removeAttr('disabled','true');
		// $('#widthRow').show();
	}else{
		// $('#widthRow').hide();
		$('#is_repeat').removeAttr('checked','checked');
		// $('#field_extra_width').attr('disabled','true');
	}

	if($('#include_image').val() == 0){
		$('#image').hide();
		$('#include_image').removeAttr('checked','checked');
		$('#DisplayImage').hide();

	}else{
		$('#DisplayImage').show();
		$('#image').show();
		$('#include_image').attr('checked','checked');

	}

	if($('#visible').val() == 0){
		
		$('#visible').removeAttr('checked','checked');
		
	}else{
		$('#visible').attr('checked','checked');
		
	}

	if($('#field_id_visible1').val() == 0){
		
		$('#field_id_visible1').removeAttr('checked','checked');
		
	}else{
		$('#field_id_visible1').attr('checked','checked');
		
	}
	if($('#visible_varification').val() == 0){
		
		$('#visible_varification').removeAttr('checked','checked');
		
	}else{
		$('#visible_varification').attr('checked','checked');
		
	}

	if($('#field_id_varification1').val() == 0){
		
		$('#field_id_varification1').removeAttr('checked','checked');
		
	}else{
		$('#field_id_varification1').attr('checked','checked');
		
	}


	if($('#field_qr_image_chk1').val() == 1){
		$('.qr_image_src').show();
		$('#field_qr_image_chk1').attr('checked','checked');
		$('#field_qr_image1').removeAttr('disabled','true');
	}else{
		$('.qr_image_src').hide();
		$('#field_qr_image1').attr('disabled','true');
		$('#field_qr_image_chk1').removeAttr('checked','checked');
	}


	if($('#grey_scale').val() == 1){
		$('#grey_scale').attr('checked','checked');

	}else{
		$('#grey_scale').removeAttr('checked','checked');
	}
	$('#water_mark').click(function(){
		if($(this).prop("checked") == true){
			$(this).val(1);
		}else if($(this).prop("checked") == false){
			$(this).val(0);
		}
	})
	$('#visible').click(function(){
		if($(this).prop("checked") == true){
			
			$(this).val(1);
			var i = $id - 3;
			$('[name="visible[]"]')[i].setAttribute('value',$('#visible').val());
		
		}else if($(this).prop("checked") == false){
			$(this).val(0);
			var i = $id - 3;
			$('[name="visible[]"]')[i].setAttribute('value',$('#visible').val());
		}
	})
	$('#field_id_visible1').click(function(){
		if($(this).prop("checked") == true){
			
			$('#field_id_visible').val(1);
			
		}else if($(this).prop("checked") == false){
			$('#field_id_visible').val(0);
		}
	})
	$('#visible_varification').click(function(){
		if($(this).prop("checked") == true){
			
			$(this).val(1);
			var i = $id - 3;
			$('[name="visible_varification[]"]')[i].setAttribute('value',$('#visible_varification').val());
		
		}else if($(this).prop("checked") == false){
			$(this).val(0);
			var i = $id - 3;
			$('[name="visible_varification[]"]')[i].setAttribute('value',$('#visible_varification').val());
		}
	})
	$('#field_id_varification1').click(function(){
		if($(this).prop("checked") == true){
			
			$('#field_id_varification').val(1);
			
		}else if($(this).prop("checked") == false){
			$('#field_id_varification').val(0);
		}
	})
	if($('#water_mark').val() == 1){
		$('#water_mark').attr('checked','checked');

	}else{
		$('#water_mark').removeAttr('checked','checked');
	}
	if($('#is_uv_image').val() == 1){
		$('#is_uv_image').attr('checked','checked');

	}else{
		$('#is_uv_image').removeAttr('checked','checked');
	}
	if($('#is_transparent_image').val() == 1){
		$('#is_transparent_image').attr('checked','checked');

	}else{
		$('#is_transparent_image').removeAttr('checked','checked');
	}
	if($('#field_extra_font_case').val() == 1){
		$('#field_extra_font_case').attr('checked','checked');

	}else{
		$('#field_extra_font_case').removeAttr('checked','checked');
	}

	if($('#is_meta_data').val() == 1){
		$('#is_meta_data').attr('checked','checked');

	}else{
		$('#is_meta_data').removeAttr('checked','checked');
	}
	
	function resetStyle($enabled=true)
	{

		$('.fa-bold').removeClass('btn-primary');
		$('.fa-bold').addClass('btn-default');
		$('.fa-italic').removeClass('btn-primary');
		$('.fa-italic').addClass('btn-default');

		$('#field_extra_font_style').val('');
		if($enabled) {
			$('.fa-bold').removeAttr('disabled');
			$('.fa-italic').removeAttr('disabled');
		} else {
			$('.fa-bold').attr('disabled', 'true');
			$('.fa-bold').attr('disabled', 'true');
			$('.fa-italic').attr('disabled', 'true');
		}
	}

	function resetAlign($enabled=true)
	{

		$('.fa-align-left').addClass('btn-primary');
		$('.fa-align-left').removeClass('btn-default');
		$('.fa-align-center').removeClass('btn-primary');
		$('.fa-align-center').addClass('btn-default');
		$('.fa-align-right').removeClass('btn-primary');
		$('.fa-align-right').addClass('btn-default');
		$('.fa-align-justify').removeClass('btn-primary');
		$('.fa-align-justify').addClass('btn-default');

		$('#field_extra_text_align').val('L');
		if($enabled) {
			$('.fa-align-left').removeAttr('disabled');
			$('.fa-align-center').removeAttr('disabled');
			$('.fa-align-right').removeAttr('disabled');
			$('.fa-align-justify').removeAttr('disabled');
		} else {
			$('.fa-align-left').attr('disabled', 'true');
			$('.fa-align-center').attr('disabled', 'true');
			$('.fa-align-right').attr('disabled', 'true');
			$('.fa-align-justify').attr('disabled', 'true');
		}
	}


	var security_type = $security_type;
	console.log(security_type);
	console.log(window.newField);
	if(window.newField) {
		$('#field_extra_font_color').val('000000');
		$('#lock_index').val('unlock');
		$('#is_uv_image').val(0);
		$('#is_transparent_image').val(0);
		$('#is_meta_data').val(0);
		
		//console.log(security_type);
		if(security_type == 'Normal') {

			// Hide Fields
			$('#UVBlack').hide();
			$('#heightRow').hide();
			$('#Angle').hide();
			$('#gapBetweenLines').hide();
			$('#Length').hide();
			$('#uvPercentage').hide();
			$('#repeatLabel').hide();
			$('#repeatInput').hide();
			$('#image').hide();
			$('#printImage').hide();
			$('#printImageTransparent').hide();
			$('#imageInclude').hide();
			$('#DisplayImage').hide();
			$('#WaterMarkRow').hide();
			$('#fontColorExtra').hide();
			$('#textOpicity').hide();
			$('#fontCase').hide();
			$('#comboQRText').hide();

			// Show fields
			$('#infiniteLabel').show();
			$('#infiniteInput').show();
			$('#Alignment').show();
			$('#fontStyle').show();
			$('#fontName').show();
			$('#fontSize').show();
			$('#fontColor').show();
			$('#widthRow').show();
			$('#sampleText').show();
			$('#visible').show();
			

			$('#angle').val(0);
			$('#line_gap').val(0);
			$('#length').val(0);
			$('#uv_percentage').val(0);
			$('#text_opicity').val(1);
			$('#is_repeat').val(0);
			$('#infinite_height').val(0);
			$('#is_uv_image').val(0);
			$('#is_transparent_image').val(0);
			$('#grey_scale').val(0);
			$('#include_image').val(0);
			$('#water_mark').val(0);
			$('#visible').val(0);
			$('#visible_varification').val(0);
			$('#field_extra_font_case').val(0);

			$('#is_meta_data').val(0);
			
			// Disabled
		}else if(security_type == 'Static Text') {

			// Hide Fields
			$('#UVBlack').hide();
			$('#heightRow').hide();
			$('#Angle').hide();
			$('#gapBetweenLines').hide();
			$('#Length').hide();
			$('#uvPercentage').hide();
			$('#infiniteRepeat').hide();
			$('#image').hide();
			$('#printImage').hide();
			$('#printImageTransparent').hide();
			$('#imageInclude').hide();
			$('#DisplayImage').hide();
			$('#WaterMarkRow').hide();
			$('#fontColorExtra').hide();
			$('#textOpicity').hide();
			$('#fontCase').hide();
			$('#comboQRText').hide();

			// Show Fields
			$('#Alignment').show();
			$('#fontStyle').show();
			$('#fontName').show();
			$('#fontSize').show();
			$('#fontColor').show();
			$('#widthRow').show();
			$('#sampleText').show();
			$('#visible').show();

			$('#angle').val(0);
			$('#line_gap').val(0);
			$('#length').val(0);
			$('#uv_percentage').val(0);
			$('#text_opicity').val(1);
			$('#is_repeat').val(0);
			$('#infinite_height').val(0);
			$('#is_uv_image').val(0);
			$('#is_transparent_image').val(0);
			$('#grey_scale').val(0);
			$('#include_image').val(0);
			$('#water_mark').val(0);
			$('#visible').val(0);
			$('#visible_varification').val(0);
			$('#field_extra_font_case').val(0);
			$('#is_meta_data').val(0);

			// Disabled
		} else if(security_type == 'Micro line') {
			// enabled
			$('#UVBlack').hide();
			$('#fontName').hide();
			$('#fontSize').hide();
			$('#heightRow').hide();
			$('#gapBetweenLines').hide();
			$('#Length').hide();
			$('#uvPercentage').hide();
			$('#infiniteLabel').hide();
			$('#infiniteInput').hide();
			$('#fontColor').hide();
			$('#image').hide();
			$('#printImage').hide();
			$('#printImageTransparent').hide();
			$('#imageInclude').hide();
			$('#DisplayImage').hide();
			$('#WaterMarkRow').hide();
			$('#fontColorExtra').hide();
			$('#fontStyle').hide();
			$('#textOpicity').hide();
			$('#fontCase').hide();
			$('#comboQRText').hide();

			// show
			$('#widthRow').show();
			$('#Alignment').show();
			$('#Angle').show();
			$('#sampleText').show();
			$('#visible').show();

			$('#angle').val(0);
			$('#line_gap').val(0);
			$('#length').val(0);
			$('#uv_percentage').val(0);
			$('#text_opicity').val(1);
			$('#is_repeat').val(0);
			$('#infinite_height').val(0);
			$('#is_uv_image').val(0);
			$('#is_transparent_image').val(0);
			$('#grey_scale').val(0);
			$('#include_image').val(0);
			$('#water_mark').val(0);
			$('#visible').val(0);
			$('#visible_varification').val(0);
			$('#field_extra_font_case').val(0);

			$('#is_meta_data').val(0);
		}
		// styling when security type is microtext border
		else if(security_type == 'Micro Text Border' || security_type == 'Static Microtext Border' ) {

			$('#UVBlack').hide();
			$('#fontColor').hide();
			$('#fontName').hide();
			$('#fontSize').hide();
			$('#Angle').hide();
			$('#gapBetweenLines').hide();
			$('#Length').hide();
			$('#uvPercentage').hide();
			$('#infiniteRepeat').hide();
			$('#image').hide();
			$('#Alignment').hide();
			$('#fontStyle').hide();
			$('#printImage').hide();
			$('#printImageTransparent').hide();
			$('#imageInclude').hide();
			$('#DisplayImage').hide();
			$('#WaterMarkRow').hide();
			$('#fontColorExtra').hide();
			$('#textOpicity').hide();
			$('#fontCase').hide();
			$('#comboQRText').hide();

			// Enabled

			// show
			$('#widthRow').show();
			$('#heightRow').show();
			$('#sampleText').show();
			$('#visible').show();

			$('#angle').val(0);
			$('#line_gap').val(0);
			$('#length').val(0);
			$('#uv_percentage').val(0);
			$('#text_opicity').val(1);
			$('#is_repeat').val(0);
			$('#infinite_height').val(0);
			$('#is_uv_image').val(0);
			$('#is_transparent_image').val(0);
			$('#grey_scale').val(0);
			$('#include_image').val(0);
			$('#water_mark').val(0);
			$('#visible').val(0);
			$('#visible_varification').val(0);
			$('#field_extra_font_case').val(0);

			$('#is_meta_data').val(0);
		}
		else if(security_type == 'Ghost Image') {

			$('#UVBlack').hide();
			$('#fontColor').hide();
			$('#fontName').hide();
			$('#Angle').hide();
			$('#gapBetweenLines').hide();
			$('#uvPercentage').hide();
			$('#infiniteRepeat').hide();
			$('#widthRow').hide();
			$('#heightRow').hide();
			$('#image').hide();
			$('#printImage').hide();
			$('#printImageTransparent').hide();
			$('#imageInclude').hide();
			$('#DisplayImage').hide();
			$('#WaterMarkRow').hide();
			$('#fontColorExtra').hide();
			$('#Alignment').hide();
			$('#textOpicity').hide();
			$('#fontCase').hide();
			$('#comboQRText').hide();

			// show
			$('#Length').show();
			$('#fontSize').show();
			$('#sampleText').show();
			$('#visible').show();

			$('#angle').val(0);
			$('#line_gap').val(0);
			$('#length').val(0);
			$('#uv_percentage').val(0);
			$('#text_opicity').val(1);
			$('#is_repeat').val(0);
			$('#infinite_height').val(0);
			$('#is_uv_image').val(0);
			$('#is_transparent_image').val(0);
			$('#grey_scale').val(0);
			$('#include_image').val(0);
			$('#water_mark').val(0);
			$('#visible').val(0);
			$('#visible_varification').val(0);
			$('#field_extra_font_case').val(0);

			$('#is_meta_data').val(0);

		} else if(security_type == 'Anti-Copy') {

			$('#UVBlack').hide();
			$('#fontName').hide();
			$('#fontSize').hide();
			$('#gapBetweenLines').hide();
			$('#uvPercentage').hide();
			$('#infiniteRepeat').hide();
			$('#widthRow').hide();
			$('#heightRow').hide();
			$('#Length').hide();
			$('#image').hide();
			$('#Angle').hide();
			$('#fontColor').hide();
			$('#printImage').hide();
			$('#printImageTransparent').hide();
			$('#imageInclude').hide();
			$('#DisplayImage').hide();
			$('#WaterMarkRow').hide();
			$('#fontColorExtra').hide();
			$('#Alignment').hide();
			$('#textOpicity').hide();
			$('#fontCase').hide();
			$('#comboQRText').hide();
			
			$('#metadataDiv').hide();
			$('#metadataDetailsDiv').hide();

			// show
			$('#sampleText').show();
			$('#visible').show();

			$('#angle').val(0);
			$('#line_gap').val(0);
			$('#length').val(0);
			$('#uv_percentage').val(0);
			$('#text_opicity').val(1);
			$('#is_repeat').val(0);
			$('#infinite_height').val(0);
			$('#is_uv_image').val(0);
			$('#is_transparent_image').val(0);
			$('#grey_scale').val(0);
			$('#include_image').val(0);
			$('#water_mark').val(0);
			$('#visible').val(0);
			$('#visible_varification').val(0);
			$('#field_extra_font_case').val(0);

		} else if(security_type == '1D Barcode') {

			$('#UVBlack').hide();
			$('#fontName').hide();
			$('#fontSize').hide();
			$('#fontColor').hide();
			$('#widthRow').hide();
			$('#heightRow').hide();
			$('#Angle').hide();
			$('#gapBetweenLines').hide();
			$('#Length').hide();
			$('#uvPercentage').hide();
			$('#infiniteRepeat').hide();
			$('#Alignment').hide();
			$('#fontStyle').hide();
			$('#image').hide();
			$('#printImage').hide();
			$('#printImageTransparent').hide();
			$('#imageInclude').hide();
			$('#DisplayImage').hide();
			$('#WaterMarkRow').hide();
			$('#fontColorExtra').hide();
			$('#textOpicity').hide();
			$('#fontCase').hide();
			$('#comboQRText').hide();

			// show
			$('#sampleText').show();
			$('#visible').show();

			$('#angle').val(0);
			$('#line_gap').val(0);
			$('#length').val(0);
			$('#uv_percentage').val(0);
			$('#text_opicity').val(1);
			$('#is_repeat').val(0);
			$('#infinite_height').val(0);
			$('#is_uv_image').val(0);
			$('#is_transparent_image').val(0);
			$('#grey_scale').val(0);
			$('#include_image').val(0);
			$('#water_mark').val(0);
			$('#visible').val(0);
			$('#visible_varification').val(0);
			$('#field_extra_font_case').val(0);
			$('#is_meta_data').val(0);

		}  else if(security_type == 'Invisible' ) {

			// hide
			
			$('#heightRow').hide();
			$('#fontColor').hide();
			$('#Angle').hide();
			$('#gapBetweenLines').hide();
			$('#Length').hide();
			$('#infiniteRepeat').hide();
			$('#image').hide();
			$('#printImage').hide();
			$('#printImageTransparent').hide();
			$('#imageInclude').hide();
			$('#DisplayImage').hide();
			$('#WaterMarkRow').hide();
			$('#textOpicity').hide();
			$('#fontCase').hide();
			$('#comboQRText').hide();

			// show
			$('#UVBlack').show();
			$('#fontName').show();
			$('#fontSize').show();
			$('#uvPercentage').show();
			$('#Alignment').show();
			$('#fontStyle').show();
			$('#sampleText').show();
			$('#fontColorExtra').show();
			$('#widthRow').show();
			$('#visible').show();

			$('#angle').val(0);
			$('#line_gap').val(0);
			$('#length').val(0);
			$('#uv_percentage').val(0);
			$('#text_opicity').val(1);
			$('#is_repeat').val(0);
			$('#infinite_height').val(0);
			$('#is_uv_image').val(0);
			$('#is_transparent_image').val(0);
			$('#grey_scale').val(0);
			$('#include_image').val(0);
			$('#water_mark').val(0);
			$('#visible').val(0);
			$('#visible_varification').val(0);
			$('#field_extra_font_case').val(0);
			$('#is_meta_data').val(0);
		}
		// styling when security type is uv Repeate line
		else if(security_type == 'UV Repeat line') {

			// hide
			$('#widthRow').hide();
			$('#heightRow').hide();
			$('#fontColor').hide();
			$('#gapBetweenLines').hide();
			$('#Length').hide();
			$('#infiniteRepeat').hide();
			$('#image').hide();
			$('#printImage').hide();
			$('#printImageTransparent').hide();
			$('#imageInclude').hide();
			$('#DisplayImage').hide();
			$('#WaterMarkRow').hide();
			$('#textOpicity').hide();
			$('#fontCase').hide();
			$('#comboQRText').hide();

			// show
			$('#UVBlack').show();
			$('#fontName').show();
			$('#fontSize').show();
			$('#uvPercentage').show();
			$('#Alignment').show();
			$('#fontStyle').show();
			$('#Angle').show();
			$('#sampleText').show();
			$('#fontColorExtra').show();
			$('#visible').show();

			$('#angle').val(0);
			$('#line_gap').val(0);
			$('#length').val(0);
			$('#uv_percentage').val(0);
			$('#text_opicity').val(1);
			$('#is_repeat').val(0);
			$('#infinite_height').val(0);
			$('#is_uv_image').val(0);
			$('#is_transparent_image').val(0);
			$('#grey_scale').val(0);
			$('#include_image').val(0);
			$('#water_mark').val(0);
			$('#visible').val(0);
			$('#visible_varification').val(0);
			$('#field_extra_font_case').val(0);
			$('#is_meta_data').val(0);
		}
		else if(security_type == 'Security line') {
			console.log(12222222);
			// hide
			$('#fontName').show();

			$('#widthRow').hide();
			$('#heightRow').hide();
			$('#gapBetweenLines').hide();
			$('#Length').hide();
			$('#infiniteRepeat').hide();
			$('#uvPercentage').hide();
			$('#image').hide();
			$('#printImage').hide();
			$('#printImageTransparent').hide();
			$('#imageInclude').hide();
			$('#DisplayImage').hide();
			$('#WaterMarkRow').hide();
			$('#fontColorExtra').hide();
			$('#comboQRText').hide();

			$('#fontStyle').show();
			$('#fontColor').show();
			$('#fontCase').show();
			// show
			$('#UVBlack').show();
			$('#Alignment').show();
			$('#Angle').show();
			$('#sampleText').show();
			$('#fontSize').show();
			$('#textOpicity').show();
			$('#visible').show();

			$('#angle').val(0);
			$('#line_gap').val(0);
			$('#length').val(0);
			$('#uv_percentage').val(0);
			$('#text_opicity').val(0.3);
			$('#is_repeat').val(0);
			$('#infinite_height').val(0);
			$('#is_uv_image').val(0);
			$('#is_transparent_image').val(0);
			$('#grey_scale').val(0);
			$('#include_image').val(0);
			$('#water_mark').val(0);
			$('#visible').val(0);
			$('#visible_varification').val(0);
			$('#field_extra_font_case').val(1);

			$('#is_meta_data').val(0);
		}
		// styling when security type is Dynamic Image Or Static Image
		else if(security_type == 'Dynamic Image' || security_type == 'Static Image' || security_type == 'Invisible Image'){
			$('#fontName').hide();
			$('#fontSize').hide();
			$('#fontColor').hide();
			$('#widthRow').hide();
			$('#heightRow').hide();
			$('#Angle').hide();
			$('#gapBetweenLines').hide();
			$('#Length').hide();
			$('#uvPercentage').hide();
			$('#infiniteRepeat').hide();
			$('#UVBlack').hide();
			$('#Alignment').hide();
			$('#fontStyle').hide();
			$('#sampleText').hide();
			$('#imageInclude').hide();
			$('#fontColorExtra').hide();
			$('#textOpicity').hide();
			$('#fontCase').hide();
			$('#comboQRText').hide();

			// show
			$('#image').show();
			$('#printImage').show();
			$('#printImageTransparent').show();
			$('#DisplayImage').show();
			$('#WaterMarkRow').hide();
			$('#visible').hide();

			$('#angle').val(0);
			$('#line_gap').val(0);
			$('#length').val(0);
			$('#uv_percentage').val(0);
			$('#text_opicity').val(1);
			$('#is_repeat').val(0);
			$('#infinite_height').val(0);
			$('#is_uv_image').val(0);
			$('#is_transparent_image').val(0);
			$('#grey_scale').val(1);
			$('#include_image').val(0);
			$('#water_mark').val(0);
			$('#visible').val(0);
			$('#visible_varification').val(0);
			$('#field_extra_font_case').val(0);


		}else if(security_type == 'Qr Code'){

			$('#fontName').hide();
			$('#fontSize').hide();
			$('#fontColor').hide();
			$('#Angle').hide();
			$('#gapBetweenLines').hide();
			$('#Length').hide();
			$('#uvPercentage').hide();
			$('#infiniteRepeat').hide();
			$('#UVBlack').hide();
			$('#Alignment').hide();
			$('#fontStyle').hide();
			$('#sampleText').show();
			$('#printImage').hide();
			$('#printImageTransparent').hide();
			$('#DisplayImage').hide();
			$('#WaterMarkRow').hide();
			$('#fontColorExtra').hide();
			$('#widthRow').hide();
			$('#heightRow').hide();
			$('#textOpicity').hide();
			$('#fontCase').hide();
			
			// show

			$('#image').show();
			$('#imageInclude').show();
			$('#visible').show();
			$('#comboQRText').show();

			$('#angle').val(0);
			$('#line_gap').val(0);
			$('#length').val(0);
			$('#uv_percentage').val(0);
			$('#text_opicity').val(1);
			$('#is_repeat').val(0);
			$('#infinite_height').val(0);
			$('#is_uv_image').val(0);
			$('#is_transparent_image').val(0);
			$('#grey_scale').val(0);
			$('#include_image').val(0);
			$('#water_mark').val(0);
			$('#visible').val(0);
			$('#visible_varification').val(0);
			$('#field_extra_font_case').val(0);
			
			$('#is_meta_data').val(0);

			/*$('#combo_qr_text').val('');*/
		}else if(security_type == 'UV Repeat Fullpage') {
			// Enabled
			$('#widthRow').hide();
			$('#heightRow').hide();
			$('#fontColor').hide();
			$('#Angle').hide();
			$('#Length').hide();
			$('#infiniteRepeat').hide();
			$('#image').hide();
			$('#printImage').hide();
			$('#printImageTransparent').hide();
			$('#imageInclude').hide();
			$('#DisplayImage').hide();
			$('#WaterMarkRow').hide();
			$('#Alignment').hide();
			$('#textOpicity').hide();
			$('#fontCase').hide();
			$('#comboQRText').hide();
			// show
			$('#sampleText').show();
			$('#uvPercentage').show();
			$('#gapBetweenLines').show();
			$('#UVBlack').show();
			$('#fontName').show();
			$('#fontSize').show();
			$('#fontStyle').show();
			$('#fontColorExtra').show();
			$('#visible').show();

			$('#angle').val(0);
			$('#line_gap').val(0);
			$('#length').val(0);
			$('#uv_percentage').val(0);
			$('#text_opicity').val(1);
			$('#is_repeat').val(0);
			$('#infinite_height').val(0);
			$('#is_uv_image').val(0);
			$('#is_transparent_image').val(0);
			$('#grey_scale').val(0);
			$('#include_image').val(0);
			$('#water_mark').val(0);
			$('#visible').val(0);
			$('#visible_varification').val(0);
			$('#field_extra_font_case').val(0);
			
			$('#is_meta_data').val(0);
		}
		else if(security_type == '2D Barcode') {

			$('#fontName').hide();
			$('#fontSize').hide();
			$('#fontColor').hide();
			$('#Angle').hide();
			$('#gapBetweenLines').hide();
			$('#Length').hide();
			$('#uvPercentage').hide();
			$('#infiniteRepeat').hide();
			$('#UVBlack').hide();
			$('#Alignment').hide();
			$('#fontStyle').hide();
			$('#image').hide();
			$('#printImage').hide();
			$('#printImageTransparent').hide();
			$('#imageInclude').hide();
			$('#DisplayImage').hide();
			$('#WaterMarkRow').hide();
			$('#fontColorExtra').hide();
			$('#widthRow').hide();
			$('#heightRow').hide();
			$('#textOpicity').hide();
			$('#fontCase').hide();
			$('#comboQRText').hide();
			// show
			$('#sampleText').show();
			$('#visible').show();


			$('#angle').val(0);
			$('#line_gap').val(0);
			$('#length').val(0);
			$('#uv_percentage').val(0);
			$('#text_opicity').val(1);
			$('#is_repeat').val(0);
			$('#infinite_height').val(0);
			$('#is_uv_image').val(0);
			$('#is_transparent_image').val(0);
			$('#grey_scale').val(0);
			$('#include_image').val(0);
			$('#water_mark').val(0);
			$('#visible').val(0);
			$('#visible_varification').val(0);
			$('#field_extra_font_case').val(0);
			$('#is_meta_data').val(0);
		}

		var security_type = $('#field_extra_security_type').val();

		$('#myFieldInfo').text('Add Field');
		$('#field_extra_height').hide();
		$('#field_extra_width').hide();
		$('#field_extra_height').val($('#field_extra_font_size').val());

	}
	else {


		$('#myFieldInfo').text('Update Field');
		if(security_type == 'Normal'){
			// Hide Fields
			$('#UVBlack').hide();
			$('#heightRow').hide();
			$('#Angle').hide();
			$('#gapBetweenLines').hide();
			$('#Length').hide();
			$('#uvPercentage').hide();
			$('#repeatLabel').hide();
			$('#repeatInput').hide();
			$('#image').hide();
			$('#printImage').hide();
			$('#printImageTransparent').hide();
			$('#imageInclude').hide();
			$('#DisplayImage').hide();
			$('#WaterMarkRow').hide();
			$('#fontColorExtra').hide();
			$('#textOpicity').hide();
			$('#fontCase').hide();
			$('#comboQRText').hide();

			// Show fields
			$('#infiniteLabel').show();
			$('#infiniteInput').show();
			$('#Alignment').show();
			$('#fontStyle').show();
			$('#fontName').show();
			$('#fontSize').show();
			$('#fontColor').show();
			$('#widthRow').show();
			$('#sampleText').show();
			$('#visible').show();

			
				
		}else if(security_type == 'Florescent' || security_type == 'Static Text' ) {

			// Hide Fields
			$('#UVBlack').hide();
			$('#heightRow').hide();
			$('#Angle').hide();
			$('#gapBetweenLines').hide();
			$('#Length').hide();
			$('#uvPercentage').hide();
			$('#infiniteRepeat').hide();
			$('#image').hide();
			$('#printImage').hide();
			$('#printImageTransparent').hide();
			$('#imageInclude').hide();
			$('#DisplayImage').hide();
			$('#WaterMarkRow').hide();
			$('#fontColorExtra').hide();
			$('#textOpicity').hide();
			$('#fontCase').hide();
			$('#comboQRText').hide();

			// Show Fields
			$('#Alignment').show();
			$('#fontStyle').show();
			$('#fontName').show();
			$('#fontSize').show();
			$('#fontColor').show();
			$('#widthRow').show();
			$('#sampleText').show();
			$('#visible').show();

		}else if(security_type == 'Micro Text Border' || security_type == 'Static Microtext Border'){

			$('#UVBlack').hide();
			$('#fontColor').hide();
			$('#fontName').hide();
			$('#fontSize').hide();
			$('#Angle').hide();
			$('#gapBetweenLines').hide();
			$('#Length').hide();
			$('#uvPercentage').hide();
			$('#infiniteRepeat').hide();
			$('#image').hide();
			$('#Alignment').hide();
			$('#fontStyle').hide();
			$('#printImage').hide();
			$('#printImageTransparent').hide();
			$('#imageInclude').hide();
			$('#DisplayImage').hide();
			$('#WaterMarkRow').hide();
			$('#fontColorExtra').hide();
			$('#textOpicity').hide();
			$('#fontCase').hide();
			$('#comboQRText').hide();
			// Enabled

			// show
			$('#widthRow').show();
			$('#heightRow').show();
			$('#sampleText').show();
			$('#visible').show();

		}else if(security_type == 'Invisible'){


			// hide
			
			$('#heightRow').hide();
			$('#fontColor').hide();
			$('#Angle').hide();
			$('#gapBetweenLines').hide();
			$('#Length').hide();
			$('#infiniteRepeat').hide();
			$('#image').hide();
			$('#printImage').hide();
			$('#printImageTransparent').hide();
			$('#imageInclude').hide();
			$('#DisplayImage').hide();
			$('#WaterMarkRow').hide();
			$('#textOpicity').hide();
			$('#fontCase').hide();
			$('#comboQRText').hide();
			// show
			$('#UVBlack').show();
			$('#fontName').show();
			$('#fontSize').show();
			$('#uvPercentage').show();
			$('#Alignment').show();
			$('#fontStyle').show();
			$('#sampleText').show();
			$('#fontColorExtra').show();
			$('#widthRow').show();
			$('#visible').show();

		}else if(security_type == 'Ghost Image') {

			$('#UVBlack').hide();
			$('#fontColor').hide();
			$('#fontName').hide();
			$('#Angle').hide();
			$('#gapBetweenLines').hide();
			$('#uvPercentage').hide();
			$('#infiniteRepeat').hide();
			$('#widthRow').hide();
			$('#heightRow').hide();
			$('#image').hide();
			$('#printImage').hide();
			$('#printImageTransparent').hide();
			$('#imageInclude').hide();
			$('#DisplayImage').hide();
			$('#WaterMarkRow').hide();
			$('#fontColorExtra').hide();
			$('#Alignment').hide();
			$('#fontStyle').hide();
			$('#textOpicity').hide();
			$('#fontCase').hide();
			$('#comboQRText').hide();
			// show
			$('#Length').show();
			$('#fontSize').show();
			$('#sampleText').show();
			$('#visible').show();
			resetAlign();
			resetStyle();
		}
		else if(security_type == 'Anti-Copy') {

			$('#UVBlack').hide();
			$('#fontName').hide();
			$('#fontSize').hide();
			$('#gapBetweenLines').hide();
			$('#uvPercentage').hide();
			$('#infiniteRepeat').hide();
			$('#widthRow').hide();
			$('#heightRow').hide();
			$('#Length').hide();
			$('#image').hide();
			$('#Angle').hide();
			$('#fontColor').hide();
			$('#printImage').hide();
			$('#printImageTransparent').hide();
			$('#imageInclude').hide();
			$('#DisplayImage').hide();
			$('#WaterMarkRow').hide();
			$('#fontColorExtra').hide();
			$('#Alignment').hide();
			$('#fontStyle').hide();
			$('#sampleText').hide();
			$('#textOpicity').hide();
			$('#fontCase').hide();
			$('#comboQRText').hide();

			$('#visible').show();

			// show
			resetAlign();
			resetStyle();
		}
		else if(security_type == '1D Barcode') {


			
			$('#UVBlack').hide();
			$('#fontName').hide();
			$('#fontSize').hide();
			$('#fontColor').hide();
			$('#widthRow').hide();
			$('#heightRow').hide();
			$('#Angle').hide();
			$('#gapBetweenLines').hide();
			$('#Length').hide();
			$('#uvPercentage').hide();
			$('#infiniteRepeat').hide();
			$('#Alignment').hide();
			$('#fontStyle').hide();
			$('#image').hide();
			$('#printImage').hide();
			$('#printImageTransparent').hide();
			$('#imageInclude').hide();
			$('#DisplayImage').hide();
			$('#WaterMarkRow').hide();
			$('#fontColorExtra').hide();
			$('#textOpicity').hide();
			$('#fontCase').hide();
			$('#comboQRText').hide();

			// show
			$('#sampleText').show();
			$('#visible').show();
			resetAlign();
			resetStyle();
		}
		// Style when security type is UV Repeat line,Dynamic Image,Static Image
		else if(security_type == 'Micro line'){
			
			$('#UVBlack').hide();
			$('#fontName').hide();
			$('#fontSize').hide();
			$('#heightRow').hide();
			$('#gapBetweenLines').hide();
			$('#Length').hide();
			$('#uvPercentage').hide();
			$('#infiniteLabel').hide();
			$('#infiniteInput').hide();
			$('#fontColor').hide();
			$('#image').hide();
			$('#printImage').hide();
			$('#printImageTransparent').hide();
			$('#imageInclude').hide();
			$('#DisplayImage').hide();
			$('#WaterMarkRow').hide();
			$('#fontColorExtra').hide();
			$('#textOpicity').hide();
			$('#fontCase').hide();
			$('#comboQRText').hide();
			// show
			$('#widthRow').show();
			$('#Alignment').show();
			$('#fontStyle').show();
			$('#Angle').show();
			$('#sampleText').show();
			$('#visible').show();

		}else if(security_type == 'Security line'){

			$('#fontName').show();

			$('#widthRow').hide();
			$('#heightRow').hide();
			$('#gapBetweenLines').hide();
			$('#Length').hide();
			$('#infiniteRepeat').hide();
			$('#uvPercentage').hide();
			$('#image').hide();
			$('#printImage').hide();
			$('#printImageTransparent').hide();
			$('#imageInclude').hide();
			$('#DisplayImage').hide();
			$('#WaterMarkRow').hide();
			$('#fontColorExtra').hide();
			$('#fontCase').show();
			$('#comboQRText').hide();
			// show
			$('#fontColor').show();
			$('#UVBlack').show();
			$('#Alignment').show();
			$('#fontStyle').show();
			$('#Angle').show();
			$('#sampleText').show();
			$('#fontSize').show();
			$('#textOpicity').show();
			$('#visible').show();

		}else if(security_type == 'UV Repeat line'){

			$('#widthRow').hide();
			$('#heightRow').hide();
			$('#fontColor').hide();
			$('#gapBetweenLines').hide();
			$('#Length').hide();
			$('#infiniteRepeat').hide();
			$('#image').hide();
			$('#printImage').hide();
			$('#printImageTransparent').hide();
			$('#imageInclude').hide();
			$('#DisplayImage').hide();
			$('#WaterMarkRow').hide();
			$('#textOpicity').hide();
			$('#fontCase').hide();
			$('#comboQRText').hide();
			// show
			$('#UVBlack').show();
			$('#fontName').show();
			$('#fontSize').show();
			$('#uvPercentage').show();
			$('#Alignment').show();
			$('#fontStyle').show();
			$('#Angle').show();
			$('#sampleText').show();
			$('#fontColorExtra').show();
			$('#visible').show();

		}else if(security_type == 'UV Repeat Fullpage'){

			$('#widthRow').hide();
			$('#heightRow').hide();
			$('#fontColor').hide();
			$('#Angle').hide();
			$('#Length').hide();
			$('#infiniteRepeat').hide();
			$('#image').hide();
			$('#printImage').hide();
			$('#printImageTransparent').hide();
			$('#imageInclude').hide();
			$('#DisplayImage').hide();
			$('#WaterMarkRow').hide();
			$('#textOpicity').hide();
			$('#fontCase').hide();
			$('#comboQRText').hide();
			// show
			$('#sampleText').show();
			$('#uvPercentage').show();
			$('#gapBetweenLines').show();
			$('#UVBlack').show();
			$('#fontName').show();
			$('#fontSize').show();
			$('#Alignment').show();
			$('#fontStyle').show();
			$('#fontColorExtra').show();
			$('#visible').show();
		}
		else if(security_type == 'Dynamic Image' || security_type == 'Static Image' || security_type == 'Invisible Image'){

			$('#fontName').hide();
			$('#fontSize').hide();
			$('#fontColor').hide();
			$('#widthRow').hide();
			/*$('#heightRow').hide();*/
			$('#Angle').hide();
			$('#gapBetweenLines').hide();
			$('#Length').hide();
			$('#uvPercentage').hide();
			$('#infiniteRepeat').hide();
			$('#UVBlack').hide();
			$('#Alignment').hide();
			$('#fontStyle').hide();
			$('#sampleText').hide();
			$('#imageInclude').hide();
			$('#fontColorExtra').hide();
			$('#textOpicity').hide();
			$('#fontCase').hide();
			$('#comboQRText').hide();
			// show
			$('#image').show();
			$('#printImage').show();
			$('#printImageTransparent').show();
			$('#DisplayImage').show();
			$('#visible').show();

			// $('#WaterMarkRow').show();
			var field_image_data = $('#field_image').attr('value');
			
			var template_id = $('#template_id').val();
			if(template_name != ''){

				$('.uploaded_image').attr('src',canvas_upload_path+'/templates/'+template_id+'/'+field_image_data+'');

			}else{

				$('.uploaded_image').attr('src',canvas_upload_path+'/customImages/'+field_image_data+'');
			}
       		$('.uploaded_image').show();
		}else if(security_type == 'Qr Code'){

			$('#fontName').hide();
			$('#fontSize').hide();
			$('#fontColor').hide();
			$('#Angle').hide();
			$('#gapBetweenLines').hide();
			$('#Length').hide();
			$('#uvPercentage').hide();
			$('#infiniteRepeat').hide();
			$('#UVBlack').hide();
			$('#Alignment').hide();
			$('#fontStyle').hide();
			$('#sampleText').show();
			$('#printImage').hide();
			$('#printImageTransparent').hide();
			$('#WaterMarkRow').hide();
			$('#fontColorExtra').hide();
			$('#textOpicity').hide();
			$('#fontCase').hide();

			// show
			$('#widthRow').show();
			$('#heightRow').show();
			$('#image').show();
			$('#imageInclude').show();
			$('#DisplayImage').show();
			$('#visible').show();
			$('#comboQRText').show();

			var field_image_data = $('#field_image').attr('value');


			$('.uploaded_image').attr('src',field_image_data);
     		$('.uploaded_image').show();
		}
		else if(security_type == '2D Barcode')
		{
			$('#fontName').hide();
			$('#fontSize').hide();
			$('#fontColor').hide();
			$('#Angle').hide();
			$('#gapBetweenLines').hide();
			$('#Length').hide();
			$('#uvPercentage').hide();
			$('#infiniteRepeat').hide();
			$('#UVBlack').hide();
			$('#Alignment').hide();
			$('#fontStyle').hide();
			$('#image').hide();
			$('#printImage').hide();
			$('#printImageTransparent').hide();
			$('#imageInclude').hide();
			$('#DisplayImage').hide();
			$('#WaterMarkRow').hide();
			$('#fontColorExtra').hide();
			$('#textOpicity').hide();
			$('#fontCase').hide();
			$('#comboQRText').hide();
			// show
			$('#sampleText').show();
			$('#widthRow').show();
			$('#heightRow').show();
			$('#labelSampleText').text('Sample Text');
			$('.uploaded_image').hide();
			$('#visible').show();
			resetAlign();
			resetStyle();
		}


	}


	//for tempalte style call function for any keyboard event
	keyboardEvents($id,$security_type,i);


	//on barcode image change
	barcodeImageChange();

	//on dynamic/static image change
	dynamicStaticImageChange(i,$id,$security_type);
}

function getFieldPanel($id=1, $ratio)
{
	$('#deleteCurrent').show();
	$('#copyCurrent').show();
    var str = '<input type="hidden" id="field_id" value="'+$id+'" />';
    if($id == 1){
        str += '<tr>';
         
        str += '<tr>';
        str+= '<td style="width:33%">Security Type :</td>';
        str+= '<td>Qr code</td>';
        str += '</tr>';
        str += '<tr>';
        str += '<td>Add Image</td>';
        str += '<td><input type="checkbox" name="field_qr_image_chk1" id="field_qr_image_chk1" value="'+$('#field_qr_image_chk').val()+'"></td>';
		str += '</tr>';
		str += '<tr>';
		str += '<td style="width:10%;">X</td>';
		str += '<td style="width:30%;"><input type="text"  onkeypress="return isNumberKey(event)" style="width:100%;" class="custom_text" id="field_qr_x1" name="field_qr_x1" maxlength="10" value="'+$('#field_qr_x').val()+'"></td>';
		str += '<td style="width:10%;">Y</td>';
		str += '<td style="width:30%;"><input type="text"  onkeypress="return isNumberKey(event)" style="width:100%;" class="custom_text" id="field_qr_y1" name="field_qr_y1" maxlength="10" value="'+$('#field_qr_y').val()+'"></td>';
		str += '</tr>';
		str += '<tr style="display:none">';
		str += '<td>Width</td>'; 
		str += '<td><input type="text" style="width:100%;"  onkeypress="return isNumberKey(event)" class="custom_text" id="field_qr_width1" name="field_qr_width1" maxlength="10" value="'+$('#field_qr_width').val()+'"></td>';
		str += '<td>Height</td>'; 
		str += '<td><input type="text" style="width:100%;"  onkeypress="return isNumberKey(event)" class="custom_text" id="field_qr_height1" name="field_qr_height1" maxlength="10" value="'+$('#field_qr_height').val()+'"></td>';
		str += '</tr>';
		str += '<tr>';
		
		str += '<div class="range-slider">';
		  str += '<td colspan="1">Size</td>';
		  str += '<td colspan="2"><input type="range" class="range-slider__range"  min="10" max="35" value="'+$('#field_qr_height').val()+'" /></td>';
		  str += ' <td class="range-slider__value">'+$('#field_qr_height').val()+'</td>';
		str += '</div>';   
		str += '</tr>';
		str += '<tr>';
		str += '<td >Sample Text</td>';
		str += '<td colspan="3"><input type="text" style="width:100%;" class="custom_text" id="field_qr_sample_text1" name="field_qr_sample_text1" maxlength="100" class="form-control" value="'+$('#field_qr_sample_text').val()+'"></td>'; 
		str += '</tr>';
		str += '<tr>';
		str += '<td >Combo QR Text</td>';
		str += '<td colspan="3"><textarea type="text" style="width:100%;" class="custom_text" id="field_qr_combo_qr_text1" name="field_qr_combo_qr_text1">'+$('#field_qr_combo_qr_text').val()+'</textarea></td>'; 
		str += '</tr>';
		str += '<tr>';
		str += '<td>Image</td>';
		str += '<td colspan="3"><input type="file" id="field_qr_image1" name="field_qr_image1" class="form-control" value="'+ $('#field_qr_image').val() +'"></td>';
		str += '</tr>';
		str += '<tr>';
		str += '<td colspan="4"><label id="qr_image_error" class="error"></label></td>';
		str += '</tr>';
		str += '<tr>';
		str += '<td><img src="" class="qr_image_src" height="50" width="50" alt="uploading Image">';
		str += '</tr>';

		str += '<input type="hidden" id="field_qr_lockIndex1" name="field_qr_lockIndex1" value="'+$('#field_qr_lockIndex').val()+'">';
    }else if($id == 2){
		  str += '<tr>';
		str += '<tr>';
		str+= '<td style="width:33%">Security Type : </td>';
		str+= '<td>ID Barcode</td>';
		str += '</tr>';
		str += '<tr>';
		str+= '<td>Do not print:</td>';
		str += '<td><input type="checkbox" name="field_id_visible1" id="field_id_visible1" value="'+$('#field_id_visible').val()+'"></td>';
		str+= '<td>Do not display on verification</td>';
		str += '<td><input type="checkbox" name="field_id_varification1" id="field_id_varification1" value="'+$('#field_id_varification').val()+'"></td>';
		str += '</tr>';
		str += '<tr>';
		str += '<td>X</td>';
		str += '<td><input type="text" style="width:100%;"  onkeypress="return isNumberKey(event)" class="custom_text" id="field_id_x1" name="field_id_x1" maxlength="10" value="'+$('#field_id_x').val()+'"></td>';
		str += '<td>Y</td>';
		str += '<td><input type="text" style="width:100%;"  onkeypress="return isNumberKey(event)" class="custom_text" id="field_id_y1" name="field_id_y1" maxlength="10" value="'+$('#field_id_y').val()+'"></td>';
		str += '</tr>';
		str += '<tr>';
		str += '<td>Width</td>'; 
		str += '<td><input type="text" style="width:100%;"  onkeypress="return isNumberKey(event)" class="custom_text" id="field_id_width1" name="field_id_width1" maxlength="10" value="'+$('#field_id_width').val()+'"></td>';
		str += '<td>Height</td>'; 
		str += '<td><input type="text" style="width:100%;"  onkeypress="return isNumberKey(event)" class="custom_text" id="field_id_height1" name="field_id_height1" maxlength="10" value="'+$('#field_id_height').val()+'"></td>';
		str += '</tr>';
		str += '<input type="hidden" id="field_id_lockIndex"  onkeypress="return isNumberKey(event)" name="field_id_lockIndex" value="'+$('#field_id_lockIndex').val()+'">';
    }else if($id > 2){
		var i = $id - 3;

		style        = $('[name="field_extra_font_style[]"]')[i].getAttribute('value'),
		style_bold   = 'btn-default', style_italic = 'btn-default',
		justify      = $('[name="field_extra_text_align[]"]')[i].getAttribute('value'),
		justify_left = 'btn-primary', justify_center = 'btn-default', justify_right = 'btn-default',justify_justify='btn-default';

		if(style == 'B') {
		style_bold = 'btn-primary';
		}
		else if(style == 'BI') {
			style_bold = 'btn-primary';
			style_italic = 'btn-primary';
		} if(style == 'I')
			style_italic = 'btn-primary';

		if(justify == 'L') {
			justify_left = 'btn-primary';
			justify_center = 'btn-default';
			justify_right = 'btn-default';
			justify_justify = 'btn-default';
		}
		else if(justify == 'C') {
			justify_left = 'btn-default';
			justify_center = 'btn-primary';
			justify_justify = 'btn-default';
			justify_right = 'btn-default';
		}
		else if(justify == 'R') {
			justify_left = 'btn-default';
			justify_center = 'btn-default';
			justify_justify = 'btn-default';
			justify_right = 'btn-primary';
		}
		else if(justify == 'J') {
			justify_left = 'btn-default';
			justify_center = 'btn-default';
			justify_right = 'btn-default';
			justify_justify = 'btn-primary';
		}
      	
    	$('#field_extra_text_align').val(justify);

    	if($('[name="field_extra_security_type[]"]')[i].getAttribute('value') == 'Normal'){

    		var securityType = 'Dynamic Text';
    	}else{
    		var securityType = $('[name="field_extra_security_type[]"]')[i].getAttribute('value');
    	}
    	
		
		str += '<tr>';
		str += '<tr>';
		str+= '<td style="width:33%">Security Type : </td>';
		str+= '<td><input type="hidden" id="field_extra_security_type" name="field_extra_security_type" value="'+$('[name="field_extra_security_type[]"]')[i].getAttribute('value')+'"></td>';
		str+= '<td>'+securityType+'</td>';
		str += '</tr>';
		str += '<tr id="infiniteRepeat">';
		str += '<td style="width:20%;" id="infiniteLabel">Infinite</td>';
		str += '<td style="width:20%;" id="infiniteInput"><input type="checkbox" name="infinite_height" id="infinite_height" value="'+$('[name="infinite_height[]"]')[i].getAttribute('value')+'" style="width:100%;"></td>';  
		str += '<td style="width:20%;" id="repeatLabel">Repeat</td>';
		str += '<td style="width:20%;" id="repeatInput"><input type="checkbox" name="is_repeat" id="is_repeat" value="'+$('[name="is_repeat[]"]')[i].getAttribute('value')+'" style="width:100%;"></td>';
		str += '</tr>';
		str += '<tr id="imageInclude">';
		str += '<td id="include_image_label">Add Image</td>';
	    str += '<td><input type="checkbox" name="include_image" id="include_image" value="'+$('[name="include_image[]"]')[i].getAttribute('value')+'"></td>';
		str += '</tr>';
		str += '<tr>';
		str += '<td colspan="2" id="visible_label">Do not print</td>';
	    str += '<td colspan="2"><input type="checkbox" name="visible" id="visible" value="'+$('[name="visible[]"]')[i].getAttribute('value')+'"></td>';
		str += '</tr>';
	    
		str += '<tr>';
		str += '<td colspan="2" id="visible_varification_label">Do not display on verification PDF</td>';
	    str += '<td colspan="2"><input type="checkbox" name="visible_varification" id="visible_varification" value="'+$('[name="visible_varification[]"]')[i].getAttribute('value')+'"></td>';
		str += '</tr>';
		str += '<tr id="printImage">';
		str += '<td style="width:20%;" id="printInColorLabel">Print in color</td>';
		str += '<td style="width:20%;" id="printInColorInput"><input type="checkbox" name="grey_scale" id="grey_scale" value="'+$('[name="grey_scale[]"]')[i].getAttribute('value')+'" style="width:100%;"></td>';  
		str += '<td style="width:20%;" id="isUvImageLabel">Is UV image</td>';
		str += '<td style="width:20%;" id="isUvImageInput"><input type="checkbox" name="is_uv_image" id="is_uv_image" value="'+$('[name="is_uv_image[]"]')[i].getAttribute('value')+'" style="width:100%;"></td>';
		str += '</tr>';
		str += '<tr id="printImageTransparent">';
		str += '<td  colspan="2" id="isTransparentImageLabel">Is Transparent image</td>';
		str += '<td  colspan="2" id="isTransparentImageInput"><input type="checkbox" name="is_transparent_image" id="is_transparent_image" value="'+$('[name="is_transparent_image[]"]')[i].getAttribute('value')+'" style="width:100%;"></td>';
		str += '</tr>';
		// str += '<tr id="WaterMarkRow">';
		// str += '<td style="width:20%;" id="isWatermarkImageLabel">Watermark</td>';
		// str += '<td style="width:20%;" id="isWatermarkImageInput"><input type="checkbox" name="water_mark" id="water_mark" value="'+$('[name="water_mark[]"]')[i].getAttribute('value')+'" style="width:100%;"></td>';
		// str += '</tr>';

		str += '<tr id="Alignment">';
		str += '<td>Alignment</td>';
		str += '<td></td>';
		str += '<td>';
		  str += '<div class="btn-group">';
		    str += '<button type="button" width="10%" class="btn '+justify_left+' fa fa-align-left" title="Left-align"> </button>';
		    str += '<button type=b"utton" class="btn '+justify_center+' fa fa-align-center" title="Center-align"> </button>';
		    str += '<button type="button" class="btn '+justify_right+' fa fa-align-right" title="Right-align"> </button>';
		    str += '<button type="button" class="btn '+justify_justify+' fa fa-align-justify" title="Justify-align"> </button>';
		    
		    str += '<input type="hidden" class="talign" id="field_extra_text_align" name="field_extra_text_align" value="'+$('[name="field_extra_text_align[]"]')[i].getAttribute('value')+'">';
		  str += '</div>'; 
		str+='</td>';

		str += '</tr>';
		str += '<tr id="fontStyle">';
		str += '<td>Font Style</td>';
		str += '<td></td>';
		str += '<td>';
		 str += '<div class="btn-group">';     
		    str += '<button type="button" class="btn '+style_bold+' fa fa-bold" title="Bold"> </button>';
		    str += '<button type="button" class="btn '+style_italic+' fa fa-italic" title="Italic"> </button>';
		     str += '<input type="hidden" class="fstyle" id="field_extra_font_style" name="field_extra_font_style" value="'+$('[name="field_extra_font_style[]"]')[i].getAttribute('value')+'">';
		   
		  str += '</div>';
		str += '</td>';
		str += '</tr>';
		str += '<tr>';
		str += '<td colspan="2">X</td>';
		str += '<td colspan="2"><input type="text"  onkeypress="return isNumberKey(event)" style="width:100%;" class="custom_text" id="field_extra_x" name="field_extra_x" maxlength="10" value="'+ $('[name="field_extra_x[]"]')[i].getAttribute('value') +'"></td>';
		str += '</tr>';
		str += '<tr>';
		str += '<td colspan="2">Y</td>';
		str += '<td colspan="2"><input type="text"  onkeypress="return isNumberKey(event)" style="width:100%;" class="custom_text" id="field_extra_y" name="field_extra_y" maxlength="10" value="'+ $('[name="field_extra_y[]"]')[i].getAttribute('value') +'"></td>';
		str += '</tr>';
		str += '<tr id="widthRow">';
		str += '<td colspan="2">Width</td>'; 
		str += '<td colspan="2"><input type="text"  onkeypress="return isNumberKey(event)" style="width:100%;" class="custom_text" id="field_extra_width" name="field_extra_width" maxlength="10" value="'+ $('[name="field_extra_width[]"]')[i].getAttribute('value') +'"></td>';
		str += '</tr>';
		str += '<tr id="heightRow">';
		str += '<td colspan="2">Height</td>'; 
		str += '<td colspan="2"><input type="text"  onkeypress="return isNumberKey(event)" style="width:100%;" class="custom_text" id="field_extra_height" name="field_extra_height" maxlength="10" value="'+ $('[name="field_extra_height[]"]')[i].getAttribute('value') +'"></td>';
		str += '</tr>';
		str += '<tr id="fieldName">';
		str += '<td colspan="2">Field Name</td>';
		str += '<td colspan="2"><input type="text" style="width:100%;" class="custom_text" id="field_extra_name" name="field_extra_name" maxlength="70" value="'+$('[name="field_extra_name[]"]')[i].getAttribute('value')+'"></td>';  
		str += '</tr>';
		str += '<tr id="fontName">';
		str += '<td colspan="2">Font Name</td>';
		str += '<td colspan="2">';
		str += '<select class="custom_select" id="field_extra_font_id" name="field_extra_font_id">';
        str += font_style;
        str += '</select>';   
		str += '</td>';  
		str += '</tr>';
		str += '<tr id="fontSize">';
		str += '<td colspan="2">Font Size</td>';
		str += '<td colspan="2">';
		str += '<select class="custom_select" id="field_extra_font_size" name="field_extra_font_size" >';
        str += font_size;
        str += '</select>';
		str += '</td>';
		str += '</tr>';
		str += '<tr id="fontColor">';
		str += '<td colspan="2">Font Color</td>'; 
		str += '<td colspan="2"><input type="text" class="form-control jscolor {zIndex:9999}" id="field_extra_font_color" name="field_extra_font_color" maxlength="6" value="'+ $('[name="field_extra_font_color[]"]')[i].getAttribute('value') +'">';
		str += '</tr>';

		str += '<tr id="fontCase">';
		str += '<td  colspan="2" id="isFontCaseAllCapsLabel">All-CAPS</td>';
		str += '<td  colspan="2" id="isFontCaseAllCapsInput"><input type="checkbox" name="field_extra_font_case" id="field_extra_font_case" value="'+$('[name="field_extra_font_case[]"]')[i].getAttribute('value')+'" style="width:100%;" checked></td>';
		str += '</tr>';

		str += '</tr>';
		str += '<tr id="fontColorExtra">';
		str += '<td colspan="2">Font Color</td>'; 
		str += '<td colspan="2">';
		  str += '<select class="custom_select" id="field_extra_font_color_dr" name="font_color_extra">';
		    if($('[name="font_color_extra[]"]')[i].getAttribute('value') == '000000')
		    {
		      str += '<option value="000000">Black</option>';
		      str += '<option value="FFFF00">Yellow</option>';  
		    }else{

		      str += '<option value="FFFF00">Yellow</option>';  
		      str += '<option value="000000">Black</option>'; 
		    }
		    str += '</select>';
		str += '</td>';
		str += '</tr>';

		str += '<tr id="sampleText">';
		str += '<td colspan="2">Sample Text</td>';
		
		var text = $('[name="field_sample_text[]"]')[i].getAttribute('value');
    	var strposDouble  = text.indexOf('"');
		//console.log("strposDouble");
		//console.log(strposDouble);
		if(strposDouble >= 0){
			text = text.replace(/"/g, '&quot;');
			
		   str += '<td colspan="2"><input type="text" style="width:100%;" class="custom_text" id="field_sample_text" name="field_sample_text"  value="'+text+'"></td>'; 
		}else{
		   str += '<td colspan="2"><input type="text" style="width:100%;" class="custom_text" id="field_sample_text" name="field_sample_text"  value="'+text+'"></td>'; 
		}
		
		str += '</tr>';
		str += '<tr id="comboQRText">';
		str += '<td colspan="2">Combo QR Text</td>';
		
		var text = $('[name="combo_qr_text[]"]')[i].getAttribute('value');
		/*if(text!=""&&text!=null){
			var strposDouble  = text.indexOf('"');
		}else{
			strposDouble=-1;
		}
    	
		console.log("strposDouble");
		console.log(strposDouble);
		if(strposDouble >= 0){
			text = text.replace(/"/g, '&quot;');
			
		   str += '<td colspan="2"><textarea type="text" style="width:100%;" class="custom_text" id="combo_qr_text" name="combo_qr_text">'+text+'</textarea></td>'; 
		}else{*/
		   str += '<td colspan="2"><textarea type="text" style="width:100%;" class="custom_text" id="combo_qr_text" name="combo_qr_text">'+text+'</textarea></td>'; 
	/*	}*/
		
		str += '</tr>';

		str += '<tr id="Angle">';
		str += '<td colspan="2">Angle</td>'; 
		str += '<td colspan="2"><input type="text" style="width:100%;" class="custom_text" id="angle" name="angle" maxlength="3" value="'+ $('[name="angle[]"]')[i].getAttribute('value') +'"></td>';  
		str += '</tr>';
		str += '<tr id="gapBetweenLines">';
		str += '<td colspan="2">Gap between lines</td>'; 
		str += '<td colspan="2"><input type="text"  onkeypress="return isNumberKey(event)" style="width:100%;" class="custom_text" id="line_gap" name="line_gap" maxlength="2" value="'+ $('[name="line_gap[]"]')[i].getAttribute('value') +'"></td>';  
		str += '</tr>';
		str += '<tr id="Length">';
		str += '<td colspan="2">length</td>'; 
		str += '<td>';
		str += '<input type="text" class="custom_text" id="length" name="length" maxlength="2"value="'+ $('[name="length[]"]')[i].getAttribute('value') +'">';
		str += '</td>';
		str += '</tr>';
		str += '<tr id="uvPercentage">';
		str += '<td colspan="2">Uv percentage</td>';
		str += '<td colspan="2">';
		str += '<select class="custom_select"  id="uv_percentage" name="uv_percentage" value="'+ $('[name="uv_percentage[]"]')[i].getAttribute('value') +'">';
		str += uv_percentage_value;
		str += '</select>'; 
		str += '</td>';
		str += '</tr>';
		str += '<tr id="textOpicity">';
		str += '<td colspan="2">Opicity</td>';
		str += '<td colspan="2">';
		str += '<select class="custom_select"  id="text_opicity" name="text_opicity" value="'+ $('[name="text_opicity[]"]')[i].getAttribute('value') +'">';
		str += opacity;
		str += '</select>'; 
		str += '</td>';
		str += '</tr>';
		str += '<tr id="image">';
		str += '<td colspan="2">Image</td>';
		str += '<td colspan="2">';
		str += '<input type="file" id="field_image" name="field_image" class="form-control"  value="'+ $('[name="field_image1[]"]')[i].getAttribute('value') +'">'; 
		str += '</td>';
		str += '</tr>';
		str += '<tr id="DisplayImage">';
		  str += '<td colspan="2">';
		str += '<img src="" class="uploaded_image" height="50" width="50" alt="uploading Image">';

		str += '</td>';
		str += '</tr>';
		/* Block Chain */
		str += '<tr id="metadataDiv">';
		str += '<td colspan="2" id="is_meta_label">Use For Block Chain Metadata</td>';
	    str += '<td colspan="2"><input type="checkbox" name="is_meta_data" id="is_meta_data" value="'+$('[name="is_meta_data[]"]')[i].getAttribute('value')+'"></td>';
		str += '</tr>';
		var is_meta_data = $('[name="is_meta_data[]"]')[i].getAttribute('value');

		if(is_meta_data==1){
			//console.log('Metadata '+is_meta_data);
			var meta_data_display="";
		}else{
			var meta_data_display="display:none;";
		}
		str += '<tr id="metadataDetailsDiv" style="'+meta_data_display+'">';
		var text = $('[name="field_metadata_label[]"]')[i].getAttribute('value');
		str += '<td colspan="2"><input type="text" style="width:100%;" class="custom_text" id="field_metadata_label" name="field_metadata_label"  value="'+text+'" placeholder="Enter Metadata Label" maxlength="100"></td>';
		
		var text = $('[name="field_metadata_value[]"]')[i].getAttribute('value');
    	str += '<td colspan="2"><input type="text" style="width:100%;" class="custom_text" id="field_metadata_value" name="field_metadata_value"  value="'+text+'" placeholder="Enter Metadata Value" maxlength="256"></td>'; 
		str += '</tr>';
		/* End Block Chain */

		str += '<tr>';
		str += '<td colspan="4"><label id="error_message" class="error"></label></td>';
		str += '</tr>';
		str += '<input type="hidden" id="field_lockIndex" name="field_lockIndex" value="'+ $('[name="field_lockIndex[]"]')[i].getAttribute('value') +'">'; 
        

    }
      $('#field_dialog').html(str);
}

//on x-y axis number changes
function isNumberKey(evt)
{
    var charCode = (evt.which) ? evt.which : event.keyCode
    if (charCode > 31 && (charCode < 48 || charCode > 57))
        return false;

    return true;
}

//update field
function updateAllFields($needsRefresh = true) {
	var myframe = document.getElementById('myframe');
	myframe.contentWindow.postFrameMessage("update", 1, $('#field_qr_x').val(), $('#field_qr_y').val(), $('#field_qr_width').val(), $('#field_qr_height').val(),$('#field_qr_lockIndex').val());
	myframe.contentWindow.postFrameMessage("update", 2, $('#field_id_x').val(), $('#field_id_y').val(), $('#field_id_width').val(), $('#field_id_height').val(),$('#field_id_lockIndex').val());

	var count = $('[name="field_extra_name[]"]').length;
	for(var i = 0; i < count; i++) 
	{
		myframe.contentWindow.postFrameMessage("update", i+3, 
		$('[name="field_extra_x[]"]')[i].getAttribute('value'), 
		$('[name="field_extra_y[]"]')[i].getAttribute('value'), 
		$('[name="field_extra_width[]"]')[i].getAttribute('value'), 
		$('[name="field_extra_height[]"]')[i].getAttribute('value')
		);
	}
	if($needsRefresh)
		myframe.contentWindow.refreshCanvas();
}

//on copy click call copy elemnt function
$('#copyCurrent').click(function(){
	var id = $('#field_id').val();
	if(id == undefined){
		toastr["error"]("Please select any element for copy"); 
	}else{
		window.duplicateField(id);
	}
});


//for copy element
window.duplicateField = function($idx){

    var idx = $('#field_id').val();

    var i = $idx -3;

    var field_extra_x = Number($('[name="field_extra_x[]"]')[i].getAttribute('value')) + 20;
    var field_extra_y = Number($('[name="field_extra_y[]"]')[i].getAttribute('value')) + 20;
    var field_extra_width = $('[name="field_extra_width[]"]')[i].getAttribute('value');
    var field_extra_height = $('[name="field_extra_height[]"]')[i].getAttribute('value');
    var field_extra_security_type = $('[name="field_extra_security_type[]"]')[i].getAttribute('value');
    var field_extra_font_id = $('[name="field_extra_font_id[]"]')[i].getAttribute('value');
    var field_extra_font_size = $('[name="field_extra_font_size[]"]')[i].getAttribute('value');
    var field_extra_font_style = $('[name="field_extra_font_style[]"]')[i].getAttribute('value');
    var field_extra_font_case = $('[name="field_extra_font_case[]"]')[i].getAttribute('value');
    var field_extra_name = $('[name="field_extra_name[]"]')[i].getAttribute('value');
    var field_sample_text = $('[name="field_sample_text[]"]')[i].getAttribute('value');
    var field_extra_font_color = $('[name="field_extra_font_color[]"]')[i].getAttribute('value');
    var font_color_extra = $('[name="font_color_extra[]"]')[i].getAttribute('value');
    var field_extra_text_align = $('[name="field_extra_text_align[]"]')[i].getAttribute('value');
    var field_image = $('[name="field_image1[]"]')[i].getAttribute('value');
    var angle = $('[name="angle[]"]')[i].getAttribute('value');
    var line_gap = $('[name="line_gap[]"]')[i].getAttribute('value');
    var field_lockIndex = $('[name="field_lockIndex[]"]')[i].getAttribute('value');
    var length = $('[name="length[]"]')[i].getAttribute('value');
    var uv_percentage = $('[name="uv_percentage[]"]')[i].getAttribute('value');
    var is_repeat = $('[name="is_repeat[]"]')[i].getAttribute('value');
    var infinite_height = $('[name="infinite_height[]"]')[i].getAttribute('value');
    var include_image = $('[name="include_image[]"]')[i].getAttribute('value');
    var grey_scale = $('[name="grey_scale[]"]')[i].getAttribute('value');
    var water_mark = $('[name="water_mark[]"]')[i].getAttribute('value');
    var is_uv_image = $('[name="is_uv_image[]"]')[i].getAttribute('value');
    var is_transparent_image = $('[name="is_transparent_image[]"]')[i].getAttribute('value');
    var visible = $('[name="visible[]"]')[i].getAttribute('value');
    var visible_varification = $('[name="visible_varification[]"]')[i].getAttribute('value');
    var combo_qr_text = $('[name="combo_qr_text[]"]')[i].getAttribute('value');

    /*For Block Chain*/
    var is_meta_data = $('[name="is_meta_data[]"]')[i].getAttribute('value');
    var field_metadata_label = $('[name="field_metadata_label[]"]')[i].getAttribute('value');
    var field_metadata_value = $('[name="field_metadata_value[]"]')[i].getAttribute('value');
    /*End Block Chain*/

    var div = $("<div />");

    div.html(GetField());
    div.addClass('extrafields');
    $('#additional_field').append(div);

    var newId = $('[name="field_extra_name[]"]').length - 1;


    $('[name="field_extra_x[]"]')[newId].setAttribute('value',field_extra_x);
    $('[name="field_extra_y[]"]')[newId].setAttribute('value',field_extra_y);
    $('[name="field_extra_width[]"]')[newId].setAttribute('value',field_extra_width);
    $('[name="field_extra_height[]"]')[newId].setAttribute('value',field_extra_height);
    $('[name="field_lockIndex[]"]')[newId].setAttribute('value',field_lockIndex);
    $('[name="field_extra_security_type[]"]')[newId].setAttribute('value',field_extra_security_type);
    $('[name="field_extra_font_id[]"]')[newId].setAttribute('value',field_extra_font_id);
    $('[name="field_extra_font_size[]"]')[newId].setAttribute('value',field_extra_font_size);
    $('[name="field_extra_font_style[]"]')[newId].setAttribute('value',field_extra_font_style);
    $('[name="field_extra_font_case[]"]')[newId].setAttribute('value',field_extra_font_case);
    $('[name="field_extra_name[]"]')[newId].setAttribute('value',field_extra_name);
    $('[name="field_sample_text[]"]')[newId].setAttribute('value',field_sample_text);
    $('[name="field_extra_font_color[]"]')[newId].setAttribute('value',field_extra_font_color);
    $('[name="font_color_extra[]"]')[newId].setAttribute('value',font_color_extra);
    $('[name="field_extra_text_align[]"]')[newId].setAttribute('value',field_extra_text_align);
    $('[name="field_image1[]"]')[newId].setAttribute('value',field_image);
    $('[name="angle[]"]')[newId].setAttribute('value',angle);
    $('[name="line_gap[]"]')[newId].setAttribute('value',line_gap);
    $('[name="length[]"]')[newId].setAttribute('value',length);
    $('[name="uv_percentage[]"]')[newId].setAttribute('value',uv_percentage);
    $('[name="is_repeat[]"]')[newId].setAttribute('value',is_repeat);
    $('[name="infinite_height[]"]')[newId].setAttribute('value',infinite_height);
    $('[name="include_image[]"]')[newId].setAttribute('value',include_image);
    $('[name="grey_scale[]"]')[newId].setAttribute('value',grey_scale);
    $('[name="water_mark[]"]')[newId].setAttribute('value',water_mark);
    $('[name="is_uv_image[]"]')[newId].setAttribute('value',is_uv_image);
    $('[name="is_transparent_image[]"]')[newId].setAttribute('value',is_transparent_image);
    $('[name="visible[]"]')[newId].setAttribute('value',visible);
    $('[name="visible_varification[]"]')[newId].setAttribute('value',visible_varification);
    $('[name="combo_qr_text[]"]')[newId].setAttribute('value',combo_qr_text);

    /*For Block Chain*/
    $('[name="is_meta_data[]"]')[newId].setAttribute('value',is_meta_data);
    $('[name="field_metadata_label[]"]')[newId].setAttribute('value',field_metadata_label);
    $('[name="field_metadata_value[]"]')[newId].setAttribute('value',field_metadata_value);
    /*End Block Chain*/

    myframe.contentWindow.postFrameMessage("add", i + 4,
        field_extra_x,
        field_extra_y,
        field_extra_width,
        field_extra_height,
        field_lockIndex,
        field_extra_security_type,
        field_extra_font_id,
        field_extra_font_size * pointToPixel,
        field_extra_font_style,
        field_extra_font_case,
        field_sample_text,
        field_extra_font_color,
        font_color_extra,
        field_extra_text_align,
        
        field_image,
        $("#template_id").val(),
        angle,
        line_gap,
        length,
        uv_percentage,
        is_repeat,
        infinite_height,
        include_image,
        grey_scale,
        water_mark,
        is_uv_image,
        is_transparent_image,
        visible,
        visible_varification,
        combo_qr_text,
        is_meta_data,
        field_metadata_label,
        field_metadata_value,
         true
    );

}

//on delete click call delete element function
$('#deleteCurrent').click(function(){

      // console.log(ctrlArray);
      var id = $('#field_id').val();
	
       if(id == undefined){
                      
            var ctrlId =  $('#field_id_ctrl').val();
            console.log(ctrlId);
          // $('input:hidden[name=field_id_ctrl[]]').val();
          
          if(ctrlId != undefined){
		console.log('here');
            var search = ctrlId.search(',');
            if(search < 1){
		
                window.removeField(ctrlId,'str');
            }else{


              function sortNumber(a, b) {
                return a - b;
              }
              
              var split = ctrlId.split(',');
              var sort = split.sort(sortNumber);
              var reverse = sort.reverse();
              console.log(reverse);
              window.removeField(reverse,'array');
            }
          }else{
	     console.log('errr');
            toastr["error"]("Please select any element for delete"); 
          }
      }else{
        window.removeField(id);
      }
    
    });

//for delete element
window.removeField = function ($idx,type){
	
	if(type == 'array'){
		bootbox.confirm("Are you sure you want to remove this field?", function(result){
			
			$.each($idx,function(k,v){
				// console.log(v);
				var i = v - 3;
				// console.log(i);
				var myframe = document.getElementById("myframe");
				if(window.newField) {
					$('.extrafields')[i].remove();
					myframe.contentWindow.postFrameMessage("remove", v);
					return;
				}
				$('.extrafields')[i].remove();
				myframe.contentWindow.postFrameMessage("remove", v);
			})
		});
	}else{

		var i = $idx - 3,
		len = $('.extrafields').length;

		if(i >= len) {
			return;
		}
		var myframe = document.getElementById("myframe");
		if(window.newField) {
			$('.extrafields')[i].remove();
			myframe.contentWindow.postFrameMessage("remove", $idx);
			return;
		}

		bootbox.confirm("Are you sure you want to remove this field?", function(result){
			if(result){
				$('.extrafields')[i].remove();
				myframe.contentWindow.postFrameMessage("remove", $idx);
			}
		});
	}
}
