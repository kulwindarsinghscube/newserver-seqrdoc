<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\models\ttd\TTD;
use App\models\Config;
use App\models\SystemConfig;
use App\models\StudentTable;
use App\models\PrintingDetail;
use Session,TCPDF,TCPDF_FONTS,Auth,DB,PDF;
use Validator;

class TtdCertificateController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $domain = \Request::getHost();
        $subdomain = explode('.', $domain); //$subdomain[0]="icat"   
        if($request->ajax()){
            $where_str    = "1 = ?";
            $where_params = array(1); 

            if (!empty($request->input('sSearch')))
            {
                $search     = $request->input('sSearch');
                $where_str .= " and ( username like \"%{$search}%\""
                . " or dob like \"%{$search}%\""
                . " or id_number like \"%{$search}%\""
                . " or name like \"%{$search}%\""
                . ")";
            }
            
            
            $status=$request->get('status');
            
           
            // if($status)
            // {
            //     $where_str.= " and (ttds.status = $status)";
            // }

            // if($status==1)
            // {
            //     $status='1';
            //     $where_str.= " and (ttds.status = $status)";
            // }
            // else if($status==0)
            // {
            //     $status='0';
            //     $where_str.=" and (ttds.status= $status)";
            // }

            $auth_site_id=Auth::guard('admin')->user()->site_id;                                               
              //for serial number
            $iDisplayStart=$request->input('iDisplayStart'); 
            DB::statement(DB::raw('set @rownum='.$iDisplayStart));
            DB::statement(DB::raw('set @rownum=0'));   
            $columns = [DB::raw('@rownum  := @rownum  + 1 AS rownum'),'id','full_name','dob','place_of_birth','td_no','sex','nationality','address_country_residance','mother_name','file','status'];
            //'doi','occupation','file','photo','fingerprint'

            $font_master_count = TTD::select($columns)
                //  ->whereRaw($where_str, $where_params)
                ->where('publish',1)
                ->where('status',$status)
                //  ->where('site_id',$auth_site_id)
                ->count();
                //  \DB::enableQueryLog();
            $fontMaster_list = TTD::select($columns)
                   ->where('publish',1)
                   ->where('status',$status);
                //    ->get();
                //    ->where('site_id',$auth_site_id)
                //    ->whereRaw($where_str, $where_params);
      
            if($request->get('iDisplayStart') != '' && $request->get('iDisplayLength') != ''){
                $fontMaster_list = $fontMaster_list->take($request->input('iDisplayLength'))
                ->skip($request->input('iDisplayStart'));
            } 
            
            // dd(DB::getQueryLog());
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
            
            $fontMaster_list = $fontMaster_list->OrderBy('updated_at','desc')->get();
            // $fontMaster_list = $fontMaster_list->OrderBy('id','desc')->get();
             
            $response['iTotalDisplayRecords'] = $font_master_count;
            $response['iTotalRecords'] = $font_master_count;
            $response['sEcho'] = intval($request->input('sEcho'));
            $response['aaData'] = $fontMaster_list->toArray();
            
            
            return $response;
        }
        return view('admin.ttd.index');
        // $data = TTD::orderBy('id', 'desc')->get();
        // return view('admin.ttd.index',compact('data'));
    }

    
    
    public function allRecordAjax(){
        $data = TTD::orderBy('id', 'desc')->get();
        return $data;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.ttd.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'td_no' => 'required|unique:ttds,td_no',
            'full_name' => 'required',
            'date' => 'required|date',
            'mother_name' => 'required',
            'sex' => 'required',
            'dob' => 'required|date|before:today',
            'place_of_birth' => 'required',
            'nationality' => 'required',
            'face_marks' => 'required',
            'address_country_residance' => 'required',
            'date_of_issue' => 'required|date',
            'issuing_authority' => 'required',
            'valid_unit' => 'required',
        ]);

        $domain = \Request::getHost();
        $subdomain = explode('.', $domain);

        // 'photo' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        // name dob birth_place id_number sex marital_status address mother_name doi occupation file photo fingerprint
        $data = new TTD();
        $data->td_no  = $request->td_no;
        $data->full_name = $request->full_name;
        $data->date = $request->date;
        $data->mother_name = $request->mother_name;
        $data->sex = $request->sex;
        $data->dob = $request->dob;
        $data->place_of_birth = $request->place_of_birth;
        $data->nationality = $request->nationality;
        $data->face_marks = $request->face_marks;
        $data->address_country_residance = $request->address_country_residance;
        $data->date_of_issue = $request->date_of_issue;
        $data->issuing_authority = $request->issuing_authority;
        $data->valid_unit = $request->valid_unit;
        $data->status = 'open';

		$root_path = public_path().'\\'.$subdomain[0];
        $image = $request->file('photo');
        $fingerprint = $request->file('fingerprint');
        $profileImage = '';
        $profileFingerPrint = '';

        $imageName = str_replace("/","_",$request->td_no);
        if ($image) {
            $destinationPath = $root_path.'/ttd_images/photos/';
            $profileImage = date('YmdHis')."_".$imageName. "." . $image->getClientOriginalExtension();
            $image->move($destinationPath, $profileImage);
            $data['photo'] = "$profileImage";
        }
        if ($fingerprint) {
            $destinationPath = $root_path.'/ttd_images/fingerprints/';
            $profileFingerPrint = date('YmdHis')."_".$imageName. "." . $fingerprint->getClientOriginalExtension();
            $fingerprint->move($destinationPath, $profileFingerPrint);
            $data['fingerprint'] = "$profileFingerPrint";
        }
        
        $data->save();
        // $link = '/'.$subdomain[0].'/backend/tcpdf/examples/'.$certName;
       
        
        return view('admin.ttd.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return view('admin.ttd.show');
    }


    public function getDetailAjax($id)
    {

        $ttdDetail = TTD::findOrFail($id);

        $ttdLog = DB::table('ttd_logs')->where('ttd_id',$id)->where('status','correction')->orderBy('id','desc')->first();
        return response()->json(['success' => true,'data'=>$ttdDetail,'ttdLog'=>$ttdLog]);

    }


    public function statusUpdate( Request $request ) {
    
        if($request->status != "approve") {
            $validator = Validator::make( $request->all(), [
                'comment' => 'required|string',
            ],
            [
                'comment.required' => 'Please Enter Comment'
            ]);
            
            if ( $validator->fails() ) {
                return response()->json( [ 'errors' => $validator->errors() ] );
            }
        }
        
        $id = $request->id;
        $ttdData = TTD::findOrFail($id);
        $ttdData->status = $request->status;

        if($request->status =='approve') {
            $certName = $this->pdfGenerateJob($ttdData);
            $ttdData->file = $certName;
        }

        $ttdData->save();
        if($ttdData) {
            DB::table('ttd_logs')->insert([
                'ttd_id' => $ttdData->id,
                'comment' => $request->comment,
                'status' => $request->status,
                'created_by' => Auth::guard('admin')->user()->id,
                'created_at' => date("Y-m-d H:i:s"),

            ]);
            
        }
        return response()->json( [ 'status' => true ,'message'=>'Status Updated.' ] );
    }


    public function ajaxUpdate( Request $request ) {
        
        $id = $request->edit_ttd_id;

        $validator = Validator::make( $request->all(), [
            'edit_td_number' => 'required|unique:ttds,td_no,'.$id,
            'edit_full_name' => 'required',
            'edit_date' => 'required|date',
            'edit_mother_name' => 'required',
            'edit_sex' => 'required',
            'edit_dob' => 'required|date|before:today',
            'edit_pob' => 'required',
            'edit_nationality' => 'required',
            'edit_face_marks' => 'required',
            'edit_address' => 'required',
            'edit_doi' => 'required|date',
            'edit_issuing_authority' => 'required',
            'edit_valid_unit' => 'required',
        ],
        [
            'edit_td_number.required' => 'Please TD No Here',
            'edit_td_number.unique' => 'Please TD No is already exist in other record.',
            'edit_full_name.required' => 'Please Full Name Here',
            'edit_mother_name.required' => 'Please Mother Name Here',
            'edit_sex.required' => 'Please Sex Here',
            'edit_dob.required' => 'Please Date Of Birth Here',
            'edit_pob.required' => 'Please Place Of Birth Here',
            'edit_nationality.required' => 'Please Nationality Here',
            'edit_face_marks.required' => 'Please Face Marks Here',
            'edit_address.required' => 'Please Address of Country Residence Here',
            'edit_doi.required' => 'Please Date of Issue Here',        
            'edit_issuing_authority.required' => 'Please Issuing Authority Here',
            'edit_valid_unit.required' => 'Please Valid Unit Here'
        ]);
        
        if ( $validator->fails() ) {
            return response()->json( [ 'errors' => $validator->errors() ] );
        }
        $ttdData = TTD::findOrFail($id);
        $ttdData->td_no = $request->edit_td_number;
        $ttdData->full_name = $request->edit_full_name;
        $ttdData->date = $request->edit_date;
        $ttdData->mother_name = $request->edit_mother_name;
        $ttdData->sex = $request->edit_sex;
        $ttdData->dob = $request->edit_dob;
        $ttdData->place_of_birth = $request->edit_pob;
        $ttdData->nationality = $request->edit_nationality;
        $ttdData->face_marks = $request->edit_face_marks;
        $ttdData->address_country_residance = $request->edit_address;
        $ttdData->date_of_issue = $request->edit_doi;
        $ttdData->issuing_authority = $request->edit_issuing_authority;
        $ttdData->valid_unit = $request->edit_valid_unit;
        $ttdData->status = 'open';
        $ttdData->save();
        if($ttdData) {
            DB::table('ttd_logs')->insert([
                'ttd_id' => $ttdData->id,
                'comment' => 'correction updated',
                'status' => 'correction',
                'created_by' => Auth::guard('admin')->user()->id,
                'created_at' => date("Y-m-d H:i:s"),

            ]);
            
        }
        return response()->json( [ 'status' => true ,'message'=>'Record Updated.'] );
    }



    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function pdfGenerateJob($ttdData) {

        
        $domain = \Request::getHost();
        $subdomain = explode('.', $domain);

        $root_path = public_path().'\\'.$subdomain[0];

        $photo_display = $root_path.'/ttd_images\photos\\'.$ttdData->photo;
        
        
        $serial_no=$ttdData->td_no;
        $GUID=$ttdData->td_no;
        $dt = date("_ymdHis");
        
        $str=$GUID.$dt;
        $codeContents = "Name:".$ttdData->name."\n\n".strtoupper(md5($str));
        $encryptedString = strtoupper(md5($str));

        $admin_id = \Auth::guard('admin')->user()->toArray(); 
        $template_id=100;
        $cardDetails=$this->getNextCardNo('TTDC');
        $card_serial_no=$cardDetails->next_serial_no;    
        
        $certName = str_replace("/", "_", $GUID) .".pdf";				
        $myPath = $root_path.'\backend\pdf_file'; //with background image
        $fileVerificationPath=$myPath . DIRECTORY_SEPARATOR . $certName;


        
        $pdfBig = new TCPDF('P', 'mm', array('210', '297'), true, 'UTF-8', false);
        $pdfBig->SetCreator(PDF_CREATOR);
        $pdfBig->SetAuthor('SSSL');
        $pdfBig->SetTitle('Temporary Travel Document');
        $pdfBig->SetSubject('');

        // remove default header/footer
        $pdfBig->setPrintHeader(false);
        $pdfBig->setPrintFooter(false);
        $pdfBig->SetAutoPageBreak(false, 0);
        $pdfBig->AddPage();
        $pdfBig->AddSpotColor('Spot Red', 30, 100, 90, 10);        // For Invisible
        $pdfBig->AddSpotColor('Spot Dark Green', 100, 50, 80, 45); // clear text on bottom red and in clear text logo
        $pdfBig->AddSpotColor('Spot Light Yellow', 0, 0, 100, 0);

        // PDF
        $pdf = new TCPDF('P', 'mm', array('210', '297'), true, 'UTF-8', false);
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('SSSL');
        $pdf->SetTitle('Temporary Travel Document');
        $pdf->SetSubject('');

        // remove default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetAutoPageBreak(false, 0);
        $pdf->AddPage();
        $pdf->AddSpotColor('Spot Red', 30, 100, 90, 10);        // For Invisible
        $pdf->AddSpotColor('Spot Dark Green', 100, 50, 80, 45); // clear text on bottom red and in clear text logo
        $pdf->AddSpotColor('Spot Light Yellow', 0, 0, 100, 0);



        //set fonts
        $arial = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\Arial.TTF', 'TrueTypeUnicode', '', 96);
        $arialb = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\Arialb.TTF', 'TrueTypeUnicode', '', 96);
        $ariali = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\ARIALI.TTF', 'TrueTypeUnicode', '', 96);
        

        $arialNarrow = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\arialn.TTF', 'TrueTypeUnicode', '', 96);
        $arialNarrowB = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\ARIALNB.TTF', 'TrueTypeUnicode', '', 96);
        $times = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\Times-New-Roman.ttf', 'TrueTypeUnicode', '', 96);
        $timesb = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\Times-New-Roman-Bold.ttf', 'TrueTypeUnicode', '', 96);
        $trebuc = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\trebuc.ttf', 'TrueTypeUnicode', '', 96);
        $trebucb = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\TREBUCBD.ttf', 'TrueTypeUnicode', '', 96);
        $trebuci = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\Trebuchet-MS-Italic.ttf', 'TrueTypeUnicode', '', 96);
        $graduateR = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\graduate-regular.ttf', 'TrueTypeUnicode', '', 96);
        $poppinsR = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\Poppins Regular 400.ttf', 'TrueTypeUnicode', '', 96);
        $poppinsM = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\Poppins Medium 500.ttf', 'TrueTypeUnicode', '', 96);

        $urdu = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\ARIALUNI.ttf', 'TrueTypeUnicode', '', 32);


        //set background image
        $high_res_bg ='TEMPORARY TRAVEL DOCUMENT_BG.jpg';
		$template_img_generate = public_path().'\\'.$subdomain[0].'\backend\canvas\bg_images\\'.$high_res_bg;
        $pdfBig->Image($template_img_generate, 0, 0, '210', '297', "JPG", '', 'R', true);
        $pdfBig->setPageMark(); 
        // start watermark 
        $pdfBig->SetFont($arial, '',11, '', false);
        $watermarkStr = $ttdData->full_name.' '.$ttdData->nationality.' '.$ttdData->valid_unit.' '.$ttdData->td_no;
        $security_line = '';
        for($d = 0; $d < 18; $d++) {
            $security_line .= $watermarkStr . ' ';
        }
       
        $pdfWidth = 210;
        $pdfHeight = 297;
        $j_increased=5;
        $line_gap = 30;
        $angle = 45;
                                
        $pdfBig->SetOverprint(true, true, 0);
            
        $pdfBig->SetTextColor(0, 0, 0, '', false, '');

        $pdfBig->SetAlpha(0.2);
        for ($i=0; $i <= $pdfHeight; $i+=$line_gap) {
            $pdfBig->SetXY(0,$i);
            $pdfBig->StartTransform();  
            $pdfBig->Rotate($angle);
            $pdfBig->Cell(0, 0, $security_line, 0, false, 'C');
            $pdfBig->StopTransform();
        }
       
        for ($j=0; $j < $pdfWidth; $j+=$line_gap) {
            //$pdf->SetXY($j+5,$pdfHeight);
            $pdfBig->SetXY($j+$j_increased,$pdfHeight);
            $pdfBig->StartTransform();  
            $pdfBig->Rotate($angle);
            $pdfBig->Cell(0, 0, $security_line, 0, false, 'C');
            $pdfBig->StopTransform();
        }
       
        $pdfBig->SetOverprint(false, false, 0);
        $pdfBig->SetAlpha(1);
        $pdfBig->SetAlpha(1);

        // PDF

        $pdf->SetFont($arial, '',11, '', false);
        $watermarkStr = $ttdData->full_name.' '.$ttdData->nationality.' '.$ttdData->valid_unit.' '.$ttdData->td_no;
        $security_line = '';
        for($d = 0; $d < 18; $d++) {
            $security_line .= $watermarkStr . ' ';
        }
       
        $pdfWidth = 210;
        $pdfHeight = 297;
        $j_increased=5;
        $line_gap = 30;
        $angle = 45;
                                
        $pdf->SetOverprint(true, true, 0);
            
        $pdf->SetTextColor(0, 0, 0, '', false, '');

        $pdf->SetAlpha(0.2);
        for ($i=0; $i <= $pdfHeight; $i+=$line_gap) {
            $pdf->SetXY(0,$i);
            $pdf->StartTransform();  
            $pdf->Rotate($angle);
            $pdf->Cell(0, 0, $security_line, 0, false, 'C');
            $pdf->StopTransform();
        }
       
        for ($j=0; $j < $pdfWidth; $j+=$line_gap) {
            //$pdf->SetXY($j+5,$pdfHeight);
            $pdf->SetXY($j+$j_increased,$pdfHeight);
            $pdf->StartTransform();  
            $pdf->Rotate($angle);
            $pdf->Cell(0, 0, $security_line, 0, false, 'C');
            $pdf->StopTransform();
        }
       
        $pdf->SetOverprint(false, false, 0);
        $pdf->SetAlpha(1);
        $pdf->SetAlpha(1);

        // end watermark 


        // start logo
        //$logoName = 'imt_logo';
        // $logo_path = public_path().'\\'.$subdomain[0].'\backend\canvas\images\imt_logo.png';
        // $logox = 92; 
        // $logoy = 11;
        // $logoWidth =30;
        // $logoHeight = 26;
        // // $ecc = 'L';
        // // $pixel_Size = 1;
        // // $frame_Size = 1;  
        
        // $pdfBig->Image($logo_path, $logox, $logoy, $logoWidth,  $logoHeight, 'PNG', '', true, false); 
        // end logo


        // $pdfBig->SetFont($arial, '',11, '', false);
        // $pdfBig->SetXY(0, 39);
        // $pdfBig->MultiCell(210, 0, "JAMHUURIYADDA FEDERAALKA SOOMAALIYA", 0, "C", 0, 0, '', '', true, 0, true);

        // $pdfBig->SetXY(0, 44);
        // $pdfBig->MultiCell(210, 0, "FEDERAL REPUBLIC OF SOMALIA", 0, "C", 0, 0, '', '', true, 0, true);

        // $pdfBig->SetLineStyle(array('width' => 0.7, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(137, 142, 145)));
        // $pdfBig->SetXY(3, 50);
        // $pdfBig->MultiCell(204, 0, "", "T", "L", 0, 0, '', '', true, 0, true);

        
        $pdfBig->SetLineStyle(array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)));
        $pdfBig->SetFont($arialb, '',11, '', false);
        $pdfBig->SetXY(0, 51);
        $pdfBig->MultiCell(210, 0, "TEMPORARY TRAVEL DOCUMENT", 0, "C", 0, 0, '', '', true, 0, true);
        
        $pdfBig->SetFont('aefurat', '', 9);
        $pdfBig->SetXY(0, 56);
        $pdfBig->MultiCell(210, 0, "وثيقة السفر المؤقتة", 0, "C", 0, 0, '', '', true, 0, true);


        $pdf->SetLineStyle(array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)));
        $pdf->SetFont($arialb, '',11, '', false);
        $pdf->SetXY(0, 51);
        $pdf->MultiCell(210, 0, "TEMPORARY TRAVEL DOCUMENT", 0, "C", 0, 0, '', '', true, 0, true);
        
        $pdf->SetFont('aefurat', '', 9);
        $pdf->SetXY(0, 56);
        $pdf->MultiCell(210, 0, "وثيقة السفر المؤقتة", 0, "C", 0, 0, '', '', true, 0, true);


        if($photo_display) {

            $profilePath = 'nobody';
            // nobody.jpg
            //$profile_path = public_path().'\\'.$subdomain[0].'\backend\canvas\images\/'.$profilePath.'.jpg';
            $profile_path = $photo_display;
            $profilex = 172; 
            $profiley = 52.9;
            $profileWidth =31;
            $profileHeight = 33;
            $ecc = 'L';
            $pixel_Size = 1;
            $frame_Size = 1;  
            
            $pdfBig->Image($profile_path, $profilex, $profiley, $profileWidth,  $profileHeight, '', '', true, false);
            $pdfBig->setPageMark();
            
            $pdf->Image($profile_path, $profilex, $profiley, $profileWidth,  $profileHeight, '', '', true, false);
            $pdf->setPageMark();


        } else {
            $pdfBig->SetFont($arialb, '',8, '', false);
            $pdfBig->SetTextColor(227, 214, 213, 10);
            $pdfBig->SetXY(169, 69);
            $pdfBig->Cell(31, 32, 'PHOTO', 1, $ln=0, 'C', 0, '', 0, false, 'C', 'C');

            $pdf->SetFont($arialb, '',8, '', false);
            $pdf->SetTextColor(227, 214, 213, 10);
            $pdf->SetXY(169, 69);
            $pdf->Cell(31, 32, 'PHOTO', 1, $ln=0, 'C', 0, '', 0, false, 'C', 'C');

        }        

        
        $pdfBig->SetFont($arial, '',10, '', false);
        $pdfBig->SetTextColor(0, 0, 0, 100);
        $pdfBig->SetXY(10, 72);
        $pdfBig->setCellPaddings($left = 0, $top = 0, $right = 0, $bottom = 0);
        $pdfBig->MultiCell(50, 0, "Tar/Date : <b>".date('d/m/Y', strtotime($ttdData->date))."</b> ", 0, "L", 0, 0, '', '', true, 0, true);


        $pdfBig->SetXY(10, 90);
        $pdfBig->setCellPaddings($left = 0, $top = 0, $right = 0, $bottom = 0);
        $pdfBig->MultiCell(190, 0, "Lambar/TD No : <b>".$ttdData->td_no."</b> ", 0, "R", 0, 0, '', '', true, 0, true);

        $pdf->SetFont($arial, '',10, '', false);
        $pdf->SetTextColor(0, 0, 0, 100);
        $pdf->SetXY(10, 72);
        $pdf->setCellPaddings($left = 0, $top = 0, $right = 0, $bottom = 0);
        $pdf->MultiCell(50, 0, "Tar/Date : <b>".date('d/m/Y', strtotime($ttdData->date))."</b> ", 0, "L", 0, 0, '', '', true, 0, true);


        $pdf->SetXY(10, 90);
        $pdf->setCellPaddings($left = 0, $top = 0, $right = 0, $bottom = 0);
        $pdf->MultiCell(190, 0, "Lambar/TD No : <b>".$ttdData->td_no."</b> ", 0, "R", 0, 0, '', '', true, 0, true);



        $tableY = 96;
        $tableHeight = 10;
        $pdfBig->SetFont($arial, '',11, '', false);
        $pdfBig->SetXY(10, $tableY);
        $pdfBig->setCellPaddings( $left = 2, $top = 3.5, $right = 1, $bottom = 0);
        $pdfBig->MultiCell(190, $tableHeight, "Magaca / Full Name: &nbsp;&nbsp;&nbsp;&nbsp;<B>".$ttdData->full_name."</B>", 'LRT', "L", 0, 0, '', '', true, 0, true);

        $pdf->SetFont($arial, '',11, '', false);
        $pdf->SetXY(10, $tableY);
        $pdf->setCellPaddings( $left = 2, $top = 3.5, $right = 1, $bottom = 0);
        $pdf->MultiCell(190, $tableHeight, "Magaca / Full Name: &nbsp;&nbsp;&nbsp;&nbsp;<B>".$ttdData->full_name."</B>", 'LRT', "L", 0, 0, '', '', true, 0, true);

        
        $pdfBig->SetXY(10, $tableY);
        $pdfBig->MultiCell(190, $tableHeight, "الاسم", 0, "R", 0, 0, '', '', true, 0, true);
        $pdf->SetXY(10, $tableY);
        $pdf->MultiCell(190, $tableHeight, "الاسم", 0, "R", 0, 0, '', '', true, 0, true);
        $tableY = $tableY+$tableHeight;
        
        $pdfBig->SetXY(10, $tableY);
        $pdfBig->MultiCell(190, $tableHeight, "Magaca Hooyo / Mother Name: &nbsp;&nbsp;&nbsp;&nbsp;<B>".$ttdData->mother_name."</B>", 'LRT', "L", 0, 0, '', '', true, 0, true);   
        $pdfBig->SetXY(10, $tableY);
        $pdfBig->MultiCell(190, $tableHeight, "اسم الأم", 0, "R", 0, 0, '', '', true, 0, true);

        $pdf->SetXY(10, $tableY);
        $pdf->MultiCell(190, $tableHeight, "Magaca Hooyo / Mother Name: &nbsp;&nbsp;&nbsp;&nbsp;<B>".$ttdData->mother_name."</B>", 'LRT', "L", 0, 0, '', '', true, 0, true);
        $pdf->SetXY(10, $tableY);
        $pdf->MultiCell(190, $tableHeight, "اسم الأم", 0, "R", 0, 0, '', '', true, 0, true);


        $tableY = $tableY+$tableHeight;

        //$pdfBig->SetFont('aefurat', '', 12);
        $pdfBig->SetXY(10, $tableY);
        $pdfBig->MultiCell(190, $tableHeight, "Jinsiga / Sex: &nbsp;&nbsp;&nbsp;&nbsp;<B>".strtoupper($ttdData->sex)."</B>", 'LRT', "L", 0, 0, '', '', true, 0, true);
        $pdfBig->SetXY(10, $tableY);
        $pdfBig->MultiCell(190, $tableHeight, "الجنس", 0, "R", 0, 0, '', '', true, 0, true);

        $pdf->SetXY(10, $tableY);
        $pdf->MultiCell(190, $tableHeight, "Jinsiga / Sex: &nbsp;&nbsp;&nbsp;&nbsp;<B>".strtoupper($ttdData->sex)."</B>", 'LRT', "L", 0, 0, '', '', true, 0, true);
        $pdf->SetXY(10, $tableY);
        $pdf->MultiCell(190, $tableHeight, "الجنس", 0, "R", 0, 0, '', '', true, 0, true);
        $tableY = $tableY+$tableHeight;

        $pdfBig->SetXY(10, $tableY);
        $pdfBig->MultiCell(190, $tableHeight, "Tariikhda Dhalashada / Date of Birth: &nbsp;&nbsp;&nbsp;&nbsp;<B>".date('d/m/Y', strtotime($ttdData->dob))."</B>", 'LRT', "L", 0, 0, '', '', true, 0, true);
        $pdfBig->SetXY(10, $tableY);
        $pdfBig->MultiCell(190, $tableHeight, "تاريخ الميلاد", 0, "R", 0, 0, '', '', true, 0, true);

        $pdf->SetXY(10, $tableY);
        $pdf->MultiCell(190, $tableHeight, "Tariikhda Dhalashada / Date of Birth: &nbsp;&nbsp;&nbsp;&nbsp;<B>".date('d/m/Y', strtotime($ttdData->dob))."</B>", 'LRT', "L", 0, 0, '', '', true, 0, true);
        $pdf->SetXY(10, $tableY);
        $pdf->MultiCell(190, $tableHeight, "تاريخ الميلاد", 0, "R", 0, 0, '', '', true, 0, true);
        $tableY = $tableY+$tableHeight;

        $pdfBig->SetXY(10, $tableY);
        $pdfBig->MultiCell(190, $tableHeight, "Goobta Dhalashada / Place of Birth: &nbsp;&nbsp;&nbsp;&nbsp;<B>".$ttdData->place_of_birth."</B>", 'LRT', "L", 0, 0, '', '', true, 0, true);
        $pdfBig->SetXY(10, $tableY);
        $pdfBig->MultiCell(190, $tableHeight, "مكان الميلاد", 0, "R", 0, 0, '', '', true, 0, true);

        $pdf->SetXY(10, $tableY);
        $pdf->MultiCell(190, $tableHeight, "Goobta Dhalashada / Place of Birth: &nbsp;&nbsp;&nbsp;&nbsp;<B>".$ttdData->place_of_birth."</B>", 'LRT', "L", 0, 0, '', '', true, 0, true);
        $pdf->SetXY(10, $tableY);
        $pdf->MultiCell(190, $tableHeight, "مكان الميلاد", 0, "R", 0, 0, '', '', true, 0, true);

        $tableY = $tableY+$tableHeight;

        $pdfBig->SetXY(10, $tableY);
        $pdfBig->MultiCell(190, $tableHeight, "Jinsiyadda / Nationality: &nbsp;&nbsp;&nbsp;&nbsp;<B>".$ttdData->nationality."</B>", 'LRT', "L", 0, 0, '', '', true, 0, true);
        $pdfBig->SetXY(10, $tableY);
        $pdfBig->MultiCell(190, $tableHeight, "الجنسية", 0, "R", 0, 0, '', '', true, 0, true);

        $pdf->SetXY(10, $tableY);
        $pdf->MultiCell(190, $tableHeight, "Jinsiyadda / Nationality: &nbsp;&nbsp;&nbsp;&nbsp;<B>".$ttdData->nationality."</B>", 'LRT', "L", 0, 0, '', '', true, 0, true);
        $pdf->SetXY(10, $tableY);
        $pdf->MultiCell(190, $tableHeight, "الجنسية", 0, "R", 0, 0, '', '', true, 0, true);

        $tableY = $tableY+$tableHeight;

        $pdfBig->SetXY(10, $tableY);
        $pdfBig->MultiCell(190, $tableHeight, "Calaamad Wajiga / Face marks: &nbsp;&nbsp;&nbsp;&nbsp;<B>".$ttdData->face_marks."</B>", 'LRT', "L", 0, 0, '', '', true, 0, true);
        $pdfBig->SetXY(10, $tableY);
        $pdfBig->MultiCell(190, $tableHeight, "علامة مميزة", 0, "R", 0, 0, '', '', true, 0, true);

        $pdf->SetXY(10, $tableY);
        $pdf->MultiCell(190, $tableHeight, "Calaamad Wajiga / Face marks: &nbsp;&nbsp;&nbsp;&nbsp;<B>".$ttdData->face_marks."</B>", 'LRT', "L", 0, 0, '', '', true, 0, true);
        $pdf->SetXY(10, $tableY);
        $pdf->MultiCell(190, $tableHeight, "علامة مميزة", 0, "R", 0, 0, '', '', true, 0, true);
        $tableY = $tableY+$tableHeight;

        $pdfBig->SetXY(10, $tableY);
        $pdfBig->MultiCell(190, $tableHeight+1, "Cinwaanka dalka uu deganyahay /<br>Address in the coutry of residence", 'LRT', "L", 0, 0, '', '', true, 0, true);
        $pdfBig->setCellPaddings( $left = 2, $top = 5, $right = 1, $bottom = 0);
        $pdfBig->SetXY(75, $tableY);
        $pdfBig->MultiCell(190, $tableHeight, "<B>".$ttdData->address_country_residance."</B>", 0, "L", 0, 0, '', '', true, 0, true);
        $pdfBig->SetXY(10, $tableY);
        $pdfBig->MultiCell(190, $tableHeight, "عنوان بلد الإقامة", 0, "R", 0, 0, '', '', true, 0, true);


        $pdf->SetXY(10, $tableY);
        $pdf->MultiCell(190, $tableHeight+1, "Cinwaanka dalka uu deganyahay /<br>Address in the coutry of residence", 'LRT', "L", 0, 0, '', '', true, 0, true);
        $pdf->setCellPaddings( $left = 2, $top = 5, $right = 1, $bottom = 0);
        $pdf->SetXY(75, $tableY);
        $pdf->MultiCell(190, $tableHeight, "<B>".$ttdData->address_country_residance."</B>", 0, "L", 0, 0, '', '', true, 0, true);   
        $pdf->SetXY(10, $tableY);
        $pdf->MultiCell(190, $tableHeight, "عنوان بلد الإقامة", 0, "R", 0, 0, '', '', true, 0, true);


        $tableY = $tableY+$tableHeight+3;

        $pdfBig->setCellPaddings( $left = 2, $top = 3.5, $right = 1, $bottom = 0);
        $pdfBig->SetXY(10, $tableY);
        $pdfBig->MultiCell(190, $tableHeight, "Tariikhda la bixiyay / Date of issue: &nbsp;&nbsp;&nbsp;&nbsp;<B>".date('d/m/Y', strtotime($ttdData->date_of_issue))."</B>", 'LRT', "L", 0, 0, '', '', true, 0, true);
        $pdfBig->SetXY(10, $tableY);
        $pdfBig->MultiCell(190, $tableHeight, "تاريخ الإصدار", 0, "R", 0, 0, '', '', true, 0, true);

        $pdf->setCellPaddings( $left = 2, $top = 3.5, $right = 1, $bottom = 0);
        $pdf->SetXY(10, $tableY);
        $pdf->MultiCell(190, $tableHeight, "Tariikhda la bixiyay / Date of issue: &nbsp;&nbsp;&nbsp;&nbsp;<B>".date('d/m/Y', strtotime($ttdData->date_of_issue))."</B>", 'LRT', "L", 0, 0, '', '', true, 0, true);
        $pdf->SetXY(10, $tableY);
        $pdf->MultiCell(190, $tableHeight, "تاريخ الإصدار", 0, "R", 0, 0, '', '', true, 0, true);

        $tableY = $tableY+$tableHeight;


        $pdfBig->SetXY(10, $tableY);
        $pdfBig->MultiCell(190, $tableHeight, "Hay'adda bixisay / Issuing Authority: &nbsp;&nbsp;&nbsp;&nbsp;<B>".$ttdData->issuing_authority."</B>", 'LRT', "L", 0, 0, '', '', true, 0, true);
        $pdfBig->SetXY(10, $tableY);
        $pdfBig->MultiCell(190, $tableHeight, "جهة الإصدار", 0, "R", 0, 0, '', '', true, 0, true);

        $pdf->SetXY(10, $tableY);
        $pdf->MultiCell(190, $tableHeight, "Hay'adda bixisay / Issuing Authority: &nbsp;&nbsp;&nbsp;&nbsp;<B>".$ttdData->issuing_authority."</B>", 'LRT', "L", 0, 0, '', '', true, 0, true);
        $pdf->SetXY(10, $tableY);
        $pdf->MultiCell(190, $tableHeight, "جهة الإصدار", 0, "R", 0, 0, '', '', true, 0, true);

        $tableY = $tableY+$tableHeight;

        $pdfBig->SetXY(10, $tableY);
        $pdfBig->MultiCell(190, $tableHeight, "Mudada ay ansax tahay / Valid until: &nbsp;&nbsp;&nbsp;&nbsp;<B>".$ttdData->valid_unit."</B>", 'LRTB', "L", 0, 0, '', '', true, 0, true);
        $pdfBig->SetXY(10, $tableY);
        $pdfBig->MultiCell(190, $tableHeight, "مدة الصلاحية", 0, "R", 0, 0, '', '', true, 0, true);

        $pdf->SetXY(10, $tableY);
        $pdf->MultiCell(190, $tableHeight, "Mudada ay ansax tahay / Valid until: &nbsp;&nbsp;&nbsp;&nbsp;<B>".$ttdData->valid_unit."</B>", 'LRTB', "L", 0, 0, '', '', true, 0, true);
        $pdf->SetXY(10, $tableY);
        $pdf->MultiCell(190, $tableHeight, "مدة الصلاحية", 0, "R", 0, 0, '', '', true, 0, true);

        
        

        $pdfBig->SetFont($arial, '',8, '', false);
        $pdfBig->SetXY(10, 222);
        $pdfBig->setCellPaddings($left = 0, $top = 1, $right = 0, $bottom = 0);
        $pdfBig->MultiCell(190, 0, "Warqaddaan waxaa lagu gali karaa oo kali ah xuduudaha dalka jamhuuriyadda federaalka Soomaaliya", 0, "L", 0, 0, '', '', true, 0, true);
        
        $pdfBig->SetXY(10, 228);
        $pdfBig->MultiCell(190, 0, "This TTD for use only to travel bak to the Federal Republic of Somalia", 0, "L", 0, 0, '', '', true, 0, true);

        $pdfBig->SetFont('aefurat', '', 9);
        $pdfBig->SetXY(0, 236);
        $pdfBig->MultiCell(210, 0, "هذه الوثيقة للسفر المؤقتة صالحة للعودة إلى جمهورية الصومال الفدرالية فقط", 0, "C", 0, 0, '', '', true, 0, true);

        $pdfBig->SetFont($arial, '',10, '', false);
        $pdfBig->SetXY(10, 255);
        $pdfBig->MultiCell(190, 0, "Signature: ___________________", 0, "L", 0, 0, '', '', true, 0, true);


        $pdf->SetFont($arial, '',8, '', false);
        $pdf->SetXY(10, 222);
        $pdf->setCellPaddings($left = 0, $top = 1, $right = 0, $bottom = 0);
        $pdf->MultiCell(190, 0, "Warqaddaan waxaa lagu gali karaa oo kali ah xuduudaha dalka jamhuuriyadda federaalka Soomaaliya", 0, "L", 0, 0, '', '', true, 0, true);
        
        $pdf->SetXY(10, 228);
        $pdf->MultiCell(190, 0, "This TTD for use only to travel bak to the Federal Republic of Somalia", 0, "L", 0, 0, '', '', true, 0, true);

        $pdf->SetFont('aefurat', '', 9);
        $pdf->SetXY(0, 236);
        $pdf->MultiCell(210, 0, "هذه الوثيقة للسفر المؤقتة صالحة للعودة إلى جمهورية الصومال الفدرالية فقط", 0, "C", 0, 0, '', '', true, 0, true);

        $pdf->SetFont($arial, '',10, '', false);
        $pdf->SetXY(10, 255);
        $pdf->MultiCell(190, 0, "Signature: ___________________", 0, "L", 0, 0, '', '', true, 0, true);


        // $pdfBig->SetLineStyle(array('width' => 1.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(137, 142, 145)));
        // $pdfBig->SetXY(3, 280);
        // $pdfBig->MultiCell(204, 0, "", "T", "L", 0, 0, '', '', true, 0, true);

        // $pdfBig->SetLineStyle(array('width' => 0.4, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)));
        // $pdfBig->SetXY(2.5, 277);
        // $pdfBig->MultiCell(205, 0, "", "T", "L", 0, 0, '', '', true, 0, true);


        
        $pdfBig->SetFont($ariali, '',8, '', false);
        $pdfBig->SetXY(7, 280);
        $pdfBig->MultiCell(196, 0, "Immigration and Naturalization Directorate, Mogadishu, Somalia", 0, "C", 0, 0, '', '', true, 0, true);

        $pdfBig->SetXY(7, 283.5);
        $pdfBig->MultiCell(196, 0, "Phone: +252 61 055 5001, Email: <u>info@immigration.gov.so</u> www.immigration.gov.so", 0, "C", 0, 0, '', '', true, 0, true);

        $pdf->SetFont($ariali, '',8, '', false);
        $pdf->SetXY(7, 280);
        $pdf->MultiCell(196, 0, "Immigration and Naturalization Directorate, Mogadishu, Somalia", 0, "C", 0, 0, '', '', true, 0, true);

        $pdf->SetXY(7, 283.5);
        $pdf->MultiCell(196, 0, "Phone: +252 61 055 5001, Email: <u>info@immigration.gov.so</u> www.immigration.gov.so", 0, "C", 0, 0, '', '', true, 0, true);

        

        $nameOrg = $ttdData->full_name;
        
        // Ghost image
        $ghost_font_size = '13';
        $ghostImagex = 141;
        $ghostImagey = 211;
        //$ghostImageWidth = 55; //68
        //$ghostImageHeight = 9.8;
        $ghostImageWidth = 39.405983333;
        $ghostImageHeight = 10;
        $name = substr(str_replace(' ', '', strtoupper($nameOrg)) , 0, 6);
        $tmpDir = $this->createTemp(public_path() . '/backend/images/ghosttemp/temp');

        $w = $this->CreateMessage($tmpDir, $name, $ghost_font_size, '');

        $pdfBig->Image("$tmpDir/" . $name . "" . $ghost_font_size . ".png", $ghostImagex, $ghostImagey, $w, $ghostImageHeight, "PNG", '', 'L', true, 3600);
        $pdfBig->setPageMark();
        
        $pdf->Image("$tmpDir/" . $name . "" . $ghost_font_size . ".png", $ghostImagex, $ghostImagey, $w, $ghostImageHeight, "PNG", '', 'L', true, 3600);
        $pdf->setPageMark();



        //qr code
        //$GUID='2666';    
        //$dt = date("_ymdHis");
        $str=$GUID.$dt;
        // $codeContents = $QR_Output."\n\n".strtoupper(md5($str));
        // $codeContents = "[".$student_id." - ". $candidate_name ."]"."\n\n" ."\n\n".strtoupper(md5($str));
        // $codeContents = "[".$student_id." - ". $candidate_name ."]";
        // $codeContents .="\n";
        // $codeContents .= "[CGPA - ".$cgpa." (". $cgpaRemark .")]";
        // $codeContents .="\n";
        // $codeContents .= "[Percentage - ".$Percentage."%]";
        // $codeContents .="\n";
        // $codeContents .= $Programme."(".$major.")";
        // $codeContents .="\n\n".strtoupper(md5($str));
        $encryptedString = strtoupper(md5($serial_no.$dt));

        // echo $encryptedString;
        // echo "<br>";


        $qr_code_path = public_path().'\\'.$subdomain[0].'\backend\canvas\images\qr\/'.$encryptedString.'.png';
        $qrCodex = 180; 
        $qrCodey = 245;
        $qrCodeWidth =22;
        $qrCodeHeight = 22;
        $ecc = 'L';
        $pixel_Size = 1;
        $frame_Size = 1;  
        // \PHPQRCode\QRcode::png($codeContents, $qr_code_path, $ecc, $pixel_Size, $frame_Size);

        \QrCode::backgroundColor(255, 255, 0)            
            ->format('png')        
            ->size(500)    
            ->generate($encryptedString, $qr_code_path);
        
        $pdfBig->Image($qr_code_path, $qrCodex, $qrCodey, $qrCodeWidth,  $qrCodeHeight, 'png', '', true, false);   
        $pdfBig->setPageMark();

        $pdf->Image($qr_code_path, $qrCodex, $qrCodey, $qrCodeWidth,  $qrCodeHeight, 'png', '', true, false);   
        $pdf->setPageMark();


        // microtext
        $nameOrg = $nameOrg;

        $microtext_line1 = '';
        for($d = 0; $d < 100; $d++) {
            $microtext_line1 .= $nameOrg . ' ';
        }

        $microlinestr=str_replace(' ','',$microtext_line1);

        $pdfBig->SetFont($arialb, '', 1.2, '', false);
        $pdfBig->SetTextColor(0, 0, 0);
        $pdfBig->StartTransform();
        $pdfBig->SetXY(180, 264);

        $microlinestr1 = substr($microlinestr,0,55);
        $pdfBig->MultiCell(22, 0, $microlinestr1, "", "C", 0, 0, '', '', true, 0, true);
        $pdfBig->StopTransform();


        $pdf->SetFont($arialb, '', 1.2, '', false);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->StartTransform();
        $pdf->SetXY(180, 264);

        $pdf->MultiCell(22, 0, $microlinestr1, "", "C", 0, 0, '', '', true, 0, true);
        $pdf->StopTransform();
        // microtext
        // printable PDF
        // printable PDF

        $certName1 = str_replace("/", "_", $GUID) .".pdf";        
        $myPath1 = $root_path.'\backend\tcpdf\examples\preview'; //edit the path
        $fileVerificationPath=$myPath . DIRECTORY_SEPARATOR . $certName1;
        $pdfBig->output($myPath1 . DIRECTORY_SEPARATOR . $certName1, 'F');


        $pdf->output($myPath . DIRECTORY_SEPARATOR . $certName, 'F');
        
        $this->addCertificate($serial_no, $certName, $dt,$template_id,$admin_id);

        $username = $admin_id['username'];
        date_default_timezone_set('Asia/Kolkata');
        $print_datetime = date("Y-m-d H:i:s");
        

        $print_count = $this->getPrintCount($serial_no);
        $printer_name = "";
        $print_serial_no = $this->nextPrintSerial();
        $this->addPrintDetails($username, $print_datetime, $printer_name, $print_count, $print_serial_no, $serial_no,'IMTC',$admin_id,$card_serial_no);
        
        // save
        return $certName;

        // $data->file = $certName;

    }

    public function addCertificate($serial_no, $certName, $dt,$template_id,$admin_id)
    {

        $domain = \Request::getHost();
        $subdomain = explode('.', $domain);

        $file1 = public_path().'/backend/temp_pdf_file/'.$certName;
        $file2 = public_path().'/backend/pdf_file/'.$certName;
        
        //Updated by Mandar for api based pdf generation
        if(Auth::guard('admin')->user()){
            $auth_site_id=Auth::guard('admin')->user()->site_id;
        }else{
            $auth_site_id=$this->pdf_data['auth_site_id'];
        } 

        $systemConfig = SystemConfig::where('site_id',$auth_site_id)->first();

        $pdfActualPath=public_path().'/'.$subdomain[0].'/backend/pdf_file/'.$certName;

        /*copy($file1, $file2);        
        $aws_qr = \File::copy($file2,$pdfActualPath);
        @unlink($file2);
		$source=\Config::get('constant.directoryPathBackward')."\\backend\\temp_pdf_file\\".$certName;
		$output=\Config::get('constant.directoryPathBackward').$subdomain[0]."\\backend\\pdf_file\\".$certName; 
		CoreHelper::compressPdfFile($source,$output);
        @unlink($file1);*/

        //Sore file on azure server

        $sts = '1';
        $datetime  = date("Y-m-d H:i:s");
        $ses_id  = $admin_id["id"];
        $certName = str_replace("/", "_", $certName);

        $get_config_data = Config::select('configuration')->first();
     
        $c = explode(", ", $get_config_data['configuration']);
        $key = "";


        $tempDir = public_path().'/backend/qr';
        $key = strtoupper(md5($serial_no.$dt)); 
        $codeContents = $key;
        $fileName = $key.'.png'; 
        
        $urlRelativeFilePath = 'qr/'.$fileName; 

        if($systemConfig['sandboxing'] == 1){
        $resultu = SbStudentTable::where('serial_no','T-'.$serial_no)->update(['status'=>'0']);
            // Insert the new record
        
            $result = SbStudentTable::create(['serial_no'=>'T-'.$serial_no,'certificate_filename'=>$certName,'template_id'=>$template_id,'key'=>$key,'path'=>$urlRelativeFilePath,'created_at'=>$datetime,'created_by'=>$ses_id,'updated_at'=>$datetime,'updated_by'=>$ses_id,'status'=>$sts,'site_id'=>$auth_site_id]);
        }else{
            $resultu = StudentTable::where('serial_no',''.$serial_no)->update(['status'=>'0']);
            // Insert the new record
    
            $result = StudentTable::create(['serial_no'=>$serial_no,'certificate_filename'=>$certName,'template_id'=>$template_id,'key'=>$key,'path'=>$urlRelativeFilePath,'created_at'=>$datetime,'created_by'=>$ses_id,'updated_at'=>$datetime,'updated_by'=>$ses_id,'status'=>$sts,'site_id'=>$auth_site_id,'template_type'=>2]);
        }
        
    }   

    public function getPrintCount($serial_no)
    {
        //Updated by Mandar for api based pdf generation
        if(Auth::guard('admin')->user()){
            $auth_site_id=Auth::guard('admin')->user()->site_id;
        }else{
            $auth_site_id=$this->pdf_data['auth_site_id'];
        }
        $systemConfig = SystemConfig::where('site_id',$auth_site_id)->first();
        $numCount = PrintingDetail::select('id')->where('sr_no',$serial_no)->count();
        
        return $numCount + 1;
    
}    

    public function addPrintDetails($username, $print_datetime, $printer_name, $printer_count, $print_serial_no, $sr_no,$template_name,$admin_id,$card_serial_no)
    {
       
        $sts = 1;
        $datetime = date("Y-m-d H:i:s");
        $ses_id = $admin_id["id"];

        //Updated by Mandar for api based pdf generation
        if(Auth::guard('admin')->user()){
            $auth_site_id=Auth::guard('admin')->user()->site_id;
        }else{
            $auth_site_id=$this->pdf_data['auth_site_id'];
        }

        $systemConfig = SystemConfig::where('site_id',$auth_site_id)->first();

        if($systemConfig['sandboxing'] == 1){
        $result = PrintingDetail::create(['username'=>$username,'print_datetime'=>$print_datetime,'printer_name'=>$printer_name,'print_count'=>$printer_count,'print_serial_no'=>$print_serial_no,'sr_no'=>'T-'.$sr_no,'template_name'=>$template_name,'created_at'=>$datetime,'created_by'=>$ses_id,'updated_at'=>$datetime,'updated_by'=>$ses_id,'status'=>$sts,'site_id'=>$auth_site_id,'publish'=>1]);
        }else{
        $result = PrintingDetail::create(['username'=>$username,'print_datetime'=>$print_datetime,'printer_name'=>$printer_name,'print_count'=>$printer_count,'print_serial_no'=>$print_serial_no,'sr_no'=>$sr_no,'template_name'=>$template_name,'created_at'=>$datetime,'created_by'=>$ses_id,'updated_at'=>$datetime,'updated_by'=>$ses_id,'status'=>$sts,'site_id'=>$auth_site_id,'publish'=>1]);    
        }
    }    

    public function nextPrintSerial()
    {
        $current_year = 'PN/' . $this->getFinancialyear() . '/';
        // find max
        $maxNum = 0;
        
        //Updated by Mandar for api based pdf generation
        if(Auth::guard('admin')->user()){
            $auth_site_id=Auth::guard('admin')->user()->site_id;
        }else{
            $auth_site_id=$this->pdf_data['auth_site_id'];
        }

        $systemConfig = SystemConfig::where('site_id',$auth_site_id)->first();
        $result = \DB::select("SELECT COALESCE(MAX(CONVERT(SUBSTR(print_serial_no, 10), UNSIGNED)), 0) AS next_num "
                . "FROM printing_details WHERE SUBSTR(print_serial_no, 1, 9) = '$current_year'");
        //get next num
        $maxNum = $result[0]->next_num + 1;
       
        return $current_year . $maxNum;
    }

    public function getFinancialyear()
    {
        $yy = date('y');
        $mm = date('m');
        $fy = str_pad($yy, 2, "0", STR_PAD_LEFT);
        if($mm > 3)
            $fy = $fy . "-" . ($yy + 1);
        else
            $fy = str_pad($yy - 1, 2, "0", STR_PAD_LEFT) . "-" . $fy;
        return $fy;
    }

    public function getNextCardNo($template_name)
    { 
        //Updated by Mandar for api based pdf generation
        if(Auth::guard('admin')->user()){
            $auth_site_id=Auth::guard('admin')->user()->site_id;
        }else{
            $auth_site_id=$this->pdf_data['auth_site_id'];
        
        }
        $systemConfig = SystemConfig::where('site_id',$auth_site_id)->first();

        if($systemConfig['sandboxing'] == 1){
        $result = \DB::select("SELECT * FROM sb_card_serial_numbers WHERE template_name = '$template_name'");
        }else{
        $result = \DB::select("SELECT * FROM card_serial_numbers WHERE template_name = '$template_name'");
        }
          
        return $result[0];
    }

    public function updateCardNo($template_name,$count,$next_serial_no)
    { 
        //Updated by Mandar for api based pdf generation
        if(Auth::guard('admin')->user()){
            $auth_site_id=Auth::guard('admin')->user()->site_id;
        }else{
            $auth_site_id=$this->pdf_data['auth_site_id'];
        }

        $systemConfig = SystemConfig::where('site_id',$auth_site_id)->first();
        if($systemConfig['sandboxing'] == 1){
        $result = \DB::select("UPDATE sb_card_serial_numbers SET card_count='$count',next_serial_no='$next_serial_no' WHERE template_name = '$template_name'");
        }else{
        $result = \DB::select("UPDATE card_serial_numbers SET card_count='$count',next_serial_no='$next_serial_no' WHERE template_name = '$template_name'");
        }
        
        return $result;
    }


    public function testPdf()
    { 

        $domain = \Request::getHost();
        echo $domain;
        $subdomain = explode('.', $domain);

        $pdf = new TCPDF('P', 'mm', array('210', '297'), true, 'UTF-8', false);
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('SSSL');
        $pdf->SetTitle('Temporary Travel Document');
        $pdf->SetSubject('');

        // remove default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetAutoPageBreak(false, 0);
        $pdf->AddPage();
        //$pdf->setLink('', 0);
        //$pdf->setLink(0); 
        // add spot colors
        $pdf->AddSpotColor('Spot Red', 30, 100, 90, 10);        // For Invisible
        $pdf->AddSpotColor('Spot Dark Green', 100, 50, 80, 45); // clear text on bottom red and in clear text logo
        $pdf->AddSpotColor('Spot Light Yellow', 0, 0, 100, 0);
        //set fonts
        $arial = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\Arial.TTF', 'TrueTypeUnicode', '', 96);
        $arialb = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\Arialb.TTF', 'TrueTypeUnicode', '', 96);
        $ariali = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\ARIALI.TTF', 'TrueTypeUnicode', '', 96);
        

        $arialNarrow = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\arialn.TTF', 'TrueTypeUnicode', '', 96);
        $arialNarrowB = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\ARIALNB.TTF', 'TrueTypeUnicode', '', 96);
        $times = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\Times-New-Roman.ttf', 'TrueTypeUnicode', '', 96);
        $timesb = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\Times-New-Roman-Bold.ttf', 'TrueTypeUnicode', '', 96);
        $trebuc = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\trebuc.ttf', 'TrueTypeUnicode', '', 96);
        $trebucb = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\TREBUCBD.ttf', 'TrueTypeUnicode', '', 96);
        $trebuci = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\Trebuchet-MS-Italic.ttf', 'TrueTypeUnicode', '', 96);
        $graduateR = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\graduate-regular.ttf', 'TrueTypeUnicode', '', 96);
        $poppinsR = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\Poppins Regular 400.ttf', 'TrueTypeUnicode', '', 96);
        $poppinsM = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\Poppins Medium 500.ttf', 'TrueTypeUnicode', '', 96);

        $urdu = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\ARIALUNI.ttf', 'TrueTypeUnicode', '', 32);


        // start watermark 
        $pdf->SetFont($arial, '',11, '', false);
        $str = "ADAN MOHAMED IKAR  SOMALI 3 MONTHS ESR/HSJ/001/2018";
        $security_line = '';
        for($d = 0; $d < 5; $d++) {
            $security_line .= $str . ' ';
        }
       
        $pdfWidth = 210;
        $pdfHeight = 297;
        $j_increased=5;
        $line_gap = 30;
        $angle = 45;
                                
        $pdf->SetOverprint(true, true, 0);
            
        $pdf->SetTextColor(0, 0, 0, '', false, '');

        $pdf->SetAlpha(0.2);
        for ($i=0; $i <= $pdfHeight; $i+=$line_gap) {
            $pdf->SetXY(0,$i);
            $pdf->StartTransform();  
            $pdf->Rotate($angle);
            $pdf->Cell(0, 0, $security_line, 0, false, 'C');
            $pdf->StopTransform();
        }
       
        for ($j=0; $j < $pdfWidth; $j+=$line_gap) {
            //$pdf->SetXY($j+5,$pdfHeight);
            $pdf->SetXY($j+$j_increased,$pdfHeight);
            $pdf->StartTransform();  
            $pdf->Rotate($angle);
            $pdf->Cell(0, 0, $security_line, 0, false, 'C');
            $pdf->StopTransform();
        }
       
        $pdf->SetOverprint(false, false, 0);
        $pdf->SetAlpha(1);
        $pdf->SetAlpha(1);

        // end watermark 


        // start logo
        //$logoName = 'imt_logo';
        $logo_path = public_path().'\\'.$subdomain[0].'\backend\canvas\images\imt_logo.png';
        $logox = 92; 
        $logoy = 11;
        $logoWidth =30;
        $logoHeight = 26;
        // $ecc = 'L';
        // $pixel_Size = 1;
        // $frame_Size = 1;  
        
        //$pdf->Image($logo_path, $logox, $logoy, $logoWidth,  $logoHeight, 'PNG', '', true, false); 
        // end logo
        
        

        $pdf->SetFont($arial, '',11, '', false);
        $pdf->SetXY(0, 39);
        $pdf->MultiCell(210, 0, "JAMHUURIYADDA FEDERAALKA SOOMAALIYA", 0, "C", 0, 0, '', '', true, 0, true);

        $pdf->SetXY(0, 44);
        $pdf->MultiCell(210, 0, "FEDERAL REPUBLIC OF SOMALIA", 0, "C", 0, 0, '', '', true, 0, true);

        $pdf->SetLineStyle(array('width' => 0.7, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(137, 142, 145)));
        $pdf->SetXY(3, 50);
        $pdf->MultiCell(204, 0, "", "T", "L", 0, 0, '', '', true, 0, true);

        
        $pdf->SetLineStyle(array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)));
        $pdf->SetFont($arialb, '',11, '', false);
        $pdf->SetXY(0, 51);
        $pdf->MultiCell(210, 0, "TEMPORARY TRAVEL DOCUMENT", 0, "C", 0, 0, '', '', true, 0, true);
        
        $pdf->SetFont($urdu, '',11, '', false);
        $pdf->SetXY(0, 56);
        $pdf->MultiCell(210, 0, "عارضی سفری دستاویز", 0, "C", 0, 0, '', '', true, 0, true);

        
        $pdf->SetFont($arialb, '',8, '', false);
        $pdf->SetTextColor(227, 214, 213, 10);
        $pdf->SetXY(165, 69);
        $pdf->Cell(31, 32, 'PHOTO', 1, $ln=0, 'C', 0, '', 0, false, 'C', 'C');


        $profilePath = 'nobody';
        // nobody.jpg
        $profile_path = public_path().'\\'.$subdomain[0].'\backend\canvas\images\/'.$profilePath.'.jpg';
        $profilex = 172; 
        $profiley = 52.9;
        $profileWidth =31;
        $profileHeight = 33;
        $ecc = 'L';
        $pixel_Size = 1;
        $frame_Size = 1;  
        
        $pdf->Image($profile_path, $profilex, $profiley, $profileWidth,  $profileHeight, '', '', true, false);  


        $pdf->SetFont($arial, '',10, '', false);
        $pdf->SetTextColor(0, 0, 0, 100);
        $pdf->SetXY(10, 72);
        $pdf->MultiCell(50, 0, "Tar/Date : <b>27/11/2023</b> ", 0, "L", 0, 0, '', '', true, 0, true);


        $pdf->SetXY(10, 90);
        $pdf->setCellPaddings( $left = 0, $top = 0, $right = 0, $bottom = 0);
        $pdf->MultiCell(193, 0, "Lambar/TD No : <b>ESR/HSJ/001/2018</b> ", 0, "R", 0, 0, '', '', true, 0, true);



        $tableY = 96;
        $tableHeight = 10;
        $pdf->SetFont($arial, '',11, '', false);
        $pdf->SetXY(7, $tableY);
        $pdf->setCellPaddings( $left = 2, $top = 3.5, $right = 1, $bottom = 0);
        $pdf->MultiCell(196, $tableHeight, "Magaca / Full Name: &nbsp;&nbsp;&nbsp;&nbsp;<B>ADAN MOHAMED IKAR</B>", 'LRT', "L", 0, 0, '', '', true, 0, true);
        
        $pdf->SetXY(7, $tableY);
        $pdf->MultiCell(196, $tableHeight, "الاسم", 0, "R", 0, 0, '', '', true, 0, true);
        $tableY = $tableY+$tableHeight;
        
        $pdf->SetXY(7, $tableY);
        //$pdf->setCellPaddings( $left = 2, $top = 3.5, $right = 0, $bottom = 0);
        $pdf->MultiCell(196, $tableHeight, "Magaca Hooyo / Mother Name: &nbsp;&nbsp;&nbsp;&nbsp;<B>MANA MOHAMED ABU</B>", 'LRT', "L", 0, 0, '', '', true, 0, true);
        
        $pdf->SetXY(7, $tableY);
        $pdf->MultiCell(196, $tableHeight, "اسم الأم", 0, "R", 0, 0, '', '', true, 0, true);

        $tableY = $tableY+$tableHeight;

        $pdf->SetXY(7, $tableY);
        $pdf->MultiCell(196, $tableHeight, "Jinsiga / Sex: &nbsp;&nbsp;&nbsp;&nbsp;<B>MALE</B>", 'LRT', "L", 0, 0, '', '', true, 0, true);

        $pdf->SetXY(7, $tableY);
        $pdf->MultiCell(196, $tableHeight, "الجنس", 0, "R", 0, 0, '', '', true, 0, true);
        $tableY = $tableY+$tableHeight;

        $pdf->SetXY(7, $tableY);
        $pdf->MultiCell(196, $tableHeight, "Tariikhda Dhalashada / Date of Birth: &nbsp;&nbsp;&nbsp;&nbsp;<B>30/02/2023</B>", 'LRT', "L", 0, 0, '', '', true, 0, true);
        
        $pdf->SetXY(7, $tableY);
        $pdf->MultiCell(196, $tableHeight, "تاريخ الميلاد", 0, "R", 0, 0, '', '', true, 0, true);
        $tableY = $tableY+$tableHeight;

        $pdf->SetXY(7, $tableY);
        $pdf->MultiCell(196, $tableHeight, "Goobta Dhalashada / Place of Birth: &nbsp;&nbsp;&nbsp;&nbsp;<B>MOGADISHU-SOMALIA</B>", 'LRT', "L", 0, 0, '', '', true, 0, true);
        
        $pdf->SetXY(7, $tableY);
        $pdf->MultiCell(196, $tableHeight, "مكان الميلاد", 0, "R", 0, 0, '', '', true, 0, true);
        $tableY = $tableY+$tableHeight;

        $pdf->SetXY(7, $tableY);
        $pdf->MultiCell(196, $tableHeight, "Jinsiyadda / Nationality: &nbsp;&nbsp;&nbsp;&nbsp;<B>SOMALI</B>", 'LRT', "L", 0, 0, '', '', true, 0, true);

        $pdf->SetXY(7, $tableY);
        $pdf->MultiCell(196, $tableHeight, "الجنسية", 0, "R", 0, 0, '', '', true, 0, true);
        $tableY = $tableY+$tableHeight;

        $pdf->SetXY(7, $tableY);
        $pdf->MultiCell(196, $tableHeight, "Calaamad Wajiga / Face marks: &nbsp;&nbsp;&nbsp;&nbsp;<B>NO</B>", 'LRT', "L", 0, 0, '', '', true, 0, true);

        $pdf->SetXY(7, $tableY);
        $pdf->MultiCell(196, $tableHeight, "علامة مميزة", 0, "R", 0, 0, '', '', true, 0, true);
        $tableY = $tableY+$tableHeight;

        $pdf->SetXY(7, $tableY);
        $pdf->MultiCell(196, $tableHeight+1, "Cinwaanka dalka uu deganyahay /<br>Address in the coutry of residence", 'LRT', "L", 0, 0, '', '', true, 0, true);
        
        $pdf->setCellPaddings( $left = 2, $top = 5, $right = 1, $bottom = 0);
        $pdf->SetXY(75, $tableY);
        $pdf->MultiCell(196, $tableHeight, "<B>MOGADISHU-SOMALIA</B>", 0, "L", 0, 0, '', '', true, 0, true);
        
        $pdf->SetXY(7, $tableY);
        $pdf->MultiCell(196, $tableHeight, "عنوان بلد الإقامة", 0, "R", 0, 0, '', '', true, 0, true);

        $tableY = $tableY+$tableHeight+3;

        $pdf->setCellPaddings( $left = 2, $top = 3.5, $right = 1, $bottom = 0);
        $pdf->SetXY(7, $tableY);
        $pdf->MultiCell(196, $tableHeight, "Tariikhda la bixiyay / Date of issue: &nbsp;&nbsp;&nbsp;&nbsp;<B>13/11/2018</B>", 'LRT', "L", 0, 0, '', '', true, 0, true);

        $pdf->SetXY(7, $tableY);
        $pdf->MultiCell(196, $tableHeight, "تاريخ الإصدار", 0, "R", 0, 0, '', '', true, 0, true);
        $tableY = $tableY+$tableHeight;


        $pdf->SetXY(7, $tableY);
        $pdf->MultiCell(196, $tableHeight, "Hay'adda bixisay / Issuing Authority: &nbsp;&nbsp;&nbsp;&nbsp;<B>13/11/2018</B>", 'LRT', "L", 0, 0, '', '', true, 0, true);

        $pdf->SetXY(7, $tableY);
        $pdf->MultiCell(196, $tableHeight, "جهة الإصدار", 0, "R", 0, 0, '', '', true, 0, true);
        $tableY = $tableY+$tableHeight;

        $pdf->SetXY(7, $tableY);
        $pdf->MultiCell(196, $tableHeight, "Mudada ay ansax tahay / Valid until: &nbsp;&nbsp;&nbsp;&nbsp;<B>3 MONTH</B>", 'LRTB', "L", 0, 0, '', '', true, 0, true);

        $pdf->SetXY(7, $tableY);
        $pdf->MultiCell(196, $tableHeight, "مدة الصلاحية", 0, "R", 0, 0, '', '', true, 0, true);

        
        

        $pdf->SetFont($arial, '',8, '', false);
        $pdf->SetXY(7, 220);
        $pdf->MultiCell(196, 0, "Warqaddaan waxaa lagu gali karaa oo kali ah xuduudaha dalka jamhuuriyadda federaalka Soomaaliya", 0, "L", 0, 0, '', '', true, 0, true);
        
        $pdf->SetXY(7, 226);
        $pdf->MultiCell(196, 0, "This TTD for use only to travel bak to the Federal Republic of Somalia", 0, "L", 0, 0, '', '', true, 0, true);

        $pdf->SetXY(80, 234);
        $pdf->MultiCell(0, 0, "یہ  صرف وفاقی جمہوریہ صومالیہ کے سفر کے لیے استعمال کے لیے ہے۔", 0, "L", 0, 0, '', '', true, 0, true);


        $pdf->SetXY(7, 255);
        $pdf->MultiCell(196, 0, "Signature: ___________________", 0, "L", 0, 0, '', '', true, 0, true);

        $pdf->SetLineStyle(array('width' => 1.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(137, 142, 145)));
        $pdf->SetXY(3, 276);
        $pdf->MultiCell(204, 0, "", "T", "L", 0, 0, '', '', true, 0, true);

        $pdf->SetLineStyle(array('width' => 0.4, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)));
        $pdf->SetXY(2.5, 277);
        $pdf->MultiCell(205, 0, "", "T", "L", 0, 0, '', '', true, 0, true);


        
        $pdf->SetFont($ariali, '',8, '', false);
        $pdf->SetXY(7, 277);
        $pdf->MultiCell(196, 0, "Immigration and Naturalization Directorate, Mogadishu, Somalia", 0, "C", 0, 0, '', '', true, 0, true);

        $pdf->SetXY(7, 280.5);
        $pdf->MultiCell(196, 0, "Phone: +252 61 446 5000, Email: <u>info@immigration.gov.so</u> www.immigration.gov.so", 0, "C", 0, 0, '', '', true, 0, true);


        $nameOrg = 'Rohit Bachkar';
        
        // Ghost image
        $ghost_font_size = '13';
        $ghostImagex = 142;
        $ghostImagey = 211;
        //$ghostImageWidth = 55; //68
        //$ghostImageHeight = 9.8;
        $ghostImageWidth = 39.405983333;
        $ghostImageHeight = 10;
        $name = substr(str_replace(' ', '', strtoupper($nameOrg)) , 0, 6);
        $tmpDir = $this->createTemp(public_path() . '/backend/images/ghosttemp/temp');

        $w = $this->CreateMessage($tmpDir, $name, $ghost_font_size, '');

        $pdf->Image("$tmpDir/" . $name . "" . $ghost_font_size . ".png", $ghostImagex, $ghostImagey, $w, $ghostImageHeight, "PNG", '', 'L', true, 3600);




        //qr code
        $GUID='2666';    
        $dt = date("_ymdHis");
        $str=$GUID.$dt;
        // $codeContents = $QR_Output."\n\n".strtoupper(md5($str));
        // $codeContents = "[".$student_id." - ". $candidate_name ."]"."\n\n" ."\n\n".strtoupper(md5($str));
        // $codeContents = "[".$student_id." - ". $candidate_name ."]";
        // $codeContents .="\n";
        // $codeContents .= "[CGPA - ".$cgpa." (". $cgpaRemark .")]";
        // $codeContents .="\n";
        // $codeContents .= "[Percentage - ".$Percentage."%]";
        // $codeContents .="\n";
        // $codeContents .= $Programme."(".$major.")";
        // $codeContents .="\n\n".strtoupper(md5($str));
        $encryptedString = strtoupper(md5($str));
        $qr_code_path = public_path().'\\'.$subdomain[0].'\backend\canvas\images\qr\/'.$encryptedString.'.png';
        $qrCodex = 180; 
        $qrCodey = 245;
        $qrCodeWidth =22;
        $qrCodeHeight = 22;
        $ecc = 'L';
        $pixel_Size = 1;
        $frame_Size = 1;  
        // \PHPQRCode\QRcode::png($codeContents, $qr_code_path, $ecc, $pixel_Size, $frame_Size);

        \QrCode::backgroundColor(255, 255, 0)            
            ->format('png')        
            ->size(500)    
            ->generate($encryptedString, $qr_code_path);
        
        $pdf->Image($qr_code_path, $qrCodex, $qrCodey, $qrCodeWidth,  $qrCodeHeight, 'png', '', true, false);   
        $pdf->setPageMark(); 

        // microtext
        // $nameOrg = 'Joe india ';
        // $nameOrg = 'Joe india 2 month test';
        // $nameOrg = 'ADAN MOHAMMED IKAR SOMALIA 5 MONTH ISS/HSI/001/2012';
        $nameOrg = 'ADAN MOHAMMED IKAR SOMALIA 5 MONTH ISS/HSI/001/2012 ISS/HSI/001/2012ISS/HSI/001/2012';
        $microtext_line1 = '';
        for($d = 0; $d < 100; $d++) {
            $microtext_line1 .= $nameOrg . ' ';
        }

        $microlinestr=str_replace(' ','',$microtext_line1);

        $pdf->SetFont($arialb, '', 1.2, '', false);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->StartTransform();
        $pdf->SetXY(180.6, 262);

        $microlinestr1 = substr($microlinestr,0,71);
        $pdf->MultiCell(0, 0, $microlinestr1, "", "L", 0, 0, '', '', true, 0, true);
        $pdf->StopTransform();
        // microtext

        $pdf->output('test.pdf','I');


    }


    public function createTemp($path)
    {
        //create ghost image folder
        $tmp = date("ymdHis");
        
        $tmpname = tempnam($path, $tmp);
        //unlink($tmpname);
        //mkdir($tmpname);
        if (file_exists($tmpname)) {
         unlink($tmpname);
        }
        mkdir($tmpname, 0777);
        return $tmpname;
    }


    public function CreateMessage($tmpDir, $name = "",$font_size,$print_color)
    {
        if($name == "")
            return;
        $name = strtoupper($name);
        // Create character image
        if($font_size == 15 || $font_size == "15"){


            $AlphaPosArray = array(
                "A" => array(0, 825),
                "B" => array(825, 840),
                "C" => array(1665, 824),
                "D" => array(2489, 856),
                "E" => array(3345, 872),
                "F" => array(4217, 760),
                "G" => array(4977, 848),
                "H" => array(5825, 896),
                "I" => array(6721, 728),
                "J" => array(7449, 864),
                "K" => array(8313, 840),
                "L" => array(9153, 817),
                "M" => array(9970, 920),
                "N" => array(10890, 728),
                "O" => array(11618, 944),
                "P" => array(12562, 736),
                "Q" => array(13298, 920),
                "R" => array(14218, 840),
                "S" => array(15058, 824),
                "T" => array(15882, 816),
                "U" => array(16698, 800),
                "V" => array(17498, 841),
                "W" => array(18339, 864),
                "X" => array(19203, 800),
                "Y" => array(20003, 824),
                "Z" => array(20827, 876)
            );

            $filename = public_path()."/backend/canvas/ghost_images/F15_H14_W504.png";

            $charsImage = imagecreatefrompng($filename);
            $size = getimagesize($filename);
            // Create Backgoround image
            $filename   = public_path()."/backend/canvas/ghost_images/alpha_GHOST.png";
            $bgImage = imagecreatefrompng($filename);
            $currentX = 0;
            $len = strlen($name);
            
            for($i = 0; $i < $len; $i++) {
                $value = $name[$i];
                if(!array_key_exists($value, $AlphaPosArray))
                    continue;
                $X = $AlphaPosArray[$value][0];
                $W = $AlphaPosArray[$value][1];
                
                imagecopymerge($bgImage, $charsImage, $currentX, 0, $X, 0, $W, $size[1], 100);
                $currentX += $W;
            }

            $rect = array("x" => 0, "y" => 0, "width" => $currentX, "height" => $size[1]);
            $im = imagecrop($bgImage, $rect);
            
            imagepng($im, "$tmpDir/" . $name."".$font_size.".png");
            imagedestroy($bgImage);
            imagedestroy($charsImage);
            return round((14 * $currentX)/ $size[1]);

        }else if($font_size == 12){

            $AlphaPosArray = array(
                "A" => array(0, 849),
                "B" => array(849, 864),
                "C" => array(1713, 840),
                "D" => array(2553, 792),
                "E" => array(3345, 872),
                "F" => array(4217, 776),
                "G" => array(4993, 832),
                "H" => array(5825, 880),
                "I" => array(6705, 744),
                "J" => array(7449, 804),
                "K" => array(8273, 928),
                "L" => array(9201, 776),
                "M" => array(9977, 920),
                "N" => array(10897, 744),
                "O" => array(11641, 864),
                "P" => array(12505, 808),
                "Q" => array(13313, 804),
                "R" => array(14117, 904),
                "S" => array(15021, 832),
                "T" => array(15853, 816),
                "U" => array(16669, 824),
                "V" => array(17493, 800),
                "W" => array(18293, 909),
                "X" => array(19202, 800),
                "Y" => array(20002, 840),
                "Z" => array(20842, 792)
            
            );
                
                $filename = public_path()."/backend/canvas/ghost_images/F12_H8_W288.png";
            $charsImage = imagecreatefrompng($filename);
            $size = getimagesize($filename);
            // Create Backgoround image
            $filename   = public_path()."/backend/canvas/ghost_images/alpha_GHOST.png";
            $bgImage = imagecreatefrompng($filename);
            $currentX = 0;
            $len = strlen($name);
            
            for($i = 0; $i < $len; $i++) {
                $value = $name[$i];
                if(!array_key_exists($value, $AlphaPosArray))
                    continue;
                $X = $AlphaPosArray[$value][0];
                $W = $AlphaPosArray[$value][1];
                
                imagecopymerge($bgImage, $charsImage, $currentX, 0, $X, 0, $W, $size[1], 100);
                $currentX += $W;
            }

            $rect = array("x" => 0, "y" => 0, "width" => $currentX, "height" => $size[1]);
            $im = imagecrop($bgImage, $rect);
            
            imagepng($im, "$tmpDir/" . $name."".$font_size.".png");
            imagedestroy($bgImage);
            imagedestroy($charsImage);
            return round((8 * $currentX)/ $size[1]);

        }else if($font_size == "10" || $font_size == 10){
            $AlphaPosArray = array(
                "A" => array(0, 700),
                "B" => array(700, 757),
                "C" => array(1457, 704),
                "D" => array(2161, 712),
                "E" => array(2873, 672),
                "F" => array(3545, 664),
                "G" => array(4209, 752),
                "H" => array(4961, 744),
                "I" => array(5705, 616),
                "J" => array(6321, 736),
                "K" => array(7057, 784),
                "L" => array(7841, 673),
                "M" => array(8514, 752),
                "N" => array(9266, 640),
                "O" => array(9906, 760),
                "P" => array(10666, 664),
                "Q" => array(11330, 736),
                "R" => array(12066, 712),
                "S" => array(12778, 664),
                "T" => array(13442, 723),
                "U" => array(14165, 696),
                "V" => array(14861, 696),
                "W" => array(15557, 745),
                "X" => array(16302, 680),
                "Y" => array(16982, 728),
                "Z" => array(17710, 680)
                
            );
            
            $filename = public_path()."/backend/canvas/ghost_images/F10_H5_W180.png";
            $charsImage = imagecreatefrompng($filename);
            $size = getimagesize($filename);
            // Create Backgoround image
            $filename   = public_path()."/backend/canvas/ghost_images/alpha_GHOST.png";
            $bgImage = imagecreatefrompng($filename);
            $currentX = 0;
            $len = strlen($name);
            
            for($i = 0; $i < $len; $i++) {
                $value = $name[$i];
                if(!array_key_exists($value, $AlphaPosArray))
                    continue;
                $X = $AlphaPosArray[$value][0];
                $W = $AlphaPosArray[$value][1];
                imagecopymerge($bgImage, $charsImage, $currentX, 0, $X, 0, $W, $size[1], 100);
                $currentX += $W;
            }
            
            $rect = array("x" => 0, "y" => 0, "width" => $currentX, "height" => $size[1]);
            $im = imagecrop($bgImage, $rect);
           
            imagepng($im, "$tmpDir/" . $name."".$font_size.".png");
            imagedestroy($bgImage);
            imagedestroy($charsImage);
            return round((5 * $currentX)/ $size[1]);

        }else if($font_size == 11){

            $AlphaPosArray = array(
                "A" => array(0, 833),
                "B" => array(833, 872),
                "C" => array(1705, 800),
                "D" => array(2505, 888),
                "E" => array(3393, 856),
                "F" => array(4249, 760),
                "G" => array(5009, 856),
                "H" => array(5865, 896),
                "I" => array(6761, 744),
                "J" => array(7505, 832),
                "K" => array(8337, 887),
                "L" => array(9224, 760),
                "M" => array(9984, 920),
                "N" => array(10904, 789),
                "O" => array(11693, 896),
                "P" => array(12589, 776),
                "Q" => array(13365, 904),
                "R" => array(14269, 784),
                "S" => array(15053, 872),
                "T" => array(15925, 776),
                "U" => array(16701, 832),
                "V" => array(17533, 824),
                "W" => array(18357, 872),
                "X" => array(19229, 806),
                "Y" => array(20035, 832),
                "Z" => array(20867, 848)
            
            );
                
                $filename = public_path()."/backend/canvas/ghost_images/F11_H7_W250.png";
            $charsImage = imagecreatefrompng($filename);
            $size = getimagesize($filename);
            // Create Backgoround image
            $filename   = public_path()."/backend/canvas/ghost_images/alpha_GHOST.png";
            $bgImage = imagecreatefrompng($filename);
            $currentX = 0;
            $len = strlen($name);
            
            for($i = 0; $i < $len; $i++) {
                $value = $name[$i];
                if(!array_key_exists($value, $AlphaPosArray))
                    continue;
                $X = $AlphaPosArray[$value][0];
                $W = $AlphaPosArray[$value][1];
                
                imagecopymerge($bgImage, $charsImage, $currentX, 0, $X, 0, $W, $size[1], 100);
                $currentX += $W;
            }

            $rect = array("x" => 0, "y" => 0, "width" => $currentX, "height" => $size[1]);
            $im = imagecrop($bgImage, $rect);
            

            imagepng($im, "$tmpDir/" . $name."".$font_size.".png");
            imagedestroy($bgImage);
            imagedestroy($charsImage);
            return round((7 * $currentX)/ $size[1]);

        }else if($font_size == "13" || $font_size == 13){

            $AlphaPosArray = array(
                "A" => array(0, 865),
                "B" => array(865, 792),
                "C" => array(1657, 856),
                "D" => array(2513, 888),
                "E" => array(3401, 768),
                "F" => array(4169, 864),
                "G" => array(5033, 824),
                "H" => array(5857, 896),
                "I" => array(6753, 784),
                "J" => array(7537, 808),
                "K" => array(8345, 877),
                "L" => array(9222, 664),
                "M" => array(9886, 976),
                "N" => array(10862, 832),
                "O" => array(11694, 856),
                "P" => array(12550, 776),
                "Q" => array(13326, 896),
                "R" => array(14222, 816),
                "S" => array(15038, 784),
                "T" => array(15822, 816),
                "U" => array(16638, 840),
                "V" => array(17478, 794),
                "W" => array(18272, 920),
                "X" => array(19192, 808),
                "Y" => array(20000, 880),
                "Z" => array(20880, 800)
            
            );

                
            $filename = public_path()."/backend/canvas/ghost_images/F13_H10_W360.png";
            $charsImage = imagecreatefrompng($filename);
            $size = getimagesize($filename);
            // Create Backgoround image
            $filename   = public_path()."/backend/canvas/ghost_images/alpha_GHOST.png";
            $bgImage = imagecreatefrompng($filename);
            $currentX = 0;
            $len = strlen($name);
            
            for($i = 0; $i < $len; $i++) {
                $value = $name[$i];
                if(!array_key_exists($value, $AlphaPosArray))
                    continue;
                $X = $AlphaPosArray[$value][0];
                $W = $AlphaPosArray[$value][1];
                
                imagecopymerge($bgImage, $charsImage, $currentX, 0, $X, 0, $W, $size[1], 100);
                $currentX += $W;
            }

            $rect = array("x" => 0, "y" => 0, "width" => $currentX, "height" => $size[1]);
            // dd($rect);
            $im = imagecrop($bgImage, $rect);
            
            imagepng($im, "$tmpDir/" . $name."".$font_size.".png");
            imagedestroy($bgImage);
            imagedestroy($charsImage);
            return round((10 * $currentX)/ $size[1]);

        }else if($font_size == "14" || $font_size == 14){

            $AlphaPosArray = array(
                "A" => array(0, 833),
                "B" => array(833, 872),
                "C" => array(1705, 856),
                "D" => array(2561, 832),
                "E" => array(3393, 832),
                "F" => array(4225, 736),
                "G" => array(4961, 892),
                "H" => array(5853, 940),
                "I" => array(6793, 736),
                "J" => array(7529, 792),
                "K" => array(8321, 848),
                "L" => array(9169, 746),
                "M" => array(9915, 1024),
                "N" => array(10939, 744),
                "O" => array(11683, 864),
                "P" => array(12547, 792),
                "Q" => array(13339, 848),
                "R" => array(14187, 872),
                "S" => array(15059, 808),
                "T" => array(15867, 824),
                "U" => array(16691, 872),
                "V" => array(17563, 736),
                "W" => array(18299, 897),
                "X" => array(19196, 808),
                "Y" => array(20004, 880),
                "Z" => array(80884, 808)
            
            );
                
                $filename = public_path()."/backend/canvas/ghost_images/F14_H12_W432.png";
            $charsImage = imagecreatefrompng($filename);
            $size = getimagesize($filename);
            // Create Backgoround image
            $filename   = public_path()."/backend/canvas/ghost_images/alpha_GHOST.png";
            $bgImage = imagecreatefrompng($filename);
            $currentX = 0;
            $len = strlen($name);
            
            for($i = 0; $i < $len; $i++) {
                $value = $name[$i];
                if(!array_key_exists($value, $AlphaPosArray))
                    continue;
                $X = $AlphaPosArray[$value][0];
                $W = $AlphaPosArray[$value][1];
                
                imagecopymerge($bgImage, $charsImage, $currentX, 0, $X, 0, $W, $size[1], 100);
                $currentX += $W;
            }

            $rect = array("x" => 0, "y" => 0, "width" => $currentX, "height" => $size[1]);
            $im = imagecrop($bgImage, $rect);
            
            imagepng($im, "$tmpDir/" . $name."".$font_size.".png");
            imagedestroy($bgImage);
            imagedestroy($charsImage);
            return round((12 * $currentX)/ $size[1]);

        }else{
            $AlphaPosArray = array(
                "A" => array(0, 944),
                "B" => array(943, 944),
                "C" => array(1980, 944),
                "D" => array(2923, 944),
                "E" => array(3897, 944),
                "F" => array(4840, 753),
                "G" => array(5657, 943),
                "H" => array(6694, 881),
                "I" => array(7668, 504),
                "J" => array(8265, 692),
                "K" => array(9020, 881),
                "L" => array(9899, 944),
                "M" => array(10842, 944),
                "N" => array(11974, 724),
                "O" => array(12916, 850),
                "P" => array(13859, 850),
                "Q" => array(14802, 880),
                "R" => array(15776, 944),
                "S" => array(16719, 880),
                "T" => array(17599, 880),
                "U" => array(18479, 880),
                "V" => array(19485, 880),
                "W" => array(20396, 1038),
                "X" => array(21465, 944),
                "Y" => array(22407, 880),
                "Z" => array(23287, 880)
            );  

            $filename = public_path()."/backend/canvas/ghost_images/ALPHA_GHOST.png";
            $charsImage = imagecreatefrompng($filename);
            $size = getimagesize($filename);

            // Create Backgoround image
            $filename   = public_path()."/backend/canvas/ghost_images/alpha_GHOST.png";
            $bgImage = imagecreatefrompng($filename);
            $currentX = 0;
            $len = strlen($name);
            
            for($i = 0; $i < $len; $i++) {
                $value = $name[$i];
                if(!array_key_exists($value, $AlphaPosArray))
                    continue;
                $X = $AlphaPosArray[$value][0];
                $W = $AlphaPosArray[$value][1];
                imagecopymerge($bgImage, $charsImage, $currentX, 0, $X, 0, $W, $size[1], 100);
                $currentX += $W;
            }

            $rect = array("x" => 0, "y" => 0, "width" => $currentX, "height" => $size[1]);
            $im = imagecrop($bgImage, $rect);
            
            imagepng($im, "$tmpDir/" . $name."".$font_size.".png");
            imagedestroy($bgImage);
            imagedestroy($charsImage);
            return round((10 * $currentX)/ $size[1]);
        }
    }



    public function watermarkPdf () {

        $domain = \Request::getHost();
        $subdomain = explode('.', $domain);

        $pdf = new TCPDF('P', 'mm', array('210', '297'), true, 'UTF-8', false);
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('SSSL');
        $pdf->SetTitle('Temporary Travel Document');
        $pdf->SetSubject('');

        // remove default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetAutoPageBreak(false, 0);
        $pdf->AddPage();
        //$pdf->setLink('', 0);
        //$pdf->setLink(0); 
        // add spot colors
        $pdf->AddSpotColor('Spot Red', 30, 100, 90, 10);        // For Invisible
        $pdf->AddSpotColor('Spot Dark Green', 100, 50, 80, 45); // clear text on bottom red and in clear text logo
        $pdf->AddSpotColor('Spot Light Yellow', 0, 0, 100, 0);
        //set fonts
        $arial = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\Arial.TTF', 'TrueTypeUnicode', '', 96);
        $arialb = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\Arialb.TTF', 'TrueTypeUnicode', '', 96);
        $ariali = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\ARIALI.TTF', 'TrueTypeUnicode', '', 96);
        

        $arialNarrow = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\arialn.TTF', 'TrueTypeUnicode', '', 96);
        $arialNarrowB = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\ARIALNB.TTF', 'TrueTypeUnicode', '', 96);
        $times = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\Times-New-Roman.ttf', 'TrueTypeUnicode', '', 96);
        $timesb = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\Times-New-Roman-Bold.ttf', 'TrueTypeUnicode', '', 96);
        $trebuc = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\trebuc.ttf', 'TrueTypeUnicode', '', 96);
        $trebucb = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\TREBUCBD.ttf', 'TrueTypeUnicode', '', 96);
        $trebuci = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\Trebuchet-MS-Italic.ttf', 'TrueTypeUnicode', '', 96);
        $graduateR = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\graduate-regular.ttf', 'TrueTypeUnicode', '', 96);
        $poppinsR = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\Poppins Regular 400.ttf', 'TrueTypeUnicode', '', 96);
        $poppinsM = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\Poppins Medium 500.ttf', 'TrueTypeUnicode', '', 96);

        $urdu = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\ARIALUNI.ttf', 'TrueTypeUnicode', '', 32);



        $pdf->SetFont($arial, '',11, '', false);
        $str = "ADAN MOHAMED IKAR  SOMALI 3 MONTHS ESR/HSJ/001/2018";
        $security_line = '';
        for($d = 0; $d < 30; $d++) {
            $security_line .= $str . ' ';
        }
       
        $pdfWidth = 210;
        $pdfHeight = 297;
        $j_increased=5;
        $line_gap = 30;
        $angle = 45;
                                
        $pdf->SetOverprint(true, true, 0);
            
        $pdf->SetTextColor(0, 0, 0, '', false, '');

        $pdf->SetAlpha(0.2);
        for ($i=0; $i <= $pdfHeight; $i+=$line_gap) {
            $pdf->SetXY(0,$i);
            $pdf->StartTransform();  
            $pdf->Rotate($angle);
            $pdf->Cell(0, 0, $security_line, 0, false, 'C');
            $pdf->StopTransform();
        }
       
        for ($j=0; $j < $pdfWidth; $j+=$line_gap) {
            //$pdf->SetXY($j+5,$pdfHeight);
            $pdf->SetXY($j+$j_increased,$pdfHeight);
            $pdf->StartTransform();  
            $pdf->Rotate($angle);
            $pdf->Cell(0, 0, $security_line, 0, false, 'C');
            $pdf->StopTransform();
        }
       
        $pdf->SetOverprint(false, false, 0);
        $pdf->SetAlpha(1);
        $pdf->SetAlpha(1);
        
        $pdf->output('watermark.pdf','I');
    }

}
