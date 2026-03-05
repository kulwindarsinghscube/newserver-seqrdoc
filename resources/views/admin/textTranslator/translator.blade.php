@extends('admin.layout.layout')
@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">
    
    <div class="container">
        <div class="col-xs-12">
            <div class="clearfix">
                <style type="text/css">
                    label.error {
                        color: red;
                    }

                    .frow {
                        /*border-bottom: 2px solid black;*/
                        /*border-top: 1px solid black;*/
                    }

                    .tmpl .btn {
                        margin-left: 65px;
                    }
                </style>
                <div id="">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-lg-12">
                                <h1 class="page-header"><i class="fa fa-language"></i>Transalator
                                    <ol class="breadcrumb pull-right"
                                        style="background:transparent;font-size:14px;margin-top:10px;"></ol>
                                </h1>
                            </div>
                        </div>

                        <form id="transliterationForm" class="mb-4">
                            <div class="form-group">
                                <h2 for="englishText" class="mb-3">English Text:</h2>
                
                                <textarea id="englishText" name="englishText" rows="4" class="form-control"
                                    placeholder="Enter your name in english"><?php echo isset($englishText) ? htmlspecialchars($englishText) : ''; ?></textarea>
                            </div>
                        </form>
                
                
                        <div id="results" class="row">
                            <div class="col-md-6">
                                <div class="row">
                                    <div class="col-md-12">
                
                                        <div class="row">
                                            <h2 class="mb-0 col-md-6">Hindi Text:</h2>
                                            <div class="col-md-6 text-right  ">
                                                <button class="btn btn-outline-primary keyboard_toogle ml-2 " style="margin-top: 20px;">
                                                    <i class="fa fa-keyboard-o"></i>
                                                </button>
                                            </div>
                                         
                                        </div>
                                        <textarea id="txtHindi" rows="4" class="form-control txtHindi"
                                            placeholder="अपना नाम हिंदी में दर्ज करें"></textarea>
                                    </div>
                                    <div class="col-md-12 mt-2  hindi_keyboard" style="display:none;min-height:100px">
                                      
                                       @include('admin.textTranslator.hindi_keyboard')

                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h2 class="mb-3">KrutiDev Text:</h2>
                                <textarea id="txtkritidev" rows="4" class="form-control" readonly></textarea>
                            </div>
                        </div>
                

                    </div>
                </div>
            </div>
        </div>
    </div>
@stop 
@section('script')
<script type="text/javascript">
    $(document).ready(function () {

        var csrfToken = $('meta[name="csrf-token"]').attr('content');

        // Setup AJAX to include CSRF token in headers
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': csrfToken
            }
        });

        $('#transliterationForm').on('change', function (e) {
            // $('.loader').removeClass('hidden');
            e.preventDefault();
            var text = $('#englishText').val();
            var convertTo = 'hindi'
            $.ajax({
                url: "<?= route('text-translator.save')?>",
                type: 'POST',
                data: { text: text, convertTo: convertTo },
                dataType: 'json',
                success: function (response) {
                    $('#txtHindi').val(response.value);
                    $('#txtHindi').trigger('change');
                    // $('.loader').addClass('hidden');
                },
                error: function () {
                    alert('An error occurred. Please try again.');
                    // $('.loader').addClass('hidden');
                }
            });
        });


        $('.txtHindi').on('change', function (e) {
            var text = $('#txtHindi').val();
            // $('.loader').removeClass('hidden');
            var convertTo = 'kritidev'
            e.preventDefault();
            $.ajax({
                url: "<?= route('text-translator.save')?>",
                type: 'POST',
                data: { text: text, convertTo: convertTo },
                dataType: 'json',
                success: function (response) {
                    $('#txtkritidev').val(response.value);
                    // $('.loader').addClass('hidden');

                },
                error: function () {
                    // $('.loader').addClass('hidden');
                    alert('An error occurred. Please try again.');
                }
            });
        });
    });
    $('.keyboard_toogle').on("click", function () {
        var $keyboard = $('.hindi_keyboard');
        var $button = $(this);

        if ($keyboard.is(':visible')) {
            // Hide the keyboard
            $keyboard.hide();
            // Change button class to 'btn-outline-primary'
            $button.removeClass('btn-primary').addClass('btn-outline-primary');
        } else {
            // Show the keyboard
            $keyboard.show();
            // Change button class to 'btn-primary'
            $button.removeClass('btn-outline-primary').addClass('btn-primary');
        }
    });
</script>
@stop