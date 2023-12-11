@extends('base')

@section('content')
    <div class="container">
        <!-- Button trigger modal -->
        <button id="messageButton" type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#messageModal"
            style="display: none;">
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
    <div class="content">
        <div class="container">

        </div>
        <div class="container mt-5">
            <p class="text-primary">Files: {{ $fileCount }}</p>
            <div class="table-responsive">
                <table class="table">
                    <thead >
                        <tr class="bg-primary text-white">
                            <th>File Name</th>
                            <th>Last Opened</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($fileInfo as $file => $info)
                            <tr>
                                <td>
                                    <a style="color: black; text-decoration: none;"
                                        href="{{ route('show-csv-content', ['fileName' => $file]) }}">{{ $file }}</a>
                                </td>
                                <td>{{ $info['last_opened'] }}</td>
                                <td>
                                    <a style="color: red; text-decoration: none;"
                                        href="{{ route('deleteFile', ['filename' => $file]) }}">Delete</a>
                                    <span style="margin-left: 10px;">
                                        <a style="color: blue; text-decoration: none;"
                                            href="{{ route('download-prepared-csv', ['filename' => $file]) }}">Download</a>
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
