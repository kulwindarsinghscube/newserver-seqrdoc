@extends('admin.layout.layout')
@section('content')

<div class="container">
    <div class="col-xs-12">
        <div class="clearfix">
            <div id="">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-lg-12">
                            <h1 class="page-header">
                                <i class="fa fa-fw fa-file-o"></i> Map Old Data
                                <ol class="breadcrumb pull-right" style="background:transparent;font-size:14px;">
                                    {{ Breadcrumbs::render('generateidcard') }}
                                </ol>
                                <i class="fa fa-info-circle iconModalCss" title="User Manual" id="generateIdCardsClick"></i>
                            </h1>
                        </div>
                    </div>

                    <!-- Upload Form -->
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <strong>Upload Excel</strong>
                        </div>
                        <div class="panel-body">
                            <form action="<?=route('functionalusers.postdatamapping')?>" method="POST" enctype="multipart/form-data">
                                @csrf
                                
                                <div class="form-group">
                                    <label for="doc_type">Select Dococumet Type</label>
                                    <select id="doc_type" name="doc_type" class="form-control">
                                        <option value="">Please Select</option>
                                        <option value="card">ID card</option>
                                        <option value="certificate">Certificate</option>
                                    </select>
                                </div>
                                <div class="form-group"> 
                                    <label for="templateName">Select Template</label> 
                                    <select id="template" name="templateName" class="form-control"> 
                                        <option value="">Please Select</option> 
                                    </select> 
                                </div>
                                <div class="form-group">
                                    <label for="field_file">Select File</label>
                                    <input type="file" name="field_file" id="field_file" class="form-control" required>
                                </div>

                                <button id="btn_updfile" type="button" class="btn btn-success">
                                    <i class="fa fa-upload"></i> Upload
                                </button>
                            </form>
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
    $('#template').select2({
            placeholder: "Select Template",
            allowClear: true
        });
    $('#btn_updfile').click(function (event) {
        // alert("hello");
        if($('#doc_type').val()==''){
            alert('please select document type');
            return false;
        }

         // $('#btn_updfile').html('Please wait...').prop('disabled', true);
        var fd = new FormData();
        var id = $('#template').val();
        var doc_type = $('#doc_type').val();
        var files = $('[name="field_file"]')[0].files[0];
        var token = "<?= csrf_token()?>";
        
        fd.append('field_file',files);
        fd.append('_token',token);
        fd.append('templateName',id);
        fd.append('doc_type',doc_type);

        $.ajax({
            url:'<?= route('functionalusers.postdatamapping')?>',
            type: "POST",
            dataType: "JSON",
            data:fd,
            processData: false,
            contentType:false,
            // async:true,
            success: function(response) 
            {
                if(response.success == false){
                    toastr["error"](response.message);
                    return false;
                }
                else{
                    toastr["success"](response.message);
                    location.reload();
                }
            }
        });
    });
    $('#doc_type').change(function () {

        var doc_type = $(this).val();
        $('#template').html('<option value="">Loading...</option>');

        if(doc_type != '') {
            $.ajax({
                url: "{{ route('functionalusers.getTemplates', ':doc') }}".replace(':doc', doc_type),
                type: 'GET',
                success: function (data) {
                    console.log(data);
                    $('#template').empty().append('<option value="">Please Select</option>');
                    $.each(data, function (key, template) {
                        $('#template').append('<option value="'+template.id+'">'+template.template_name+'</option>');
                    });
                }
            });
        } else {
            $('#template').html('<option value="">Please Select</option>');
        }
    });
</script>
@stop
