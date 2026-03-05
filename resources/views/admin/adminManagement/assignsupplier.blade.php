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
{!! Form::open(['action' =>['admin\AdminManagementController@AssignSupplierSave',$id], 'method' => 'put'])!!}    
    <input name="id" type="hidden" value="{{$id}}">
    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header"><i class="fa fa-fw fa fa-code-fork"></i> Assign Supplier
            <ol class="breadcrumb pull-right" style="background:transparent;font-size:14px;">{{ Breadcrumbs::render('adminmanagement') }}</ol>
            </h1>
        </div>
    </div>		
    <div class="row" style="margin-top:0 !important;">
        <div class="col-lg-11"><h4 class="htag">User: {{$fullname}}</h4></div>
        <div class="col-lg-1 text-center"><h4 class="htag"><a href="<?= route('adminmaster.index') ?>">Back</a></h4></div>
    </div>
    <div class="row">
        <div class="col-lg-5 text-center text-primary">        
            <h4 class="text-success">Available Suppliers</h4>
            <select name="sbTwo" id="sbTwo" class="form-control" style="width: 100%;">
                <option value="0">Select Supplier</option>
                @foreach($SupplierData as $value)
                    <option value="{{ $value->id }}"  <?php if($supplier_id == $value->id) echo "selected"; ?>>{{ $value->company_name }}</option>
                @endforeach				
            </select>
        </div>
        <div class="clearfix"></div>
        <div class="col-lg-5 text-center"> <br />
            <button class="btn btn-success" title="Save" type="submit" id="select_all">Save</button>
        </div>
    </div>
{!! Form::close() !!}    
<?php } ?>		
	</div> 
	
@stop
@section('script')
<script type="text/javascript"> 
var subdomain='<?php echo $subdomain[0];?>';   

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
</style>
@stop
