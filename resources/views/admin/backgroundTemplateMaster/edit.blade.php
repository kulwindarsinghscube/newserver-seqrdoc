@extends('admin.layout.layout')
@section('content')
<div id="">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <h1 class="page-header">
                    <i class="fa fa-fw fa fa-file-o"></i>Background Template
                    <ol class="breadcrumb pull-right" style="background:transparent;font-size:14px;margin-top:10px;"></ol>
                </h1>
            </div>
        </div>
        <div class="col-lg-12">
            <a href="{{ route('background-master.index') }}" class="btn btn-theme" id="backto"><i class="fa fa-arrow-left"></i> Back</a>	
        </div>
        <div class="clearfix">  </div>
        <?= Form::model($backgroundTemplate,array('url' => route('background-master.update',$backgroundTemplate->id),'class' => 'form-horizontal','files'=>true,'method'=>'patch')) ?>
            <div class="panel panel-primary" style="margin: 0 auto; width: 800px;" >
                <div class="panel-heading">Background Template</div>
                <div class="panel-body">
                    <div id="response"></div>
                    <div class="form-group clearfix">
                        <label for="field_bg_template_name" class="form-label col-md-4">Background Template Name</label>
                        <div class="col-md-8">
                            <?= Form::text('background_name',old('background_name'),['class'=>'form-control', 'id'=>'field_bg_template_name']) ?> 
                            <span class='text-danger'>{{ $errors->first('background_name') }}</span>
                        </div>
                    </div>
                    <div class="form-group clearfix">
                        <label for="field_width" class="form-label col-md-2">Width <small>(mm)</small></label>
                        <div class="col-md-4">
                            <?= Form::text('width',old('width'),['class'=>'form-control', 'id'=>'field_bg_width']) ?> 
                            <span class='text-danger'>{{ $errors->first('width') }}</span>
                        </div>
                        <label for="field_height" class="form-label col-md-2">Height <small>(mm)</small></label>
                        <div class="col-md-4">
                            <?= Form::text('height',old('height'),['class'=>'form-control', 'id'=>'field_bg_height']) ?> 
                            <span class='text-danger'>{{ $errors->first('height') }}</span>
                        </div>
                    </div>
                    <div class="form-group clearfix">
                        <label for="field_status" class="form-label col-md-2">Status</label>
                        <div class="col-md-4">
                            <?= Form::select('status',['1' => 'Active','0' =>'Inactive'],null,['id'=>'field_status','class'=>'form-control']) ?>
                            <span class='text-danger'>{{ $errors->first('category') }}</span>
                        </div>
                    </div>
                    <div class="form-group clearfix">
                        <label for="field_bg_template_img" class="form-label col-md-3">Existing Background Image</label>
                        <div class="col-md-6">
                            
                            <?= Form::hidden('exist_image_path', 'yes' , array('id' => 'exist_image_path')) ?>
                            <?php
                                $domain = \Request::getHost();
                                $subdomain = explode('.', $domain);
                            ?>
                            @if($get_file_aws_local_flag['file_aws_local'] == '1')
                                <img data-enlargeable src="<?= Config::get('constant.amazone_path').$subdomain[0]?>/backend/canvas/bg_images/{{$backgroundTemplate->image_path }}" style="height: 100px;width:100px;" /> <br>
                            @else
                                <img data-enlargeable src="<?= Config::get('constant.local_base_path').$subdomain[0]?>/backend/canvas/bg_images/{{$backgroundTemplate->image_path }}" style="height: 100px;width:100px;" /> <br>
                            @endif
                            {{ $backgroundTemplate->image_path }}
                        </div>

                    </div>
                    <div class="form-group clearfix">
                        <label for="field_bg_template_img" class="form-label col-md-3">Background Image</label>
                        <div class="col-md-9">
                            <?= Form::file('image_path',['id'=>'selectImage','class'=>'form-control','accept'=>"image/jpg, image/jpeg, image/png"]) ?> 
                            <span class='text-danger'>{{ $errors->first('image_path') }}</span>

                                <img style="display:none; width: 100%;height: 100%;margin-top: 10px" id="preview" src="#" alt="your image" class="mt-3" style="display:none;"/>
                            
                        </div>
                    </div>
                    <div class="form-group clearfix">
                        <label for="opicity" class="form-label col-md-3">Image Opacity</label>
                        <div class="col-md-8">
                            <input type="range" name="background_opicity" class="sliders"  min="1" max="10" value="" />
                        </div>
                        <div class="col-md-1"><span class="range-slider__value"></span>
                        </div>
                    </div>
                    <div class="form-group clearfix text-center">
                        <img src="" />
                    </div>
                    
                    <div class="form-group clearfix tmpl">
                        <button type="submit" class="btn btn-success col-lg-2 col-md-2 col-md-offset-4 col-sm-12 col-xs-12" id="btnSave"><i class="fa fa-save"></i> Save</button>
                        <a href="{{ route('background-master.index') }}" class="btn btn-danger col-lg-2 col-md-2 col-md-offset-1 col-sm-12 col-xs-12" id="btnCancel"><i class="fa fa-close"></i>Cancel</a>
                        
                    </div>
                </div>
            </div>
        <?= Form::close() ?>
    </div>
</div>
<br>
@stop
@section('script')
@include('partials.alert')
<script>
    $('img[data-enlargeable]').addClass('img-enlargeable').click(function() {
        var src = $(this).attr('src');
        var modal;

        function removeModal() {
            modal.remove();
            $('body').off('keyup.modal-close');
        }
        modal = $('<div>').css({
            background: 'RGBA(0,0,0,.5) url(' + src + ') no-repeat center',
            backgroundSize: 'contain',
            width: '100%',
            height: '100%',
            position: 'fixed',
            zIndex: '10000',
            top: '0',
            left: '0',
            cursor: 'zoom-out'
        }).click(function() {
            removeModal();
        }).appendTo('body');
        //handling ESC
        $('body').on('keyup.modal-close', function(e) {
            if (e.key === 'Escape') {
                removeModal();
            }
        });
    });
</script>

<script>
    var rangeValue = $('.sliders').val();
    $('.range-slider__value').html(rangeValue);
    $('.sliders').on('change', function() {
        let val = $(this).val();
        $('.range-slider__value').html(val);
    });
</script>

<script>
    selectImage.onchange = evt => {
        preview = document.getElementById('preview');
        preview.style.display = 'block';
        const [file] = selectImage.files
        if (file) {
            
            $("#preview").css("display", "block");
            $("#preview").css("border", "1px solid #000000");
            // border: 1px solid #000000;
            preview.src = URL.createObjectURL(file)
        }
    }
</script>

@stop

