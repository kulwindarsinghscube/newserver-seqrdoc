<?php

namespace App\Exports;

use App\Models\convodataverification\StudentTransaction;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet; // Correct namespace for Worksheet
use Carbon\Carbon;

class ConvoStudentPaymentsExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    private $rowNumber = 1; // Counter for serial number
    
    protected $filters;
    public function __construct(array $filters)
    {
        $this->filters = $filters;
    }
    
    public function collection()
    {
        $convoStudentTransaction = StudentTransaction::select('id', 'student_id', 'status', 'order_id', 'txn_id', 'payment_mode', 'bank_txn_id', 'txn_date', 'txn_amount')
            ->with('student')
            ->whereHas('student', function ($query) {
                // $query->where('is_printed',0);
                if (!empty($this->filters['prn'])) {
                    $query->where('prn', 'like', '%' . $this->filters['prn'] . '%');
                }
                if (!empty($this->filters['name'])) {
                    $query->where('full_name', 'like', '%' . $this->filters['name'] . '%');
                }
                if (!empty($this->filters['course'])) {
                    $query->where('course_name', 'like', '%' . $this->filters['course'] . '%');
                }
                if (!empty($this->filters['faculty_name'])) {
                    $query->where('faculty_name', 'like', '%' . $this->filters['faculty_name'] . '%');
                }
            
                if (!empty($this->filters['specialization'])) {
                    $query->where('specialization', 'like', '%' . $this->filters['specialization'] . '%');
                }
        
                if (!empty($this->filters['completion_year'])) {
                    $query->where('completion_date', '=',  $this->filters['completion_year']);
                }
                if (!empty($this->filters['student_type'])) {
                    $query->where('student_type', $this->filters['student_type']);
                }else{
                    $query->where('student_type',0);
                }
                
                // Handle the status filter
                if (!empty($this->filters['status'])){
                    if ($this->filters['status'] === 'registration completed') {
                        $registration_completed = [
                            'student re-acknowledged new data as correct, Payment is completed and preview pdf is approved',
                            'student acknowledge all data as correct, Payment is completed and preview pdf is approved'
                        ];
                        $query->whereIn('status', $registration_completed);
                    } else {
                        $query->where('status', $this->filters['status']);
                    }
                }
            })
            ->orderBy('txn_date','desc')
            ->get();
            // dd($convoStudentTransaction)
        return $convoStudentTransaction;
    }

    public function headings(): array
    {
        return [
            'SR No.',
            'PRN',
            'Student Name',   
            'Competency Level',
            'Mobile', 
            'Collection Mode',
            'Order ID', 
            'Amount', 
            'Payment Status', 
            'Payment Method', 
            'Paytm Transaction ID',
            'Bank Transaction ID', 
            'Date & Time', 
        ];
    }

    public function map($convoStudentTransaction): array
    {
        return [
            $this->rowNumber++, // Increment and return the serial number 
            $convoStudentTransaction->student->prn,
            $convoStudentTransaction->student->full_name,
            $convoStudentTransaction->student->course_name,
            $convoStudentTransaction->student->student_mobile_no, 
            $convoStudentTransaction->student->collection_mode, 
            $convoStudentTransaction->order_id,
            $convoStudentTransaction->txn_amount,
            str_replace('TXN_', '', $convoStudentTransaction->status),
            $convoStudentTransaction->payment_mode,
            $convoStudentTransaction->txn_id, 
            $convoStudentTransaction->bank_txn_id, 
            Carbon::parse($convoStudentTransaction->txn_date)->format('Y-m-d h:i:s A')
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Set the header row to bold
        $sheet->getStyle('1:1')->getFont()->setBold(true);
        
        // Auto size columns
        foreach (range('A', $sheet->getHighestDataColumn()) as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }
    }
}
