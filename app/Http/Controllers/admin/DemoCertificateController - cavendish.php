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
                $records['certificateTopName'] = $certificateTopName;
                
                // Certificate address
                $certificateAddress = $sheet1->rangeToArray('A5', NULL, TRUE, FALSE);
                $certificateAddress = $certificateAddress[0][0];
                $records['certificateAddress'] = $certificateAddress;

                // Certificate school
                $certificateSchool = $sheet1->rangeToArray('A7', NULL, TRUE, FALSE);
                $certificateSchool = $certificateSchool[0][0];
                $records['certificateSchool'] = $certificateSchool;

                // Certificate statement of results
                $certificateStm = $sheet1->rangeToArray('A9', NULL, TRUE, FALSE);
                $certificateStm = $certificateStm[0][0];
                $records['certificateStm'] = $certificateStm;

                // Certificate name
                $certificateName = $sheet1->rangeToArray('B11', NULL, TRUE, FALSE);
                $certificateName = $certificateName[0][0];
                $records['certificateName'] = $certificateName;

                // Certificate gender
                $certificateGender = $sheet1->rangeToArray('B12', NULL, TRUE, FALSE);
                $certificateGender = $certificateGender[0][0];
                $records['certificateGender'] = $certificateGender;

                // Certificate DOB
                $certificateDOB = $sheet1->rangeToArray('B13', NULL, TRUE, FALSE);
                $certificateDOB = $certificateDOB[0][0];
                $records['certificateDOB'] = $certificateDOB;

                // Certificate Nationality
                $certificateNationality = $sheet1->rangeToArray('B14', NULL, TRUE, FALSE);
                $certificateNationality = $certificateNationality[0][0];
                $records['certificateNationality'] = $certificateNationality;

                // Certificate Reg No
                $certificateRegNo = $sheet1->rangeToArray('G11', NULL, TRUE, FALSE);
                $certificateRegNo = $certificateRegNo[0][0];
                $records['certificateRegNo'] = $certificateRegNo;

                // Certificate Programme
                $certificateProgramme = $sheet1->rangeToArray('G12', NULL, TRUE, FALSE);
                $certificateProgramme = $certificateProgramme[0][0];
                $records['certificateProgramme'] = $certificateProgramme;

                // Cert School
                $certSchool = $sheet1->rangeToArray('G13', NULL, TRUE, FALSE);
                $certSchool = $certSchool[0][0];
                $records['certSchool'] = $certSchool;

                // Certificate year of entry
                $certificateYearEntry = $sheet1->rangeToArray('G14', NULL, TRUE, FALSE);
                $certificateYearEntry = $certificateYearEntry[0][0];
                $records['certificateYearEntry'] = $certificateYearEntry;

                // Certificate award
                $certificateAward = $sheet1->rangeToArray('A17', NULL, TRUE, FALSE);
                $certificateAward = $certificateAward[0][0];
                $records['certificateAward'] = $certificateAward;

                // Certificate award1
                $certificateAward1 = $sheet1->rangeToArray('A16', NULL, TRUE, FALSE);
                $certificateAward1 = $certificateAward1[0][0];
                $records['certificateAward1'] = $certificateAward1;

                // Foundation year data //
                // Certificate Foundation courseCode1
                $foundationCourseCode1 = $sheet1->rangeToArray('A23', NULL, TRUE, FALSE);
                $foundationCourseCode1 = $foundationCourseCode1[0][0];
                $records['foundationCourseCode1'] = $foundationCourseCode1;

                // Certificate Foundation courseCode2
                $foundationCourseCode2 = $sheet1->rangeToArray('A24', NULL, TRUE, FALSE);
                $foundationCourseCode2 = $foundationCourseCode2[0][0];
                $records['foundationCourseCode2'] = $foundationCourseCode2;

                // Certificate Foundation courseCode3
                $foundationCourseCode3 = $sheet1->rangeToArray('A25', NULL, TRUE, FALSE);
                $foundationCourseCode3 = $foundationCourseCode3[0][0];
                $records['foundationCourseCode3'] = $foundationCourseCode3;


                // Certificate Foundation courseName1
                $foundationCourseName1 = $sheet1->rangeToArray('B23', NULL, TRUE, FALSE);
                $foundationCourseName1 = $foundationCourseName1[0][0];
                $records['foundationCourseName1'] = $foundationCourseName1;

                // Certificate Foundation courseName2
                $foundationCourseName2 = $sheet1->rangeToArray('B24', NULL, TRUE, FALSE);
                $foundationCourseName2 = $foundationCourseName2[0][0];
                $records['foundationCourseName2'] = $foundationCourseName2;

                // Certificate Foundation courseName3
                $foundationCourseName3 = $sheet1->rangeToArray('B25', NULL, TRUE, FALSE);
                $foundationCourseName3 = $foundationCourseName3[0][0];
                $records['foundationCourseName3'] = $foundationCourseName3;

                // Certificate Foundation Grade1
                $foundationGrade1 = $sheet1->rangeToArray('H23', NULL, TRUE, FALSE);
                $foundationGrade1 = $foundationGrade1[0][0];
                $records['foundationGrade1'] = $foundationGrade1;

                // Certificate Foundation Grade2
                $foundationGrade2 = $sheet1->rangeToArray('H24', NULL, TRUE, FALSE);
                $foundationGrade2 = $foundationGrade2[0][0];
                $records['foundationGrade2'] = $foundationGrade2;

                // Certificate Foundation Grade3
                $foundationGrade3 = $sheet1->rangeToArray('H25', NULL, TRUE, FALSE);
                $foundationGrade3 = $foundationGrade3[0][0];
                $records['foundationGrade3'] = $foundationGrade3;

                // Certificate Foundation Remark1
                $foundationRemark1 = $sheet1->rangeToArray('I23', NULL, TRUE, FALSE);
                $foundationRemark1 = $foundationRemark1[0][0];
                $records['foundationRemark1'] = $foundationRemark1;

                // Certificate Foundation Remark2
                $foundationRemark2 = $sheet1->rangeToArray('I24', NULL, TRUE, FALSE);
                $foundationRemark2 = $foundationRemark2[0][0];
                $records['foundationRemark2'] = $foundationRemark2;

                // Certificate Foundation Remark3
                $foundationRemark3 = $sheet1->rangeToArray('I25', NULL, TRUE, FALSE);
                $foundationRemark3 = $foundationRemark3[0][0];
                $records['foundationRemark3'] = $foundationRemark3;
                // Foundation year data //


                // Year1 data //
                // Certificate Year 1 courseCode1
                $year1CourseCode1 = $sheet1->rangeToArray('A33', NULL, TRUE, FALSE);
                $year1CourseCode1 = $year1CourseCode1[0][0];
                $records['year1CourseCode1'] = $year1CourseCode1;

                // Certificate Year 1 courseCode2
                $year1CourseCode2 = $sheet1->rangeToArray('A34', NULL, TRUE, FALSE);
                $year1CourseCode2 = $year1CourseCode2[0][0];
                $records['year1CourseCode2'] = $year1CourseCode2;

                // Certificate Year 1 courseCode3
                $year1CourseCode3 = $sheet1->rangeToArray('A35', NULL, TRUE, FALSE);
                $year1CourseCode3 = $year1CourseCode3[0][0];
                $records['year1CourseCode3'] = $year1CourseCode3;

                // Certificate Year 1 courseCode4
                $year1CourseCode4 = $sheet1->rangeToArray('A36', NULL, TRUE, FALSE);
                $year1CourseCode4 = $year1CourseCode4[0][0];
                $records['year1CourseCode4'] = $year1CourseCode4;

                // Certificate Year 1 courseCode5
                $year1CourseCode5 = $sheet1->rangeToArray('A37', NULL, TRUE, FALSE);
                $year1CourseCode5 = $year1CourseCode5[0][0];
                $records['year1CourseCode5'] = $year1CourseCode5;



                // Certificate Year 1 courseName1
                $year1CourseName1 = $sheet1->rangeToArray('B33', NULL, TRUE, FALSE);
                $year1CourseName1 = $year1CourseName1[0][0];
                $records['year1CourseName1'] = $year1CourseName1;

                // Certificate Year 1 courseName2
                $year1CourseName2 = $sheet1->rangeToArray('B34', NULL, TRUE, FALSE);
                $year1CourseName2 = $year1CourseName2[0][0];
                $records['year1CourseName2'] = $year1CourseName2;

                // Certificate Year 1 courseName3
                $year1CourseName3 = $sheet1->rangeToArray('B35', NULL, TRUE, FALSE);
                $year1CourseName3 = $year1CourseName3[0][0];
                $records['year1CourseName3'] = $year1CourseName3;

                // Certificate Year 1 courseName4
                $year1CourseName4 = $sheet1->rangeToArray('B36', NULL, TRUE, FALSE);
                $year1CourseName4 = $year1CourseName4[0][0];
                $records['year1CourseName4'] = $year1CourseName4;

                // Certificate Year 1 courseName5
                $year1CourseName5 = $sheet1->rangeToArray('B37', NULL, TRUE, FALSE);
                $year1CourseName5 = $year1CourseName5[0][0];
                $records['year1CourseName5'] = $year1CourseName5;


                // Certificate Year 1 Grade1
                $year1Grade1 = $sheet1->rangeToArray('H33', NULL, TRUE, FALSE);
                $year1Grade1 = $year1Grade1[0][0];
                $records['year1Grade1'] = $year1Grade1;

                // Certificate Year 1 Grade2
                $year1Grade2 = $sheet1->rangeToArray('H34', NULL, TRUE, FALSE);
                $year1Grade2 = $year1Grade2[0][0];
                $records['year1Grade2'] = $year1Grade2;

                // Certificate Year 1 Grade3
                $year1Grade3 = $sheet1->rangeToArray('H35', NULL, TRUE, FALSE);
                $year1Grade3 = $year1Grade3[0][0];
                $records['year1Grade3'] = $year1Grade3;

                // Certificate Year 1 Grade4
                $year1Grade4 = $sheet1->rangeToArray('H36', NULL, TRUE, FALSE);
                $year1Grade4 = $year1Grade4[0][0];
                $records['year1Grade4'] = $year1Grade4;

                // Certificate Year 1 Grade5
                $year1Grade5 = $sheet1->rangeToArray('H37', NULL, TRUE, FALSE);
                $year1Grade5 = $year1Grade5[0][0];
                $records['year1Grade5'] = $year1Grade5;


                // Certificate Year 1 Remark1
                $year1Remark1 = $sheet1->rangeToArray('I33', NULL, TRUE, FALSE);
                $year1Remark1 = $year1Remark1[0][0];
                $records['year1Remark1'] = $year1Remark1;

                // Certificate Year 1 Remark2
                $year1Remark2 = $sheet1->rangeToArray('I34', NULL, TRUE, FALSE);
                $year1Remark2 = $year1Remark2[0][0];
                $records['year1Remark2'] = $year1Remark2;

                // Certificate Year 1 Remark3
                $year1Remark3 = $sheet1->rangeToArray('I35', NULL, TRUE, FALSE);
                $year1Remark3 = $year1Remark3[0][0];
                $records['year1Remark3'] = $year1Remark3;

                // Certificate Year 1 Remark4
                $year1Remark4 = $sheet1->rangeToArray('I36', NULL, TRUE, FALSE);
                $year1Remark4 = $year1Remark4[0][0];
                $records['year1Remark4'] = $year1Remark4;

                // Certificate Year 1 Remark5
                $year1Remark5 = $sheet1->rangeToArray('I37', NULL, TRUE, FALSE);
                $year1Remark5 = $year1Remark5[0][0];
                $records['year1Remark5'] = $year1Remark5;
                // Year 1 data //



                // Year2 data //
                // Certificate Year 2 courseCode1
                $year2CourseCode1 = $sheet1->rangeToArray('A43', NULL, TRUE, FALSE);
                $year2CourseCode1 = $year2CourseCode1[0][0];
                $records['year2CourseCode1'] = $year2CourseCode1;

                // Certificate Year 2 courseCode2
                $year2CourseCode2 = $sheet1->rangeToArray('A44', NULL, TRUE, FALSE);
                $year2CourseCode2 = $year2CourseCode2[0][0];
                $records['year2CourseCode2'] = $year2CourseCode2;

                // Certificate Year 2 courseCode3
                $year2CourseCode3 = $sheet1->rangeToArray('A45', NULL, TRUE, FALSE);
                $year2CourseCode3 = $year2CourseCode3[0][0];
                $records['year2CourseCode3'] = $year2CourseCode3;

                // Certificate Year 2 courseCode4
                $year2CourseCode4 = $sheet1->rangeToArray('A46', NULL, TRUE, FALSE);
                $year2CourseCode4 = $year2CourseCode4[0][0];
                $records['year2CourseCode4'] = $year2CourseCode4;

                // Certificate Year 2 courseCode5
                $year2CourseCode5 = $sheet1->rangeToArray('A47', NULL, TRUE, FALSE);
                $year2CourseCode5 = $year2CourseCode5[0][0];
                $records['year2CourseCode5'] = $year2CourseCode5;

                // Certificate Year 2 courseCode6
                $year2CourseCode6 = $sheet1->rangeToArray('A48', NULL, TRUE, FALSE);
                $year2CourseCode6 = $year2CourseCode6[0][0];
                $records['year2CourseCode6'] = $year2CourseCode6;


                // Certificate Year 2 courseName1
                $year2CourseName1 = $sheet1->rangeToArray('B43', NULL, TRUE, FALSE);
                $year2CourseName1 = $year2CourseName1[0][0];
                $records['year2CourseName1'] = $year2CourseName1;

                // Certificate Year 2 courseName2
                $year2CourseName2 = $sheet1->rangeToArray('B44', NULL, TRUE, FALSE);
                $year2CourseName2 = $year2CourseName2[0][0];
                $records['year2CourseName2'] = $year2CourseName2;

                // Certificate Year 2 courseName3
                $year2CourseName3 = $sheet1->rangeToArray('B45', NULL, TRUE, FALSE);
                $year2CourseName3 = $year2CourseName3[0][0];
                $records['year2CourseName3'] = $year2CourseName3;

                // Certificate Year 2 courseName4
                $year2CourseName4 = $sheet1->rangeToArray('B46', NULL, TRUE, FALSE);
                $year2CourseName4 = $year2CourseName4[0][0];
                $records['year2CourseName4'] = $year2CourseName4;

                // Certificate Year 2 courseName5
                $year2CourseName5 = $sheet1->rangeToArray('B47', NULL, TRUE, FALSE);
                $year2CourseName5 = $year2CourseName5[0][0];
                $records['year2CourseName5'] = $year2CourseName5;

                // Certificate Year 2 courseName6
                $year2CourseName6 = $sheet1->rangeToArray('B48', NULL, TRUE, FALSE);
                $year2CourseName6 = $year2CourseName6[0][0];
                $records['year2CourseName6'] = $year2CourseName6;


                // Certificate Year 2 Grade1
                $year2Grade1 = $sheet1->rangeToArray('H43', NULL, TRUE, FALSE);
                $year2Grade1 = $year2Grade1[0][0];
                $records['year2Grade1'] = $year2Grade1;

                // Certificate Year 2 Grade2
                $year2Grade2 = $sheet1->rangeToArray('H44', NULL, TRUE, FALSE);
                $year2Grade2 = $year2Grade2[0][0];
                $records['year2Grade2'] = $year2Grade2;

                // Certificate Year 2 Grade3
                $year2Grade3 = $sheet1->rangeToArray('H45', NULL, TRUE, FALSE);
                $year2Grade3 = $year2Grade3[0][0];
                $records['year2Grade3'] = $year2Grade3;

                // Certificate Year 2 Grade4
                $year2Grade4 = $sheet1->rangeToArray('H46', NULL, TRUE, FALSE);
                $year2Grade4 = $year2Grade4[0][0];
                $records['year2Grade4'] = $year2Grade4;

                // Certificate Year 2 Grade5
                $year2Grade5 = $sheet1->rangeToArray('H47', NULL, TRUE, FALSE);
                $year2Grade5 = $year2Grade5[0][0];
                $records['year2Grade5'] = $year2Grade5;

                // Certificate Year 2 Grade6
                $year2Grade6 = $sheet1->rangeToArray('H48', NULL, TRUE, FALSE);
                $year2Grade6 = $year2Grade6[0][0];
                $records['year2Grade6'] = $year2Grade6;


                // Certificate Year 2 Remark1
                $year2Remark1 = $sheet1->rangeToArray('I43', NULL, TRUE, FALSE);
                $year2Remark1 = $year2Remark1[0][0];
                $records['year2Remark1'] = $year2Remark1;

                // Certificate Year 2 Remark2
                $year2Remark2 = $sheet1->rangeToArray('I44', NULL, TRUE, FALSE);
                $year2Remark2 = $year2Remark2[0][0];
                $records['year2Remark2'] = $year2Remark2;

                // Certificate Year 2 Remark3
                $year2Remark3 = $sheet1->rangeToArray('I45', NULL, TRUE, FALSE);
                $year2Remark3 = $year2Remark3[0][0];
                $records['year2Remark3'] = $year2Remark3;

                // Certificate Year 2 Remark4
                $year2Remark4 = $sheet1->rangeToArray('I46', NULL, TRUE, FALSE);
                $year2Remark4 = $year2Remark4[0][0];
                $records['year2Remark4'] = $year2Remark4;

                // Certificate Year 2 Remark5
                $year2Remark5 = $sheet1->rangeToArray('I47', NULL, TRUE, FALSE);
                $year2Remark5 = $year2Remark5[0][0];
                $records['year2Remark5'] = $year2Remark5;

                // Certificate Year 2 Remark6
                $year2Remark6 = $sheet1->rangeToArray('I48', NULL, TRUE, FALSE);
                $year2Remark6 = $year2Remark6[0][0];
                $records['year2Remark6'] = $year2Remark6;

                // Year2 data //



                // Year3 data //
                // Certificate Year 3 courseCode1
                $year3CourseCode1 = $sheet1->rangeToArray('A53', NULL, TRUE, FALSE);
                $year3CourseCode1 = $year3CourseCode1[0][0];
                $records['year3CourseCode1'] = $year3CourseCode1;

                // Certificate Year 3 courseCode2
                $year3CourseCode2 = $sheet1->rangeToArray('A54', NULL, TRUE, FALSE);
                $year3CourseCode2 = $year3CourseCode2[0][0];
                $records['year3CourseCode2'] = $year3CourseCode2;

                // Certificate Year 3 courseCode3
                $year3CourseCode3 = $sheet1->rangeToArray('A55', NULL, TRUE, FALSE);
                $year3CourseCode3 = $year3CourseCode3[0][0];
                $records['year3CourseCode3'] = $year3CourseCode3;

                // Certificate Year 3 courseCode4
                $year3CourseCode4 = $sheet1->rangeToArray('A56', NULL, TRUE, FALSE);
                $year3CourseCode4 = $year3CourseCode4[0][0];
                $records['year3CourseCode4'] = $year3CourseCode4;

                // Certificate Year 3 courseCode5
                $year3CourseCode5 = $sheet1->rangeToArray('A57', NULL, TRUE, FALSE);
                $year3CourseCode5 = $year3CourseCode5[0][0];
                $records['year3CourseCode5'] = $year3CourseCode5;

                // Certificate Year 3 courseCode6
                $year3CourseCode6 = $sheet1->rangeToArray('A58', NULL, TRUE, FALSE);
                $year3CourseCode6 = $year3CourseCode6[0][0];
                $records['year3CourseCode6'] = $year3CourseCode6;


                // Certificate Year 3 courseName1
                $year3CourseName1 = $sheet1->rangeToArray('B53', NULL, TRUE, FALSE);
                $year3CourseName1 = $year3CourseName1[0][0];
                $records['year3CourseName1'] = $year3CourseName1;

                // Certificate Year 3 courseName2
                $year3CourseName2 = $sheet1->rangeToArray('B54', NULL, TRUE, FALSE);
                $year3CourseName2 = $year3CourseName2[0][0];
                $records['year3CourseName2'] = $year3CourseName2;

                // Certificate Year 3 courseName3
                $year3CourseName3 = $sheet1->rangeToArray('B55', NULL, TRUE, FALSE);
                $year3CourseName3 = $year3CourseName3[0][0];
                $records['year3CourseName3'] = $year3CourseName3;

                // Certificate Year 3 courseName4
                $year3CourseName4 = $sheet1->rangeToArray('B56', NULL, TRUE, FALSE);
                $year3CourseName4 = $year3CourseName4[0][0];
                $records['year3CourseName4'] = $year3CourseName4;

                // Certificate Year 3 courseName5
                $year3CourseName5 = $sheet1->rangeToArray('B57', NULL, TRUE, FALSE);
                $year3CourseName5 = $year3CourseName5[0][0];
                $records['year3CourseName5'] = $year3CourseName5;

                // Certificate Year 3 courseName6
                $year3CourseName6 = $sheet1->rangeToArray('B58', NULL, TRUE, FALSE);
                $year3CourseName6 = $year3CourseName6[0][0];
                $records['year3CourseName6'] = $year3CourseName6;


                // Certificate Year 3 Grade1
                $year3Grade1 = $sheet1->rangeToArray('H53', NULL, TRUE, FALSE);
                $year3Grade1 = $year3Grade1[0][0];
                $records['year3Grade1'] = $year3Grade1;

                // Certificate Year 3 Grade2
                $year3Grade2 = $sheet1->rangeToArray('H54', NULL, TRUE, FALSE);
                $year3Grade2 = $year3Grade2[0][0];
                $records['year3Grade2'] = $year3Grade2;

                // Certificate Year 3 Grade3
                $year3Grade3 = $sheet1->rangeToArray('H55', NULL, TRUE, FALSE);
                $year3Grade3 = $year3Grade3[0][0];
                $records['year3Grade3'] = $year3Grade3;

                // Certificate Year 3 Grade4
                $year3Grade4 = $sheet1->rangeToArray('H56', NULL, TRUE, FALSE);
                $year3Grade4 = $year3Grade4[0][0];
                $records['year3Grade4'] = $year3Grade4;

                // Certificate Year 3 Grade5
                $year3Grade5 = $sheet1->rangeToArray('H57', NULL, TRUE, FALSE);
                $year3Grade5 = $year3Grade5[0][0];
                $records['year3Grade5'] = $year3Grade5;

                // Certificate Year 3 Grade6
                $year3Grade6 = $sheet1->rangeToArray('H58', NULL, TRUE, FALSE);
                $year3Grade6 = $year3Grade6[0][0];
                $records['year3Grade6'] = $year3Grade6;


                // Certificate Year 3 Remark1
                $year3Remark1 = $sheet1->rangeToArray('I53', NULL, TRUE, FALSE);
                $year3Remark1 = $year3Remark1[0][0];
                $records['year3Remark1'] = $year3Remark1;

                // Certificate Year 3 Remark2
                $year3Remark2 = $sheet1->rangeToArray('I54', NULL, TRUE, FALSE);
                $year3Remark2 = $year3Remark2[0][0];
                $records['year3Remark2'] = $year3Remark2;

                // Certificate Year 3 Remark3
                $year3Remark3 = $sheet1->rangeToArray('I55', NULL, TRUE, FALSE);
                $year3Remark3 = $year3Remark3[0][0];
                $records['year3Remark3'] = $year3Remark3;

                // Certificate Year 3 Remark4
                $year3Remark4 = $sheet1->rangeToArray('I56', NULL, TRUE, FALSE);
                $year3Remark4 = $year3Remark4[0][0];
                $records['year3Remark4'] = $year3Remark4;

                // Certificate Year 3 Remark5
                $year3Remark5 = $sheet1->rangeToArray('I57', NULL, TRUE, FALSE);
                $year3Remark5 = $year3Remark5[0][0];
                $records['year3Remark5'] = $year3Remark5;

                // Certificate Year 3 Remark6
                $year3Remark6 = $sheet1->rangeToArray('I58', NULL, TRUE, FALSE);
                $year3Remark6 = $year3Remark6[0][0];
                $records['year3Remark6'] = $year3Remark6;

                // Year3 data //



                // Year4 data //
                // Certificate Year 4 courseCode1
                $year4CourseCode1 = $sheet1->rangeToArray('A67', NULL, TRUE, FALSE);
                $year4CourseCode1 = $year4CourseCode1[0][0];
                $records['year4CourseCode1'] = $year4CourseCode1;

                // Certificate Year 4 courseCode2
                $year4CourseCode2 = $sheet1->rangeToArray('A68', NULL, TRUE, FALSE);
                $year4CourseCode2 = $year4CourseCode2[0][0];
                $records['year4CourseCode2'] = $year4CourseCode2;

                // Certificate Year 4 courseCode3
                $year4CourseCode3 = $sheet1->rangeToArray('A69', NULL, TRUE, FALSE);
                $year4CourseCode3 = $year4CourseCode3[0][0];
                $records['year4CourseCode3'] = $year4CourseCode3;

                // Certificate Year 4 courseCode4
                $year4CourseCode4 = $sheet1->rangeToArray('A70', NULL, TRUE, FALSE);
                $year4CourseCode4 = $year4CourseCode4[0][0];
                $records['year4CourseCode4'] = $year4CourseCode4;


                // Certificate Year 4 courseName1
                $year4CourseName1 = $sheet1->rangeToArray('B67', NULL, TRUE, FALSE);
                $year4CourseName1 = $year4CourseName1[0][0];
                $records['year4CourseName1'] = $year4CourseName1;

                // Certificate Year 4 courseName2
                $year4CourseName2 = $sheet1->rangeToArray('B68', NULL, TRUE, FALSE);
                $year4CourseName2 = $year4CourseName2[0][0];
                $records['year4CourseName2'] = $year4CourseName2;

                // Certificate Year 4 courseName3
                $year4CourseName3 = $sheet1->rangeToArray('B69', NULL, TRUE, FALSE);
                $year4CourseName3 = $year4CourseName3[0][0];
                $records['year4CourseName3'] = $year4CourseName3;

                // Certificate Year 4 courseName4
                $year4CourseName4 = $sheet1->rangeToArray('B70', NULL, TRUE, FALSE);
                $year4CourseName4 = $year4CourseName4[0][0];
                $records['year4CourseName4'] = $year4CourseName4;


                // Certificate Year 4 Grade1
                $year4Grade1 = $sheet1->rangeToArray('H67', NULL, TRUE, FALSE);
                $year4Grade1 = $year4Grade1[0][0];
                $records['year4Grade1'] = $year4Grade1;

                // Certificate Year 4 Grade2
                $year4Grade2 = $sheet1->rangeToArray('H68', NULL, TRUE, FALSE);
                $year4Grade2 = $year4Grade2[0][0];
                $records['year4Grade2'] = $year4Grade2;

                // Certificate Year 4 Grade3
                $year4Grade3 = $sheet1->rangeToArray('H69', NULL, TRUE, FALSE);
                $year4Grade3 = $year4Grade3[0][0];
                $records['year4Grade3'] = $year4Grade3;

                // Certificate Year 4 Grade4
                $year4Grade4 = $sheet1->rangeToArray('H70', NULL, TRUE, FALSE);
                $year4Grade4 = $year4Grade4[0][0];
                $records['year4Grade4'] = $year4Grade4;


                // Certificate Year 4 Remark1
                $year4Remark1 = $sheet1->rangeToArray('I67', NULL, TRUE, FALSE);
                $year4Remark1 = $year4Remark1[0][0];
                $records['year4Remark1'] = $year4Remark1;

                // Certificate Year 4 Remark2
                $year4Remark2 = $sheet1->rangeToArray('I68', NULL, TRUE, FALSE);
                $year4Remark2 = $year4Remark2[0][0];
                $records['year4Remark2'] = $year4Remark2;

                // Certificate Year 4 Remark3
                $year4Remark3 = $sheet1->rangeToArray('I69', NULL, TRUE, FALSE);
                $year4Remark3 = $year4Remark3[0][0];
                $records['year4Remark3'] = $year4Remark3;

                // Certificate Year 4 Remark4
                $year4Remark4 = $sheet1->rangeToArray('I70', NULL, TRUE, FALSE);
                $year4Remark4 = $year4Remark4[0][0];
                $records['year4Remark4'] = $year4Remark4;

                // Year4 data //



                // Year5 data //
                // Certificate Year 5 courseCode1
                $year5CourseCode1 = $sheet1->rangeToArray('A77', NULL, TRUE, FALSE);
                $year5CourseCode1 = $year5CourseCode1[0][0];
                $records['year5CourseCode1'] = $year5CourseCode1;

                // Certificate Year 5 courseCode2
                $year5CourseCode2 = $sheet1->rangeToArray('A78', NULL, TRUE, FALSE);
                $year5CourseCode2 = $year5CourseCode2[0][0];
                $records['year5CourseCode2'] = $year5CourseCode2;

                // Certificate Year 5 courseCode3
                $year5CourseCode3 = $sheet1->rangeToArray('A79', NULL, TRUE, FALSE);
                $year5CourseCode3 = $year5CourseCode3[0][0];
                $records['year5CourseCode3'] = $year5CourseCode3;

                // Certificate Year 5 courseCode4
                $year5CourseCode4 = $sheet1->rangeToArray('A80', NULL, TRUE, FALSE);
                $year5CourseCode4 = $year5CourseCode4[0][0];
                $records['year5CourseCode4'] = $year5CourseCode4;

                // Certificate Year 5 courseCode5
                $year5CourseCode5 = $sheet1->rangeToArray('A81', NULL, TRUE, FALSE);
                $year5CourseCode5 = $year5CourseCode5[0][0];
                $records['year5CourseCode5'] = $year5CourseCode5;


                // Certificate Year 5 courseName1
                $year5CourseName1 = $sheet1->rangeToArray('B77', NULL, TRUE, FALSE);
                $year5CourseName1 = $year5CourseName1[0][0];
                $records['year5CourseName1'] = $year5CourseName1;

                // Certificate Year 5 courseName2
                $year5CourseName2 = $sheet1->rangeToArray('B78', NULL, TRUE, FALSE);
                $year5CourseName2 = $year5CourseName2[0][0];
                $records['year5CourseName2'] = $year5CourseName2;

                // Certificate Year 5 courseName3
                $year5CourseName3 = $sheet1->rangeToArray('B79', NULL, TRUE, FALSE);
                $year5CourseName3 = $year5CourseName3[0][0];
                $records['year5CourseName3'] = $year5CourseName3;

                // Certificate Year 5 courseName4
                $year5CourseName4 = $sheet1->rangeToArray('B80', NULL, TRUE, FALSE);
                $year5CourseName4 = $year5CourseName4[0][0];
                $records['year5CourseName4'] = $year5CourseName4;

                // Certificate Year 5 courseName5
                $year5CourseName5 = $sheet1->rangeToArray('B81', NULL, TRUE, FALSE);
                $year5CourseName5 = $year5CourseName5[0][0];
                $records['year5CourseName5'] = $year5CourseName5;



                // Certificate Year 5 Grade1
                $year5Grade1 = $sheet1->rangeToArray('H77', NULL, TRUE, FALSE);
                $year5Grade1 = $year5Grade1[0][0];
                $records['year5Grade1'] = $year5Grade1;

                // Certificate Year 5 Grade2
                $year5Grade2 = $sheet1->rangeToArray('H78', NULL, TRUE, FALSE);
                $year5Grade2 = $year5Grade2[0][0];
                $records['year5Grade2'] = $year5Grade2;

                // Certificate Year 5 Grade3
                $year5Grade3 = $sheet1->rangeToArray('H79', NULL, TRUE, FALSE);
                $year5Grade3 = $year5Grade3[0][0];
                $records['year5Grade3'] = $year5Grade3;

                // Certificate Year 5 Grade4
                $year5Grade4 = $sheet1->rangeToArray('H80', NULL, TRUE, FALSE);
                $year5Grade4 = $year5Grade4[0][0];
                $records['year5Grade4'] = $year5Grade4;

                // Certificate Year 5 Grade5
                $year5Grade5 = $sheet1->rangeToArray('H81', NULL, TRUE, FALSE);
                $year5Grade5 = $year5Grade5[0][0];
                $records['year5Grade5'] = $year5Grade5;


                // Certificate Year 5 Remark1
                $year5Remark1 = $sheet1->rangeToArray('I77', NULL, TRUE, FALSE);
                $year5Remark1 = $year5Remark1[0][0];    
                $records['year5Remark1'] = $year5Remark1;

                // Certificate Year 5 Remark2
                $year5Remark2 = $sheet1->rangeToArray('I78', NULL, TRUE, FALSE);
                $year5Remark2 = $year5Remark2[0][0];
                $records['year5Remark2'] = $year5Remark2;

                // Certificate Year 5 Remark3
                $year5Remark3 = $sheet1->rangeToArray('I79', NULL, TRUE, FALSE);
                $year5Remark3 = $year5Remark3[0][0];
                $records['year5Remark3'] = $year5Remark3;

                // Certificate Year 5 Remark4
                $year5Remark4 = $sheet1->rangeToArray('I80', NULL, TRUE, FALSE);
                $year5Remark4 = $year5Remark4[0][0];
                $records['year5Remark4'] = $year5Remark4;

                // Certificate Year 5 Remark5
                $year5Remark5 = $sheet1->rangeToArray('I81', NULL, TRUE, FALSE);
                $year5Remark5 = $year5Remark5[0][0];
                $records['year5Remark5'] = $year5Remark5;

                // Year5 data //



                // Year6 data //
                // Certificate Year 6 courseCode1
                $year6CourseCode1 = $sheet1->rangeToArray('A87', NULL, TRUE, FALSE);
                $year6CourseCode1 = $year6CourseCode1[0][0];
                $records['year6CourseCode1'] = $year6CourseCode1;

                // Certificate Year 6 courseCode2
                $year6CourseCode2 = $sheet1->rangeToArray('A88', NULL, TRUE, FALSE);
                $year6CourseCode2 = $year6CourseCode2[0][0];
                $records['year6CourseCode2'] = $year6CourseCode2;

                // Certificate Year 6 courseCode3
                $year6CourseCode3 = $sheet1->rangeToArray('A89', NULL, TRUE, FALSE);
                $year6CourseCode3 = $year6CourseCode3[0][0];
                $records['year6CourseCode3'] = $year6CourseCode3;

                // Certificate Year 6 courseCode4
                $year6CourseCode4 = $sheet1->rangeToArray('A90', NULL, TRUE, FALSE);
                $year6CourseCode4 = $year6CourseCode4[0][0];
                $records['year6CourseCode4'] = $year6CourseCode4;



                // Certificate Year 6 courseName1
                $year6CourseName1 = $sheet1->rangeToArray('B87', NULL, TRUE, FALSE);
                $year6CourseName1 = $year6CourseName1[0][0];
                $records['year6CourseName1'] = $year6CourseName1;

                // Certificate Year 6 courseName2
                $year6CourseName2 = $sheet1->rangeToArray('B88', NULL, TRUE, FALSE);
                $year6CourseName2 = $year6CourseName2[0][0];
                $records['year6CourseName2'] = $year6CourseName2;

                // Certificate Year 6 courseName3
                $year6CourseName3 = $sheet1->rangeToArray('B89', NULL, TRUE, FALSE);
                $year6CourseName3 = $year6CourseName3[0][0];
                $records['year6CourseName3'] = $year6CourseName3;

                // Certificate Year 6 courseName4
                $year6CourseName4 = $sheet1->rangeToArray('B90', NULL, TRUE, FALSE);
                $year6CourseName4 = $year6CourseName4[0][0];
                $records['year6CourseName4'] = $year6CourseName4;


                // Certificate Year 6 Grade1
                $year6Grade1 = $sheet1->rangeToArray('H87', NULL, TRUE, FALSE);
                $year6Grade1 = $year6Grade1[0][0];
                $records['year6Grade1'] = $year6Grade1;

                // Certificate Year 6 Grade2
                $year6Grade2 = $sheet1->rangeToArray('H88', NULL, TRUE, FALSE);
                $year6Grade2 = $year6Grade2[0][0];
                $records['year6Grade2'] = $year6Grade2;

                // Certificate Year 6 Grade3
                $year6Grade3 = $sheet1->rangeToArray('H89', NULL, TRUE, FALSE);
                $year6Grade3 = $year6Grade3[0][0];
                $records['year6Grade3'] = $year6Grade3;

                // Certificate Year 6 Grade4
                $year6Grade4 = $sheet1->rangeToArray('H90', NULL, TRUE, FALSE);
                $year6Grade4 = $year6Grade4[0][0];
                $records['year6Grade4'] = $year6Grade4;


                // Certificate Year 6 Remark1
                $year6Remark1 = $sheet1->rangeToArray('I87', NULL, TRUE, FALSE);
                $year6Remark1 = $year6Remark1[0][0];    
                $records['year6Remark1'] = $year6Remark1;

                // Certificate Year 6 Remark2
                $year6Remark2 = $sheet1->rangeToArray('I88', NULL, TRUE, FALSE);
                $year6Remark2 = $year6Remark2[0][0];
                $records['year6Remark2'] = $year6Remark2;

                // Certificate Year 6 Remark3
                $year6Remark3 = $sheet1->rangeToArray('I89', NULL, TRUE, FALSE);
                $year6Remark3 = $year6Remark3[0][0];
                $records['year6Remark3'] = $year6Remark3;

                // Certificate Year 6 Remark4
                $year6Remark4 = $sheet1->rangeToArray('I90', NULL, TRUE, FALSE);
                $year6Remark4 = $year6Remark4[0][0];
                $records['year6Remark4'] = $year6Remark4;
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
 
        $pdfBig = new TCPDF('P', 'mm', array('215', '280'), true, 'UTF-8', false);
        $pdfBig->SetCreator(PDF_CREATOR);
        $pdfBig->SetAuthor('TCPDF');
        $pdfBig->SetTitle('Certificate');
        $pdfBig->SetSubject('');

        // remove default header/footer
        $pdfBig->setPrintHeader(false);
        $pdfBig->setPrintFooter(false);
        $pdfBig->SetAutoPageBreak(false, 0);


        //set fonts
        $Times_New_Roman = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\Times-New-Roman-Bold-Italic.ttf', 'TrueTypeUnicode', '', 96);
        $K101 = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\K101.ttf', 'TrueTypeUnicode', '', 96);
        $K100 = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\K100.ttf', 'TrueTypeUnicode', '', 96);
        $Kruti_Dev_730k = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\Kruti Dev 730k.ttf', 'TrueTypeUnicode', '', 96);
        $MICR_B10 = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\MICR-B10.ttf', 'TrueTypeUnicode', '', 96);
        $OLD_ENG1 = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\OLD_ENG1.ttf', 'TrueTypeUnicode', '', 96);
        $OLD_ENGL = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\OLD_ENGL.ttf', 'TrueTypeUnicode', '', 96);
        $times = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\times.ttf', 'TrueTypeUnicode', '', 96);
        $timesbd = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\timesbd.ttf', 'TrueTypeUnicode', '', 96);
        $timesbi = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\timesbi.ttf', 'TrueTypeUnicode', '', 96);
        $timesi = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\timesi.ttf', 'TrueTypeUnicode', '', 96);
        $Arial = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\Arial.TTF', 'TrueTypeUnicode', '', 96);
        $ArialB = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\Arialb.TTF', 'TrueTypeUnicode', '', 96);
        $ariali = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\ARIALI.TTF', 'TrueTypeUnicode', '', 96);

        $arialNarrow = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\arialn.ttf', 'TrueTypeUnicode', '', 96);


        $log_serial_no = 1;
        
        $preview_serial_no=1;
        $log_serial_no = 1;
        
        // comment for demo testing
        // $cardDetails=$this->getNextCardNo('CAVENDISH-SOM');
        // $card_serial_no=$cardDetails->next_serial_no;
        $card_serial_no = 1;
        // comment for demo testing

        $template_img_generate = public_path().'\\'.$subdomain[0].'\backend\canvas\bg_images\RAJ_RISHI__Bg_new.jpg'; 
        $fontEBPath = public_path() . '\\' . $subdomain[0] . '\backend\canvas\fonts\E-13B_0.php';
        $style1D = array(
            'position' => '',
            'align' => 'C',
            'stretch' => false,
            'fitwidth' => true,
            'cellfitalign' => '',
            'border' => false,
            'hpadding' => 'auto',
            'vpadding' => 'auto',
            'fgcolor' => array(0,0,0),
            'bgcolor' => false, //array(255,255,255),
            'text' => false,
            'font' => 'helvetica',
            'fontsize' => 8,
            'stretchtext' => 4
        );  

        $signature_path = public_path().'\\'.$subdomain[0].'\backend\canvas\images\Vice Chancellor.png';

        $generated_documents=0;  
        // foreach ($studentDataOrg as $studentData) 
        // {
            // $card_serial_no = '';
            //For Custom Loader
             $startTimeLoader =  date('Y-m-d H:i:s');
         
             $pdfBig->AddPage();
            
             //set background image
               
             if($previewPdf==1){
                if($previewWithoutBg!=1){
                    $pdfBig->Image($template_img_generate, 0, 0, '210', '297', "JPG", '', 'R', true);
                }
             }
            $pdfBig->setPageMark();

            $pdf = new TCPDF('P', 'mm', array('215', '280'), true, 'UTF-8', false);
            $pdf->SetCreator(PDF_CREATOR);
            $pdf->SetAuthor('TCPDF');
            $pdf->SetTitle('Certificate');
            $pdf->SetSubject('');

            // remove default header/footer
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);
            $pdf->SetAutoPageBreak(false, 0);
            
            $pdf->AddPage();
            //set background image
            //$template_img_generate = public_path().'\\'.$subdomain[0].'\backend\canvas\bg_images\RAJ_RISHI_Bg_new.jpg';
            if($previewPdf!=1){
                $pdf->Image($template_img_generate, 0, 0, '215', '180', "JPG", '', 'R', true);

            }
            $pdf->setPageMark();

            $print_serial_no = '';
            // comment for testing
            // $print_serial_no = $this->nextPrintSerial();
            // comment for testing

            // print_r($studentDataOrg);
            // die();

            $certificateTopName = $records['certificateTopName'];
            $certificateAddress = $records['certificateAddress'];
            $certificateSchool = $records['certificateSchool'];
            $certificateStm = $records['certificateStm'];
            $certificateName = $records['certificateName'];
            $certificateGender = $records['certificateGender'];
            $certificateDOB = $records['certificateDOB'];
            $certificateNationality = $records['certificateNationality'];
            $certificateRegNo = $records['certificateRegNo'];
            $certificateProgramme = $records['certificateProgramme'];
            $certSchool = $records['certSchool'];
            $certificateYearEntry = $records['certificateYearEntry'];
            $certificateAward = $records['certificateAward'];
            $certificateAward1 = $records['certificateAward1'];
            $foundationCourseCode1 = $records['foundationCourseCode1'];
            $foundationCourseCode2 = $records['foundationCourseCode2'];
            $foundationCourseCode3 = $records['foundationCourseCode3'];
            $foundationCourseCode3 = $records['foundationCourseCode3'];
            $foundationCourseName1 = $records['foundationCourseName1'];
            $foundationCourseName2 = $records['foundationCourseName2'];
            $foundationCourseName3 = $records['foundationCourseName3'];
            $foundationGrade1 = strtoupper($records['foundationGrade1']);
            $foundationGrade2 = strtoupper($records['foundationGrade2']);
            $foundationGrade3 = strtoupper($records['foundationGrade3']);
            $foundationRemark1 = $records['foundationRemark1'];
            $foundationRemark2 = $records['foundationRemark2'];
            $foundationRemark3 = $records['foundationRemark3'];
            $year1CourseCode1 = $records['year1CourseCode1'];
            $year1CourseCode2 = $records['year1CourseCode2'];
            $year1CourseCode3 = $records['year1CourseCode3'];
            $year1CourseCode4 = $records['year1CourseCode4'];
            $year1CourseCode5 = $records['year1CourseCode5'];
            $year1CourseName1 = $records['year1CourseName1'];
            $year1CourseName2 = $records['year1CourseName2'];
            $year1CourseName3 = $records['year1CourseName3'];
            $year1CourseName4 = $records['year1CourseName4'];
            $year1CourseName5 = $records['year1CourseName5'];
            $year1Grade1 = strtoupper($records['year1Grade1']);
            $year1Grade2 = strtoupper($records['year1Grade2']);
            $year1Grade3 = strtoupper($records['year1Grade3']);
            $year1Grade4 = strtoupper($records['year1Grade4']);
            $year1Grade5 = strtoupper($records['year1Grade5']);
            $year1Remark1 = $records['year1Remark1'];
            $year1Remark2 = $records['year1Remark2'];
            $year1Remark3 = $records['year1Remark3'];
            $year1Remark4 = $records['year1Remark4'];
            $year1Remark5 = $records['year1Remark5'];
            $year2CourseCode1 = $records['year2CourseCode1'];
            $year2CourseCode2 = $records['year2CourseCode2'];
            $year2CourseCode3 = $records['year2CourseCode3'];
            $year2CourseCode4 = $records['year2CourseCode4'];
            $year2CourseCode5 = $records['year2CourseCode5'];
            $year2CourseCode6 = $records['year2CourseCode6'];
            $year2CourseName1 = $records['year2CourseName1'];
            $year2CourseName2 = $records['year2CourseName2'];
            $year2CourseName3 = $records['year2CourseName3'];
            $year2CourseName4 = $records['year2CourseName4'];
            $year2CourseName5 = $records['year2CourseName5'];
            $year2CourseName6 = $records['year2CourseName6'];
            $year2Grade1 = strtoupper($records['year2Grade1']);
            $year2Grade2 = strtoupper($records['year2Grade2']);
            $year2Grade3 = strtoupper($records['year2Grade3']);
            $year2Grade4 = strtoupper($records['year2Grade4']);
            $year2Grade5 = strtoupper($records['year2Grade5']);
            $year2Grade6 = strtoupper($records['year2Grade6']);
            $year2Remark1 = $records['year2Remark1'];
            $year2Remark2 = $records['year2Remark2'];
            $year2Remark3 = $records['year2Remark3'];
            $year2Remark4 = $records['year2Remark4'];
            $year2Remark5 = $records['year2Remark5'];
            $year2Remark6 = $records['year2Remark6'];
            $year3CourseCode1 = $records['year3CourseCode1'];
            $year3CourseCode2 = $records['year3CourseCode2'];
            $year3CourseCode3 = $records['year3CourseCode3'];
            $year3CourseCode4 = $records['year3CourseCode4'];
            $year3CourseCode5 = $records['year3CourseCode5'];
            $year3CourseName1 = $records['year3CourseName1'];
            $year3CourseName2 = $records['year3CourseName2'];
            $year3CourseName3 = $records['year3CourseName3'];
            $year3CourseName4 = $records['year3CourseName4'];
            $year3CourseName5 = $records['year3CourseName5'];
            $year3Grade1 = strtoupper($records['year3Grade1']);
            $year3Grade2 = strtoupper($records['year3Grade2']);
            $year3Grade3 = strtoupper($records['year3Grade3']);
            $year3Grade4 = strtoupper($records['year3Grade4']);
            $year3Grade5 = strtoupper($records['year3Grade5']);
            $year3Remark1 = $records['year3Remark1'];
            $year3Remark2 = $records['year3Remark2'];
            $year3Remark3 = $records['year3Remark3'];
            $year3Remark4 = $records['year3Remark4'];
            $year3Remark5 = $records['year3Remark5'];
            $year4CourseCode1 = $records['year4CourseCode1'];
            $year4CourseCode2 = $records['year4CourseCode2'];
            $year4CourseCode3 = $records['year4CourseCode3'];
            $year4CourseCode4 = $records['year4CourseCode4'];
            $year4CourseName1 = $records['year4CourseName1'];
            $year4CourseName2 = $records['year4CourseName2'];
            $year4CourseName3 = $records['year4CourseName3'];
            $year4CourseName4 = $records['year4CourseName4'];
            $year4Grade1 = strtoupper($records['year4Grade1']);
            $year4Grade2 = strtoupper($records['year4Grade2']);
            $year4Grade3 = strtoupper($records['year4Grade3']);
            $year4Grade4 = strtoupper($records['year4Grade4']);
            $year4Remark1 = $records['year4Remark1'];
            $year4Remark2 = $records['year4Remark2'];
            $year4Remark3 = $records['year4Remark3'];
            $year4Remark4 = $records['year4Remark4'];
            $year5CourseCode1 = $records['year5CourseCode1'];
            $year5CourseCode2 = $records['year5CourseCode2'];
            $year5CourseCode3 = $records['year5CourseCode3'];
            $year5CourseCode4 = $records['year5CourseCode4'];
            $year5CourseCode5 = $records['year5CourseCode5'];
            $year5CourseName1 = $records['year5CourseName1'];
            $year5CourseName2 = $records['year5CourseName2'];
            $year5CourseName3 = $records['year5CourseName3'];
            $year5CourseName4 = $records['year5CourseName4'];
            $year5CourseName5 = $records['year5CourseName5'];
            $year5Grade1 = strtoupper($records['year5Grade1']);
            $year5Grade2 = strtoupper($records['year5Grade2']);
            $year5Grade3 = strtoupper($records['year5Grade3']);
            $year5Grade4 = strtoupper($records['year5Grade4']);
            $year5Grade5 = strtoupper($records['year5Grade5']);
            $year5Remark1 = $records['year5Remark1'];
            $year5Remark2 = $records['year5Remark2'];
            $year5Remark3 = $records['year5Remark3'];
            $year5Remark4 = $records['year5Remark4'];
            $year5Remark5 = $records['year5Remark5'];
            $year6CourseCode1 = $records['year6CourseCode1'];
            $year6CourseCode2 = $records['year6CourseCode2'];
            $year6CourseCode3 = $records['year6CourseCode3'];
            $year6CourseCode4 = $records['year6CourseCode4'];
            $year6CourseName1 = $records['year6CourseName1'];
            $year6CourseName2 = $records['year6CourseName2'];
            $year6CourseName3 = $records['year6CourseName3'];
            $year6CourseName4 = $records['year6CourseName4'];
            $year6Grade1 = strtoupper($records['year6Grade1']);
            $year6Grade2 = strtoupper($records['year6Grade2']);
            $year6Grade3 = strtoupper($records['year6Grade3']);
            $year6Grade4 = strtoupper($records['year6Grade4']);
            $year6Remark1 = $records['year6Remark1'];
            $year6Remark2 = $records['year6Remark2'];
            $year6Remark3 = $records['year6Remark3'];
            $year6Remark4 = $records['year6Remark4'];



            $pdfBig->SetTextColor(0,0,0);
            $pdf->SetTextColor(0,0,0);

            $xStart= 21;
            $pageWidth = 178;
            $pdfBig->SetFont($times, 'B', 12, '', false);
            $pdfBig->SetXY($xStart, 11);
            $pdfBig->SetTextColor(200,180,216);
            $pdfBig->MultiCell($pageWidth, 0, 'CAVENDISH', 0, 'C', 0, 0, '', '', false);

            $pdf->SetFont($times, 'B', 12, '', false);
            $pdf->SetXY($xStart, 11);
            $pdf->SetTextColor(200,180,216);
            $pdf->MultiCell($pageWidth, 0, 'CAVENDISH', 0, 'C', 0, 0, '', '', false);

            

            $pdfBig->SetXY(177, 12);
            $pdfBig->Image($subdomain[0].'\backend\images/image_som.PNG', '', '', 22, 29, '', '', 'T', false, 300, '', false, false, 1, false, false, false);

            $pdf->SetXY(177, 12);
            $pdf->Image($subdomain[0].'\backend\images/image_som.PNG', '', '', 22, 29, '', '', 'T', false, 300, '', false, false, 1, false, false, false);

            $pdfBig->SetFont($times, 'B', 9, '', false);
            $pdfBig->SetXY(173, 41);
            $pdfBig->SetTextColor(0,0,0);
            $pdfBig->Cell(30, 0, str_replace('-','',$certificateRegNo), 0, false, 'C');

            $pdf->SetFont($times, 'B', 9, '', false);
            $pdf->SetXY(173, 41);
            $pdf->SetTextColor(0,0,0);
            $pdf->Cell(30, 0, str_replace('-','',$certificateRegNo), 0, false, 'C');


            $pdfBig->SetFont($times, 'B', 12, '', false);
            $pdfBig->SetXY($xStart, 16);
            $pdfBig->SetTextColor(24,57,106);
            $pdfBig->Cell($pageWidth, 0, "UNIVERSITY", 0, false, 'C');

            $pdf->SetFont($times, 'B', 12, '', false);
            $pdf->SetXY($xStart, 16);
            $pdf->SetTextColor(24,57,106);
            $pdf->Cell($pageWidth, 0, "UNIVERSITY", 0, false, 'C');


            $pdfBig->SetFont($times, '', 13, '', false);
            $pdfBig->SetXY($xStart, 21);
            $pdfBig->SetTextColor(24,57,106);
            $pdfBig->Cell($pageWidth, 0, "Z A M B I A", 0, false, 'C');

            $pdf->SetFont($times, '', 13, '', false);
            $pdf->SetXY($xStart, 21);
            $pdf->SetTextColor(24,57,106);
            $pdf->Cell($pageWidth, 0, "Z A M B I A", 0, false, 'C');


            $pdfBig->SetFont($times, 'I', 7.5, '', false);
            $pdfBig->SetXY($xStart, 26);
            $pdfBig->SetTextColor(24,57,106);
            $pdfBig->Cell($pageWidth, 0, $certificateTopName, 0, false, 'C');

            $pdf->SetFont($times, 'I', 7.5, '', false);
            $pdf->SetXY($xStart, 26);
            $pdf->SetTextColor(24,57,106);
            $pdf->Cell($pageWidth, 0, $certificateTopName, 0, false, 'C');


            $pdfBig->SetFont($times, '', 7, '', false);
            $pdfBig->SetXY($xStart, 29);
            $pdfBig->SetTextColor(24,57,106);
            $pdfBig->Cell($pageWidth, 0, $certificateAddress, 0, false, 'C');

            $pdf->SetFont($times, '', 7, '', false);
            $pdf->SetXY($xStart, 29);
            $pdf->SetTextColor(24,57,106);
            $pdf->Cell($pageWidth, 0, $certificateAddress, 0, false, 'C');


            $pdfBig->SetFont($times, 'B', 11, '', false);
            $pdfBig->SetXY($xStart, 32);
            $pdfBig->SetTextColor(0,0,0);
            $pdfBig->Cell($pageWidth, 0, 'OFFICE OF THE ACADEMIC REGISTRAR', 0, false, 'C');

            $pdf->SetFont($times, 'B', 11, '', false);
            $pdf->SetXY($xStart, 32);
            $pdf->SetTextColor(0,0,0);
            $pdf->Cell($pageWidth, 0, 'OFFICE OF THE ACADEMIC REGISTRAR', 0, false, 'C');


            $pdfBig->SetFont($times, 'B', 11, '', false);
            $pdfBig->SetXY($xStart, 36);
            $pdfBig->SetTextColor(0,0,0);
            $pdfBig->Cell($pageWidth, 0, 'ACADEMIC TRANSCRIPT', 0, false, 'C');

            $pdf->SetFont($times, 'B', 11, '', false);
            $pdf->SetXY($xStart, 36);
            $pdf->SetTextColor(0,0,0);
            $pdf->Cell($pageWidth, 0, 'ACADEMIC TRANSCRIPT', 0, false, 'C');


            $pdfBig->setCellPaddings( $left = '0', $top = '0.5', $right = '', $bottom = '');
            $pdf->setCellPaddings( $left = '0', $top = '0.5', $right = '', $bottom = '');

            $pdfBig->SetFont($Arial, '', 9, '', false);
            $pdfBig->SetXY($xStart, 42);
            $pdfBig->SetTextColor(0,0,0);
            $pdfBig->Cell(30, 0, 'Name', 0, false, 'L');

            $pdf->SetFont($Arial, '', 9, '', false);
            $pdf->SetXY($xStart, 42);
            $pdf->SetTextColor(0,0,0);
            $pdf->Cell(30, 0, 'Name', 0, false, 'L');


            $pdfBig->SetFont($Arial, '', 9, '', false);
            $pdfBig->SetXY(41, 42);
            $pdfBig->Cell(0, 0, $certificateName, 0, false, 'L');

            $pdf->SetFont($Arial, '', 9, '', false);
            $pdf->SetXY(41, 42);
            $pdf->Cell(0, 0, $certificateName, 0, false, 'L');


            $pdfBig->SetFont($Arial, '', 9, '', false);
            $pdfBig->SetXY($xStart, 46);
            $pdfBig->Cell(30, 0, 'Gender', 0, false, 'L');

            $pdf->SetFont($Arial, '', 9, '', false);
            $pdf->SetXY($xStart, 46);
            $pdf->Cell(30, 0, 'Gender', 0, false, 'L');

            $pdfBig->SetFont($Arial, '', 9, '', false);
            $pdfBig->SetXY(41, 46);
            $pdfBig->Cell(0, 0, $certificateGender, 0, false, 'L');

            $pdf->SetFont($Arial, '', 9, '', false);
            $pdf->SetXY(41, 46);
            $pdf->Cell(0, 0, $certificateGender, 0, false, 'L');


            $pdfBig->SetFont($Arial, '', 9, '', false);
            $pdfBig->SetXY($xStart, 49);
            $pdfBig->Cell(30, 0, 'Date of Birth', 0, false, 'L');

            $pdf->SetFont($Arial, '', 9, '', false);
            $pdf->SetXY($xStart, 49);
            $pdf->Cell(30, 0, 'Date of Birth', 0, false, 'L');

            $pdfBig->SetFont($Arial, '', 9, '', false);
            $pdfBig->SetXY(41, 49);
            $pdfBig->Cell(0, 0, $certificateDOB, 0, false, 'L');

            $pdf->SetFont($Arial, '', 9, '', false);
            $pdf->SetXY(41, 49);
            $pdf->Cell(0, 0, $certificateDOB, 0, false, 'L');


            $pdfBig->SetFont($Arial, '', 9, '', false);
            $pdfBig->SetXY($xStart, 52);
            $pdfBig->SetTextColor(0,0,0);
            $pdfBig->Cell(30, 0, 'Nationality', 0, false, 'L');

            $pdf->SetFont($Arial, '', 9, '', false);
            $pdf->SetXY($xStart, 52);
            $pdf->SetTextColor(0,0,0);
            $pdf->Cell(30, 0, 'Nationality', 0, false, 'L');


            $pdfBig->SetFont($Arial, '', 9, '', false);
            $pdfBig->SetXY(41, 52);
            $pdfBig->Cell(0, 0, $certificateNationality, 0, false, 'L');

            $pdf->SetFont($Arial, '', 9, '', false);
            $pdf->SetXY(41, 52);
            $pdf->Cell(0, 0, $certificateNationality, 0, false, 'L');

            // second part right top heading
            $pdfBig->SetFont($Arial, '', 9, '', false);
            $pdfBig->SetXY(110, 42);
            $pdfBig->SetTextColor(0,0,0);
            $pdfBig->Cell(30, 0, 'Student No.', 0, false, 'L');

            $pdf->SetFont($Arial, '', 9, '', false);
            $pdf->SetXY(110, 42);
            $pdf->SetTextColor(0,0,0);
            $pdf->Cell(30, 0, 'Student No.', 0, false, 'L');


            $pdfBig->SetFont($Arial, '', 9, '', false);
            $pdfBig->SetXY(132, 42);
            $pdfBig->Cell(0, 0, $certificateRegNo, 0, false, 'L');

            $pdf->SetFont($Arial, '', 9, '', false);
            $pdf->SetXY(132, 42);
            $pdf->Cell(0, 0, $certificateRegNo, 0, false, 'L');



            $pdfBig->SetFont($Arial, '', 9, '', false);
            $pdfBig->SetXY(110, 46);
            $pdfBig->Cell(30, 0, 'Programme', 0, false, 'L');

            $pdf->SetFont($Arial, '', 9, '', false);
            $pdf->SetXY(110, 46);
            $pdf->Cell(30, 0, 'Programme', 0, false, 'L');


            $pdfBig->SetFont($Arial, '', 9, '', false);
            $pdfBig->SetXY(132, 46);
            $pdfBig->Cell(0, 0, $certificateProgramme, 0, false, 'L');

            $pdf->SetFont($Arial, '', 9, '', false);
            $pdf->SetXY(132, 46);
            $pdf->Cell(0, 0, $certificateProgramme, 0, false, 'L');


            $pdfBig->SetFont($Arial, '', 9, '', false);
            $pdfBig->SetXY(110, 49);
            $pdfBig->Cell(30, 0, 'School', 0, false, 'L');

            $pdf->SetFont($Arial, '', 9, '', false);
            $pdf->SetXY(110, 49);
            $pdf->Cell(30, 0, 'School', 0, false, 'L');


            $pdfBig->SetFont($Arial, '', 9, '', false);
            $pdfBig->SetXY(132, 49);
            $pdfBig->Cell(0, 0, $certificateSchool, 0, false, 'L');


            $pdf->SetFont($Arial, '', 9, '', false);
            $pdf->SetXY(132, 49);
            $pdf->Cell(0, 0, $certificateSchool, 0, false, 'L');


            $pdfBig->SetFont($Arial, '', 9, '', false);
            $pdfBig->SetXY(110, 52);
            $pdfBig->SetTextColor(0,0,0);
            $pdfBig->Cell(30, 0, 'Year of Entry', 0, false, 'L');

            $pdf->SetFont($Arial, '', 9, '', false);
            $pdf->SetXY(110, 52);
            $pdf->SetTextColor(0,0,0);
            $pdf->Cell(30, 0, 'Year of Entry', 0, false, 'L');


            $pdfBig->SetFont($Arial, '', 9, '', false);
            $pdfBig->SetXY(132, 52);
            $pdfBig->Cell(0, 0, $certificateYearEntry, 0, false, 'L');

            $pdf->SetFont($Arial, '', 9, '', false);
            $pdf->SetXY(132, 52);
            $pdf->Cell(0, 0, $certificateYearEntry, 0, false, 'L');


            // start foundation year table
            $tableY = 56;
            $pdfBig->SetFont($Arial, 'B', 7, '', false);
            $pdfBig->SetXY($xStart, $tableY);
            $pdfBig->SetTextColor(255,255,255);
            $pdfBig->SetFillColor(24,57,106);
            $pdfBig->MultiCell($pageWidth, 4, 'FOUNDATION YEAR', '', 'C', 1, 0);

            $pdf->SetFont($Arial, 'B', 7, '', false);
            $pdf->SetXY($xStart, $tableY);
            $pdf->SetTextColor(255,255,255);
            $pdf->SetFillColor(24,57,106);
            $pdf->MultiCell($pageWidth, 4, 'FOUNDATION YEAR', '', 'C', 1, 0);



            $tableY = $tableY+4;
            $pdfBig->SetFont($Arial, 'B', 7, '', false);
            $pdfBig->SetXY($xStart, $tableY);
            $pdfBig->SetFillColor(216, 204, 201 );
            $pdfBig->MultiCell($pageWidth, 4.5, '', '', 'C', 1, 0);

            $pdf->SetFont($Arial, 'B', 7, '', false);
            $pdf->SetXY($xStart, $tableY);
            $pdf->SetFillColor(216, 204, 201 );
            $pdf->MultiCell($pageWidth, 4.5, '', '', 'C', 1, 0);


            $tableY = $tableY+4.5;

            $tableX = 21;
            $pdfBig->setCellPaddings( $left = '1', $top = '0.5', $right = '', $bottom = '');
            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($tableX, $tableY);
            $pdfBig->SetTextColor(0,0,0);
            $pdfBig->SetFillColor(255,255,255);
            $pdfBig->MultiCell(19, 7, 'Module Code', 'TBRL', 'L', 1, 0);
            
            $pdf->setCellPaddings( $left = '1', $top = '0.5', $right = '', $bottom = '');
            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($tableX, $tableY);
            $pdf->SetTextColor(0,0,0);
            $pdf->SetFillColor(255,255,255);
            $pdf->MultiCell(19, 7, 'Module Code', 'TBRL', 'L', 1, 0);

            $tableX = $tableX +19;
            
            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($tableX, $tableY);
            $pdfBig->MultiCell(116, 7, 'Module Name', 'TBRL', 'L', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($tableX, $tableY);
            $pdf->MultiCell(116, 7, 'Module Name', 'TBRL', 'L', 1, 0);

            $tableX = $tableX +116;

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($tableX, $tableY);
            $pdfBig->MultiCell(19, 7, 'Grade', 'TBRL', 'C', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($tableX, $tableY);
            $pdf->MultiCell(19, 7, 'Grade', 'TBRL', 'C', 1, 0);


            $tableX = $tableX +19;

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($tableX, $tableY);
            $pdfBig->MultiCell(24, 7, 'Remark', 'TBRL', 'C', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($tableX, $tableY);
            $pdf->MultiCell(24, 7, 'Remark', 'TBRL', 'C', 1, 0);


            $foundationX = 21;
            $foundationY = $tableY +7;
            $tableHeight= 4;
            $pdfBig->setCellPaddings( $left = '', $top = '0.5', $right = '', $bottom = '');
            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($foundationX, $foundationY);
            $pdfBig->MultiCell(19, $tableHeight, $foundationCourseCode1, 'TBRL', 'C', 1, 0);

            $pdf->setCellPaddings( $left = '', $top = '0.5', $right = '', $bottom = '');
            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($foundationX, $foundationY);
            $pdf->MultiCell(19, $tableHeight, $foundationCourseCode1, 'TBRL', 'C', 1, 0);

            $foundationX = $foundationX +19;

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($foundationX, $foundationY);
            $pdfBig->MultiCell(116, $tableHeight, $foundationCourseName1, 'TBRL', 'L', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($foundationX, $foundationY);
            $pdf->MultiCell(116, $tableHeight, $foundationCourseName1, 'TBRL', 'L', 1, 0);
            $foundationX = $foundationX +116;

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($foundationX, $foundationY);
            $pdfBig->MultiCell(19, $tableHeight,  $foundationGrade1, 'TBRL', 'C', 1, 0);
            
            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($foundationX, $foundationY);
            $pdf->MultiCell(19, $tableHeight,  $foundationGrade1, 'TBRL', 'C', 1, 0);

            $foundationX = $foundationX +19;

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($foundationX, $foundationY);
            $pdfBig->MultiCell(24, $tableHeight, $foundationRemark1, 'TBRL', 'L', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($foundationX, $foundationY);
            $pdf->MultiCell(24, $tableHeight, $foundationRemark1, 'TBRL', 'L', 1, 0);

            

            $foundationX = 21;
            $foundationY = $foundationY +$tableHeight;
            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($foundationX, $foundationY);
            $pdfBig->MultiCell(19, $tableHeight, $foundationCourseCode2, 'TBRL', 'C', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($foundationX, $foundationY);
            $pdf->MultiCell(19, $tableHeight, $foundationCourseCode2, 'TBRL', 'C', 1, 0);

            $foundationX = $foundationX +19;

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($foundationX, $foundationY);
            $pdfBig->MultiCell(116, $tableHeight, $foundationCourseName2, 'TBRL', 'L', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($foundationX, $foundationY);
            $pdf->MultiCell(116, $tableHeight, $foundationCourseName2, 'TBRL', 'L', 1, 0);

            $foundationX = $foundationX +116;

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($foundationX, $foundationY);
            $pdfBig->MultiCell(19, $tableHeight, $foundationGrade2, 'TBRL', 'C', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($foundationX, $foundationY);
            $pdf->MultiCell(19, $tableHeight, $foundationGrade2, 'TBRL', 'C', 1, 0);

            $foundationX = $foundationX +19;

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($foundationX, $foundationY);
            $pdfBig->MultiCell(24, $tableHeight, $foundationRemark2, 'TBRL', 'L', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($foundationX, $foundationY);
            $pdf->MultiCell(24, $tableHeight, $foundationRemark2, 'TBRL', 'L', 1, 0);
            

            $foundationX = 21;
            $foundationY = $foundationY +$tableHeight;
            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($foundationX, $foundationY);
            $pdfBig->MultiCell(19, $tableHeight, $foundationCourseCode3, 'TBRL', 'C', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($foundationX, $foundationY);
            $pdf->MultiCell(19, $tableHeight, $foundationCourseCode3, 'TBRL', 'C', 1, 0);
            $foundationX = $foundationX +19;

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($foundationX, $foundationY);
            $pdfBig->MultiCell(116, $tableHeight, $foundationCourseName3, 'TBRL', 'L', 1, 0);
            
            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($foundationX, $foundationY);
            $pdf->MultiCell(116, $tableHeight, $foundationCourseName3, 'TBRL', 'L', 1, 0);

            $foundationX = $foundationX +116;


            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($foundationX, $foundationY);
            $pdfBig->MultiCell(19, $tableHeight, $foundationGrade3, 'TBRL', 'C', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($foundationX, $foundationY);
            $pdf->MultiCell(19, $tableHeight, $foundationGrade3, 'TBRL', 'C', 1, 0);

            $foundationX = $foundationX +19;

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($foundationX, $foundationY);
            $pdfBig->MultiCell(24, $tableHeight, $foundationRemark3, 'TBRL', 'L', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($foundationX, $foundationY);
            $pdf->MultiCell(24, $tableHeight, $foundationRemark3, 'TBRL', 'L', 1, 0);

            $foundationY = $foundationY +$tableHeight;

            $foundationX = 21;
            for($i=0; $i<4; $i++) {

                $pdfBig->SetFont($Arial, 'B', 7, '', false);
                $pdfBig->SetXY($foundationX, $foundationY);
                $pdfBig->MultiCell(19, $tableHeight, '', 'TBRL', 'L', 1, 0);

                $pdf->SetFont($Arial, 'B', 7, '', false);
                $pdf->SetXY($foundationX, $foundationY);
                $pdf->MultiCell(19, $tableHeight, '', 'TBRL', 'L', 1, 0);
                $foundationX = $foundationX+19;

                $pdfBig->SetFont($Arial, 'B', 7, '', false);
                $pdfBig->SetXY($foundationX, $foundationY);
                $pdfBig->MultiCell(116, $tableHeight,'', 'TBRL', 'L', 1, 0);
                
                $pdf->SetFont($Arial, 'B', 7, '', false);
                $pdf->SetXY($foundationX, $foundationY);
                $pdf->MultiCell(116, $tableHeight,'', 'TBRL', 'L', 1, 0);
                $foundationX = $foundationX+116;

                $pdfBig->SetFont($Arial, 'B', 7, '', false);
                $pdfBig->SetXY($foundationX, $foundationY);
                $pdfBig->MultiCell(19, $tableHeight, '', 'TBRL', 'C', 1, 0);

                $pdf->SetFont($Arial, 'B', 7, '', false);
                $pdf->SetXY($foundationX, $foundationY);
                $pdf->MultiCell(19, $tableHeight, '', 'TBRL', 'C', 1, 0);
                $foundationX = $foundationX+19;

                $pdfBig->SetFont($Arial, 'B', 7, '', false);
                $pdfBig->SetXY($foundationX, $foundationY);
                $pdfBig->MultiCell(24, $tableHeight, '', 'TBRL', 'C', 1, 0);

                $pdf->SetFont($Arial, 'B', 7, '', false);
                $pdf->SetXY($foundationX, $foundationY);
                $pdf->MultiCell(24, $tableHeight, '', 'TBRL', 'C', 1, 0);
                $foundationY = $foundationY +$tableHeight;
                $foundationX = 21;
            }
            // end foundation year table

            // start year one table
            $tableX = 21;
            $tableY = $foundationY;
            $pdfBig->SetFont($Arial, 'B', 7, '', false);
            $pdfBig->SetXY($tableX, $tableY);
            $pdfBig->SetTextColor(255,255,255);
            $pdfBig->SetFillColor(19,57,106);
            $pdfBig->MultiCell($pageWidth, 4, 'YEAR ONE', '', 'C', 1, 0);

            $pdf->SetFont($Arial, 'B', 7, '', false);
            $pdf->SetXY($tableX, $tableY);
            $pdf->SetTextColor(255,255,255);
            $pdf->SetFillColor(19,57,106);
            $pdf->MultiCell($pageWidth, 4, 'YEAR ONE', '', 'C', 1, 0);


            $tableY = $tableY+4;
            $pdfBig->SetFont($Arial, 'B', 7, '', false);
            $pdfBig->SetXY($tableX, $tableY);
            $pdfBig->SetFillColor(216, 204, 201 );
            $pdfBig->MultiCell($pageWidth, 4.5, '', '', 'C', 1, 0);

            $pdf->SetFont($Arial, 'B', 7, '', false);
            $pdf->SetXY($tableX, $tableY);
            $pdf->SetFillColor(216, 204, 201 );
            $pdf->MultiCell($pageWidth, 4.5, '', '', 'C', 1, 0);


            $tableY = $tableY+4.5;
            $pdfBig->setCellPaddings( $left = '', $top = '0.5', $right = '', $bottom = '');
            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($tableX, $tableY);
            $pdfBig->SetTextColor(0,0,0);
            $pdfBig->SetFillColor(255,255,255);
            $pdfBig->MultiCell(19, 7, 'Module Code', 'TBRL', 'L', 1, 0);

            $pdf->setCellPaddings( $left = '', $top = '0.5', $right = '', $bottom = '');
            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($tableX, $tableY);
            $pdf->SetTextColor(0,0,0);
            $pdf->SetFillColor(255,255,255);
            $pdf->MultiCell(19, 7, 'Module Code', 'TBRL', 'L', 1, 0);
            $tableX = $tableX + 19;

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($tableX, $tableY);
            $pdfBig->MultiCell(116, 7, 'Module Name', 'TBRL', 'L', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($tableX, $tableY);
            $pdf->MultiCell(116, 7, 'Module Name', 'TBRL', 'L', 1, 0);
            $tableX = $tableX+116;


            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($tableX, $tableY);
            $pdfBig->MultiCell(19, 7, 'Grade', 'TBRL', 'C', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($tableX, $tableY);
            $pdf->MultiCell(19, 7, 'Grade', 'TBRL', 'C', 1, 0);

            $tableX = $tableX+19;

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($tableX, $tableY);
            $pdfBig->MultiCell(24, 7, 'Remark', 'TBRL', 'C', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($tableX, $tableY);
            $pdf->MultiCell(24, 7, 'Remark', 'TBRL', 'C', 1, 0);

            
            $year1X= 21;
            $year1Y = $tableY +7;
            $pdfBig->setCellPaddings( $left = '', $top = '0.5', $right = '', $bottom = '');
            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year1X, $year1Y);
            $pdfBig->MultiCell(19, $tableHeight, $year1CourseCode1, 'TBRL', 'C', 1, 0);

            $pdf->setCellPaddings( $left = '', $top = '0.5', $right = '', $bottom = '');
            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year1X, $year1Y);
            $pdf->MultiCell(19, $tableHeight, $year1CourseCode1, 'TBRL', 'C', 1, 0);

            $year1X = $year1X+19;

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year1X, $year1Y);
            $pdfBig->MultiCell(116, $tableHeight, $year1CourseName1, 'TBRL', 'L', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year1X, $year1Y);
            $pdf->MultiCell(116, $tableHeight, $year1CourseName1, 'TBRL', 'L', 1, 0);
            $year1X = $year1X+116;

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year1X, $year1Y);
            $pdfBig->MultiCell(19, $tableHeight,  $year1Grade1, 'TBRL', 'C', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year1X, $year1Y);
            $pdf->MultiCell(19, $tableHeight,  $year1Grade1, 'TBRL', 'C', 1, 0);
            $year1X = $year1X+19;

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year1X, $year1Y);
            $pdfBig->MultiCell(24, $tableHeight, $year1Remark1, 'TBRL', 'L', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year1X, $year1Y);
            $pdf->MultiCell(24, $tableHeight, $year1Remark1, 'TBRL', 'L', 1, 0);
            $year1X =21;
            $year1Y = $year1Y +$tableHeight;
            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year1X, $year1Y);
            $pdfBig->MultiCell(19, $tableHeight, $year1CourseCode2, 'TBRL', 'C', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year1X, $year1Y);
            $pdf->MultiCell(19, $tableHeight, $year1CourseCode2, 'TBRL', 'C', 1, 0);
            $year1X = $year1X+19;

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year1X, $year1Y);
            $pdfBig->MultiCell(116, $tableHeight, $year1CourseName2, 'TBRL', 'L', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year1X, $year1Y);
            $pdf->MultiCell(116, $tableHeight, $year1CourseName2, 'TBRL', 'L', 1, 0);
            $year1X = $year1X +116;

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year1X, $year1Y);
            $pdfBig->MultiCell(19, $tableHeight, $year1Grade2, 'TBRL', 'C', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year1X, $year1Y);
            $pdf->MultiCell(19, $tableHeight, $year1Grade2, 'TBRL', 'C', 1, 0);
            $year1X = $year1X +19;

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year1X, $year1Y);
            $pdfBig->MultiCell(24, $tableHeight, $year1Remark2, 'TBRL', 'L', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year1X, $year1Y);
            $pdf->MultiCell(24, $tableHeight, $year1Remark2, 'TBRL', 'L', 1, 0);
            $year1Y = $year1Y +$tableHeight;

            $year1X = 21;
            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year1X, $year1Y);
            $pdfBig->MultiCell(19, $tableHeight, $year1CourseCode3, 'TBRL', 'C', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year1X, $year1Y);
            $pdf->MultiCell(19, $tableHeight, $year1CourseCode3, 'TBRL', 'C', 1, 0);
            $year1X = $year1X+19;

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year1X, $year1Y);
            $pdfBig->MultiCell(116, $tableHeight, $year1CourseName3, 'TBRL', 'L', 1, 0);
            
            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year1X, $year1Y);
            $pdf->MultiCell(116, $tableHeight, $year1CourseName3, 'TBRL', 'L', 1, 0);

            $year1X = $year1X+116;

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year1X, $year1Y);
            $pdfBig->MultiCell(19, $tableHeight, $year1Grade3, 'TBRL', 'C', 1, 0);
            
            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year1X, $year1Y);
            $pdf->MultiCell(19, $tableHeight, $year1Grade3, 'TBRL', 'C', 1, 0);
            $year1X = $year1X+19;

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year1X, $year1Y);
            $pdfBig->MultiCell(24, $tableHeight, $year1Remark3, 'TBRL', 'L', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year1X, $year1Y);
            $pdf->MultiCell(24, $tableHeight, $year1Remark3, 'TBRL', 'L', 1, 0);

            $year1X = 21;
            $year1Y = $year1Y +$tableHeight;

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year1X, $year1Y);
            $pdfBig->MultiCell(19, $tableHeight, $year1CourseCode4, 'TBRL', 'C', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year1X, $year1Y);
            $pdf->MultiCell(19, $tableHeight, $year1CourseCode4, 'TBRL', 'C', 1, 0);
            $year1X = $year1X+19;

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year1X, $year1Y);
            $pdfBig->MultiCell(116, $tableHeight, $year1CourseName4, 'TBRL', 'L', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year1X, $year1Y);
            $pdf->MultiCell(116, $tableHeight, $year1CourseName4, 'TBRL', 'L', 1, 0);
            $year1X = $year1X+116;

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year1X, $year1Y);
            $pdfBig->MultiCell(19, $tableHeight, $year1Grade4, 'TBRL', 'C', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year1X, $year1Y);
            $pdf->MultiCell(19, $tableHeight, $year1Grade4, 'TBRL', 'C', 1, 0);
            $year1X = $year1X+19;

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year1X, $year1Y);
            $pdfBig->MultiCell(24, $tableHeight, $year1Remark4, 'TBRL', 'L', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year1X, $year1Y);
            $pdf->MultiCell(24, $tableHeight, $year1Remark4, 'TBRL', 'L', 1, 0);

            $year1X = 21;
            $year1Y = $year1Y +$tableHeight;
            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year1X, $year1Y);
            $pdfBig->MultiCell(19, $tableHeight, $year1CourseCode5, 'TBRL', 'C', 1, 0);
            
            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year1X, $year1Y);
            $pdf->MultiCell(19, $tableHeight, $year1CourseCode5, 'TBRL', 'C', 1, 0);
            $year1X = $year1X+19;


            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year1X, $year1Y);
            $pdfBig->MultiCell(116, $tableHeight, $year1CourseName5, 'TBRL', 'L', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year1X, $year1Y);
            $pdf->MultiCell(116, $tableHeight, $year1CourseName5, 'TBRL', 'L', 1, 0);
            $year1X = $year1X+116;

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year1X, $year1Y);
            $pdfBig->MultiCell(19, $tableHeight, $year1Grade5, 'TBRL', 'C', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year1X, $year1Y);
            $pdf->MultiCell(19, $tableHeight, $year1Grade5, 'TBRL', 'C', 1, 0);
            $year1X = $year1X+19;

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year1X, $year1Y);
            $pdfBig->MultiCell(24, $tableHeight, $year1Remark5, 'TBRL', 'L', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year1X, $year1Y);
            $pdf->MultiCell(24, $tableHeight, $year1Remark5, 'TBRL', 'L', 1, 0);
            $year1Y = $year1Y +$tableHeight;

            $year1X = 21;


            for($i=0; $i<2; $i++) {

                $pdfBig->SetFont($Arial, 'B', 6, '', false);
                $pdfBig->SetXY($year1X, $year1Y);
                $pdfBig->MultiCell(19, $tableHeight, '', 'TBRL', 'L', 1, 0);

                $pdf->SetFont($Arial, 'B', 6, '', false);
                $pdf->SetXY($year1X, $year1Y);
                $pdf->MultiCell(19, $tableHeight, '', 'TBRL', 'L', 1, 0);
                $year1X = $year1X+19;

                $pdfBig->SetFont($Arial, 'B', 6, '', false);
                $pdfBig->SetXY($year1X, $year1Y);
                $pdfBig->MultiCell(116, $tableHeight,'', 'TBRL', 'L', 1, 0);

                $pdf->SetFont($Arial, 'B', 6, '', false);
                $pdf->SetXY($year1X, $year1Y);
                $pdf->MultiCell(116, $tableHeight,'', 'TBRL', 'L', 1, 0);
                $year1X = $year1X+116;

                $pdfBig->SetFont($Arial, 'B', 6, '', false);
                $pdfBig->SetXY($year1X, $year1Y);
                $pdfBig->MultiCell(19, $tableHeight, '', 'TBRL', 'C', 1, 0);

                $pdf->SetFont($Arial, 'B', 6, '', false);
                $pdf->SetXY($year1X, $year1Y);
                $pdf->MultiCell(19, $tableHeight, '', 'TBRL', 'C', 1, 0);
                $year1X = $year1X+19;

                $pdfBig->SetFont($Arial, 'B', 6, '', false);
                $pdfBig->SetXY($year1X, $year1Y);
                $pdfBig->MultiCell(24, $tableHeight, '', 'TBRL', 'C', 1, 0);

                $pdf->SetFont($Arial, 'B', 6, '', false);
                $pdf->SetXY($year1X, $year1Y);
                $pdf->MultiCell(24, $tableHeight, '', 'TBRL', 'C', 1, 0);
                $year1X = 21;
                $year1Y = $year1Y +$tableHeight;
            }
            // end year one table



            // start year two table
            $tableX = 21;
            $tableY = $year1Y;
            $pdfBig->SetFont($Arial, 'B', 7, '', false);
            $pdfBig->SetXY($tableX, $tableY);
            $pdfBig->SetTextColor(255,255,255);
            $pdfBig->SetFillColor(19,57,106);
            $pdfBig->MultiCell($pageWidth, 4, 'YEAR TWO', '', 'C', 1, 0);
            
            $pdf->SetFont($Arial, 'B', 7, '', false);
            $pdf->SetXY($tableX, $tableY);
            $pdf->SetTextColor(255,255,255);
            $pdf->SetFillColor(19,57,106);
            $pdf->MultiCell($pageWidth, 4, 'YEAR TWO', '', 'C', 1, 0);
            $tableY = $tableY+4;

            $pdfBig->SetFont($Arial, 'B', 7, '', false);
            $pdfBig->SetXY($tableX, $tableY);
            $pdfBig->SetFillColor(216, 204, 201 );
            $pdfBig->MultiCell($pageWidth, 4.5, '', '', 'C', 1, 0);

            $pdf->SetFont($Arial, 'B', 7, '', false);
            $pdf->SetXY($tableX, $tableY);
            $pdf->SetFillColor(216, 204, 201 );
            $pdf->MultiCell($pageWidth, 4.5, '', '', 'C', 1, 0);

            $tableY = $tableY+4.5;
            $pdfBig->setCellPaddings( $left = '', $top = '0.5', $right = '', $bottom = '');
            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($tableX, $tableY);
            $pdfBig->SetTextColor(0,0,0);
            $pdfBig->SetFillColor(255,255,255);
            $pdfBig->MultiCell(19, 7, 'Module Code', 'TBRL', 'L', 1, 0);

            $pdf->setCellPaddings( $left = '', $top = '0.5', $right = '', $bottom = '');
            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($tableX, $tableY);
            $pdf->SetTextColor(0,0,0);
            $pdf->SetFillColor(255,255,255);
            $pdf->MultiCell(19, 7, 'Module Code', 'TBRL', 'L', 1, 0);
            $tableX = $tableX + 19;

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($tableX, $tableY);
            $pdfBig->MultiCell(116, 7, 'Module Name', 'TBRL', 'L', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($tableX, $tableY);
            $pdf->MultiCell(116, 7, 'Module Name', 'TBRL', 'L', 1, 0);
            $tableX = $tableX+116;


            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($tableX, $tableY);
            $pdfBig->MultiCell(19, 7, 'Grade', 'TBRL', 'C', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($tableX, $tableY);
            $pdf->MultiCell(19, 7, 'Grade', 'TBRL', 'C', 1, 0);
            $tableX = $tableX+19;

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($tableX, $tableY);
            $pdfBig->MultiCell(24, 7, 'Remark', 'TBRL', 'C', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($tableX, $tableY);
            $pdf->MultiCell(24, 7, 'Remark', 'TBRL', 'C', 1, 0);
            $year2X= 21;
            
            $year2Y = $tableY +7;
            $pdfBig->setCellPaddings( $left = '', $top = '0.5', $right = '', $bottom = '');
            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year2X, $year2Y);
            $pdfBig->MultiCell(19, $tableHeight, $year2CourseCode1, 'TBRL', 'C', 1, 0);

            $pdf->setCellPaddings( $left = '', $top = '0.5', $right = '', $bottom = '');
            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year2X, $year2Y);
            $pdf->MultiCell(19, $tableHeight, $year2CourseCode1, 'TBRL', 'C', 1, 0);
            $year2X = $year2X+19;

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year2X, $year2Y);
            $pdfBig->MultiCell(116, $tableHeight, $year2CourseName1, 'TBRL', 'L', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year2X, $year2Y);
            $pdf->MultiCell(116, $tableHeight, $year2CourseName1, 'TBRL', 'L', 1, 0);
            $year2X = $year2X+116;

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year2X, $year2Y);
            $pdfBig->MultiCell(19, $tableHeight,  $year2Grade1, 'TBRL', 'C', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year2X, $year2Y);
            $pdf->MultiCell(19, $tableHeight,  $year2Grade1, 'TBRL', 'C', 1, 0);
            $year2X = $year2X+19;

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year2X, $year2Y);
            $pdfBig->MultiCell(24, $tableHeight, $year2Remark1, 'TBRL', 'L', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year2X, $year2Y);
            $pdf->MultiCell(24, $tableHeight, $year2Remark1, 'TBRL', 'L', 1, 0);

            $year2X =21;
            $year2Y = $year2Y +$tableHeight;
            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year2X, $year2Y);
            $pdfBig->MultiCell(19, $tableHeight, $year2CourseCode2, 'TBRL', 'C', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year2X, $year2Y);
            $pdf->MultiCell(19, $tableHeight, $year2CourseCode2, 'TBRL', 'C', 1, 0);
            $year2X = $year2X+19;

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year2X, $year2Y);
            $pdfBig->MultiCell(116, $tableHeight, $year2CourseName2, 'TBRL', 'L', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year2X, $year2Y);
            $pdf->MultiCell(116, $tableHeight, $year2CourseName2, 'TBRL', 'L', 1, 0);
            $year2X = $year2X +116;

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year2X, $year2Y);
            $pdfBig->MultiCell(19, $tableHeight, $year2Grade2, 'TBRL', 'C', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year2X, $year2Y);
            $pdf->MultiCell(19, $tableHeight, $year2Grade2, 'TBRL', 'C', 1, 0);
            $year2X = $year2X +19;

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year2X, $year2Y);
            $pdfBig->MultiCell(24, $tableHeight, $year2Remark2, 'TBRL', 'L', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year2X, $year2Y);
            $pdf->MultiCell(24, $tableHeight, $year2Remark2, 'TBRL', 'L', 1, 0);
            $year2Y = $year2Y +$tableHeight;

            $year2X = 21;
            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year2X, $year2Y);
            $pdfBig->MultiCell(19, $tableHeight, $year2CourseCode3, 'TBRL', 'C', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year2X, $year2Y);
            $pdf->MultiCell(19, $tableHeight, $year2CourseCode3, 'TBRL', 'C', 1, 0);
            $year2X = $year2X+19;

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year2X, $year2Y);
            $pdfBig->MultiCell(116, $tableHeight, $year2CourseName3, 'TBRL', 'L', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year2X, $year2Y);
            $pdf->MultiCell(116, $tableHeight, $year2CourseName3, 'TBRL', 'L', 1, 0);
            $year2X = $year2X+116;

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year2X, $year2Y);
            $pdfBig->MultiCell(19, $tableHeight, $year2Grade3, 'TBRL', 'C', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year2X, $year2Y);
            $pdf->MultiCell(19, $tableHeight, $year2Grade3, 'TBRL', 'C', 1, 0);
            $year2X = $year2X+19;

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year2X, $year2Y);
            $pdfBig->MultiCell(24, $tableHeight, $year2Remark3, 'TBRL', 'L', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year2X, $year2Y);
            $pdf->MultiCell(24, $tableHeight, $year2Remark3, 'TBRL', 'L', 1, 0);

            $year2X = 21;
            $year2Y = $year2Y +$tableHeight;

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year2X, $year2Y);
            $pdfBig->MultiCell(19, $tableHeight, $year2CourseCode4, 'TBRL', 'C', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year2X, $year2Y);
            $pdf->MultiCell(19, $tableHeight, $year2CourseCode4, 'TBRL', 'C', 1, 0);
            $year2X = $year2X+19;

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year2X, $year2Y);
            $pdfBig->MultiCell(116, $tableHeight, $year2CourseName4, 'TBRL', 'L', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year2X, $year2Y);
            $pdf->MultiCell(116, $tableHeight, $year2CourseName4, 'TBRL', 'L', 1, 0);
            $year2X = $year2X+116;

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year2X, $year2Y);
            $pdfBig->MultiCell(19, $tableHeight, $year2Grade4, 'TBRL', 'C', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year2X, $year2Y);
            $pdf->MultiCell(19, $tableHeight, $year2Grade4, 'TBRL', 'C', 1, 0);
            $year2X = $year2X+19;

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year2X, $year2Y);
            $pdfBig->MultiCell(24, $tableHeight, $year2Remark4, 'TBRL', 'L', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year2X, $year2Y);
            $pdf->MultiCell(24, $tableHeight, $year2Remark4, 'TBRL', 'L', 1, 0);

            $year2X = 21;
            $year2Y = $year2Y +$tableHeight;
            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year2X, $year2Y);
            $pdfBig->MultiCell(19, $tableHeight, $year2CourseCode5, 'TBRL', 'C', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year2X, $year2Y);
            $pdf->MultiCell(19, $tableHeight, $year2CourseCode5, 'TBRL', 'C', 1, 0);
            $year2X = $year2X+19;

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year2X, $year2Y);
            $pdfBig->MultiCell(116, $tableHeight, $year2CourseName5, 'TBRL', 'L', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year2X, $year2Y);
            $pdf->MultiCell(116, $tableHeight, $year2CourseName5, 'TBRL', 'L', 1, 0);
            $year2X = $year2X+116;

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year2X, $year2Y);
            $pdfBig->MultiCell(19, $tableHeight, $year2Grade5, 'TBRL', 'C', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year2X, $year2Y);
            $pdf->MultiCell(19, $tableHeight, $year2Grade5, 'TBRL', 'C', 1, 0);
            $year2X = $year2X+19;

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year2X, $year2Y);
            $pdfBig->MultiCell(24, $tableHeight, $year2Remark5, 'TBRL', 'L', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year2X, $year2Y);
            $pdf->MultiCell(24, $tableHeight, $year2Remark5, 'TBRL', 'L', 1, 0);

            $year2X = 21;
            $year2Y = $year2Y +$tableHeight;
            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year2X, $year2Y);
            $pdfBig->MultiCell(19, $tableHeight, $year2CourseCode6, 'TBRL', 'C', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year2X, $year2Y);
            $pdf->MultiCell(19, $tableHeight, $year2CourseCode6, 'TBRL', 'C', 1, 0);
            $year2X = $year2X+19;

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year2X, $year2Y);
            $pdfBig->MultiCell(116, $tableHeight, $year2CourseName6, 'TBRL', 'L', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year2X, $year2Y);
            $pdf->MultiCell(116, $tableHeight, $year2CourseName6, 'TBRL', 'L', 1, 0);
            $year2X = $year2X+116;

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year2X, $year2Y);
            $pdfBig->MultiCell(19, $tableHeight, $year2Grade6, 'TBRL', 'C', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year2X, $year2Y);
            $pdf->MultiCell(19, $tableHeight, $year2Grade6, 'TBRL', 'C', 1, 0);
            $year2X = $year2X+19;

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year2X, $year2Y);
            $pdfBig->MultiCell(24, $tableHeight, $year2Remark6, 'TBRL', 'L', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year2X, $year2Y);
            $pdf->MultiCell(24, $tableHeight, $year2Remark6, 'TBRL', 'L', 1, 0);


            $year2X = 21;
            $year2Y = $year2Y +$tableHeight;

            for($i=0; $i<1; $i++) {

                $pdfBig->SetFont($Arial, 'B', 6, '', false);
                $pdfBig->SetXY($year2X, $year2Y);
                $pdfBig->MultiCell(19, $tableHeight, '', 'TBRL', 'L', 1, 0);

                $pdf->SetFont($Arial, 'B', 6, '', false);
                $pdf->SetXY($year2X, $year2Y);
                $pdf->MultiCell(19, $tableHeight, '', 'TBRL', 'L', 1, 0);
                $year2X = $year2X+19;

                $pdfBig->SetFont($Arial, 'B', 6, '', false);
                $pdfBig->SetXY($year2X, $year2Y);
                $pdfBig->MultiCell(116, $tableHeight,'', 'TBRL', 'L', 1, 0);

                $pdf->SetFont($Arial, 'B', 6, '', false);
                $pdf->SetXY($year2X, $year2Y);
                $pdf->MultiCell(116, $tableHeight,'', 'TBRL', 'L', 1, 0);
                $year2X = $year2X+116;

                $pdfBig->SetFont($Arial, 'B', 6, '', false);
                $pdfBig->SetXY($year2X, $year2Y);
                $pdfBig->MultiCell(19, $tableHeight, '', 'TBRL', 'C', 1, 0);

                $pdf->SetFont($Arial, 'B', 6, '', false);
                $pdf->SetXY($year2X, $year2Y);
                $pdf->MultiCell(19, $tableHeight, '', 'TBRL', 'C', 1, 0);
                $year2X = $year2X+19;

                $pdfBig->SetFont($Arial, 'B', 6, '', false);
                $pdfBig->SetXY($year2X, $year2Y);
                $pdfBig->MultiCell(24, $tableHeight, '', 'TBRL', 'C', 1, 0);

                $pdf->SetFont($Arial, 'B', 6, '', false);
                $pdf->SetXY($year2X, $year2Y);
                $pdf->MultiCell(24, $tableHeight, '', 'TBRL', 'C', 1, 0);
                $year2X = 21;
                $year2Y = $year2Y +$tableHeight;
            }
            // end year two table



            // start year three table
            $tableX = 21;
            $tableY = $year2Y;
            $pdfBig->SetFont($Arial, 'B', 7, '', false);
            $pdfBig->SetXY($tableX, $tableY);
            $pdfBig->SetTextColor(255,255,255);
            $pdfBig->SetFillColor(19,57,106);
            $pdfBig->MultiCell($pageWidth, 4, 'YEAR THREE', '', 'C', 1, 0);

            $pdf->SetFont($Arial, 'B', 7, '', false);
            $pdf->SetXY($tableX, $tableY);
            $pdf->SetTextColor(255,255,255);
            $pdf->SetFillColor(19,57,106);
            $pdf->MultiCell($pageWidth, 4, 'YEAR THREE', '', 'C', 1, 0);

            $tableY = $tableY+4;
            $pdfBig->SetFont($Arial, 'B', 7, '', false);
            $pdfBig->SetXY($tableX, $tableY);
            $pdfBig->SetFillColor(216, 204, 201 );
            $pdfBig->MultiCell($pageWidth, 4.5, '', '', 'C', 1, 0);

            $pdf->SetFont($Arial, 'B', 7, '', false);
            $pdf->SetXY($tableX, $tableY);
            $pdf->SetFillColor(216, 204, 201 );
            $pdf->MultiCell($pageWidth, 4.5, '', '', 'C', 1, 0);

            $tableY = $tableY+4.5;
            $pdfBig->setCellPaddings( $left = '', $top = '0.5', $right = '', $bottom = '');
            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($tableX, $tableY);
            $pdfBig->SetTextColor(0,0,0);
            $pdfBig->SetFillColor(255,255,255);
            $pdfBig->MultiCell(19, 7, 'Module Code', 'TBRL', 'L', 1, 0);

            $pdf->setCellPaddings( $left = '', $top = '0.5', $right = '', $bottom = '');
            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($tableX, $tableY);
            $pdf->SetTextColor(0,0,0);
            $pdf->SetFillColor(255,255,255);
            $pdf->MultiCell(19, 7, 'Module Code', 'TBRL', 'L', 1, 0);
            $tableX = $tableX + 19;

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($tableX, $tableY);
            $pdfBig->MultiCell(116, 7, 'Module Name', 'TBRL', 'L', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($tableX, $tableY);
            $pdf->MultiCell(116, 7, 'Module Name', 'TBRL', 'L', 1, 0);
            $tableX = $tableX+116;


            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($tableX, $tableY);
            $pdfBig->MultiCell(19, 7, 'Grade', 'TBRL', 'C', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($tableX, $tableY);
            $pdf->MultiCell(19, 7, 'Grade', 'TBRL', 'C', 1, 0);
            $tableX = $tableX+19;

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($tableX, $tableY);
            $pdfBig->MultiCell(24, 7, 'Remark', 'TBRL', 'C', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($tableX, $tableY);
            $pdf->MultiCell(24, 7, 'Remark', 'TBRL', 'C', 1, 0);
            
            $year3X= 21;
            $year3Y = $tableY +7;
            $pdfBig->setCellPaddings( $left = '', $top = '0.5', $right = '', $bottom = '');
            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year3X, $year3Y);
            $pdfBig->MultiCell(19, $tableHeight, $year3CourseCode1, 'TBRL', 'C', 1, 0);

            $pdf->setCellPaddings( $left = '', $top = '0.5', $right = '', $bottom = '');
            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year3X, $year3Y);
            $pdf->MultiCell(19, $tableHeight, $year3CourseCode1, 'TBRL', 'C', 1, 0);
            $year3X = $year3X+19;

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year3X, $year3Y);
            $pdfBig->MultiCell(116, $tableHeight, $year3CourseName1, 'TBRL', 'L', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year3X, $year3Y);
            $pdf->MultiCell(116, $tableHeight, $year3CourseName1, 'TBRL', 'L', 1, 0);
            $year3X = $year3X+116;

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year3X, $year3Y);
            $pdfBig->MultiCell(19, $tableHeight,  $year3Grade1, 'TBRL', 'C', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year3X, $year3Y);
            $pdf->MultiCell(19, $tableHeight,  $year3Grade1, 'TBRL', 'C', 1, 0);
            $year3X = $year3X+19;

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year3X, $year3Y);
            $pdfBig->MultiCell(24, $tableHeight, $year3Remark1, 'TBRL', 'L', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year3X, $year3Y);
            $pdf->MultiCell(24, $tableHeight, $year3Remark1, 'TBRL', 'L', 1, 0);

            $year3X =21;
            $year3Y = $year3Y +$tableHeight;
            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year3X, $year3Y);
            $pdfBig->MultiCell(19, $tableHeight, $year3CourseCode2, 'TBRL', 'C', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year3X, $year3Y);
            $pdf->MultiCell(19, $tableHeight, $year3CourseCode2, 'TBRL', 'C', 1, 0);
            $year3X = $year3X+19;

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year3X, $year3Y);
            $pdfBig->MultiCell(116, $tableHeight, $year3CourseName2, 'TBRL', 'L', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year3X, $year3Y);
            $pdf->MultiCell(116, $tableHeight, $year3CourseName2, 'TBRL', 'L', 1, 0);
            $year3X = $year3X +116;

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year3X, $year3Y);
            $pdfBig->MultiCell(19, $tableHeight, $year3Grade2, 'TBRL', 'C', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year3X, $year3Y);
            $pdf->MultiCell(19, $tableHeight, $year3Grade2, 'TBRL', 'C', 1, 0);
            $year3X = $year3X +19;

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year3X, $year3Y);
            $pdfBig->MultiCell(24, $tableHeight, $year3Remark2, 'TBRL', 'L', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year3X, $year3Y);
            $pdf->MultiCell(24, $tableHeight, $year3Remark2, 'TBRL', 'L', 1, 0);
            $year3Y = $year3Y +$tableHeight;

            $year3X = 21;
            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year3X, $year3Y);
            $pdfBig->MultiCell(19, $tableHeight, $year3CourseCode3, 'TBRL', 'C', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year3X, $year3Y);
            $pdf->MultiCell(19, $tableHeight, $year3CourseCode3, 'TBRL', 'C', 1, 0);
            $year3X = $year3X+19;

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year3X, $year3Y);
            $pdfBig->MultiCell(116, $tableHeight, $year3CourseName3, 'TBRL', 'L', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year3X, $year3Y);
            $pdf->MultiCell(116, $tableHeight, $year3CourseName3, 'TBRL', 'L', 1, 0);
            $year3X = $year3X+116;

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year3X, $year3Y);
            $pdfBig->MultiCell(19, $tableHeight, $year3Grade3, 'TBRL', 'C', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year3X, $year3Y);
            $pdf->MultiCell(19, $tableHeight, $year3Grade3, 'TBRL', 'C', 1, 0);
            $year3X = $year3X+19;

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year3X, $year3Y);
            $pdfBig->MultiCell(24, $tableHeight, $year3Remark3, 'TBRL', 'L', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year3X, $year3Y);
            $pdf->MultiCell(24, $tableHeight, $year3Remark3, 'TBRL', 'L', 1, 0);

            $year3X = 21;
            $year3Y = $year3Y +$tableHeight;

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year3X, $year3Y);
            $pdfBig->MultiCell(19, $tableHeight, $year3CourseCode4, 'TBRL', 'C', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year3X, $year3Y);
            $pdf->MultiCell(19, $tableHeight, $year3CourseCode4, 'TBRL', 'C', 1, 0);
            $year3X = $year3X+19;

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year3X, $year3Y);
            $pdfBig->MultiCell(116, $tableHeight, $year3CourseName4, 'TBRL', 'L', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year3X, $year3Y);
            $pdf->MultiCell(116, $tableHeight, $year3CourseName4, 'TBRL', 'L', 1, 0);
            $year3X = $year3X+116;

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year3X, $year3Y);
            $pdfBig->MultiCell(19, $tableHeight, $year3Grade4, 'TBRL', 'C', 1, 0);
            
            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year3X, $year3Y);
            $pdf->MultiCell(19, $tableHeight, $year3Grade4, 'TBRL', 'C', 1, 0);
            $year3X = $year3X+19;

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year3X, $year3Y);
            $pdfBig->MultiCell(24, $tableHeight, $year3Remark4, 'TBRL', 'L', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year3X, $year3Y);
            $pdf->MultiCell(24, $tableHeight, $year3Remark4, 'TBRL', 'L', 1, 0);

            $year3X = 21;
            $year3Y = $year3Y +$tableHeight;
            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year3X, $year3Y);
            $pdfBig->MultiCell(19, $tableHeight, $year3CourseCode5, 'TBRL', 'C', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year3X, $year3Y);
            $pdf->MultiCell(19, $tableHeight, $year3CourseCode5, 'TBRL', 'C', 1, 0);
            $year3X = $year3X+19;

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year3X, $year3Y);
            $pdfBig->MultiCell(116, $tableHeight, $year3CourseName5, 'TBRL', 'L', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year3X, $year3Y);
            $pdf->MultiCell(116, $tableHeight, $year3CourseName5, 'TBRL', 'L', 1, 0);
            $year3X = $year3X+116;

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year3X, $year3Y);
            $pdfBig->MultiCell(19, $tableHeight, $year3Grade5, 'TBRL', 'C', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year3X, $year3Y);
            $pdf->MultiCell(19, $tableHeight, $year3Grade5, 'TBRL', 'C', 1, 0);
            $year3X = $year3X+19;

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year3X, $year3Y);
            $pdfBig->MultiCell(24, $tableHeight, $year3Remark5, 'TBRL', 'L', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year3X, $year3Y);
            $pdf->MultiCell(24, $tableHeight, $year3Remark5, 'TBRL', 'L', 1, 0);


            $year3X = 21;
            $year3Y = $year3Y +$tableHeight;
            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year3X, $year3Y);
            $pdfBig->MultiCell(19, $tableHeight, $year3CourseCode6, 'TBRL', 'C', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year3X, $year3Y);
            $pdf->MultiCell(19, $tableHeight, $year3CourseCode6, 'TBRL', 'C', 1, 0);
            $year3X = $year3X+19;

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year3X, $year3Y);
            $pdfBig->MultiCell(116, $tableHeight, $year3CourseName6, 'TBRL', 'L', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year3X, $year3Y);
            $pdf->MultiCell(116, $tableHeight, $year3CourseName6, 'TBRL', 'L', 1, 0);
            $year3X = $year3X+116;

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year3X, $year3Y);
            $pdfBig->MultiCell(19, $tableHeight, $year3Grade6, 'TBRL', 'C', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year3X, $year3Y);
            $pdf->MultiCell(19, $tableHeight, $year3Grade6, 'TBRL', 'C', 1, 0);
            $year3X = $year3X+19;

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year3X, $year3Y);
            $pdfBig->MultiCell(24, $tableHeight, $year3Remark6, 'TBRL', 'L', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year3X, $year3Y);
            $pdf->MultiCell(24, $tableHeight, $year3Remark6, 'TBRL', 'L', 1, 0);



            $year3X = 21;
            $year3Y = $year3Y +$tableHeight;

            for($i=0; $i<3; $i++) {

                $pdfBig->SetFont($Arial, 'B', 6, '', false);
                $pdfBig->SetXY($year3X, $year3Y);
                $pdfBig->MultiCell(19, $tableHeight, '', 'TBRL', 'L', 1, 0);

                $pdf->SetFont($Arial, 'B', 6, '', false);
                $pdf->SetXY($year3X, $year3Y);
                $pdf->MultiCell(19, $tableHeight, '', 'TBRL', 'L', 1, 0);
                $year3X = $year3X+19;

                $pdfBig->SetFont($Arial, 'B', 6, '', false);
                $pdfBig->SetXY($year3X, $year3Y);
                $pdfBig->MultiCell(116, $tableHeight,'', 'TBRL', 'L', 1, 0);

                $pdf->SetFont($Arial, 'B', 6, '', false);
                $pdf->SetXY($year3X, $year3Y);
                $pdf->MultiCell(116, $tableHeight,'', 'TBRL', 'L', 1, 0);
                $year3X = $year3X+116;

                $pdfBig->SetFont($Arial, 'B', 6, '', false);
                $pdfBig->SetXY($year3X, $year3Y);
                $pdfBig->MultiCell(19, $tableHeight, '', 'TBRL', 'C', 1, 0);

                $pdf->SetFont($Arial, 'B', 6, '', false);
                $pdf->SetXY($year3X, $year3Y);
                $pdf->MultiCell(19, $tableHeight, '', 'TBRL', 'C', 1, 0);
                $year3X = $year3X+19;

                $pdfBig->SetFont($Arial, 'B', 6, '', false);
                $pdfBig->SetXY($year3X, $year3Y);
                $pdfBig->MultiCell(24, $tableHeight, '', 'TBRL', 'C', 1, 0);

                $pdf->SetFont($Arial, 'B', 6, '', false);
                $pdf->SetXY($year3X, $year3Y);
                $pdf->MultiCell(24, $tableHeight, '', 'TBRL', 'C', 1, 0);
                $year3X = 21;
                $year3Y = $year3Y +$tableHeight;
            }
            // end year three table

            $bottomSecY = $year3Y-0.5;
            // echo $year3Y+3.6;
            $pdfBig->setCellPaddings( $left = '0', $top = '0.5', $right = '', $bottom = '');
            $pdfBig->SetFont($Arial, '', 6, '', false);
            $pdfBig->SetXY($xStart, $bottomSecY);
            $pdfBig->MultiCell(11, 0, 'AWARD : ', '', 'L', 0, 0);

            $pdf->setCellPaddings( $left = '0', $top = '0.5', $right = '', $bottom = '');
            $pdf->SetFont($Arial, '', 6, '', false);
            $pdf->SetXY($xStart, $bottomSecY);
            $pdf->MultiCell(11, 0, 'AWARD : ', '', 'L', 0, 0);


            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($xStart+10, $bottomSecY);
            $pdfBig->MultiCell(121, 0, $certificateAward, '', 'L', 0, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($xStart+10, $bottomSecY);
            $pdf->MultiCell(121, 0, $certificateAward, '', 'L', 0, 0);

            $pdfBig->SetFont($Arial, '', 6, '', false);
            $pdfBig->SetXY($xStart+120, $bottomSecY);
            $pdfBig->MultiCell(30, 0, 'DATE OF COMPLETION :', '', 'L', 0, 0);

            $pdf->SetFont($Arial, '', 6, '', false);
            $pdf->SetXY($xStart+120, $bottomSecY);
            $pdf->MultiCell(30, 0, 'DATE OF COMPLETION :', '', 'L', 0, 0);

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($xStart+145, $bottomSecY);
            $pdfBig->MultiCell(30, 0, '15th October, 2022', '', 'L', 0, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($xStart+145, $bottomSecY);
            $pdf->MultiCell(30, 0, '15th October, 2022', '', 'L', 0, 0);


            $pdfBig->setCellPaddings( $left = '0', $top = '0.2', $right = '', $bottom = '');
            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($xStart, $bottomSecY+6);
            $pdfBig->SetTextColor(255,255,255);
            $pdfBig->SetFillColor(19,57,106);
            $pdfBig->MultiCell($pageWidth, 3, 'The medium of instruction is English. For key to grafes and remarks,see overleaf.', '', 'C', 1, 0);

            $pdf->setCellPaddings( $left = '0', $top = '0.2', $right = '', $bottom = '');
            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($xStart, $bottomSecY+6);
            $pdf->SetTextColor(255,255,255);
            $pdf->SetFillColor(19,57,106);
            $pdf->MultiCell($pageWidth, 3, 'The medium of instruction is English. For key to grafes and remarks,see overleaf.', '', 'C', 1, 0);

            $bottomSecY =$bottomSecY+6;

            $y01 = $bottomSecY+13;
            $pdfBig->SetFont($Arial, '', 6, '', false);
            $pdfBig->SetXY($xStart, $y01);
            $pdfBig->SetTextColor(0,0,0);
            $pdfBig->MultiCell(65, 0, '______________________________________________________', '', 'L', 0, 0);

            $pdf->SetFont($Arial, '', 6, '', false);
            $pdf->SetXY($xStart, $y01);
            $pdf->SetTextColor(0,0,0);
            $pdf->MultiCell(65, 0, '______________________________________________________', '', 'L', 0, 0);

            $y02 = $bottomSecY+16;
            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($xStart, $y02);
            $pdfBig->SetTextColor(0,0,0);
            $pdfBig->MultiCell(65, 0, 'Dean, School of Medicine', '', 'C', 0, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($xStart, $y02);
            $pdf->SetTextColor(0,0,0);
            $pdf->MultiCell(65, 0, 'Dean, School of Medicine', '', 'C', 0, 0);

            $y03 = $bottomSecY+19;
            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($xStart, $y03);
            $pdfBig->SetTextColor(0,0,0);
            $pdfBig->MultiCell(65, 0, 'CAVENDISH UNIVERSITY ZAMBIA', '', 'C', 0, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($xStart, $y03);
            $pdf->SetTextColor(0,0,0);
            $pdf->MultiCell(65, 0, 'CAVENDISH UNIVERSITY ZAMBIA', '', 'C', 0, 0);


            $y04 = $bottomSecY+14;
            $pdfBig->SetFont($Arial, '', 6, '', false);
            $pdfBig->SetXY(135, $y04);
            $pdfBig->SetTextColor(0,0,0);
            $pdfBig->MultiCell(65, 0, '______________________________________________________', '', 'R', 0, 0);

            $pdf->SetFont($Arial, '', 6, '', false);
            $pdf->SetXY(135, $y04);
            $pdf->SetTextColor(0,0,0);
            $pdf->MultiCell(65, 0, '______________________________________________________', '', 'R', 0, 0);



            $bY1 = $bottomSecY+17;
            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY(135, $bY1);
            $pdfBig->SetTextColor(0,0,0);
            $pdfBig->MultiCell(65, 0, 'Academic Registrar', '', 'C', 0, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY(135, $bY1);
            $pdf->SetTextColor(0,0,0);
            $pdf->MultiCell(65, 0, 'Academic Registrar', '', 'C', 0, 0);


            $bY2 = $bottomSecY+20;
            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY(135, $bY2);
            $pdfBig->SetTextColor(0,0,0);
            $pdfBig->MultiCell(65, 0, 'CAVENDISH UNIVERSITY ZAMBIA', '', 'C', 0, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY(135, $bY2);
            $pdf->SetTextColor(0,0,0);
            $pdf->MultiCell(65, 0, 'CAVENDISH UNIVERSITY ZAMBIA', '', 'C', 0, 0);


            $bY3= $bottomSecY+22;
            $pdfBig->SetFont($Arial, '', 7, '', false);
            $pdfBig->SetXY($xStart, $bY3);
            $pdfBig->SetTextColor(161,150,148);
            $pdfBig->MultiCell($pageWidth, 0, 'To verify document check Control Code at:', '', 'C', 0, 0);

            $pdf->SetFont($Arial, '', 7, '', false);
            $pdf->SetXY($xStart, $bY3);
            $pdf->SetTextColor(161,150,148);
            $pdf->MultiCell($pageWidth, 0, 'To verify document check Control Code at:', '', 'C', 0, 0);

            $bY4=$bottomSecY+24;
            $pdfBig->SetFont($Arial, '', 7, '', false);
            $pdfBig->SetXY($xStart, $bY4);
            $pdfBig->SetTextColor(161,150,148);
            $pdfBig->MultiCell($pageWidth, 0, 'http://www.cavendishza.org/verify', '', 'C', 0, 0);

            $pdf->SetFont($Arial, '', 7, '', false);
            $pdf->SetXY($xStart, $bY4);
            $pdf->SetTextColor(161,150,148);
            $pdf->MultiCell($pageWidth, 0, 'http://www.cavendishza.org/verify', '', 'C', 0, 0);


            $bY5 = $bottomSecY+28;
            $pdfBig->SetFont($Arial, '', 7, '', false);
            $pdfBig->SetXY($xStart, $bY5);
            $pdfBig->SetTextColor(161,150,148);
            $pdfBig->MultiCell($pageWidth, 0, 'THIS TRANSCRIPT IS NOT VALID IT DOES NOT BEAR THE OFFICIAL SEAL OR IF IT HAS ANY ALTERNATIONS', '', 'C', 0, 0);

            $pdf->SetFont($Arial, '', 7, '', false);
            $pdf->SetXY($xStart, $bY5);
            $pdf->SetTextColor(161,150,148);
            $pdf->MultiCell($pageWidth, 0, 'THIS TRANSCRIPT IS NOT VALID IT DOES NOT BEAR THE OFFICIAL SEAL OR IF IT HAS ANY ALTERNATIONS', '', 'C', 0, 0);



            // second page 
            $pdfBig->AddPage();
            $pdf->AddPage();


            $pdfBig->SetTextColor(0,0,0);
            $pdf->SetTextColor(0,0,0);

            $xStart= 21;
            $pageWidth = 178;
            $pdfBig->SetFont($times, 'B', 12, '', false);
            $pdfBig->SetXY($xStart, 11);
            $pdfBig->SetTextColor(200,180,216);
            $pdfBig->MultiCell($pageWidth, 0, 'CAVENDISH', 0, 'C', 0, 0, '', '', false);

            $pdf->SetFont($times, 'B', 12, '', false);
            $pdf->SetXY($xStart, 11);
            $pdf->SetTextColor(200,180,216);
            $pdf->MultiCell($pageWidth, 0, 'CAVENDISH', 0, 'C', 0, 0, '', '', false);

            

            $pdfBig->SetXY(177, 12);
            $pdfBig->Image($subdomain[0].'\backend\images/image_som.PNG', '', '', 22, 29, '', '', 'T', false, 300, '', false, false, 1, false, false, false);

            $pdf->SetXY(177, 12);
            $pdf->Image($subdomain[0].'\backend\images/image_som.PNG', '', '', 22, 29, '', '', 'T', false, 300, '', false, false, 1, false, false, false);

            $pdfBig->SetFont($times, 'B', 9, '', false);
            $pdfBig->SetXY(173, 41);
            $pdfBig->SetTextColor(0,0,0);
            $pdfBig->Cell(30, 0, str_replace('-','',$certificateRegNo), 0, false, 'C');

            $pdf->SetFont($times, 'B', 9, '', false);
            $pdf->SetXY(173, 41);
            $pdf->SetTextColor(0,0,0);
            $pdf->Cell(30, 0, str_replace('-','',$certificateRegNo), 0, false, 'C');


            $pdfBig->SetFont($times, 'B', 12, '', false);
            $pdfBig->SetXY($xStart, 16);
            $pdfBig->SetTextColor(24,57,106);
            $pdfBig->Cell($pageWidth, 0, "UNIVERSITY", 0, false, 'C');

            $pdf->SetFont($times, 'B', 12, '', false);
            $pdf->SetXY($xStart, 16);
            $pdf->SetTextColor(24,57,106);
            $pdf->Cell($pageWidth, 0, "UNIVERSITY", 0, false, 'C');


            $pdfBig->SetFont($times, '', 13, '', false);
            $pdfBig->SetXY($xStart, 21);
            $pdfBig->SetTextColor(24,57,106);
            $pdfBig->Cell($pageWidth, 0, "Z A M B I A", 0, false, 'C');

            $pdf->SetFont($times, '', 13, '', false);
            $pdf->SetXY($xStart, 21);
            $pdf->SetTextColor(24,57,106);
            $pdf->Cell($pageWidth, 0, "Z A M B I A", 0, false, 'C');


            $pdfBig->SetFont($times, 'I', 7.5, '', false);
            $pdfBig->SetXY($xStart, 26);
            $pdfBig->SetTextColor(24,57,106);
            $pdfBig->Cell($pageWidth, 0, $certificateTopName, 0, false, 'C');

            $pdf->SetFont($times, 'I', 7.5, '', false);
            $pdf->SetXY($xStart, 26);
            $pdf->SetTextColor(24,57,106);
            $pdf->Cell($pageWidth, 0, $certificateTopName, 0, false, 'C');


            $pdfBig->SetFont($times, '', 7, '', false);
            $pdfBig->SetXY($xStart, 29);
            $pdfBig->SetTextColor(24,57,106);
            $pdfBig->Cell($pageWidth, 0, $certificateAddress, 0, false, 'C');

            $pdf->SetFont($times, '', 7, '', false);
            $pdf->SetXY($xStart, 29);
            $pdf->SetTextColor(24,57,106);
            $pdf->Cell($pageWidth, 0, $certificateAddress, 0, false, 'C');


            $pdfBig->SetFont($times, 'B', 11, '', false);
            $pdfBig->SetXY($xStart, 32);
            $pdfBig->SetTextColor(0,0,0);
            $pdfBig->Cell($pageWidth, 0, 'OFFICE OF THE ACADEMIC REGISTRAR', 0, false, 'C');

            $pdf->SetFont($times, 'B', 11, '', false);
            $pdf->SetXY($xStart, 32);
            $pdf->SetTextColor(0,0,0);
            $pdf->Cell($pageWidth, 0, 'OFFICE OF THE ACADEMIC REGISTRAR', 0, false, 'C');


            $pdfBig->SetFont($times, 'B', 11, '', false);
            $pdfBig->SetXY($xStart, 36);
            $pdfBig->SetTextColor(0,0,0);
            $pdfBig->Cell($pageWidth, 0, 'ACADEMIC TRANSCRIPT', 0, false, 'C');

            $pdf->SetFont($times, 'B', 11, '', false);
            $pdf->SetXY($xStart, 36);
            $pdf->SetTextColor(0,0,0);
            $pdf->Cell($pageWidth, 0, 'ACADEMIC TRANSCRIPT', 0, false, 'C');


            $pdfBig->setCellPaddings( $left = '0', $top = '0.5', $right = '', $bottom = '');
            $pdf->setCellPaddings( $left = '0', $top = '0.5', $right = '', $bottom = '');

            $pdfBig->SetFont($Arial, '', 9, '', false);
            $pdfBig->SetXY($xStart, 42);
            $pdfBig->SetTextColor(0,0,0);
            $pdfBig->Cell(30, 0, 'Name', 0, false, 'L');

            $pdf->SetFont($Arial, '', 9, '', false);
            $pdf->SetXY($xStart, 42);
            $pdf->SetTextColor(0,0,0);
            $pdf->Cell(30, 0, 'Name', 0, false, 'L');


            $pdfBig->SetFont($Arial, '', 9, '', false);
            $pdfBig->SetXY(41, 42);
            $pdfBig->Cell(0, 0, $certificateName, 0, false, 'L');

            $pdf->SetFont($Arial, '', 9, '', false);
            $pdf->SetXY(41, 42);
            $pdf->Cell(0, 0, $certificateName, 0, false, 'L');


            $pdfBig->SetFont($Arial, '', 9, '', false);
            $pdfBig->SetXY($xStart, 46);
            $pdfBig->Cell(30, 0, 'Gender', 0, false, 'L');

            $pdf->SetFont($Arial, '', 9, '', false);
            $pdf->SetXY($xStart, 46);
            $pdf->Cell(30, 0, 'Gender', 0, false, 'L');

            $pdfBig->SetFont($Arial, '', 9, '', false);
            $pdfBig->SetXY(41, 46);
            $pdfBig->Cell(0, 0, $certificateGender, 0, false, 'L');

            $pdf->SetFont($Arial, '', 9, '', false);
            $pdf->SetXY(41, 46);
            $pdf->Cell(0, 0, $certificateGender, 0, false, 'L');


            $pdfBig->SetFont($Arial, '', 9, '', false);
            $pdfBig->SetXY($xStart, 49);
            $pdfBig->Cell(30, 0, 'Date of Birth', 0, false, 'L');

            $pdf->SetFont($Arial, '', 9, '', false);
            $pdf->SetXY($xStart, 49);
            $pdf->Cell(30, 0, 'Date of Birth', 0, false, 'L');

            $pdfBig->SetFont($Arial, '', 9, '', false);
            $pdfBig->SetXY(41, 49);
            $pdfBig->Cell(0, 0, $certificateDOB, 0, false, 'L');

            $pdf->SetFont($Arial, '', 9, '', false);
            $pdf->SetXY(41, 49);
            $pdf->Cell(0, 0, $certificateDOB, 0, false, 'L');


            $pdfBig->SetFont($Arial, '', 9, '', false);
            $pdfBig->SetXY($xStart, 52);
            $pdfBig->SetTextColor(0,0,0);
            $pdfBig->Cell(30, 0, 'Nationality', 0, false, 'L');

            $pdf->SetFont($Arial, '', 9, '', false);
            $pdf->SetXY($xStart, 52);
            $pdf->SetTextColor(0,0,0);
            $pdf->Cell(30, 0, 'Nationality', 0, false, 'L');


            $pdfBig->SetFont($Arial, '', 9, '', false);
            $pdfBig->SetXY(41, 52);
            $pdfBig->Cell(0, 0, $certificateNationality, 0, false, 'L');

            $pdf->SetFont($Arial, '', 9, '', false);
            $pdf->SetXY(41, 52);
            $pdf->Cell(0, 0, $certificateNationality, 0, false, 'L');

            // second part right top heading
            $pdfBig->SetFont($Arial, '', 9, '', false);
            $pdfBig->SetXY(110, 42);
            $pdfBig->SetTextColor(0,0,0);
            $pdfBig->Cell(30, 0, 'Student No.', 0, false, 'L');

            $pdf->SetFont($Arial, '', 9, '', false);
            $pdf->SetXY(110, 42);
            $pdf->SetTextColor(0,0,0);
            $pdf->Cell(30, 0, 'Student No.', 0, false, 'L');


            $pdfBig->SetFont($Arial, '', 9, '', false);
            $pdfBig->SetXY(132, 42);
            $pdfBig->Cell(0, 0, $certificateRegNo, 0, false, 'L');

            $pdf->SetFont($Arial, '', 9, '', false);
            $pdf->SetXY(132, 42);
            $pdf->Cell(0, 0, $certificateRegNo, 0, false, 'L');



            $pdfBig->SetFont($Arial, '', 9, '', false);
            $pdfBig->SetXY(110, 46);
            $pdfBig->Cell(30, 0, 'Programme', 0, false, 'L');

            $pdf->SetFont($Arial, '', 9, '', false);
            $pdf->SetXY(110, 46);
            $pdf->Cell(30, 0, 'Programme', 0, false, 'L');


            $pdfBig->SetFont($Arial, '', 9, '', false);
            $pdfBig->SetXY(132, 46);
            $pdfBig->Cell(0, 0, $certificateProgramme, 0, false, 'L');

            $pdf->SetFont($Arial, '', 9, '', false);
            $pdf->SetXY(132, 46);
            $pdf->Cell(0, 0, $certificateProgramme, 0, false, 'L');


            $pdfBig->SetFont($Arial, '', 9, '', false);
            $pdfBig->SetXY(110, 49);
            $pdfBig->Cell(30, 0, 'School', 0, false, 'L');

            $pdf->SetFont($Arial, '', 9, '', false);
            $pdf->SetXY(110, 49);
            $pdf->Cell(30, 0, 'School', 0, false, 'L');


            $pdfBig->SetFont($Arial, '', 9, '', false);
            $pdfBig->SetXY(132, 49);
            $pdfBig->Cell(0, 0, $certificateSchool, 0, false, 'L');


            $pdf->SetFont($Arial, '', 9, '', false);
            $pdf->SetXY(132, 49);
            $pdf->Cell(0, 0, $certificateSchool, 0, false, 'L');


            $pdfBig->SetFont($Arial, '', 9, '', false);
            $pdfBig->SetXY(110, 52);
            $pdfBig->SetTextColor(0,0,0);
            $pdfBig->Cell(30, 0, 'Year of Entry', 0, false, 'L');

            $pdf->SetFont($Arial, '', 9, '', false);
            $pdf->SetXY(110, 52);
            $pdf->SetTextColor(0,0,0);
            $pdf->Cell(30, 0, 'Year of Entry', 0, false, 'L');


            $pdfBig->SetFont($Arial, '', 9, '', false);
            $pdfBig->SetXY(132, 52);
            $pdfBig->Cell(0, 0, $certificateYearEntry, 0, false, 'L');

            $pdf->SetFont($Arial, '', 9, '', false);
            $pdf->SetXY(132, 52);
            $pdf->Cell(0, 0, $certificateYearEntry, 0, false, 'L');


            // start year four table
            $tableX = 21;
            $tableY = 56;
            $pdfBig->setCellPaddings( $left = '1', $top = '', $right = '', $bottom = '');
            $pdfBig->SetFont($Arial, 'B', 7, '', false);
            $pdfBig->SetXY($tableX, $tableY);
            $pdfBig->SetTextColor(255,255,255);
            $pdfBig->SetFillColor(19,57,106);
            $pdfBig->MultiCell($pageWidth, 4, 'YEAR FOUR', '', 'C', 1, 0);

            $pdf->setCellPaddings( $left = '1', $top = '', $right = '', $bottom = '');
            $pdf->SetFont($Arial, 'B', 7, '', false);
            $pdf->SetXY($tableX, $tableY);
            $pdf->SetTextColor(255,255,255);
            $pdf->SetFillColor(19,57,106);
            $pdf->MultiCell($pageWidth, 4, 'YEAR FOUR', '', 'C', 1, 0);


            $tableY = $tableY+4;
            $pdfBig->SetFont($Arial, 'B', 7, '', false);
            $pdfBig->SetXY($tableX, $tableY);
            $pdfBig->SetFillColor(216, 204, 201 );
            $pdfBig->MultiCell($pageWidth, 4.5, '', '', 'C', 1, 0);

            $pdf->SetFont($Arial, 'B', 7, '', false);
            $pdf->SetXY($tableX, $tableY);
            $pdf->SetFillColor(216, 204, 201 );
            $pdf->MultiCell($pageWidth, 4.5, '', '', 'C', 1, 0);


            $tableY = $tableY+4.5;
            $pdfBig->setCellPaddings( $left = '', $top = '0.5', $right = '', $bottom = '');
            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($tableX, $tableY);
            $pdfBig->SetTextColor(0,0,0);
            $pdfBig->SetFillColor(255,255,255);
            $pdfBig->MultiCell(19, 7, 'Module Code', 'TBRL', 'L', 1, 0);

            $pdf->setCellPaddings( $left = '', $top = '0.5', $right = '', $bottom = '');
            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($tableX, $tableY);
            $pdf->SetTextColor(0,0,0);
            $pdf->SetFillColor(255,255,255);
            $pdf->MultiCell(19, 7, 'Module Code', 'TBRL', 'L', 1, 0);
            $tableX = $tableX + 19;

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($tableX, $tableY);
            $pdfBig->MultiCell(116, 7, 'Module Name', 'TBRL', 'L', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($tableX, $tableY);
            $pdf->MultiCell(116, 7, 'Module Name', 'TBRL', 'L', 1, 0);
            $tableX = $tableX+116;


            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($tableX, $tableY);
            $pdfBig->MultiCell(19, 7, 'Grade', 'TBRL', 'C', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($tableX, $tableY);
            $pdf->MultiCell(19, 7, 'Grade', 'TBRL', 'C', 1, 0);

            $tableX = $tableX+19;

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($tableX, $tableY);
            $pdfBig->MultiCell(24, 7, 'Remark', 'TBRL', 'C', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($tableX, $tableY);
            $pdf->MultiCell(24, 7, 'Remark', 'TBRL', 'C', 1, 0);

            
            $year4X= 21;
            $year4Y = $tableY +7;
            $pdfBig->setCellPaddings( $left = '', $top = '0.5', $right = '', $bottom = '');
            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year4X, $year4Y);
            $pdfBig->MultiCell(19, $tableHeight, $year4CourseCode1, 'TBRL', 'C', 1, 0);

            $pdf->setCellPaddings( $left = '', $top = '0.5', $right = '', $bottom = '');
            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year4X, $year4Y);
            $pdf->MultiCell(19, $tableHeight, $year4CourseCode1, 'TBRL', 'C', 1, 0);

            $year4X = $year4X+19;

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year4X, $year4Y);
            $pdfBig->MultiCell(116, $tableHeight, $year4CourseName1, 'TBRL', 'L', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year4X, $year4Y);
            $pdf->MultiCell(116, $tableHeight, $year4CourseName1, 'TBRL', 'L', 1, 0);
            $year4X = $year4X+116;

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year4X, $year4Y);
            $pdfBig->MultiCell(19, $tableHeight,  $year4Grade1, 'TBRL', 'C', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year4X, $year4Y);
            $pdf->MultiCell(19, $tableHeight,  $year4Grade1, 'TBRL', 'C', 1, 0);
            $year4X = $year4X+19;

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year4X, $year4Y);
            $pdfBig->MultiCell(24, $tableHeight, $year4Remark1, 'TBRL', 'L', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year4X, $year4Y);
            $pdf->MultiCell(24, $tableHeight, $year4Remark1, 'TBRL', 'L', 1, 0);
            $year4X =21;
            $year4Y = $year4Y +$tableHeight;
            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year4X, $year4Y);
            $pdfBig->MultiCell(19, $tableHeight, $year4CourseCode2, 'TBRL', 'C', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year4X, $year4Y);
            $pdf->MultiCell(19, $tableHeight, $year4CourseCode2, 'TBRL', 'C', 1, 0);
            $year4X = $year4X+19;

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year4X, $year4Y);
            $pdfBig->MultiCell(116, $tableHeight, $year4CourseName2, 'TBRL', 'L', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year4X, $year4Y);
            $pdf->MultiCell(116, $tableHeight, $year4CourseName2, 'TBRL', 'L', 1, 0);
            $year4X = $year4X +116;

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year4X, $year4Y);
            $pdfBig->MultiCell(19, $tableHeight, $year4Grade2, 'TBRL', 'C', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year4X, $year4Y);
            $pdf->MultiCell(19, $tableHeight, $year4Grade2, 'TBRL', 'C', 1, 0);
            $year4X = $year4X +19;

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year4X, $year4Y);
            $pdfBig->MultiCell(24, $tableHeight, $year4Remark2, 'TBRL', 'L', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year4X, $year4Y);
            $pdf->MultiCell(24, $tableHeight, $year4Remark2, 'TBRL', 'L', 1, 0);
            $year4Y = $year4Y +$tableHeight;

            $year4X = 21;
            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year4X, $year4Y);
            $pdfBig->MultiCell(19, $tableHeight, $year4CourseCode3, 'TBRL', 'C', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year4X, $year4Y);
            $pdf->MultiCell(19, $tableHeight, $year4CourseCode3, 'TBRL', 'C', 1, 0);
            $year4X = $year4X+19;

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year4X, $year4Y);
            $pdfBig->MultiCell(116, $tableHeight, $year4CourseName3, 'TBRL', 'L', 1, 0);
            
            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year4X, $year4Y);
            $pdf->MultiCell(116, $tableHeight, $year4CourseName3, 'TBRL', 'L', 1, 0);

            $year4X = $year4X+116;

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year4X, $year4Y);
            $pdfBig->MultiCell(19, $tableHeight, $year4Grade3, 'TBRL', 'C', 1, 0);
            
            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year4X, $year4Y);
            $pdf->MultiCell(19, $tableHeight, $year4Grade3, 'TBRL', 'C', 1, 0);
            $year4X = $year4X+19;

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year4X, $year4Y);
            $pdfBig->MultiCell(24, $tableHeight, $year4Remark3, 'TBRL', 'L', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year4X, $year4Y);
            $pdf->MultiCell(24, $tableHeight, $year4Remark3, 'TBRL', 'L', 1, 0);

            $year4X = 21;
            $year4Y = $year4Y +$tableHeight;

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year4X, $year4Y);
            $pdfBig->MultiCell(19, $tableHeight, $year4CourseCode4, 'TBRL', 'C', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year4X, $year4Y);
            $pdf->MultiCell(19, $tableHeight, $year4CourseCode4, 'TBRL', 'C', 1, 0);
            $year4X = $year4X+19;

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year4X, $year4Y);
            $pdfBig->MultiCell(116, $tableHeight, $year4CourseName4, 'TBRL', 'L', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year4X, $year4Y);
            $pdf->MultiCell(116, $tableHeight, $year4CourseName4, 'TBRL', 'L', 1, 0);
            $year4X = $year4X+116;

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year4X, $year4Y);
            $pdfBig->MultiCell(19, $tableHeight, $year4Grade4, 'TBRL', 'C', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year4X, $year4Y);
            $pdf->MultiCell(19, $tableHeight, $year4Grade4, 'TBRL', 'C', 1, 0);
            $year4X = $year4X+19;

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year4X, $year4Y);
            $pdfBig->MultiCell(24, $tableHeight, $year4Remark4, 'TBRL', 'L', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year4X, $year4Y);
            $pdf->MultiCell(24, $tableHeight, $year4Remark4, 'TBRL', 'L', 1, 0);

            $year4X = 21;
            $year4Y = $year4Y +$tableHeight;
            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year4X, $year4Y);
            $pdfBig->MultiCell(19, $tableHeight, $year4CourseCode5, 'TBRL', 'C', 1, 0);
            
            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year4X, $year4Y);
            $pdf->MultiCell(19, $tableHeight, $year4CourseCode5, 'TBRL', 'C', 1, 0);
            $year4X = $year4X+19;


            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year4X, $year4Y);
            $pdfBig->MultiCell(116, $tableHeight, $year4CourseName5, 'TBRL', 'L', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year4X, $year4Y);
            $pdf->MultiCell(116, $tableHeight, $year4CourseName5, 'TBRL', 'L', 1, 0);
            $year4X = $year4X+116;

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year4X, $year4Y);
            $pdfBig->MultiCell(19, $tableHeight, $year4Grade5, 'TBRL', 'C', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year4X, $year4Y);
            $pdf->MultiCell(19, $tableHeight, $year4Grade5, 'TBRL', 'C', 1, 0);
            $year4X = $year4X+19;

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year4X, $year4Y);
            $pdfBig->MultiCell(24, $tableHeight, $year4Remark5, 'TBRL', 'L', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year4X, $year4Y);
            $pdf->MultiCell(24, $tableHeight, $year4Remark5, 'TBRL', 'L', 1, 0);
            $year4Y = $year4Y +$tableHeight;

            $year4X = 21;


            for($i=0; $i<2; $i++) {

                $pdfBig->SetFont($Arial, 'B', 6, '', false);
                $pdfBig->SetXY($year4X, $year4Y);
                $pdfBig->MultiCell(19, $tableHeight, '', 'TBRL', 'L', 1, 0);

                $pdf->SetFont($Arial, 'B', 6, '', false);
                $pdf->SetXY($year4X, $year4Y);
                $pdf->MultiCell(19, $tableHeight, '', 'TBRL', 'L', 1, 0);
                $year4X = $year4X+19;

                $pdfBig->SetFont($Arial, 'B', 6, '', false);
                $pdfBig->SetXY($year4X, $year4Y);
                $pdfBig->MultiCell(116, $tableHeight,'', 'TBRL', 'L', 1, 0);

                $pdf->SetFont($Arial, 'B', 6, '', false);
                $pdf->SetXY($year4X, $year4Y);
                $pdf->MultiCell(116, $tableHeight,'', 'TBRL', 'L', 1, 0);
                $year4X = $year4X+116;

                $pdfBig->SetFont($Arial, 'B', 6, '', false);
                $pdfBig->SetXY($year4X, $year4Y);
                $pdfBig->MultiCell(19, $tableHeight, '', 'TBRL', 'C', 1, 0);

                $pdf->SetFont($Arial, 'B', 6, '', false);
                $pdf->SetXY($year4X, $year4Y);
                $pdf->MultiCell(19, $tableHeight, '', 'TBRL', 'C', 1, 0);
                $year4X = $year4X+19;

                $pdfBig->SetFont($Arial, 'B', 6, '', false);
                $pdfBig->SetXY($year4X, $year4Y);
                $pdfBig->MultiCell(24, $tableHeight, '', 'TBRL', 'C', 1, 0);

                $pdf->SetFont($Arial, 'B', 6, '', false);
                $pdf->SetXY($year4X, $year4Y);
                $pdf->MultiCell(24, $tableHeight, '', 'TBRL', 'C', 1, 0);
                $year4X = 21;
                $year4Y = $year4Y +$tableHeight;
            }
            // end year four table



            // start year five table
            $tableX = 21;
            $tableY = $year4Y;
            $pdfBig->SetFont($Arial, 'B', 7, '', false);
            $pdfBig->SetXY($tableX, $tableY);
            $pdfBig->SetTextColor(255,255,255);
            $pdfBig->SetFillColor(19,57,106);
            $pdfBig->MultiCell($pageWidth, 4, 'YEAR FIVE', '', 'C', 1, 0);
            
            $pdf->SetFont($Arial, 'B', 7, '', false);
            $pdf->SetXY($tableX, $tableY);
            $pdf->SetTextColor(255,255,255);
            $pdf->SetFillColor(19,57,106);
            $pdf->MultiCell($pageWidth, 4, 'YEAR FIVE', '', 'C', 1, 0);
            $tableY = $tableY+4;

            $pdfBig->SetFont($Arial, 'B', 7, '', false);
            $pdfBig->SetXY($tableX, $tableY);
            $pdfBig->SetFillColor(216, 204, 201 );
            $pdfBig->MultiCell($pageWidth, 4.5, '', '', 'C', 1, 0);

            $pdf->SetFont($Arial, 'B', 7, '', false);
            $pdf->SetXY($tableX, $tableY);
            $pdf->SetFillColor(216, 204, 201 );
            $pdf->MultiCell($pageWidth, 4.5, '', '', 'C', 1, 0);

            $tableY = $tableY+4.5;
            $pdfBig->setCellPaddings( $left = '', $top = '0.5', $right = '', $bottom = '');
            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($tableX, $tableY);
            $pdfBig->SetTextColor(0,0,0);
            $pdfBig->SetFillColor(255,255,255);
            $pdfBig->MultiCell(19, 7, 'Module Code', 'TBRL', 'L', 1, 0);

            $pdf->setCellPaddings( $left = '', $top = '0.5', $right = '', $bottom = '');
            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($tableX, $tableY);
            $pdf->SetTextColor(0,0,0);
            $pdf->SetFillColor(255,255,255);
            $pdf->MultiCell(19, 7, 'Module Code', 'TBRL', 'L', 1, 0);
            $tableX = $tableX + 19;

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($tableX, $tableY);
            $pdfBig->MultiCell(116, 7, 'Module Name', 'TBRL', 'L', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($tableX, $tableY);
            $pdf->MultiCell(116, 7, 'Module Name', 'TBRL', 'L', 1, 0);
            $tableX = $tableX+116;


            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($tableX, $tableY);
            $pdfBig->MultiCell(19, 7, 'Grade', 'TBRL', 'C', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($tableX, $tableY);
            $pdf->MultiCell(19, 7, 'Grade', 'TBRL', 'C', 1, 0);
            $tableX = $tableX+19;

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($tableX, $tableY);
            $pdfBig->MultiCell(24, 7, 'Remark', 'TBRL', 'C', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($tableX, $tableY);
            $pdf->MultiCell(24, 7, 'Remark', 'TBRL', 'C', 1, 0);
            
            $year5X= 21;
            $year5Y = $tableY +7;
            $pdfBig->setCellPaddings( $left = '', $top = '0.5', $right = '', $bottom = '');
            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year5X, $year5Y);
            $pdfBig->MultiCell(19, $tableHeight, $year5CourseCode1, 'TBRL', 'C', 1, 0);

            $pdf->setCellPaddings( $left = '', $top = '0.5', $right = '', $bottom = '');
            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year5X, $year5Y);
            $pdf->MultiCell(19, $tableHeight, $year5CourseCode1, 'TBRL', 'C', 1, 0);
            $year5X = $year5X+19;

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year5X, $year5Y);
            $pdfBig->MultiCell(116, $tableHeight, $year5CourseName1, 'TBRL', 'L', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year5X, $year5Y);
            $pdf->MultiCell(116, $tableHeight, $year5CourseName1, 'TBRL', 'L', 1, 0);
            $year5X = $year5X+116;

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year5X, $year5Y);
            $pdfBig->MultiCell(19, $tableHeight,  $year5Grade1, 'TBRL', 'C', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year5X, $year5Y);
            $pdf->MultiCell(19, $tableHeight,  $year5Grade1, 'TBRL', 'C', 1, 0);
            $year5X = $year5X+19;

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year5X, $year5Y);
            $pdfBig->MultiCell(24, $tableHeight, $year5Remark1, 'TBRL', 'L', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year5X, $year5Y);
            $pdf->MultiCell(24, $tableHeight, $year5Remark1, 'TBRL', 'L', 1, 0);

            $year5X =21;
            $year5Y = $year5Y +$tableHeight;
            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year5X, $year5Y);
            $pdfBig->MultiCell(19, $tableHeight, $year5CourseCode2, 'TBRL', 'C', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year5X, $year5Y);
            $pdf->MultiCell(19, $tableHeight, $year5CourseCode2, 'TBRL', 'C', 1, 0);
            $year5X = $year5X+19;

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year5X, $year5Y);
            $pdfBig->MultiCell(116, $tableHeight, $year5CourseName2, 'TBRL', 'L', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year5X, $year5Y);
            $pdf->MultiCell(116, $tableHeight, $year5CourseName2, 'TBRL', 'L', 1, 0);
            $year5X = $year5X +116;

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year5X, $year5Y);
            $pdfBig->MultiCell(19, $tableHeight, $year5Grade2, 'TBRL', 'C', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year5X, $year5Y);
            $pdf->MultiCell(19, $tableHeight, $year5Grade2, 'TBRL', 'C', 1, 0);
            $year5X = $year5X +19;

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year5X, $year5Y);
            $pdfBig->MultiCell(24, $tableHeight, $year5Remark2, 'TBRL', 'L', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year5X, $year5Y);
            $pdf->MultiCell(24, $tableHeight, $year5Remark2, 'TBRL', 'L', 1, 0);
            $year5Y = $year5Y +$tableHeight;

            $year5X = 21;
            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year5X, $year5Y);
            $pdfBig->MultiCell(19, $tableHeight, $year5CourseCode3, 'TBRL', 'C', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year5X, $year5Y);
            $pdf->MultiCell(19, $tableHeight, $year5CourseCode3, 'TBRL', 'C', 1, 0);
            $year5X = $year5X+19;

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year5X, $year5Y);
            $pdfBig->MultiCell(116, $tableHeight, $year5CourseName3, 'TBRL', 'L', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year5X, $year5Y);
            $pdf->MultiCell(116, $tableHeight, $year5CourseName3, 'TBRL', 'L', 1, 0);
            $year5X = $year5X+116;

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year5X, $year5Y);
            $pdfBig->MultiCell(19, $tableHeight, $year5Grade3, 'TBRL', 'C', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year5X, $year5Y);
            $pdf->MultiCell(19, $tableHeight, $year5Grade3, 'TBRL', 'C', 1, 0);
            $year5X = $year5X+19;

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year5X, $year5Y);
            $pdfBig->MultiCell(24, $tableHeight, $year5Remark3, 'TBRL', 'L', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year5X, $year5Y);
            $pdf->MultiCell(24, $tableHeight, $year5Remark3, 'TBRL', 'L', 1, 0);

            $year5X = 21;
            $year5Y = $year5Y +$tableHeight;

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year5X, $year5Y);
            $pdfBig->MultiCell(19, $tableHeight, $year5CourseCode4, 'TBRL', 'C', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year5X, $year5Y);
            $pdf->MultiCell(19, $tableHeight, $year5CourseCode4, 'TBRL', 'C', 1, 0);
            $year5X = $year5X+19;

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year5X, $year5Y);
            $pdfBig->MultiCell(116, $tableHeight, $year5CourseName4, 'TBRL', 'L', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year5X, $year5Y);
            $pdf->MultiCell(116, $tableHeight, $year5CourseName4, 'TBRL', 'L', 1, 0);
            $year5X = $year5X+116;

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year5X, $year5Y);
            $pdfBig->MultiCell(19, $tableHeight, $year5Grade4, 'TBRL', 'C', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year5X, $year5Y);
            $pdf->MultiCell(19, $tableHeight, $year5Grade4, 'TBRL', 'C', 1, 0);
            $year5X = $year5X+19;

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year5X, $year5Y);
            $pdfBig->MultiCell(24, $tableHeight, $year5Remark4, 'TBRL', 'L', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year5X, $year5Y);
            $pdf->MultiCell(24, $tableHeight, $year5Remark4, 'TBRL', 'L', 1, 0);

            $year5X = 21;
            $year5Y = $year5Y +$tableHeight;
            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year5X, $year5Y);
            $pdfBig->MultiCell(19, $tableHeight, $year5CourseCode5, 'TBRL', 'C', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year5X, $year5Y);
            $pdf->MultiCell(19, $tableHeight, $year5CourseCode5, 'TBRL', 'C', 1, 0);
            $year5X = $year5X+19;

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year5X, $year5Y);
            $pdfBig->MultiCell(116, $tableHeight, $year5CourseName5, 'TBRL', 'L', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year5X, $year5Y);
            $pdf->MultiCell(116, $tableHeight, $year5CourseName5, 'TBRL', 'L', 1, 0);
            $year5X = $year5X+116;

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year5X, $year5Y);
            $pdfBig->MultiCell(19, $tableHeight, $year5Grade5, 'TBRL', 'C', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year5X, $year5Y);
            $pdf->MultiCell(19, $tableHeight, $year5Grade5, 'TBRL', 'C', 1, 0);
            $year5X = $year5X+19;

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year5X, $year5Y);
            $pdfBig->MultiCell(24, $tableHeight, $year5Remark5, 'TBRL', 'L', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year5X, $year5Y);
            $pdf->MultiCell(24, $tableHeight, $year5Remark5, 'TBRL', 'L', 1, 0);

            $year5X = 21;
            $year5Y = $year5Y +$tableHeight;
            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year5X, $year5Y);
            $pdfBig->MultiCell(19, $tableHeight, $year5CourseCode6, 'TBRL', 'C', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year5X, $year5Y);
            $pdf->MultiCell(19, $tableHeight, $year5CourseCode6, 'TBRL', 'C', 1, 0);
            $year5X = $year5X+19;

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year5X, $year5Y);
            $pdfBig->MultiCell(116, $tableHeight, $year5CourseName6, 'TBRL', 'L', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year5X, $year5Y);
            $pdf->MultiCell(116, $tableHeight, $year5CourseName6, 'TBRL', 'L', 1, 0);
            $year5X = $year5X+116;

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year5X, $year5Y);
            $pdfBig->MultiCell(19, $tableHeight, $year5Grade6, 'TBRL', 'C', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year5X, $year5Y);
            $pdf->MultiCell(19, $tableHeight, $year5Grade6, 'TBRL', 'C', 1, 0);
            $year5X = $year5X+19;

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year5X, $year5Y);
            $pdfBig->MultiCell(24, $tableHeight, $year5Remark6, 'TBRL', 'L', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year5X, $year5Y);
            $pdf->MultiCell(24, $tableHeight, $year5Remark6, 'TBRL', 'L', 1, 0);


            $year5X = 21;
            $year5Y = $year5Y +$tableHeight;

            for($i=0; $i<1; $i++) {

                $pdfBig->SetFont($Arial, 'B', 6, '', false);
                $pdfBig->SetXY($year5X, $year5Y);
                $pdfBig->MultiCell(19, $tableHeight, '', 'TBRL', 'L', 1, 0);

                $pdf->SetFont($Arial, 'B', 6, '', false);
                $pdf->SetXY($year5X, $year5Y);
                $pdf->MultiCell(19, $tableHeight, '', 'TBRL', 'L', 1, 0);
                $year5X = $year5X+19;

                $pdfBig->SetFont($Arial, 'B', 6, '', false);
                $pdfBig->SetXY($year5X, $year5Y);
                $pdfBig->MultiCell(116, $tableHeight,'', 'TBRL', 'L', 1, 0);

                $pdf->SetFont($Arial, 'B', 6, '', false);
                $pdf->SetXY($year5X, $year5Y);
                $pdf->MultiCell(116, $tableHeight,'', 'TBRL', 'L', 1, 0);
                $year5X = $year5X+116;

                $pdfBig->SetFont($Arial, 'B', 6, '', false);
                $pdfBig->SetXY($year5X, $year5Y);
                $pdfBig->MultiCell(19, $tableHeight, '', 'TBRL', 'C', 1, 0);

                $pdf->SetFont($Arial, 'B', 6, '', false);
                $pdf->SetXY($year5X, $year5Y);
                $pdf->MultiCell(19, $tableHeight, '', 'TBRL', 'C', 1, 0);
                $year5X = $year5X+19;

                $pdfBig->SetFont($Arial, 'B', 6, '', false);
                $pdfBig->SetXY($year5X, $year5Y);
                $pdfBig->MultiCell(24, $tableHeight, '', 'TBRL', 'C', 1, 0);

                $pdf->SetFont($Arial, 'B', 6, '', false);
                $pdf->SetXY($year5X, $year5Y);
                $pdf->MultiCell(24, $tableHeight, '', 'TBRL', 'C', 1, 0);
                $year5X = 21;
                $year5Y = $year5Y +$tableHeight;
            }
            // end year five table



            // start year six table
            $tableX = 21;
            $tableY = $year5Y;
            $pdfBig->SetFont($Arial, 'B', 7, '', false);
            $pdfBig->SetXY($tableX, $tableY);
            $pdfBig->SetTextColor(255,255,255);
            $pdfBig->SetFillColor(19,57,106);
            $pdfBig->MultiCell($pageWidth, 4, 'YEAR SIX', '', 'C', 1, 0);

            $pdf->SetFont($Arial, 'B', 7, '', false);
            $pdf->SetXY($tableX, $tableY);
            $pdf->SetTextColor(255,255,255);
            $pdf->SetFillColor(19,57,106);
            $pdf->MultiCell($pageWidth, 4, 'YEAR SIX', '', 'C', 1, 0);

            $tableY = $tableY+4;
            $pdfBig->SetFont($Arial, 'B', 7, '', false);
            $pdfBig->SetXY($tableX, $tableY);
            $pdfBig->SetFillColor(216, 204, 201 );
            $pdfBig->MultiCell($pageWidth, 4.5, '', '', 'C', 1, 0);

            $pdf->SetFont($Arial, 'B', 7, '', false);
            $pdf->SetXY($tableX, $tableY);
            $pdf->SetFillColor(216, 204, 201 );
            $pdf->MultiCell($pageWidth, 4.5, '', '', 'C', 1, 0);

            $tableY = $tableY+4.5;
            $pdfBig->setCellPaddings( $left = '', $top = '0.5', $right = '', $bottom = '');
            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($tableX, $tableY);
            $pdfBig->SetTextColor(0,0,0);
            $pdfBig->SetFillColor(255,255,255);
            $pdfBig->MultiCell(19, 7, 'Module Code', 'TBRL', 'L', 1, 0);

            $pdf->setCellPaddings( $left = '', $top = '0.5', $right = '', $bottom = '');
            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($tableX, $tableY);
            $pdf->SetTextColor(0,0,0);
            $pdf->SetFillColor(255,255,255);
            $pdf->MultiCell(19, 7, 'Module Code', 'TBRL', 'L', 1, 0);
            $tableX = $tableX + 19;

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($tableX, $tableY);
            $pdfBig->MultiCell(116, 7, 'Module Name', 'TBRL', 'L', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($tableX, $tableY);
            $pdf->MultiCell(116, 7, 'Module Name', 'TBRL', 'L', 1, 0);
            $tableX = $tableX+116;


            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($tableX, $tableY);
            $pdfBig->MultiCell(19, 7, 'Grade', 'TBRL', 'C', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($tableX, $tableY);
            $pdf->MultiCell(19, 7, 'Grade', 'TBRL', 'C', 1, 0);
            $tableX = $tableX+19;

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($tableX, $tableY);
            $pdfBig->MultiCell(24, 7, 'Remark', 'TBRL', 'C', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($tableX, $tableY);
            $pdf->MultiCell(24, 7, 'Remark', 'TBRL', 'C', 1, 0);
            
            $year6X= 21;
            $year6Y = $tableY +7;
            $pdfBig->setCellPaddings( $left = '', $top = '0.5', $right = '', $bottom = '');
            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year6X, $year6Y);
            $pdfBig->MultiCell(19, $tableHeight, $year6CourseCode1, 'TBRL', 'C', 1, 0);

            $pdf->setCellPaddings( $left = '', $top = '0.5', $right = '', $bottom = '');
            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year6X, $year6Y);
            $pdf->MultiCell(19, $tableHeight, $year6CourseCode1, 'TBRL', 'C', 1, 0);
            $year6X = $year6X+19;

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year6X, $year6Y);
            $pdfBig->MultiCell(116, $tableHeight, $year6CourseName1, 'TBRL', 'L', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year6X, $year6Y);
            $pdf->MultiCell(116, $tableHeight, $year6CourseName1, 'TBRL', 'L', 1, 0);
            $year6X = $year6X+116;

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year6X, $year6Y);
            $pdfBig->MultiCell(19, $tableHeight,  $year6Grade1, 'TBRL', 'C', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year6X, $year6Y);
            $pdf->MultiCell(19, $tableHeight,  $year6Grade1, 'TBRL', 'C', 1, 0);
            $year6X = $year6X+19;

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year6X, $year6Y);
            $pdfBig->MultiCell(24, $tableHeight, $year6Remark1, 'TBRL', 'L', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year6X, $year6Y);
            $pdf->MultiCell(24, $tableHeight, $year6Remark1, 'TBRL', 'L', 1, 0);

            $year6X =21;
            $year6Y = $year6Y +$tableHeight;
            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year6X, $year6Y);
            $pdfBig->MultiCell(19, $tableHeight, $year6CourseCode2, 'TBRL', 'C', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year6X, $year6Y);
            $pdf->MultiCell(19, $tableHeight, $year6CourseCode2, 'TBRL', 'C', 1, 0);
            $year6X = $year6X+19;

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year6X, $year6Y);
            $pdfBig->MultiCell(116, $tableHeight, $year6CourseName2, 'TBRL', 'L', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year6X, $year6Y);
            $pdf->MultiCell(116, $tableHeight, $year6CourseName2, 'TBRL', 'L', 1, 0);
            $year6X = $year6X +116;

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year6X, $year6Y);
            $pdfBig->MultiCell(19, $tableHeight, $year6Grade2, 'TBRL', 'C', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year6X, $year6Y);
            $pdf->MultiCell(19, $tableHeight, $year6Grade2, 'TBRL', 'C', 1, 0);
            $year6X = $year6X +19;

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year6X, $year6Y);
            $pdfBig->MultiCell(24, $tableHeight, $year6Remark2, 'TBRL', 'L', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year6X, $year6Y);
            $pdf->MultiCell(24, $tableHeight, $year6Remark2, 'TBRL', 'L', 1, 0);
            $year6Y = $year6Y +$tableHeight;

            $year6X = 21;
            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year6X, $year6Y);
            $pdfBig->MultiCell(19, $tableHeight, $year6CourseCode3, 'TBRL', 'C', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year6X, $year6Y);
            $pdf->MultiCell(19, $tableHeight, $year6CourseCode3, 'TBRL', 'C', 1, 0);
            $year6X = $year6X+19;

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year6X, $year6Y);
            $pdfBig->MultiCell(116, $tableHeight, $year6CourseName3, 'TBRL', 'L', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year6X, $year6Y);
            $pdf->MultiCell(116, $tableHeight, $year6CourseName3, 'TBRL', 'L', 1, 0);
            $year6X = $year6X+116;

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year6X, $year6Y);
            $pdfBig->MultiCell(19, $tableHeight, $year6Grade3, 'TBRL', 'C', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year6X, $year6Y);
            $pdf->MultiCell(19, $tableHeight, $year6Grade3, 'TBRL', 'C', 1, 0);
            $year6X = $year6X+19;

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year6X, $year6Y);
            $pdfBig->MultiCell(24, $tableHeight, $year6Remark3, 'TBRL', 'L', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year6X, $year6Y);
            $pdf->MultiCell(24, $tableHeight, $year6Remark3, 'TBRL', 'L', 1, 0);

            $year6X = 21;
            $year6Y = $year6Y +$tableHeight;

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year6X, $year6Y);
            $pdfBig->MultiCell(19, $tableHeight, $year6CourseCode4, 'TBRL', 'C', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year6X, $year6Y);
            $pdf->MultiCell(19, $tableHeight, $year6CourseCode4, 'TBRL', 'C', 1, 0);
            $year6X = $year6X+19;

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year6X, $year6Y);
            $pdfBig->MultiCell(116, $tableHeight, $year6CourseName4, 'TBRL', 'L', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year6X, $year6Y);
            $pdf->MultiCell(116, $tableHeight, $year6CourseName4, 'TBRL', 'L', 1, 0);
            $year6X = $year6X+116;

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year6X, $year6Y);
            $pdfBig->MultiCell(19, $tableHeight, $year6Grade4, 'TBRL', 'C', 1, 0);
            
            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year6X, $year6Y);
            $pdf->MultiCell(19, $tableHeight, $year6Grade4, 'TBRL', 'C', 1, 0);
            $year6X = $year6X+19;

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year6X, $year6Y);
            $pdfBig->MultiCell(24, $tableHeight, $year6Remark4, 'TBRL', 'L', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year6X, $year6Y);
            $pdf->MultiCell(24, $tableHeight, $year6Remark4, 'TBRL', 'L', 1, 0);

            $year6X = 21;
            $year6Y = $year6Y +$tableHeight;
            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year6X, $year6Y);
            $pdfBig->MultiCell(19, $tableHeight, $year6CourseCode5, 'TBRL', 'C', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year6X, $year6Y);
            $pdf->MultiCell(19, $tableHeight, $year6CourseCode5, 'TBRL', 'C', 1, 0);
            $year6X = $year6X+19;

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year6X, $year6Y);
            $pdfBig->MultiCell(116, $tableHeight, $year6CourseName5, 'TBRL', 'L', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year6X, $year6Y);
            $pdf->MultiCell(116, $tableHeight, $year6CourseName5, 'TBRL', 'L', 1, 0);
            $year6X = $year6X+116;

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year6X, $year6Y);
            $pdfBig->MultiCell(19, $tableHeight, $year6Grade5, 'TBRL', 'C', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year6X, $year6Y);
            $pdf->MultiCell(19, $tableHeight, $year6Grade5, 'TBRL', 'C', 1, 0);
            $year6X = $year6X+19;

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year6X, $year6Y);
            $pdfBig->MultiCell(24, $tableHeight, $year6Remark5, 'TBRL', 'L', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year6X, $year6Y);
            $pdf->MultiCell(24, $tableHeight, $year6Remark5, 'TBRL', 'L', 1, 0);


            $year6X = 21;
            $year6Y = $year6Y +$tableHeight;
            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year6X, $year6Y);
            $pdfBig->MultiCell(19, $tableHeight, $year6CourseCode6, 'TBRL', 'C', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year6X, $year6Y);
            $pdf->MultiCell(19, $tableHeight, $year6CourseCode6, 'TBRL', 'C', 1, 0);
            $year6X = $year6X+19;

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year6X, $year6Y);
            $pdfBig->MultiCell(116, $tableHeight, $year6CourseName6, 'TBRL', 'L', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year6X, $year6Y);
            $pdf->MultiCell(116, $tableHeight, $year6CourseName6, 'TBRL', 'L', 1, 0);
            $year6X = $year6X+116;

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year6X, $year6Y);
            $pdfBig->MultiCell(19, $tableHeight, $year6Grade6, 'TBRL', 'C', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year6X, $year6Y);
            $pdf->MultiCell(19, $tableHeight, $year6Grade6, 'TBRL', 'C', 1, 0);
            $year6X = $year6X+19;

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($year6X, $year6Y);
            $pdfBig->MultiCell(24, $tableHeight, $year6Remark6, 'TBRL', 'L', 1, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($year6X, $year6Y);
            $pdf->MultiCell(24, $tableHeight, $year6Remark6, 'TBRL', 'L', 1, 0);



            $year6X = 21;
            $year6Y = $year6Y +$tableHeight;

            for($i=0; $i<3; $i++) {

                $pdfBig->SetFont($Arial, 'B', 6, '', false);
                $pdfBig->SetXY($year6X, $year6Y);
                $pdfBig->MultiCell(19, $tableHeight, '', 'TBRL', 'L', 1, 0);

                $pdf->SetFont($Arial, 'B', 6, '', false);
                $pdf->SetXY($year6X, $year6Y);
                $pdf->MultiCell(19, $tableHeight, '', 'TBRL', 'L', 1, 0);
                $year6X = $year6X+19;

                $pdfBig->SetFont($Arial, 'B', 6, '', false);
                $pdfBig->SetXY($year6X, $year6Y);
                $pdfBig->MultiCell(116, $tableHeight,'', 'TBRL', 'L', 1, 0);

                $pdf->SetFont($Arial, 'B', 6, '', false);
                $pdf->SetXY($year6X, $year6Y);
                $pdf->MultiCell(116, $tableHeight,'', 'TBRL', 'L', 1, 0);
                $year6X = $year6X+116;

                $pdfBig->SetFont($Arial, 'B', 6, '', false);
                $pdfBig->SetXY($year6X, $year6Y);
                $pdfBig->MultiCell(19, $tableHeight, '', 'TBRL', 'C', 1, 0);

                $pdf->SetFont($Arial, 'B', 6, '', false);
                $pdf->SetXY($year6X, $year6Y);
                $pdf->MultiCell(19, $tableHeight, '', 'TBRL', 'C', 1, 0);
                $year6X = $year6X+19;

                $pdfBig->SetFont($Arial, 'B', 6, '', false);
                $pdfBig->SetXY($year6X, $year6Y);
                $pdfBig->MultiCell(24, $tableHeight, '', 'TBRL', 'C', 1, 0);

                $pdf->SetFont($Arial, 'B', 6, '', false);
                $pdf->SetXY($year6X, $year6Y);
                $pdf->MultiCell(24, $tableHeight, '', 'TBRL', 'C', 1, 0);
                $year6X = 21;
                $year6Y = $year6Y +$tableHeight;
            }
            // end year six table

            $bottomSecY = $year6Y-0.5;
            // echo $year3Y+3.6;
            $pdfBig->setCellPaddings( $left = '0', $top = '0.5', $right = '', $bottom = '');
            $pdfBig->SetFont($Arial, '', 6, '', false);
            $pdfBig->SetXY($xStart, $bottomSecY);
            $pdfBig->MultiCell(11, 0, 'AWARD : ', '', 'L', 0, 0);

            $pdf->setCellPaddings( $left = '0', $top = '0.5', $right = '', $bottom = '');
            $pdf->SetFont($Arial, '', 6, '', false);
            $pdf->SetXY($xStart, $bottomSecY);
            $pdf->MultiCell(11, 0, 'AWARD : ', '', 'L', 0, 0);


            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($xStart+10, $bottomSecY);
            $pdfBig->MultiCell(121, 0, $certificateAward, '', 'L', 0, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($xStart+10, $bottomSecY);
            $pdf->MultiCell(121, 0, $certificateAward, '', 'L', 0, 0);

            $pdfBig->SetFont($Arial, '', 6, '', false);
            $pdfBig->SetXY($xStart+120, $bottomSecY);
            $pdfBig->MultiCell(30, 0, 'DATE OF COMPLETION :', '', 'L', 0, 0);

            $pdf->SetFont($Arial, '', 6, '', false);
            $pdf->SetXY($xStart+120, $bottomSecY);
            $pdf->MultiCell(30, 0, 'DATE OF COMPLETION :', '', 'L', 0, 0);

            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($xStart+145, $bottomSecY);
            $pdfBig->MultiCell(30, 0, '15th October, 2022', '', 'L', 0, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($xStart+145, $bottomSecY);
            $pdf->MultiCell(30, 0, '15th October, 2022', '', 'L', 0, 0);


            $pdfBig->setCellPaddings( $left = '0', $top = '0.2', $right = '', $bottom = '');
            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($xStart, $bottomSecY+6);
            $pdfBig->SetTextColor(255,255,255);
            $pdfBig->SetFillColor(19,57,106);
            $pdfBig->MultiCell($pageWidth, 3, 'The medium of instruction is English. For key to grafes and remarks,see overleaf.', '', 'C', 1, 0);

            $pdf->setCellPaddings( $left = '0', $top = '0.2', $right = '', $bottom = '');
            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($xStart, $bottomSecY+6);
            $pdf->SetTextColor(255,255,255);
            $pdf->SetFillColor(19,57,106);
            $pdf->MultiCell($pageWidth, 3, 'The medium of instruction is English. For key to grafes and remarks,see overleaf.', '', 'C', 1, 0);

            $bottomSecY =$bottomSecY+6;

            $y01 = $bottomSecY+13;
            $pdfBig->SetFont($Arial, '', 6, '', false);
            $pdfBig->SetXY($xStart, $y01);
            $pdfBig->SetTextColor(0,0,0);
            $pdfBig->MultiCell(65, 0, '______________________________________________________', '', 'L', 0, 0);

            $pdf->SetFont($Arial, '', 6, '', false);
            $pdf->SetXY($xStart, $y01);
            $pdf->SetTextColor(0,0,0);
            $pdf->MultiCell(65, 0, '______________________________________________________', '', 'L', 0, 0);

            $y02 = $bottomSecY+16;
            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($xStart, $y02);
            $pdfBig->SetTextColor(0,0,0);
            $pdfBig->MultiCell(65, 0, 'Dean, School of Medicine', '', 'C', 0, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($xStart, $y02);
            $pdf->SetTextColor(0,0,0);
            $pdf->MultiCell(65, 0, 'Dean, School of Medicine', '', 'C', 0, 0);

            $y03 = $bottomSecY+19;
            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY($xStart, $y03);
            $pdfBig->SetTextColor(0,0,0);
            $pdfBig->MultiCell(65, 0, 'CAVENDISH UNIVERSITY ZAMBIA', '', 'C', 0, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY($xStart, $y03);
            $pdf->SetTextColor(0,0,0);
            $pdf->MultiCell(65, 0, 'CAVENDISH UNIVERSITY ZAMBIA', '', 'C', 0, 0);


            $y04 = $bottomSecY+14;
            $pdfBig->SetFont($Arial, '', 6, '', false);
            $pdfBig->SetXY(135, $y04);
            $pdfBig->SetTextColor(0,0,0);
            $pdfBig->MultiCell(65, 0, '______________________________________________________', '', 'R', 0, 0);

            $pdf->SetFont($Arial, '', 6, '', false);
            $pdf->SetXY(135, $y04);
            $pdf->SetTextColor(0,0,0);
            $pdf->MultiCell(65, 0, '______________________________________________________', '', 'R', 0, 0);



            $bY1 = $bottomSecY+17;
            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY(135, $bY1);
            $pdfBig->SetTextColor(0,0,0);
            $pdfBig->MultiCell(65, 0, 'Academic Registrar', '', 'C', 0, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY(135, $bY1);
            $pdf->SetTextColor(0,0,0);
            $pdf->MultiCell(65, 0, 'Academic Registrar', '', 'C', 0, 0);


            $bY2 = $bottomSecY+20;
            $pdfBig->SetFont($Arial, 'B', 6, '', false);
            $pdfBig->SetXY(135, $bY2);
            $pdfBig->SetTextColor(0,0,0);
            $pdfBig->MultiCell(65, 0, 'CAVENDISH UNIVERSITY ZAMBIA', '', 'C', 0, 0);

            $pdf->SetFont($Arial, 'B', 6, '', false);
            $pdf->SetXY(135, $bY2);
            $pdf->SetTextColor(0,0,0);
            $pdf->MultiCell(65, 0, 'CAVENDISH UNIVERSITY ZAMBIA', '', 'C', 0, 0);


            $bY3= $bottomSecY+44;
            $pdfBig->SetFont($Arial, '', 7, '', false);
            $pdfBig->SetXY($xStart, $bY3);
            $pdfBig->SetTextColor(161,150,148);
            $pdfBig->MultiCell($pageWidth, 0, 'To verify document check Control Code at:', '', 'C', 0, 0);

            $pdf->SetFont($Arial, '', 7, '', false);
            $pdf->SetXY($xStart, $bY3);
            $pdf->SetTextColor(161,150,148);
            $pdf->MultiCell($pageWidth, 0, 'To verify document check Control Code at:', '', 'C', 0, 0);

            $bY4=$bottomSecY+46;
            $pdfBig->SetFont($Arial, '', 7, '', false);
            $pdfBig->SetXY($xStart, $bY4);
            $pdfBig->SetTextColor(161,150,148);
            $pdfBig->MultiCell($pageWidth, 0, 'http://www.cavendishza.org/verify', '', 'C', 0, 0);

            $pdf->SetFont($Arial, '', 7, '', false);
            $pdf->SetXY($xStart, $bY4);
            $pdf->SetTextColor(161,150,148);
            $pdf->MultiCell($pageWidth, 0, 'http://www.cavendishza.org/verify', '', 'C', 0, 0);


            $bY5 = $bottomSecY+50;
            $pdfBig->SetFont($Arial, '', 7, '', false);
            $pdfBig->SetXY($xStart, $bY5);
            $pdfBig->SetTextColor(161,150,148);
            $pdfBig->MultiCell($pageWidth, 0, 'THIS TRANSCRIPT IS NOT VALID IT DOES NOT BEAR THE OFFICIAL SEAL OR IF IT HAS ANY ALTERNATIONS', '', 'C', 0, 0);

            $pdf->SetFont($Arial, '', 7, '', false);
            $pdf->SetXY($xStart, $bY5);
            $pdf->SetTextColor(161,150,148);
            $pdf->MultiCell($pageWidth, 0, 'THIS TRANSCRIPT IS NOT VALID IT DOES NOT BEAR THE OFFICIAL SEAL OR IF IT HAS ANY ALTERNATIONS', '', 'C', 0, 0);











            $pdf->Output('test.pdf', 'I');
        
        // }



        
        // for testing data


    }


   
}
