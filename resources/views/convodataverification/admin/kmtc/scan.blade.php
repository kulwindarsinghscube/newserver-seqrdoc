@extends('admin.layout.layout')

@section('content')
    <style>
        #video {
            border: 1px solid black;
            width: 100%;
            height: 100%;
        }

        #photo {
            border: 1px solid black;
            width: 100%;
            height: 100%;
        }

        #canvas {
            display: none;
        }

        .camera {
            position: relative;
            width: 340px;
            display: inline-block;
        }

        .camera img {
            position: absolute;
            top: 30px;
            left: 10px;
            width: 93%;
            height: 100%;
            pointer-events: none;
            filter: invert(1);
        }

        .output {
            width: 340px;
            display: inline-block;
        }

        #startbutton {
            display: block;
            position: relative;
            margin-left: auto;
            margin-right: auto;
            bottom: 36px;
            padding: 5px;
            background-color: #6a67ce;
            border: 1px solid rgba(255, 255, 255, 0.7);
            font-size: 14px;
            color: rgba(255, 255, 255, 1.0);
            cursor: pointer;
        }

        .contentarea {
            font-size: 16px;
            font-family: Arial;
            text-align: center;
        }

        .captured-images {
            margin-top: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .captured-images img {
            width: 320px;
            height: 240px;
            margin-bottom: 10px;
        }

        #messages {
            font-size: 16px;
            font-family: Arial;
            color: #333;
            margin-top: 10px;
        }
    </style>

    </head>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <body>
        <!-- Button to trigger the modal -->
        <div class="contentarea">
            <h1>Verify Student</h1>
            <button class="btn btn-primary" data-toggle="modal" data-target="#photoModal">
                Verify Student
            </button>
        </div>

        <!-- Modal -->
        <div class="modal fade" id="photoModal" tabindex="-1" aria-labelledby="photoModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="photoModalLabel">Capture Photo with Webcam</h5>
                        <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="text" name="prn" id="prn"><br>
                        <!-- Camera and photo preview -->
                        <div class="camera">
                            <video id="video">Video stream not available.</video>
                            {{-- <img src="file:///C:/Users/admin/Downloads/human_shape.png" alt="Overlay Image"> --}}
                        </div>
                        <div>
                            <button id="startbutton">Take Photo</button>
                        </div>
                        <canvas id="canvas"></canvas>
                        <div class="output">
                            <img id="photo" alt="Captured image will appear here.">
                        </div>
                        <div class="captured-images" id="capturedImagesContainer"></div>
                    </div>
                </div>
            </div>
        </div>
    @stop

    @section('script')
        <!-- Bootstrap 5 JS (with Popper) -->

        <script>
            /* JS comes here */
            (function() {
                var width = 320; // We will scale the photo width to this
                var height = 0; // This will be computed based on the input stream

                var streaming = false;

                var video = null;
                var canvas = null;
                var photo = null;
                var startbutton = null;

                var attempts = 0; // Count the number of attempts
                var intervalID = null; // Store the interval ID to stop it later

                function startup() {
                    video = document.getElementById('video');
                    canvas = document.getElementById('canvas');
                    photo = document.getElementById('photo');
                    startbutton = document.getElementById('startbutton');

                    navigator.mediaDevices.getUserMedia({
                            video: true,
                            audio: false
                        })
                        .then(function(stream) {
                            video.srcObject = stream;
                            video.play();
                        })
                        .catch(function(err) {
                            console.log("An error occurred: " + err);
                        });

                    video.addEventListener('canplay', function(ev) {
                        if (!streaming) {
                            height = video.videoHeight / (video.videoWidth / width);

                            if (isNaN(height)) {
                                height = width / (4 / 3);
                            }

                            video.setAttribute('width', width);
                            video.setAttribute('height', height);
                            canvas.setAttribute('width', width);
                            canvas.setAttribute('height', height);
                            streaming = true;
                        }
                    }, false);

                    startbutton.addEventListener('click', function(ev) {
                        takepicture();
                        ev.preventDefault();
                    }, false);

                    clearphoto();
                }

                function clearphoto() {
                    var context = canvas.getContext('2d');
                    context.fillStyle = "#AAA";
                    context.fillRect(0, 0, canvas.width, canvas.height);

                    var data = canvas.toDataURL('image/png');
                    photo.setAttribute('src', data);
                }

                function takepicture() {
                    var context = canvas.getContext('2d');
                    if (width && height && attempts < 5) {
                        canvas.width = width;
                        canvas.height = height;
                        context.drawImage(video, 0, 0, width, height);

                        var data = canvas.toDataURL('image/png');
                        photo.setAttribute('src', data);
                        attempts++;
                        var formData = new FormData();
                        formData.append('image', data); // Attach the base64 image to the form data
                        formData.append('attempt', attempts); // Optionally, include an attempt count
                        formData.append('prn', $('#prn').val());

                        // Send the image to the server via AJAX
                        $.ajax({
                            url: '{{ route('convo_student.upload_verify_image') }}', // Your backend endpoint (adjust as needed)
                            type: 'POST',
                            data: formData,
                            dataType: "JSON",
                            processData: false, // Don't process the data
                            contentType: false, // Don't set content type (FormData handles it)
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr(
                                    'content') // Include CSRF token in the header
                            },
                            success: function(response) {
                                console.log(response);
                               if(response.Status == 1){
                                    alert("User Verified Successfully");
                                    $('#photoModal').modal('hide');
                               }else{
                                 alert("User Not Verified Try Again");
                               }
                            },
                            error: function(xhr, status, error) {
                                console.error('Image upload failed:', error);
                            }
                        });

                        var imagesContainer = document.getElementById('capturedImagesContainer');
                        var newImageDiv = document.createElement('div');
                        var newImage = document.createElement('img');
                        newImage.setAttribute('src', data);
                        newImage.setAttribute('alt', 'Captured Image');
                        // newImageDiv.appendChild(newImage);
                        imagesContainer.appendChild(newImageDiv);
                    } else {
                        clearphoto();
                    }
                }

                window.addEventListener('load', startup, false);
            })();
        </script>
    @stop
