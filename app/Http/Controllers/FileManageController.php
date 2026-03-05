<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ApiTracker;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ApiTrakerExport;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;

class FileManageController extends Controller
{
    
    function getFolderDetails($directory)
    {
        $folders = [];
        $directoryIterator = new RecursiveDirectoryIterator($directory);
        $iterator = new RecursiveIteratorIterator($directoryIterator);

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                // Get folder path and file name
                $filePath = $file->getPathname();
                $folderPath = dirname($filePath);
                $fileName = $file->getFilename();

                // Read lines and count them
                $lineCount = count(file($filePath));


                // Get last modified date and time
                $lastModified = date("d-m-Y H:i:s", filemtime($filePath));


                // Structure data by folder
                if (!isset($folders[$folderPath])) {
                    $folders[$folderPath] = [
                        'total_line_count' => 0,
                        'files' => []
                    ];
                }

                // Add file information
                $folders[$folderPath]['files'][] = [
                    'name' => $fileName,
                    'path' => $filePath,
                    'line_count' => $lineCount,
                    'last_modified' => $lastModified,
                ];

                // Update folder line count
                $folders[$folderPath]['total_line_count'] += $lineCount;
            }
        }

        return $folders;
    }


    public function fileManage()
    {

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set the headers for the columns
        $sheet->setCellValue('A1', 'Path');
        $sheet->setCellValue('B1', 'Filename');
        // $sheet->setCellValue('C1', 'File Path');
        $sheet->setCellValue('C1', 'Code Lines');
        $sheet->setCellValue('D1', 'Last updated');
        $sheet->setCellValue('E1', 'Type');

        // Run the function
        // $projectDirectory = 'C:/wamp64/www/uneb/resources/views';
        $projectPath = base_path().'\\resources\\views'; // Current directory; you can set this to any directory path
        
        // print($projectPath);
        $folderDetails = $this->getFolderDetails($projectPath);

        $rowIndex = 2;
        // Display results
        foreach ($folderDetails as $folder => $details) {
            // echo "Folder: $folder\n";
            // echo "Total Lines: " . $details['total_line_count'] . "\n";
            foreach ($details['files'] as $file) {
                // echo "    Path: " . $file['path'] . "\n";
                // echo "<br>";
                
                // echo "  File: " . $file['name'] . "\n";
                // echo "<br>";

                // echo "    Lines: " . $file['line_count'] . "\n";
                // echo "<br>";

                // echo "File Modifed: {$file['last_modified']}\n";
                // echo "<br>";
                // echo "<br>";

                $sheet->setCellValue('A' . $rowIndex, $folder);
                $sheet->setCellValue('B' . $rowIndex, $file['name']);
                // $sheet->setCellValue('C' . $rowIndex, $file['path']);
                $sheet->setCellValue('C' . $rowIndex, $file['line_count']);
                $sheet->setCellValue('D' . $rowIndex, $details['total_line_count']);
                $sheet->setCellValue('E' . $rowIndex, 'PHP');
                $rowIndex++;

            }
            // echo "\n";

           

        }

         // Save the file to the desired location
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $filePath = public_path('folder_line_count.xlsx');
        $writer->save($filePath);

        // Return the file for download
        return response()->download($filePath);

    }


    
}
