@extends('bverify.layout.layout')
@section('content')

<style>
.containerPdf {
  position: relative;
  width: 100%;
  overflow: hidden;
  /* padding-top: 56.25%; 16:9 Aspect Ratio */
    min-height: 1140px;
}
body {
  background-image: none !important;
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
/* tetsing */

#show-pdf-button {
	width: 150px;
	display: block;
	margin: 20px auto;
}

#file-to-upload {
	display: none;
}

#pdf-main-container {
	/* width: 400px; */
	/* margin: 20px auto; */
}

#pdf-loader {
	display: none;
	text-align: center;
	color: #999999;
	font-size: 13px;
	line-height: 100px;
	height: 100px;
}

#pdf-contents {
	display: none;
}

#pdf-meta {
	overflow: hidden;
	margin: 0 0 20px 0;
}

#pdf-buttons {
	float: left;
}

#page-count-container {
	float: right;
}

#pdf-current-page {
	display: inline;
}

#pdf-total-pages {
	display: inline;
}

#pdf-canvas {
	border: 1px solid rgba(0,0,0,0.2);
	box-sizing: border-box;
}

#page-loader {
	height: 100px;
	line-height: 100px;
	text-align: center;
	display: none;
	color: #999999;
	font-size: 13px;
}

</style>
<body>

<!-- <body onload="disableContextMenu();" oncontextmenu="return false"> -->
<br>
<div class="row">
	<div class="col-lg-3 col-md-3 col-sm-12">
		
	</div>

	<div class="col-lg-6 col-md-6 col-sm-12">
	  	<div id="pdfDiv" class="fade-in-right containerPdf" style="text-align: center; left: 100;">
		
			<div id="pdf-main-container">
				<div id="pdf-loader">Loading document ...</div>
				<div id="pdf-contents">
					
					<canvas style="width: 100%;" id="pdf-canvas" width="794px" height="1122px"></canvas>
				</div>
			</div>

		
		</div>
	</div>
</div>

</body>
@stop
@section('script')

<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.2.228/pdf.min.js"></script>

<script>

// var pdfUrlStr = '<?php echo $data['pdfUrl']; ?>';
// console.log(pdfUrlStr);
// showPDF(pdfUrlStr);

var pdfUrlStr = <?php echo json_encode($data['pdfUrl']); ?>;
console.log(pdfUrlStr);
showPDF(pdfUrlStr);

var _PDF_DOC,
    _CURRENT_PAGE,
    _TOTAL_PAGES,
    _PAGE_RENDERING_IN_PROGRESS = 0,
    _CANVAS = document.querySelector('#pdf-canvas');

// initialize and load the PDF
async function showPDF(pdf_url) {
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

// load and render specific page of the PDF
async function showPage(page_no) {
    _PAGE_RENDERING_IN_PROGRESS = 1;
    _CURRENT_PAGE = page_no;

    // disable Previous & Next buttons while page is being loaded
    // document.querySelector("#pdf-next").disabled = true;
    // document.querySelector("#pdf-prev").disabled = true;

    // while page is being rendered hide the canvas and show a loading message
    document.querySelector("#pdf-canvas").style.display = 'none';
    // document.querySelector("#page-loader").style.display = 'block';

    // update current page
    // document.querySelector("#pdf-current-page").innerHTML = page_no;
    
    // get handle of page
    try {
        var page = await _PDF_DOC.getPage(page_no);
    }
    catch(error) {
        alert(error.message);
    }

    // original width of the pdf page at scale 1
    var pdf_original_width = page.getViewport(1).width;
    
    // as the canvas is of a fixed width we need to adjust the scale of the viewport where page is rendered
    var scale_required = _CANVAS.width / pdf_original_width;

    // get viewport to render the page at required scale
    var viewport = page.getViewport(scale_required);

    // set canvas height same as viewport height
    _CANVAS.height = viewport.height;

    // setting page loader height for smooth experience
    // document.querySelector("#page-loader").style.height =  _CANVAS.height + 'px';
    // document.querySelector("#page-loader").style.lineHeight = _CANVAS.height + 'px';

    // page is rendered on <canvas> element
    var render_context = {
        canvasContext: _CANVAS.getContext('2d'),
        viewport: viewport
    };
        
    // render the page contents in the canvas
    try {
        await page.render(render_context);
    }
    catch(error) {
        alert(error.message);
    }

    _PAGE_RENDERING_IN_PROGRESS = 0;

    // re-enable Previous & Next buttons
    // document.querySelector("#pdf-next").disabled = true;
    // document.querySelector("#pdf-prev").disabled = true;

    // show the canvas and hide the page loader
    document.querySelector("#pdf-canvas").style.display = 'block';
    // document.querySelector("#page-loader").style.display = 'none';
    // document.querySelector("#pdf-buttons").style.display = 'none';
    

	
	


}


</script>

<script>
$('a[data-url^="dashboard"]').parent().addClass('active');
$("#name").fadeToggle(1000);
$("#description").fadeToggle(1500);
$("#card1").fadeToggle(2000);
$("#card2").fadeIn(2500);
$("#card3").fadeIn(3000);
$("#card4").fadeIn(3500);
$("#card5").fadeIn(4000);
$("#cardDownload").fadeIn(4000);
$("#pdfDiv").slideUp(1500).slideDown(2000);

$("#showMint").click(function(){
	if($('.mint-heading').is(':visible')){

  		$(".mint-details").fadeOut(500);
		$('.mint-heading').fadeOut(800);
	}else{
		$('.mint-heading').fadeIn(1500);
  		$(".mint-details").fadeToggle(2000);		
	}
  
});

$(document).contextmenu(function () {
	return false;
});




 // function saveFile(url) {
 //    // Get file name from url.
 //    var filename = url.substring(url.lastIndexOf("/") + 1).split("?")[0];
 //    var xhr = new XMLHttpRequest();
 //    xhr.responseType = 'blob';
 //    xhr.onload = function() {
 //        var a = document.createElement('a');
 //        a.href = window.URL.createObjectURL(xhr.response); // xhr.response is a blob
 //        a.download = filename; // Set the file name.
 //        a.target = '_blank';
 //        a.style.display = 'none';
        
 //        document.body.appendChild(a);
 //        a.click();
 //        delete a;
 //    };
 //    xhr.open('GET', url);
 //    xhr.send();

 //    }
</script>

<!--    <script type="text/jscript">
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
</script> -->
@stop
