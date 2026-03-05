@extends('webapp.layouts.layout')
@section('style')
<link rel="stylesheet" href="{{asset('webapp/webcamjs/css/style.css')}}">

<style>

  <?php
    
    $domain = \Request::getHost();
        $subdomain = explode('.', $domain);
        $awsS3Instances = \Config::get('constant.awsS3Instances');
    ?>

body{
  padding:0;
  background-image: linear-gradient(to bottom, rgba(34, 143, 176, 0.3), rgba(111, 128, 206, 0.4)), url("{{asset('webapp/images/bg2.jpg')}}");
}
.phonebg{
  background:url("{{asset('webapp/images/phonebg2.png')}}");
  padding:25px 25px;
  background-repeat:no-repeat;
  background-position:center center;
  height:766px;
  padding-top:105px;
}
 #preview {
  width: 318px;
  border: 1px solid #cecece;
  min-height: 239px;

 }
.alert-default{
  background:linear-gradient(to bottom, rgba(255, 255, 255, 0.8), rgba(255,255,255, 0.8));
  border:0px;
}

.thumbnail{
  border:0;
  background:transparent;
}

.cam-selecter {
    margin-bottom: 10px;
    text-align: center;
}

/*#camera-select {
    width: 100%;
    padding: 8px;
    font-size: 16px;
}*/


.list-group-css {
  width:320px;margin:0 auto;
}

.pdfpathDisplay {
  width: 39vw;
  height: 100vh;
  overflow: scroll;
}

.pdfpathDisplayIframe {
  width: 36vw;height: 108vh;pointer-events: none
}


.widthHeightDefault {
  width: 810px; 
  height: 780px;
}

#pdf-canvas {
  /*width: auto;*/
  width: 100%;
}


#placeholder {
  position: absolute; 
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;

  display: flex;
  align-items: center;
  justify-content: center;

  background: rgba(0, 0, 0, 0.7);
  color: white;
  font-size: 18px;

  font-weight: bold;
  z-index: 10;
}

.phoneBox {
  position:relative;height:554px;
}

#response-QR {
  /*display: in*/
}

#pdf-loader {
  display: none;
  text-align: center;
  color: #000000;
  font-size: 13px;
  /*line-height: 100px;*/
  height: 50px;
}


@media only screen and (max-width: 801px) {
  /*#preview {
    width: 318px;
    height: 100px;
  }*/

  .phonebg  {
    height: 850px;
  }

  .phoneBox {
    height:720px;
  }

}


@media only screen and (max-width: 767px) {
  

}


@media only screen and (max-width: 480px) {
  .list-group-css {
    width:290px;
  }

  .controls {
      width: 293px;
  }

  #preview{
    width: 288px;
    min-height: 218px;
  

  }

  .mobile-res {
      display: inline-block;
    }


  .pdfpathDisplay {
    width: 100%;
    height: 100%;
    overflow: scroll;
  }

  .pdfpathDisplayIframe {
    width: 100%;height: 100%;pointer-events: none
    width: 100%;height: 100%;pointer-events: none
  }
  
  .widthHeightDefault {
    width: 100%;
    height: 100%;
  }


  #pdf-canvas {
    width: 100% !important;
  }

}


@media only screen and (max-width: 414px) {

  .cam-selecter {
      width: auto;
  }

  .list-group-css {
        width: auto;
    }

    .controls {
        width: auto;
    }

    #preview{
      width: 302px;
      min-height: 210px;
      
    }
    #output {
      padding: 0px 15px;
    }

    .padding-res-0 {
      padding-right: 0px;
      padding-left: 0px;
    }


    

}

@media only screen and (max-width: 394px) {
    
    #preview {
      width: 245px;
    }

    .scanner-laser {
      margin: 20px;
    }


}


@media only screen and (max-width: 340px) {
 

    /*.scanner-laser {
      margin: 20px;
  }*/
}
@media only screen and (max-width: 360px) {
  .phonebg  {
    height: 750px;
  }
  .phoneBox {
    height:610px;
  }

  .scanner-laser {
      margin: 20px;
  }

  #preview{
    width: 250px;
    /*height: 200px*/
    
  }

  /*.laser-leftTop {
    margin-left: 0px;
  }

  .laser-leftBottom {
    margin-left: 0px;
  }*/

  
}


@media only screen and (max-width: 321px) {
  select#camera-select {
      font-size: 12px;
  }

}







</style>
@stop
@section('content')
  
<div class="" id="QR-Code" onload="disableContextMenu();" oncontextmenu="return false">   
  <div class="col-xs-12 padding-res-0">
    <div class="col-md-0 helper">
    </div>
    <div class="col-md-5 text-center phonebg col-md-offset-0">
      <div class="cam-selecter">
        <select class="form-control" id="camera-select"></select>
        {{-- <input type="file" id="fileInput" accept="image/*" /> --}}
      </div>
      <div class="phoneBox" style="">
        <div class="controls">
          <button title="Upload Image" class="btn btn-secondary hide" id="decode-img" type="button" data-toggle="tooltip"><span class="fa fa-upload"></span></button>
          <button title="Capture Image" class="btn btn-secondary disabled hide" id="grab-img" type="button" data-toggle="tooltip"><span class="fa fa-camera"></span></button>
          
          <button title="Pause" class="btn btn-secondary" id="pause" type="button" data-toggle="tooltip"><span class="fa fa-pause"></span></button>

          <button title="Start" class="btn btn-secondary" id="play" type="button" data-toggle="tooltip"><span class="fa fa-play fa-2x"></span></button>
        
          <button title="Stop streams" class="btn btn-secondary" id="stop" type="button" data-toggle="tooltip"><span class="fa fa-stop"></span></button>
        </div>
        <div class="" style="position: relative;display: inline-block;margin-top:28px">
          <video id="preview" style=""></video>

          <div id="placeholder">
            Please start camera
          </div>

        </div>
      
        <div class="" id="output">
          <div class="hidden">
            <img width="320" height="240" id="scanned-img" src="{{asset('webapp/images/blank.png')}}">
          </div>
          <div class="caption ">
            <h3></h3>
            <div class="list-group text-left list-group-css">
              <li class="list-group-item">
                <b>Status:</b>
                <span id="scanned-QR">Point camera to QR </span>
              </li>
              <li class="list-group-item hidden" id="qrcode">
              <b>QR Code:</b>
                <span id="response-QR">Press the <i class="fa fa-play fa-fw"></i> button to start </span>
              </li>
            </div>
            
            
            
          </div>
           
        </div>
      </div>
      <br>
    </div>
    <div class="col-md-6 col-md-offset-1" class="helper">
      <div class="helper">
      <br><br><br><br>
      <h1 style="color:#fff;font-family:roboto;">Start Scanning</h1>
      <h5 style="color:#fff">Follow these simple steps to scan your QR Code.</h5><br>
        <div class="alert alert-default">
          <strong>Step 1: </strong><i class="fa fa-plug fa-fw theme"></i> Plugin Webcam. 
        </div>
        <div class="alert alert-default">
          <strong>Step 2: </strong> Press <i class="fa fa-play fa-fw theme"></i> button to begin. 
        </div>
        <div class="alert alert-default">
          <strong>Step 3: </strong> Allow the browser to access camera <i class="fa fa-video-camera fa-fw theme"></i>.
        </div>
        <div class="alert alert-default">
          <strong>Step 4: </strong> Hold the <i class="fa fa-qrcode fa-fw theme"></i> QR Code infront of the webcam.
        </div>
        <div class="alert alert-default">
          <strong>Step 5: </strong> Wait for the scanner to scan the <i class="fa fa-qrcode fa-fw theme"></i> QR Code and display the data.
        </div>
      </div>
    </div>
    <div class="col-md-6 col-md-offset-1 thumbnail hidden">
      <div class="cardpadding">
        
        <div class="ajaxResponse">
          Scan a QR code to begin
        </div>
       

          <div claass="row">
            
            <div id="pdf-main-container" class="ajaxResponseV1">

              <div class="panel panel-info">
                <div class="panel-heading"><b>Document Information</b></div>
                  <div class="panel-body">
                    <div class="col-xs-12 padding-res-0">
                      <div claass="row">
                          <div class="col-md-5 col-xs-5"><label for="info1">Document ID</label></div>
                          <div class="col-md-1 col-xs-1"><label for="info1">:</label></div>
                          <div class="col-md-6 studentSerialNo" ></div>
                      </div>
                      
                      
                      <input type="hidden" name="pdfUrl" id="pdfUrl" value="'.$pathPDF.'">
                      

                    </div>
                  </div>
                
              </div>


              <div id="pdf-loader">Loading document ...</div>
              <div id="pdf-contents">
                
                <canvas id="pdf-canvas" width="794px" height="1122px"></canvas>
                <div id="page-loader"></div>
              </div>
            </div>
          </div>

       
      </div>
    </div>
  </div> 
</div>
@stop
@section('script')



<script src="https://cdn.jsdelivr.net/npm/@zxing/library@0.18.6/umd/index.min.js"></script>

<script>

    // var screensize= $( window ).width();
    // var screenHeight= $(document).innerHeight();

    //  jQuery(window).resize(function() {
    //         height = window.innerHeight;
    //         width = window.innerWidth;
    //         console.log('Height: '+height+'<br>Width: '+width);
    // });

    // alert(screensize);
    // alert(screensize +' X '+ screenHeight);
      var key = '';
      var pay = '';

      const videoElem = document.getElementById("preview");
      const output = document.getElementById("scanned-QR");
      const cameraSelect = document.getElementById("camera-select");
      const playButton = document.getElementById("play");
      const pauseButton = document.getElementById("pause");
      const stopButton = document.getElementById("stop");

      // Create ZXing QR Code reader instance
      const codeReader = new ZXing.BrowserQRCodeReader();
      let currentStream = null;

      let scanCooldown = false;
      
      // Toggle placeholder visibility
  function togglePlaceholder(show) {
    const placeholder = document.getElementById("placeholder"); // Make sure a placeholder element exists
    if (placeholder) {
      placeholder.style.display = show ? "flex" : "none";
    }
  }

  // Populate camera options on page load but do not start scanning
  function populateCameraOptions() {
    navigator.mediaDevices.getUserMedia({ video: true })
      .then(() => {
        codeReader.listVideoInputDevices().then((devices) => {
          devices.forEach((device) => {
            const option = document.createElement("option");
            option.value = device.deviceId;
            option.text = device.label || `Camera ${cameraSelect.length + 1}`;
            cameraSelect.appendChild(option);
          });
        });
      })
      .catch((err) => {
        alert(`Error accessing camera: ${err}`);
        togglePlaceholder(true);
      });
  }

  // Start scanning
  function startScanning(deviceId) {
    const constraints = {
      video: {
        deviceId: { exact: deviceId },
        width: { ideal: 1920 },
        height: { ideal: 1080 },
      },
    };

    navigator.mediaDevices.getUserMedia(constraints).then((stream) => {
      currentStream = stream;
      videoElem.srcObject = stream;
      togglePlaceholder(false); // Hide the placeholder
      codeReader.decodeFromVideoDevice(deviceId, videoElem, (result, err) => {
        // if (result) {
        //   output.textContent = `QR Text: ${result.text}`;
        //   key = result.text;
        //   ajaxCall(key, '1'); // Add your AJAX function
        // } else if (err && !(err instanceof ZXing.NotFoundException)) {
        //   console.error(err);
        // }

        if (result && !scanCooldown) {
          if (key !== result.text) {
            output.textContent = `QR Text: ${result.text}`;
            key = result.text;
            ajaxCall(key, '0');
            scanCooldown = true;
            // Cooldown for 5 seconds
            setTimeout(() => {
              scanCooldown = false;
            }, 5000);
          }
        }

      });
    });
  }

  // Stop the video stream
  function stopCamera() {
    if (currentStream) {
      currentStream.getTracks().forEach((track) => track.stop());
      videoElem.srcObject = null;
      currentStream = null;
      codeReader.reset();
      togglePlaceholder(true); // Show the placeholder
    }
  }

  // Pause the video stream
  function pauseCamera() {
    if (currentStream) {
      videoElem.pause();
      togglePlaceholder(true); // Show the placeholder
    }
  }

  // Play button click event
  playButton.addEventListener("click", () => {
    output.textContent = `Scanning...`;
    if (!currentStream) {
      const selectedDeviceId = cameraSelect.value;
      if (selectedDeviceId) {
        startScanning(selectedDeviceId);
      } else {
        alert("No camera selected!");
      }
    } else {
      videoElem.play();
      togglePlaceholder(false); // Hide the placeholder
    }
  });

  pauseButton.addEventListener("click", pauseCamera);
  stopButton.addEventListener("click", stopCamera);

  // Camera selection change
  cameraSelect.addEventListener("change", () => {
    const selectedDeviceId = cameraSelect.value;
    if (selectedDeviceId && currentStream) {
      stopCamera();
      startScanning(selectedDeviceId);
    }
  });

  // Initialize camera options on page load
  document.addEventListener("DOMContentLoaded", populateCameraOptions);



    // Handle image file input
    // fileInput.addEventListener("change", function (e) {
    //   const file = e.target.files[0];
    //   if (file) {
    //     const reader = new FileReader();
    //     reader.onload = function () {
    //       const img = new Image();
    //       img.onload = function () {
    //         codeReader.decodeFromImage(img).then((result) => {
    //           output.textContent = `QR Code detected from image: ${result.text}`;
    //           alert(`QR Code detected from image: ${result.text}`);
    //         }).catch((err) => {
    //           output.textContent = `Error: ${err}`;
    //         });
    //       };
    //       img.src = reader.result;
    //     };
    //     reader.readAsDataURL(file);
    //   }
    // });

// New Code End


// console.log(56)
if(sessionStorage.getItem('qrCodeKey') != null && sessionStorage.getItem('qrCodeKey') != 'null'){
  $('#QR-Code').hide()
  var qrCode = sessionStorage.getItem('qrCodeKey');
  ajaxCall(qrCode,'1');
}

$(window).focusin(function(){
  if(key != '' && pay !=''){
    console.log('focusin');
    ajaxCall(key,'1');
  }else{
    return false;
  }
}); 

$(window).focusout(function(){
  console.log('focusout');
  pay = '1';
}); 

function load(){
  console.log('load');
  $('.payment-url').click(function(e){

    e.preventDefault();
    $url = $(this).attr('href');
    window.open($url,'_blank');
    pay = '1';
  });
}

$('#play,#stop').click(function(){
    

  $('#qrcode').fadeOut();
  $('.phonebg').addClass('col-md-offset-0');
  $('.thumbnail').addClass('hidden');
  $('.helper').removeClass('hidden');
  key ='';
  pay = '';
});

function ajaxCall(val,update='0'){
  if(sessionStorage.getItem('qrCodeKey') != null && sessionStorage.getItem('qrCodeKey') != 'null'){
    sessionStorage.removeItem('qrCodeKey')
  }

  key = val;
  $('#scanned-QR').text('success');
  $('#response-QR').text(key);
  $('#qrcode').removeClass('hidden');
  $('#qrcode').fadeIn();
  console.log('ajaxCall');
  var qrcode = key;
  var token = "{{ csrf_token() }}";
  $.ajax({
      url: "{{ URL::route('dashboard.storeV1') }}",
      type: 'post',
      data:{
          qrcode:qrcode,
          _token:token,
          update_scan : update
      },
      success: function(data) {

       /* 🔥 PAYSTACK REDIRECT HANDLER (ADD THIS ONLY) */
    //         if (data.paystack_redirect === true) {
    //     window.location.href = data.url;
    //     return;
    // }

        $('#QR-Code').show()
        console.log(data);
        // $('.ajaxResponse').html(data);
        $('.phonebg').removeClass('col-md-offset-0');
        //$('.loader').removeClass('hidden');
        $('.thumbnail').removeClass('hidden');
        $('.thumbnail').removeClass('col-md-6');
        $('.thumbnail').removeClass('col-md-offset-1');
        $('.thumbnail').addClass('col-md-6 col-xs-12');
        $('.phonebg').addClass('hidden');
        $('.helper').addClass('hidden');

        $('#response-QR').text(qrcode);
        
        pay ='';
        load();
       

        if (data.serial_no) {
            
            $('.ajaxResponseV1').show();
            $('.ajaxResponse').hide();
            $('.studentSerialNo').text(data.serial_no);
            showPDF(data.pdfUrl);
        } else {

            $('.ajaxResponseV1').hide();
            $('.ajaxResponse').html(data);
        }
          
        
      }
  });
  return;
}

document.addEventListener('contextmenu', function(e) {
  e.preventDefault();
});

function disableContextMenu()
{
  window.frames["fraDisabled"].document.oncontextmenu = function(){alert("No way!"); return false;};   
  // Or use this
  // document.getElementById("fraDisabled").contentWindow.document.oncontextmenu = function(){alert("No way!"); return false;};;    
}  
  document.onkeypress = function (event) {
    event = (event || window.event);
    return keyFunction(event);
}
document.onmousedown = function (event) {
    event = (event || window.event);
    return keyFunction(event);
}
document.onkeydown = function (event) {
    event = (event || window.event);
    return keyFunction(event);
}
function keyFunction(event){
    //"F12" key
    if (event.keyCode == 123) {
        return false;
    }

    if (event.ctrlKey && event.shiftKey && event.keyCode == 73) {
        return false;
    }
    //"J" key
    if (event.ctrlKey && event.shiftKey && event.keyCode == 74) {
        return false;
    }
    //"S" key
    if (event.keyCode == 83) {
       return false;
    }
    //"U" key
    if (event.ctrlKey && event.keyCode == 85) {
       return false;
    }
    //F5
    if (event.keyCode == 116) {
       return false;
    }
}

let workerUrl = 'data:application/javascript;base64,' + btoa(`
self.addEventListener('message', (e) => {
  if(e.data==='hello'){
    self.postMessage('hello');
  }
  debugger;
  self.postMessage('');
});
`);
function checkIfDebuggerEnabled() {
  return new Promise((resolve) => {
    let fulfilled = false;
    let worker = new Worker(workerUrl);
    worker.onmessage = (e) => {
      let data = e.data;
      if (data === 'hello') {
        setTimeout(() => {
          if (!fulfilled) {
            resolve(true);
            worker.terminate();
          }
        }, 1);
      } else {
        fulfilled = true;
        resolve(false);
        worker.terminate();
      }
    };
    worker.postMessage('hello');
  });
}

checkIfDebuggerEnabled().then((result) => {
  if (result) {
    return false;
    // alert('browser DevTools is open');
  }else{
    // alert('browser DevTools is not open, unless you have deactivated breakpoints');
  }
});
</script>



  <!-- add pdf view js added -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.2.228/pdf.min.js"></script>

  <script>

  // var pdfUrlStr = $('#pdfUrl').val();
  // console.log(pdfUrlStr);

  // showPDF();

  var _PDF_DOC,
      _CURRENT_PAGE,
      _TOTAL_PAGES,
      _PAGE_RENDERING_IN_PROGRESS = 0,
      _CANVAS = document.querySelector('#pdf-canvas');

  // initialize and load the PDF
  async function showPDF(pdf_url) {
    // var pdf_url = $('#pdfUrl').val();
        console.log(pdf_url);
      document.querySelector("#pdf-loader").style.display = 'block';

      // get handle of pdf document
      try {
          _PDF_DOC = await pdfjsLib.getDocument({ url: pdf_url });
      }
      catch(error) {
          alert(error.message);
      }

      // total pages in pdf
      _TOTAL_PAGES = _PDF_DOC.numPages;
      
      // Hide the pdf loader and show pdf container
      document.querySelector("#pdf-loader").style.display = 'none';
      document.querySelector("#pdf-contents").style.display = 'block';
      // document.querySelector("#pdf-total-pages").innerHTML = _TOTAL_PAGES;

      // show the first page
      showPage(1);
  }
  
  // Declare a variable to store the rendering task
  let _RENDERING_TASK = null;

  async function showPage(page_no) {
      // Check if a rendering task is already in progress
      if (_PAGE_RENDERING_IN_PROGRESS) {
          console.log("Another rendering in progress. Please wait.");
          return;
      }

      _PAGE_RENDERING_IN_PROGRESS = 1;
      _CURRENT_PAGE = page_no;

      // Hide the canvas and show the loading indicator
      document.querySelector("#pdf-canvas").style.display = 'none';
      document.querySelector("#page-loader").style.display = 'block';

      try {
          // Get the page
          let page = await _PDF_DOC.getPage(page_no);

          // Calculate the scale
          let pdf_original_width = page.getViewport({ scale: 1 }).width;
          let scale_required = _CANVAS.width / pdf_original_width;

          // Get viewport
          let viewport = page.getViewport({ scale: scale_required });

          // Update canvas dimensions
          _CANVAS.height = viewport.height;

          // Set render context
          let render_context = {
              canvasContext: _CANVAS.getContext('2d'),
              viewport: viewport
          };

          // Cancel the previous rendering task if any
          if (_RENDERING_TASK) {
              _RENDERING_TASK.cancel();
          }

          // Start a new rendering task
          _RENDERING_TASK = page.render(render_context);

          await _RENDERING_TASK.promise;

          _PAGE_RENDERING_IN_PROGRESS = 0;

          // Show the canvas and hide the loader
          document.querySelector("#pdf-canvas").style.display = 'block';
          document.querySelector("#page-loader").style.display = 'none';
      } catch (error) {
          console.error("Error while rendering page: ", error);
          _PAGE_RENDERING_IN_PROGRESS = 0;
      }
  }


  </script>

  <!-- add pdf view js added-->


@stop
