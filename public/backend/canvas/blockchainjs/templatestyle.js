
function showFieldDialogCtrl($ctrlArray){
    getFieldOfctrl($ctrlArray);
    
    $('#field_x_ctrl').keyup(function(){
		
		var field_x_ctrl = $('#field_x_ctrl').val();
		$ctrlArray.forEach(function(element) {
		  	if(field_x_ctrl != null && field_x_ctrl != ''){

			  	if(element == 1){

					$('#field_qr_x').val(field_x_ctrl);
					var field_qr_x = $('#field_qr_x').val();
					var field_qr_y = $('#field_qr_y').val();
					var field_qr_width = $('#field_qr_width').val();
					var field_qr_height = $('#field_qr_height').val();
					var field_qr_lockIndex = $('#field_qr_lockIndex').val();
					myframe.contentWindow.postFrameMessage('update',element,field_qr_x,field_qr_y,field_qr_width,field_qr_height,field_qr_lockIndex);
					
				  
				}else if(element == 2){

				  
			  		$('#field_id_x').val(field_x_ctrl);
					var field_id_x = $('#field_id_x').val();
					var field_id_y = $('#field_id_y').val();
					var field_id_width = $('#field_id_width').val();
					var field_id_height = $('#field_id_height').val();
					var field_id_lockIndex = $('#field_id_lockIndex').val();
				  	myframe.contentWindow.postFrameMessage('update',element,field_id_x,field_id_y,field_id_width,field_id_height,field_id_lockIndex);
				  
				}else{

				  	// myframe.contentWindow.postFrameMessage('update',element,field_x_ctrl);
				  	$('[name="field_extra_x[]"]')[element - 3].setAttribute('value', field_x_ctrl);
				  	setValueOnKeyUpCtrl(element);
				}
		  	}
		});
	})
	$('#field_y_ctrl').keyup(function(){
		
		var field_y_ctrl = $('#field_y_ctrl').val();
		$ctrlArray.forEach(function(element) {
		  	if(field_y_ctrl != null && field_y_ctrl != ''){

			  	if(element == 1){

					$('#field_qr_y').val(field_y_ctrl);
					var field_qr_x = $('#field_qr_x').val();
					var field_qr_y = $('#field_qr_y').val();
					var field_qr_width = $('#field_qr_width').val();
					var field_qr_height = $('#field_qr_height').val();
					var field_qr_lockIndex = $('#field_qr_lockIndex').val();
					myframe.contentWindow.postFrameMessage('update',element,field_qr_x,field_qr_y,field_qr_width,field_qr_height,field_qr_lockIndex);
					
				  
				}else if(element == 2){

				  
			  		$('#field_id_y').val(field_y_ctrl);
					var field_id_x = $('#field_id_x').val();
					var field_id_y = $('#field_id_y').val();
					var field_id_width = $('#field_id_width').val();
					var field_id_height = $('#field_id_height').val();
					var field_id_lockIndex = $('#field_id_lockIndex').val();
				  	myframe.contentWindow.postFrameMessage('update',element,field_id_x,field_id_y,field_id_width,field_id_height,field_id_lockIndex);
				  
				}else{

				  	// myframe.contentWindow.postFrameMessage('update',element,'',field_y_ctrl);
				  	$('[name="field_extra_y[]"]')[element - 3].setAttribute('value', field_y_ctrl);
				  	setValueOnKeyUpCtrl(element);
				}
		  	}
		});
	})
	$('#field_extra_font_ctrl').change(function(){
		var font_id = $(this).val();
		$ctrlArray.forEach(function(element) {
			if (element > 2) {
				$('[name="field_extra_font_id[]"]')[element - 3].setAttribute('value', font_id);
				setValueOnKeyUpCtrl(element);
			}
		});
	});
	$('#field_extra_font_size_ctrl').change(function(){
		var font_size = $(this).val();
		$ctrlArray.forEach(function(element) {
			if (element > 2) {

				$('[name="field_extra_font_size[]"]')[element - 3].setAttribute('value', font_size);
				setValueOnKeyUpCtrl(element);
			}
		});
	});
	function setValueOnKeyUpCtrl($id){
		
		var i = $id - 3;

		myframe.contentWindow.postFrameMessage("update", i + 3,
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
			/* End Rushik Code*/
			true
		);
	}
}
function keyboardEvents($id,$security_type,i){
    // keyboard events
    $('#field_qr_x1,#field_qr_y1,#field_qr_width1,#field_qr_height1,#field_qr_lockIndex1').keyup(function(){

        var fReturn = false;

        var field_qr_x1 = $('#field_qr_x1').val();
        var field_qr_y1 = $('#field_qr_y1').val();
        var field_qr_width1 = $('#field_qr_width1').val();
        var field_qr_height1 = $('#field_qr_height1').val();
        var field_qr_lockIndex1 = $('#field_qr_lockIndex1').val();
        
        $('#field_qr_width').val(field_qr_width1);
        $('#field_qr_height').val(field_qr_height1);

        myframe.contentWindow.postFrameMessage('update',$id,field_qr_x1,field_qr_y1,field_qr_width1,field_qr_height1,field_qr_lockIndex1);
    })

    //image size slider changes
    $('.range-slider__range').change(function(){

        $('#field_qr_width').val($(this).val());
        $('#field_qr_height').val($(this).val());
        $('.range-slider__value').text($(this).val());

        var field_qr_x1 = $('#field_qr_x1').val();
        var field_qr_y1 = $('#field_qr_y1').val();
        var field_qr_width1 = $('#field_qr_width').val();
        var field_qr_height1 = $('#field_qr_height').val();
        var field_qr_lockIndex1 = $('#field_qr_lockIndex1').val();

        myframe.contentWindow.postFrameMessage('update',$id,field_qr_x1,field_qr_y1,field_qr_width1,field_qr_height1,field_qr_lockIndex1);
    })

    //barcode keyup changes
    $('#field_id_x1,#field_id_y1,#field_id_width1,#field_id_height1,#field_id_lockIndex').keyup(function(){

        var field_id_x1 = $('#field_id_x1').val();
        var field_id_y1 = $('#field_id_y1').val();
        var field_id_width1 = $('#field_id_width1').val();
        var field_id_height1 = $('#field_id_height1').val();
        var field_id_lockIndex = $('#field_id_lockIndex').val();

        $('#field_id_x').val(field_id_x1);
        $('#field_id_y').val(field_id_y1);
        $('#field_id_width').val(field_id_width1);
        $('#field_id_height').val(field_id_height1);


        myframe.contentWindow.postFrameMessage('update',$id,field_id_x1,field_id_y1,field_id_width1,field_id_height1,field_id_lockIndex);
    })
    $('#field_extra_x').keyup(function(){
        setValueOnKeyUp($id,$security_type);
    });
    $('#field_extra_y').keyup(function(){
        setValueOnKeyUp($id,$security_type);
    });

    $('#field_qr_image_chk1').click(function(){
        if($(this).prop("checked") == true){
            $(this).val(1);
            $('#field_qr_image1').removeAttr('disabled','true');

            $('#field_qr_image_chk').val($('#field_qr_image_chk1').val());
            var field_qr_x1 = $('#field_qr_x1').val();
            var field_qr_y1 = $('#field_qr_y1').val();
            var field_qr_width1 = $('#field_qr_width1').val();
            var field_qr_height1 = $('#field_qr_height1').val();
            var field_qr_lockIndex1 = $('#field_qr_lockIndex1').val();
            myframe.contentWindow.postFrameMessage('update',$id,field_qr_x1,field_qr_y1,field_qr_width1,field_qr_height1,field_qr_lockIndex1,'','','','','','','','','','','','','','','','',$('#field_qr_image_chk').val());

        }else if($(this).prop("checked") == false){
            $(this).val(0);
            $('#field_qr_image1').attr('disabled','true');

            var field_qr_x1 = $('#field_qr_x1').val();
            var field_qr_y1 = $('#field_qr_y1').val();
            var field_qr_width1 = $('#field_qr_width1').val();
            var field_qr_height1 = $('#field_qr_height1').val();
            var field_qr_lockIndex1 = $('#field_qr_lockIndex1').val();
            $('#field_qr_image_chk').val($('#field_qr_image_chk1').val());
            myframe.contentWindow.postFrameMessage('update',$id,field_qr_x1,field_qr_y1,field_qr_width1,field_qr_height1,field_qr_lockIndex1,'','','','','','','','','','','','','','','','',$('#field_qr_image_chk').val());
        }
    })
    //on extra width change
    $('#field_extra_width').keyup(function(){
        setValueOnKeyUp($id,$security_type);
    });
    //on alignment change
    $('.fa.fa-align-left,.fa.fa-align-right,.fa.fa-align-center,.fa.fa-align-justify').click(function (event) {
        
        event.preventDefault();
        if($(this).hasClass('btn-default')) {
            $(this).removeClass('btn-default');
            $(this).parent().children().filter('.fa-align-left').removeClass('btn-primary').addClass('btn-default');
            $(this).parent().children().filter('.fa-align-right').removeClass('btn-primary').addClass('btn-default');
            $(this).parent().children().filter('.fa-align-center').removeClass('btn-primary').addClass('btn-default');
            $(this).parent().children().filter('.fa-align-justify').removeClass('btn-primary').addClass('btn-default');
            $(this).addClass('btn-primary');
            if($(this).hasClass('fa-align-left')) {
                $(this).parent().children().filter('input.talign').val('L');
                $('#widthRow').show();
                var i = $id - 3;
                text = $('#field_sample_text').val();
                setValueOnKeyUp($id,$security_type);

            } else if($(this).hasClass('fa-align-center')) {
                $(this).parent().children().filter('input.talign').val('C');
                $('#widthRow').show()
                var i = $id - 3;
                text = $('#field_sample_text').val();
                setValueOnKeyUp($id,$security_type);

            } else if($(this).hasClass('fa-align-right')) {
                $('#widthRow').show()
                //console.log('rigjt');
                $(this).parent().children().filter('input.talign').val('R');
                var i = $id - 3;
                text = $('#field_sample_text').val();
                setValueOnKeyUp($id,$security_type);

            }else if($(this).hasClass('fa-align-justify')) {
                $(this).parent().children().filter('input.talign').val('J');
                $('#widthRow').show();
                var i = $id - 3;
                text = $('#field_sample_text').val();
                setValueOnKeyUp($id,$security_type);

            }
        }
    });
    //bold italic effect
    $('.fa.fa-bold,.fa.fa-italic').click(function (event) {
        var fReturn = false;
        event.preventDefault();
        var b = '', i = '',
        t = $(this).parent().children().filter('input.fstyle').val();
        if(t == 'B')
            b = 'B';
        else if (t == 'I')
            i = 'I';
        else if(t == 'BI')
        {
            b = 'B'; i = 'I';
        }
        var font_id = $('#field_extra_font_id').val();

        if($(this).hasClass('btn-default')) {
            if($(this).hasClass('fa-bold'))
                b = 'B';
            else
                i = 'I';

            toastr.options = {"timeOut": "500","positionClass": "toast-top-center"};
            $.each(fontList, function(id, font) {
                if(font.id == font_id) {
                    var str = "font_id="+font.id+", font_name="+font.font_name;
                    if(b == 'B' && i == 'I') {
                        if(!font.font_filename_BI) {
                            toastr["error"]('Bold Italic font not available');
                            fReturn = true;
                        }
                    } else if(b == 'B') {
                        if(!font.font_filename_B) {
                            toastr["error"]('Bold font not available');
                            fReturn = true;
                        }
                    } else if(i == 'I') {
                        if(!font.font_filename_I) {
                            toastr["error"]('Italic font not available');
                            fReturn = true;
                        }
                    }
                }
                if(fReturn)
                    return false;
            });
            toastr.options = {"timeOut": "2000","positionClass": "toast-top-right"};
            if(fReturn)
                return;
            $(this).addClass('btn-primary');
            $(this).removeClass('btn-default');
        } else if($(this).hasClass('btn-primary')) {
            $(this).removeClass('btn-primary');
            $(this).addClass('btn-default');
            if($(this).hasClass('fa-bold'))
                b = '';
            else
                i = '';
        }

        var ids = $id - 3;
        text = $('#field_sample_text').val();
        // myframe.contentWindow.postFrameMessage('update',$id,'','','','','',$('[name="field_extra_security_type[]"]')[ids].getAttribute('value'),'','',b+i,text);
        $(this).parent().children().filter('input.fstyle').val(b + i);

        $(this).blur();
        setValueOnKeyUp($id,$security_type);
    });
    //font  name change
    $('#field_extra_font_id').change(function(){
        var font_id = $(this).val()
        fontStyleChange(font_id);
        setValueOnKeyUp($id,$security_type);
    });
    //on font size change
    $('#field_extra_font_size').change(function(){
        setValueOnKeyUp($id,$security_type);
    });
    //on color change
    $('#field_extra_font_color').change(function(){
        var i = $id - 3;
        $('[name="field_extra_font_color[]"]')[i].setAttribute('value',$('#field_extra_font_color').val());
        var field_sample_text = $('#field_sample_text').val();
        setValueOnKeyUp($id,$security_type);
    });

    //All Caps
    $('#field_extra_font_case').click(function(){

        if($(this).prop("checked") == true){
            $('#field_extra_font_case').val(1);
           // $('[name="field_extra_font_case[]"]')[i].setAttribute('value',$('#field_sample_text').val());
            setValueOnKeyUp($id,$security_type);

        }else if($(this).prop("checked") == false){
            $('#field_extra_font_case').val(0);
           // $('[name="field_extra_font_case[]"]')[i].setAttribute('value',$('#field_extra_font_case').val());
            setValueOnKeyUp($id,$security_type);
        }
    })

    $('#field_extra_height').keyup(function(){
            var i = $id - 3;
        text = $('#field_sample_text').val();

        var field_extra_height = $(this).val();
        $('[name="field_sample_text[]"]')[i].setAttribute('value', $('#field_sample_text').val());
        $('[name="field_extra_height[]"]')[i].setAttribute('value',$('#field_extra_height').val());
        setValueOnKeyUp($id,$security_type);
    });

    $('#field_sample_text').keyup(function(){

        var field_sample_text = $(this).val();
        $('[name="field_sample_text[]"]')[i].setAttribute('value', $('#field_sample_text').val());
        setValueOnKeyUp($id,$security_type);

    });

    $('#combo_qr_text').keyup(function(){

        var combo_qr_text = $(this).val();
        $('[name="combo_qr_text[]"]')[i].setAttribute('value', $('#combo_qr_text').val());
        setValueOnKeyUp($id,$security_type);

    });

    $('#field_qr_combo_qr_text1').keyup(function(){

       var field_qr_combo_qr_text = $(this).val();
        $('[name="field_qr_combo_qr_text"]').val($('#field_qr_combo_qr_text1').val());
        //setValueOnKeyUp($id,$security_type);
       // console.log('A'+field_qr_combo_qr_text);
    });

   

    $('#field_metadata_label').keyup(function(){

        var field_metadata_label = $(this).val();
        $('[name="field_metadata_label[]"]')[i].setAttribute('value', $('#field_metadata_label').val());
        setValueOnKeyUp($id,$security_type);

    });

    $('#field_metadata_value').keyup(function(){

        var field_metadata_value = $(this).val();
        $('[name="field_metadata_value[]"]')[i].setAttribute('value', $('#field_metadata_value').val());
        setValueOnKeyUp($id,$security_type);

    });

    //for extra width
    if($('#infinite_height').val() == 1){
        $('#infinite_height').attr('checked','checked');
        $('#widthRow').show();
    }else{
        $('#infinite_height').removeAttr('checked','checked');
        $('#widthRow').show();
        //on micro text border/invisible/qr/2D barcode code click width show
        if($security_type == 'Micro Text Border' || $security_type == 'Invisible' || $security_type == 'Qr Code' || $security_type == '2D Barcode'){
            $('#widthRow').show();
        }
        //on micro line click if repeat is checked then width show
        if($security_type == 'Micro line'){
            if($('#is_repeat').val() == 1){
                $('#widthRow').show();
            }
        }
    }


    $('#infinite_height').click(function(){
        if($(this).prop("checked") == true){
            $(this).val(1);
            $('#widthRow').show();
            $('#field_extra_width').val(50);
            var field_sample_text = $('#field_sample_text').val();
            setValueOnKeyUp($id,$security_type);

        }else if($(this).prop("checked") == false){
            $(this).val(0);
            $('#widthRow').hide();
            $('#field_extra_width').val(50);
            var field_sample_text = $('#field_sample_text').val();
            setValueOnKeyUp($id,$security_type);
        }
    })

    //on field name change
    $('#field_extra_name').keyup(function(){
        setValueOnKeyUp($id,$security_type);
    });

    //in custom image print color click
    $('#grey_scale').click(function(){
        if($(this).prop("checked") == true){
            $(this).val(1);
            var i = $id - 3;
            var field_sample_text = $('#field_sample_text').val();
            var field_image_data = $('[name="field_image1[]"]')[i].getAttribute('value');
            var template_name = $('#template_name').val();
            var width =$('[name="field_extra_width[]"]')[i].getAttribute('value');
            var height = $('[name="field_extra_height[]"]')[i].getAttribute('value');


            $('[name="grey_scale[]"]')[i].setAttribute('value',$('#grey_scale').val());
            setValueOnKeyUp($id,$security_type);
        }else if($(this).prop("checked") == false){
            var i = $id - 3;
            $(this).val(0);
            var field_sample_text = $('#field_sample_text').val();
            var field_image_data = $('[name="field_image1[]"]')[i].getAttribute('value');
            var template_name = $('#template_name').val();
            var width = $('[name="field_extra_width[]"]')[i].getAttribute('value');
            var height = $('[name="field_extra_height[]"]')[i].getAttribute('value');
            $('[name="grey_scale[]"]')[i].setAttribute('value',$('#grey_scale').val());
            setValueOnKeyUp($id,$security_type);
        }
    });

    //uv image
    $('#is_uv_image').click(function(){
        if($(this).prop("checked") == true){
            $('#is_uv_image').val(1);
            $('[name="is_uv_image[]"]')[i].setAttribute('value',$('#is_uv_image').val());
            setValueOnKeyUp($id,$security_type);

        }else if($(this).prop("checked") == false){
            $('#is_uv_image').val(0);
            $('[name="is_uv_image[]"]')[i].setAttribute('value',$('#is_uv_image').val());
            setValueOnKeyUp($id,$security_type);
        }
    })

    //transparent image
    $('#is_transparent_image').click(function(){
        if($(this).prop("checked") == true){
            $('#is_transparent_image').val(1);
            $('[name="is_transparent_image[]"]')[i].setAttribute('value',$('#is_transparent_image').val());
            setValueOnKeyUp($id,$security_type);

        }else if($(this).prop("checked") == false){
            $('#is_uv_image').val(0);
            $('[name="is_transparent_image[]"]')[i].setAttribute('value',$('#is_transparent_image').val());
            setValueOnKeyUp($id,$security_type);
        }
    });

    //on micro text angle key up
    $('#angle').keyup(function(){

        var i = $id - 3;
        var field_sample_text = $('#field_sample_text').val();
        $('[name="field_sample_text[]"]')[i].setAttribute('value', field_sample_text);
        $('[name="angle[]"]')[i].setAttribute('value', $('#angle').val());
        $('[name="field_extra_width[]"]')[i].setAttribute('value', $('#field_extra_width').val());

        var width = $('[name="field_extra_width[]"]')[i].getAttribute('value');
        var angle = $('[name="angle[]"]')[i].getAttribute('value');
        var is_repeat = $('[name="is_repeat[]"]')[i].getAttribute('value');

        setValueOnKeyUp($id,$security_type);
    });

    //ghost image length key up
    $('#length').keyup(function(){
        setValueOnKeyUp($id,$security_type);
    });
    //on security line opacity change
    $('#text_opicity').change(function(){
        setValueOnKeyUp($id,$security_type);
    });
    //on font color change
    $('#field_extra_font_color_dr').change(function(){
        setValueOnKeyUp($id,$security_type);
    });
    //on line gap keyup inside uv repeat
    $('#line_gap').keyup(function(){
        setValueOnKeyUp($id,$security_type);
    });
    //on uv percentage change
    $('#uv_percentage').change(function(){
        setValueOnKeyUp($id,$security_type);
    });
    //check include image check box is checked or not
    if($('#include_image').val() == 0){
        $('#include_image').removeAttr('checked','checked');
        if($security_type == 'Qr Code'){
            $('#image').hide()
            $('#DisplayImage').hide()
        }
    }else{
        $('#include_image').attr('checked','checked');

    }
    //on add image checkbox click
    $('#include_image').click(function(){
        if($(this).prop("checked") == true){
            $(this).val(1);
            $('#DisplayImage').show();
            $('#image').show();
            setValueOnKeyUp($id,$security_type);

        }else if($(this).prop("checked") == false){
            $(this).val(0);
            $('#image').hide();
            $('#DisplayImage').hide();

            setValueOnKeyUp($id,$security_type);
        }
    });


    //isMetaData
    $('#is_meta_data').click(function(){

        if($(this).prop("checked") == true){

           // alert($('input[name="is_meta_data[]"]').length);
            $('#is_meta_data').val(1);
            $('[name="is_meta_data[]"]')[i].setAttribute('value',$('#is_meta_data').val());
            $('#metadataDetailsDiv').show();
            setValueOnKeyUp($id,$security_type);



        }else if($(this).prop("checked") == false){
            $('#is_meta_data').val(0);
            $('[name="is_meta_data[]"]')[i].setAttribute('value',$('#is_meta_data').val());
            $('#metadataDetailsDiv').hide();
            setValueOnKeyUp($id,$security_type);
        }
    });

    
    // Rohit Changes
    //isMetaDataRohit
    $('#is_encrypted_qr').click(function(){
        
        if($(this).prop("checked") == true){

          
            $('#is_encrypted_qr').val(1);
            $('#is_encrypted_qrHidden').val(1);
            //$('[name="is_encrypted_qr[]"]')[i].setAttribute('value',$('#is_encrypted_qr').val());
            $('#metadataDetailsDivRohit').show();
            //setValueOnKeyUp($id,$security_type);



        }else if($(this).prop("checked") == false){
            $('#is_encrypted_qr').val(0);
            $('#is_encrypted_qrHidden').val(0);
            //$('[name="is_encrypted_qr[]"]')[i].setAttribute('value',$('#is_meta_data_rohit').val());
            $('#metadataDetailsDivRohit').hide();
            //setValueOnKeyUp($id,$security_type);
        }
    });

    
    if($id == 1) {
        var is_encrypted_qrVal = $("#is_encrypted_qrHidden").val();
        var encrypted_qr_text = $("#encrypted_qr_textHidden").val();
        
        if(is_encrypted_qrVal == 1) {
            $("#is_encrypted_qr").attr("checked", "checked");
            $("#is_encrypted_qr").val(is_encrypted_qrVal);
            $("#metadataDetailsDivRohit").show();
            $("#encrypted_qr_text").val(encrypted_qr_text);
        }
    }

    // $("#encrypted_qr_text").keyup(function(){
        
    //     $("#encrypted_qr_textHidden").val(this.value);

    // });
    // Rohit Changes

}



// on key up call function
function setValueOnKeyUp($id,security_type){
    var i = $id - 3;

    var text = $('#field_sample_text').val();
    if(text.trim().length == 0)
        text = $('#field_extra_name').val();

    $('[name="field_extra_name[]"]')[i].setAttribute('value',$('#field_extra_name').val());
    $('[name="field_extra_font_color[]"]')[i].setAttribute('value',$('#field_extra_font_color').val());
    $('[name="font_color_extra[]"]')[i].setAttribute('value',$('#field_extra_font_color_dr').val());
    $('[name="field_extra_x[]"]')[i].setAttribute('value',$('#field_extra_x').val());
    $('[name="field_extra_y[]"]')[i].setAttribute('value',$('#field_extra_y').val());
    $('[name="field_extra_width[]"]')[i].setAttribute('value',$('#field_extra_width').val());
    $('[name="field_extra_height[]"]')[i].setAttribute('value',$('#field_extra_height').val());
    $('[name="field_sample_text[]"]')[i].setAttribute('value', text);

    // console.log($('#field_extra_security_type').val());
    $('[name="field_extra_security_type[]"]')[i].setAttribute('value',security_type);

    $('[name="field_extra_font_id[]"]')[i].setAttribute('value',$('#field_extra_font_id').val());
    $('[name="field_extra_font_size[]"]')[i].setAttribute('value',$('#field_extra_font_size').val());
    $('[name="field_extra_font_style[]"]')[i].setAttribute('value',$('#field_extra_font_style').val());
    $('[name="field_extra_font_case[]"]')[i].setAttribute('value',$('#field_extra_font_case').val());
    $('[name="field_extra_text_align[]"]')[i].setAttribute('value',$('#field_extra_text_align').val());
    $('[name="angle[]"]')[i].setAttribute('value',$('#angle').val()),
    $('[name="line_gap[]"]')[i].setAttribute('value',$('#line_gap').val()),
    $('[name="field_lockIndex[]"]')[i].setAttribute('value',$('#field_lockIndex').val()),
    $('[name="length[]"]')[i].setAttribute('value',$('#length').val()),
    $('[name="uv_percentage[]"]')[i].setAttribute('value',$('#uv_percentage').val()),
    $('[name="is_repeat[]"]')[i].setAttribute('value',$('#is_repeat').val()),
    $('[name="infinite_height[]"]')[i].setAttribute('value',$('#infinite_height').val()),
    $('[name="include_image[]"]')[i].setAttribute('value',$('#include_image').val()),
    $('[name="grey_scale[]"]')[i].setAttribute('value',$('#grey_scale').val()),
    $('[name="water_mark[]"]')[i].setAttribute('value',$('#water_mark').val()),
    $('[name="is_uv_image[]"]')[i].setAttribute('value',$('#is_uv_image').val()),
    $('[name="is_transparent_image[]"]')[i].setAttribute('value',$('#is_transparent_image').val()),
    $('[name="text_opicity[]"]')[i].setAttribute('value',$('#text_opicity').val()),
    $('[name="visible[]"]')[i].setAttribute('value',$('#visible').val()),
    $('[name="visible_varification[]"]')[i].setAttribute('value',$('#visible_varification').val()),
    $('[name="combo_qr_text[]"]')[i].setAttribute('value',$('#combo_qr_text').val()),
    $('[name="is_meta_data[]"]')[i].setAttribute('value',$('#is_meta_data').val()),
    $('[name="field_metadata_label[]"]')[i].setAttribute('value',$('#field_metadata_label').val()),
    $('[name="field_metadata_value[]"]')[i].setAttribute('value',$('#field_metadata_value').val()),
    myframe.contentWindow.postFrameMessage("update", i + 3,
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

        // send data to template
        $('[name="field_image1[]"]')[i].getAttribute('value'),
        $("#template_id").val(),
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
        true
    );
}
