@extends('base')
@section('content')
    <div class="content">
        <style>
            #csv_file {
                display: none;
            }

            .drag-and-drop {
                border: 2px dashed #0093d4;
                padding: 50px;
                text-align: center;
                cursor: pointer;
            }
        </style>

        <div class="container">
            <button id="messageButton" type="button" class="btn btn-primary" data-bs-toggle="modal"
                data-bs-target="#messageModal" style="display: none;">
                Launch demo modal
            </button>

            <!-- Modal -->
            <div class="modal fade" id="messageModal" tabindex="-1" aria-labelledby="exampleModalLabel"
                aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="messageTitle">Message</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body" id="messageBody">
                            ...
                        </div>
                    </div>
                </div>
            </div>

            @if (session('success'))
                <script>
                    document.addEventListener("DOMContentLoaded", function () {
                        var successMessage = '{{ session('success') }}';
                        var messageModal = new bootstrap.Modal(document.getElementById('messageModal'));
                        document.getElementById('messageTitle').textContent = 'Success';
                        document.getElementById('messageBody').textContent = successMessage;
                        messageModal.show();
                        setTimeout(function () {
                            messageModal.hide();
                        }, 3000); // Close the modal after 3 seconds
                    });
                </script>
            @endif

            @if (session('error'))
                <script>
                    document.addEventListener("DOMContentLoaded", function () {
                        var errorMessage = '{{ session('error') }}';
                        var messageModal = new bootstrap.Modal(document.getElementById('messageModal'));
                        document.getElementById('messageTitle').textContent = 'Error';
                        document.getElementById('messageBody').textContent = errorMessage;
                        messageModal.show();
                        setTimeout(function () {
                            messageModal.hide();
                        }, 3000); // Close the modal after 3 seconds
                    });
                </script>
            @endif
        </div>

        <div class="container mt-5">
            <div class="card border-primary">
                <div class="card-header bg-primary text-white">
                    <h1>Upload a CSV File</h1>
                </div>
                <div class="card-body">
                    <form action="{{ route('upload-csv') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3 drag-and-drop" id="dropArea">
                            <label for="csv_file" class="form-label">Drag and drop a CSV File here or click to
                                select</label>
                            <input type="file" class="form-control" id="csv_file" name="csv_file"
                                onchange="displayFileName()">
                        </div>
                        <div id="selectedFileName" style="font-weight: bold;"></div>
                        <button type="submit" class="btn btn-outline-primary">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                                class="bi bi-cloud-upload" viewBox="0 0 16 16">
                                <path
                                    d="M8 1a7 7 0 0 0-5.77 11.23A5.5 5.5 0 0 0 1 16h14a4 4 0 0 0 0-8H9.5A1.5 1.5 0 0 1 8 6a1.5 1.5 0 0 1 1.5-1.5c.55 0 1.05.28 1.35.74A3 3 0 0 1 14 9a3 3 0 0 1-3 3H5a1 1 0 0 1-1-1 1 1 0 0 1 2 0 1 1 0 0 0 2 0 3 3 0 0 0 3-3 3 3 0 0 0-3-3z" />
                                <path
                                    d="M7.47 9.47a.5.5 0 0 1 .36.15L8 10.29l.18-.18a.5.50 0 0 1 .65-.04l.15.14a.5.50 0 0 1 .04.65l-.04.06-2 2a.5.50 0 0 1-.85-.36V10a.5.50 0 0 1 .5-.5z" />
                                <path d="M8 3.5a.5.5 0 0 1 .5.5V10a.5.50 0 0 1-1 0V4a.5.50 0 0 1 .5-.5z" />
                            </svg>
                            Upload File
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <script>
            function displayFileName() {
                const fileInput = document.getElementById("csv_file");
                const selectedFile = fileInput.files[0];
                const selectedFileNameElement = document.getElementById("selectedFileName");

                if (selectedFile) {
                    selectedFileNameElement.textContent = "Selected File: " + selectedFile.name;
                } else {
                    selectedFileNameElement.textContent = "";
                }
            }

            const dropArea = document.getElementById("dropArea");

            dropArea.addEventListener("dragover", (e) => {
                e.preventDefault();
                dropArea.style.borderColor = "#007BFF";
            });

            dropArea.addEventListener("dragleave", () => {
                dropArea.style.borderColor = "#ccc";
            });

            dropArea.addEventListener("drop", (e) => {
                e.preventDefault();
                dropArea.style.borderColor = "#ccc";
                const files = e.dataTransfer.files;

                if (files.length > 0) {
                    const fileInput = document.getElementById("csv_file");
                    fileInput.files = files;
                    displayFileName(); // Display the selected file name
                }
            });

            // Listen for file selection via the input element
            document.getElementById("csv_file").addEventListener("change", displayFileName);
        </script>
    </div>
@endsection
