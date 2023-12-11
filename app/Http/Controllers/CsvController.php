<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Input;
use SplFileObject;
use League\Csv\Reader;
use League\Csv\Writer;
use League\Csv\Statement;



class CsvController extends Controller
{
    public function index()
    {
        return view('upload');
    }

    public function upload(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'csv_file' => 'required|mimes:csv,txt,xlsx',
        ]);

        if ($validator->fails()) {
            return redirect()->route('upload-csv')->with('error', 'Invalid file format. Please upload a .csv or .xlsx file.');
        }

        $file = $request->file('csv_file');

        // Check if the file has a .csv or .xlsx extension
        $allowedExtensions = ['csv', 'xlsx'];
        $extension = $file->getClientOriginalExtension();

        if (!in_array($extension, $allowedExtensions)) {
            return redirect()->route('upload-csv')->with('error', 'Invalid file extension. Please upload a .csv or .xlsx file.');
        }

        $fileName = 'imported_' . $file->getClientOriginalName();
        $file->move(public_path('uploads'), $fileName);

        return redirect()->route('show-csv-content', ['fileName' => $fileName]);
    }

    public function showCsvContent($fileName)
    {
        $filePath = public_path('uploads/' . $fileName);
        $csvData = [];
        $message = ''; // Add a variable for the message

        if (file_exists($filePath)) {
            $csv = new SplFileObject($filePath, 'r');
            $csv->setFlags(SplFileObject::READ_CSV);

            $totalRows = 0;
            $totalColumns = 0;

            foreach ($csv as $row) {
                $csvData[] = $row;
                $totalRows++;

                if ($totalColumns == 0) {
                    // Set the total columns based on the first row
                    $totalColumns = count($row);
                }
            }
        }
        $success = 'CSV file loaded successfully!'; // Set your desired success message here
        return view('showcsv', compact('csvData', 'fileName', 'success', 'totalRows', 'totalColumns'));
    }


    public function prepareCsv(Request $request)
    {
        $fileName = $request->input('fileName');
        $selectedColumns = $request->input('selectedColumns');
        $targetColumns = $request->input('targetColumns');
        $newfilename = $request->input('newfilename');
        $myCheckbox = $request->input('myCheckbox');
        $uploadDirectory = public_path('uploads');
        $filePath = $uploadDirectory . DIRECTORY_SEPARATOR . $fileName;

        if (!file_exists($filePath)) {
            return redirect()->back()->with('error', 'File not found.');
        }

        // Read the CSV file and filter out empty rows
        $csvData = $this->readAndFilterCsvFile($filePath);

        // Prepare the data for writing
        $preparedCsvData = [];
        $headerRow = $selectedColumns; // Initialize with selected columns

        // Include the header row as the first row
        $preparedCsvData[] = $headerRow;

        // Track seen values in the target columns
        $seenValues = [];

        // ...

        foreach ($csvData as $row) {
            $newRow = [];
            $hasTargetData = true;

            if (!empty($targetColumns)) {
                $targetColumnValues = array_intersect_key($row, array_flip($targetColumns));

                // Check if any target column has an empty value
                if (in_array('', $targetColumnValues)) {
                    // Skip this row as it has empty values in the target columns
                    continue;
                }

                $targetColumnHash = md5(serialize($targetColumnValues));

                if (in_array($targetColumnHash, $seenValues)) {
                    // Skip this row as it has the same values in the target columns as before
                    continue;
                }

                // Add the hash to the seen list to prevent duplicates
                $seenValues[] = $targetColumnHash;
            }

            if ($myCheckbox !== 'checkme' || ($myCheckbox === 'checkme' && $hasTargetData)) {
                foreach ($selectedColumns as $column) {
                    if (isset($row[$column])) {
                        $newRow[] = $row[$column];
                    } else {
                        $newRow[] = 'Column Not Found';
                    }
                }
                $preparedCsvData[] = $newRow;
            }
        }

        // ...


        // Create a new file handle for writing
        $preparedCsvFileName = $newfilename;
        $newCsvFilePath = $uploadDirectory . DIRECTORY_SEPARATOR . $preparedCsvFileName;
        $newCsvFile = fopen($newCsvFilePath, 'w');

        // Write the data to the new file
        foreach ($preparedCsvData as $row) {
            fputcsv($newCsvFile, $row);
        }

        fclose($newCsvFile);

        return redirect()->route('download-prepared-csv', ['filename' => $preparedCsvFileName]);
    }

    private function readAndFilterCsvFile($filePath)
    {
        $csv = new SplFileObject($filePath, 'r');
        $csv->setFlags(SplFileObject::READ_CSV);

        $csvData = [];
        $headers = [];

        foreach ($csv as $row) {
            if (!empty(array_filter($row))) {
                if (empty($headers)) {
                    $headers = $row;
                } elseif (count($headers) == count($row)) {
                    $rowData = array_combine($headers, $row);
                    $csvData[] = $rowData;
                }
            }
        }

        return $csvData;
    }

    private function removeDuplicateValues($data, $targetColumns)
    {
        $uniqueRows = [];
        $uniqueValues = [];

        // Filter out null values from $targetColumns
        $targetColumns = array_filter($targetColumns, function ($value) {
            return $value !== null;
        });

        foreach ($data as $index => $row) {
            $hasDuplicate = false;
            $rowValues = array_intersect_key($row, array_flip($targetColumns));

            foreach ($rowValues as $column => $value) {
                // Convert all values to lowercase for case-insensitive comparison
                $valueLower = is_string($value) ? strtolower($value) : $value;

                if (isset($uniqueValues[$column][$valueLower])) {
                    // This value is a duplicate, mark the row as having a duplicate
                    $hasDuplicate = true;
                    break;
                } else {
                    $uniqueValues[$column][$valueLower] = true;
                }
            }

            if (!$hasDuplicate) {
                $uniqueRows[] = $row;
            }
        }

        return $uniqueRows;
    }

    public function downloadPreparedCsv($filename)
    {
        $filePath = public_path('uploads/' . $filename);

        if (file_exists($filePath)) {
            return response()->download($filePath, $filename, [
                'Content-Type' => 'text/csv',
            ]);
        } else {
            return redirect()->back()->with('error', 'File not found.');
        }
    }

    public function prev()
    {
        // Get the path to your "uploads" folder
        $uploadsPath = public_path('uploads');

        // Check if the directory exists
        if (is_dir($uploadsPath)) {
            // Get the list of files in the folder
            $files = scandir($uploadsPath);

            // Filter out the "." and ".." entries
            $files = array_diff($files, array('.', '..'));

            // Create an empty array to store file information (including last modified time and last opened time)
            $fileInfo = [];

            // Loop through each file and get its last modified time and last opened time
            foreach ($files as $file) {
                $filePath = $uploadsPath . '/' . $file;
                $fileInfo[$file] = [
                    'last_modified' => filemtime($filePath),
                    'last_opened' => date('F j, Y H:i:s', fileatime($filePath)) // Format last opened time
                ];
            }

            // Sort the file information by last modified time in descending order
            arsort($fileInfo);

            // Get the total count of files
            $fileCount = count($fileInfo);

            // Pass the file information and file count to the view
            return view('previouswork', ['fileInfo' => $fileInfo, 'fileCount' => $fileCount]);
        } else {
            // Handle the case when the directory doesn't exist
            return view('previouswork', ['fileInfo' => [], 'fileCount' => 0]);
        }
    }

    public function deleteFile($filename)
    {
        // Get the path to your "uploads" folder
        $uploadsPath = public_path('uploads');

        // Construct the full file path
        $filePath = $uploadsPath . '/' . $filename;

        if (file_exists($filePath)) {
            // File exists, so we can delete it
            if (unlink($filePath)) {
                // File successfully deleted
                return redirect()->route('prev')->with('success', 'File deleted successfully.');
            } else {
                // File couldn't be deleted
                return redirect()->route('prev')->with('error', 'Unable to delete the file.');
            }
        } else {
            // File not found
            return redirect()->route('prev')->with('error', 'File not found.');
        }
    }


    public function clean(Request $request)
    {
        // Check if the HTTP method is POST
        if ($request->isMethod('post')) {
            $validator = Validator::make($request->all(), [
                'csv_file' => 'required|mimes:csv,txt,xlsx',
            ]);

            if ($validator->fails()) {
                return redirect()->route('clean')->with('error', 'Invalid file format. Please upload a .csv or .xlsx file.');
            }

            // Get the uploaded CSV file
            $file = $request->file('csv_file');

            // Check if the file type is allowed
            $allowedTypes = ['csv', 'xlsx'];
            $fileType = $file->getClientOriginalExtension();

            if (!in_array($fileType, $allowedTypes)) {
                return redirect()->back()->with('error', 'Invalid file type. Please upload a .csv or .xlsx file.');
            }

            // Define the path where you want to save the uploaded CSV file
            $uploadPath = public_path('uploads');
            $fileName = 'uploaded_file_' . $file->getClientOriginalName();

            // Save the uploaded file
            $file->move($uploadPath, $fileName);

            $specifiedColumns = [
                'BusinessName', 'Telephone', 'Email', 'WebsiteURL', 'Linkedin', 'FacebookProfile', 'Instagram', 'Twitter', 'GMB_Claimed', 'Address', 'City', 'State', 'ZIP', 'Country',
            ];

            // Path to the saved uploaded file
            $uploadedFilePath = $uploadPath . DIRECTORY_SEPARATOR . $fileName;

            if (file_exists($uploadedFilePath)) {
                // Open the original CSV file for reading
                $originalFile = fopen($uploadedFilePath, 'r');

                // Read the header row
                $headerRow = fgetcsv($originalFile);

                // Identify columns to keep based on exact match with the specified columns
                $columnsToKeep = [];
                foreach ($headerRow as $index => $column) {
                    if (in_array($column, $specifiedColumns)) {
                        $columnsToKeep[] = $index;
                    }
                }

                // Create a new cleaned CSV file for writing
                $cleanedFilePath = storage_path('app/public/cleaned_file.csv');
                $cleanedFile = fopen($cleanedFilePath, 'w');

                $rowCount = 0; // Counter for valid entries

                // Write the header row to the cleaned file
                fputcsv($cleanedFile, $specifiedColumns);

                // Loop through each row in the original CSV file
                while (($row = fgetcsv($originalFile)) !== false) {
                    // Check if the Email and Phone columns are not empty
                    $emailColumn = array_search('Email', $specifiedColumns);
                    $phoneColumn = array_search('Telephone', $specifiedColumns);

                    if (empty($row[$emailColumn]) || empty($row[$phoneColumn])) {
                        continue; // Skip this row if the Email or Phone column is empty
                    }

                    // Extract only the columns you want to keep
                    $newRow = [];
                    foreach ($columnsToKeep as $index) {
                        $newRow[] = $row[$index] ?? ''; // Use an empty string if the column is not found in the original row
                    }

                    // Write the row to the cleaned file
                    fputcsv($cleanedFile, $newRow);
                    $rowCount++;
                }

                // Close both files
                fclose($originalFile);
                fclose($cleanedFile);

                // Define the filename for the cleaned CSV file
                $cleanedFilename = 'cleaned_file - ' . $rowCount . ' leads.csv';

                // Move the cleaned CSV file to a public directory (replace 'your_public_path' with the actual path)
                $publicPath = 'your_public_path'; // Set the actual path where you want to store the cleaned files
                rename($cleanedFilePath, $publicPath . $cleanedFilename);

                // Send the new cleaned CSV file for download
                return response()->download($publicPath . $cleanedFilename, $cleanedFilename, [
                    'Content-Type' => 'text/csv',
                ]);
            } else {
                return redirect()->back()->with('error', 'No file uploaded.');
            }
        } else {
            return view('clean'); // Return to the 'clean' view for GET requests
        }
    }

    public function auto_clean(Request $request)
    {   // Check if the HTTP method is POST
        if ($request->isMethod('post')) {
            $validator = Validator::make($request->all(), [
                'csv_file' => 'required|mimes:csv,txt,xlsx',
            ]);

            if ($validator->fails()) {
                return redirect()->route('auto-clean')->with('error', 'Invalid file format. Please upload a .csv or .xlsx file.');
            }

            // Get the uploaded CSV file
            $file = $request->file('csv_file');
            // Check if the file type is allowed
            $allowedTypes = ['csv', 'xlsx'];
            $fileType = $file->getClientOriginalExtension();

            if (!in_array($fileType, $allowedTypes)) {
                return redirect()->back()->with('error', 'Invalid file type. Please upload a .csv or .xlsx file.');
            }

            // Define the path where you want to save the uploaded CSV file
            $uploadPath = public_path('uploads');
            $fileName = 'uploaded_file_' . $file->getClientOriginalName();
            // Save the uploaded file
            $file->move($uploadPath, $fileName);

            $specifiedColumns = [
                'BusinessName', 'Telephone', 'Email', 'WebsiteURL', 'Linkedin', 'FacebookProfile', 'Instagram', 'Twitter', 'GMB_Claimed', 'Address', 'City', 'State', 'ZIP', 'Country',
            ];

            // Path to the saved uploaded file
            $uploadedFilePath = $uploadPath . DIRECTORY_SEPARATOR . $fileName;

            if (file_exists($uploadedFilePath)) {
                // Open the original CSV file for reading
                $originalFile = fopen($uploadedFilePath, 'r');

                // Read the header row
                $headerRow = fgetcsv($originalFile);

                // Identify columns to keep based on exact match with the specified columns
                $columnsToKeep = [];
                foreach ($headerRow as $index => $column) {
                    if (in_array($column, $specifiedColumns)) {
                        $columnsToKeep[] = $index;
                    }
                }

                // Create a new cleaned CSV file for writing
                $cleanedFilePath = storage_path('app/public/cleaned_file.csv');
                $cleanedFile = fopen($cleanedFilePath, 'w');

                $rowCount = 0; // Counter for valid entries

                // Write the header row to the cleaned file
                fputcsv($cleanedFile, $specifiedColumns);

                // Loop through each row in the original CSV file
                while (($row = fgetcsv($originalFile)) !== false) {
                    // Check if the Email column is empty
                    $emailColumn = array_search('Email', $specifiedColumns);
                    if (empty($row[$emailColumn])) {
                        continue; // Skip this row if the Email column is empty
                    }

                    // Extract only the columns you want to keep
                    $newRow = [];
                    foreach ($columnsToKeep as $index) {
                        $newRow[] = $row[$index] ?? ''; // Use an empty string if the column is not found in the original row
                    }

                    // Write the row to the cleaned file
                    fputcsv($cleanedFile, $newRow);
                    $rowCount++;
                }

                // Close both files
                fclose($originalFile);
                fclose($cleanedFile);

                // Define the filename for the cleaned CSV file
                $cleanedFilename = 'cleaned_file - ' . $rowCount . ' leads.csv';

                // Move the cleaned CSV file to a public directory (replace 'your_public_path' with the actual path)
                $publicPath = 'your_public_path'; // Set the actual path where you want to store the cleaned files
                rename($cleanedFilePath, $publicPath . $cleanedFilename);

                // Send the new cleaned CSV file for download
                return response()->download($publicPath . $cleanedFilename, $cleanedFilename, [
                    'Content-Type' => 'text/csv',
                ]);
            } else {
                return redirect()->back()->with('error', 'No file uploaded.');
            }
        } else {
            return view('auto_clean'); // Return to the 'clean' view for GET requests
        }
    }

    public function editcsv(Request $request){
        if($request->method == 'post'){
        //
        }
        return view('filter');
    }
}
