<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use App\models\raisoni\DamagedStock;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use DB;
use App\models\KsgBtatchRecordModel;

class BatchRecordsExport  implements FromCollection, WithHeadings, ShouldAutoSize

{
   
    protected $id;

    public function __construct($id)
    {
        $this->id = $id;
    }

    public function collection()
    {
    
        // return KsgBtatchRecordModel::where('batch_id', $this->id)->get();
        return KsgBtatchRecordModel::where('batch_id', $this->id)
        ->select('usn', 'name', 'course', 'course_date', 'course_month','fees','status')
        ->get();
    }
    public function headings(): array
    {
        return [
            'Unique sr. no.',             
            'Name',     
            'Course', 
            "Date",
            "Month",     
            'Fees',       
            'Status',      
           
        ];
    }

}