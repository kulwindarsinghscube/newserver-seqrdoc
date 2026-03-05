<?php

namespace App\Http\Controllers;

use App\Imports\StudentsImport;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\convodataverification\ConvoAdmin;
// use App\Imports\StudentsImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Validator;
use App\Models\convodataverification\ConvoStudent;
use ReflectionClass;
use Illuminate\Support\Facades\DB;
use  App\Mail\convocation\AdminCorrection;
use App\Models\convodataverification\StudentAckLog;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;

use App\Models\convodataverification\StudentTransaction;
use App\Exports\ConvoStudentExport;
use App\Exports\ConvoStudentPaymentsExport;
use App\Http\Controllers\Admin\TextTranslator;
use App\Models\convodataverification\ConvoStudentLog;
use PaytmWallet;
use  App\Mail\convocation\PaymentConfirmation;
class ConvoStudentImportController extends Controller
{
    public function importExcel(Request $request)
    {
        // Validate the request file
        $request->validate([
            'excel_data' => 'required|mimes:xls,xlsx',
        ]); 
        try {
            // Instantiate the import class
            $student_import = new StudentsImport();

            // Import the data from the file
            Excel::import($student_import, $request->file('excel_data'));

            // dd($student_import);
            // Check if there are any errors recorded in the import process
            if (!empty($student_import->errors)) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Data imported with some errors!',
                    'processed_data' => @$student_import->processed_data,
                    'total_data' => @$student_import->total_data,
                    'errors' => $student_import->errors,
                    'failed' => ((int)@$student_import->total_data - (int)@$student_import->processed_data),

                ]);
            } else {
                return response()->json([
                    'status' => 'success',
                    'processed_data' => @$student_import->processed_data,
                    'total_data' => @$student_import->total_data,
                    'failed' => ((int)@$student_import->total_data - (int)@$student_import->processed_data),
                    'message' => 'Data Imported and Processed Successfully!'
                ]);
            }
        } catch (\Throwable $e) {
            // Handle any exception that may occur during import
            $errors = [];
            $file = $request->file('excel_data');

            // Determine the file type and create the appropriate reader
            $inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($file->getPathname());
            $objReader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);

            // Load the file into a Spreadsheet object
            $spreadsheet = $objReader->load($file->getPathname());

            // Get the first worksheet (or specify which worksheet you want)
            $worksheet = $spreadsheet->getActiveSheet();

            // Get the highest row number
            $total_data = $worksheet->getHighestRow();

            $total_data = $total_data - 1; 

            if (method_exists($e, 'failures') && $e->failures()) {
                $reflection = new ReflectionClass($e);
                $methods = $reflection->getProperties();


                $errors = $e->failures();
            } else {
                $errors = [
                    ['message' => $e->getMessage()]
                ];
            }

            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred: ' . $e->getMessage(),
                'errors' => $errors,
                'processed_data' => 0,
                'total_data' => $total_data,
                'failed' => $total_data
            ]); // Use 500 for server errors
        }
    }
}
