<!DOCTYPE html>
<html lang="en">
    <?php header("Access-Control-Allow-Origin: https://mainnet-apis.herokuapp.com"); ?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Verification</title>
    <!-- <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" rel="stylesheet"> -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.9.359/pdf.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" integrity="sha512-Avb2QiuDEEvB4bZJYdft2mNjVShBftLdPG8FJ0V7irTLQ8Uo0qcPxh4Plq7G5tGm0rU+1SPhVotteLpBERwTkw==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <style>
        /* General Styles */
        * {
            box-sizing: border-box;
        }

        :root {
            --clr-white: rgb(255, 255, 255);
            --clr-black: rgb(0, 0, 0);
            --clr-light: rgb(245, 248, 255);
            --clr-light-gray: rgb(196, 195, 196);
            --clr-blue: rgb(63, 134, 255);
            --clr-light-blue: rgb(171, 202, 255);
        }

        body {

            margin: 0;
            padding: 0;
            background-color: var(--clr-light);
            color: var(--clr-black);
            */ font-family: 'Montserrat', sans-serif;
            font-size: 1.125rem;
            min-height: 100vh;
            display: flex;
            justify-content: space-evenly;
            align-items: center;
            background-size: cover;
            background-attachment: fixed;

        }

        /* End General Styles */
        li {
            list-style-type: none;
        }

        .card{
               font-size: 15px;
             font-family: arial;
        }

        /* Upload Area */
        .upload-area {
            width: 100%;
            max-width: 35rem;
            background-color: var(--clr-white);
            /*box-shadow: 0 10px 60px rgb(218, 229, 255);*/
            border: 2px solid #122243;
            border-radius: 24px;
            padding: 2rem 1.875rem 5rem 1.875rem;
            margin-top: 10px;
            text-align: center;
            /* position: absolute; */

        }

        .upload-area--open {
            /* Slid Down Animation */
            animation: slidDown 500ms ease-in-out;
        }

        @keyframes  slidDown {
            from {
                height: 28.125rem;
                /* 450px */
            }

            to {
                height: 35rem;
                /* 560px */
            }
        }

        /* Header */
        .upload-area__header {}

        .upload-area__title {
            font-size: 1.8rem;
            font-weight: 500;
            margin-bottom: 0.3125rem;
        }

        .upload-area__paragraph {
            font-size: 0.9375rem;
            color: var(--clr-light-gray);
            margin-top: 0;
        }

        .upload-area__tooltip {
            position: relative;
            color: var(--clr-light-blue);
            cursor: pointer;
            transition: color 300ms ease-in-out;
        }

        .upload-area__tooltip:hover {
            color: var(--clr-blue);
        }

        .upload-area__tooltip-data {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -125%);
            min-width: max-content;
            background-color: var(--clr-white);
            color: var(--clr-blue);
            border: 1px solid var(--clr-light-blue);
            padding: 0.625rem 1.25rem;
            font-weight: 500;
            opacity: 0;
            visibility: hidden;
            transition: none 300ms ease-in-out;
            transition-property: opacity, visibility;
        }

        .upload-area__tooltip:hover .upload-area__tooltip-data {
            opacity: 1;
            visibility: visible;
        }

        /* Drop Zoon */
        .upload-area__drop-zoon {
            position: relative;
            height: 11.25rem;
            /* 180px */
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            border: 2px dashed #122243;
            /*var(--clr-light-blue)*/
            border-radius: 15px;
            margin-top: 2.1875rem;
            cursor: pointer;
            transition: border-color 300ms ease-in-out;
        }

        .upload-area__drop-zoon:hover {
            border-color: #1265c3;
            /*var(--clr-blue)*/
        }

        .drop-zoon__icon {
            display: flex;
            font-size: 3.75rem;
            color: var(--clr-blue);
            transition: opacity 300ms ease-in-out;
        }

        .drop-zoon__paragraph {
            font-size: 0.9375rem;
            color: var(--clr-light-gray);
            margin: 0;
            margin-top: 0.625rem;
            transition: opacity 300ms ease-in-out;
        }

        .drop-zoon:hover .drop-zoon__icon,
        .drop-zoon:hover .drop-zoon__paragraph {
            opacity: 0.7;
        }

        .drop-zoon__loading-text {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            display: none;
            color: var(--clr-light-blue);
            z-index: 10;
        }

        .drop-zoon__preview-image {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: contain;
            padding: 0.3125rem;
            border-radius: 10px;
            display: none;
            z-index: 1000;
            transition: opacity 300ms ease-in-out;
        }

        .drop-zoon:hover .drop-zoon__preview-image {
            opacity: 0.8;
        }

        .drop-zoon__file-input {
            display: none;
        }

        /* (drop-zoon--over) Modifier Class */
        .drop-zoon--over {
            border-color: var(--clr-blue);
        }

        .drop-zoon--over .drop-zoon__icon,
        .drop-zoon--over .drop-zoon__paragraph {
            opacity: 0.7;
        }

        /* (drop-zoon--over) Modifier Class */
        .drop-zoon--Uploaded {}

        .drop-zoon--Uploaded .drop-zoon__icon,
        .drop-zoon--Uploaded .drop-zoon__paragraph {
            display: none;
        }

        /* File Details Area */
        .upload-area__file-details {
            height: 0;
            visibility: hidden;
            opacity: 0;
            text-align: left;
            transition: none 500ms ease-in-out;
            transition-property: opacity, visibility;
            transition-delay: 500ms;
        }

        /* (duploaded-file--open) Modifier Class */
        .file-details--open {
            height: auto;
            visibility: visible;
            opacity: 1;
        }

        .file-details__title {
            font-size: 1.125rem;
            font-weight: 500;
            color: var(--clr-light-gray);
        }

        /* Uploaded File */
        .uploaded-file {
            display: flex;
            align-items: center;
            padding: 0.625rem 0;
            visibility: hidden;
            opacity: 0;
            transition: none 500ms ease-in-out;
            transition-property: visibility, opacity;
        }

        /* (duploaded-file--open) Modifier Class */
        .uploaded-file--open {
            visibility: visible;
            opacity: 1;
        }

        .uploaded-file__icon-container {
            position: relative;
            margin-right: 0.3125rem;
        }

        .uploaded-file__icon {
            font-size: 3.4375rem;
            color: var(--clr-blue);
        }

        .uploaded-file__icon-text {
            position: absolute;
            top: 1.5625rem;
            left: 50%;
            transform: translateX(-50%);
            font-size: 0.9375rem;
            font-weight: 500;
            color: var(--clr-white);
        }

        .uploaded-file__info {
            position: relative;
            top: -0.3125rem;
            width: 100%;
            display: flex;
            justify-content: space-between;
        }

        .uploaded-file__info::before,
        .uploaded-file__info::after {
            content: '';
            position: absolute;
            bottom: -0.9375rem;
            width: 0;
            height: 0.5rem;
            background-color: #ebf2ff;
            border-radius: 0.625rem;
        }

        .uploaded-file__info::before {
            width: 100%;
        }

        .uploaded-file__info::after {
            width: 100%;
            background-color: var(--clr-blue);
        }

        /* Progress Animation */
        .uploaded-file__info--active::after {
            animation: progressMove 800ms ease-in-out;
            animation-delay: 300ms;
        }

        @keyframes  progressMove {
            from {
                width: 0%;
                background-color: transparent;
            }

            to {
                width: 100%;
                background-color: var(--clr-blue);
            }
        }

        .uploaded-file__name {
            width: 100%;
            max-width: 6.25rem;
            /* 100px */
            display: inline-block;
            font-size: 1rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .uploaded-file__counter {
            font-size: 1rem;
            color: var(--clr-light-gray);
        }

        .headingTitle {
            position: absolute;
            top: 0;
            left: 0;
            margin: 0;
            padding: 15px;
            width: 100%;
            background: #0052CC;
            border: 0px;
            border-radius: 0px;
            box-shadow: 0px 1px 3px #555;
            color: #fff;
            font-size: 16px;
        }

        /* Custom CSS for Tablet Responsive */
        @media  screen and (min-width: 768px) and (max-width: 1024px) {


            .rows {
                display: flex;
                flex-direction: column;
                 
            }
        }
    </style>

</head>

<body style="background-image: url('../backend/images/blockchain_bg_image1.jpg');">
    <p class="headingTitle"><i class="fa fa-qrcode fa-fw"></i> SeQR Verification Page</p>


    <div class="container">
        <div class="row rows">
            <div class="col-sm-12 col-md-6" style="margin-top: 50px;">


                <!-- Upload Area -->
                <div id="uploadArea" class="upload-area">
                    <!-- Header -->
                    <div class="upload-area__header">
                        <h1 class="upload-area__title">Upload File For Verification</h1>
                        <p class="upload-area__paragraph">
                            File should be
                            <strong class="upload-area__tooltip">
                                Like
                                <span class="upload-area__tooltip-data"></span> <!-- Data Will be Comes From Js -->
                            </strong>
                        </p>
                    </div>
                    <!-- End Header -->

                    <!-- Drop Zoon -->
                    <div id="dropZoon" class="upload-area__drop-zoon drop-zoon">
                        <span class="drop-zoon__icon">
                            <i class='bx bxs-file-image'></i>
                        </span>
                        <p class="drop-zoon__paragraph">Drop your file here or Click to browse</p>
                        <span id="loadingText" class="drop-zoon__loading-text">Please Wait File is Processing...<img
                                src="../backend/images/loading.gif" /></span>
                        <img src="" alt="Preview Image" id="previewImage" class="drop-zoon__preview-image"
                            draggable="false">
                        <input type="file" id="fileInput" class="drop-zoon__file-input"
                            accept="application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document">
                    </div>
                    <!-- End Drop Zoon -->
                    <p id="progressMsg"></p>
                    <!-- File Details -->
                    <div id="fileDetails" class="upload-area__file-details file-details">
                        <h3 class="file-details__title">Uploaded File</h3>

                        <div id="uploadedFile" class="uploaded-file">
                            <div class="uploaded-file__icon-container">
                                <i class='bx bxs-file-blank uploaded-file__icon'></i>
                                <span class="uploaded-file__icon-text"></span> <!-- Data Will be Comes From Js -->
                            </div>

                            <div id="uploadedFileInfo" class="uploaded-file__info">
                                <span class="uploaded-file__name">Proejct 1</span>
                                <span class="uploaded-file__counter">0%</span>
                            </div>
                        </div>
                    </div>
                    <!-- End File Details -->
                </div>
                <!-- End Upload Area -->




            </div>

            <div class=" col-sm-12 col-md-6">

                <div class="upload-area" id="mintDataDiv" style="display:none ;    margin-top: 60px;">
                    <div class="col-lg-12 col-md-12 col-sm-12" style="background-color: #f0f0f0;">
                        <div class="row" style="margin: auto;border: 2px solid #dbdbdb;">


                            <div class="col-lg-12 col-md-12 col-sm-12 text-center"
                                style="background-color: orange;padding: 10px;color: #fff;margin-bottom: 10px;font-size: 17px;">
                                <b>DATA</b>

                            </div>
                            <div class="col-lg-12 col-md-1 col-sm-12 text-center">
                                <div class="card" style="margin: auto;" id="name">
                                    <div class="card-header"
                                        style="  word-wrap: break-word;background-color: #0052cc;color: #fff;">
                                        Document Type
                                    </div>
                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item" style="  word-wrap: break-word;"><b><span
                                                    id="bc_name"></span></b></li>
                                    </ul>
                                </div>
                            </div>


                            <div class="col-lg-12 col-md-1 col-sm-12 text-center">
                                <div class="card" style="margin: auto;" id="description">
                                    <div class="card-header"
                                        style="  word-wrap: break-word;background-color: #0052cc;color: #fff;">
                                        Description
                                    </div>
                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item" style="  word-wrap: break-word;"><b><span
                                                    id="bc_description"></span></b></li>
                                    </ul>
                                </div>
                            </div>

                            <div id="metaContainer"></div>




                            <div class="col-lg-12 col-md-1 col-sm-12 text-center">
                                <div class="card" style="margin: auto;" id="cardDownload">
                                    <div class="card-header"
                                        style="  word-wrap: break-word;background-color: #0052cc;color: #fff;">
                                        Download PDF
                                    </div>
                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item" style="  word-wrap: break-word;"><b><a href=""
                                                    id="bc_pdfUrl" target="_blank">Click here to download.</a></b></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-lg-12 col-md-12 col-sm-12 text-center"
                                style="padding: 5px;color: #000;    padding: 15px;border-top: 2px solid #dbdbdb;">
                                <b style="color: #fff;background-color: #3f51b5;border:1px solid #3f51b5; padding:5px;border-radius: 5px;font-size: 17px;cursor: pointer;"
                                    id="showMint">MINT Details</b>
                            </div>
                            <div class="col-lg-12 col-md-12 col-sm-12 text-center mint-heading" style="margin-top: 10px;
background-color: orange;
margin-bottom: 10px;
color: #fff;
padding: 10px;font-size: 17px; display:none ;">
                                <b>MINT DETAILS</b>
                            </div>
                            <div class="col-lg-12 col-md-1 col-sm-12 text-center mint-details" style=" ">
                                <div class="card" style="margin: auto;">
                                    <div class="card-header"
                                        style="  word-wrap: break-word;background-color: #0052cc;color: #fff;">
                                        Wallet Address
                                    </div>
                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item" style="  word-wrap: break-word;"><b><span
                                                    id="bc_walletID"></span></b></li>
                                    </ul>
                                </div>

                                <div class="card" style="margin: auto;">
                                    <div class="card-header"
                                        style="  word-wrap: break-word;background-color: #0052cc;color: #fff;">
                                        Polygon Transaction URL
                                    </div>
                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item" style="  word-wrap: break-word;text-align: left;"><a
                                                href="" id="hrefpolygonTxnUrl" target="_blank"
                                                title="Click to check on Polygon Network"><b><span
                                                        id="bc_polygonTxnUrl"></span></b></a></li>
                                    </ul>
                                </div>

                                <div class="card" style="margin: auto;">
                                    <div class="card-header"
                                        style="  word-wrap: break-word;background-color: #0052cc;color: #fff;">
                                        Smart Contract Address
                                    </div>
                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item" style="  word-wrap: break-word;"><b><span
                                                    id="bc_contractAddress"></span></b></li>
                                    </ul>
                                </div>

                                <div class="card" style="margin: auto;">
                                    <div class="card-header"
                                        style="  word-wrap: break-word;background-color: #0052cc;color: #fff;">
                                        Transaction Hash
                                    </div>
                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item" style="  word-wrap: break-word;"><b><span
                                                    id="bc_txnHash"></span></b></li>
                                    </ul>
                                </div>


                            </div>

                        </div>
                    </div>
                </div>

            </div>


        </div>
    </div>
    </div>




    <!-- End Mint Details -->


    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://unpkg.com/axios/dist/axios.min.js"></script>
    <script type="text/javascript">
        // Design By
        // - https://dribbble.com/shots/13992184-File-Uploader-Drag-Drop

        // Select Upload-Area
        const uploadArea = document.querySelector('#uploadArea')

        // Select Drop-Zoon Area
        const dropZoon = document.querySelector('#dropZoon');

        // Loading Text
        const loadingText = document.querySelector('#loadingText');

        // progressMsg
        const progressMsg = document.querySelector('#progressMsg');

        // Slect File Input 
        const fileInput = document.querySelector('#fileInput');

        // Select Preview Image
        const previewImage = document.querySelector('#previewImage');

        // File-Details Area
        const fileDetails = document.querySelector('#fileDetails');

        // Uploaded File
        const uploadedFile = document.querySelector('#uploadedFile');

        // Uploaded File Info
        const uploadedFileInfo = document.querySelector('#uploadedFileInfo');

        // Uploaded File  Name
        const uploadedFileName = document.querySelector('.uploaded-file__name');

        // Uploaded File Icon
        const uploadedFileIconText = document.querySelector('.uploaded-file__icon-text');

        // Uploaded File Counter
        const uploadedFileCounter = document.querySelector('.uploaded-file__counter');

        // ToolTip Data
        const toolTipData = document.querySelector('.upload-area__tooltip-data');

        // Images Types
        const filesTypes = [
            "pdf"
        ];

        // Append Images Types Array Inisde Tooltip Data
        toolTipData.innerHTML = [...filesTypes].join(',');

        // When (drop-zoon) has (dragover) Event 
        dropZoon.addEventListener('dragover', function (event) {
            // Prevent Default Behavior 
            event.preventDefault();

            // Add Class (drop-zoon--over) On (drop-zoon)
            dropZoon.classList.add('drop-zoon--over');
        });

        // When (drop-zoon) has (dragleave) Event 
        dropZoon.addEventListener('dragleave', function (event) {
            // Remove Class (drop-zoon--over) from (drop-zoon)
            dropZoon.classList.remove('drop-zoon--over');
        });

        // When (drop-zoon) has (drop) Event 
        dropZoon.addEventListener('drop', function (event) {
            // Prevent Default Behavior 
            event.preventDefault();

            // Remove Class (drop-zoon--over) from (drop-zoon)
            dropZoon.classList.remove('drop-zoon--over');

            // Select The Dropped File
            const file = event.dataTransfer.files[0];

            // Call Function uploadFile(), And Send To Her The Dropped File :)
            uploadFile(file);
        });

        // When (drop-zoon) has (click) Event 
        dropZoon.addEventListener('click', function (event) {
            // Click The (fileInput)
            fileInput.value = "";
            fileInput.click();
        });

        // When (fileInput) has (change) Event 
        fileInput.addEventListener('change', function (event) {
            // Select The Chosen File
            const file = event.target.files[0];
            // Call Function uploadFile(), And Send To Her The Chosen File :)
            uploadFile(file);
        });

        // Upload File Function
        function uploadFile(file) {
            // Show Loading-text
            // loadingText.style.display = "none";
            // console.log(file);

            // FileReader()
            const fileReader = new FileReader();
            // File Type 
            const fileType = file.type;
            // File Size 
            const fileSize = file.size;

            // If File Is Passed from the (File Validation) Function
            if (fileValidate(fileType, fileSize)) {
                // Add Class (drop-zoon--Uploaded) on (drop-zoon)
                dropZoon.classList.add('drop-zoon--Uploaded');

                // Show Loading-text
                // Show Loading-text
                progressMsg.style.display = "block";
                $('#progressMsg').html("<span style='color:#767676;'>Please Wait File is Processing...</span><img src='../backend/images/loading.gif'/>");
                $('#mintDataDiv').hide();
                // loadingText.style.display = "block";
                // Hide Preview Image
                previewImage.style.display = 'none';

                // Remove Class (uploaded-file--open) From (uploadedFile)
                uploadedFile.classList.remove('uploaded-file--open');
                // Remove Class (uploaded-file__info--active) from (uploadedFileInfo)
                uploadedFileInfo.classList.remove('uploaded-file__info--active');


                const formData = new FormData();
                // console.log(file);
                formData.append("file", file);

                var envValue = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiJkaWQ6ZXRocjoweEZiNmI4NzhmNmUwMEY3MDdhMGZkM0JGNjIyN0M1MzI3ZDA3OWUzYzUiLCJpc3MiOiJuZnQtc3RvcmFnZSIsImlhdCI6MTY5ODMwMzczNDIxMSwibmFtZSI6Imhvc3Rpbmcgc2VjdXJlIGRvY3MgZmlsZXMifQ.9Lbc93JLoNSw3LKnV8aLKEMPasq0fPwKnpoU_7iBKr0';

                // Setting headers For The API Request
                const headers = {
                    Authorization: `Bearer ${envValue}`,
                    'Content-Type': 'application/pdf',
                };

                // let tokenId = null;

                async function start() {
                    //Making The API Request To Upload The File And Generate The IPFS hash 
                    //const resFile = await axios.post(`https://api.nft.storage/upload/`, file, { headers });                   
                    // const resFile = await axios.post(`https://mainnet-apis.herokuapp.com/v1/mainnet/getFileHash`, file, { headers });
                    // // var resFile = '';
                    // return resFile;

                    
                    var url = window.location.host;
                    var subdomain = url.split('.')
                    $.ajax({
                        url: 'https://'+subdomain[0]+'.seqrdoc.com/api/callPdfData',
                        type: 'POST',
                        dataType: 'json',
                        data: formData,
                        processData: false,
                        contentType: false,
                        cache: false,
                        success: function (response) {
                            console.log(response);
                            console.log(response.response.pinataIpfsHash);



                            var url = window.location.host;
                            var subdomain = url.split('.')
                            // alert(subdomain[0]);

                            const formDataNew = new FormData();
                            formDataNew.append("pdfData", response.response.pinataIpfsHash);
                            // formDataNew.append("pdfData", 'TESTPDFDATA');
                            //console.log('https://'+subdomain[0]+'.seqrdoc.com/api/verifyPdf');
                            $('.drop-zoon__paragraph').css('display', 'none');
                             $('#metaContainer').empty();
                            $.ajax({
                                url: 'https://'+subdomain[0]+'.seqrdoc.com/api/verifyV1Pdf',
                                type: 'POST',
                                dataType: 'json',
                                data: formDataNew,
                                processData: false,
                                contentType: false,
                                cache: false,
                                success: function (data, textStatus, xhr) {

                                    // alert(data.success);
                                    console.log(data);
                                    if (data.success == true) {
                                        $('#mintDataDiv').show();
                                        $('#progressMsg').html("File Upload Verified Successfully !");
                                        $('#progressMsg').css("color", "green");
                                        $('.drop-zoon__paragraph').css('display', 'block');
                                        $('#bc_name').html(data.name);
                                        $('#bc_description').html(data.description);

                                        $('#bc_walletID').html(data.walletID);
                                        $('#bc_polygonTxnUrl').html(data.polygonTxnUrl);
                                        $('#bc_txnHash').html(data.txnHash);
                                        $('#bc_contractAddress').html(data.contractAddress);
                                        //  $('#bc_pdfUrl').html(data.pdfUrl);
                                        $("#hrefpolygonTxnUrl").attr("href", data.polygonTxnUrl);
                                        $("#bc_pdfUrl").attr("href", data.pdfUrl);
                                        var metadata = data.metadata;
                                        let dLen = metadata.length;

                                        for (let i = 0; i < dLen; i++) {
                                            //console.log(metadata[i]['key']);

                                            $('#metaContainer').append('<div class="col-lg-12 col-md-1 col-sm-12 text-center">' +
                                                '<div class="card" style="margin: auto;" id="">' +
                                                '<div class="card-header"  style="  word-wrap: break-word;background-color: #0052cc;color: #fff;">' +
                                                metadata[i]['key'] +
                                                '</div>' +
                                                '<ul class="list-group list-group-flush">' +
                                                '<li class="list-group-item" style="  word-wrap: break-word;"><b>' + metadata[i]['value'] + '</b></li>' +
                                                '</ul>' +
                                                '</div>' +
                                                '</div>');
                                        }



                                    } else if (data.success == false) {
                                        $('#progressMsg').html(data.message);
                                        $('#progressMsg').css("color", "indianred");
                                        $('.drop-zoon__paragraph').css('display', 'block');
                                    }

                                },
                                error: function (xhr, textStatus, errorThrown) {
                                    console.log('Error in Operation');
                                }
                            });

                        },
                        error: function (error) {
                              console.log(error.responseJSON.message);
                        }
                    });

                    
                }
                // console.log(start().value);
                start().then(function (data) {

                    
                });

            } else { // Else
                this; // (this) Represent The fileValidate(fileType, fileSize) Function
            };
        };

        // Progress Counter Increase Function
        function progressMove() {
            // Counter Start
            let counter = 0;

            // After 600ms 
            setTimeout(() => {
                // Every 100ms
                let counterIncrease = setInterval(() => {
                    // If (counter) is equle 100 
                    if (counter === 100) {
                        // Stop (Counter Increase)
                        clearInterval(counterIncrease);
                    } else { // Else
                        // plus 10 on counter
                        counter = counter + 10;
                        // add (counter) vlaue inisde (uploadedFileCounter)
                        uploadedFileCounter.innerHTML = `${counter}%`
                    }
                }, 100);
            }, 600);
        };


        // Simple File Validate Function
        function fileValidate(fileType, fileSize) {
            // File Type Validation
            let isImage = filesTypes.filter((type) => fileType.indexOf(`application/${type}`) !== -1);

            // If The Uploaded File Type Is 'jpeg'
            if (isImage[0] === 'pdf') {
                // Add Inisde (uploadedFileIconText) The (jpg) Value
                uploadedFileIconText.innerHTML = 'pdf';
            } else { // else
                // Add Inisde (uploadedFileIconText) The Uploaded File Type 
                uploadedFileIconText.innerHTML = isImage[0];
            };

            // If The Uploaded File Is An Image
            if (isImage.length !== 0) {
                // Check, If File Size Is 5MB or Less
                if (fileSize <= 10000000) { // 5MB :)
                    return true;
                } else { // Else File Size
                    return alert('Please Your File Should be 5 Megabytes or Less');
                };
            } else { // Else File Type 
                return alert('Please make sure to upload An Image File Type');
            };
        };

        // :)
    </script>






    <script>


        //    ========================================= GET THE DATA FROPM PDF FILE


        //             document.getElementById('file-input').addEventListener('change', async function (e) {
        //                 const file = e.target.files[0];

        //                 const reader = new FileReader();
        //                 reader.onload = async function (event) {
        //                     const typedarray = new Uint8Array(event.target.result);
        //                     const pdf = await pdfjsLib.getDocument(typedarray).promise;

        //                     // Fetch the first page of the PDF
        //                     const page = await pdf.getPage(1);

        //                     // Get the text content of the page
        //                     const textContent = await page.getTextContent();

        //                     // Extract a specific item (change index as needed)
        //                     const specificItem = textContent.items;
        //   console.log(JSON.stringify(specificItem,null,2))
        //                     // Display the extracted specific item
        //                    document.write(specificItem);
        //                 };

        //                 reader.readAsArrayBuffer(file);
        //             });


    </script>

</html><?php /**PATH C:\inetpub\vhosts\seqrdoc.com\httpdocs\demo\resources\views/bverify_v1.blade.php ENDPATH**/ ?>