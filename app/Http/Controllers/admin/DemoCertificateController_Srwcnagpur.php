<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\models\TemplateMaster;
use App\models\SuperAdmin;
use Session,TCPDF,TCPDF_FONTS,Auth,DB;
use App\Http\Requests\ExcelValidationRequest;
use App\Http\Requests\MappingDatabaseRequest;
use App\Http\Requests\TemplateMapRequest;
use App\Http\Requests\TemplateMasterRequest;
use App\Imports\TemplateMapImport
;use App\Imports\TemplateMasterImport;
use App\Jobs\PDFGenerateJob;
use App\models\BackgroundTemplateMaster;
use App\Events\BarcodeImageEvent;
use App\Events\TemplateEvent;
use App\models\FontMaster;
use App\models\FieldMaster;
use App\models\User;
use App\models\StudentTable;
use App\models\SbStudentTable;
use Maatwebsite\Excel\Facades\Excel;
use App\models\SystemConfig;
use App\Jobs\PreviewPDFGenerateJob;
use App\Exports\TemplateMasterExport;
use Storage;
use App\Library\Services\CheckUploadedFileOnAwsORLocalService;
use App\models\Config;
use App\models\PrintingDetail;
use App\models\ExcelUploadHistory;
use App\models\SbExceUploadHistory;

use App\Helpers\CoreHelper;
use Helper;
use App\Jobs\ValidateExcelDemoSOMJob;
use App\Jobs\ValidateExcelDemoJob;
use App\Jobs\PdfGenerateDemoSOMJob;

class DemoCertificateController extends Controller
{
    public function index(Request $request)
    {
       return view('admin.demo_certificate.index');
    }

    public function validateExcel(Request $request,CheckUploadedFileOnAwsORLocalService $checkUploadedFileOnAwsOrLocal){
        $template_id=100;
        $get_file_aws_local_flag = $checkUploadedFileOnAwsOrLocal->checkUploadedFileOnAwsORLocal();

        $domain = \Request::getHost();
        $subdomain = explode('.', $domain);
        //check file is uploaded or not
        if($request->hasFile('field_file')){
            //check extension
            $file_name = $request['field_file']->getClientOriginalName();
            $ext = pathinfo($file_name, PATHINFO_EXTENSION);

            //excel file name
            $excelfile =  date("YmdHis") . "_" . $file_name;
            $target_path = public_path().'/backend/canvas/dummy_images/'.$template_id;
            $fullpath = $target_path.'/'.$excelfile;
            if(!is_dir($target_path)){
                mkdir($target_path, 0777);
            }

            if($request['field_file']->move($target_path,$excelfile)){
                //get excel file data
                // dd('hi');
                if($ext == 'xlsx' || $ext == 'Xlsx'){
                    $inputFileType = 'Xlsx';
                }
                else{
                    $inputFileType = 'Xls';
                }

                if($ext == 'csv' || $ext == 'CSV'){
                    $inputFileType = 'Csv';
                }
                else{
                    $inputFileType = 'Csv';
                }

                $auth_site_id=Auth::guard('admin')->user()->site_id;

                $systemConfig = SystemConfig::select('sandboxing')->where('site_id',$auth_site_id)->first();
                if($get_file_aws_local_flag->file_aws_local == '1'){
                    CoreHelper::sandboxingFileAWS($systemConfig,$fullpath,$template_id,$excelfile);
                }
                else{
                    CoreHelper::sandboxingFile($systemConfig,$fullpath,$template_id,$excelfile);
                }
                $objReader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
                /**  Load $inputFileName to a Spreadsheet Object  **/

                $objPHPExcel1 = $objReader->load($fullpath);
                $sheet1 = $objPHPExcel1->getSheet(0);


                $highestColumn1 = $sheet1->getHighestColumn();
                $highestRow1 = $sheet1->getHighestDataRow();
                
                $records = array();
                // Certificate top name
                $certificateTopName = $sheet1->rangeToArray('A4', NULL, TRUE, FALSE);
                $certificateTopName = $certificateTopName[0][0];
                $records[] = $certificateTopName;
                
                // Certificate address
                $certificateAddress = $sheet1->rangeToArray('A5', NULL, TRUE, FALSE);
                $certificateAddress = $certificateAddress[0][0];
                $records[] = $certificateAddress;

                // Certificate school
                $certificateSchool = $sheet1->rangeToArray('A7', NULL, TRUE, FALSE);
                $certificateSchool = $certificateSchool[0][0];
                $records[] = $certificateSchool;

                // Certificate statement of results
                $certificateStm = $sheet1->rangeToArray('A9', NULL, TRUE, FALSE);
                $certificateStm = $certificateStm[0][0];
                $records[] = $certificateStm;

                // Certificate name
                $certificateName = $sheet1->rangeToArray('B11', NULL, TRUE, FALSE);
                $certificateName = $certificateName[0][0];
                $records[] = $certificateName;

                // Certificate gender
                $certificateGender = $sheet1->rangeToArray('B12', NULL, TRUE, FALSE);
                $certificateGender = $certificateGender[0][0];
                $records[] = $certificateGender;

                // Certificate DOB
                $certificateDOB = $sheet1->rangeToArray('B13', NULL, TRUE, FALSE);
                $certificateDOB = $certificateDOB[0][0];
                $records[] = $certificateDOB;

                // Certificate Nationality
                $certificateNationality = $sheet1->rangeToArray('B14', NULL, TRUE, FALSE);
                $certificateNationality = $certificateNationality[0][0];
                $records[] = $certificateNationality;

                // Certificate Reg No
                $certificateRegNo = $sheet1->rangeToArray('G11', NULL, TRUE, FALSE);
                $certificateRegNo = $certificateRegNo[0][0];
                $records[] = $certificateRegNo;

                // Certificate Programme
                $certificateProgramme = $sheet1->rangeToArray('G12', NULL, TRUE, FALSE);
                $certificateProgramme = $certificateProgramme[0][0];
                $records[] = $certificateProgramme;

                // Cert School
                $certSchool = $sheet1->rangeToArray('G13', NULL, TRUE, FALSE);
                $certSchool = $certSchool[0][0];
                $records[] = $certSchool;

                // Certificate year of entry
                $certificateYearEntry = $sheet1->rangeToArray('G14', NULL, TRUE, FALSE);
                $certificateYearEntry = $certificateYearEntry[0][0];
                $records[] = $certificateYearEntry;

                // Certificate award
                $certificateAward = $sheet1->rangeToArray('A17', NULL, TRUE, FALSE);
                $certificateAward = $certificateAward[0][0];
                $records[] = $certificateAward;

                // Certificate award1
                $certificateAward1 = $sheet1->rangeToArray('A16', NULL, TRUE, FALSE);
                $certificateAward1 = $certificateAward1[0][0];
                $records[] = $certificateAward1;

                // Foundation year data //
                // Certificate Foundation courseCode1
                $foundationCourseCode1 = $sheet1->rangeToArray('A23', NULL, TRUE, FALSE);
                $foundationCourseCode1 = $foundationCourseCode1[0][0];
                $records[] = $foundationCourseCode1;

                // Certificate Foundation courseCode2
                $foundationCourseCode2 = $sheet1->rangeToArray('A24', NULL, TRUE, FALSE);
                $foundationCourseCode2 = $foundationCourseCode2[0][0];
                $records[] = $foundationCourseCode2;

                // Certificate Foundation courseCode3
                $foundationCourseCode3 = $sheet1->rangeToArray('A25', NULL, TRUE, FALSE);
                $foundationCourseCode3 = $foundationCourseCode3[0][0];
                $records[] = $foundationCourseCode3;


                // Certificate Foundation courseName1
                $foundationCourseName1 = $sheet1->rangeToArray('B23', NULL, TRUE, FALSE);
                $foundationCourseName1 = $foundationCourseName1[0][0];
                $records[] = $foundationCourseName1;

                // Certificate Foundation courseName2
                $foundationCourseName2 = $sheet1->rangeToArray('B24', NULL, TRUE, FALSE);
                $foundationCourseName2 = $foundationCourseName2[0][0];
                $records[] = $foundationCourseName2;

                // Certificate Foundation courseName3
                $foundationCourseName3 = $sheet1->rangeToArray('B25', NULL, TRUE, FALSE);
                $foundationCourseName3 = $foundationCourseName3[0][0];
                $records[] = $foundationCourseName3;

                // Certificate Foundation Grade1
                $foundationGrade1 = $sheet1->rangeToArray('H23', NULL, TRUE, FALSE);
                $foundationGrade1 = $foundationGrade1[0][0];
                $records[] = $foundationGrade1;

                // Certificate Foundation Grade2
                $foundationGrade2 = $sheet1->rangeToArray('H24', NULL, TRUE, FALSE);
                $foundationGrade2 = $foundationGrade2[0][0];
                $records[] = $foundationGrade2;

                // Certificate Foundation Grade3
                $foundationGrade3 = $sheet1->rangeToArray('H25', NULL, TRUE, FALSE);
                $foundationGrade3 = $foundationGrade3[0][0];
                $records[] = $foundationGrade3;

                // Certificate Foundation Remark1
                $foundationRemark1 = $sheet1->rangeToArray('I23', NULL, TRUE, FALSE);
                $foundationRemark1 = $foundationRemark1[0][0];
                $records[] = $foundationRemark1;

                // Certificate Foundation Remark2
                $foundationRemark2 = $sheet1->rangeToArray('I24', NULL, TRUE, FALSE);
                $foundationRemark2 = $foundationRemark2[0][0];
                $records[] = $foundationRemark2;

                // Certificate Foundation Remark3
                $foundationRemark3 = $sheet1->rangeToArray('I25', NULL, TRUE, FALSE);
                $foundationRemark3 = $foundationRemark3[0][0];
                $records[] = $foundationRemark3;
                // Foundation year data //


                // Year1 data //
                // Certificate Year 1 courseCode1
                $year1CourseCode1 = $sheet1->rangeToArray('A33', NULL, TRUE, FALSE);
                $year1CourseCode1 = $year1CourseCode1[0][0];
                $records[] = $year1CourseCode1;

                // Certificate Year 1 courseCode2
                $year1CourseCode2 = $sheet1->rangeToArray('A34', NULL, TRUE, FALSE);
                $year1CourseCode2 = $year1CourseCode2[0][0];
                $records[] = $year1CourseCode2;

                // Certificate Year 1 courseCode3
                $year1CourseCode3 = $sheet1->rangeToArray('A35', NULL, TRUE, FALSE);
                $year1CourseCode3 = $year1CourseCode3[0][0];
                $records[] = $year1CourseCode3;

                // Certificate Year 1 courseCode4
                $year1CourseCode4 = $sheet1->rangeToArray('A36', NULL, TRUE, FALSE);
                $year1CourseCode4 = $year1CourseCode4[0][0];
                $records[] = $year1CourseCode4;

                // Certificate Year 1 courseCode5
                $year1CourseCode5 = $sheet1->rangeToArray('A37', NULL, TRUE, FALSE);
                $year1CourseCode5 = $year1CourseCode5[0][0];
                $records[] = $year1CourseCode5;



                // Certificate Year 1 courseName1
                $year1CourseName1 = $sheet1->rangeToArray('B33', NULL, TRUE, FALSE);
                $year1CourseName1 = $year1CourseName1[0][0];
                $records[] = $year1CourseName1;

                // Certificate Year 1 courseName2
                $year1CourseName2 = $sheet1->rangeToArray('B34', NULL, TRUE, FALSE);
                $year1CourseName2 = $year1CourseName2[0][0];
                $records[] = $year1CourseName2;

                // Certificate Year 1 courseName3
                $year1CourseName3 = $sheet1->rangeToArray('B35', NULL, TRUE, FALSE);
                $year1CourseName3 = $year1CourseName3[0][0];
                $records[] = $year1CourseName3;

                // Certificate Year 1 courseName4
                $year1CourseName4 = $sheet1->rangeToArray('B36', NULL, TRUE, FALSE);
                $year1CourseName4 = $year1CourseName4[0][0];
                $records[] = $year1CourseName4;

                // Certificate Year 1 courseName5
                $year1CourseName5 = $sheet1->rangeToArray('B37', NULL, TRUE, FALSE);
                $year1CourseName5 = $year1CourseName5[0][0];
                $records[] = $year1CourseName5;


                // Certificate Year 1 Grade1
                $year1Grade1 = $sheet1->rangeToArray('H33', NULL, TRUE, FALSE);
                $year1Grade1 = $year1Grade1[0][0];
                $records[] = $year1Grade1;

                // Certificate Year 1 Grade2
                $year1Grade2 = $sheet1->rangeToArray('H34', NULL, TRUE, FALSE);
                $year1Grade2 = $year1Grade2[0][0];
                $records[] = $year1Grade2;

                // Certificate Year 1 Grade3
                $year1Grade3 = $sheet1->rangeToArray('H35', NULL, TRUE, FALSE);
                $year1Grade3 = $year1Grade3[0][0];
                $records[] = $year1Grade3;

                // Certificate Year 1 Grade4
                $year1Grade4 = $sheet1->rangeToArray('H36', NULL, TRUE, FALSE);
                $year1Grade4 = $year1Grade4[0][0];
                $records[] = $year1Grade4;

                // Certificate Year 1 Grade5
                $year1Grade5 = $sheet1->rangeToArray('H37', NULL, TRUE, FALSE);
                $year1Grade5 = $year1Grade5[0][0];
                $records[] = $year1Grade5;


                // Certificate Year 1 Remark1
                $year1Remark1 = $sheet1->rangeToArray('I33', NULL, TRUE, FALSE);
                $year1Remark1 = $year1Remark1[0][0];
                $records[] = $year1Remark1;

                // Certificate Year 1 Remark2
                $year1Remark2 = $sheet1->rangeToArray('I34', NULL, TRUE, FALSE);
                $year1Remark2 = $year1Remark2[0][0];
                $records[] = $year1Remark2;

                // Certificate Year 1 Remark3
                $year1Remark3 = $sheet1->rangeToArray('I35', NULL, TRUE, FALSE);
                $year1Remark3 = $year1Remark3[0][0];
                $records[] = $year1Remark3;

                // Certificate Year 1 Remark4
                $year1Remark4 = $sheet1->rangeToArray('I36', NULL, TRUE, FALSE);
                $year1Remark4 = $year1Remark4[0][0];
                $records[] = $year1Remark4;

                // Certificate Year 1 Remark5
                $year1Remark5 = $sheet1->rangeToArray('I37', NULL, TRUE, FALSE);
                $year1Remark5 = $year1Remark5[0][0];
                $records[] = $year1Remark5;
                // Year 1 data //



                // Year2 data //
                // Certificate Year 2 courseCode1
                $year2CourseCode1 = $sheet1->rangeToArray('A43', NULL, TRUE, FALSE);
                $year2CourseCode1 = $year2CourseCode1[0][0];
                $records[] = $year2CourseCode1;

                // Certificate Year 2 courseCode2
                $year2CourseCode2 = $sheet1->rangeToArray('A44', NULL, TRUE, FALSE);
                $year2CourseCode2 = $year2CourseCode2[0][0];
                $records[] = $year2CourseCode2;

                // Certificate Year 2 courseCode3
                $year2CourseCode3 = $sheet1->rangeToArray('A45', NULL, TRUE, FALSE);
                $year2CourseCode3 = $year2CourseCode3[0][0];
                $records[] = $year2CourseCode3;

                // Certificate Year 2 courseCode4
                $year2CourseCode4 = $sheet1->rangeToArray('A46', NULL, TRUE, FALSE);
                $year2CourseCode4 = $year2CourseCode4[0][0];
                $records[] = $year2CourseCode4;

                // Certificate Year 2 courseCode5
                $year2CourseCode5 = $sheet1->rangeToArray('A47', NULL, TRUE, FALSE);
                $year2CourseCode5 = $year2CourseCode5[0][0];
                $records[] = $year1CourseCode5;

                // Certificate Year 2 courseCode6
                $year2CourseCode6 = $sheet1->rangeToArray('A48', NULL, TRUE, FALSE);
                $year2CourseCode6 = $year2CourseCode6[0][0];
                $records[] = $year1CourseCode6;


                // Certificate Year 2 courseName1
                $year2CourseName1 = $sheet1->rangeToArray('B43', NULL, TRUE, FALSE);
                $year2CourseName1 = $year2CourseName1[0][0];
                $records[] = $year2CourseName1;

                // Certificate Year 2 courseName2
                $year2CourseName2 = $sheet1->rangeToArray('B44', NULL, TRUE, FALSE);
                $year2CourseName2 = $year2CourseName2[0][0];
                $records[] = $year2CourseName2;

                // Certificate Year 2 courseName3
                $year2CourseName3 = $sheet1->rangeToArray('B45', NULL, TRUE, FALSE);
                $year2CourseName3 = $year2CourseName3[0][0];
                $records[] = $year2CourseName3;

                // Certificate Year 2 courseName4
                $year2CourseName4 = $sheet1->rangeToArray('B46', NULL, TRUE, FALSE);
                $year2CourseName4 = $year2CourseName4[0][0];
                $records[] = $year2CourseName4;

                // Certificate Year 2 courseName5
                $year2CourseName5 = $sheet1->rangeToArray('B47', NULL, TRUE, FALSE);
                $year2CourseName5 = $year2CourseName5[0][0];
                $records[] = $year2CourseName5;

                // Certificate Year 2 courseName6
                $year2CourseName6 = $sheet1->rangeToArray('B48', NULL, TRUE, FALSE);
                $year2CourseName6 = $year2CourseName6[0][0];
                $records[] = $year2CourseName6;


                // Certificate Year 2 Grade1
                $year2Grade1 = $sheet1->rangeToArray('H43', NULL, TRUE, FALSE);
                $year2Grade1 = $year2Grade1[0][0];
                $records[] = $year2Grade1;

                // Certificate Year 2 Grade2
                $year2Grade2 = $sheet1->rangeToArray('H44', NULL, TRUE, FALSE);
                $year2Grade2 = $year2Grade2[0][0];
                $records[] = $year2Grade2;

                // Certificate Year 2 Grade3
                $year2Grade3 = $sheet1->rangeToArray('H45', NULL, TRUE, FALSE);
                $year2Grade3 = $year2Grade3[0][0];
                $records[] = $year2Grade3;

                // Certificate Year 2 Grade4
                $year2Grade4 = $sheet1->rangeToArray('H46', NULL, TRUE, FALSE);
                $year2Grade4 = $year2Grade4[0][0];
                $records[] = $year2Grade4;

                // Certificate Year 2 Grade5
                $year2Grade5 = $sheet1->rangeToArray('H47', NULL, TRUE, FALSE);
                $year2Grade5 = $year2Grade5[0][0];
                $records[] = $year2Grade5;

                // Certificate Year 2 Grade6
                $year2Grade6 = $sheet1->rangeToArray('H48', NULL, TRUE, FALSE);
                $year2Grade6 = $year2Grade6[0][0];
                $records[] = $year2Grade6;


                // Certificate Year 2 Remark1
                $year2Remark1 = $sheet1->rangeToArray('I43', NULL, TRUE, FALSE);
                $year2Remark1 = $year2Remark1[0][0];
                $records[] = $year2Remark1;

                // Certificate Year 2 Remark2
                $year2Remark2 = $sheet1->rangeToArray('I44', NULL, TRUE, FALSE);
                $year2Remark2 = $year2Remark2[0][0];
                $records[] = $year2Remark2;

                // Certificate Year 2 Remark3
                $year2Remark3 = $sheet1->rangeToArray('I45', NULL, TRUE, FALSE);
                $year2Remark3 = $year2Remark3[0][0];
                $records[] = $year2Remark3;

                // Certificate Year 2 Remark4
                $year2Remark4 = $sheet1->rangeToArray('I46', NULL, TRUE, FALSE);
                $year2Remark4 = $year2Remark4[0][0];
                $records[] = $year2Remark4;

                // Certificate Year 2 Remark5
                $year2Remark5 = $sheet1->rangeToArray('I47', NULL, TRUE, FALSE);
                $year2Remark5 = $year2Remark5[0][0];
                $records[] = $year2Remark5;

                // Certificate Year 2 Remark6
                $year2Remark6 = $sheet1->rangeToArray('I48', NULL, TRUE, FALSE);
                $year2Remark6 = $year2Remark6[0][0];
                $records[] = $year2Remark6;

                // Year2 data //



                // Year3 data //
                // Certificate Year 3 courseCode1
                $year3CourseCode1 = $sheet1->rangeToArray('A53', NULL, TRUE, FALSE);
                $year3CourseCode1 = $year3CourseCode1[0][0];
                $records[] = $year3CourseCode1;

                // Certificate Year 3 courseCode2
                $year3CourseCode2 = $sheet1->rangeToArray('A54', NULL, TRUE, FALSE);
                $year3CourseCode2 = $year3CourseCode2[0][0];
                $records[] = $year3CourseCode2;

                // Certificate Year 3 courseCode3
                $year3CourseCode3 = $sheet1->rangeToArray('A55', NULL, TRUE, FALSE);
                $year3CourseCode3 = $year3CourseCode3[0][0];
                $records[] = $year3CourseCode3;

                // Certificate Year 3 courseCode4
                $year3CourseCode4 = $sheet1->rangeToArray('A56', NULL, TRUE, FALSE);
                $year3CourseCode4 = $year3CourseCode4[0][0];
                $records[] = $year3CourseCode4;

                // Certificate Year 3 courseCode5
                $year3CourseCode5 = $sheet1->rangeToArray('A57', NULL, TRUE, FALSE);
                $year3CourseCode5 = $year3CourseCode5[0][0];
                $records[] = $year1CourseCode5;


                // Certificate Year 3 courseName1
                $year3CourseName1 = $sheet1->rangeToArray('B53', NULL, TRUE, FALSE);
                $year3CourseName1 = $year3CourseName1[0][0];
                $records[] = $year3CourseName1;

                // Certificate Year 3 courseName2
                $year3CourseName2 = $sheet1->rangeToArray('B54', NULL, TRUE, FALSE);
                $year3CourseName2 = $year3CourseName2[0][0];
                $records[] = $year3CourseName2;

                // Certificate Year 3 courseName3
                $year3CourseName3 = $sheet1->rangeToArray('B55', NULL, TRUE, FALSE);
                $year3CourseName3 = $year3CourseName3[0][0];
                $records[] = $year3CourseName3;

                // Certificate Year 3 courseName4
                $year3CourseName4 = $sheet1->rangeToArray('B56', NULL, TRUE, FALSE);
                $year3CourseName4 = $year3CourseName4[0][0];
                $records[] = $year3CourseName4;

                // Certificate Year 3 courseName5
                $year3CourseName5 = $sheet1->rangeToArray('B57', NULL, TRUE, FALSE);
                $year3CourseName5 = $year3CourseName5[0][0];
                $records[] = $year3CourseName5;


                // Certificate Year 3 Grade1
                $year3Grade1 = $sheet1->rangeToArray('H53', NULL, TRUE, FALSE);
                $year3Grade1 = $year3Grade1[0][0];
                $records[] = $year3Grade1;

                // Certificate Year 3 Grade2
                $year3Grade2 = $sheet1->rangeToArray('H54', NULL, TRUE, FALSE);
                $year3Grade2 = $year3Grade2[0][0];
                $records[] = $year3Grade2;

                // Certificate Year 3 Grade3
                $year3Grade3 = $sheet1->rangeToArray('H55', NULL, TRUE, FALSE);
                $year3Grade3 = $year3Grade3[0][0];
                $records[] = $year3Grade3;

                // Certificate Year 3 Grade4
                $year3Grade4 = $sheet1->rangeToArray('H56', NULL, TRUE, FALSE);
                $year3Grade4 = $year3Grade4[0][0];
                $records[] = $year3Grade4;

                // Certificate Year 3 Grade5
                $year3Grade5 = $sheet1->rangeToArray('H57', NULL, TRUE, FALSE);
                $year3Grade5 = $year3Grade5[0][0];
                $records[] = $year3Grade5;


                // Certificate Year 3 Remark1
                $year3Remark1 = $sheet1->rangeToArray('I53', NULL, TRUE, FALSE);
                $year3Remark1 = $year3Remark1[0][0];
                $records[] = $year3Remark1;

                // Certificate Year 3 Remark2
                $year3Remark2 = $sheet1->rangeToArray('I54', NULL, TRUE, FALSE);
                $year3Remark2 = $year3Remark2[0][0];
                $records[] = $year3Remark2;

                // Certificate Year 3 Remark3
                $year3Remark3 = $sheet1->rangeToArray('I55', NULL, TRUE, FALSE);
                $year3Remark3 = $year3Remark3[0][0];
                $records[] = $year3Remark3;

                // Certificate Year 3 Remark4
                $year3Remark4 = $sheet1->rangeToArray('I56', NULL, TRUE, FALSE);
                $year3Remark4 = $year3Remark4[0][0];
                $records[] = $year3Remark4;

                // Certificate Year 3 Remark5
                $year3Remark5 = $sheet1->rangeToArray('I57', NULL, TRUE, FALSE);
                $year3Remark5 = $year3Remark5[0][0];
                $records[] = $year3Remark5;

                // Year3 data //



                // Year4 data //
                // Certificate Year 4 courseCode1
                $year4CourseCode1 = $sheet1->rangeToArray('A67', NULL, TRUE, FALSE);
                $year4CourseCode1 = $year4CourseCode1[0][0];
                $records[] = $year4CourseCode1;

                // Certificate Year 4 courseCode2
                $year4CourseCode2 = $sheet1->rangeToArray('A68', NULL, TRUE, FALSE);
                $year4CourseCode2 = $year4CourseCode2[0][0];
                $records[] = $year4CourseCode2;

                // Certificate Year 4 courseCode3
                $year4CourseCode3 = $sheet1->rangeToArray('A69', NULL, TRUE, FALSE);
                $year4CourseCode3 = $year4CourseCode3[0][0];
                $records[] = $year4CourseCode3;

                // Certificate Year 4 courseCode4
                $year4CourseCode4 = $sheet1->rangeToArray('A70', NULL, TRUE, FALSE);
                $year4CourseCode4 = $year4CourseCode4[0][0];
                $records[] = $year4CourseCode4;


                // Certificate Year 4 courseName1
                $year4CourseName1 = $sheet1->rangeToArray('B67', NULL, TRUE, FALSE);
                $year4CourseName1 = $year4CourseName1[0][0];
                $records[] = $year4CourseName1;

                // Certificate Year 4 courseName2
                $year4CourseName2 = $sheet1->rangeToArray('B68', NULL, TRUE, FALSE);
                $year4CourseName2 = $year4CourseName2[0][0];
                $records[] = $year4CourseName2;

                // Certificate Year 4 courseName3
                $year4CourseName3 = $sheet1->rangeToArray('B69', NULL, TRUE, FALSE);
                $year4CourseName3 = $year4CourseName3[0][0];
                $records[] = $year4CourseName3;

                // Certificate Year 4 courseName4
                $year4CourseName4 = $sheet1->rangeToArray('B70', NULL, TRUE, FALSE);
                $year4CourseName4 = $year4CourseName4[0][0];
                $records[] = $year4CourseName4;


                // Certificate Year 4 Grade1
                $year4Grade1 = $sheet1->rangeToArray('H67', NULL, TRUE, FALSE);
                $year4Grade1 = $year4Grade1[0][0];
                $records[] = $year4Grade1;

                // Certificate Year 4 Grade2
                $year4Grade2 = $sheet1->rangeToArray('H68', NULL, TRUE, FALSE);
                $year4Grade2 = $year4Grade2[0][0];
                $records[] = $year4Grade2;

                // Certificate Year 4 Grade3
                $year4Grade3 = $sheet1->rangeToArray('H69', NULL, TRUE, FALSE);
                $year4Grade3 = $year4Grade3[0][0];
                $records[] = $year4Grade3;

                // Certificate Year 4 Grade4
                $year4Grade4 = $sheet1->rangeToArray('H70', NULL, TRUE, FALSE);
                $year4Grade4 = $year4Grade4[0][0];
                $records[] = $year4Grade4;


                // Certificate Year 4 Remark1
                $year4Remark1 = $sheet1->rangeToArray('I67', NULL, TRUE, FALSE);
                $year4Remark1 = $year4Remark1[0][0];
                $records[] = $year4Remark1;

                // Certificate Year 4 Remark2
                $year4Remark2 = $sheet1->rangeToArray('I68', NULL, TRUE, FALSE);
                $year4Remark2 = $year4Remark2[0][0];
                $records[] = $year4Remark2;

                // Certificate Year 4 Remark3
                $year4Remark3 = $sheet1->rangeToArray('I69', NULL, TRUE, FALSE);
                $year4Remark3 = $year4Remark3[0][0];
                $records[] = $year4Remark3;

                // Certificate Year 4 Remark4
                $year4Remark4 = $sheet1->rangeToArray('I70', NULL, TRUE, FALSE);
                $year4Remark4 = $year4Remark4[0][0];
                $records[] = $year4Remark4;

                // Year4 data //



                // Year5 data //
                // Certificate Year 5 courseCode1
                $year5CourseCode1 = $sheet1->rangeToArray('A77', NULL, TRUE, FALSE);
                $year5CourseCode1 = $year5CourseCode1[0][0];
                $records[] = $year5CourseCode1;

                // Certificate Year 5 courseCode2
                $year5CourseCode2 = $sheet1->rangeToArray('A78', NULL, TRUE, FALSE);
                $year5CourseCode2 = $year5CourseCode2[0][0];
                $records[] = $year5CourseCode2;

                // Certificate Year 5 courseCode3
                $year5CourseCode3 = $sheet1->rangeToArray('A79', NULL, TRUE, FALSE);
                $year5CourseCode3 = $year5CourseCode3[0][0];
                $records[] = $year5CourseCode3;

                // Certificate Year 5 courseCode4
                $year5CourseCode4 = $sheet1->rangeToArray('A80', NULL, TRUE, FALSE);
                $year5CourseCode4 = $year5CourseCode4[0][0];
                $records[] = $year5CourseCode4;

                // Certificate Year 5 courseCode5
                $year5CourseCode5 = $sheet1->rangeToArray('A81', NULL, TRUE, FALSE);
                $year5CourseCode5 = $year5CourseCode5[0][0];
                $records[] = $year5CourseCode5;


                // Certificate Year 5 courseName1
                $year5CourseName1 = $sheet1->rangeToArray('B77', NULL, TRUE, FALSE);
                $year5CourseName1 = $year5CourseName1[0][0];
                $records[] = $year5CourseName1;

                // Certificate Year 5 courseName2
                $year5CourseName2 = $sheet1->rangeToArray('B78', NULL, TRUE, FALSE);
                $year5CourseName2 = $year5CourseName2[0][0];
                $records[] = $year5CourseName2;

                // Certificate Year 5 courseName3
                $year5CourseName3 = $sheet1->rangeToArray('B79', NULL, TRUE, FALSE);
                $year5CourseName3 = $year5CourseName3[0][0];
                $records[] = $year5CourseName3;

                // Certificate Year 5 courseName4
                $year5CourseName4 = $sheet1->rangeToArray('B80', NULL, TRUE, FALSE);
                $year5CourseName4 = $year5CourseName4[0][0];
                $records[] = $year5CourseName4;

                // Certificate Year 5 courseName5
                $year5CourseName5 = $sheet1->rangeToArray('B81', NULL, TRUE, FALSE);
                $year5CourseName5 = $year5CourseName5[0][0];
                $records[] = $year5CourseName5;



                // Certificate Year 5 Grade1
                $year5Grade1 = $sheet1->rangeToArray('H77', NULL, TRUE, FALSE);
                $year5Grade1 = $year5Grade1[0][0];
                $records[] = $year5Grade1;

                // Certificate Year 5 Grade2
                $year5Grade2 = $sheet1->rangeToArray('H78', NULL, TRUE, FALSE);
                $year5Grade2 = $year5Grade2[0][0];
                $records[] = $year5Grade2;

                // Certificate Year 5 Grade3
                $year5Grade3 = $sheet1->rangeToArray('H79', NULL, TRUE, FALSE);
                $year5Grade3 = $year5Grade3[0][0];
                $records[] = $year5Grade3;

                // Certificate Year 5 Grade4
                $year5Grade4 = $sheet1->rangeToArray('H80', NULL, TRUE, FALSE);
                $year5Grade4 = $year5Grade4[0][0];
                $records[] = $year5Grade4;

                // Certificate Year 5 Grade5
                $year5Grade5 = $sheet1->rangeToArray('H81', NULL, TRUE, FALSE);
                $year5Grade5 = $year5Grade5[0][0];
                $records[] = $year5Grade5;


                // Certificate Year 5 Remark1
                $year5Remark1 = $sheet1->rangeToArray('I77', NULL, TRUE, FALSE);
                $year5Remark1 = $year5Remark1[0][0];    
                $records[] = $year5Remark1;

                // Certificate Year 5 Remark2
                $year5Remark2 = $sheet1->rangeToArray('I78', NULL, TRUE, FALSE);
                $year5Remark2 = $year5Remark2[0][0];
                $records[] = $year5Remark2;

                // Certificate Year 5 Remark3
                $year5Remark3 = $sheet1->rangeToArray('I79', NULL, TRUE, FALSE);
                $year5Remark3 = $year5Remark3[0][0];
                $records[] = $year5Remark3;

                // Certificate Year 5 Remark4
                $year5Remark4 = $sheet1->rangeToArray('I80', NULL, TRUE, FALSE);
                $year5Remark4 = $year5Remark4[0][0];
                $records[] = $year5Remark4;

                // Certificate Year 5 Remark5
                $year5Remark5 = $sheet1->rangeToArray('I81', NULL, TRUE, FALSE);
                $year5Remark5 = $year5Remark5[0][0];
                $records[] = $year5Remark5;

                // Year5 data //



                // Year6 data //
                // Certificate Year 6 courseCode1
                $year6CourseCode1 = $sheet1->rangeToArray('A87', NULL, TRUE, FALSE);
                $year6CourseCode1 = $year6CourseCode1[0][0];
                $records[] = $year6CourseCode1;

                // Certificate Year 6 courseCode2
                $year6CourseCode2 = $sheet1->rangeToArray('A88', NULL, TRUE, FALSE);
                $year6CourseCode2 = $year6CourseCode2[0][0];
                $records[] = $year6CourseCode2;

                // Certificate Year 6 courseCode3
                $year6CourseCode3 = $sheet1->rangeToArray('A89', NULL, TRUE, FALSE);
                $year6CourseCode3 = $year6CourseCode3[0][0];
                $records[] = $year6CourseCode3;

                // Certificate Year 6 courseCode4
                $year6CourseCode4 = $sheet1->rangeToArray('A90', NULL, TRUE, FALSE);
                $year6CourseCode4 = $year6CourseCode4[0][0];
                $records[] = $year6CourseCode4;



                // Certificate Year 6 courseName1
                $year6CourseName1 = $sheet1->rangeToArray('B87', NULL, TRUE, FALSE);
                $year6CourseName1 = $year6CourseName1[0][0];
                $records[] = $year6CourseName1;

                // Certificate Year 6 courseName2
                $year6CourseName2 = $sheet1->rangeToArray('B88', NULL, TRUE, FALSE);
                $year6CourseName2 = $year6CourseName2[0][0];
                $records[] = $year6CourseName2;

                // Certificate Year 6 courseName3
                $year6CourseName3 = $sheet1->rangeToArray('B89', NULL, TRUE, FALSE);
                $year6CourseName3 = $year6CourseName3[0][0];
                $records[] = $year6CourseName3;

                // Certificate Year 6 courseName4
                $year6CourseName4 = $sheet1->rangeToArray('B90', NULL, TRUE, FALSE);
                $year6CourseName4 = $year6CourseName4[0][0];
                $records[] = $year6CourseName4;


                // Certificate Year 6 Grade1
                $year6Grade1 = $sheet1->rangeToArray('H87', NULL, TRUE, FALSE);
                $year6Grade1 = $year6Grade1[0][0];
                $records[] = $year6Grade1;

                // Certificate Year 6 Grade2
                $year6Grade2 = $sheet1->rangeToArray('H88', NULL, TRUE, FALSE);
                $year6Grade2 = $year6Grade2[0][0];
                $records[] = $year6Grade2;

                // Certificate Year 6 Grade3
                $year6Grade3 = $sheet1->rangeToArray('H89', NULL, TRUE, FALSE);
                $year6Grade3 = $year6Grade3[0][0];
                $records[] = $year6Grade3;

                // Certificate Year 6 Grade4
                $year6Grade4 = $sheet1->rangeToArray('H90', NULL, TRUE, FALSE);
                $year6Grade4 = $year6Grade4[0][0];
                $records[] = $year6Grade4;


                // Certificate Year 6 Remark1
                $year6Remark1 = $sheet1->rangeToArray('I87', NULL, TRUE, FALSE);
                $year6Remark1 = $year6Remark1[0][0];    
                $records[] = $year6Remark1;

                // Certificate Year 6 Remark2
                $year6Remark2 = $sheet1->rangeToArray('I88', NULL, TRUE, FALSE);
                $year6Remark2 = $year6Remark2[0][0];
                $records[] = $year6Remark2;

                // Certificate Year 6 Remark3
                $year6Remark3 = $sheet1->rangeToArray('I89', NULL, TRUE, FALSE);
                $year6Remark3 = $year6Remark3[0][0];
                $records[] = $year6Remark3;

                // Certificate Year 6 Remark4
                $year6Remark4 = $sheet1->rangeToArray('I90', NULL, TRUE, FALSE);
                $year6Remark4 = $year6Remark4[0][0];
                $records[] = $year6Remark4;

                // Year6 data //


                $rowData1 = $sheet1->rangeToArray('A1:' . $highestColumn1 . $highestRow1, NULL, TRUE, FALSE);
                

                // $rowData = $sheet->rangeToArray('A1:' . $highestColumn . '1', NULL, TRUE, FALSE);
                //For checking certificate limit updated by Mandar
                $recordToGenerate=$highestRow1-1;
                $checkStatus = CoreHelper::checkMaxCertificateLimit($recordToGenerate);
                if(!$checkStatus['status']){
                  return response()->json($checkStatus);
                }



                // $objPHPExcel2 = $objReader->load($fullpath);
                // $sheet2 = $objPHPExcel2->getSheet(1);
                // $highestColumn2 = $sheet2->getHighestColumn();
                // $highestRow2 = $sheet2->getHighestDataRow();
                // $rowData2 = $sheet2->rangeToArray('A1:' . $highestColumn2 . $highestRow2, NULL, TRUE, FALSE);
                // $rowData2 = $sheet2->rangeToArray('A1:' . $highestColumn2 . '1', NULL, TRUE, FALSE);
                // foreach ($rowData[0] as $key => $value) {
                // }
                $excelData=array('rowData1'=>$records,'rowData2'=>$records,'auth_site_id'=>$auth_site_id);
                $response = $this->dispatch(new ValidateExcelDemoSOMJob($excelData));
                // print_r($response);
                $responseData =$response->getData();
                //print_r($responseData);
                if($responseData->success){
                    $old_rows=$responseData->old_rows;
                    $new_rows=$responseData->new_rows;
                }else{
                   return $response;
                }
              
            }

            //echo $fullpath;
            if (file_exists($fullpath)) {
                unlink($fullpath);
            }
            
            return response()->json(['success'=>true,'type' => 'success', 'message' => 'success','old_rows'=>$old_rows,'new_rows'=>$new_rows]);

        }
        else{
            return response()->json(['success'=>false,'message'=>'File not found!']);
        }


    }


    public function uploadfile(Request $request,CheckUploadedFileOnAwsORLocalService $checkUploadedFileOnAwsOrLocal){
        // $start_time = microtime(true); 
        $get_file_aws_local_flag = $checkUploadedFileOnAwsOrLocal->checkUploadedFileOnAwsORLocal();

        $domain = \Request::getHost();
        $subdomain = explode('.', $domain);

        //Blockchain data
        //Generate id once and define to contractAddress variable
        //CoreHelper::checkContactAddress(100,$templateType='CUSTOMTEMPLATE');
        $contractAddress="0x9b2bBB33CB0C72d9A1Cb7c375851Da5da1b0591F";
        $isBlockChain=1;
       
        $template_id = 100;
        
        $previewPdf = array($request['previewPdf'],$request['previewWithoutBg']);
        $auth_site_id=Auth::guard('admin')->user()->site_id;
        //check file is uploaded or not
        if($request->hasFile('field_file')){
            //check extension
            $file_name = $request['field_file']->getClientOriginalName();
            $ext = pathinfo($file_name, PATHINFO_EXTENSION);

            //excel file name
            $excelfile =  date("YmdHis") . "_" . $file_name;
            $target_path = public_path().'/backend/canvas/dummy_images/'.$template_id;
            $fullpath = $target_path.'/'.$excelfile;

            if(!is_dir($target_path)){
                mkdir($target_path, 0777);
            }

            if($request['field_file']->move($target_path,$excelfile)){
                //get excel file data
                if($ext == 'xlsx' || $ext == 'Xlsx'){
                    $inputFileType = 'Xlsx';
                }
                else{
                    $inputFileType = 'Xls';
                }

                if($ext == 'csv' || $ext == 'Csv'){
                    $inputFileType = 'Csv';
                }
                else{
                    $inputFileType = 'Csv';
                }

                $systemConfig = SystemConfig::select('sandboxing')->where('site_id',$auth_site_id)->first();
                if($get_file_aws_local_flag->file_aws_local == '1'){
                    CoreHelper::sandboxingFileAWS($systemConfig,$fullpath,$template_id,$excelfile);
                 }
                else{
                    // add sandboxing funcationlity
                    CoreHelper::sandboxingFile($systemConfig,$fullpath,$template_id,$excelfile);
                }
                $objReader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
                /**  Load $inputFileName to a Spreadsheet Object  **/
                $objPHPExcel1 = $objReader->load($fullpath);
                $sheet1 = $objPHPExcel1->getSheet(0);
                $highestColumn1 = $sheet1->getHighestColumn();
                $highestRow1 = $sheet1->getHighestDataRow();
                
                $records = array();
                // Certificate top name
                $certificateTopName = $sheet1->rangeToArray('A4', NULL, TRUE, FALSE);
                $certificateTopName = $certificateTopName[0][0];
                $records[] = $certificateTopName;
                
                // Certificate address
                $certificateAddress = $sheet1->rangeToArray('A5', NULL, TRUE, FALSE);
                $certificateAddress = $certificateAddress[0][0];
                $records[] = $certificateAddress;

                // Certificate school
                $certificateSchool = $sheet1->rangeToArray('A7', NULL, TRUE, FALSE);
                $certificateSchool = $certificateSchool[0][0];
                $records[] = $certificateSchool;

                // Certificate statement of results
                $certificateStm = $sheet1->rangeToArray('A9', NULL, TRUE, FALSE);
                $certificateStm = $certificateStm[0][0];
                $records[] = $certificateStm;

                // Certificate name
                $certificateName = $sheet1->rangeToArray('B11', NULL, TRUE, FALSE);
                $certificateName = $certificateName[0][0];
                $records[] = $certificateName;

                // Certificate gender
                $certificateGender = $sheet1->rangeToArray('B12', NULL, TRUE, FALSE);
                $certificateGender = $certificateGender[0][0];
                $records[] = $certificateGender;

                // Certificate DOB
                $certificateDOB = $sheet1->rangeToArray('B13', NULL, TRUE, FALSE);
                $certificateDOB = $certificateDOB[0][0];
                $records[] = $certificateDOB;

                // Certificate Nationality
                $certificateNationality = $sheet1->rangeToArray('B14', NULL, TRUE, FALSE);
                $certificateNationality = $certificateNationality[0][0];
                $records[] = $certificateNationality;

                // Certificate Reg No
                $certificateRegNo = $sheet1->rangeToArray('G11', NULL, TRUE, FALSE);
                $certificateRegNo = $certificateRegNo[0][0];
                $records[] = $certificateRegNo;

                // Certificate Programme
                $certificateProgramme = $sheet1->rangeToArray('G12', NULL, TRUE, FALSE);
                $certificateProgramme = $certificateProgramme[0][0];
                $records[] = $certificateProgramme;

                // Cert School
                $certSchool = $sheet1->rangeToArray('G13', NULL, TRUE, FALSE);
                $certSchool = $certSchool[0][0];
                $records[] = $certSchool;

                // Certificate year of entry
                $certificateYearEntry = $sheet1->rangeToArray('G14', NULL, TRUE, FALSE);
                $certificateYearEntry = $certificateYearEntry[0][0];
                $records[] = $certificateYearEntry;

                // Certificate award
                $certificateAward = $sheet1->rangeToArray('A17', NULL, TRUE, FALSE);
                $certificateAward = $certificateAward[0][0];
                $records[] = $certificateAward;

                // Certificate award1
                $certificateAward1 = $sheet1->rangeToArray('A16', NULL, TRUE, FALSE);
                $certificateAward1 = $certificateAward1[0][0];
                $records[] = $certificateAward1;

                // Foundation year data //
                // Certificate Foundation courseCode1
                $foundationCourseCode1 = $sheet1->rangeToArray('A23', NULL, TRUE, FALSE);
                $foundationCourseCode1 = $foundationCourseCode1[0][0];
                $records[] = $foundationCourseCode1;

                // Certificate Foundation courseCode2
                $foundationCourseCode2 = $sheet1->rangeToArray('A24', NULL, TRUE, FALSE);
                $foundationCourseCode2 = $foundationCourseCode2[0][0];
                $records[] = $foundationCourseCode2;

                // Certificate Foundation courseCode3
                $foundationCourseCode3 = $sheet1->rangeToArray('A25', NULL, TRUE, FALSE);
                $foundationCourseCode3 = $foundationCourseCode3[0][0];
                $records[] = $foundationCourseCode3;


                // Certificate Foundation courseName1
                $foundationCourseName1 = $sheet1->rangeToArray('B23', NULL, TRUE, FALSE);
                $foundationCourseName1 = $foundationCourseName1[0][0];
                $records[] = $foundationCourseName1;

                // Certificate Foundation courseName2
                $foundationCourseName2 = $sheet1->rangeToArray('B24', NULL, TRUE, FALSE);
                $foundationCourseName2 = $foundationCourseName2[0][0];
                $records[] = $foundationCourseName2;

                // Certificate Foundation courseName3
                $foundationCourseName3 = $sheet1->rangeToArray('B25', NULL, TRUE, FALSE);
                $foundationCourseName3 = $foundationCourseName3[0][0];
                $records[] = $foundationCourseName3;

                // Certificate Foundation Grade1
                $foundationGrade1 = $sheet1->rangeToArray('H23', NULL, TRUE, FALSE);
                $foundationGrade1 = $foundationGrade1[0][0];
                $records[] = $foundationGrade1;

                // Certificate Foundation Grade2
                $foundationGrade2 = $sheet1->rangeToArray('H24', NULL, TRUE, FALSE);
                $foundationGrade2 = $foundationGrade2[0][0];
                $records[] = $foundationGrade2;

                // Certificate Foundation Grade3
                $foundationGrade3 = $sheet1->rangeToArray('H25', NULL, TRUE, FALSE);
                $foundationGrade3 = $foundationGrade3[0][0];
                $records[] = $foundationGrade3;

                // Certificate Foundation Remark1
                $foundationRemark1 = $sheet1->rangeToArray('I23', NULL, TRUE, FALSE);
                $foundationRemark1 = $foundationRemark1[0][0];
                $records[] = $foundationRemark1;

                // Certificate Foundation Remark2
                $foundationRemark2 = $sheet1->rangeToArray('I24', NULL, TRUE, FALSE);
                $foundationRemark2 = $foundationRemark2[0][0];
                $records[] = $foundationRemark2;

                // Certificate Foundation Remark3
                $foundationRemark3 = $sheet1->rangeToArray('I25', NULL, TRUE, FALSE);
                $foundationRemark3 = $foundationRemark3[0][0];
                $records[] = $foundationRemark3;
                // Foundation year data //


                // Year1 data //
                // Certificate Year 1 courseCode1
                $year1CourseCode1 = $sheet1->rangeToArray('A33', NULL, TRUE, FALSE);
                $year1CourseCode1 = $year1CourseCode1[0][0];
                $records[] = $year1CourseCode1;

                // Certificate Year 1 courseCode2
                $year1CourseCode2 = $sheet1->rangeToArray('A34', NULL, TRUE, FALSE);
                $year1CourseCode2 = $year1CourseCode2[0][0];
                $records[] = $year1CourseCode2;

                // Certificate Year 1 courseCode3
                $year1CourseCode3 = $sheet1->rangeToArray('A35', NULL, TRUE, FALSE);
                $year1CourseCode3 = $year1CourseCode3[0][0];
                $records[] = $year1CourseCode3;

                // Certificate Year 1 courseCode4
                $year1CourseCode4 = $sheet1->rangeToArray('A36', NULL, TRUE, FALSE);
                $year1CourseCode4 = $year1CourseCode4[0][0];
                $records[] = $year1CourseCode4;

                // Certificate Year 1 courseCode5
                $year1CourseCode5 = $sheet1->rangeToArray('A37', NULL, TRUE, FALSE);
                $year1CourseCode5 = $year1CourseCode5[0][0];
                $records[] = $year1CourseCode5;



                // Certificate Year 1 courseName1
                $year1CourseName1 = $sheet1->rangeToArray('B33', NULL, TRUE, FALSE);
                $year1CourseName1 = $year1CourseName1[0][0];
                $records[] = $year1CourseName1;

                // Certificate Year 1 courseName2
                $year1CourseName2 = $sheet1->rangeToArray('B34', NULL, TRUE, FALSE);
                $year1CourseName2 = $year1CourseName2[0][0];
                $records[] = $year1CourseName2;

                // Certificate Year 1 courseName3
                $year1CourseName3 = $sheet1->rangeToArray('B35', NULL, TRUE, FALSE);
                $year1CourseName3 = $year1CourseName3[0][0];
                $records[] = $year1CourseName3;

                // Certificate Year 1 courseName4
                $year1CourseName4 = $sheet1->rangeToArray('B36', NULL, TRUE, FALSE);
                $year1CourseName4 = $year1CourseName4[0][0];
                $records[] = $year1CourseName4;

                // Certificate Year 1 courseName5
                $year1CourseName5 = $sheet1->rangeToArray('B37', NULL, TRUE, FALSE);
                $year1CourseName5 = $year1CourseName5[0][0];
                $records[] = $year1CourseName5;


                // Certificate Year 1 Grade1
                $year1Grade1 = $sheet1->rangeToArray('H33', NULL, TRUE, FALSE);
                $year1Grade1 = $year1Grade1[0][0];
                $records[] = $year1Grade1;

                // Certificate Year 1 Grade2
                $year1Grade2 = $sheet1->rangeToArray('H34', NULL, TRUE, FALSE);
                $year1Grade2 = $year1Grade2[0][0];
                $records[] = $year1Grade2;

                // Certificate Year 1 Grade3
                $year1Grade3 = $sheet1->rangeToArray('H35', NULL, TRUE, FALSE);
                $year1Grade3 = $year1Grade3[0][0];
                $records[] = $year1Grade3;

                // Certificate Year 1 Grade4
                $year1Grade4 = $sheet1->rangeToArray('H36', NULL, TRUE, FALSE);
                $year1Grade4 = $year1Grade4[0][0];
                $records[] = $year1Grade4;

                // Certificate Year 1 Grade5
                $year1Grade5 = $sheet1->rangeToArray('H37', NULL, TRUE, FALSE);
                $year1Grade5 = $year1Grade5[0][0];
                $records[] = $year1Grade5;


                // Certificate Year 1 Remark1
                $year1Remark1 = $sheet1->rangeToArray('I33', NULL, TRUE, FALSE);
                $year1Remark1 = $year1Remark1[0][0];
                $records[] = $year1Remark1;

                // Certificate Year 1 Remark2
                $year1Remark2 = $sheet1->rangeToArray('I34', NULL, TRUE, FALSE);
                $year1Remark2 = $year1Remark2[0][0];
                $records[] = $year1Remark2;

                // Certificate Year 1 Remark3
                $year1Remark3 = $sheet1->rangeToArray('I35', NULL, TRUE, FALSE);
                $year1Remark3 = $year1Remark3[0][0];
                $records[] = $year1Remark3;

                // Certificate Year 1 Remark4
                $year1Remark4 = $sheet1->rangeToArray('I36', NULL, TRUE, FALSE);
                $year1Remark4 = $year1Remark4[0][0];
                $records[] = $year1Remark4;

                // Certificate Year 1 Remark5
                $year1Remark5 = $sheet1->rangeToArray('I37', NULL, TRUE, FALSE);
                $year1Remark5 = $year1Remark5[0][0];
                $records[] = $year1Remark5;
                // Year 1 data //



                // Year2 data //
                // Certificate Year 2 courseCode1
                $year2CourseCode1 = $sheet1->rangeToArray('A43', NULL, TRUE, FALSE);
                $year2CourseCode1 = $year2CourseCode1[0][0];
                $records[] = $year2CourseCode1;

                // Certificate Year 2 courseCode2
                $year2CourseCode2 = $sheet1->rangeToArray('A44', NULL, TRUE, FALSE);
                $year2CourseCode2 = $year2CourseCode2[0][0];
                $records[] = $year2CourseCode2;

                // Certificate Year 2 courseCode3
                $year2CourseCode3 = $sheet1->rangeToArray('A45', NULL, TRUE, FALSE);
                $year2CourseCode3 = $year2CourseCode3[0][0];
                $records[] = $year2CourseCode3;

                // Certificate Year 2 courseCode4
                $year2CourseCode4 = $sheet1->rangeToArray('A46', NULL, TRUE, FALSE);
                $year2CourseCode4 = $year2CourseCode4[0][0];
                $records[] = $year2CourseCode4;

                // Certificate Year 2 courseCode5
                $year2CourseCode5 = $sheet1->rangeToArray('A47', NULL, TRUE, FALSE);
                $year2CourseCode5 = $year2CourseCode5[0][0];
                $records[] = $year2CourseCode5;

                // Certificate Year 2 courseCode6
                $year2CourseCode6 = $sheet1->rangeToArray('A48', NULL, TRUE, FALSE);
                $year2CourseCode6 = $year2CourseCode6[0][0];
                $records[] = $year2CourseCode6;


                // Certificate Year 2 courseName1
                $year2CourseName1 = $sheet1->rangeToArray('B43', NULL, TRUE, FALSE);
                $year2CourseName1 = $year2CourseName1[0][0];
                $records[] = $year2CourseName1;

                // Certificate Year 2 courseName2
                $year2CourseName2 = $sheet1->rangeToArray('B44', NULL, TRUE, FALSE);
                $year2CourseName2 = $year2CourseName2[0][0];
                $records[] = $year2CourseName2;

                // Certificate Year 2 courseName3
                $year2CourseName3 = $sheet1->rangeToArray('B45', NULL, TRUE, FALSE);
                $year2CourseName3 = $year2CourseName3[0][0];
                $records[] = $year2CourseName3;

                // Certificate Year 2 courseName4
                $year2CourseName4 = $sheet1->rangeToArray('B46', NULL, TRUE, FALSE);
                $year2CourseName4 = $year2CourseName4[0][0];
                $records[] = $year2CourseName4;

                // Certificate Year 2 courseName5
                $year2CourseName5 = $sheet1->rangeToArray('B47', NULL, TRUE, FALSE);
                $year2CourseName5 = $year2CourseName5[0][0];
                $records[] = $year2CourseName5;

                // Certificate Year 2 courseName6
                $year2CourseName6 = $sheet1->rangeToArray('B48', NULL, TRUE, FALSE);
                $year2CourseName6 = $year2CourseName6[0][0];
                $records[] = $year2CourseName6;


                // Certificate Year 2 Grade1
                $year2Grade1 = $sheet1->rangeToArray('H43', NULL, TRUE, FALSE);
                $year2Grade1 = $year2Grade1[0][0];
                $records[] = $year2Grade1;

                // Certificate Year 2 Grade2
                $year2Grade2 = $sheet1->rangeToArray('H44', NULL, TRUE, FALSE);
                $year2Grade2 = $year2Grade2[0][0];
                $records[] = $year2Grade2;

                // Certificate Year 2 Grade3
                $year2Grade3 = $sheet1->rangeToArray('H45', NULL, TRUE, FALSE);
                $year2Grade3 = $year2Grade3[0][0];
                $records[] = $year2Grade3;

                // Certificate Year 2 Grade4
                $year2Grade4 = $sheet1->rangeToArray('H46', NULL, TRUE, FALSE);
                $year2Grade4 = $year2Grade4[0][0];
                $records[] = $year2Grade4;

                // Certificate Year 2 Grade5
                $year2Grade5 = $sheet1->rangeToArray('H47', NULL, TRUE, FALSE);
                $year2Grade5 = $year2Grade5[0][0];
                $records[] = $year2Grade5;

                // Certificate Year 2 Grade6
                $year2Grade6 = $sheet1->rangeToArray('H48', NULL, TRUE, FALSE);
                $year2Grade6 = $year2Grade6[0][0];
                $records[] = $year2Grade6;


                // Certificate Year 2 Remark1
                $year2Remark1 = $sheet1->rangeToArray('I43', NULL, TRUE, FALSE);
                $year2Remark1 = $year2Remark1[0][0];
                $records[] = $year2Remark1;

                // Certificate Year 2 Remark2
                $year2Remark2 = $sheet1->rangeToArray('I44', NULL, TRUE, FALSE);
                $year2Remark2 = $year2Remark2[0][0];
                $records[] = $year2Remark2;

                // Certificate Year 2 Remark3
                $year2Remark3 = $sheet1->rangeToArray('I45', NULL, TRUE, FALSE);
                $year2Remark3 = $year2Remark3[0][0];
                $records[] = $year2Remark3;

                // Certificate Year 2 Remark4
                $year2Remark4 = $sheet1->rangeToArray('I46', NULL, TRUE, FALSE);
                $year2Remark4 = $year2Remark4[0][0];
                $records[] = $year2Remark4;

                // Certificate Year 2 Remark5
                $year2Remark5 = $sheet1->rangeToArray('I47', NULL, TRUE, FALSE);
                $year2Remark5 = $year2Remark5[0][0];
                $records[] = $year2Remark5;

                // Certificate Year 2 Remark6
                $year2Remark6 = $sheet1->rangeToArray('I48', NULL, TRUE, FALSE);
                $year2Remark6 = $year2Remark6[0][0];
                $records[] = $year2Remark6;

                // Year2 data //



                // Year3 data //
                // Certificate Year 3 courseCode1
                $year3CourseCode1 = $sheet1->rangeToArray('A53', NULL, TRUE, FALSE);
                $year3CourseCode1 = $year3CourseCode1[0][0];
                $records[] = $year3CourseCode1;

                // Certificate Year 3 courseCode2
                $year3CourseCode2 = $sheet1->rangeToArray('A54', NULL, TRUE, FALSE);
                $year3CourseCode2 = $year3CourseCode2[0][0];
                $records[] = $year3CourseCode2;

                // Certificate Year 3 courseCode3
                $year3CourseCode3 = $sheet1->rangeToArray('A55', NULL, TRUE, FALSE);
                $year3CourseCode3 = $year3CourseCode3[0][0];
                $records[] = $year3CourseCode3;

                // Certificate Year 3 courseCode4
                $year3CourseCode4 = $sheet1->rangeToArray('A56', NULL, TRUE, FALSE);
                $year3CourseCode4 = $year3CourseCode4[0][0];
                $records[] = $year3CourseCode4;

                // Certificate Year 3 courseCode5
                $year3CourseCode5 = $sheet1->rangeToArray('A57', NULL, TRUE, FALSE);
                $year3CourseCode5 = $year3CourseCode5[0][0];
                $records[] = $year3CourseCode5;


                // Certificate Year 3 courseName1
                $year3CourseName1 = $sheet1->rangeToArray('B53', NULL, TRUE, FALSE);
                $year3CourseName1 = $year3CourseName1[0][0];
                $records[] = $year3CourseName1;

                // Certificate Year 3 courseName2
                $year3CourseName2 = $sheet1->rangeToArray('B54', NULL, TRUE, FALSE);
                $year3CourseName2 = $year3CourseName2[0][0];
                $records[] = $year3CourseName2;

                // Certificate Year 3 courseName3
                $year3CourseName3 = $sheet1->rangeToArray('B55', NULL, TRUE, FALSE);
                $year3CourseName3 = $year3CourseName3[0][0];
                $records[] = $year3CourseName3;

                // Certificate Year 3 courseName4
                $year3CourseName4 = $sheet1->rangeToArray('B56', NULL, TRUE, FALSE);
                $year3CourseName4 = $year3CourseName4[0][0];
                $records[] = $year3CourseName4;

                // Certificate Year 3 courseName5
                $year3CourseName5 = $sheet1->rangeToArray('B57', NULL, TRUE, FALSE);
                $year3CourseName5 = $year3CourseName5[0][0];
                $records[] = $year3CourseName5;


                // Certificate Year 3 Grade1
                $year3Grade1 = $sheet1->rangeToArray('H53', NULL, TRUE, FALSE);
                $year3Grade1 = $year3Grade1[0][0];
                $records[] = $year3Grade1;

                // Certificate Year 3 Grade2
                $year3Grade2 = $sheet1->rangeToArray('H54', NULL, TRUE, FALSE);
                $year3Grade2 = $year3Grade2[0][0];
                $records[] = $year3Grade2;

                // Certificate Year 3 Grade3
                $year3Grade3 = $sheet1->rangeToArray('H55', NULL, TRUE, FALSE);
                $year3Grade3 = $year3Grade3[0][0];
                $records[] = $year3Grade3;

                // Certificate Year 3 Grade4
                $year3Grade4 = $sheet1->rangeToArray('H56', NULL, TRUE, FALSE);
                $year3Grade4 = $year3Grade4[0][0];
                $records[] = $year3Grade4;

                // Certificate Year 3 Grade5
                $year3Grade5 = $sheet1->rangeToArray('H57', NULL, TRUE, FALSE);
                $year3Grade5 = $year3Grade5[0][0];
                $records[] = $year3Grade5;


                // Certificate Year 3 Remark1
                $year3Remark1 = $sheet1->rangeToArray('I53', NULL, TRUE, FALSE);
                $year3Remark1 = $year3Remark1[0][0];
                $records[] = $year3Remark1;

                // Certificate Year 3 Remark2
                $year3Remark2 = $sheet1->rangeToArray('I54', NULL, TRUE, FALSE);
                $year3Remark2 = $year3Remark2[0][0];
                $records[] = $year3Remark2;

                // Certificate Year 3 Remark3
                $year3Remark3 = $sheet1->rangeToArray('I55', NULL, TRUE, FALSE);
                $year3Remark3 = $year3Remark3[0][0];
                $records[] = $year3Remark3;

                // Certificate Year 3 Remark4
                $year3Remark4 = $sheet1->rangeToArray('I56', NULL, TRUE, FALSE);
                $year3Remark4 = $year3Remark4[0][0];
                $records[] = $year3Remark4;

                // Certificate Year 3 Remark5
                $year3Remark5 = $sheet1->rangeToArray('I57', NULL, TRUE, FALSE);
                $year3Remark5 = $year3Remark5[0][0];
                $records[] = $year3Remark5;

                // Year3 data //



                // Year4 data //
                // Certificate Year 4 courseCode1
                $year4CourseCode1 = $sheet1->rangeToArray('A67', NULL, TRUE, FALSE);
                $year4CourseCode1 = $year4CourseCode1[0][0];
                $records[] = $year4CourseCode1;

                // Certificate Year 4 courseCode2
                $year4CourseCode2 = $sheet1->rangeToArray('A68', NULL, TRUE, FALSE);
                $year4CourseCode2 = $year4CourseCode2[0][0];
                $records[] = $year4CourseCode2;

                // Certificate Year 4 courseCode3
                $year4CourseCode3 = $sheet1->rangeToArray('A69', NULL, TRUE, FALSE);
                $year4CourseCode3 = $year4CourseCode3[0][0];
                $records[] = $year4CourseCode3;

                // Certificate Year 4 courseCode4
                $year4CourseCode4 = $sheet1->rangeToArray('A70', NULL, TRUE, FALSE);
                $year4CourseCode4 = $year4CourseCode4[0][0];
                $records[] = $year4CourseCode4;


                // Certificate Year 4 courseName1
                $year4CourseName1 = $sheet1->rangeToArray('B67', NULL, TRUE, FALSE);
                $year4CourseName1 = $year4CourseName1[0][0];
                $records[] = $year4CourseName1;

                // Certificate Year 4 courseName2
                $year4CourseName2 = $sheet1->rangeToArray('B68', NULL, TRUE, FALSE);
                $year4CourseName2 = $year4CourseName2[0][0];
                $records[] = $year4CourseName2;

                // Certificate Year 4 courseName3
                $year4CourseName3 = $sheet1->rangeToArray('B69', NULL, TRUE, FALSE);
                $year4CourseName3 = $year4CourseName3[0][0];
                $records[] = $year4CourseName3;

                // Certificate Year 4 courseName4
                $year4CourseName4 = $sheet1->rangeToArray('B70', NULL, TRUE, FALSE);
                $year4CourseName4 = $year4CourseName4[0][0];
                $records[] = $year4CourseName4;


                // Certificate Year 4 Grade1
                $year4Grade1 = $sheet1->rangeToArray('H67', NULL, TRUE, FALSE);
                $year4Grade1 = $year4Grade1[0][0];
                $records[] = $year4Grade1;

                // Certificate Year 4 Grade2
                $year4Grade2 = $sheet1->rangeToArray('H68', NULL, TRUE, FALSE);
                $year4Grade2 = $year4Grade2[0][0];
                $records[] = $year4Grade2;

                // Certificate Year 4 Grade3
                $year4Grade3 = $sheet1->rangeToArray('H69', NULL, TRUE, FALSE);
                $year4Grade3 = $year4Grade3[0][0];
                $records[] = $year4Grade3;

                // Certificate Year 4 Grade4
                $year4Grade4 = $sheet1->rangeToArray('H70', NULL, TRUE, FALSE);
                $year4Grade4 = $year4Grade4[0][0];
                $records[] = $year4Grade4;


                // Certificate Year 4 Remark1
                $year4Remark1 = $sheet1->rangeToArray('I67', NULL, TRUE, FALSE);
                $year4Remark1 = $year4Remark1[0][0];
                $records[] = $year4Remark1;

                // Certificate Year 4 Remark2
                $year4Remark2 = $sheet1->rangeToArray('I68', NULL, TRUE, FALSE);
                $year4Remark2 = $year4Remark2[0][0];
                $records[] = $year4Remark2;

                // Certificate Year 4 Remark3
                $year4Remark3 = $sheet1->rangeToArray('I69', NULL, TRUE, FALSE);
                $year4Remark3 = $year4Remark3[0][0];
                $records[] = $year4Remark3;

                // Certificate Year 4 Remark4
                $year4Remark4 = $sheet1->rangeToArray('I70', NULL, TRUE, FALSE);
                $year4Remark4 = $year4Remark4[0][0];
                $records[] = $year4Remark4;

                // Year4 data //



                // Year5 data //
                // Certificate Year 5 courseCode1
                $year5CourseCode1 = $sheet1->rangeToArray('A77', NULL, TRUE, FALSE);
                $year5CourseCode1 = $year5CourseCode1[0][0];
                $records[] = $year5CourseCode1;

                // Certificate Year 5 courseCode2
                $year5CourseCode2 = $sheet1->rangeToArray('A78', NULL, TRUE, FALSE);
                $year5CourseCode2 = $year5CourseCode2[0][0];
                $records[] = $year5CourseCode2;

                // Certificate Year 5 courseCode3
                $year5CourseCode3 = $sheet1->rangeToArray('A79', NULL, TRUE, FALSE);
                $year5CourseCode3 = $year5CourseCode3[0][0];
                $records[] = $year5CourseCode3;

                // Certificate Year 5 courseCode4
                $year5CourseCode4 = $sheet1->rangeToArray('A80', NULL, TRUE, FALSE);
                $year5CourseCode4 = $year5CourseCode4[0][0];
                $records[] = $year5CourseCode4;

                // Certificate Year 5 courseCode5
                $year5CourseCode5 = $sheet1->rangeToArray('A81', NULL, TRUE, FALSE);
                $year5CourseCode5 = $year5CourseCode5[0][0];
                $records[] = $year5CourseCode5;


                // Certificate Year 5 courseName1
                $year5CourseName1 = $sheet1->rangeToArray('B77', NULL, TRUE, FALSE);
                $year5CourseName1 = $year5CourseName1[0][0];
                $records[] = $year5CourseName1;

                // Certificate Year 5 courseName2
                $year5CourseName2 = $sheet1->rangeToArray('B78', NULL, TRUE, FALSE);
                $year5CourseName2 = $year5CourseName2[0][0];
                $records[] = $year5CourseName2;

                // Certificate Year 5 courseName3
                $year5CourseName3 = $sheet1->rangeToArray('B79', NULL, TRUE, FALSE);
                $year5CourseName3 = $year5CourseName3[0][0];
                $records[] = $year5CourseName3;

                // Certificate Year 5 courseName4
                $year5CourseName4 = $sheet1->rangeToArray('B80', NULL, TRUE, FALSE);
                $year5CourseName4 = $year5CourseName4[0][0];
                $records[] = $year5CourseName4;

                // Certificate Year 5 courseName5
                $year5CourseName5 = $sheet1->rangeToArray('B81', NULL, TRUE, FALSE);
                $year5CourseName5 = $year5CourseName5[0][0];
                $records[] = $year5CourseName5;



                // Certificate Year 5 Grade1
                $year5Grade1 = $sheet1->rangeToArray('H77', NULL, TRUE, FALSE);
                $year5Grade1 = $year5Grade1[0][0];
                $records[] = $year5Grade1;

                // Certificate Year 5 Grade2
                $year5Grade2 = $sheet1->rangeToArray('H78', NULL, TRUE, FALSE);
                $year5Grade2 = $year5Grade2[0][0];
                $records[] = $year5Grade2;

                // Certificate Year 5 Grade3
                $year5Grade3 = $sheet1->rangeToArray('H79', NULL, TRUE, FALSE);
                $year5Grade3 = $year5Grade3[0][0];
                $records[] = $year5Grade3;

                // Certificate Year 5 Grade4
                $year5Grade4 = $sheet1->rangeToArray('H80', NULL, TRUE, FALSE);
                $year5Grade4 = $year5Grade4[0][0];
                $records[] = $year5Grade4;

                // Certificate Year 5 Grade5
                $year5Grade5 = $sheet1->rangeToArray('H81', NULL, TRUE, FALSE);
                $year5Grade5 = $year5Grade5[0][0];
                $records[] = $year5Grade5;


                // Certificate Year 5 Remark1
                $year5Remark1 = $sheet1->rangeToArray('I77', NULL, TRUE, FALSE);
                $year5Remark1 = $year5Remark1[0][0];    
                $records[] = $year5Remark1;

                // Certificate Year 5 Remark2
                $year5Remark2 = $sheet1->rangeToArray('I78', NULL, TRUE, FALSE);
                $year5Remark2 = $year5Remark2[0][0];
                $records[] = $year5Remark2;

                // Certificate Year 5 Remark3
                $year5Remark3 = $sheet1->rangeToArray('I79', NULL, TRUE, FALSE);
                $year5Remark3 = $year5Remark3[0][0];
                $records[] = $year5Remark3;

                // Certificate Year 5 Remark4
                $year5Remark4 = $sheet1->rangeToArray('I80', NULL, TRUE, FALSE);
                $year5Remark4 = $year5Remark4[0][0];
                $records[] = $year5Remark4;

                // Certificate Year 5 Remark5
                $year5Remark5 = $sheet1->rangeToArray('I81', NULL, TRUE, FALSE);
                $year5Remark5 = $year5Remark5[0][0];
                $records[] = $year5Remark5;

                // Year5 data //



                // Year6 data //
                // Certificate Year 6 courseCode1
                $year6CourseCode1 = $sheet1->rangeToArray('A87', NULL, TRUE, FALSE);
                $year6CourseCode1 = $year6CourseCode1[0][0];
                $records[] = $year6CourseCode1;

                // Certificate Year 6 courseCode2
                $year6CourseCode2 = $sheet1->rangeToArray('A88', NULL, TRUE, FALSE);
                $year6CourseCode2 = $year6CourseCode2[0][0];
                $records[] = $year6CourseCode2;

                // Certificate Year 6 courseCode3
                $year6CourseCode3 = $sheet1->rangeToArray('A89', NULL, TRUE, FALSE);
                $year6CourseCode3 = $year6CourseCode3[0][0];
                $records[] = $year6CourseCode3;

                // Certificate Year 6 courseCode4
                $year6CourseCode4 = $sheet1->rangeToArray('A90', NULL, TRUE, FALSE);
                $year6CourseCode4 = $year6CourseCode4[0][0];
                $records[] = $year6CourseCode4;



                // Certificate Year 6 courseName1
                $year6CourseName1 = $sheet1->rangeToArray('B87', NULL, TRUE, FALSE);
                $year6CourseName1 = $year6CourseName1[0][0];
                $records[] = $year6CourseName1;

                // Certificate Year 6 courseName2
                $year6CourseName2 = $sheet1->rangeToArray('B88', NULL, TRUE, FALSE);
                $year6CourseName2 = $year6CourseName2[0][0];
                $records[] = $year6CourseName2;

                // Certificate Year 6 courseName3
                $year6CourseName3 = $sheet1->rangeToArray('B89', NULL, TRUE, FALSE);
                $year6CourseName3 = $year6CourseName3[0][0];
                $records[] = $year6CourseName3;

                // Certificate Year 6 courseName4
                $year6CourseName4 = $sheet1->rangeToArray('B90', NULL, TRUE, FALSE);
                $year6CourseName4 = $year6CourseName4[0][0];
                $records[] = $year6CourseName4;


                // Certificate Year 6 Grade1
                $year6Grade1 = $sheet1->rangeToArray('H87', NULL, TRUE, FALSE);
                $year6Grade1 = $year6Grade1[0][0];
                $records[] = $year6Grade1;

                // Certificate Year 6 Grade2
                $year6Grade2 = $sheet1->rangeToArray('H88', NULL, TRUE, FALSE);
                $year6Grade2 = $year6Grade2[0][0];
                $records[] = $year6Grade2;

                // Certificate Year 6 Grade3
                $year6Grade3 = $sheet1->rangeToArray('H89', NULL, TRUE, FALSE);
                $year6Grade3 = $year6Grade3[0][0];
                $records[] = $year6Grade3;

                // Certificate Year 6 Grade4
                $year6Grade4 = $sheet1->rangeToArray('H90', NULL, TRUE, FALSE);
                $year6Grade4 = $year6Grade4[0][0];
                $records[] = $year6Grade4;


                // Certificate Year 6 Remark1
                $year6Remark1 = $sheet1->rangeToArray('I87', NULL, TRUE, FALSE);
                $year6Remark1 = $year6Remark1[0][0];    
                $records[] = $year6Remark1;

                // Certificate Year 6 Remark2
                $year6Remark2 = $sheet1->rangeToArray('I88', NULL, TRUE, FALSE);
                $year6Remark2 = $year6Remark2[0][0];
                $records[] = $year6Remark2;

                // Certificate Year 6 Remark3
                $year6Remark3 = $sheet1->rangeToArray('I89', NULL, TRUE, FALSE);
                $year6Remark3 = $year6Remark3[0][0];
                $records[] = $year6Remark3;

                // Certificate Year 6 Remark4
                $year6Remark4 = $sheet1->rangeToArray('I90', NULL, TRUE, FALSE);
                $year6Remark4 = $year6Remark4[0][0];
                $records[] = $year6Remark4;


                // Year6 data //

                $rowData1 = $sheet1->rangeToArray('A1:' . $highestColumn1 . $highestRow1, NULL, TRUE, FALSE);
                // $rowData = $sheet->rangeToArray('A1:' . $highestColumn . '1', NULL, TRUE, FALSE);
                
                // $objPHPExcel2 = $objReader->load($fullpath);
                // $sheet2 = $objPHPExcel2->getSheet(1);
                // $highestColumn2 = $sheet2->getHighestColumn();
                // $highestRow2 = $sheet2->getHighestDataRow();
                // $rowData2 = $sheet2->rangeToArray('A1:' . $highestColumn2 . $highestRow2, NULL, TRUE, FALSE);
                // $rowData2 = $sheet2->rangeToArray('A1:' . $highestColumn2 . '1', NULL, TRUE, FALSE);
                // foreach ($rowData[0] as $key => $value) {

            } 
        }
        else{
            return response()->json(['success'=>false,'message'=>'File not found!']);
        }

        // unset($rowData1[0]);
        // unset($rowData2[0]);
        // $rowData1=array_values($rowData1);
        // $rowData2=array_values($rowData2);
        //store ghost image
        //$tmpDir = $this->createTemp(public_path().'/backend/images/ghosttemp/temp');
        $admin_id = \Auth::guard('admin')->user()->toArray();
 
        /*//Separate students subjects
        $subjectsArr = array();
        foreach ($rowData2 as $element) {
            $subjectsArr[$element[0]][] = $element;
        }

        //print_r($result);
        foreach ($rowData1 as $readData) {
         
            $subjects=$subjectsArr[$readData[3]];
            //Separate semesters 
            $semesterArr = array();
            foreach ($subjects as $element) {
                $semesterArr[$element[1]][] = $element;
            }
            ksort($semesterArr);
           // print_r($semesterArr);
          $this->certificateGenerate($readData,$semesterArr);
          exit;
        }*/
        //$link=$this->certificateGenerate($rowData1,$rowData2,$template_id,$previewPdf,$excelfile);
        /* echo json_encode(array("studentData"=>$rowData1,"subjects"=>$rowData2));
        exit;*/
        $pdfData=array('studentDataOrg'=>$records,'subjectsDataOrg'=>$records,'auth_site_id'=>$auth_site_id,'template_id'=>$template_id,'previewPdf'=>$previewPdf,'excelfile'=>$excelfile,"contractAddress"=>$contractAddress,"isBlockChain"=>$isBlockChain);
        $link = $this->dispatch(new PdfGenerateDemoSOMJob($pdfData));
        /*// End clock time in seconds 
        $end_time = microtime(true);
        // Calculate script execution time 
        $execution_time = ($end_time - $start_time); 
  
        echo " Execution time of script = ".$execution_time." sec";*/
        return response()->json(['success'=>true,'message'=>'Certificates generated successfully.','link'=>$link]);
    }


    public function testUploadFile(Request $request,CheckUploadedFileOnAwsORLocalService $checkUploadedFileOnAwsOrLocal){
        // $start_time = microtime(true); 
        $get_file_aws_local_flag = $checkUploadedFileOnAwsOrLocal->checkUploadedFileOnAwsORLocal();

        $domain = \Request::getHost();
        $subdomain = explode('.', $domain);

        //Blockchain data
        //Generate id once and define to contractAddress variable
        //CoreHelper::checkContactAddress(100,$templateType='CUSTOMTEMPLATE');
        $contractAddress="0x9b2bBB33CB0C72d9A1Cb7c375851Da5da1b0591F";
        $isBlockChain=1;
       
        $template_id = 100;
        
        $previewPdf = array($request['previewPdf'],$request['previewWithoutBg']);
        $auth_site_id=Auth::guard('admin')->user()->site_id;
        //check file is uploaded or not
        if($request->hasFile('field_file')){
            //check extension
            $file_name = $request['field_file']->getClientOriginalName();
            $ext = pathinfo($file_name, PATHINFO_EXTENSION);

            //excel file name
            $excelfile =  date("YmdHis") . "_" . $file_name;
            $target_path = public_path().'/backend/canvas/dummy_images/'.$template_id;
            $fullpath = $target_path.'/'.$excelfile;

            if(!is_dir($target_path)){
                mkdir($target_path, 0777);
            }

            if($request['field_file']->move($target_path,$excelfile)){
                //get excel file data
                
                if($ext == 'xlsx' || $ext == 'Xlsx'){
                    $inputFileType = 'Xlsx';
                }
                else{
                    $inputFileType = 'Xls';
                }
                $auth_site_id=Auth::guard('admin')->user()->site_id;

                $systemConfig = SystemConfig::select('sandboxing')->where('site_id',$auth_site_id)->first();
                if($get_file_aws_local_flag->file_aws_local == '1'){
                    if($systemConfig['sandboxing'] == 1){
                        $sandbox_directory = \Config::get('constant.amazone_path').$subdomain[0].'/'.\Config::get('constant.sandbox').'/';
                        //if directory not exist make directory
                        if(!is_dir($sandbox_directory)){
                
                            mkdir($sandbox_directory, 0777);
                        }

                        $aws_excel = \Storage::disk('s3')->put($subdomain[0].'/'.\Config::get('constant.sandbox').'/'.$template_id.'/'.$excelfile, file_get_contents($fullpath), 'public');
                        $filename1 = \Storage::disk('s3')->url($excelfile);
                        
                    }else{
                        
                        $aws_excel = \Storage::disk('s3')->put($subdomain[0].'/'.\Config::get('constant.template').'/'.$template_id.'/'.$excelfile, file_get_contents($fullpath), 'public');
                        $filename1 = \Storage::disk('s3')->url($excelfile);
                    }
                }
                else{

                      $sandbox_directory = public_path().'/'.$subdomain[0].'/'.\Config::get('constant.sandbox').'/';
                    //if directory not exist make directory
                    if(!is_dir($sandbox_directory)){
            
                        mkdir($sandbox_directory, 0777);
                    }

                    if($systemConfig['sandboxing'] == 1){
                        $aws_excel = \File::copy($fullpath,public_path().'/'.$subdomain[0].'/'.\Config::get('constant.sandbox').'/'.$template_id.'/'.$excelfile);
                        
                    }else{
                        $aws_excel = \File::copy($fullpath,public_path().'/'.$subdomain[0].'/'.\Config::get('constant.template').'/'.$template_id.'/'.$excelfile);
                        
                    }
                }
                $objReader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
                /**  Load $inputFileName to a Spreadsheet Object  **/
                $objPHPExcel1 = $objReader->load($fullpath);
                $sheet1 = $objPHPExcel1->getSheet(0);
                $highestColumn1 = $sheet1->getHighestColumn();
                $highestRow1 = $sheet1->getHighestDataRow();
                $rowData1 = $sheet1->rangeToArray('A1:' . $highestColumn1 . $highestRow1, NULL, TRUE, FALSE);
                 //For checking certificate limit updated by Mandar
                $recordToGenerate=$highestRow1-1;
                $checkStatus = CoreHelper::checkMaxCertificateLimit($recordToGenerate);
                if(!$checkStatus['status']){
                  return response()->json($checkStatus);
                }
                $excelData=array('rowData1'=>$rowData1,'auth_site_id'=>$auth_site_id,'dropdown_template_id'=>$dropdown_template_id);
                $response = $this->dispatch(new ValidateExcelDemoJob($excelData));
                // print_r($response);
                $responseData =$response->getData();
               
                if($responseData->success){
                    $old_rows=$responseData->old_rows;
                    $new_rows=$responseData->new_rows;
                }else{
                   // return $response;
                }
            }
            
            if (file_exists($fullpath)) {
                unlink($fullpath);
            }
            // return response()->json(['success'=>true,'type' => 'success', 'message' => 'success','old_rows'=>$old_rows,'new_rows'=>$new_rows,'loaderFile'=>$loaderData['fileName'],'loader_token'=>$loaderData['loader_token']]);

        }
        

        unset($rowData1[0]);
        // unset($rowData2[0]);
        $rowData1=array_values($rowData1);
        // $rowData2=array_values($rowData2);
        //store ghost image
        //$tmpDir = $this->createTemp(public_path().'/backend/images/ghosttemp/temp');
        $admin_id = \Auth::guard('admin')->user()->toArray();
 
        /*//Separate students subjects
        $subjectsArr = array();
        foreach ($rowData2 as $element) {
            $subjectsArr[$element[0]][] = $element;
        }

        //print_r($result);
        foreach ($rowData1 as $readData) {
         
            $subjects=$subjectsArr[$readData[3]];
            //Separate semesters 
            $semesterArr = array();
            foreach ($subjects as $element) {
                $semesterArr[$element[1]][] = $element;
            }
            ksort($semesterArr);
           // print_r($semesterArr);
          $this->certificateGenerate($readData,$semesterArr);
          exit;
        }*/
        //$link=$this->certificateGenerate($rowData1,$rowData2,$template_id,$previewPdf,$excelfile);
        /* echo json_encode(array("studentData"=>$rowData1,"subjects"=>$rowData2));
        exit;*/
        $pdf_data=array('studentDataOrg'=>$rowData1,'subjectsDataOrg'=>$rowData1,'auth_site_id'=>$auth_site_id,'template_id'=>$template_id,'previewPdf'=>$previewPdf,'excelfile'=>$excelfile,"contractAddress"=>$contractAddress,"isBlockChain"=>$isBlockChain);

        $studentDataOrg=$pdf_data['studentDataOrg'];
        $template_id=$pdf_data['template_id'];
        $previewPdf=$pdf_data['previewPdf'];
        $excelfile=$pdf_data['excelfile'];
        $auth_site_id=$pdf_data['auth_site_id'];
        $previewWithoutBg=$previewPdf[1];
        $previewPdf=$previewPdf[0];
         $domain = \Request::getHost();
        $subdomain = explode('.', $domain);

        $systemConfig = SystemConfig::select('sandboxing','printer_name')->where('site_id',$auth_site_id)->first();
        $printer_name = $systemConfig['printer_name'];
 
        $ghostImgArr = array();
        $pdfBig = new TCPDF('P', 'mm', array('210', '297'), true, 'UTF-8', false);
        $pdfBig->SetCreator(PDF_CREATOR);
        $pdfBig->SetAuthor('TCPDF');
        $pdfBig->SetTitle('Certificate');
        $pdfBig->SetSubject('');

        // remove default header/footer
        $pdfBig->setPrintHeader(false);
        $pdfBig->setPrintFooter(false);
        $pdfBig->SetAutoPageBreak(false, 0);


        // add spot colors
        $pdfBig->AddSpotColor('Spot Red', 30, 100, 90, 10);        // For Invisible
        $pdfBig->AddSpotColor('Spot Dark Green', 100, 50, 80, 45); // clear text on bottom red and in clear text logo
        $pdfBig->AddSpotColor('Spot Light Yellow', 0, 0, 100, 0);
        //set fonts
        $arial = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\Arial.TTF', 'TrueTypeUnicode', '', 96);
        $arialb = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\Arialb.TTF', 'TrueTypeUnicode', '', 96);
        $arialNarrow = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\arialn.TTF', 'TrueTypeUnicode', '', 96);
        $arialNarrowB = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\ARIALNB.TTF', 'TrueTypeUnicode', '', 96);
        $times = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\Times-New-Roman.ttf', 'TrueTypeUnicode', '', 96);
        $timesb = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\Times-New-Roman-Bold.ttf', 'TrueTypeUnicode', '', 96);
        $AdobeCaslonProRegular = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\Adobe_Caslon_Pro_Regular.ttf', 'TrueTypeUnicode', '', 96);
        $CourierRegular = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\CourierRegular.ttf', 'TrueTypeUnicode', '', 96);
        $HankenGroteskRegular = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\HankenGroteskRegular.ttf', 'TrueTypeUnicode', '', 96);
        $HankenGroteskBold = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\HankenGroteskBold.ttf', 'TrueTypeUnicode', '', 96);
        $HankenGroteskLight = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\HankenGroteskLight.ttf', 'TrueTypeUnicode', '', 96);



        $preview_serial_no=1;
        //$card_serial_no="";
        $log_serial_no = 1;
        // $cardDetails=$this->getNextCardNo('vbitM');
        $card_serial_no=$cardDetails->next_serial_no;
        $generated_documents=0;  //for custom loader

        foreach ($studentDataOrg as $studentData) {
         
            if($card_serial_no>999999&&$previewPdf!=1){
                echo "<h5>Your card series ended...!</h5>";
                exit;
            }
            //For Custom Loader
            $startTimeLoader =  date('Y-m-d H:i:s');    
            $high_res_bg="Sadabai_Raisoni_Nagpur_BG.jpg"; // VBIT_BG, VBIT_BG_DATA
            $low_res_bg="Sadabai_Raisoni_Nagpur_BG.jpg";
            $pdfBig->AddPage();
            $pdfBig->SetFont($arialNarrowB, '', 8, '', false);
            //set background image
            $template_img_generate = public_path().'\\'.$subdomain[0].'\backend\canvas\bg_images\\'.$high_res_bg;   

            if($previewPdf==1){
                if($previewWithoutBg!=1){
                    $pdfBig->Image($template_img_generate, 0, 0, '210', '297', "JPG", '', 'R', true);
                }
                $date_font_size = '11';
                $date_nox = 13;
                $date_noy = 40;
                $date_nostr = 'DRAFT '.date('d-m-Y H:i:s');
                // $pdfBig->SetFont($arialb, '', $date_font_size, '', false);
                // $pdfBig->SetTextColor(192,192,192);
                // $pdfBig->SetXY($date_nox, $date_noy);
                // $pdfBig->Cell(0, 0, $date_nostr, 0, false, 'L');
                // $pdfBig->SetTextColor(0,0,0,100,false,'');
                // $pdfBig->SetFont($arialNarrowB, '', 9, '', false);
            }
            $pdfBig->setPageMark();

            $ghostImgArr = array();
            $pdf = new TCPDF('P', 'mm', array('210', '297'), true, 'UTF-8', false);
            $pdf->SetCreator(PDF_CREATOR);
            $pdf->SetAuthor('TCPDF');
            $pdf->SetTitle('Certificate');
            $pdf->SetSubject('');

            // remove default header/footer
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);
            $pdf->SetAutoPageBreak(false, 0);


            // add spot colors
            //$pdf->AddSpotColor('Spot Red', 30, 100, 90, 10);        // For Invisible
            //$pdf->AddSpotColor('Spot Dark Green', 100, 50, 80, 45); // clear text on bottom red and in clear text logo

            $pdf->AddPage();   
            $print_serial_no = '-' ;    
            // $print_serial_no = $this->nextPrintSerial();
            //set background image
            $template_img_generate = public_path().'\\'.$subdomain[0].'\backend\canvas\bg_images\\'.$low_res_bg;

            if($previewPdf!=1){
                $pdf->Image($template_img_generate, 0, 0, '210', '297', "JPG", '', 'R', true);
            }
            //$pdf->setPageMark();
            $pdf->setPageMark();
            //$pdfBig->setPageMark();
            //if($previewPdf!=1){            
                $x= 173;
                $y = 39.1;
                $font_size=12;
                if($previewPdf!=1){
                    $str = str_pad($card_serial_no, 7, '0', STR_PAD_LEFT);
                }else{
                    $str = str_pad($preview_serial_no, 7, '0', STR_PAD_LEFT);   
                }
                $strArr = str_split($str);
                $x_org=$x;
                $y_org=$y;
                $font_size_org=$font_size;
                $i =0;
                $j=0;
                $y=$y+4.5;
                $z=0;
                /*foreach ($strArr as $character) {
                    $pdf->SetFont($arialNarrow,0, $font_size, '', false);
                    $pdf->SetXY($x, $y+$z);

                    $pdfBig->SetFont($arialNarrow,0, $font_size, '', false);
                    $pdfBig->SetXY($x, $y+$z);

                    if($i==3){
                        $j=$j+0.2;
                    }else if($i>1){
                        $j=$j+0.1;   
                    }
                   
                   if($i>1){
                       $z=$z+0.1;
                    }
                    if($i>3){
                      $x=$x+0.4;  
                    }else if($i>2){
                      $x=$x+0.2;
                    } 
                   $pdf->Cell(0, 0, $character, 0, $ln=0,  'L', 0, '', 0, false, 'B', 'B');
                   $pdfBig->Cell(0, 0, $character, 0, $ln=0,  'L', 0, '', 0, false, 'B', 'B');
                    $i++;
                    $x=$x+2.2+$j; 
                    if($i>2){
                     $font_size=$font_size+1.7;   
                    }
                } */        
            //}
            
            //$pdf->SetFont($arialNarrowB, '', 9, '', false);
            //$pdfBig->SetFont($arialNarrowB, '', 9, '', false);        
            //images
            if($studentData[$photo_col]!=''){
                //path of photos
                $profile_path_org = public_path().'\\'.$subdomain[0].'\backend\templates\100\\'.$studentData[$photo_col];        
                
                //set profile image   
                $profilex = 170;
                $profiley = 60;
                $profileWidth = 23;
                $profileHeight = 28;
                $pdf->image($profile_path_org,$profilex,$profiley,$profileWidth,$profileHeight,"",'','L',true,3600);
                $pdfBig->image($profile_path_org,$profilex,$profiley,$profileWidth,$profileHeight,"",'','L',true,3600);  
                $pdf->setPageMark();
                $pdfBig->setPageMark(); 
            }
           

            
            $title1_x = 15.5; 
            $title1_colonx = 47.7;        
            $left_title1_y = 51.5;        
            $left_title2_y = 58.7;
            $left_title3_y = 65.8;
            $left_title4_y = 72.7;
            $left_title5_y = 79.7;
            $left_title6_y = 86.7;
            $left_title7_y = 93.7;
            $left_str_x = 29;
            $title2_x = 106;
            $title2_colonx = 142.3;
            $right_str_x = 145;
            $title_font_size = '11';        
            $str_font_size = '11';       
            
            $unique_id = trim($studentData[0]);
            $rollNo = trim($studentData[1]);
            $candidate_name = trim($studentData[2]);
            $candidate_mother_name = trim($studentData[3]);
            $candidate_father_name = trim($studentData[4]);
            $registration_no = trim($studentData[5]);
            $Enrollment_Number = trim($studentData[6]);
            $term = trim($studentData[7]);
            $academic_year = trim($studentData[8]);
            $session = trim($studentData[9]);
            $examination = trim($studentData[10]);
            $programme = trim($studentData[11]);
            $branch = trim($studentData[12]);
            $examRegistrasionCredits = trim($studentData[13]);
            $earnCredit = trim($studentData[14]);
            $GradePointEarned = trim($studentData[15]);
            $sgpa = trim($studentData[16]);
            $cumulativeCredits = trim($studentData[17]);
            $cumulativeEGP = trim($studentData[18]);
            $cgpa = trim($studentData[19]);
            $resultDate = trim($studentData[20]);
            
            $courseCode1 = trim($studentData[21]);
            $courseName1 = trim($studentData[22]);
            $componentName1 = trim($studentData[23]);
            $credits1 = trim($studentData[24]);
            $grades1 = trim($studentData[25]);

            $courseCode2 = trim($studentData[26]);
            $courseName2 = trim($studentData[27]);
            $componentName2 = trim($studentData[28]);
            $credits2 = trim($studentData[29]);
            $grades2 = trim($studentData[30]);

            $courseCode3 = trim($studentData[31]);
            $courseName3 = trim($studentData[32]);
            $componentName3 = trim($studentData[33]);
            $credits3 = trim($studentData[34]);
            $grades3 = trim($studentData[35]);

            $courseCode4 = trim($studentData[36]);
            $courseName4 = trim($studentData[37]);
            $componentName4 = trim($studentData[38]);
            $credits4 = trim($studentData[39]);
            $grades4 = trim($studentData[40]);

            $courseCode5 = trim($studentData[41]);
            $courseName5 = trim($studentData[42]);
            $componentName5 = trim($studentData[43]);
            $credits5 = trim($studentData[44]);
            $grades5 = trim($studentData[45]);

            $courseCode6 = trim($studentData[46]);
            $courseName6 = trim($studentData[47]);
            $componentName6 = trim($studentData[48]);
            $credits6 = trim($studentData[49]);
            $grades6 = trim($studentData[50]);

            $courseCode7 = trim($studentData[51]);
            $courseName7 = trim($studentData[52]);
            $componentName7 = trim($studentData[53]);
            $credits7 = trim($studentData[54]);
            $grades7 = trim($studentData[55]);

            $courseCode8 = trim($studentData[56]);
            $courseName8 = trim($studentData[57]);
            $componentName8 = trim($studentData[58]);
            $credits8 = trim($studentData[59]);
            $grades8 = trim($studentData[60]);


            $courseCode9 = trim($studentData[61]);
            $courseName9 = trim($studentData[62]);
            $componentName9 = trim($studentData[63]);
            $credits9 = trim($studentData[64]);
            $grades9 = trim($studentData[65]);


            $courseCode10 = trim($studentData[66]);
            $courseName10 = trim($studentData[67]);
            $componentName10 = trim($studentData[68]);
            $credits10 = trim($studentData[69]);
            $grades10 = trim($studentData[70]);


            $courseCode11 = trim($studentData[71]);
            $courseName11 = trim($studentData[72]);
            $componentName11 = trim($studentData[73]);
            $credits11 = trim($studentData[74]);
            $grades11 = trim($studentData[75]);




            
            
            //Start pdf    
            // stat invisible data 
            $pdfBig->SetFont($arialb, '', 10, '', false); 
            $pdfBig->SetTextColor(255, 255, 0);        
            $pdfBig->SetXY(14, 35);
            $pdfBig->Cell(0, 0, $candidate_name, 0, false, 'L');

            $pdf->SetFont($arialb, '', 10, '', false); 
            $pdf->SetTextColor(255, 255, 0);        
            $pdf->SetXY(14, 35);
            $pdf->Cell(0, 0, $candidate_name, 0, false, 'L');

            // end invisible data 
            
            $pdfBig->SetFont($HankenGroteskLight, '', 12, '', false);
            $pdfBig->SetTextColor(0, 0, 0);
            $pdfBig->SetXY(173, 33);
            $pdfBig->MultiCell(23, 6, $unique_id,'', 'C', 0, 0);

            $pdfBig->setCellPaddings( $left = '3', $top = '0.5', $right = '', $bottom = '');
            $pdf->setCellPaddings( $left = '3', $top = '0.5', $right = '', $bottom = '');
            $pdfBig->SetFont($HankenGroteskLight, '', 11, '', false);
            $pdfBig->SetTextColor(0, 0, 0);
            $pdfBig->SetXY(14, 40);
            $pdfBig->MultiCell(182, 6, "Student's Name : ",'LRT', 'L', 0, 0);

            $pdfBig->SetXY(45, 40);
            $pdfBig->MultiCell(142, 6, $candidate_name,'', 'L', 0, 0);

            $pdf->SetFont($HankenGroteskLight, '', 11, '', false);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetXY(14, 40);
            $pdf->MultiCell(182, 6, "Student's Name : ",'LRT', 'L', 0, 0);

            $pdf->SetXY(45, 40);
            $pdf->MultiCell(142, 6, $candidate_name,'', 'L', 0, 0);



            $pdfBig->SetFont($HankenGroteskLight, '', 11, '', false);
            $pdfBig->SetTextColor(0, 0, 0);
            $pdfBig->SetXY(14, 46);
            $pdfBig->MultiCell(182, 6, "Mother's Name : ",'LRT', 'L', 0, 0);

            $pdfBig->SetXY(45, 46);
            $pdfBig->MultiCell(142, 6, $candidate_mother_name,'', 'L', 0, 0);

            $pdf->SetFont($HankenGroteskLight, '', 11, '', false);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetXY(14, 46);
            $pdf->MultiCell(182, 6, "Mother's Name : ",'LRT', 'L', 0, 0);

            $pdf->SetXY(45, 46);
            $pdf->MultiCell(142, 6, $candidate_mother_name,'', 'L', 0, 0);



            $pdfBig->SetFont($HankenGroteskLight, '', 11, '', false);
            $pdfBig->SetTextColor(0, 0, 0);
            $pdfBig->SetXY(14, 52);
            $pdfBig->MultiCell(182, 6, "Registration No. : ",'LRT', 'L', 0, 0);

            $pdfBig->SetXY(45, 52);
            $pdfBig->MultiCell(142, 6, $registration_no,'', 'L', 0, 0);

            $pdf->SetFont($HankenGroteskLight, '', 11, '', false);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetXY(14, 52);
            $pdf->MultiCell(182, 6, "Registration No. : ",'LRT', 'L', 0, 0);

            $pdf->SetXY(45, 52);
            $pdf->MultiCell(142, 6, $registration_no,'', 'L', 0, 0);




            $pdfBig->SetFont($HankenGroteskLight, '', 11, '', false);
            $pdfBig->SetTextColor(0, 0, 0);
            $pdfBig->SetXY(14, 58);
            $pdfBig->MultiCell(182, 6, "Term. : ",'LRTB', 'L', 0, 0);

            $pdfBig->SetXY(30, 58);
            $pdfBig->MultiCell(20, 6, $term,'', 'L', 0, 0);

            $pdf->SetFont($HankenGroteskLight, '', 11, '', false);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetXY(14, 58);
            $pdf->MultiCell(182, 6, "Term. : ",'LRTB', 'L', 0, 0);

            $pdf->SetXY(30, 58);
            $pdf->MultiCell(20, 6, $term,'', 'L', 0, 0);




            $pdfBig->SetFont($HankenGroteskLight, '', 11, '', false);
            $pdfBig->SetTextColor(0, 0, 0);
            $pdfBig->SetXY(101, 58);
            $pdfBig->MultiCell(182, 6, "Examination : ",'', 'L', 0, 0);

            $pdfBig->SetXY(125, 58);
            $pdfBig->MultiCell(70, 6, $examination,'', 'L', 0, 0);

            $pdf->SetFont($HankenGroteskLight, '', 11, '', false);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetXY(101, 58);
            $pdf->MultiCell(182, 6, "Examination : ",'', 'L', 0, 0);

            $pdf->SetXY(125, 58);
            $pdf->MultiCell(70, 6, $examination,'', 'L', 0, 0);


            $pdfBig->SetFont($HankenGroteskLight, '', 11, '', false);
            $pdfBig->SetTextColor(0, 0, 0);
            $pdfBig->SetXY(14, 64);
            $pdfBig->MultiCell(40, 6, "Programme : ",'', 'L', 0, 0);

            $pdfBig->SetXY(37, 64);
            $pdfBig->MultiCell(155, 6, $programme,'', 'L', 0, 0);

            $pdf->SetFont($HankenGroteskLight, '', 11, '', false);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetXY(14, 64);
            $pdf->MultiCell(40, 6, "Programme : ",'', 'L', 0, 0);

            $pdf->SetXY(37, 64);
            $pdf->MultiCell(155, 6, $programme,'', 'L', 0, 0);


            //  start table data
            $pdfBig->SetFont($arial, 'B', 10, '', false);
            $pdfBig->MultiCell($w=29, $h=10, $txt="Course\nCode", $border='TLB', $align='C', $fill=0, 1, $x=14, $y=73, $reseth=true, $strech=0, $ishtml=false, $autopadding=true, $maxh=10, $valign='M');
            
            $pdfBig->MultiCell($w=110, $h=10, $txt="Course Name", $border='TLB', $align='C', $fill=0, 1, $x=43, $y=73, $reseth=true, $strech=0, $ishtml=false, $autopadding=true, $maxh=10, $valign='M');

            $pdfBig->MultiCell($w=21, $h=10, $txt="Credits", $border='TLB', $align='C', $fill=0, 1, $x=153, $y=73, $reseth=true, $strech=0, $ishtml=false, $autopadding=true, $maxh=10, $valign='M');

            $pdfBig->MultiCell($w=22, $h=10, $txt="Grades", $border='TLBR', $align='C', $fill=0, 1, $x=174, $y=73, $reseth=true, $strech=0, $ishtml=false, $autopadding=true, $maxh=10, $valign='M');

            $pdf->SetFont($arial, 'B', 10, '', false);
            $pdf->MultiCell($w=29, $h=10, $txt="Course\nCode", $border='TLB', $align='C', $fill=0, 1, $x=14, $y=73, $reseth=true, $strech=0, $ishtml=false, $autopadding=true, $maxh=10, $valign='M');
            
            $pdf->MultiCell($w=110, $h=10, $txt="Course Name", $border='TLB', $align='C', $fill=0, 1, $x=43, $y=73, $reseth=true, $strech=0, $ishtml=false, $autopadding=true, $maxh=10, $valign='M');

            $pdf->MultiCell($w=21, $h=10, $txt="Credits", $border='TLB', $align='C', $fill=0, 1, $x=153, $y=73, $reseth=true, $strech=0, $ishtml=false, $autopadding=true, $maxh=10, $valign='M');

            $pdf->MultiCell($w=22, $h=10, $txt="Grades", $border='TLBR', $align='C', $fill=0, 1, $x=174, $y=73, $reseth=true, $strech=0, $ishtml=false, $autopadding=true, $maxh=10, $valign='M');
                    

            $pdfBig->SetXY(14, 83);
            $pdfBig->Cell(29, 137, "", 'LB', $ln=0, 'C', 0, '', 0, false, '', 'C');

            $pdfBig->SetXY(43, 83);
            $pdfBig->Cell(110, 137, "", 'LB', $ln=0, 'C', 0, '', 0, false, '', 'C');
            
            $pdfBig->SetXY(153, 83);
            $pdfBig->Cell(21, 137, "", 'LB', $ln=0, 'C', 0, '', 0, false, '', 'C');

            $pdfBig->SetXY(174, 83);
            $pdfBig->Cell(22, 137, "", 'LRB', $ln=0, 'C', 0, '', 0, false, '', 'C');

            $pdf->SetXY(14, 83);
            $pdf->Cell(29, 137, "", 'LB', $ln=0, 'C', 0, '', 0, false, '', 'C');

            $pdf->SetXY(43, 83);
            $pdf->Cell(110, 137, "", 'LB', $ln=0, 'C', 0, '', 0, false, '', 'C');
            
            $pdf->SetXY(153, 83);
            $pdf->Cell(21, 137, "", 'LB', $ln=0, 'C', 0, '', 0, false, '', 'C');

            $pdf->SetXY(174, 83);
            $pdf->Cell(22, 137, "", 'LRB', $ln=0, 'C', 0, '', 0, false, '', 'C');


            $tableDataY = 86;
            $pdfBig->SetXY(14, $tableDataY);
            $pdfBig->Cell(29, 0, $courseCode1, '', $ln=0, 'C', 0, '', 0, false, '', 'C');

            $pdfBig->SetXY(43, $tableDataY);
            $pdfBig->Cell(110, 0, $courseName1, '', $ln=0, 'L', 0, '', 0, false, '', 'C');

            $pdfBig->SetXY(153, $tableDataY);
            $pdfBig->Cell(21, 0, $credits1, '', $ln=0, 'C', 0, '', 0, false, '', 'C');

            $pdfBig->SetXY(174, $tableDataY);
            $pdfBig->Cell(22, 0, $grades1, '', $ln=0, 'C', 0, '', 0, false, '', 'C');


            $pdf->SetXY(14, $tableDataY);
            $pdf->Cell(29, 0, $courseCode1, '', $ln=0, 'C', 0, '', 0, false, '', 'C');

            $pdf->SetXY(43, $tableDataY);
            $pdf->Cell(110, 0, $courseName1, '', $ln=0, 'L', 0, '', 0, false, '', 'C');

            $pdf->SetXY(153, $tableDataY);
            $pdf->Cell(21, 0, $credits1, '', $ln=0, 'C', 0, '', 0, false, '', 'C');

            $pdf->SetXY(174, $tableDataY);
            $pdf->Cell(22, 0, $grades1, '', $ln=0, 'C', 0, '', 0, false, '', 'C');

            $tableDataY = $tableDataY+5;
            $pdfBig->SetXY(14, $tableDataY);
            $pdfBig->Cell(29, 0, $courseCode2, '', $ln=0, 'C', 0, '', 0, false, '', 'C');

            $pdfBig->SetXY(43, $tableDataY);
            $pdfBig->Cell(110, 0, $courseName2, '', $ln=0, 'L', 0, '', 0, false, '', 'C');

            $pdfBig->SetXY(153, $tableDataY);
            $pdfBig->Cell(21, 0, $credits2, '', $ln=0, 'C', 0, '', 0, false, '', 'C');

            $pdfBig->SetXY(174, $tableDataY);
            $pdfBig->Cell(22, 0, $grades2, '', $ln=0, 'C', 0, '', 0, false, '', 'C');

            $pdf->SetXY(14, $tableDataY);
            $pdf->Cell(29, 0, $courseCode2, '', $ln=0, 'C', 0, '', 0, false, '', 'C');

            $pdf->SetXY(43, $tableDataY);
            $pdf->Cell(110, 0, $courseName2, '', $ln=0, 'L', 0, '', 0, false, '', 'C');

            $pdf->SetXY(153, $tableDataY);
            $pdf->Cell(21, 0, $credits2, '', $ln=0, 'C', 0, '', 0, false, '', 'C');

            $pdf->SetXY(174, $tableDataY);
            $pdf->Cell(22, 0, $grades2, '', $ln=0, 'C', 0, '', 0, false, '', 'C');


            $tableDataY = $tableDataY+5;
            $pdfBig->SetXY(14, $tableDataY);
            $pdfBig->Cell(29, 0, $courseCode3, '', $ln=0, 'C', 0, '', 0, false, '', 'C');

            $pdfBig->SetXY(43, $tableDataY);
            $pdfBig->Cell(110, 0, $courseName3, '', $ln=0, 'L', 0, '', 0, false, '', 'C');

            $pdfBig->SetXY(153, $tableDataY);
            $pdfBig->Cell(21, 0, $credits3, '', $ln=0, 'C', 0, '', 0, false, '', 'C');

            $pdfBig->SetXY(174, $tableDataY);
            $pdfBig->Cell(22, 0, $grades3, '', $ln=0, 'C', 0, '', 0, false, '', 'C');

            $pdf->SetXY(14, $tableDataY);
            $pdf->Cell(29, 0, $courseCode3, '', $ln=0, 'C', 0, '', 0, false, '', 'C');

            $pdf->SetXY(43, $tableDataY);
            $pdf->Cell(110, 0, $courseName3, '', $ln=0, 'L', 0, '', 0, false, '', 'C');

            $pdf->SetXY(153, $tableDataY);
            $pdf->Cell(21, 0, $credits3, '', $ln=0, 'C', 0, '', 0, false, '', 'C');

            $pdf->SetXY(174, $tableDataY);
            $pdf->Cell(22, 0, $grades3, '', $ln=0, 'C', 0, '', 0, false, '', 'C');


            $tableDataY = $tableDataY+5;
            $pdfBig->SetXY(14, $tableDataY);
            $pdfBig->Cell(29, 0, $courseCode4, '', $ln=0, 'C', 0, '', 0, false, '', 'C');

            $pdfBig->SetXY(43, $tableDataY);
            $pdfBig->Cell(110, 0, $courseName4, '', $ln=0, 'L', 0, '', 0, false, '', 'C');

            $pdfBig->SetXY(153, $tableDataY);
            $pdfBig->Cell(21, 0, $credits4, '', $ln=0, 'C', 0, '', 0, false, '', 'C');

            $pdfBig->SetXY(174, $tableDataY);
            $pdfBig->Cell(22, 0, $grades4, '', $ln=0, 'C', 0, '', 0, false, '', 'C');

            $pdf->SetXY(14, $tableDataY);
            $pdf->Cell(29, 0, $courseCode4, '', $ln=0, 'C', 0, '', 0, false, '', 'C');

            $pdf->SetXY(43, $tableDataY);
            $pdf->Cell(110, 0, $courseName4, '', $ln=0, 'L', 0, '', 0, false, '', 'C');

            $pdf->SetXY(153, $tableDataY);
            $pdf->Cell(21, 0, $credits4, '', $ln=0, 'C', 0, '', 0, false, '', 'C');

            $pdf->SetXY(174, $tableDataY);
            $pdf->Cell(22, 0, $grades4, '', $ln=0, 'C', 0, '', 0, false, '', 'C');


            $tableDataY =$tableDataY +5;
            $pdfBig->SetXY(14, $tableDataY);
            $pdfBig->Cell(29, 0, $courseCode5, '', $ln=0, 'C', 0, '', 0, false, '', 'C');

            $pdfBig->SetXY(43, $tableDataY);
            $pdfBig->Cell(110, 0, $courseName5, '', $ln=0, 'L', 0, '', 0, false, '', 'C');

            $pdfBig->SetXY(153, $tableDataY);
            $pdfBig->Cell(21, 0, $credits5, '', $ln=0, 'C', 0, '', 0, false, '', 'C');

            $pdfBig->SetXY(174, $tableDataY);
            $pdfBig->Cell(22, 0, $grades5, '', $ln=0, 'C', 0, '', 0, false, '', 'C');

            $pdf->SetXY(14, $tableDataY);
            $pdf->Cell(29, 0, $courseCode5, '', $ln=0, 'C', 0, '', 0, false, '', 'C');

            $pdf->SetXY(43, $tableDataY);
            $pdf->Cell(110, 0, $courseName5, '', $ln=0, 'L', 0, '', 0, false, '', 'C');

            $pdf->SetXY(153, $tableDataY);
            $pdf->Cell(21, 0, $credits5, '', $ln=0, 'C', 0, '', 0, false, '', 'C');

            $pdf->SetXY(174, $tableDataY);
            $pdf->Cell(22, 0, $grades5, '', $ln=0, 'C', 0, '', 0, false, '', 'C');


            $tableDataY = $tableDataY+5;
            $pdfBig->SetXY(14, $tableDataY);
            $pdfBig->Cell(29, 0, $courseCode6, '', $ln=0, 'C', 0, '', 0, false, '', 'C');

            $pdfBig->SetXY(43, $tableDataY);
            $pdfBig->Cell(110, 0, $courseName6, '', $ln=0, 'L', 0, '', 0, false, '', 'C');

            $pdfBig->SetXY(153, $tableDataY);
            $pdfBig->Cell(21, 0, $credits6, '', $ln=0, 'C', 0, '', 0, false, '', 'C');

            $pdfBig->SetXY(174, $tableDataY);
            $pdfBig->Cell(22, 0, $grades6, '', $ln=0, 'C', 0, '', 0, false, '', 'C');

            $pdf->SetXY(14, $tableDataY);
            $pdf->Cell(29, 0, $courseCode6, '', $ln=0, 'C', 0, '', 0, false, '', 'C');

            $pdf->SetXY(43, $tableDataY);
            $pdf->Cell(110, 0, $courseName6, '', $ln=0, 'L', 0, '', 0, false, '', 'C');

            $pdf->SetXY(153, $tableDataY);
            $pdf->Cell(21, 0, $credits6, '', $ln=0, 'C', 0, '', 0, false, '', 'C');

            $pdf->SetXY(174, $tableDataY);
            $pdf->Cell(22, 0, $grades6, '', $ln=0, 'C', 0, '', 0, false, '', 'C');


            $tableDataY = $tableDataY+5;
            $pdfBig->SetXY(14, $tableDataY);
            $pdfBig->Cell(29, 0, $courseCode7, '', $ln=0, 'C', 0, '', 0, false, '', 'C');

            $pdfBig->SetXY(43, $tableDataY);
            $pdfBig->Cell(110, 0, $courseName7, '', $ln=0, 'L', 0, '', 0, false, '', 'C');

            $pdfBig->SetXY(153, $tableDataY);
            $pdfBig->Cell(21, 0, $credits7, '', $ln=0, 'C', 0, '', 0, false, '', 'C');

            $pdfBig->SetXY(174, $tableDataY);
            $pdfBig->Cell(22, 0, $grades7, '', $ln=0, 'C', 0, '', 0, false, '', 'C');

            $pdf->SetXY(14, $tableDataY);
            $pdf->Cell(29, 0, $courseCode7, '', $ln=0, 'C', 0, '', 0, false, '', 'C');

            $pdf->SetXY(43, $tableDataY);
            $pdf->Cell(110, 0, $courseName7, '', $ln=0, 'L', 0, '', 0, false, '', 'C');

            $pdf->SetXY(153, $tableDataY);
            $pdf->Cell(21, 0, $credits7, '', $ln=0, 'C', 0, '', 0, false, '', 'C');

            $pdf->SetXY(174, $tableDataY);
            $pdf->Cell(22, 0, $grades7, '', $ln=0, 'C', 0, '', 0, false, '', 'C');


            $tableDataY = $tableDataY+5;
            $pdfBig->SetXY(14, $tableDataY);
            $pdfBig->Cell(29, 0, $courseCode8, '', $ln=0, 'C', 0, '', 0, false, '', 'C');

            $pdfBig->SetXY(43, $tableDataY);
            $pdfBig->Cell(110, 0, $courseName8, '', $ln=0, 'L', 0, '', 0, false, '', 'C');

            $pdfBig->SetXY(153, $tableDataY);
            $pdfBig->Cell(21, 0, $credits8, '', $ln=0, 'C', 0, '', 0, false, '', 'C');

            $pdfBig->SetXY(174, $tableDataY);
            $pdfBig->Cell(22, 0, $grades8, '', $ln=0, 'C', 0, '', 0, false, '', 'C');

            $pdf->SetXY(14, $tableDataY);
            $pdf->Cell(29, 0, $courseCode8, '', $ln=0, 'C', 0, '', 0, false, '', 'C');

            $pdf->SetXY(43, $tableDataY);
            $pdf->Cell(110, 0, $courseName8, '', $ln=0, 'L', 0, '', 0, false, '', 'C');

            $pdf->SetXY(153, $tableDataY);
            $pdf->Cell(21, 0, $credits8, '', $ln=0, 'C', 0, '', 0, false, '', 'C');

            $pdf->SetXY(174, $tableDataY);
            $pdf->Cell(22, 0, $grades8, '', $ln=0, 'C', 0, '', 0, false, '', 'C');


            $tableDataY = $tableDataY+5; 
            $pdfBig->SetXY(14, $tableDataY);
            $pdfBig->Cell(29, 0, $courseCode9, '', $ln=0, 'C', 0, '', 0, false, '', 'C');

            $pdfBig->SetXY(43, $tableDataY);
            $pdfBig->Cell(110, 0, $courseName9, '', $ln=0, 'L', 0, '', 0, false, '', 'C');

            $pdfBig->SetXY(153, $tableDataY);
            $pdfBig->Cell(21, 0, $credits9, '', $ln=0, 'C', 0, '', 0, false, '', 'C');

            $pdfBig->SetXY(174, $tableDataY);
            $pdfBig->Cell(22, 0, $grades9, '', $ln=0, 'C', 0, '', 0, false, '', 'C');

            $pdf->SetXY(14, $tableDataY);
            $pdf->Cell(29, 0, $courseCode9, '', $ln=0, 'C', 0, '', 0, false, '', 'C');

            $pdf->SetXY(43, $tableDataY);
            $pdf->Cell(110, 0, $courseName9, '', $ln=0, 'L', 0, '', 0, false, '', 'C');

            $pdf->SetXY(153, $tableDataY);
            $pdf->Cell(21, 0, $credits9, '', $ln=0, 'C', 0, '', 0, false, '', 'C');

            $pdf->SetXY(174, $tableDataY);
            $pdf->Cell(22, 0, $grades9, '', $ln=0, 'C', 0, '', 0, false, '', 'C');


            $tableDataY = $tableDataY+5;
            $pdfBig->SetXY(14, $tableDataY);
            $pdfBig->Cell(29, 0, $courseCode10, '', $ln=0, 'C', 0, '', 0, false, '', 'C');

            $pdfBig->SetXY(43, $tableDataY);
            $pdfBig->Cell(110, 0, $courseName10, '', $ln=0, 'L', 0, '', 0, false, '', 'C');

            $pdfBig->SetXY(153, $tableDataY);
            $pdfBig->Cell(21, 0, $credits10, '', $ln=0, 'C', 0, '', 0, false, '', 'C');

            $pdfBig->SetXY(174, $tableDataY);
            $pdfBig->Cell(22, 0, $grades10, '', $ln=0, 'C', 0, '', 0, false, '', 'C');

            $pdf->SetXY(14, $tableDataY);
            $pdf->Cell(29, 0, $courseCode10, '', $ln=0, 'C', 0, '', 0, false, '', 'C');

            $pdf->SetXY(43, $tableDataY);
            $pdf->Cell(110, 0, $courseName10, '', $ln=0, 'L', 0, '', 0, false, '', 'C');

            $pdf->SetXY(153, $tableDataY);
            $pdf->Cell(21, 0, $credits10, '', $ln=0, 'C', 0, '', 0, false, '', 'C');

            $pdf->SetXY(174, $tableDataY);
            $pdf->Cell(22, 0, $grades10, '', $ln=0, 'C', 0, '', 0, false, '', 'C');
            
            //  end table data



            // second table
            $pdfBig->SetFont($arial, 'B', 9.5, '', false);
            $pdfBig->MultiCell($w=25, $h=25, $txt="Semester Grade Point Average (SGPA)", $border='TLB', $align='C', $fill=0, 1, $x=14, $y=223, $reseth=true, $strech=0, $ishtml=false, $autopadding=true, $maxh=25, $valign='M');

            $pdfBig->MultiCell($w=24, $h=14, $txt="Exam \nRegistration\n(Credits)", $border='TLB', $align='C', $fill=0, 1, $x=39, $y=223, $reseth=true, $strech=0, $ishtml=false, $autopadding=true, $maxh=14, $valign='M');

            $pdfBig->MultiCell($w=24, $h=11, $txt=$examRegistrasionCredits, $border='TLB', $align='C', $fill=0, 1, $x=39, $y=237, $reseth=true, $strech=0, $ishtml=false, $autopadding=true, $maxh=11, $valign='M');


            $pdfBig->MultiCell($w=16, $h=14, $txt="Earn \nCredits", $border='TLB', $align='C', $fill=0, 1, $x=63, $y=223, $reseth=true, $strech=0, $ishtml=false, $autopadding=true, $maxh=14, $valign='M');

            $pdfBig->MultiCell($w=16, $h=11, $txt=$earnCredit, $border='TLB', $align='C', $fill=0, 1, $x=63, $y=237, $reseth=true, $strech=0, $ishtml=false, $autopadding=true, $maxh=11, $valign='M');


            $pdfBig->MultiCell($w=17, $h=14, $txt="Earn\nGrade\nPoints", $border='TLB', $align='C', $fill=0, 1, $x=79, $y=223, $reseth=true, $strech=0, $ishtml=false, $autopadding=true, $maxh=14, $valign='M');

            $pdfBig->MultiCell($w=17, $h=11, $txt=$GradePointEarned, $border='TLB', $align='C', $fill=0, 1, $x=79, $y=237, $reseth=true, $strech=0, $ishtml=false, $autopadding=true, $maxh=11, $valign='M');


            $pdfBig->MultiCell($w=17, $h=14, $txt="SGPA", $border='TLB', $align='C', $fill=0, 1, $x=96, $y=223, $reseth=true, $strech=0, $ishtml=false, $autopadding=true, $maxh=14, $valign='M');

            $pdfBig->MultiCell($w=17, $h=11, $txt=$sgpa, $border='TLB', $align='C', $fill=0, 1, $x=96, $y=237, $reseth=true, $strech=0, $ishtml=false, $autopadding=true, $maxh=11, $valign='M');

            $pdfBig->MultiCell($w=24, $h=25, $txt="Cumulative Grade Point Average (CGPA)", $border='TLB', $align='C', $fill=0, 1, $x=113, $y=223, $reseth=true, $strech=0, $ishtml=false, $autopadding=true, $maxh=25, $valign='M');

            $pdfBig->MultiCell($w=26, $h=14, $txt="Cumulative\nCredits\nEarned", $border='TLB', $align='C', $fill=0, 1, $x=137, $y=223, $reseth=true, $strech=0, $ishtml=false, $autopadding=true, $maxh=14, $valign='M');

            $pdfBig->MultiCell($w=26, $h=11, $txt=$cumulativeCredits, $border='TLB', $align='C', $fill=0, 1, $x=137, $y=237, $reseth=true, $strech=0, $ishtml=false, $autopadding=true, $maxh=11, $valign='M');

            $pdfBig->MultiCell($w=16, $h=14, $txt="Earned\nGrade\nPoints", $border='TLB', $align='C', $fill=0, 1, $x=163, $y=223, $reseth=true, $strech=0, $ishtml=false, $autopadding=true, $maxh=14, $valign='M');

            $pdfBig->MultiCell($w=16, $h=11, $txt=$cumulativeEGP, $border='TLB', $align='C', $fill=0, 1, $x=163, $y=237, $reseth=true, $strech=0, $ishtml=false, $autopadding=true, $maxh=11, $valign='M');

            $pdfBig->MultiCell($w=17, $h=14, $txt="CGPA", $border='TLBR', $align='C', $fill=0, 1, $x=179, $y=223, $reseth=true, $strech=0, $ishtml=false, $autopadding=true, $maxh=14, $valign='M');

            $pdfBig->MultiCell($w=17, $h=11, $txt=$cgpa, $border='TLBR', $align='C', $fill=0, 1, $x=179, $y=237, $reseth=true, $strech=0, $ishtml=false, $autopadding=true, $maxh=11, $valign='M');




            $pdf->SetFont($arial, 'B', 9.5, '', false);
            $pdf->MultiCell($w=25, $h=25, $txt="Semester Grade Point Average (SGPA)", $border='TLB', $align='C', $fill=0, 1, $x=14, $y=223, $reseth=true, $strech=0, $ishtml=false, $autopadding=true, $maxh=25, $valign='M');

            $pdf->MultiCell($w=24, $h=14, $txt="Exam \nRegistration\n(Credits)", $border='TLB', $align='C', $fill=0, 1, $x=39, $y=223, $reseth=true, $strech=0, $ishtml=false, $autopadding=true, $maxh=14, $valign='M');

            $pdf->MultiCell($w=24, $h=11, $txt=$examRegistrasionCredits, $border='TLB', $align='C', $fill=0, 1, $x=39, $y=237, $reseth=true, $strech=0, $ishtml=false, $autopadding=true, $maxh=11, $valign='M');


            $pdf->MultiCell($w=16, $h=14, $txt="Earn \nCredits", $border='TLB', $align='C', $fill=0, 1, $x=63, $y=223, $reseth=true, $strech=0, $ishtml=false, $autopadding=true, $maxh=14, $valign='M');

            $pdf->MultiCell($w=16, $h=11, $txt=$earnCredit, $border='TLB', $align='C', $fill=0, 1, $x=63, $y=237, $reseth=true, $strech=0, $ishtml=false, $autopadding=true, $maxh=11, $valign='M');


            $pdf->MultiCell($w=17, $h=14, $txt="Earn\nGrade\nPoints", $border='TLB', $align='C', $fill=0, 1, $x=79, $y=223, $reseth=true, $strech=0, $ishtml=false, $autopadding=true, $maxh=14, $valign='M');

            $pdf->MultiCell($w=17, $h=11, $txt=$GradePointEarned, $border='TLB', $align='C', $fill=0, 1, $x=79, $y=237, $reseth=true, $strech=0, $ishtml=false, $autopadding=true, $maxh=11, $valign='M');


            $pdf->MultiCell($w=17, $h=14, $txt="SGPA", $border='TLB', $align='C', $fill=0, 1, $x=96, $y=223, $reseth=true, $strech=0, $ishtml=false, $autopadding=true, $maxh=14, $valign='M');

            $pdf->MultiCell($w=17, $h=11, $txt=$sgpa, $border='TLB', $align='C', $fill=0, 1, $x=96, $y=237, $reseth=true, $strech=0, $ishtml=false, $autopadding=true, $maxh=11, $valign='M');

            $pdf->MultiCell($w=24, $h=25, $txt="Cumulative Grade Point Average (CGPA)", $border='TLB', $align='C', $fill=0, 1, $x=113, $y=223, $reseth=true, $strech=0, $ishtml=false, $autopadding=true, $maxh=25, $valign='M');

            $pdf->MultiCell($w=26, $h=14, $txt="Cumulative\nCredits\nEarned", $border='TLB', $align='C', $fill=0, 1, $x=137, $y=223, $reseth=true, $strech=0, $ishtml=false, $autopadding=true, $maxh=14, $valign='M');

            $pdf->MultiCell($w=26, $h=11, $txt=$cumulativeCredits, $border='TLB', $align='C', $fill=0, 1, $x=137, $y=237, $reseth=true, $strech=0, $ishtml=false, $autopadding=true, $maxh=11, $valign='M');

            $pdf->MultiCell($w=16, $h=14, $txt="Earned\nGrade\nPoints", $border='TLB', $align='C', $fill=0, 1, $x=163, $y=223, $reseth=true, $strech=0, $ishtml=false, $autopadding=true, $maxh=14, $valign='M');

            $pdf->MultiCell($w=16, $h=11, $txt=$cumulativeEGP, $border='TLB', $align='C', $fill=0, 1, $x=163, $y=237, $reseth=true, $strech=0, $ishtml=false, $autopadding=true, $maxh=11, $valign='M');

            $pdf->MultiCell($w=17, $h=14, $txt="CGPA", $border='TLBR', $align='C', $fill=0, 1, $x=179, $y=223, $reseth=true, $strech=0, $ishtml=false, $autopadding=true, $maxh=14, $valign='M');

            $pdf->MultiCell($w=17, $h=11, $txt=$cgpa, $border='TLBR', $align='C', $fill=0, 1, $x=179, $y=237, $reseth=true, $strech=0, $ishtml=false, $autopadding=true, $maxh=11, $valign='M');
            
            // second table data
            
            
            
            //end pdfBig
            // Ghost image
            $nameOrg=$studentData[1];
            /*$ghost_font_size = '13';
            $ghostImagex = 70;
            $ghostImagey = 269.5;
            $ghostImageWidth = 55;//68
            $ghostImageHeight = 9.8;
            $name = substr(str_replace(' ','',strtoupper($nameOrg)), 0, 6);
            $tmpDir = $this->createTemp(public_path().'\backend\images\ghosttemp\temp');
            $pdf->Image("$tmpDir/" . $name."".$ghost_font_size.".png", $ghostImagex, $ghostImagey, $ghostImageWidth, $ghostImageHeight, "PNG", '', 'L', true, 3600);            
            $pdfBig->Image("$tmpDir/" . $name."".$ghost_font_size.".png", $ghostImagex, $ghostImagey, $ghostImageWidth, $ghostImageHeight, "PNG", '', 'L', true, 3600);*/
            $ghost_font_size = '12';
            $ghostImagex = 70;
            $ghostImagey = 269.5;
            $ghostImageWidth = 39.405983333;
            $ghostImageHeight = 8;
            $name = substr(str_replace(' ','',strtoupper($nameOrg)), 0, 6);
            // $tmpDir = $this->createTemp(public_path().'\backend\images\ghosttemp\temp');
            // $w = $this->CreateMessage($tmpDir, $name ,$ghost_font_size,'');
            // $pdf->Image("$tmpDir/" . $name."".$ghost_font_size.".png", $ghostImagex, $ghostImagey, $w, $ghostImageHeight, "PNG", '', 'L', true, 3600);
            // $pdfBig->Image("$tmpDir/" . $name."".$ghost_font_size.".png", $ghostImagex, $ghostImagey, $w, $ghostImageHeight, "PNG", '', 'L', true, 3600);
            $pdf->setPageMark();
            $pdfBig->setPageMark();
            $serial_no=$GUID=$studentData[0];
            //qr code    
            $dt = date("_ymdHis");
            $str=$GUID.$dt;
            $codeContents =$encryptedString = strtoupper(md5($str));
            $qr_code_path = public_path().'\\'.$subdomain[0].'\backend\canvas\images\qr\/'.$encryptedString.'.png';
            $qrCodex = 15.5; //14.5;
            $qrCodey = 261;
            $qrCodeWidth =19;
            $qrCodeHeight = 19;
            /*\QrCode::backgroundColor(255, 255, 0)            
                ->format('png')        
                ->size(500)    
                ->generate($codeContents, $qr_code_path);*/
            $ecc = 'L';
            $pixel_Size = 1;
            $frame_Size = 1;  
            // \PHPQRCode\QRcode::png($codeContents, $qr_code_path, $ecc, $pixel_Size, $frame_Size);
            
            $qr_code_path = public_path().'\\'.$subdomain[0].'\backend\canvas\images\qr\/'.$encryptedString.'.png';
            $qrCodex = 14;
            $qrCodey = 263;
            $qrCodeWidth =16;
            $qrCodeHeight = 16;
            \QrCode::size(75.5)
                ->backgroundColor(255, 255, 0)
                ->format('png')
                ->generate($codeContents, $qr_code_path);

            $pdf->Image($qr_code_path, $qrCodex, $qrCodey, $qrCodeWidth,  $qrCodeHeight, 'png', '', true, false);   
            $pdf->setPageMark(); 
            $pdfBig->Image($qr_code_path, $qrCodex, $qrCodey, $qrCodeWidth,  $qrCodeHeight, 'png', '', true, false); 
            $pdfBig->setPageMark();             
            
            $COE = public_path().'\\'.$subdomain[0].'\backend\canvas\images\COE.png';
            $COE_x = 112;
            $COE_y = 243;
            $COE_Width = 31.75;
            $COE_Height = 11.1125;
            $pdfBig->image($COE,$COE_x,$COE_y,$COE_Width,$COE_Height,"",'','L',true,3600);
            $pdfBig->setPageMark(); 

            $Principal = public_path().'\\'.$subdomain[0].'\backend\canvas\images\Principal.png';
            $Principal_x = 158;
            $Principal_y = 244;
            $Principal_Width = 27.78125;
            $Principal_Height = 9.525;
            $pdfBig->image($Principal,$Principal_x,$Principal_y,$Principal_Width,$Principal_Height,"",'','L',true,3600);
            $pdfBig->setPageMark(); 
            
            //1D Barcode
            $style1Da = array(
                'position' => '',
                'align' => 'C',
                'stretch' => true,
                'fitwidth' => true,
                'cellfitalign' => '',
                'border' => false,
                'hpadding' => 'auto',
                'vpadding' => 'auto',
                'fgcolor' => array(0,0,0),
                'bgcolor' => false, //array(255,255,255),
                'text' => true,
                'font' => 'helvetica',
                'fontsize' => 9,
                'stretchtext' => 7
            ); 
            
            $barcodex = 37;
            $barcodey = 267;
            $barcodeWidth = 50;
            $barodeHeight = 14;
            $pdf->SetAlpha(1);
            $pdf->write1DBarcode(trim($print_serial_no), 'C39', $barcodex, $barcodey, $barcodeWidth, $barodeHeight, 0.4, $style1Da, 'N');
            $pdfBig->SetAlpha(1);
            $pdfBig->write1DBarcode(trim($print_serial_no), 'C39', $barcodex, $barcodey, $barcodeWidth, $barodeHeight, 0.4, $style1Da, 'N');
            //$pdfBig->SetFont($arial, '', 9, '', false);
            //$pdfBig->SetXY(142, 275);
            //$pdfBig->MultiCell(0, 0, trim($print_serial_no), 0, 'L', 0, 0);
            // $pdf->write1DBarcode(trim($print_serial_no), 'C39', $barcodex, $barcodey, $barcodeWidth, $barodeHeight, 0.4, $style1Da, 'N');
            // $pdfBig->write1DBarcode(trim($print_serial_no), 'C39', $barcodex, $barcodey, $barcodeWidth, $barodeHeight, 0.4, $style1Da, 'N');
            
            $str = $candidate_name;
            $str = strtoupper(preg_replace('/\s+/', '', $str)); 
            
            $microlinestr=$str;
            $pdf->SetFont($arialb, '', 1.3, '', false);
            $pdf->SetTextColor(0, 0, 0);
            //$pdf->StartTransform();
            $pdf->SetXY(15, 278);        
            $pdf->Cell(0, 0, $microlinestr, 0, false, 'L');    
            
            $pdfBig->SetFont($arialb, '', 1.3, '', false);
            $pdfBig->SetTextColor(0, 0, 0);
            //$pdfBig->StartTransform();
            $pdfBig->SetXY(15, 278);        
            $pdfBig->Cell(0, 0, $microlinestr, 0, false, 'L'); 










        
        }
        $pdfBig->Output('test.pdf', 'I');



        
        // for testing data


    }


   
}
