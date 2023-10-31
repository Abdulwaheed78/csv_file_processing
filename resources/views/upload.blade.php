
@extends('base')
@section('content')

<div class="content">
    <style>
         #csv_file {
            display: none;
        }

        .drag-and-drop {
            border: 2px dashed #ccc;
            padding: 50px;
            text-align: center;
            cursor: pointer;
        }
    </style>
    <div class="container">
        <!-- Button trigger modal -->
        <button id="messageButton" type="button" class="btn btn-primary" data-bs-toggle="modal"
            data-bs-target="#messageModal" style="display: none;">
            Launch demo modal
        </button>

        <!-- Modal -->
        <div class="modal fade" id="messageModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="messageTitle">Message</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" id="messageBody">
                        ...
                    </div>
                </div>
            </div>
        </div>

        @if (session('success'))
            <script>
                document.addEventListener("DOMContentLoaded", function() {
                    // JavaScript code to show a success message in the modal
                    var successMessage = '{{ session('success') }}';
                    var messageModal = new bootstrap.Modal(document.getElementById('messageModal'));
                    document.getElementById('messageTitle').textContent = 'Success';
                    document.getElementById('messageBody').textContent = successMessage;
                    messageModal.show();
                    setTimeout(function() {
                        messageModal.hide();
                    }, 3000); // Close the modal after 3 seconds
                });
            </script>
        @endif

        @if (session('error'))
            <script>
                document.addEventListener("DOMContentLoaded", function() {
                    // JavaScript code using Bootstrap here
                    // JavaScript code to show an error message in the modal
                    var errorMessage = '{{ session('error') }}';
                    var messageModal = new bootstrap.Modal(document.getElementById('messageModal'));
                    document.getElementById('messageTitle').textContent = 'Error';
                    document.getElementById('messageBody').textContent = errorMessage;
                    messageModal.show();
                    setTimeout(function() {
                        messageModal.hide();
                    }, 3000); // Close the modal after 3 seconds
                });
            </script>
        @endif
    </div>
    <div class="container mt-5">
        <div class="card">
            <div class="card-header">
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
                    <button type="submit" class="btn btn-primary">Upload</button>
                </form>
            </div>
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
@endsection

