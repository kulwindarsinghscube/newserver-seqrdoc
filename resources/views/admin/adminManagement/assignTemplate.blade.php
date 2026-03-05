@extends('admin.layout.layout')
@section('content')
<?php 
$domain = \Request::getHost();
$subdomain = explode('.', $domain);
?>
	<div class="container">
    @if ($message = Session::get('success'))
        <div class="alert alert-success fade in alert-dismissible show">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true" style="font-size:20px">×</span>
            </button>
            {{ $message }}
        </div>
    @endif  
    <?php if($id != 1){ ?>
      <form action="{{ route('adminmaster.assignTemplateSave', $id) }}" method="POST">
        @csrf
        <input name="user_id" type="hidden" value="{{$id}}">
        <div class="row">
            <div class="col-lg-12">
                <h1 class="page-header"><i class="fa fa-fw fa fa-code-fork"></i> Assign Template
                <ol class="breadcrumb pull-right" style="background:transparent;font-size:14px;">{{ Breadcrumbs::render('adminmanagement') }}</ol>
                </h1>
            </div>
        </div>		
        <div class="row" style="margin-top:0 !important;">
            <div class="col-lg-11"><h4 class="htag">User: {{$fullname}}</h4></div>
            <div class="col-lg-1 text-center"><h4 class="htag"><a href="<?= route('adminmaster.index') ?>">Back</a></h4></div>
        </div>
        <div class="col-lg-12 col-sm-12 ">
          <div class="row">
              <!-- Nav tabs -->
              <div class="card">
                <ul class="nav nav-tabs" role="tablist">
                    <li role="presentation" class="active"><a href="#home" aria-controls="home" role="tab" data-toggle="tab">Template Maker ({{ count($templates) }} )</a></li>
                    <li role="presentation"><a href="#profile" aria-controls="profile" role="tab" data-toggle="tab">PDF2PDF ({{ count($pdf2pdf_templates) }} )</a></li>
                    <li role="presentation"><a href="#messages" aria-controls="messages" role="tab" data-toggle="tab">EXCEL2PDF ({{ count($excel2pdf_templates) }} )</a></li>
                </ul>
                <!-- Tab panes -->
                <div class="tab-content">
                    <div role="tabpanel" class="tab-pane active" id="home">
                      @if(count($templates) > 0)
                        <!-- Select All Checkbox -->
                        <div class="form-check">
                          <input id="select_all" class="form-check-input" type="checkbox">
                          <label for="select_all" class="form-check-label"><strong>Select All</strong></label>
                        </div>
                        <br>
                        @foreach($templates as $template)
                          <div class="form-check">
                              <input id="template_maker_{{ $template->id }}" class="form-check-input template-checkbox" type="checkbox" name="templates[]" value="{{ $template->id }}" {{ in_array($template->id, $templateIds->toArray()) ? 'checked' : '' }} >
                              <label for="template_maker_{{ $template->id }}" class="form-check-label">{{ $template->template_name }}</label>
                          </div>
                        @endforeach
                      @else 
                        <p>Template Not Found.</p>
                      @endif

                    </div>
                    <div role="tabpanel" class="tab-pane" id="profile">
                      @if(count($pdf2pdf_templates) > 0)

                        <div class="form-check">
                          <input id="select_all_pdf2pdf" class="form-check-input" type="checkbox">
                          <label for="select_all_pdf2pdf" class="form-check-label"><strong>Select All</strong></label>
                        </div>
                        <br>
                        @foreach($pdf2pdf_templates as $template)
                          <div class="form-check">
                              <input id="pdf2pdf_{{ $template->id }}" class="form-check-input pdf2pdf-template-checkbox" type="checkbox" name="pdf2pdftemplates[]" value="{{ $template->id }}" {{ in_array($template->id, $pdf2pdfIds->toArray()) ? 'checked' : '' }}>
                              <label for="pdf2pdf_{{ $template->id }}" class="form-check-label">{{ $template->template_name }}</label>
                          </div>
                        @endforeach

                      @else 
                        <p>Template Not Found.</p>
                      @endif

                    </div>
                    <div role="tabpanel" class="tab-pane" id="messages">
                     
                      @if(count($excel2pdf_templates) > 0)
                        <div class="form-check">
                          <input id="select_all_excel2pdf" class="form-check-input" type="checkbox">
                          <label for="select_all_excel2pdf" class="form-check-label"><strong>Select All</strong></label>
                        </div>
                        <br>
                        @foreach($excel2pdf_templates as $template)
                          <div class="form-check">
                              <input id="excel2pdf_{{ $template->id }}" class="form-check-input excel2pdf-template-checkbox" type="checkbox" name="excel2pdftemplates[]" value="{{ $template->id }}" {{ in_array($template->id, $excel2pdfIds->toArray()) ? 'checked' : '' }}>
                              <label for="excel2pdf_{{ $template->id }}" class="form-check-label">{{ $template->template_name }}</label>
                          </div>
                        @endforeach
                      @else 
                        <p>Template Not Found.</p>
                      @endif
                    </div>
                </div>
              </div>
            </div>
          
            <div class="clearfix"></div>
            <div class="row">
              <div class="col-lg-12 col-sm-12 "> <br />
                  <button class="btn btn-success" title="Save" type="submit">Save</button>
              </div>
            </div>
        </div>
      </form>
    <?php } ?>		
	</div> 
	
@stop
@section('script')
<script type="text/javascript"> 
  var subdomain='<?php echo $subdomain[0];?>';   
</script>

<script>
  $(document).ready(function () {

      
      // Select/Deselect All Checkboxes
      $('#select_all').change(function () {
        
          $('.template-checkbox').prop('checked', $(this).prop('checked'));
      });

      // If any checkbox is unchecked, uncheck "Select All"
      $('.template-checkbox').change(function () {
          if ($('.template-checkbox:checked').length === $('.template-checkbox').length) {
              $('#select_all').prop('checked', true);
          } else {
              $('#select_all').prop('checked', false);
          }
      });

      if ($('.template-checkbox:checked').length === $('.template-checkbox').length) {
          $('#select_all').prop('checked', true);
      } else {
          $('#select_all').prop('checked', false);
      }


      ///////////////////////////////////////////////////////////////////////////////////
      // Select/Deselect All Checkboxes PDF2PDF
      $('#select_all_pdf2pdf').change(function () {
        
          $('.pdf2pdf-template-checkbox').prop('checked', $(this).prop('checked'));
      });

      // If any checkbox is unchecked, uncheck "Select All"
      $('.pdf2pdf-template-checkbox').change(function () {
          if ($('.pdf2pdf-template-checkbox:checked').length === $('.pdf2pdf-template-checkbox').length) {
              $('#select_all_pdf2pdf').prop('checked', true);
          } else {
              $('#select_all_pdf2pdf').prop('checked', false);
          }
      });

      if ($('.pdf2pdf-template-checkbox:checked').length === $('.pdf2pdf-template-checkbox').length) {
          $('#select_all_pdf2pdf').prop('checked', true);
      } else {
          $('#select_all_pdf2pdf').prop('checked', false);
      }




      ///////////////////////////////////////////////////////////////////////////////////
      // Select/Deselect All Checkboxes EXCEL2PDF
      $('#select_all_excel2pdf').change(function () {
          $('.excel2pdf-template-checkbox').prop('checked', $(this).prop('checked'));
      });

      // If any checkbox is unchecked, uncheck "Select All"
      $('.excel2pdf-template-checkbox').change(function () {
          if ($('.excel2pdf-template-checkbox:checked').length === $('.excel2pdf-template-checkbox').length) {
              $('#select_all_excel2pdf').prop('checked', true);
          } else {
              $('#select_all_excel2pdf').prop('checked', false);
          }
      });


      if ($('.excel2pdf-template-checkbox:checked').length === $('.excel2pdf-template-checkbox').length) {
          $('#select_all_excel2pdf').prop('checked', true);
      } else {
          $('#select_all_excel2pdf').prop('checked', false);
      }
  });
</script>

@stop
@section('style')
<style type="text/css">

  #example_length label{
    display:none;
  }
  .help-inline{
    color:red;
    font-weight:normal;
  }

  .breadcrumb{
    background:#fff;
  }

  .breadcrumb a{
    color:#666;
  }

  .breadcrumb a:hover{
    text-decoration:none;
    color:#222;
  }

  .loader{
    display: table;
      background: rgba(0,0,0,0.5);
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      text-align: center;
      vertical-align: middle;
  }

  .loader-content{
    display:table-cell;
    vertical-align: middle;
    color:#fff;
  }
  .success2{
    border-left:3px solid #5CB85C;
  }
  .danger2{
    border-left:3px solid #D9534F;
  }

  #example td{
    word-break: break-all;
    padding:10px;
  }

  .nav-pills>li.active>a, .nav-pills>li.active>a:focus{
    background:#0052CC;
    color:#fff;
    border:1px solid #0052CC;
  }

  .nav-pills>li.active>a:hover, .nav-pills>li>a:focus, .nav-pills>li>a:hover
  {
    background:#fff;
    background:#ddd;
    border-radius:0;
    padding:10px 20px;
    color:#333;
    border-radius:2px;
    border:1px solid #ddd;
  }

  .nav-pills>li>a, .nav-pills>li>a
  {
    background:#fff;
    color:#aaa;
    border-radius:0;
    padding:10px 20px;
    border-radius:2px;
    margin-bottom:20px;
    border:1px solid #ddd;
  }

  #example_length label{
    display:none;
  }

  .active .success{
    background:#5CB85C !important;
    border:1px solid #5CB85C !important;
    color:#fff !important;
  }

  .active .failed{
    background:#D9534F !important;
    border:1px solid #D9534F !important;
    color:#fff !important;
  }

  /* Navbar */
  .nav-tabs {
    display: inline-flex;
    width: 100%;
    overflow-x: auto;
    border-bottom: 2px solid #DDD;
    -ms-overflow-style: none; /*// IE 10+*/
    overflow: -moz-scrollbars-none;/*// Firefox*/
  }
  .nav-tabs>li.active>a,
  .nav-tabs>li.active>a:focus,
  .nav-tabs>li.active>a:hover {
    border-width: 0;
  }
  .nav-tabs>li>a {
    border: none;
    color: #666;
  }
  .nav-tabs>li.active>a,
  .nav-tabs>li>a:hover {
    border: none;
    color: #4285F4 !important;
    background: transparent;
  }
  .nav-tabs>li>a::after {
    content: "";
    background: #4285F4;
    height: 2px;
    position: absolute;
    width: 100%;
    left: 0px;
    bottom: 1px;
    transition: all 250ms ease 0s;
    transform: scale(0);
  }
  .nav-tabs>li.active>a::after,
  .nav-tabs>li:hover>a::after {
    transform: scale(1);
  }
  .tab-nav>li>a::after {
    background: #21527d none repeat scroll 0% 0%;
    color: #fff;
  }
  .tab-pane {
    padding: 15px 0;
  }
  .tab-content {
    padding: 20px;
    max-height: 650px;
    overflow-y: scroll;
  }

  .nav-tabs::-webkit-scrollbar {
    display: none; /*Safari and Chrome*/
  }

  .nav-tabs {
    margin-left: 0;
  }
  .nav-tabs li {
    padding-bottom: 0px;
  }
  /* .card {
    background: #FFF none repeat scroll 0% 0%;
    box-shadow: 0px 1px 3px rgba(0, 0, 0, 0.3);
    margin-bottom: 30px;
  } */


</style>

@stop
