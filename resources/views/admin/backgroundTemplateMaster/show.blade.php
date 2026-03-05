@extends('admin.layout.layout')
@section('content')
<div class="container">
    <div class="col-xs-12">
        <div class="clearfix">
            
            <div id="">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-lg-1">
                            <a href="{{ route('background-master.index') }}" class="btn btn-theme"><i class="fa fa-arrow-left"></i> Back</a>	
                        </div>
                        <div class="col-lg-11">
                            <h2 class="page-header"><i class="fa fa-fw fa-map-marker"></i> Background Template Detail : {{$backgroundTemplate->background_name}}
                                <ol class="breadcrumb pull-right" style="background:transparent;font-size:14px;margin-top:10px;"></ol>
                            </h2>
                        </div>
                    </div>
                    <div class="row">
                    </div>
                    
                    <div class="row">
                        <div class="panel panel-primary" style="margin: 15px auto; width: 800px;">
                            <div class="panel-heading"><i class="fa fa-image"></i> Image </div>
                            <div class="panel-body">
                                
                                <div class="form-group " style="">
                                    
                                    <div class="col-md-12">
                                        <div class="imageSection">
                                            <?php
                                                $domain = \Request::getHost();
                                                $subdomain = explode('.', $domain);
                                            ?>
                                            @if($get_file_aws_local_flag['file_aws_local'] == '1')
                                                <img data-enlargeable src="<?= Config::get('constant.amazone_path').$subdomain[0]?>/backend/canvas/bg_images/{{$backgroundTemplate->image_path }}" style="height: 100%;width:100%;" /> <br>
                                            @else
                                                <img data-enlargeable src="<?= Config::get('constant.local_base_path').$subdomain[0]?>/backend/canvas/bg_images/{{$backgroundTemplate->image_path }}" style="height: 100%;width:100%;" /> <br>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                </div>
            </div>	
        </div>
    </div>
</div>

<style type="text/css">
    .page-header {
        margin: 0px 0 20px;
    }
</style>
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
@stop


@section('style')
@stop
