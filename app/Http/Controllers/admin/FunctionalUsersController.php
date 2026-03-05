<?php
/**
 *
 *  Author : Ketan valand 
 *   Date  : 2/11/2019
 *   Use   : listing of Webuser & update and store Webuser data
**/
namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\models\CardDataMapping;
use App\models\SessionManager;
use App\models\FunctionalUsers;
use App\models\CertificateDataMapping;
use App\models\StudentTable;
use App\Imports\StudentManagementImport;
use App\models\Role;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Auth;
use Excel; 
use App\Exports\FunctionalUserLoginHistoryExport;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class FunctionalUsersController extends Controller
{
    /**
     * Display a listing of the Webuser.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $domain = \Request::getHost();
        $subdomain = explode('.', $domain); //$subdomain[0]="icat"   
        if($request->ajax()){
            
            $auth_site_id=Auth::guard('admin')->user()->site_id;                                               
              //for serial number
            $iDisplayStart=$request->input('iDisplayStart'); 
            DB::statement(DB::raw('set @rownum='.$iDisplayStart));
            //DB::statement(DB::raw('set @rownum=0'));   
            $columns = [DB::raw('@rownum  := @rownum  + 1 AS rownum'),'id','Mobile_Number','Email_ID','is_inter','Student_Name','created_at'];

            $countQuery = FunctionalUsers::query();
            if (!empty($request->input('sSearch')))
            {
                $search = $request->input('sSearch');
                $countQuery = $countQuery->where(function($query) use ($search) {
                    $query->where('Student_Name', 'like', "%{$search}%")
                          ->orWhere('Mobile_Number', 'like', "%{$search}%")
                          ->orWhere('Email_ID', 'like', "%{$search}%");
                });
            }
            $font_master_count = $countQuery->count();

  
            $fontMaster_list = FunctionalUsers::select($columns);
            if (!empty($request->input('sSearch')))
            {
                $search = $request->input('sSearch');
                $fontMaster_list = $fontMaster_list->where(function($query) use ($search) {
                    $query->where('Student_Name', 'like', "%{$search}%")
                          ->orWhere('Mobile_Number', 'like', "%{$search}%")
                          ->orWhere('Email_ID', 'like', "%{$search}%");
                });
            }
      
            if($request->get('iDisplayStart') != '' && $request->get('iDisplayLength') != ''){
                $fontMaster_list = $fontMaster_list->take($request->input('iDisplayLength'))
                ->skip($request->input('iDisplayStart'));
            }          

            if($request->input('iSortCol_0')){
                $sql_order='';
                for ( $i = 0; $i < $request->input('iSortingCols'); $i++ )
                {
                    $column = $columns[$request->input('iSortCol_' . $i)];
                    if(false !== ($index = strpos($column, ' as '))){
                        $column = substr($column, 0, $index);
                    }
                    $fontMaster_list = $fontMaster_list->orderBy($column,$request->input('sSortDir_'.$i));   
                }
            } 
            $fontMaster_list->orderBy('created_at', 'desc');
            $fontMaster_list = $fontMaster_list->get();
            $response['iTotalDisplayRecords'] = $font_master_count;
            $response['iTotalRecords'] = $font_master_count;
            $response['sEcho'] = intval($request->input('sEcho'));
            $response['aaData'] = $fontMaster_list->toArray();
            
            return $response;
        }
       
        // dd( $category );
        return view('admin.functionalusers.index');
    }
    
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created User in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    // store user information 
    public function store(UserManagementRequest $request)
    {
        $user_data=$request->all();
        event(new UserManagmentEvent($user_data));
        return response()->json(['success'=>true]);
    }

    /**
     * Display the specified Webuser.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        
      

        $domain =$_SERVER['HTTP_HOST'];
            $subdomain = explode('.', $domain);
            if($subdomain[0] == "demo"||$subdomain[0] == "raisoni"){
                  $user_data=User::select('username','fullname','l_name','email_id','mobile_no','device_type','created_at','registration_no','user_type','working_sector','address','institute','degree','branch','passout_year')->where('id',$id)->get()->toArray();
                 // print_r($user_data);
                   if(!empty($user_data[0]['degree'])){
                        $degree = DB::table('degree_master')->where('id', $user_data[0]['degree'])->first();
                        $user_data[0]['degree']= $degree->degree_name;

                    }

                    if(!empty($user_data[0]['branch'])){
                        $branch = DB::table('branch_master')->where('id', $user_data[0]['branch'])->first();
                        $user_data[0]['branch']= $branch->branch_name_long;

                    }


            }else{
                  $user_data=User::select('username','fullname','email_id','mobile_no','device_type','created_at')->where('id',$id)->get()->toArray();
            }
        $user_data=head($user_data);
        
        $last_login_time=SessionManager::select('login_time')
                          ->where('user_id',$id)
                          ->orderBy('id','desc')
                          ->first();
                          
        
     
        $user_data['login_time']=$last_login_time['login_time']; 

       
        
        return $user_data;
    }

    /**
     * Show the form for editing the specified user.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    // get user information specific id
    public function edit($id)
    {
        $user_data=FunctionalUsers::where('id',$id)->get()->toArray();
        $user_data=head($user_data);
        return $user_data;
    }

    /**
     * Update the specified user in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $user_data=$request->all();
        // dd($user_data);
        $data=array(
            'Student_Name'=>$user_data['sname'],
            'Email_ID'=>$user_data['email_id'],
            'Mobile_Number'=>$user_data['mobile_no'],
        );
       

        if (!empty($user_data['mobile_no'])) {
            $founduser=FunctionalUsers::where('Mobile_Number', $user_data['mobile_no'])->where('id', '!=', $id)->get()->toArray();
            if (count($founduser) > 0) {
            return response()->json(['success' =>2]);
            }
        }
        if (!empty($user_data['email_id'])) {
            $founduser=FunctionalUsers::where('Email_ID', $user_data['email_id'])->where('id', '!=', $id)->get()->toArray();

            if (count($founduser) > 0) {
                return response()->json(['success' =>2]);
            }
        } 
        // if (!empty($user_data['mobile_no']) || !empty($user_data['email_id'])) {
        //     $founduser->where('Mobile_Number', $user_data['mobile_no'])->orWhere('Email_ID', $user_data['email_id']);
        // }

        //$founduser->where('id', '!=', $id);

        //$founduser = $founduser->toSql();//->get()->toArray();
        //dd($founduser);

        $result = FunctionalUsers::where('id',$id)->update($data);
        if($result){
         return response()->json(['success'=>true]);
        }else{
         return response()->json(['success'=>false]);
        }
    }

    /**
     * store Excel sheet data on storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function fileUpload(Request $request)
    {
       if($request->hasFile('field_file'))
       {     
          
          $response= Excel::toArray(new StudentManagementImport,$request->file('field_file'));

          $Excel_data=head($response);
          // dd($Excel_data);
          $columns=array();
          foreach ($Excel_data[0] as $key => $value) {
              
             if(!empty($value))
             {
                $columns[]=$value;
             }         
          }
          // Expected headings
            $expectedHeadings = ['Student Name', 'Mobile Number', 'Email ID'];

            // Read the first row (headings)
            $actualHeadings = $columns;
          
            // foreach ($sheet->getColumnIterator() as $column) {
            //     $columnIndex = $column->getColumnIndex(); // A, B, C, etc.
            //     $actualHeadings[] = $sheet->getCell("$columnIndex" . "1")->getValue();
            // }

            // Validate headings
            $missingHeadings = array_diff($expectedHeadings, $actualHeadings);
            $extraHeadings = array_diff($actualHeadings, $expectedHeadings);
            //dd($missingHeadings,$extraHeadings);
            if (empty($missingHeadings) && empty($extraHeadings)) {
                // unset Header in Excel
                   foreach ($Excel_data as $key => $value) {
                       unset($Excel_data[0]);
                    }
                    // dd($Excel_data);
                  foreach ($Excel_data as $key => $value) {
            
                  $founduser = FunctionalUsers::query();

                    if (!empty($value[1])) {
                        $founduser->where('Mobile_Number', $value[1]);
                    }

                    if (!empty($value[2])) {
                        $founduser->orWhere('Email_ID', $value[2]);
                    }

                    if (!empty($value[1]) && !empty($value[2])) {
                        $founduser->where('Mobile_Number', $value[1])->where('Email_ID', $value[2]);
                    }

                    $founduser = $founduser->first();
                    if(empty($founduser)){
                        $user_data=array(
                          'Student_Name'=>$value[0],  
                          'Mobile_Number'=>$value[1],  
                          'Email_ID'=>$value[2],  
                          'is_inter'=>(empty($value[1])&&!empty($value[2]))?'Y':'N'  
                          // 'student_tbl_id'=>$studentData->id,  
                        );
                        FunctionalUsers::create($user_data);
                    }
             }
             // success response
              return response()->json(['success'=>true]);

            } else {
                if (!empty($missingHeadings)) {
                   // echo "Missing headings: " . implode(', ', $missingHeadings) . "<br>";
                    return response()->json(["success"=>false,'InvalidData'=>"Missing headings: " . implode(', ', $missingHeadings)]);
                }
                if (!empty($extraHeadings)) {
                    //echo "Unexpected headings: " . implode(', ', $extraHeadings) . "<br>";
                    return response()->json(["success"=>false,'InvalidData'=>"Unexpected headings: " . implode(', ', $extraHeadings)]);
                }
            }
      }      
    }

    /**
     * Remove the specified user from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
    
        $user_data=User::where('id',$id)->delete();
        return $user_data ? response()->json(['success'=>true]) :'false';
    }


   
     
    
    public function ExportLoggedInFunctionalUserHistory()
    {
        // Get the current date and time in the desired format (e.g., Y-m-d_H-i-s)
        $dateTime = now()->format('Y-m-d_H-i-s');  // This will give a format like 2025-01-03_14-30-00
    
        // Generate the file name using the current date/time and string
        $fileName = "functional_user_login_history_{$dateTime}.xlsx";
    
        // Return the Excel file with the dynamically generated filename
        return Excel::download(new FunctionalUserLoginHistoryExport, $fileName);
    }


    public function expiringCards(Request $request)
    {
        $hostUrl = $request->getHttpHost();
        $subdomain = explode('.', $hostUrl);
       
        $user_query = DB::table('student_table')
            ->select('*')
            ->join('card_data_mapping', 'student_table.id', '=', 'card_data_mapping.student_tbl_id')
            ->where('student_table.status', '=', 1);
        if (!empty($request->card_category)) {
            $user_query->where('colour_code', '=', $request->card_category);
        }
        // Apply date range using raw SQL for STR_TO_DATE function
        if (!empty($request->start_date) && !empty($request->end_date)) {
            // dd($request->start_date);
            // Convert the date format to YYYY-MM-DD
            $startDate = Carbon::parse($request->start_date)->format('Y-m-d');
            $endDate = Carbon::parse($request->end_date)->format('Y-m-d');
            // dd($request->end_date,$endDate);
           
            // Apply the date range using STR_TO_DATE in the query
            $user_query->whereBetween('valid_upto', [$startDate, $endDate]);
            // $user_query->whereRaw("STR_TO_DATE(valid_upto, '%d-%b-%Y') BETWEEN ? AND ?", [$startDate, $endDate]);
        } else {
            // If no date range is provided, default to the start of the current month to the end of the third month
            $startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
            $endDate = Carbon::now()->addMonths(3)->endOfMonth()->format('Y-m-d');
            
            // Apply the date range using STR_TO_DATE in the query
            $user_query->whereBetween('valid_upto', [$startDate, $endDate]);
            // $user_query->whereRaw("STR_TO_DATE(valid_upto, '%d-%b-%Y') BETWEEN ? AND ?", [$startDate, $endDate]);
        }
    
        // Execute the query
        $user = $user_query->get()->toArray();
    //     $sql = $user_query->toSql();
    // $bindings = $user_query->getBindings();

    // // Merge bindings into SQL for display
    // $fullSql = vsprintf(str_replace('?', "'%s'", $sql), $bindings);

    // dd($fullSql); // dump the complete query with values
        // dd($user);
        foreach ($user as $key => $value) {
            $value->certificate_filepath = $request->getScheme() . '://' . $subdomain[0] . '.' . $subdomain[1] . '.com/' . $subdomain[0] . '/backend/pdf_file/' . $value->certificate_filename;
        }
    
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()->setCreator('PhpOffice')
            ->setLastModifiedBy('PhpOffice')
            ->setTitle('Expiring Cards Report')
            ->setSubject('Expiring Cards Report')
            ->setDescription('Generated Report')
            ->setKeywords('expiring cards')
            ->setCategory('Reports');
    
        // Set sheet headers
        $spreadsheet->setActiveSheetIndex(0)
            // ->setCellValue('A1', 'Serial No')
            ->setCellValue('A1', 'Name')
            ->setCellValue('B1', 'Enrolment No')
            ->setCellValue('C1', 'Course Name')
            ->setCellValue('D1', 'Validity')
            ->setCellValue('E1', 'Hub Name')
            ->setCellValue('F1', 'Batch No');
    
        // Populate sheet with data
        foreach ($user as $key => $sheet_one) {
            $i = $key + 2;
            // $spreadsheet->getActiveSheet()->setCellValue("A$i", $sheet_one->unique_number);
            $spreadsheet->getActiveSheet()->setCellValue("A$i", $sheet_one->Candidate_name);
            $spreadsheet->getActiveSheet()->setCellValue("B$i", $sheet_one->enrollment_no);
            $spreadsheet->getActiveSheet()->setCellValue("C$i", $sheet_one->course);
            $spreadsheet->getActiveSheet()->setCellValue("D$i", date('d-M-Y', strtotime($sheet_one->valid_upto)));
            $spreadsheet->getActiveSheet()->setCellValue("E$i", $sheet_one->Hub_Name);
            $spreadsheet->getActiveSheet()->setCellValue("F$i", $sheet_one->batch_no);
        }
    
        // Apply styles (with borders for header)
        $range = 'A1:E1';
        $style = [
            'font' => ['bold' => true],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ];
        $spreadsheet->getSheet(0)->getStyle($range)->applyFromArray($style);
    
        // Set bold font for the header row
        $spreadsheet->getSheet(0)->getStyle('A1:F1')->getFont()->setBold(true)->setSize(12);
    
        // Set column widths
        // $spreadsheet->getActiveSheet()->getColumnDimension('A')->setWidth(15); // Serial No
        $spreadsheet->getActiveSheet()->getColumnDimension('A')->setWidth(30); // Name
        $spreadsheet->getActiveSheet()->getColumnDimension('B')->setWidth(20); // Enrollment No
        $spreadsheet->getActiveSheet()->getColumnDimension('C')->setWidth(25); // Course Name
        $spreadsheet->getActiveSheet()->getColumnDimension('D')->setWidth(20); // Validity
        $spreadsheet->getActiveSheet()->getColumnDimension('E')->setWidth(20); // Hub Name
        $spreadsheet->getActiveSheet()->getColumnDimension('F')->setWidth(20); // Hub Name
    
        // Rename worksheet
        $spreadsheet->getActiveSheet()->setTitle('Expiring Cards Report');
    
        // Set active sheet index to the first sheet
        $spreadsheet->setActiveSheetIndex(0);
    
        // Generate file name with readable timestamp and date range
        $filename = 'expiring_cards_report_' . $startDate . '_to_' . $endDate . '_at_' . Carbon::now()->format('Ymd_His') . '.xlsx';
    
        // Clear any output before sending the file
        ob_end_clean();
        
        // Set headers for file download
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Cache-Control: max-age=0');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=1');
        header('Pragma: public');
    
        // Write file to output
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output'); // Directly output to browser
        exit; // Ensure no further output is sent to the browser
    
        // Return a response (this is optional since the file will be downloaded directly)
        return response()->json(['status' => 200, 'message' => 'Report generated successfully'], 200);
    }
    
    
    



    public function createdCardReport(Request $request)
    {
        
        $hostUrl = $request->getHttpHost();
        $subdomain = explode('.', $hostUrl);
        $cards = CardDataMapping::query();
        // dd(1);
        // Check if a date range is provided in the request
        if (!empty($request->start_date) && !empty($request->end_date)) {
            $startDate = $request->start_date;
            $endDate = $request->end_date;
            
            // Apply the date range filter
            $cards->whereBetween('created_at', [$startDate, $endDate]);
            // dd($cards);
        } 

        if (!empty($request->card_category) && isset($request->card_category) && $request->card_category!="All") {
            $cards->where('colour_code', '=', $request->card_category);
        }
        
        // Check if you also want to filter by students
        $cards->whereHas('student'); // This filters by the existence of a related student
        
        // Get the results
        $cards = $cards->get(); 
        // Group the data by color code
        $categories = $cards->groupBy('colour_code');
        
        // Create a new Spreadsheet
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()->setCreator('PhpOffice')
            ->setLastModifiedBy('PhpOffice')
            ->setTitle('Created Card Report')
            ->setSubject('Created Card Report')
            ->setDescription('Generated Card Report')
            ->setKeywords('cards, report')
            ->setCategory('Reports');
    
        // Fetch color names based on the color code
        // $colorNames = $this->getColorNames();
    
        // Remove the default sheet that is created automatically
        $spreadsheet->removeSheetByIndex(0);
    
        // Loop through each color category and create a new sheet for each
        foreach ($categories as $colorCode => $cardData) {
            // Create a new sheet for each color code
            $sheet = $spreadsheet->createSheet();
            $sheet->setTitle($colorCode ?? 'Unknown');
    
            // Set headers for the sheet
            // $sheet->setCellValue('A1', 'Serial No')
               $sheet->setCellValue('A1', 'Name')
                  ->setCellValue('B1', 'Enrolment No')
                  ->setCellValue('C1', 'Course Name')
                  ->setCellValue('D1', 'Validity')
                  ->setCellValue('E1', 'Hub Name')
                  ->setCellValue('F1', 'Color Code')
                  ->setCellValue('G1', 'Batch No');
    
            // Style the header row (bold, background color)
            $headerStyle = [
                'font' => ['bold' => true],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ],
                ],
            ];

             
            $sheet->getStyle('A1:F1')->applyFromArray($headerStyle);
    
            // Set column widths (adjust as necessary)
            // $sheet->getColumnDimension('A')->setWidth(15); // Serial No
            $sheet->getColumnDimension('A')->setWidth(30); // Name
            $sheet->getColumnDimension('B')->setWidth(20); // Enrollment No
            $sheet->getColumnDimension('C')->setWidth(25); // Course Name
            $sheet->getColumnDimension('D')->setWidth(20); // Validity
            $sheet->getColumnDimension('E')->setWidth(20); // Hub Name
            $sheet->getColumnDimension('F')->setWidth(15); // Color Code
            $sheet->getColumnDimension('G')->setWidth(15); // Color Code
    
            // Fill the data for the current color category
            $row = 2;
            foreach ($cardData as $card) {
                // $sheet->setCellValue("A$row", $card->unique_number)
                    $sheet->setCellValue("A$row", $card->Candidate_name)
                      ->setCellValue("B$row", $card->enrollment_no)
                      ->setCellValue("C$row", $card->course)
                      ->setCellValue("D$row", date('d-M-Y', strtotime($card->valid_upto)))
                      ->setCellValue("E$row", $card->Hub_Name)
                      ->setCellValue("F$row", $card->colour_code)
                      ->setCellValue("G$row", $card->batch_no);
                $row++;
            }
        }
    
        // Set active sheet index to the first sheet (optional)
        // $spreadsheet->setActiveSheetIndex(0);
        if ($spreadsheet->getSheetCount() > 0) {
            $spreadsheet->setActiveSheetIndex(0);
        }
        // Prepare for direct download
        $filename = 'created_cards_report_' . $startDate . '_to_' . $endDate . '_at_' . Carbon::now()->format('Ymd_His') . '.xlsx';
    
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Cache-Control: max-age=0');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=1');
        header('Pragma: public');
        // dd(1);
        // Save the spreadsheet to the browser
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output'); // Directly output to browser
    }

    public function cardCycleTimeReport(Request $request)
    {
        $hostUrl = $request->getHttpHost();
        $subdomain = explode('.', $hostUrl);
        $cards = CardDataMapping::query();
    
        // Check if a date range is provided in the request
        if (!empty($request->start_date) && !empty($request->end_date)) {
            $startDate = $request->start_date;
            $endDate = $request->end_date; 
            // Apply the date range filter
            $cards->whereBetween('created_at', [$startDate, $endDate]);
        }

        if (!empty($request->card_category)) {
            $cards->where('colour_code', '=', $request->card_category);
        }
    
        // Check if you also want to filter by students
        $cards->whereHas('student'); // This filters by the existence of a related student
    
        // Get the results
        $cards = $cards->get();
    
        // Calculate months and years within the date range
        $start = \Carbon\Carbon::parse($startDate);
        $end = \Carbon\Carbon::parse($endDate);
        $months = [];
        while ($start->lte($end)) {
            $months[] = $start->format('M Y'); // Store the month and year
            $start->addMonth(); // Move to next month
        }
    
        // Calculate YTD start date (assuming financial year starts in April)
        $currentYear = \Carbon\Carbon::parse($endDate)->year;
        $currentMonth = \Carbon\Carbon::parse($endDate)->month;
        $financialYearStart = ($currentMonth < 4)
            ? \Carbon\Carbon::create($currentYear - 1, 4, 1) // Start of last year's April
            : \Carbon\Carbon::create($currentYear, 4, 1);    // Start of this year's April
    
        // Filter cards for the YTD range
        $ytdCards = CardDataMapping::whereBetween('created_at', [$financialYearStart, $endDate])->get();
        $ytdGrouped = $ytdCards->groupBy('Hub_Name'); // Group YTD cards by Hub Name
    
        // Group cards by month for the monthly breakdown
        $cardsGrouped = $cards->groupBy(function ($date) {
            return \Carbon\Carbon::parse($date->created_at)->format('M Y'); // Group by month and year
        });
    
        // Create a new Spreadsheet
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()->setCreator('PhpOffice')
            ->setLastModifiedBy('PhpOffice')
            ->setTitle('Created Card Report')
            ->setSubject('Created Card Report')
            ->setDescription('Generated Card Report')
            ->setKeywords('cards, report')
            ->setCategory('Reports');
    
        // Remove the default sheet that is created automatically
        $spreadsheet->removeSheetByIndex(0);
    
        // Create a new sheet
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Card Report');
    
        // Set headers dynamically based on the date range
        $sheet->setCellValue('A1', 'Certificates');
        $column = 'B';
        foreach ($months as $month) {
            $sheet->setCellValue("$column" . '1', $month); // Set month and year headers dynamically
            $column++;
        }
        $sheet->setCellValue($column . '1', 'Total Cards (NOS)');
        $ytdColumn = ++$column; // Move to the next column for YTD
        $ytdStart = $financialYearStart->format('M Y'); // Start of financial year (e.g., Apr 2023)
        $ytdEnd = \Carbon\Carbon::parse($endDate)->format('M Y'); // End date in the given range
        
        // Update the YTD header to include the range
        $sheet->setCellValue("$ytdColumn" . '1', "YTD ($ytdStart - $ytdEnd)");    
        // Style the header row (bold, background color)
        $headerStyle = [
            'font' => ['bold' => true],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ];
    
        $sheet->getStyle('A1:' . chr(65 + count($months) + 2) . '1')->applyFromArray($headerStyle);
    
        // Set fixed column width for better visibility
        $sheet->getColumnDimension('A')->setWidth(20); // Hub Name
        $column = 'B';
        foreach ($months as $month) {
            $sheet->getColumnDimension($column)->setWidth(12); // Month columns
            $column++;
        }
        $sheet->getColumnDimension($column)->setWidth(15); // Total Cards
        $sheet->getColumnDimension($ytdColumn)->setWidth(40); // YTD Cards
    
        // Populate data into the sheet
        $row = 2;
        foreach ($cardsGrouped as $month => $cardsData) {
            $monthlyCounts = $cardsData->groupBy('Hub_Name');
            foreach ($monthlyCounts as $hubName => $data) {
                $sheet->setCellValue("A$row", $hubName);
    
                // Initialize the month count to 0
                $monthlyData = array_fill_keys($months, 0);
    
                // Count the occurrences for each month
                foreach ($data as $card) {
                    $monthKey = \Carbon\Carbon::parse($card->created_at)->format('M Y');
                    $monthlyData[$monthKey]++;
                }
    
                // Assign monthly data to respective columns
                $column = 'B';
                foreach ($months as $month) {
                    $sheet->setCellValue("$column$row", $monthlyData[$month]);
                    $column++;
                }
    
                // Total Cards column (sum of all monthly counts)
                $sheet->setCellValue("$column$row", array_sum($monthlyData));
    
                // YTD Cards column
                $ytdCount = isset($ytdGrouped[$hubName]) ? $ytdGrouped[$hubName]->count() : 0;
                $sheet->setCellValue("$ytdColumn$row", $ytdCount);
    
                $row++;
            }
        }
    
        // Prepare for direct download
        $filename = 'cards_cycle_time_report_' . $startDate . '_to_' . $endDate . '_at_' . Carbon::now()->format('Ymd_His') . '.xlsx';
    
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Cache-Control: max-age=0');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=1');
        header('Pragma: public');
    
        // Save the spreadsheet to the browser
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
    }
    


    public function certificateCreateReport(Request $request)
    {
        $hostUrl = $request->getHttpHost();
        $subdomain = explode('.', $hostUrl);
        $cerificates = CertificateDataMapping::query();
    
        // Check if a date range is provided in the request
        if (!empty($request->start_date) && !empty($request->end_date)) {
            $startDate = $request->start_date;
            $endDate = $request->end_date;
    
            // Apply the date range filter
            $cerificates->whereBetween('created_at', [$startDate, $endDate]);
        }
    
        // Check if you also want to filter by students
        $cerificates->whereHas('student'); // This filters by the existence of a related student
        // dd($cerificates->toSql);
        // Get the results
        $cerificates = $cerificates->get(); 
        
       
        // Calculate months and years within the date range
        $start = \Carbon\Carbon::parse($startDate);
        $end = \Carbon\Carbon::parse($endDate);
        $months = [];
        
        while ($start->lte($end)) {
            $months[] = $start->format('M Y'); // Store the month and year
            $start->addMonth(); // Move to next month
        }
        // dd($cerificates);
        // Group by Hub Name and month
        $cerificatesGrouped = $cerificates->groupBy(function($date) {
            return \Carbon\Carbon::parse($date->created_at)->format('M Y'); // Group by month and year
        });
        // dd($cerificatesGrouped);
        // Create a new Spreadsheet
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()->setCreator('PhpOffice')
            ->setLastModifiedBy('PhpOffice')
            ->setTitle('Created Card Report')
            ->setSubject('Created Card Report')
            ->setDescription('Generated Card Report')
            ->setKeywords('cards, report')
            ->setCategory('Reports');
    
        // Remove the default sheet that is created automatically
        $spreadsheet->removeSheetByIndex(0);
    
        // Create a new sheet
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Card Report');
    
        // Set headers dynamically based on the date range
        $sheet->setCellValue('A1', 'Certificates');
        $column = 'B';
        foreach ($months as $month) {
            $sheet->setCellValue("$column" . '1', $month); // Set month and year headers dynamically
            $column++;
        }
        $sheet->setCellValue($column . '1', 'Total');
    
        // Style the header row (bold, background color)
        $headerStyle = [
            'font' => ['bold' => true],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ];
    
        $sheet->getStyle('A1:' . chr(65 + count($months)+1) . '1')->applyFromArray($headerStyle);
    
        // Set fixed column width for better visibility
        $sheet->getColumnDimension('A')->setWidth(20); // Hub Name
        $column = 'B';
        foreach ($months as $month) {
            $sheet->getColumnDimension($column)->setWidth(12); // Month columns
            $column++;
        }
        $sheet->getColumnDimension($column)->setWidth(15); // Total Cards
    
        // Populate data into the sheet
        $row = 2;
        foreach ($cerificatesGrouped as $month => $cerificatesData) {
            // Calculate the count of cards per Hub Name for the current month
            $monthlyCounts = $cerificatesData->groupBy('Hub_Name');
            // dd($monthlyCounts);
            foreach ($monthlyCounts as $hubName => $data) {
                // Fill in the cycle time, hub name, and counts for each month
                $sheet->setCellValue("A$row", $hubName);
                $sheet->getStyle("A$row")->applyFromArray([
                    'font' => [
                        'bold' => true, // Make text bold
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, // Center the text
                    ],
                ]);
    
                // Initialize the month count to 0
                $monthlyData = array_fill_keys($months, 0); // Initialize all months with 0
    
                // Count the occurrences for each month
                foreach ($data as $card) {
                    $monthKey = \Carbon\Carbon::parse($card->created_at)->format('M Y');
                    $monthlyData[$monthKey]++;
                }
    
                // Assign monthly data to respective columns
                $column = 'B'; // Start from the first month column
                foreach ($months as $month) {
                    $sheet->setCellValue("$column$row", $monthlyData[$month]);
                    $column++;
                }
    
                // Total Cards column (sum of all monthly counts)
                $sheet->setCellValue("$column$row", array_sum($monthlyData));
                $sheet->getStyle("$column$row")->applyFromArray([
                    'font' => [
                        'bold' => true, // Make text bold
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, // Center the text
                    ],
                ]);
                $row++;
            }
        }
    
        // Set active sheet index to the first sheet (optional)
        $spreadsheet->setActiveSheetIndex(0);
    
        // Prepare for direct download
        $filename = 'create_certificate_report_' . $startDate . '_to_' . $endDate . '_at_' . Carbon::now()->format('Ymd_His') . '.xlsx';
        ob_end_clean(); // Clear any buffered output
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Cache-Control: max-age=0');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=1');
        header('Pragma: public');
    
        // Save the spreadsheet to the browser
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');die();
    //}
//}
    
    
            if ($request->ajax()) {
            $iDisplayStart = $request->input('iDisplayStart');
            DB::statement(DB::raw('set @rownum=' . $iDisplayStart));

            $columns = [
                DB::raw('@rownum := @rownum + 1 AS rownum'),
                'id', 'unique_number', 'Candidate_name', 'enrollment_no',
                'course', 'to_work_under', 'valid_upto', 'batch_no',
                'Card_Category', 'Hub_Name', 'created_at', 'colour_code'
            ];

            // Base query
            $fontMaster_query = CardDataMapping::select($columns);

            // Clone for count before pagination
            $fontMaster_count_query = CardDataMapping::query();
            // dd($request->filled('card_category'));

            // Apply filters to both
            if ($request->filled('card_category')) {
                $fontMaster_query->where('colour_code', $request->input('card_category'));
                $fontMaster_count_query->where('colour_code', $request->input('card_category'));
            }

            if ($request->filled('start_date') && $request->filled('end_date')) {
                $start = $request->input('start_date') . ' 00:00:00';
                $end = $request->input('end_date') . ' 23:59:59';

                $fontMaster_query->whereBetween('created_at', [$start, $end]);
                $fontMaster_count_query->whereBetween('created_at', [$start, $end]);
            }


            // Pagination
            if ($request->filled('iDisplayStart') && $request->filled('iDisplayLength')) {
                $fontMaster_query->skip($request->input('iDisplayStart'))
                                 ->take($request->input('iDisplayLength'));
            }

            // Sorting
            if ($request->filled('iSortCol_0')) {
                for ($i = 0; $i < $request->input('iSortingCols'); $i++) {
                   $columnIndex = $request->input('iSortCol_' . $i);
                    $column = $columns[$columnIndex];

                    // Prevent trying to sort by @rownum alias
                    if (is_object($column) && method_exists($column, '__toString') && str_contains((string)$column, '@rownum')) {
                        continue; // skip sorting on rownum
                    }

                    if (false !== ($index = strpos($column, ' as '))) {
                        $column = substr($column, 0, $index);
                    }
                    $fontMaster_query->orderBy($column, $request->input('sSortDir_' . $i));
                }
            }

            // Execute
            $fontMaster_list = $fontMaster_query->get();
            $font_master_count = $fontMaster_count_query->count();

            // Return response
            return [
                'iTotalRecords' => $font_master_count,
                'iTotalDisplayRecords' => $font_master_count,
                'sEcho' => intval($request->input('sEcho')),
                'aaData' => $fontMaster_list->toArray(),
            ];
        }

        // For initial page load
        $categories = CardDataMapping::whereNotNull('colour_code')->groupBy('colour_code')->pluck('colour_code');
        return view('admin.functionalusers.cards_listing', compact('categories'));
    }

    public function cards_listing(Request $request)
    {
        if ($request->ajax()) {
            $iDisplayStart = $request->input('iDisplayStart');
            DB::statement(DB::raw('set @rownum=' . $iDisplayStart));

            $columns = [
                DB::raw('@rownum := @rownum + 1 AS rownum'),
                'id', 'Candidate_name', 'enrollment_no',
                'course', 'to_work_under', 'valid_upto', 'batch_no',
                'Card_Category', 'Hub_Name', 'created_at', 'colour_code'
            ];

            // Base query
            $fontMaster_query = CardDataMapping::select($columns);

            // Clone for count before pagination
            $fontMaster_count_query = CardDataMapping::query();
            // dd($request->filled('card_category'));
            if ($request->filled('sSearch')) {
                $search = $request->input('sSearch');

                $fontMaster_query->where(function ($query) use ($search) {
                    $query->where('Candidate_name', 'like', "%{$search}%")
                          ->orWhere('enrollment_no', 'like', "%{$search}%")
                          ->orWhere('course', 'like', "%{$search}%")
                          ->orWhere('to_work_under', 'like', "%{$search}%")
                          ->orWhere('valid_upto', 'like', "%{$search}%")
                          ->orWhere('batch_no', 'like', "%{$search}%")
                          ->orWhere('Card_Category', 'like', "%{$search}%")
                          ->orWhere('Hub_Name', 'like', "%{$search}%");
                });

                $fontMaster_count_query->where(function ($query) use ($search) {
                    $query->where('Candidate_name', 'like', "%{$search}%")
                          ->orWhere('enrollment_no', 'like', "%{$search}%")
                          ->orWhere('course', 'like', "%{$search}%")
                          ->orWhere('to_work_under', 'like', "%{$search}%")
                          ->orWhere('valid_upto', 'like', "%{$search}%")
                          ->orWhere('batch_no', 'like', "%{$search}%")
                          ->orWhere('Card_Category', 'like', "%{$search}%")
                          ->orWhere('Hub_Name', 'like', "%{$search}%");
                });
            }
            // Apply filters to both
            if ($request->filled('card_category')) {
                $fontMaster_query->where('colour_code', $request->input('card_category'));
                $fontMaster_count_query->where('colour_code', $request->input('card_category'));
            }
             if ($request->filled('card_hub')) {
                $fontMaster_query->where('Hub_Name', $request->input('card_hub'));
                $fontMaster_count_query->where('Hub_Name', $request->input('card_hub'));
            }

            if ($request->filled('start_date') && $request->filled('end_date')) {
                $start = $request->input('start_date') . ' 00:00:00';
                $end = $request->input('end_date') . ' 23:59:59';

                $fontMaster_query->whereBetween('created_at', [$start, $end]);
                $fontMaster_count_query->whereBetween('created_at', [$start, $end]);
            }


            // Pagination
            if ($request->filled('iDisplayStart') && $request->filled('iDisplayLength')) {
                $fontMaster_query->skip($request->input('iDisplayStart'))
                                 ->take($request->input('iDisplayLength'));
            }

            // Sorting
            if ($request->filled('iSortCol_0')) {
                for ($i = 0; $i < $request->input('iSortingCols'); $i++) {
                   $columnIndex = $request->input('iSortCol_' . $i);
                    $column = $columns[$columnIndex];

                    // Prevent trying to sort by @rownum alias
                    if (is_object($column) && method_exists($column, '__toString') && str_contains((string)$column, '@rownum')) {
                        continue; // skip sorting on rownum
                    }

                    if (false !== ($index = strpos($column, ' as '))) {
                        $column = substr($column, 0, $index);
                    }
                    $fontMaster_query->orderBy($column, $request->input('sSortDir_' . $i));
                }
            }
           $fontMaster_query->orderBy('created_at', 'desc');
            // Execute
            $fontMaster_list = $fontMaster_query->get();
            $font_master_count = $fontMaster_count_query->count();

            // Return response
            return [
                'iTotalRecords' => $font_master_count,
                'iTotalDisplayRecords' => $font_master_count,
                'sEcho' => intval($request->input('sEcho')),
                'aaData' => $fontMaster_list->toArray(),
            ];
        }

        // For initial page load
        $categories = CardDataMapping::whereNotNull('colour_code')->groupBy('colour_code')->pluck('colour_code');
        $Hub_Name = CardDataMapping::whereNotNull('Hub_Name')->groupBy('Hub_Name')->pluck('Hub_Name');
        return view('admin.functionalusers.cards_listing', compact('categories','Hub_Name'));
    }
    
    public function idCardReport(Request $request)
    {
        
        $categories = CardDataMapping::groupBy('colour_code')->pluck('colour_code');
        // dd( $category );
        return view('admin.functionalusers.idCardReport',compact('categories'));
    }
    public function mapOldData(Request $request)
    {
        if($request->hasFile('field_file')){
        // dd($request->all());
            $template_id=$request->get('templateName');
            $doc_type=$request->get('doc_type');
            //check extension
            $file = $request->file('field_file'); // get file object
            $file_name = $file->getClientOriginalName();
            $ext = $file->getClientOriginalExtension();
            $fullpath = $file->getPathname(); // temp uploaded location

                if($ext == 'xlsx' || $ext == 'Xlsx'){
                    $inputFileType = 'Xlsx';
                }
                else{
                    $inputFileType = 'Xls';
                }
                $objReader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
                $objPHPExcel = $objReader->load($fullpath);
                $sheet = $objPHPExcel->getSheet(0);
                $highestColumn = $sheet->getHighestColumn();
                $highestRow = $sheet->getHighestDataRow();

                $rowData = $sheet->rangeToArray('A1:' . $highestColumn . 1, NULL, TRUE, FALSE);

                $rowData1 = $sheet->rangeToArray('A2:' . $highestColumn . $highestRow, NULL, TRUE, FALSE);

                // Remove null or empty rows
                // $rowData1 = array_map(function($row) {
                //     return array_values(array_filter($row, function($value){
                //         return $value !== null && $value !== "";
                //     }));
                // }, $rowData1);

                if($doc_type=='certificate'){
                $columnMap = [
                         37 => ["Unique no","Watermark student data","Watermark TPSDI","student name","Enrollment NO","Program Title","Date","Student Image","Place","Result","Mobile Number","Email ID","Card Category","Hub Name"],
                         44 => ["Unique no","Watermark student data","Watermark TPSDI","student name","Enrollment NO","Program Title","Date","Student Image","Place","Mobile Number","Email ID","Card Category","Hub Name"],
                        42 => ["Unique no","Watermark student data","Watermark TPSDI","student name","Enrollment NO","Program Title","Date","Student Image","Place","Mobile Number","Email ID","Card Category","Hub Name"],
                        38 => ["Unique no","Watermark student data","Watermark TPSDI","student name","Enrollment NO","Program Title","Date","Student Image","Place","Dealer Name","Type","Mobile Number","Email ID","Card Category","Hub Name"],
                         41 => ["Unique no","Watermark student data","Watermark TPSDI","student name","Roll No","Enrollment NO","Program Title","Date","Student Image","Place","Mobile Number","Email ID","Card Category","Hub Name"],
                         36 => ["Unique no","Watermark Insitute Name","Watermark TPSDI","Institute name","From Date","To Date","Mobile Number","Email ID","Card Category","Hub Name"],
                    ];
                }else if($doc_type=='card'){
                    $columnMap = [
                        1 => ["Unique no","Candidate name","enrollment no","blood group","course","framework at","can work as ","to work under ","valid upto","batch no","Mobile Number","Email ID","Card Category","Hub Name"],
                        2 => ["Unique no","Candidate name","enrollment no","blood group","course","framework at","can work as ","to work under ","valid upto","batch no","Mobile Number","Email ID","Card Category","Hub Name"],
                        4 => ["Unique no","Candidate name","enrollment no","blood group","course","framework at","can work as ","to work under ","valid upto","batch no","Mobile Number","Email ID","Card Category","Hub Name"],
                        5 => ["Unique no","Candidate name","enrollment no","blood group","course","framework at","can work as ","to work under ","valid upto","batch no","Mobile Number","Email ID","Card Category","Hub Name"],
                        6 => ["Unique no","Candidate name","enrollment no","blood group","course","framework at","can work as ","to work under ","On","batch no","Mobile Number","Email ID","Card Category","Hub Name"],
                        7 => ["Unique no","Candidate name","enrollment no","blood group","course","framework at","can work as ","to work under ","valid upto","Colour Code","Division Name","batch no","Mobile Number","Email ID","Card Category","Hub Name"]
                    ];
                }
                if (array_key_exists($template_id, $columnMap)) {
                    $expectedColumns = $columnMap[$template_id];
                    $actualColumns = $rowData[0];

                    if (count($actualColumns) !== count($expectedColumns)) {
                        return response()->json([
                            'success' => false,
                            'type' => 'error',
                            'message' => 'Columns count of excel do not matched!'
                        ]);
                    }
                    $missing = array_diff($expectedColumns, $actualColumns);
                    if ($actualColumns !== $expectedColumns) {
                        return response()->json([
                            'success' => false,
                            'type' => 'error',
                            'message' => 'Excel columns do not matched! Please check columns : '. implode(',', $missing)
                        ]);
                    }
                    $mismatchColArr = array_diff($actualColumns, $expectedColumns);

                    if (count($mismatchColArr) > 0) {
                        return response()->json([
                            'success' => false,
                            'type' => 'error',
                            'message' => 'Sheet1 : Column names not matching as per requirement. Please check columns : ' . implode(',', $mismatchColArr)
                        ]);
                    }
                }

                // dd($rowData1);
                foreach ($rowData1 as $key => $row) {
                    // dd($row[0]);
                    $student_table_data = DB::table('student_table')
                    ->where('template_id', $template_id)
                    ->where('serial_no', $row[0])
                    ->where('status', 1)
                    ->where('publish', 1)
                    ->where('site_id', 248)
                    ->select('id', 'created_at')->first();
                    // dd($template_id,$row,$student_table_data);
                    if(!empty($template_id)&&!empty($row)&&!empty( $student_table_data)&&$doc_type=='card'){
                        $this->tpsdicardcheck($template_id,$row, $student_table_data);
                    }else if(!empty($template_id)&&!empty($row)&&!empty( $student_table_data)&&$doc_type=='certificate'){
                       $this->tpsdicertificatecheck($template_id,$row, $student_table_data); 
                    }
                }
                return response()->json([
                            'success' => true,
                            'type' => 'success',
                            'message' => 'Mapping successfull'
                        ]);
        }
        return view('admin.idCards.old_map');
    }
    
    public function getTemplates($doc_type)
    {
        $ids = [1, 2, 3, 4, 5, 6, 7];

        $query = DB::table('template_master')
            ->where('site_id', 248)
            ->where('status', 1)
            ->select('id', 'template_name');

        if ($doc_type == 'card') {
            $query->whereIn('id', $ids);
        } elseif ($doc_type == 'certificate') {
            $query->whereNotIn('id', $ids);
        }

        return response()->json($query->get());
    }

    public function tpsdicardcheck($template_id,$rowData,$student_table_data){
        $student_table_id=$student_table_data->id;
        $created_at=$student_table_data->created_at;
        if(in_array($template_id, [1,2,4,5])){
                            switch ($template_id) {
                                case 1:
                                    $color='GOLD';
                                    break;
                                case 2:
                                    $color='SILVER';
                                    break;
                                case 5:
                                    $color='Bronze';
                                    break;
                                
                                default:
                                    $color='BLUE';  
                                    break;
                            }
                        $row=array(
                          'unique_number'=>$rowData[0],
                          'Candidate_name'=>$rowData[1],  
                          'enrollment_no'=>$rowData[2],  
                          'blood_group'=>$rowData[3],  
                          'course'=>$rowData[4],  
                          'framework_at'=>$rowData[5],  
                          'can_work_as'=>$rowData[6],  
                          'to_work_under'=>$rowData[7],  
                          'valid_upto'=>Date::excelToDateTimeObject($rowData[0][8])->format('Y-m-d'),
                          'batch_no'=>$rowData[9],  
                          'Mobile_Number'=>$rowData[10],  
                          'Email_ID'=>$rowData[11],  
                          'Card_Category'=>$rowData[12],  
                          'Hub_Name'=>$rowData[13],  
                          'student_tbl_id'=>$student_table_id, 
                          'colour_code'=>$color,
                          'created_at'=>$created_at 
                        );
                        }

                        if(in_array($template_id, [6])){
                        $row=array(
                          'unique_number'=>$rowData[0],
                          'Candidate_name'=>$rowData[1],  
                          'enrollment_no'=>$rowData[2],  
                          'blood_group'=>$rowData[3],  
                          'course'=>$rowData[4],  
                          'framework_at'=>$rowData[5],  
                          'can_work_as'=>$rowData[6],  
                          'to_work_under'=>$rowData[7],  
                          'on_date'=>Date::excelToDateTimeObject($rowData[0][8])->format('Y-m-d'),
                          'batch_no'=>$rowData[9],  
                          'Mobile_Number'=>$rowData[10],  
                          'Email_ID'=>$rowData[11],  
                          'Card_Category'=>$rowData[12],  
                          'Hub_Name'=>$rowData[13],  
                          'student_tbl_id'=>$student_table_id,
                          'colour_code'=>'BLUE',
                          'created_at'=>$created_at  
                        );
                        }

                        if(in_array($template_id, [7])){
                         $row=array(
                          'unique_number'=>$rowData[0],
                          'Candidate_name'=>$rowData[1],  
                          'enrollment_no'=>$rowData[2],  
                          'blood_group'=>$rowData[3],  
                          'course'=>$rowData[4],  
                          'framework_at'=>$rowData[5],  
                          'can_work_as'=>$rowData[6],  
                          'to_work_under'=>$rowData[7],  
                          'valid_upto'=>Date::excelToDateTimeObject($rowData[0][8])->format('Y-m-d'),
                          'colour_code'=>$rowData[9],  
                          'division_name'=>$rowData[10],  
                          'batch_no'=>$rowData[11],  
                          'Mobile_Number'=>$rowData[12],  
                          'Email_ID'=>$rowData[13],  
                          'Card_Category'=>$rowData[14],  
                          'Hub_Name'=>$rowData[15],  
                          'student_tbl_id'=>$student_table_id,
                          'created_at'=>$created_at  
                        );   
                        }
                        // if(!empty($rowData[$find]))
                        CardDataMapping::create($row);
                        // $find++;
                        
                    // }
                    $founduser = FunctionalUsers::query();
                    if(in_array($template_id, [7])){
                        if (!empty($rowData[12]) && empty($rowData[13])) {
                            $founduser->where('Mobile_Number', $rowData[12]);
                        }

                        if (empty($rowData[12]) && !empty($rowData[13])) {
                            $founduser->orWhere('Email_ID', $rowData[13]);
                        }

                        if (!empty($rowData[13]) && !empty($rowData[12])) {
                            $founduser->where('Mobile_Number', $rowData[12])->orWhere('Email_ID', $rowData[13]);
                        }
                        $user_data=array(
                          'Student_Name'=>$rowData[1],  
                          'Mobile_Number'=>$rowData[12],  
                          'Email_ID'=>$rowData[13],  
                          'is_inter'=>(empty($rowData[12])&&!empty($rowData[13]))?'Y':'N'  
                          // 'student_tbl_id'=>$studentData->id,  
                        );
                    }else{
                        if (!empty($rowData[10]) && empty($rowData[11])) {
                            $founduser->where('Mobile_Number', $rowData[10])->where('is_inter','N');
                        }

                        if (empty($rowData[10]) && !empty($rowData[11])) {
                            $founduser->orWhere('Email_ID', $rowData[11])->where('is_inter','Y');
                        }

                        if (!empty($rowData[11]) && !empty($rowData[10])) {
                            $founduser->where('Mobile_Number', $rowData[10])->orWhere('Email_ID', $rowData[11]);
                        }
                        $user_data=array(
                          'Student_Name'=>$rowData[1],  
                          'Mobile_Number'=>$rowData[10],  
                          'Email_ID'=>$rowData[11],  
                          'is_inter'=>(empty($rowData[10])&&!empty($rowData[11]))?'Y':'N'  
                          // 'student_tbl_id'=>$studentData->id,  
                        );
                    }
                    
                    $founduser = $founduser->first();
                    if(empty($founduser)){
                        
                        $newUser=FunctionalUsers::create($user_data);
                        $lastInsertId = $newUser->id;
                        StudentTable::where('id',$student_table_id)->where('status',1)->update(['functional_user_id'=>$lastInsertId,'tpsdidoc_type'=>'card']);
                    }else{
                     StudentTable::where('id',$student_table_id)->where('status',1)->update(['functional_user_id'=>$founduser->id,'tpsdidoc_type'=>'card']);   
                    }
    }
    public function tpsdicertificatecheck($template_id,$rowData,$student_table_data){
        // coded by sewak start
        try {
                $student_table_id=$student_table_data->id;
                $created_at=$student_table_data->created_at;
                $domain = \Request::getHost();
                $subdomain = explode('.', $domain);
                if($subdomain[0]=="tpsdi"){
                // $founddata=CertificateDataMapping::where('Unique_no',$rowData[0])->first();
                    $founduser = FunctionalUsers::query();
                    // if(empty($founddata)){  
                    //demo=>live template id 655=>42 657=>38 change this when deploy on live
                    if(in_array($template_id, [37])){
                    $row=array(
                      'Unique_no'=>$rowData[0],
                      'Watermark_student_data'=>$rowData[1],  
                      'Watermark_TPSDI'=>$rowData[2],  
                      'student_name'=>$rowData[3],  
                      'Enrollment_NO'=>$rowData[4],  
                      'Program_Title'=>$rowData[5],  
                      'On_Date'=>Carbon::createFromFormat('d-m-Y', $rowData[6])->format('Y-m-d'),
                      'Student_Image'=>$rowData[7],  
                      'Place'=>$rowData[8],  
                      'Result'=>$rowData[9],  
                      'Mobile_Number'=>$rowData[10],  
                      'Email_ID'=>$rowData[11],  
                      'Card_Category'=>$rowData[12],  
                      'Hub_Name'=>$rowData[13],  
                      'student_tbl_id'=>$student_table_id, 
                      'created_at'=>$created_at 
                    );
                    

                    if (!empty($rowData[10])) {
                        $founduser->where('Mobile_Number', $rowData[10]);
                    }

                    if (!empty($rowData[11])) {
                        $founduser->orWhere('Email_ID', $rowData[11]);
                    }

                    if (!empty($rowData[11]) && !empty($rowData[10])) {
                        $founduser->where('Mobile_Number', $rowData[10])->orWhere('Email_ID', $rowData[11]);
                    }
                    $user_data=array(
                      'Student_Name'=>$rowData[3],
                      'Mobile_Number'=>$rowData[10],  
                      'Email_ID'=>$rowData[11],  
                      'is_inter'=>(empty($rowData[10])&&!empty($rowData[11]))?'Y':'N' 
                      // 'student_tbl_id'=>$studentData->id,  
                    );
                 }
                    
                    if(in_array($template_id, [44,42])){//Certificate- STGE
                    $row=array(
                      'Unique_no'=>$rowData[0],
                      'Watermark_student_data'=>$rowData[1],  
                      'Watermark_TPSDI'=>$rowData[2],  
                      'student_name'=>$rowData[3],  
                      'Enrollment_NO'=>$rowData[4],  
                      'Program_Title'=>$rowData[5],  
                      'On_Date'=>$rowData[6],//Carbon::createFromFormat('d-m-Y', $rowData[6])->format('Y-m-d'), 
                      'Student_Image'=>$rowData[7],  
                      'Place'=>$rowData[8],  
                      'Mobile_Number'=>$rowData[9],  
                      'Email_ID'=>$rowData[10],  
                      'Card_Category'=>$rowData[11],  
                      'Hub_Name'=>$rowData[12],  
                      'student_tbl_id'=>$student_table_id, 
                      'created_at'=>$created_at   
                    );
                    

                    if (!empty($rowData[9])) {
                        $founduser->where('Mobile_Number', $rowData[9]);
                    }

                    if (!empty($rowData[10])) {
                        $founduser->orWhere('Email_ID', $rowData[10]);
                    }

                    if (!empty($rowData[10]) && !empty($rowData[9])) {
                        $founduser->where('Mobile_Number', $rowData[9])->orWhere('Email_ID', $rowData[10]);
                    }
                    $user_data=array(
                      'Student_Name'=>$rowData[3],
                      'Mobile_Number'=>$rowData[9],  
                      'Email_ID'=>$rowData[10],  
                      'is_inter'=>(empty($rowData[9])&&!empty($rowData[10]))?'Y':'N'
                      // 'student_tbl_id'=>$studentData->id,  
                    );
                 }

                 if(in_array($template_id, [38])){//Certificate-TPSSL
                    $row=array(
                      'Unique_no'=>$rowData[0],
                      'Watermark_student_data'=>$rowData[1],  
                      'Watermark_TPSDI'=>$rowData[2],  
                      'student_name'=>$rowData[3],  
                      'Enrollment_NO'=>$rowData[4],  
                      'Program_Title'=>$rowData[5],  
                      'On_Date'=>$rowData[6],//Carbon::createFromFormat('d-m-Y', $rowData[6])->format('Y-m-d'),
                      'Student_Image'=>$rowData[7],  
                      'Place'=>$rowData[8],  
                      'Dealer_Name'=>$rowData[9],  
                      'Cert_type'=>$rowData[10],  
                      'Mobile_Number'=>$rowData[11],  
                      'Email_ID'=>$rowData[12],  
                      'Card_Category'=>$rowData[13],  
                      'Hub_Name'=>$rowData[14],  
                      'student_tbl_id'=>$student_table_id,  
                      'created_at'=>$created_at
                    );
                    

                    if (!empty($rowData[11])) {
                        $founduser->where('Mobile_Number', $rowData[11]);
                    }

                    if (!empty($rowData[12])) {
                        $founduser->orWhere('Email_ID', $rowData[12]);
                    }

                    if (!empty($rowData[12]) && !empty($rowData[11])) {
                        $founduser->where('Mobile_Number', $rowData[11])->orWhere('Email_ID', $rowData[12]);
                    }
                    $user_data=array(
                      'Student_Name'=>$rowData[3],
                      'Mobile_Number'=>$rowData[11],  
                      'Email_ID'=>$rowData[12],  
                      'is_inter'=>(empty($rowData[11])&&!empty($rowData[12]))?'Y':'N'  
                      // 'student_tbl_id'=>$studentData->id,  
                    );
                 }
                 if(in_array($template_id, [41])){//Certificate - College Name
                    $row=array(
                      'Unique_no'=>$rowData[0],
                      'Watermark_student_data'=>$rowData[1],  
                      'Watermark_TPSDI'=>$rowData[2],  
                      'student_name'=>$rowData[3],  
                      'roll_no'=>$rowData[4],  
                      'Enrollment_NO'=>$rowData[5],  
                      'Program_Title'=>$rowData[6],  
                      'On_Date'=>Carbon::createFromFormat('d-m-Y', $rowData[7])->format('Y-m-d'),
                      'Student_Image'=>$rowData[8],  
                      'Place'=>$rowData[9],
                      'Mobile_Number'=>$rowData[10],  
                      'Email_ID'=>$rowData[11],  
                      'Card_Category'=>$rowData[12],  
                      'Hub_Name'=>$rowData[13],  
                      'student_tbl_id'=>$student_table_id,  
                      'created_at'=> $created_at 
                    );
                    

                    if (!empty($rowData[10])) {
                        $founduser->where('Mobile_Number', $rowData[10]);
                    }

                    if (!empty($rowData[11])) {
                        $founduser->orWhere('Email_ID', $rowData[11]);
                    }

                    if (!empty($rowData[11]) && !empty($rowData[10])) {
                        $founduser->where('Mobile_Number', $rowData[10])->orWhere('Email_ID', $rowData[11]);
                    }
                    $user_data=array(
                      'Student_Name'=>$rowData[3],
                      'Mobile_Number'=>$rowData[10],  
                      'Email_ID'=>$rowData[11],  
                      'is_inter'=>(empty($rowData[10])&&!empty($rowData[11]))?'Y':'N'
                      // 'student_tbl_id'=>$studentData->id,  
                    );
                 }
                 if(in_array($template_id, [36])){//Certificate - Technical Colleges
                    $row=array(
                      'Unique_no'=>$rowData[0],
                      'Watermark_Insitute_Name'=>$rowData[1],  
                      'Watermark_TPSDI'=>$rowData[2],  
                      'Institute_name'=>$rowData[3],  
                      'From_Date'=>Carbon::createFromFormat('d-m-Y', $rowData[4])->format('Y-m-d'),
                      'To_Date'=>Carbon::createFromFormat('d-m-Y', $rowData[5])->format('Y-m-d'),
                      'Mobile_Number'=>$rowData[6],  
                      'Email_ID'=>$rowData[7],  
                      'Card_Category'=>$rowData[8],  
                      'Hub_Name'=>$rowData[9],  
                      'student_tbl_id'=>$student_table_id,  
                      'created_at'=>$created_at
                    );
                    

                    if (!empty($rowData[6])) {
                        $founduser->where('Mobile_Number', $rowData[6]);
                    }

                    if (!empty($rowData[7])) {
                        $founduser->orWhere('Email_ID', $rowData[7]);
                    }

                    if (!empty($rowData[7]) && !empty($rowData[6])) {
                        $founduser->where('Mobile_Number', $rowData[6])->orWhere('Email_ID', $rowData[7]);
                    }
                    $user_data=array(
                      'Student_Name'=>$rowData[3],
                      'Mobile_Number'=>$rowData[6],  
                      'Email_ID'=>$rowData[7],  
                      'is_inter'=>(empty($rowData[6])&&!empty($rowData[7]))?'Y':'N'
                      // 'student_tbl_id'=>$studentData->id,  
                    );
                 }
                 // dd($row);
                    CertificateDataMapping::create($row);
                // }
                

                $founduser = $founduser->first();
                // print_r($founduser);
                // echo empty($founduser);
                if(empty($founduser)){
                    
                    $newUser=FunctionalUsers::create($user_data);
                    $lastInsertId = $newUser->id;
                StudentTable::where('id',$student_table_id)->where('status',1)->update(['functional_user_id'=>$lastInsertId,'tpsdidoc_type'=>'Certificate']);
                }else{
                 StudentTable::where('id',$student_table_id)->where('status',1)->update(['functional_user_id'=>$founduser->id,'tpsdidoc_type'=>'Certificate']);   
                }
            }

                } catch (Exception $e) {
                    dd($e);
                }
                // coded by sewak end
    }
  
}
