@extends('admin.layout.layout')
@section('content')

<style type="text/css">
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
#toast-container > .toast {
background-image: none !important;
}
.htag{margin-top: -18px !important;margin-bottom: 16px !important;}
</style>
<div class="container"> 
    @if ($message = Session::get('success'))
        <div class="alert alert-success fade in alert-dismissible show">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true" style="font-size:20px">×</span>
            </button>
            {{ $message }}
        </div>
    @endif  
{!! Form::open(['action' =>['admin\pdf2pdf\TemplateDataController@AssignLabSave',$id], 'method' => 'put'])!!}    
    <input name="id" type="hidden" value="{{$id}}">
    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header"><i class="fa fa-fw fa fa-code-fork"></i> Assign Lab
            <ol class="breadcrumb pull-right" style="background:transparent;font-size:14px;">{{ Breadcrumbs::render('templatemangement') }}</ol>
            </h1>
        </div>
    </div>		
    <div class="row" style="margin-top:0 !important;">
        <div class="col-lg-11"><h4 class="htag">Template: {{$template_name}}</h4></div>
        <div class="col-lg-1 text-center"><h4 class="htag"><a href="{{route('pdf2pdf.templatelist')}}">Back</a></h4></div>
    </div>
    <div class="row">
        <div class="col-lg-5 text-center text-primary">        
            <h4 class="text-success">Available Labs</h4>
            <select name="sbOne" id="sbOne" multiple size="20" class="form-control">
                @foreach($LabData as $value)
                    <option value="{{ $value->id }}">{{ $value->lab_title }}</option>
                @endforeach				
            </select>
        </div>
        <div class="col-lg-1 text-center" style="margin-top:45px;">
            Move selected records<br />
            <button class="btn btn-primary" id="right">></button> <br /><br />
            <button class="btn btn-primary" id="left"><</button> <br /><br /><br />
            Move all records<br />
            <button class="btn btn-primary" id="rightall">>></button> <br /><br />
            <button class="btn btn-primary" id="leftall"><<</button> <br /><br />
        </div>
        <div class="col-lg-5 text-center text-primary" id="selected-items">        
            <h4 class="text-success">Assigned Labs</h4>
            <select name="sbTwo[]" id="sbTwo" multiple size="20" class="form-control">                			
                @foreach($LabDataAssigned as $value)
                    <option value="{{ $value->id }}">{{ $value->lab_title }}</option>
                @endforeach	
            </select>
        </div>
        <div class="col-lg-1 text-center"" style="margin-top:45px;">
            <button class="btn btn-primary" id="move-up">Up</button> <br /><br />
            <button class="btn btn-primary" id="move-down">Down</button>
        </div>
        <div class="col-lg-12 text-center"> <br />
            <button class="btn btn-success" title="Save" type="submit" id="select_all">Save</button>
        </div>
    </div>
{!! Form::close() !!}    
</div>
@stop



@section('script')
<?php 
        $domain = \Request::getHost();
        $subdomain = explode('.', $domain);
        ?>
<script type="text/javascript">
$(function () { 
    function moveItems(origin, dest) {
        $(origin).find(':selected').appendTo(dest);
    }
     
    function moveAllItems(origin, dest) {
        $(origin).children().appendTo(dest);
    }
    
    function moveUp() {
        $('#selected-items select :selected').each(function(i, selected) {
            if (!$(this).prev().length) return false;
            $(this).insertBefore($(this).prev());
        });
        $('#selected-items select').focus().blur();
    }

    function moveDown() {
        $($('#selected-items select :selected').get().reverse()).each(function(i, selected) {
            if (!$(this).next().length) return false;
            $(this).insertAfter($(this).next());
        });
        $('#selected-items select').focus().blur();
    }    
     
    $('#left').click(function (event) {
        event.preventDefault();
        moveItems('#sbTwo', '#sbOne');
    });
     
    $('#right').on('click', function (event) {
        event.preventDefault();
        moveItems('#sbOne', '#sbTwo');
    });
     
    $('#leftall').on('click', function (event) {
        event.preventDefault();
        moveAllItems('#sbTwo', '#sbOne');
    });
     
    $('#rightall').on('click', function (event) {
        event.preventDefault();
        moveAllItems('#sbOne', '#sbTwo');
    });
    $('#move-up').on('click', function (event) {
        event.preventDefault();
        moveUp();
    });
    $('#move-down').on('click', function (event) {
        event.preventDefault();
        moveDown();
    });
    $('#select_all').click(function() {
        $('#sbTwo option').prop('selected', true);
    });    
    
});


    
</script>	
@stop
