<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class WordToPdfController extends Controller
{
    public function index(Request $request)
    {
        return view('admin.wordtopdf.index');
    }

    public function uploadpage()
    {
        return view('admin.wordtopdf.index');
    }

    public function uploadfile(Request $request)
    {
        // Validate as file only (ignore mime mismatch issues)
        $request->validate([
            'field_file' => 'required|file|max:20480', // 20 MB
        ]);

        $file = $request->file('field_file');
        $ext  = strtolower($file->getClientOriginalExtension());

        // Custom extension check
        if (!in_array($ext, ['doc', 'docx'])) {
            return response()->json([
                'success' => false,
                'message' => 'Only DOC and DOCX files are allowed.',
            ], 422);
        }

        // Store uploaded file
        $fileName  = time() . '_' . $file->getClientOriginalName();
        $inputPath = storage_path('app/uploads/' . $fileName);

        $file->move(storage_path('app/uploads'), $fileName);

        // Paths
     $exePath   = 'word_pdf_converter.exe';
        $outputDir = storage_path('app/converted_pdfs');

        // Ensure output dir exists
        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0777, true);
        }

        // Change working dir
          chdir(public_path('word_pdf_converter_utility')); // change cwd
        // Command
        $cmd = '"' . $exePath . '" -outputDir "' . $outputDir . '" -doc "' . $inputPath . '"';

        // Run the command
        exec($cmd . " 2>&1", $output, $returnVar);

        if ($returnVar !== 0) {
            return response()->json([
                'success' => false,
                'message' => 'Conversion failed',
                'cmd'     => $cmd,
                'output'  => $output,
                'code'    => $returnVar,
            ], 500);
        }

        // Converted PDF file name
        $pdfFile = pathinfo($fileName, PATHINFO_FILENAME) . '.pdf';
        $pdfPath = $outputDir . DIRECTORY_SEPARATOR . $pdfFile;

        if (!file_exists($pdfPath)) {
            return response()->json([
                'success' => false,
                'message' => 'PDF not generated',
                'cmd'     => $cmd,
                'output'  => $output,
            ], 500);
        }

        // If AJAX ? return JSON with link
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Conversion completed successfully!',
                'link'    => route('wordtopdf.download', ['file' => $pdfFile]),
            ]);
        }

        // Else ? direct browser download
        return response()->download($pdfPath)->deleteFileAfterSend(true);
    }

    // Separate method for PDF download
    public function download($file)
    {
        $path = storage_path('app/converted_pdfs/' . $file);

        if (!file_exists($path)) {
            abort(404, 'File not found');
        }

        // Open in browser (inline view)
        return response()->file($path, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $file . '"'
        ]);
    }
}
