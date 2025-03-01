<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>PDF Merge Tool</title>
    <!-- Bootstrap CSS -->
    <link href="<?php echo base_url(); ?>assets/css/bootstrap.min.css" rel="stylesheet">
    <script src="<?php echo base_url(); ?>assets/js/jquery-3.6.0.min.js"></script>

    <!-- Bootstrap JS -->
    <script src="<?php echo base_url(); ?>assets/js/bootstrap.min.js"></script>
    <!-- <script>
        alert('This is a pop-up message!');
    </script> -->

    <!-- Custom CSS -->
    <style>
        body {
            background-color: #f0f0f0;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        }

        .window {
            background-color: #ffffff;
            border: 1px solid #cccccc;
            border-radius: 4px;
            padding: 20px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
        }

        .window-header {
            background-color: #0078d7;
            color: white;
            padding: 10px;
            border-top-left-radius: 4px;
            border-top-right-radius: 4px;
            margin: -20px -20px 20px;
        }

        .window-title {
            margin: 0;
            font-size: 18px;
        }

        .btn-windows {
            background-color: #0078d7;
            color: white;
            border: none;
            border-radius: 2px;
            padding: 6px 12px;
        }

        .btn-windows:hover {
            background-color: #005a9e;
        }

        .form-control {
            border-radius: 2px;
        }

        .progress-bar {
            background-color: #0078d7;
        }
    </style>
</head>

<body>
    <div class="container mt-5">
        <div class="window">
            <div class="window-header">
                <h3 class="window-title">PDF Merger</h3>
            </div>
            <form id="pdfMergeForm">
                <div class="mb-3">
                        <label for="directory" class="form-label">Enter or Copy & Paste input files parent
                            Directory</label>
                        <input type="text" class="form-control" id="directory" name="directory"
                            placeholder="Enter the path to the directory">
                    </div>
                    <div class="mb-3">
                        <label for="outputFolder" class="form-label">Enter or Copy & Paste Output files
                            Directory</label>
                        <input type="text" class="form-control" id="outputFolder" name="outputFolder"
                            placeholder="Enter the output folder path">
                </div>
                <div class="tab mb-3">
                    <div class="row" style="border-bottom:1px solid #cccccc">
                        <div>
                            <button type="button" class="btn btn-light" id="tabBut1" style="border-radius:0;" onclick="showTab1()">Doc Settings</button>
                        </div>
                        <div>
                            <button type="button" class="btn btn-secondary" id="tabBut2" style="border-radius:0;" onclick="showTab2()">Color Settings</button>
                        </div>
                    </div>
                </div>
                <div class="tab1" style="display: block;">
                    
                    <div class="mb-3">
                        <label for="pageOrientation" class="form-label">Space Between Two Images (eg: 5, 10, 15)</label>
                        <input class="form-control" id="space" name="space" type="number" value="5" />
                    </div>
                    <div class="mb-3">
                        <label for="pageOrientation" class="form-label">Page Orientation&nbsp;&nbsp;</label>
                        <select class="form-select" id="pageOrientation" name="pageOrientation">
                            <option value="L">Landscape</option>
                            <option value="P">Portrait</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="mergeOption" class="form-label">Merge Option&nbsp;&nbsp;</label>
                        <select class="form-select" id="mergeOption" name="mergeOption">
                            <option value="side_by_side">Side by Side</option>
                            <option value="one_below_other">One Below Other</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="pageOrientation" class="form-label">Images Custom Name</label>
                        <input class="form-control" id="custom_name" name="custom_name" type="text" />
                    </div>

                    <!-- <button type="submit" class="btn btn-windows" data-bs-toggle="modal" data-bs-target="#progressModal">Merge PDFs</button> -->
                    <button type="submit" class="btn btn-windows mt-2" name="action" data-toggle="modal"
                        data-target="#progressModal" value="button1">Merge PDF's</button>
                    <button type="submit" class="btn btn-windows ml-3 mt-2" name="action" data-toggle="modal"
                        data-target="#progressModal" value="button2">Merge JPG's</button>
                    <button type="submit" class="btn btn-windows ml-3 mt-2" name="action" data-toggle="modal"
                        title="the merged images to a PDF after processing each folder" data-target="#progressModal"
                        value="button3">Merge JPG's & Add to PDF</button>

                    <button type="submit" class="btn btn-windows ml-3 mt-2" name="action" data-toggle="modal"
                        data-target="#progressModal" value="button4">Convert PDF to OCR PDF</button>
                    <button type="submit" class="btn btn-windows ml-3 mt-2" name="action" data-toggle="modal"
                        data-target="#progressModal" value="button5">Convert Images to OCR PDF</button>
                    <button type="submit" class="btn btn-windows ml-3 mt-2" name="action" data-toggle="modal"
                        data-target="#progressModal" value="button6">Convert PDF to Images</button>

                </div>
                <div class="tab2" style="display: none;">
                    <div class="mb-3">
                        <label for="pageOrientation" class="form-label">Brightness</label>
                        <input class="form-control" id="brightness" name="brightness" type="number" value="0" />
                    </div>

                    <div class="mb-3">
                        <label for="pageOrientation" class="form-label">Contrast</label>
                        <input class="form-control" id="contrast" name="contrast" type="number" value="0"/>
                    </div>

                    <div class="mb-3">
                        <label for="pageOrientation" class="form-label">Gamma</label>
                        <input class="form-control" id="gamma" name="gamma" type="text" value="1.0"/>
                    </div>

                    <button type="submit" class="btn btn-windows ml-3 mt-2" name="action" data-toggle="modal"
                        data-target="#progressModal" value="button7">Convert PDF/Images to Black & White</button>

                </div>







            </form>
        </div>
    </div>

    <!-- Modal for Progress -->
    <!-- <div class="modal fade" id="progressModal" tabindex="-1" aria-labelledby="progressModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="progressModalLabel">PDF Merge Progress</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="progress">
                    <div id="progressBar" class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                <div id="progressStatus" class="mt-2"></div>
            </div>
        </div>
    </div>
</div> -->

    <!-- Modal Structure -->
    <div class="modal fade" id="progressModal" tabindex="-1" aria-labelledby="progressModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="progressModalLabel">PDF Merge Progress</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="progress">
                        <div id="progressBar" class="progress-bar" role="progressbar" style="width: 0%;"
                            aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <div id="progressStatus" class="mt-2"></div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showTab1(){
            document.querySelector('.tab1').style.display = "block";
            document.querySelector('.tab2').style.display = "none";
            document.getElementById('tabBut1').className = "btn btn-light";
            document.getElementById('tabBut2').className = "btn btn-secondary";

        }
        function showTab2(){
            document.querySelector('.tab1').style.display = "none";
            document.querySelector('.tab2').style.display = "block";
            document.getElementById('tabBut1').className = "btn btn-secondary";
            document.getElementById('tabBut2').className = "btn btn-light";
        }
    </script>

    <script>
        document.getElementById('pdfMergeForm').addEventListener('submit', function (e) {
            e.preventDefault();

            var formData = new FormData(this);

            var progressBar = document.getElementById('progressBar');
            var progressStatus = document.getElementById('progressStatus');

            const action = document.activeElement.value;
            alert("Click ok to proceed!");

            if (action === 'button1') {

                fetch('<?php echo base_url("PdfMerge/merge_pdfs"); ?>', {
                    method: 'POST',
                    body: formData
                }).then(response => {
                    const reader = response.body.getReader();
                    const decoder = new TextDecoder();
                    let data = '';
                    let isCompleted = false;

                    function read() {
                        reader.read().then(({ done, value }) => {
                            if (done) {
                                // End of stream
                                if (isCompleted) {
                                    progressBar.style.width = '100%';
                                    progressStatus.textContent = 'Successfully completed!';
                                }
                                return;
                            }

                            // Decode the chunk and append it to the data
                            data += decoder.decode(value, { stream: true });

                            // Split data into individual JSON objects and process each one
                            let jsonObjects = data.split('\n').filter(line => line.trim() !== '');

                            for (let json of jsonObjects) {
                                try {
                                    let parsedData = JSON.parse(json);
                                    if (parsedData.status === 'processing') {
                                        progressBar.style.width = parsedData.progress + '%';
                                        progressBar.setAttribute('aria-valuenow', parsedData.progress);
                                        progressStatus.textContent = `Progress: ${parsedData.progress}%`;
                                    } else if (parsedData.status === 'completed') {
                                        isCompleted = true;
                                        progressStatus.textContent = 'Successfully completed!';
                                    } else {
                                        progressStatus.textContent = `Error: ${parsedData.message}`;
                                    }
                                } catch (e) {
                                    console.error('Error parsing JSON:', e);
                                }
                            }

                            read();
                        });
                    }
                    read();
                }).catch(error => {
                    progressStatus.textContent = `Error: ${error.message}`;
                });
            } else if (action === 'button2') {
                fetch('<?php echo base_url("PdfMerge/merge_images"); ?>', {
                    method: 'POST',
                    body: formData
                }).then(response => {
                    const reader = response.body.getReader();
                    const decoder = new TextDecoder();
                    let data = '';
                    let isCompleted = false;

                    function read() {
                        reader.read().then(({ done, value }) => {
                            if (done) {
                                // End of stream
                                if (isCompleted) {
                                    progressBar.style.width = '100%';
                                    progressStatus.textContent = 'Successfully completed!';
                                }
                                return;
                            }

                            // Decode the chunk and append it to the data
                            data += decoder.decode(value, { stream: true });

                            // Split data into individual JSON objects and process each one
                            let jsonObjects = data.split('\n').filter(line => line.trim() !== '');

                            for (let json of jsonObjects) {
                                try {
                                    let parsedData = JSON.parse(json);
                                    if (parsedData.status === 'processing') {
                                        progressBar.style.width = parsedData.progress + '%';
                                        progressBar.setAttribute('aria-valuenow', parsedData.progress);
                                        progressStatus.textContent = `Progress: ${parsedData.progress}%`;
                                    } else if (parsedData.status === 'completed') {
                                        isCompleted = true;
                                        progressStatus.textContent = 'Successfully completed!';
                                    } else {
                                        progressStatus.textContent = `Error: ${parsedData.message}`;
                                    }
                                } catch (e) {
                                    console.error('Error parsing JSON:', e);
                                }
                            }

                            read();
                        });
                    }
                    read();
                }).catch(error => {
                    progressStatus.textContent = `Error: ${error.message}`;
                });
            } else if (action === 'button3') {
                fetch('<?php echo base_url("PdfMerge/merge_images_pdf"); ?>', {
                    method: 'POST',
                    body: formData
                }).then(response => {
                    const reader = response.body.getReader();
                    const decoder = new TextDecoder();
                    let data = '';
                    let isCompleted = false;

                    function read() {
                        reader.read().then(({ done, value }) => {
                            if (done) {
                                // End of stream
                                if (isCompleted) {
                                    progressBar.style.width = '100%';
                                    progressStatus.textContent = 'Successfully completed!';
                                }
                                return;
                            }

                            // Decode the chunk and append it to the data
                            data += decoder.decode(value, { stream: true });

                            // Split data into individual JSON objects and process each one
                            let jsonObjects = data.split('\n').filter(line => line.trim() !== '');

                            for (let json of jsonObjects) {
                                try {
                                    let parsedData = JSON.parse(json);
                                    if (parsedData.status === 'processing') {
                                        progressBar.style.width = parsedData.progress + '%';
                                        progressBar.setAttribute('aria-valuenow', parsedData.progress);
                                        progressStatus.textContent = `Progress: ${parsedData.progress}%`;
                                    } else if (parsedData.status === 'completed') {
                                        isCompleted = true;
                                        progressStatus.textContent = 'Successfully completed!';
                                    } else {
                                        progressStatus.textContent = `Error: ${parsedData.message}`;
                                    }
                                } catch (e) {
                                    console.error('Error parsing JSON:', e);
                                }
                            }

                            read();
                        });
                    }
                    read();
                }).catch(error => {
                    progressStatus.textContent = `Error: ${error.message}`;
                });
            }
            else if (action === 'button4') {

                fetch('<?php echo base_url("PdfMerge/ocr_pdfs"); ?>', {
                    method: 'POST',
                    body: formData
                }).then(response => {
                    const reader = response.body.getReader();
                    const decoder = new TextDecoder();
                    let data = '';
                    let isCompleted = false;

                    function read() {
                        reader.read().then(({ done, value }) => {
                            if (done) {
                                // End of stream
                                if (isCompleted) {
                                    progressBar.style.width = '100%';
                                    progressStatus.textContent = 'Successfully completed!';
                                }
                                return;
                            }

                            // Decode the chunk and append it to the data
                            data += decoder.decode(value, { stream: true });

                            // Split data into individual JSON objects and process each one
                            let jsonObjects = data.split('\n').filter(line => line.trim() !== '');

                            for (let json of jsonObjects) {
                                try {
                                    let parsedData = JSON.parse(json);
                                    if (parsedData.status === 'processing') {
                                        progressBar.style.width = parsedData.progress + '%';
                                        progressBar.setAttribute('aria-valuenow', parsedData.progress);
                                        progressStatus.textContent = `Progress: ${parsedData.progress}%`;
                                    } else if (parsedData.status === 'completed') {
                                        isCompleted = true;
                                        progressStatus.textContent = 'Successfully completed!';
                                    } else {
                                        progressStatus.textContent = `Error: ${parsedData.message}`;
                                    }
                                } catch (e) {
                                    console.error('Error parsing JSON:', e);
                                }
                            }

                            read();
                        });
                    }
                    read();
                }).catch(error => {
                    progressStatus.textContent = `Error: ${error.message}`;
                });

            }
            else if (action === 'button5') {

                fetch('<?php echo base_url("PdfMerge/ocr_images"); ?>', {
                    method: 'POST',
                    body: formData
                }).then(response => {
                    const reader = response.body.getReader();
                    const decoder = new TextDecoder();
                    let data = '';
                    let isCompleted = false;

                    function read() {
                        reader.read().then(({ done, value }) => {
                            if (done) {
                                // End of stream
                                if (isCompleted) {
                                    progressBar.style.width = '100%';
                                    progressStatus.textContent = 'Successfully completed!';
                                }
                                return;
                            }

                            // Decode the chunk and append it to the data
                            data += decoder.decode(value, { stream: true });

                            // Split data into individual JSON objects and process each one
                            let jsonObjects = data.split('\n').filter(line => line.trim() !== '');

                            for (let json of jsonObjects) {
                                try {
                                    let parsedData = JSON.parse(json);
                                    if (parsedData.status === 'processing') {
                                        progressBar.style.width = parsedData.progress + '%';
                                        progressBar.setAttribute('aria-valuenow', parsedData.progress);
                                        progressStatus.textContent = `Progress: ${parsedData.progress}%`;
                                    } else if (parsedData.status === 'completed') {
                                        isCompleted = true;
                                        progressStatus.textContent = 'Successfully completed!';
                                    } else {
                                        progressStatus.textContent = `Error: ${parsedData.message}`;
                                    }
                                } catch (e) {
                                    console.error('Error parsing JSON:', e);
                                }
                            }

                            read();
                        });
                    }
                    read();
                }).catch(error => {
                    progressStatus.textContent = `Error: ${error.message}`;
                });

            }
            else if (action === 'button6') {

                fetch('<?php echo base_url("PdfMerge/extract_images_from_pdfs"); ?>', {
                    method: 'POST',
                    body: formData
                }).then(response => {
                    const reader = response.body.getReader();
                    const decoder = new TextDecoder();
                    let data = '';
                    let isCompleted = false;

                    function read() {
                        reader.read().then(({ done, value }) => {
                            if (done) {
                                // End of stream
                                if (isCompleted) {
                                    progressBar.style.width = '100%';
                                    progressStatus.textContent = 'Successfully completed!';
                                }
                                return;
                            }

                            // Decode the chunk and append it to the data
                            data += decoder.decode(value, { stream: true });

                            // Split data into individual JSON objects and process each one
                            let jsonObjects = data.split('\n').filter(line => line.trim() !== '');

                            for (let json of jsonObjects) {
                                try {
                                    let parsedData = JSON.parse(json);
                                    if (parsedData.status === 'processing') {
                                        progressBar.style.width = parsedData.progress + '%';
                                        progressBar.setAttribute('aria-valuenow', parsedData.progress);
                                        progressStatus.textContent = `Progress: ${parsedData.progress}%`;
                                    } else if (parsedData.status === 'completed') {
                                        isCompleted = true;
                                        progressStatus.textContent = 'Successfully completed!';
                                    } else {
                                        progressStatus.textContent = `Error: ${parsedData.message}`;
                                    }
                                } catch (e) {
                                    console.error('Error parsing JSON:', e);
                                }
                            }

                            read();
                        });
                    }
                    read();
                }).catch(error => {
                    progressStatus.textContent = `Error: ${error.message}`;
                });

            }
            else if (action === 'button7') {

                fetch('<?php echo base_url("PdfMerge/submitBlackWhite"); ?>', {
                    method: 'POST',
                    body: formData
                }).then(response => {
                    const reader = response.body.getReader();
                    const decoder = new TextDecoder();
                    let data = '';
                    let isCompleted = false;

                    function read() {
                        reader.read().then(({ done, value }) => {
                            if (done) {
                                // End of stream
                                if (isCompleted) {
                                    progressBar.style.width = '100%';
                                    progressStatus.textContent = 'Successfully completed!';
                                }
                                return;
                            }

                            // Decode the chunk and append it to the data
                            data += decoder.decode(value, { stream: true });

                            // Split data into individual JSON objects and process each one
                            let jsonObjects = data.split('\n').filter(line => line.trim() !== '');

                            for (let json of jsonObjects) {
                                try {
                                    let parsedData = JSON.parse(json);
                                    if (parsedData.status === 'processing') {
                                        progressBar.style.width = parsedData.progress + '%';
                                        progressBar.setAttribute('aria-valuenow', parsedData.progress);
                                        progressStatus.textContent = `Progress: ${parsedData.progress}%`;
                                    } else if (parsedData.status === 'completed') {
                                        isCompleted = true;
                                        progressStatus.textContent = 'Successfully completed!';
                                    } else {
                                        progressStatus.textContent = `Error: ${parsedData.message}`;
                                    }
                                } catch (e) {
                                    console.error('Error parsing JSON:', e);
                                }
                            }

                            read();
                        });
                    }
                    read();
                }).catch(error => {
                    progressStatus.textContent = `Error: ${error.message}`;
                });

            }

        });

    </script>

</body>

</html>