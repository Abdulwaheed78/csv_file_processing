@extends('base')

@section('content')
<div class="content">
      <!--styling of the page-->
  <style>
    @media (max-width: 767px) {
        #bulkActionSelect {
            width: 55% !important;
            /* Set the width to 55% for screens with a max width of 767px */
        }

        .container {
            margin-top: 2rem !important;
            /* Adjust the margin as needed for mobile screens */
        }
    }

    .table-responsive::-webkit-scrollbar {
        width: 12px;
        /* Width of the scrollbar */
    }

    .table-responsive::-webkit-scrollbar-thumb {
        background: #888;
        /* Color of the thumb (the moving part) */
    }

    .table-responsive::-webkit-scrollbar-track {
        background: #ddd;
        /* Color of the track (the non-moving part) */
    }

    .table thead th {
        margin-bottom: 0;
    }

    table {
        border-collapse: collapse;
        width: 100%;
    }

    table,
    th,
    td {
        border: 1px solid black;
    }

    th,
    td {
        padding: 8px;
        text-align: center;
    }

    th {
        background-color: #f2f2f2;
        font-weight: bold;
    }

    .container {
        margin-top: 5rem;
    }

    .selected-row {
        display: none;
    }

    .checkbox-options label {
        display: flex;
        align-items: center;
        margin-right: 20px;
    }

    .checkbox-container label {
        display: inline-block;
        margin-right: 20px;
    }

    .selected-item,
    .target-item {
        border: 1px solid #ccc;
        padding: 5px;
        margin-right: 10px;
    }

    .column-box {
        display: inline-block;
        border: 1px solid #ccc;
        padding: 10px;
        margin-right: 20px;
    }


    /* Default styles for all screen sizes */
    .export-btn button {
        width: 137px;
        /* Set the default button width for larger screens */
        border-radius: 0;
        /* Remove border-radius for all screens */
    }

    /* Media query for mobile screens (max-width: 767px) */
    @media screen and (max-width: 767px) {
        .export-btn button {
            width: 135px;
            /* Reduce the button width for mobile screens */
        }
    }

    /* Default styles for all screen sizes */
    .column-container {
        display: flex;
        justify-content: space-between;
    }

    .column-set {
        flex: 1;
        margin-right: 15px;
    }

    .column-container .column-set h5 {
        text-align: center;
    }

    /* Media query for mobile screens (max-width: 767px) */
    @media screen and (max-width: 767px) {
        .column-container {
            flex-direction: column;
            /* Stack the column sets on mobile screens */
        }

        .column-set {
            margin-right: 0;
            margin-bottom: 20px;
        }

        .column-container .column-set h5 {
            text-align: left;
        }
    }

    /* Media query for screens smaller than 768px (typical for mobile devices) */
    @media (max-width: 768px) {
        table {
            font-size: 10px;
            /* Adjust the font size as needed for smaller screens */
        }
    }

    /* Media query for screens smaller than 768px (typical for mobile devices) */
    @media (max-width: 768px) {
        .mobile-title h3 {
            font-size: 15px;
            /* Adjust the font size as needed for smaller screens */
        }
    }

    /* For mobile devices (max-width: 767px) */
    @media (max-width: 767px) {
        .nameinput input.form-control {
            width: 100% !important;
        }
    }

    /* For desktop devices (min-width: 768px) */
    @media (min-width: 768px) {
        .nameinput input.form-control {
            width: 30% !important;
        }
    }
</style>
    <!--showing messages error and success-->
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

        @if (isset($success))
            <script>
                document.addEventListener("DOMContentLoaded", function() {
                    // JavaScript code to show a success message in the modal
                    var success = '{{ $success }}';
                    var messageModal = new bootstrap.Modal(document.getElementById('messageModal'));
                    document.getElementById('messageTitle').textContent = 'Success';
                    document.getElementById('messageBody').textContent = success;
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
                    document.getElementById('messageBody').textContent = error;
                    messageModal.show();
                    setTimeout(function() {
                        messageModal.hide();
                    }, 3000); // Close the modal after 3 seconds
                });
            </script>
        @endif
    </div>
    <!--main content show code-->
    <div class="container">
        <div class="card">
            <h4 class="card-header">Define New File Include Excludes</h4>
            <div class="card-body">
                <form action="{{ route('prepare-csv') }}" method="POST">
                    @csrf
                    <input type="hidden" name="fileName" value="{{ $fileName }}">

                    <!-- Checkbox options for columns -->

                    <div class="row">
                        <div class="col-md-6">
                            <div class="card mb-3" style="overflow: hidden;">
                                <div class="card-body">
                                    <p class="card-header">New File Columns</p>

                                    <div class="checkbox-container mt-4">
                                        <!-- Add "Select All" checkbox for New File Columns -->
                                        <label>
                                            <input type="checkbox" class="select-all-checkbox selected-checkbox">
                                            Select All
                                        </label>
                                        @foreach ($csvData[0] as $column)
                                            <label>
                                                <input type="checkbox" name="selectedColumns[]"
                                                    value="{{ $column }}"
                                                    class="column-checkbox selected-checkbox">
                                                {{ $column }}
                                            </label>
                                        @endforeach
                                    </div>

                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card " style="overflow: hidden;">
                                <div class="card-body">
                                    <p class="card-header">Clean Columns</p>
                                    <div class="checkbox-container mt-4">
                                        <!-- Add "Select All" checkbox for Cleaning Target Columns -->
                                        <label>
                                            <input type="checkbox" class="select-all-checkbox target-checkbox">
                                            Select All
                                        </label>
                                        @foreach ($csvData[0] as $column)
                                            <label>
                                                <input type="checkbox" name="targetColumns[]"
                                                    value="{{ $column }}" class="column-checkbox target-checkbox">
                                                {{ $column }}
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <br>
                    <div class="row mb-2">
                        <div class="col-12 col-md-6">
                            <div class="form-check">
                                <input type="checkbox" id="myCheckbox" name="myCheckbox" value="checkme"
                                    class="form-check-input">
                                <label class="form-check-label" for="myCheckbox">Delete Duplicate
                                    Rows</label>
                            </div>
                        </div>
                    </div>
                    <!-- This is the button with filename section -->
                    <div class="row mx-1">
                        <div class="export-btn col-auto px-0 text-center">
                            <button type="submit" class="btn btn-primary text-center">
                                Save As/Export
                            </button>
                        </div>
                        <div class=" nameinput col px-0">
                            <input type="text" name="newfilename" placeholder="New File Name"
                                style="border-radius: 0px;" class="form-control " required>
                        </div>
                    </div>


                </form>
            </div>
        </div>


    </div>

    <div class="container mt-3">
        <div class="container mb-3">
            <div class="bulk-actions d-flex align-items-center">
                <span class="mobile-title">
                    <h3>File Details</h3>
                </span>
                <span class="mt-3 ml-5" style="margin-left: 10px;"><p>Rows({{$totalRows}})</p></span>
                <span class="mt-3 ml-5" style="margin-left: 20px;"><p> columns({{$totalColumns}})</p></span>
            </div>

        </div>
        @if (!empty($csvData))
    <div class="table-responsive" style="max-height: 600px; overflow-y: scroll;">
        <table class="table">
            <thead>
                <tr>
                    @foreach ($csvData[0] as $column)
                        <th>{{ $column }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($csvData as $key => $row)
                    @if ($key > 0)
                        <!-- Skip the first row (header) -->
                        <tr>
                            @foreach ($row as $value)
                                <td>{{ trim($value) }}</td>
                            @endforeach
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
    </div>
@else
    <p>No data available in the CSV file.</p>
@endif

    </div>

    <!-- JavaScript to handle "Select All" making new file checkboxes -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const selectedCheckboxes = document.querySelectorAll('.selected-checkbox');
            const targetCheckboxes = document.querySelectorAll('.target-checkbox');
            const selectAllSelected = document.querySelector('.select-all-checkbox.selected-checkbox');
            const selectAllTarget = document.querySelector('.select-all-checkbox.target-checkbox');

            // Function to check/uncheck all checkboxes
            const toggleAllCheckboxes = (checkboxes, selectAll) => {
                checkboxes.forEach(cb => {
                    cb.checked = selectAll.checked;
                });
            };

            selectAllSelected.addEventListener('change', function() {
                toggleAllCheckboxes(selectedCheckboxes, selectAllSelected);
            });

            selectAllTarget.addEventListener('change', function() {
                toggleAllCheckboxes(targetCheckboxes, selectAllTarget);
            });

            selectedCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    // Remove the code to display selected columns
                });
            });

            targetCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    updateRow(targetCheckboxes, targetRow);
                });
            });
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const bulkActionSelect = document.getElementById('bulkActionSelect');
            const bulkActionBtn = document.getElementById('bulkActionBtn');

            bulkActionBtn.addEventListener('click', function() {
                const selectedAction = bulkActionSelect.value;
                if (selectedAction === 'apply') {
                    // Implement the logic for "Apply" action
                    // Example: alert('Apply selected');
                } else if (selectedAction === 'delete') {
                    // Implement the logic for "Delete Selected" action
                    // Example: alert('Delete Selected selected');
                } else if (selectedAction === 'removeEmpty') {
                    // Implement the logic for "Remove Empty Rows" action
                    // Example: alert('Remove Empty Rows selected');
                }
            });
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous">
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous">
    </script>
    </form>
</div>
@endsection
