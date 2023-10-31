<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Input;
use SplFileObject;

class CsvController extends Controller
{
    public function index()
    {
        return view('upload');
    }

    public function upload(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'csv_file' => 'required|mimes:csv,txt',
        ]);

        if ($validator->fails()) {
            return redirect()->route('upload-csv')->with('error', 'Invalid file format.');
        }

        $file = $request->file('csv_file');
        $fileName = 'imported_' . $file->getClientOriginalName();
        $file->move(public_path('uploads'), $fileName);

        return redirect()->route('show-csv-content', ['fileName' => $fileName]);
    }

    public function showCsvContent($fileName)
    {

        $filePath = public_path('uploads/' . $fileName);
        $csvData = [];
        $success = "File imported Successfully"; // Set your success message here

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

        foreach ($csvData as $row) {
            $newRow = [];
            $hasTargetData = true;

            if (!empty($targetColumns)) {
                $targetColumnValues = array_intersect_key($row, array_flip($targetColumns));
                $targetColumnHash = md5(serialize($targetColumnValues));

                if (in_array($targetColumnHash, $seenValues)) {
                    // Skip this row as it has the same values in the target columns
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
        $csv = new \SplFileObject($filePath, 'r');
        $csv->setFlags(\SplFileObject::READ_CSV);

        $csvData = [];
        $headers = []; // Initialize an empty array for headers

        $uniqueRows = []; // To store unique rows

        foreach ($csv as $row) {
            if (!empty(array_filter($row))) {
                // Ensure that $headers and $row have the same number of elements
                if (empty($headers)) {
                    $headers = $row;
                } elseif (count($headers) == count($row)) {
                    $rowData = array_combine($headers, $row);

                    // Check if the row is unique
                    $hash = md5(json_encode($rowData));
                    if (!isset($uniqueRows[$hash])) {
                        $uniqueRows[$hash] = $rowData;
                    }
                }
            }
        }

        // Convert unique rows back into an array
        $csvData = array_values($uniqueRows);

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
            // Validate the uploaded CSV file
            $request->validate([
                'csv_file' => 'required|mimes:csv,txt',
            ]);

            // Get the uploaded CSV file
            $file = $request->file('csv_file');

            // Define the path where you want to save the uploaded CSV file
            $uploadPath = public_path('uploads');
            $fileName = 'uploaded_file_' . $file->getClientOriginalName();

            // Save the uploaded file
            $file->move($uploadPath, $fileName);

            // Define columns to delete
            $columnsToDelete = [
                'FacebookMessenger', 'EmailHost', 'Google_Rank', 'DomainRegistered', 'DomainExpiry',
                'DomainNameserver', 'DomainRegistrar', 'Instagram_Followers', 'Instagram_Follows',
                'Instagram_TotalPhotos', 'Instagram_Average_Likes', 'Instagram_Average_Comments',
                'Instagram_Is_verified', 'Instagram_HighlightReel_Count', 'Instagram_Is_BizAccount',
                'Instagram_AccountName', 'YelpAds', 'FBMessenger_Ads', 'FacebookAds', 'Instagram_Ads',
                'Adwords_Ads', 'FacebookPixel', 'GooglePixel', 'CriteoPixel', 'GoogleStars',
                'GoogleCount', 'YelpStars', 'Yelpcount', 'FacebookStars', 'Facebookcount',
                'MainCategory', 'MobileFriendly', 'GoogleAnalytics', 'SchemaMarkup', 'UseWordpress',
                'UseShopify', 'LinkedinAnalytics',
            ];

            // Path to the saved uploaded file
            $uploadedFilePath = $uploadPath . DIRECTORY_SEPARATOR . $fileName;

            if (file_exists($uploadedFilePath)) {
                // Open the original CSV file for reading
                $originalFile = fopen($uploadedFilePath, 'r');

                // Create a new cleaned CSV file for writing
                $cleanedFilePath = storage_path('app/public/cleaned_file.csv');
                $cleanedFile = fopen($cleanedFilePath, 'w');

                // Write the header row to the cleaned file, excluding columns to delete
                $header = fgetcsv($originalFile);
                $header = array_diff($header, $columnsToDelete);
                fputcsv($cleanedFile, $header);

                $rowCount = 0; // Counter for valid entries

                // Loop through each row in the original CSV file
                while (($row = fgetcsv($originalFile)) !== false) {
                    // Check if the row has an email or phone number (assuming the columns are 0-based)
                    $emailColumn = 2;
                    $phoneColumn = 3;
                    if (!empty($row[$emailColumn]) || !empty($row[$phoneColumn])) {
                        // Exclude columns to delete
                        $row = array_diff($row, $columnsToDelete);
                        fputcsv($cleanedFile, $row);
                        $rowCount++;
                    }
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


}
