//dynamic text click add text
$('#dynamic_text').click(function(){

	addUpdateField('Normal');
});
//static text click add text
$('#static_text').click(function(){

	addUpdateField('Static Text');
});
//dynamic image click add image
$('#dynamic_image').click(function(){

	addUpdateField('Dynamic Image');
});
//static image click
$('#static_image').click(function(){
	addUpdateField('Static Image');
});
//on microtext border click
$('#microtext_border').click(function(){
	addUpdateField('Micro Text Border');
});
//on micro text click
$('#microtext').click(function(){
	addUpdateField('Micro line');
});
//on ghost image click
$('#ghost_image').click(function(){
	addUpdateField('Ghost Image');
});
//on void pantograph click
$('#void_pantograph').click(function(){
	addUpdateField('Anti-Copy');
});
//on security line click
$('#security_line').click(function(){
	addUpdateField('Security line');
});
//on uv repeat line click
$('#uv_repeat_line').click(function(){
	addUpdateField('UV Repeat line');
});
//on invisible click
$('#invisible').click(function(){
	addUpdateField('Invisible');
});
//on uv repeat click
$('#uv_repeat_fullpage').click(function(){
    addUpdateField('UV Repeat Fullpage');
});
//on qr code click
$('#qr_code').click(function(){
	addUpdateField('Qr Code');
});
//on 1d barcode click
$('#1d_barcode').click(function(){
	addUpdateField('1D Barcode');
});
//on 2d barcode click
$('#2d_barcode').click(function(){
	addUpdateField('2D Barcode');
});

function addUpdateField(security_type){
	var pointToPixel = 1.3;

	var myframe = document.getElementById("myframe");

	myframe.contentWindow.postFrameMessage("add", '', 50, 50, 0, 0,'unlock', 'Normal', 1, 10, '', "Dummy Text", "000000", 'L');
	window.top.addField(50, 50, 0, 0, 'Normal', 1, 10, "Dummy Text",'unlock');
	var idx = $('#field_id').val();
	window.newField = false;
	var i = idx - 3;

	var field_extra_x = 50;
	var field_extra_y = 50;
	var field_lockIndex = 'unlock';
	var field_extra_font_color = "000000";
	var field_extra_security_type = security_type;
	var field_extra_font_id = 1;
	// var field_extra_font_id = 0;
	var field_extra_text_align = "L";
	var uv_percentage = 15;
	var is_repeat = 0;
	var infinite_height = 0;
	var include_image = 0;
	var water_mark = 0;
	var is_uv_image = 0;
	var is_transparent_image = 0;
	var visible = 0;
	var visible_varification = 0;
	var field_extra_font_case = 0;
	var combo_qr_text = '';
	if(security_type == 'Normal'){

		var field_extra_name = 'Dynamic Text';
		var text = "Dynamic Text";
		var font_color_extra = "none";
		var field_extra_width = 30;
		var field_extra_height = 0;
		var field_extra_font_size = 10;
		var field_extra_font_style = "";
		var angle = 0;
		var line_gap = 0;
		var length = 0;
		var text_opicity = 1;
		var grey_scale = 0;
		var field_image = 'null';

	}else if(security_type == 'Static Text'){

		var field_extra_name = 'Static Text';
		var text = "Static Text";
		var font_color_extra = "none";
		var field_extra_width = 30;
		var field_extra_height = 0;
		var field_extra_font_size = 10;
		var field_extra_font_style = "";
		var angle = 0;
		var line_gap = 0;
		var length = 0;
		var text_opicity = 1;
		var grey_scale = 0;
		var field_image = 'null';
	}else if(security_type == 'Dynamic Image'){

		var field_extra_name = 'Dynamic Image';
		var text = "Dynamic Image";
		var font_color_extra = "none";
		var field_extra_width = 100;
		var field_extra_height = 100;
		var field_extra_font_size = 10;
		var field_extra_font_style = "";
		var angle = 0;
		var line_gap = 0;
		var length = 0;
		var text_opicity = 1;
		var grey_scale = 1;
		var field_image = default_image;
	}else if(security_type == 'Static Image'){

		var field_extra_name = 'Static Image';
		var text = "Static Text";
		var font_color_extra = "none";
		var field_extra_width = 100;
		var field_extra_height = 100;
		var field_extra_font_size = 10;
		var field_extra_font_style = "";
		var angle = 0;
		var line_gap = 0;
		var length = 0;
		var text_opicity = 1;
		var grey_scale = 1;
		
		var field_image = default_image;
	}else if(security_type == 'Micro Text Border'){

		var field_extra_name = 'Micro Text Border';
		var text = "Micro Text Border";
		var font_color_extra = "none";
		var field_extra_width = 100;
		var field_extra_height = 100;
		var field_extra_font_size = 1;
		var field_extra_font_style = "";
		var angle = 0;
		var line_gap = 0;
		var length = 0;
		var text_opicity = 1;
		var grey_scale = 0;
	}else if(security_type == 'Micro line'){

		var field_extra_name = 'Micro line';
		var text = "Micro line";
		var font_color_extra = "none";
		var field_extra_width = 30;
		var field_extra_height = 0;
		var field_extra_font_size = 1;
		var field_extra_font_style = "";
		var angle = 0;
		var line_gap = 0;
		var length = 0;
		var text_opicity = 1;
		var grey_scale = 0;
		var field_image = 'null';
	}else if(security_type == 'Ghost Image'){

		var field_extra_name = 'Ghost Image';
		var text = "Ghost Image";
		var font_color_extra = "none";
		var field_extra_width = 30;
		var field_extra_height = 0;
		var field_extra_font_size = 10;
		var field_extra_font_style = "";
		var angle = 0;
		var line_gap = 0;
		var length = 6;
		var text_opicity = 1;
		var grey_scale = 0;
		var field_image = 'null';
	}else if(security_type == 'Anti-Copy'){

		var field_extra_name = 'Anticopy';
		var text = "Anticopy";
		var font_color_extra = "none";
		var field_extra_width = 30;
		var field_extra_height = 0;
		var field_extra_font_size = 10;
		var field_extra_font_style = "";
		var angle = 0;
		var line_gap = 0;
		var length = 0;
		var text_opicity = 1;
		var grey_scale = 0;
		var field_image = 'null';
	}else if(security_type == 'Security line'){

		var field_extra_name = 'Security line';
		var text = "Security line";
		var font_color_extra = "none";
		var field_extra_width = 30;
		var field_extra_height = 0;
		var field_extra_font_size = 10;
		var field_extra_font_style = "";
		var angle = 0;
		var line_gap = 0;
		var length = 0;
		var text_opicity = 0.3;
		var grey_scale = 0;
		var field_image = 'null';
		var field_extra_font_case = 1;
	}else if(security_type == 'UV Repeat line'){

		var field_extra_name = 'UV Repeat line';
		var text = "Dummy Text";
		var font_color_extra = "FFFF00";
		var field_extra_width = 30;
		var field_extra_height = 0;
		var field_extra_font_size = 10;
		var field_extra_font_style = "";
		var angle = 0;
		var line_gap = 0;
		var length = 0;
		var text_opicity = 1;
		var grey_scale = 0;
		var field_image = 'null';
	}else if(security_type == 'Invisible'){

		var field_extra_name = 'Invisible';
		var text = "Dummy Text";
		var font_color_extra = "FFFF00";
		var field_extra_width = 30;
		var field_extra_height = 0;
		var field_extra_font_size = 10;
		var field_extra_font_style = "";
		var angle = 0;
		var line_gap = 0;
		var length = 0;
		var text_opicity = 1;
		var grey_scale = 0;
		var field_image = 'null';
	}else if(security_type == 'UV Repeat Fullpage'){

		var field_extra_name = 'Dummy Text';
		var text = "Dummy Text";
		var font_color_extra = "FFFF00";
		var field_extra_width = 30;
		var field_extra_height = 0;
		var field_extra_font_size = 10;
		var field_extra_font_style = "";
		var angle = 45;
		var line_gap = 15;
		var length = 0;
		var text_opicity = 1;
		var grey_scale = 0;
		var field_image = 'null';

	}else if(security_type == '1D Barcode'){

		var field_extra_name = 'Dummy Text';
		var text = "Dummy Text";
		var font_color_extra = "none";
		var field_extra_width = 55;
		var field_extra_height = 12;
		var field_extra_font_size = 10;
		var field_extra_font_style = "";
		var angle = 0;
		var line_gap = 0;
		var length = 0;
		var text_opicity = 1;
		var grey_scale = 0;
		var field_image = 'null';

	}else if(security_type == '2D Barcode'){

		var field_extra_name = 'Dummy Text';
		var text = "Dummy Text";
		var font_color_extra = "none";
		var field_extra_width = 55;
		var field_extra_height = 55;
		var field_extra_font_size = 10;
		var field_extra_font_style = "";
		var angle = 0;
		var line_gap = 0;
		var length = 0;
		var text_opicity = 1;
		var grey_scale = 0;
		var field_image = 'null';
	}else if(security_type == 'Qr Code'){

		var field_extra_name = 'Dummy Text';
		var text = "Dummy Text";
		var font_color_extra = "none";
		var field_extra_width = 32;
		var field_extra_height = 32;
		var field_extra_font_size = 10;
		var field_extra_font_style = "";
		var angle = 0;
		var line_gap = 0;
		var length = 0;
		var text_opicity = 1;
		var grey_scale = 0;
		var field_image = aws_canvas_upload_path+'/QR.png';
		var combo_qr_text = "{{Dummy Text}}";
		
	}

	$('[name="field_extra_name[]"]')[i].setAttribute('value',field_extra_name);
	$('[name="field_extra_font_color[]"]')[i].setAttribute('value',field_extra_font_color);
	$('[name="font_color_extra[]"]')[i].setAttribute('value',font_color_extra);
	$('[name="field_extra_x[]"]')[i].setAttribute('value',field_extra_x);
	$('[name="field_extra_y[]"]')[i].setAttribute('value',field_extra_y);
	$('[name="field_extra_width[]"]')[i].setAttribute('value',field_extra_width);
	$('[name="field_extra_height[]"]')[i].setAttribute('value',field_extra_height);
	$('[name="field_sample_text[]"]')[i].setAttribute('value', text);


	$('[name="field_extra_security_type[]"]')[i].setAttribute('value',field_extra_security_type);
	$('[name="field_extra_font_id[]"]')[i].setAttribute('value',field_extra_font_id);
	$('[name="field_extra_font_size[]"]')[i].setAttribute('value',field_extra_font_size);
	$('[name="field_extra_font_style[]"]')[i].setAttribute('value',field_extra_font_style);
	$('[name="field_extra_font_case[]"]')[i].setAttribute('value',field_extra_font_case);
	$('[name="field_extra_text_align[]"]')[i].setAttribute('value',field_extra_text_align);
	$('[name="field_image1[]"]')[i].setAttribute('value',field_image),
	$('[name="angle[]"]')[i].setAttribute('value',angle),
	$('[name="line_gap[]"]')[i].setAttribute('value',line_gap),
	$('[name="field_lockIndex[]"]')[i].setAttribute('value',field_lockIndex),
	$('[name="length[]"]')[i].setAttribute('value',length),
	$('[name="uv_percentage[]"]')[i].setAttribute('value',uv_percentage),
	$('[name="is_repeat[]"]')[i].setAttribute('value',is_repeat),
	$('[name="infinite_height[]"]')[i].setAttribute('value',infinite_height),
	$('[name="include_image[]"]')[i].setAttribute('value',include_image),
	$('[name="grey_scale[]"]')[i].setAttribute('value',grey_scale),
	$('[name="water_mark[]"]')[i].setAttribute('value',water_mark),
	$('[name="is_uv_image[]"]')[i].setAttribute('value',is_uv_image),
	$('[name="is_transparent_image[]"]')[i].setAttribute('value',is_transparent_image),
	$('[name="text_opicity[]"]')[i].setAttribute('value',text_opicity),
	$('[name="visible[]"]')[i].setAttribute('value',visible),
	$('[name="visible_varification[]"]')[i].setAttribute('value',visible_varification),
	$('[name="combo_qr_text[]"]')[i].setAttribute('value',combo_qr_text),
	myframe.contentWindow.postFrameMessage("update", i+3,
		$('[name="field_extra_x[]"]')[i].getAttribute('value'),
		$('[name="field_extra_y[]"]')[i].getAttribute('value'),
		$('[name="field_extra_width[]"]')[i].getAttribute('value'),
		$('[name="field_extra_height[]"]')[i].getAttribute('value'),
		$('[name="field_lockIndex[]"]')[i].getAttribute('value'),
		$('[name="field_extra_security_type[]"]')[i].getAttribute('value'),
		$('[name="field_extra_font_id[]"]')[i].getAttribute('value'),
		$('[name="field_extra_font_size[]"]')[i].getAttribute('value') * pointToPixel,
		$('[name="field_extra_font_style[]"]')[i].getAttribute('value'),
		$('[name="field_extra_font_case[]"]')[i].getAttribute('value'),
		 text,
		$('[name="field_extra_font_color[]"]')[i].getAttribute('value'),
		$('[name="font_color_extra[]"]')[i].getAttribute('value'),
		$('[name="field_extra_text_align[]"]')[i].getAttribute('value'),

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
		true
	);


}

//add extra fields
window.addField = function ($x, $y, $width, $height, $security_type, $font_id, $font_size, $font_case, $text,$field_lockIndex)
{


	var div = $("<div />");

	div.html(GetField());
	div.addClass('extrafields');
	$('#additional_field').append(div);

	var idx = $('[name="field_extra_name[]"]').length - 1;

	$('[name="field_extra_x[]"]')[idx].setAttribute('value', $x);
	$('[name="field_extra_y[]"]')[idx].setAttribute('value', $y);
	$('[name="field_extra_width[]"]')[idx].setAttribute('value', $width);
	$('[name="field_extra_height[]"]')[idx].setAttribute('value', $height);
	$('[name="field_sample_text[]"]')[idx].setAttribute('value', $text);
	$('[name="field_lockIndex[]"]')[idx].setAttribute('value', $field_lockIndex);

	// select elements
	var elm = $('[name="field_extra_security_type[]"]')[idx];
	//$(elm).val($security_type);
	$(elm).val("select");
	var elm = $('[name="uv_percentage[]"]')[idx];

	$(elm).val("select");
	var elm = $('[name="text_opicity[]"]')[idx];

	//$(elm).val($security_type);
	$(elm).val("select");
	var elm = $('[name="field_extra_font_id[]"]')[idx];
	$(elm).val($font_id);

	elm = $('[name="field_extra_font_size[]"]')[idx];
	$(elm).val($font_size);
	window.newField = true;
	setTimeout(window.showFieldDialog(idx + 3), 300);
}

//get right side field
function GetField() {

      var elm = '<input type="hidden" name="field_extra_mapped[]">';
      elm += '<input type="hidden" name="field_extra_name[]" value="">';
      elm += '<input type="hidden" name="field_extra_security_type[]">';
      elm += '<input type="hidden" class="form-control" name="field_extra_font_id[]">';
      elm += '<input type="hidden" name="field_extra_text_align[]" value="L">';
      elm += '<input type="hidden" name="field_extra_font_style[]" value="">';
      elm += '<input type="hidden" name="field_extra_font_case[]" value="">';
      elm += '<input type="hidden" name="field_extra_font_size[]" value="">';
      elm += '<input type="hidden" value="000000" name="field_extra_font_color[]" >';
      elm += '<input type="hidden" name="font_color_extra[]" >';
      elm += '<input type="hidden" name="field_extra_x[]">';
      elm += '<input type="hidden" name="field_extra_y[]">';
      elm += '<input type="hidden" name="field_extra_width[]">';
      elm += '<input type="hidden" name="field_extra_height[]">';
      elm += '<input type="hidden" name="field_sample_text[]">';
      /* add fields in elm */
      elm += '<input type="hidden" name="field_sample_text_width[]">';
      elm += '<input type="hidden" name="field_sample_text_vertical_width[]">';
      elm += '<input type="hidden" name="field_sample_text_horizontal_width[]">';
      elm += '<input type="hidden" name="microline_width[]">';
      elm += '<input type="hidden" name="field_image1[]">';
      elm += '<input type="hidden" name="angle[]">';
      elm += '<input type="hidden" name="line_gap[]">';
      elm += '<input type="hidden" name="field_lockIndex[]">';
      elm += '<input type="hidden" name="length[]">';
      elm += '<input type="hidden" name="uv_percentage[]">';
      elm += '<input type="hidden" name="is_repeat[]">';
      elm += '<input type="hidden" name="infinite_height[]">';
      elm += '<input type="hidden" name="include_image[]">';
      elm += '<input type="hidden" name="grey_scale[]">';
      elm += '<input type="hidden" name="water_mark[]">';
      elm += '<input type="hidden" name="is_uv_image[]">';
      elm += '<input type="hidden" name="is_transparent_image[]">';
      elm += '<input type="hidden" name="text_opicity[]">';
      elm += '<input type="hidden" name="visible[]">';
      elm += '<input type="hidden" name="visible_varification[]">';
      elm += '<input type="hidden" name="combo_qr_text[]">';
      return elm;
   }