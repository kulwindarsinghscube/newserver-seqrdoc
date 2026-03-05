@extends('bverify_new.layout.layout')
@section('content')

<style>
.containerPdf {
  position: relative;
  width: 100%;
  overflow: hidden;
  padding-top: 56.25%; /* 16:9 Aspect Ratio */
    min-height: 1140px;
}

.responsive-iframe {
  position: absolute;
  top: 0;
  left: 0;
  bottom: 0;
  right: 0;
  width: 100%;
  height: 100%;

  border: none;
}

@media only screen and (max-width: 600px) {
  .containerPdf {
    min-height: 1140px;
  }
}

@media only screen and (max-width: 420px) {
  .containerPdf {
    margin-top: 20px;
    min-height: 550px;
  }

  .responsive-iframe {
    width: 420;
  }
}

</style>

<div class="container-fluid">
  <div class="row">
    <div class="col-lg-12 col-md-12 col-sm-12">
      <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12">
          <div style="    color: red;
              font-size: 23px;
              max-width: 600px;
              margin: auto;
              text-align: center;
              background-color: #fff;
              padding: 10px;
              border: 1px solid #dbdbdb;
              border-radius: 5px;">
              <img src="../backend/images/error.png" style="    max-width: 100px;" />
              <br>
              <?php echo $data['message']; ?>
              </div>
            </div>
      </div>
    </div>
  </div>
</div>

@stop
@section('script')


<script>

setTimeout(function(){
    location.reload();
}, 15000); // 15000 ms = 15 second


</script>
@stop
