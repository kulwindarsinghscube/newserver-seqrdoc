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

                if($ext == 'xlsx' || $ext == 'Xlsx'){
                    $inputFileType = 'Xlsx';
                }
                else{
                    $inputFileType = 'Xls';
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
                
                $studentData = $this->fetchArrayData($sheet1,$highestRow1);

                

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
        $pdfData=array('studentDataOrg'=>$studentData[0],'subjectDataOrg'=>$studentData[1],'auth_site_id'=>$auth_site_id,'template_id'=>$template_id,'previewPdf'=>$previewPdf,'excelfile'=>$excelfile,"contractAddress"=>$contractAddress,"isBlockChain"=>$isBlockChain);
        
        $studentDataOrg=$pdfData['studentDataOrg'];
        $subjectDataOrg=$pdfData['subjectDataOrg'];
        $subjectDataOrg1=$pdfData['subjectDataOrg'];
        $template_id=$pdfData['template_id'];
        $previewPdf=$pdfData['previewPdf'];
        $excelfile=$pdfData['excelfile'];
        $auth_site_id=$pdfData['auth_site_id'];
        $previewWithoutBg=$previewPdf[1];
        $previewPdf=$previewPdf[0];
        $domain = \Request::getHost();
        $subdomain = explode('.', $domain);
        
        $systemConfig = SystemConfig::select('sandboxing','printer_name')->where('site_id',$auth_site_id)->first();
        $printer_name = $systemConfig['printer_name'];
        
        $pdfBig = new TCPDF('L', 'mm', array('297', '420'), true, 'UTF-8', false);
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

        //set fonts
        $timesNewRoman = TCPDF_FONTS::addTTFfont(public_path() . '\\' . $subdomain[0] . '\backend\canvas\fonts\Times-New-Roman.TTF', 'TrueTypeUnicode', '', 96);
        $arial = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\Arial.TTF', 'TrueTypeUnicode', '', 96);
        $arialb = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\Arialb.TTF', 'TrueTypeUnicode', '', 96);
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

        $log_serial_no = 1;
        // $cardDetails=$this->getNextCardNo('anuA3'); //not needed for this
        // $card_serial_no=$cardDetails->next_serial_no; //not needed for this
        $card_serial_no = '';
        $generated_documents=0;  

        // echo "<pre>";
        //     print_r($studentDataOrg);
        //     echo "</pre>";

        foreach ($studentDataOrg as $studentData) {
            
            //For Custom Loader
            $startTimeLoader =  date('Y-m-d H:i:s');

            $pdfBig->AddPage();
            
            //set background image
            $template_img_generate = public_path().'\\'.$subdomain[0].'\backend\canvas\bg_images\anu_a3_transcript.jpg';

            if($previewPdf==1){
                if($previewWithoutBg!=1){
                    $pdfBig->Image($template_img_generate, 0, 0, '420', '297', "JPG", '', 'R', true);
                }
            }
            $pdfBig->setPageMark();

            $pdf = new TCPDF('L', 'mm', array('297', '420'), true, 'UTF-8', false);
            $pdf->SetCreator(PDF_CREATOR);
            $pdf->SetAuthor('TCPDF');
            $pdf->SetTitle('Certificate');
            $pdf->SetSubject('');
            // remove default header/footer
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);
            $pdf->SetAutoPageBreak(false, 0);
            // add spot colors
            $pdf->AddSpotColor('Spot Red', 30, 100, 90, 10);        // For Invisible
            $pdf->AddSpotColor('Spot Dark Green', 100, 50, 80, 45); // clear text on bottom red and in clear text logo
            $pdf->AddPage();
            
            //set background image
            $template_img_generate = public_path().'\\'.$subdomain[0].'\backend\canvas\bg_images\anu_a3_transcript.jpg';
            if($previewPdf!=1){
                $pdf->Image($template_img_generate, 0, 0, '420', '297', "JPG", '', 'R', true);
            }
            $pdf->setPageMark();

            

            $unique_id = trim($studentData[6]);
            $student_id = trim($studentData[6]);
            
            $DOB = trim($studentData[10]);
            $candidate_name = trim($studentData[8]);
            $Batch = trim($studentData[5]);
            $Semester = trim($studentData[9]);
            $Programme = trim($studentData[3]);
            $major = trim($studentData[4]);
            $remarks = trim($studentData[229]);
            $remarksHu = trim($studentData[235]);
            $tec = trim($studentData[26]);
            $ecp = trim($studentData[27]);
            $Percentage = trim($studentData[17]);
            $doi = trim($studentData[18]);


            // $str=$studentData[6];
            // $codeContents = "[".$student_id." - ". $candidate_name ."]";
            // $codeContents .="\n";
            // $codeContents .= "[CGPA - ".$cgpa." (". $cgpaRemark .")]";
            // $codeContents .="\n";
            // $codeContents .= "[Percentage - ".$Percentage."%]";
            // $codeContents .="\n";
            // $codeContents .= $Programme."(".$major.")";
            // $codeContents .="\n\n".strtoupper(md5($str));
            // // $codeContents =$encryptedString = strtoupper(md5($str));
            // $qr_code_path = public_path().'\\'.$subdomain[0].'\backend\canvas\images\qr\/'.$encryptedString.'.png';
            // $qrCodex = 176;
            // $qrCodey = 15;
            // $qrCodeWidth =20;
            // $qrCodeHeight = 20;
            // \QrCode::size(75.6)
            //     ->backgroundColor(255, 255, 0)
            //     ->format('png')
            //     ->generate($codeContents, $qr_code_path);

            // $pdfBig->Image($qr_code_path, $qrCodex, $qrCodey, $qrCodeWidth,  $qrCodeHeight, '', '', '', false, 600);
            // $pdf->Image($qr_code_path, $qrCodex, $qrCodey, $qrCodeWidth,  $qrCodeHeight, '', '', '', false, 600);

        
            $pdfBig->SetFont($trebucb, '', 14, '', false);
            $pdfBig->SetTextColor(0, 0, 0);
            $pdfBig->SetXY(90, 11);
            $pdfBig->MultiCell(0, 0, "TRANSCRIPT", 0, "L", 0, 0, '', '', true, 0, true);


            $pdfBig->SetFont($trebucb, '', 10, '', false);
            $pdfBig->SetXY(80, 18);
            $pdfBig->MultiCell(42, 0, "STUDENT NAME:", 0, "R", 0, 0, '', '', true, 0, true);

            $pdfBig->SetFont($trebuc, '', 10, '', false);
            $pdfBig->SetXY(122, 18);
            $pdfBig->MultiCell(42, 0, $candidate_name, 0, "L", 0, 0, '', '', true, 0, true);


            $pdfBig->SetFont($trebucb, '', 10, '', false);
            $pdfBig->SetXY(80, 23);
            $pdfBig->MultiCell(42, 0, "STUDENT ID NUMBER: ", 0, "R", 0, 0, '', '', true, 0, true);

            $pdfBig->SetFont($trebuc, '', 10, '', false);
            $pdfBig->SetXY(122, 23);
            $pdfBig->MultiCell(42, 0, $student_id, 0, "L", 0, 0, '', '', true, 0, true);


            $pdfBig->SetFont($trebucb, '', 10, '', false);
            $pdfBig->SetXY(80, 28);
            $pdfBig->MultiCell(42, 0, "DATE OF BIRTH: ", 0, "R", 0, 0, '', '', true, 0, true);

            $pdfBig->SetFont($trebuc, '', 10, '', false);
            $pdfBig->SetXY(122, 28);
            $pdfBig->MultiCell(42, 0, $DOB, 0, "L", 0, 0, '', '', true, 0, true);

            $pdfBig->SetFont($trebucb, '', 10, '', false);
            $pdfBig->SetXY(80, 33);
            $pdfBig->MultiCell(42, 0, "BATCH: ", 0, "R", 0, 0, '', '', true, 0, true);

            $pdfBig->SetFont($trebuc, '', 10, '', false);
            $pdfBig->SetXY(122, 33);
            $pdfBig->MultiCell(42, 0, $Batch, 0, "L", 0, 0, '', '', true, 0, true);
            
            
            $pdfBig->SetLineStyle(array('width' => 0.3, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(109, 113, 115)));
            $pdfBig->SetFont($trebuc, '', 10, '', false);
            $pdfBig->SetXY(210, 40);
            $pdfBig->MultiCell(1, 207, '', 'L', "L", 0, 0, '', '', true, 0, true);


            $pdfBig->SetLineStyle(array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(137, 142, 145)));
            $pdfBig->setCellPaddings( $left = 0, $top = 0.8, $right = 0, $bottom = 0);
            
            $tableFont= 7;
            $pdfBig->SetFont($trebucb, '', $tableFont, '', false);
            $tableX= 13;
            $tableY= 40;
            $tableHeight= 4.5;
            $tableSpa1=20;
            $tableSpa2=18;
            $tableSpa3=20;
            $tableSpa4=96;
            $tableSpa5=13;
            $tableSpa6=13;
            $tableSpa7=13;

            $pdfBig->SetXY($tableX, $tableY);
            $pdfBig->MultiCell($tableSpa1, $tableHeight, 'SECTION', 'LTRB', "C", 0, 0, '', '', true, 0, true);
            $tableX = $tableX+$tableSpa1;

            $pdfBig->SetXY($tableX, $tableY);
            $pdfBig->MultiCell($tableSpa2, $tableHeight, 'SEMESTER', 'LTRB', "C", 0, 0, '', '', true, 0, true);
            $tableX = $tableX+$tableSpa2;

            $pdfBig->SetXY($tableX, $tableY);
            $pdfBig->MultiCell($tableSpa3, $tableHeight, 'CODE', 'LTRB', "C", 0, 0, '', '', true, 0, true);
            $tableX = $tableX+$tableSpa3;

            $pdfBig->SetXY($tableX, $tableY);
            $pdfBig->MultiCell($tableSpa4, $tableHeight, 'COURSE TITLE', 'LTRB', "C", 0, 0, '', '', true, 0, true);
            $tableX = $tableX+$tableSpa4;

            $pdfBig->SetXY($tableX, $tableY);
            $pdfBig->MultiCell($tableSpa5, $tableHeight, 'CR', 'LTRB', "C", 0, 0, '', '', true, 0, true);
            $tableX = $tableX+$tableSpa5;

            $pdfBig->SetXY($tableX, $tableY);
            $pdfBig->MultiCell($tableSpa6, $tableHeight, 'LG', 'LTRB', "C", 0, 0, '', '', true, 0, true);
            $tableX = $tableX+$tableSpa6;

            $pdfBig->SetXY($tableX, $tableY);
            $pdfBig->MultiCell($tableSpa7, $tableHeight, 'GP', 'LTRB', "C", 0, 0, '', '', true, 0, true);
            


            // second table 
            $tableX1= 214;
            $tableY1= 40;
            $tableSpa11=20;
            $tableSpa22=18;
            $tableSpa33=20;
            $tableSpa44=96;
            $tableSpa55=13;
            $tableSpa66=13;
            $tableSpa77=13;

            $pdfBig->SetXY($tableX1, $tableY1);
            $pdfBig->MultiCell($tableSpa11, $tableHeight, 'SECTION', 'LTRB', "C", 0, 0, '', '', true, 0, true);
            $tableX1 = $tableX1+$tableSpa11;

            $pdfBig->SetXY($tableX1, $tableY1);
            $pdfBig->MultiCell($tableSpa22, $tableHeight, 'SEMESTER', 'LTRB', "C", 0, 0, '', '', true, 0, true);
            $tableX1 = $tableX1+$tableSpa22;

            $pdfBig->SetXY($tableX1, $tableY1);
            $pdfBig->MultiCell($tableSpa33, $tableHeight, 'CODE', 'LTRB', "C", 0, 0, '', '', true, 0, true);
            $tableX1 = $tableX1+$tableSpa33;

            $pdfBig->SetXY($tableX1, $tableY1);
            $pdfBig->MultiCell($tableSpa44, $tableHeight, 'COURSE TITLE', 'LTRB', "C", 0, 0, '', '', true, 0, true);
            $tableX1 = $tableX1+$tableSpa44;

            $pdfBig->SetXY($tableX1, $tableY1);
            $pdfBig->MultiCell($tableSpa55, $tableHeight, 'CR', 'LTRB', "C", 0, 0, '', '', true, 0, true);
            $tableX1 = $tableX1+$tableSpa55;

            $pdfBig->SetXY($tableX1, $tableY1);
            $pdfBig->MultiCell($tableSpa66, $tableHeight, 'LG', 'LTRB', "C", 0, 0, '', '', true, 0, true);
            $tableX1 = $tableX1+$tableSpa66;

            $pdfBig->SetXY($tableX1, $tableY1);
            $pdfBig->MultiCell($tableSpa77, $tableHeight, 'GP', 'LTRB', "C", 0, 0, '', '', true, 0, true);
            // second table 


            $pdfBig->SetFont($trebuc, '', $tableFont, '', false);
            
            $subCount1 = 1;
            $subCount2 = 1;

            foreach($subjectDataOrg as $row => $arrayValue){
     
                if($arrayValue[0] == $student_id) {
                    if ($arrayValue[1] == "I" || $arrayValue[1] == "II" || $arrayValue[1] == "III" || $arrayValue[1] == "IV") {
                        if ($subCount1 <= 45) {
                            if(!empty($arrayValue[5])) {

                                $tableX = 13;
                                $tableY = $tableY+$tableHeight;
                                $pdfBig->SetXY($tableX, $tableY);
                                $pdfBig->setCellPaddings( $left = 0, $top = 0.8, $right = 0, $bottom = 0);
                                $pdfBig->MultiCell($tableSpa1, $tableHeight, $arrayValue[3], 'LTB', 'C', 0, 0, '', '', true, 0, true);

                                $pdf->SetXY($tableX, $tableY);
                                $pdf->setCellPaddings( $left = 0, $top = 0.8, $right = 0, $bottom = 0);
                                $pdf->MultiCell($tableSpa1, $tableHeight, $arrayValue[3], 'LTB', 'C', 0, 0, '', '', true, 0, true);

                                $tableX = $tableX+$tableSpa1;

                                $pdfBig->SetXY($tableX, $tableY);
                                $pdfBig->MultiCell($tableSpa2, $tableHeight, 'Semester-'.$arrayValue[1] , 'LTB', 'C', 0, 0, '', '', true, 0, true);

                                $pdf->SetXY($tableX, $tableY);
                                $pdf->MultiCell($tableSpa2, $tableHeight, 'Semester-'.$arrayValue[1] , 'LTB', 'C', 0, 0, '', '', true, 0, true);
                                $tableX = $tableX+$tableSpa2;

                                $pdfBig->SetXY($tableX, $tableY);
                                $pdfBig->MultiCell($tableSpa3, $tableHeight, $arrayValue[6], 'LTB', 'C', 0, 0, '', '', true, 0, true);

                                $pdf->SetXY($tableX, $tableY);
                                $pdf->MultiCell($tableSpa3, $tableHeight, $arrayValue[6], 'LTB', 'C', 0, 0, '', '', true, 0, true);

                                $tableX = $tableX+$tableSpa3;

                                $pdfBig->SetXY($tableX, $tableY);
                                $pdfBig->MultiCell($tableSpa4, $tableHeight, $arrayValue[5], 'LTB', 'C', 0, 0, '', '', true, 0, true);

                                $pdf->SetXY($tableX, $tableY);
                                $pdf->MultiCell($tableSpa4, $tableHeight, $arrayValue[5], 'LTB', 'C', 0, 0, '', '', true, 0, true);

                                $tableX = $tableX+$tableSpa4;

                                $pdfBig->SetXY($tableX, $tableY);
                                $pdf->SetXY($tableX, $tableY);
                                if(is_numeric($arrayValue[15])) {
                                    $crPoint = number_format($arrayValue[15],2);
                                } else {
                                    $crPoint = $arrayValue[15];
                                }
                                $pdfBig->MultiCell($tableSpa5, $tableHeight, $crPoint , 'LTB', 'C', 0, 0, '', '', true, 0, true);
                                $pdf->MultiCell($tableSpa5, $tableHeight, $crPoint , 'LTB', 'C', 0, 0, '', '', true, 0, true);
                                $tableX = $tableX+$tableSpa5;

                                $pdfBig->SetXY($tableX, $tableY);
                                $pdfBig->MultiCell($tableSpa6, $tableHeight, $arrayValue[13], 'LTB', 'C', 0, 0, '', '', true, 0, true);
                                $pdf->SetXY($tableX, $tableY);
                                $pdf->MultiCell($tableSpa6, $tableHeight, $arrayValue[13], 'LTB', 'C', 0, 0, '', '', true, 0, true);

                                $tableX = $tableX+$tableSpa6;

                                if(is_numeric($arrayValue[14])) {
                                    $gpPoint = number_format($arrayValue[14],2);
                                } else {
                                    $gpPoint = $arrayValue[14];
                                }

                                $pdfBig->SetXY($tableX, $tableY);
                                $pdfBig->MultiCell($tableSpa7, $tableHeight, $gpPoint, 'LTBR', 'C', 0, 0, '', '', true, 0, true);

                                $pdf->SetXY($tableX, $tableY);
                                $pdf->MultiCell($tableSpa7, $tableHeight, $gpPoint, 'LTBR', 'C', 0, 0, '', '', true, 0, true);


                                $subCount1++;

                            }
                        }

                    } else {
                        if ($subCount2 <= 45) {
                            if(!empty($arrayValue[5])) {

                                $tableX1 = 214;
                                $tableY1 = $tableY1+$tableHeight;
                                $pdfBig->SetXY($tableX1, $tableY1);
                                $pdfBig->setCellPaddings( $left = 0, $top = 0.8, $right = 0, $bottom = 0);
                                $pdfBig->MultiCell($tableSpa11, $tableHeight, $arrayValue[3], 'LTB', 'C', 0, 0, '', '', true, 0, true);

                                $pdf->SetXY($tableX1, $tableY1);
                                $pdf->setCellPaddings( $left = 0, $top = 0.8, $right = 0, $bottom = 0);
                                $pdf->MultiCell($tableSpa11, $tableHeight, $arrayValue[3], 'LTB', 'C', 0, 0, '', '', true, 0, true);

                                $tableX1 = $tableX1+$tableSpa11;

                                $pdfBig->SetXY($tableX1, $tableY1);
                                $pdfBig->MultiCell($tableSpa22, $tableHeight, 'Semester-'.$arrayValue[1], 'LTB', 'C', 0, 0, '', '', true, 0, true);

                                $pdf->SetXY($tableX1, $tableY1);
                                $pdf->MultiCell($tableSpa22, $tableHeight, 'Semester-'.$arrayValue[1], 'LTB', 'C', 0, 0, '', '', true, 0, true);
                                $tableX1 = $tableX1+$tableSpa22;

                                $pdfBig->SetXY($tableX1, $tableY1);
                                $pdfBig->MultiCell($tableSpa33, $tableHeight, $arrayValue[6], 'LTB', 'C', 0, 0, '', '', true, 0, true);

                                $pdf->SetXY($tableX1, $tableY1);
                                $pdf->MultiCell($tableSpa33, $tableHeight, $arrayValue[6], 'LTB', 'C', 0, 0, '', '', true, 0, true);

                                $tableX1 = $tableX1+$tableSpa33;

                                $pdfBig->SetXY($tableX1, $tableY1);
                                $pdfBig->MultiCell($tableSpa44, $tableHeight, $arrayValue[5], 'LTB', 'C', 0, 0, '', '', true, 0, true);

                                $pdf->SetXY($tableX1, $tableY1);
                                $pdf->MultiCell($tableSpa44, $tableHeight, $arrayValue[5], 'LTB', 'C', 0, 0, '', '', true, 0, true);

                                $tableX1 = $tableX1+$tableSpa44;

                                $pdfBig->SetXY($tableX1, $tableY1);
                                $pdf->SetXY($tableX1, $tableY1);
                                if(is_numeric($arrayValue[15])) {
                                    $crPoint = number_format($arrayValue[15],2);
                                } else {
                                    $crPoint = $arrayValue[15];
                                }
                                $pdfBig->MultiCell($tableSpa55, $tableHeight, $crPoint , 'LTB', 'C', 0, 0, '', '', true, 0, true);
                                $pdf->MultiCell($tableSpa55, $tableHeight, $crPoint , 'LTB', 'C', 0, 0, '', '', true, 0, true);
                                $tableX1 = $tableX1+$tableSpa55;

                                $pdfBig->SetXY($tableX1, $tableY1);
                                $pdfBig->MultiCell($tableSpa66, $tableHeight, $arrayValue[13], 'LTB', 'C', 0, 0, '', '', true, 0, true);
                                $pdf->SetXY($tableX1, $tableY1);
                                $pdf->MultiCell($tableSpa66, $tableHeight, $arrayValue[13], 'LTB', 'C', 0, 0, '', '', true, 0, true);

                                $tableX1 = $tableX1+$tableSpa66;

                                if(is_numeric($arrayValue[14])) {
                                    $gpPoint = number_format($arrayValue[14],2);
                                } else {
                                    $gpPoint = $arrayValue[14];
                                }

                                $pdfBig->SetXY($tableX1, $tableY1);
                                $pdfBig->MultiCell($tableSpa77, $tableHeight, $gpPoint, 'LTBR', 'C', 0, 0, '', '', true, 0, true);

                                $pdf->SetXY($tableX1, $tableY1);
                                $pdf->MultiCell($tableSpa77, $tableHeight, $gpPoint, 'LTBR', 'C', 0, 0, '', '', true, 0, true);

                                $subCount2++;

                            }
                        }

                    }

                
                }

            }
            
            if($subCount1 <= 45) {
                $emptyBoxRow1 = 45 - $subCount1;
                
                for ($emptyBoxRow = 0; $emptyBoxRow <= $emptyBoxRow1; $emptyBoxRow++) {
                    $tableX = 13;
                    $tableY = $tableY+$tableHeight;
                    $pdfBig->SetXY($tableX, $tableY);
                    $pdfBig->setCellPaddings( $left = 0, $top = 1, $right = 0, $bottom = 0);
                    $pdfBig->MultiCell($tableSpa1, $tableHeight, '', 'LTB', 'C', 0, 0, '', '', true, 0, true);

                    $pdf->SetXY($tableX, $tableY);
                    $pdf->setCellPaddings( $left = 0, $top = 1, $right = 0, $bottom = 0);
                    $pdf->MultiCell($tableSpa1, $tableHeight, '', 'LTB', 'C', 0, 0, '', '', true, 0, true);

                    $tableX = $tableX+$tableSpa1;

                    $pdfBig->SetXY($tableX, $tableY);
                    $pdfBig->MultiCell($tableSpa2, $tableHeight, '', 'LTB', 'C', 0, 0, '', '', true, 0, true);
                    $pdf->SetXY($tableX, $tableY);
                    $pdf->MultiCell($tableSpa2, $tableHeight, '', 'LTB', 'C', 0, 0, '', '', true, 0, true);

                    $tableX = $tableX+$tableSpa2;

                    $pdfBig->SetXY($tableX, $tableY);
                    $pdfBig->MultiCell($tableSpa3, $tableHeight, '', 'LTB', 'C', 0, 0, '', '', true, 0, true);

                    $pdf->SetXY($tableX, $tableY);
                    $pdf->MultiCell($tableSpa3, $tableHeight, '', 'LTB', 'C', 0, 0, '', '', true, 0, true);

                    $tableX = $tableX+$tableSpa3;

                    $pdfBig->SetXY($tableX, $tableY);
                    $pdfBig->MultiCell($tableSpa4, $tableHeight, '', 'LTB', 'C', 0, 0, '', '', true, 0, true);

                    $pdf->SetXY($tableX, $tableY);
                    $pdf->MultiCell($tableSpa4, $tableHeight, '', 'LTB', 'C', 0, 0, '', '', true, 0, true);
                    $tableX = $tableX+$tableSpa4;

                    $pdfBig->SetXY($tableX, $tableY);
                    $pdfBig->MultiCell($tableSpa5, $tableHeight, '' , 'LTB', 'C', 0, 0, '', '', true, 0, true);
                    $pdf->SetXY($tableX, $tableY);
                    $pdf->MultiCell($tableSpa5, $tableHeight, '' , 'LTB', 'C', 0, 0, '', '', true, 0, true);

                    $tableX = $tableX+$tableSpa5;

                    $pdfBig->SetXY($tableX, $tableY);
                    $pdfBig->MultiCell($tableSpa6, $tableHeight, '', 'LTB', 'C', 0, 0, '', '', true, 0, true);
                    $pdf->SetXY($tableX, $tableY);
                    $pdf->MultiCell($tableSpa6, $tableHeight, '', 'LTB', 'C', 0, 0, '', '', true, 0, true);
                    $tableX = $tableX+$tableSpa6;

                    $pdfBig->SetXY($tableX, $tableY);
                    $pdfBig->MultiCell($tableSpa7, $tableHeight, '', 'LTBR', 'C', 0, 0, '', '', true, 0, true);
                    $pdf->SetXY($tableX, $tableY);
                    $pdf->MultiCell($tableSpa7, $tableHeight, '', 'LTBR', 'C', 0, 0, '', '', true, 0, true);

                }
            }


            if($subCount2 <= 45) {
                $emptyBoxRow2 = 45 - $subCount2;
                
                for ($emptyBoxRow11 = 0; $emptyBoxRow11 <= $emptyBoxRow2; $emptyBoxRow11++) {
                    $tableX1 = 214;
                    $tableY1 = $tableY1+$tableHeight;
                    $pdfBig->SetXY($tableX1, $tableY1);
                    $pdfBig->setCellPaddings( $left = 0, $top = 1, $right = 0, $bottom = 0);
                    $pdfBig->MultiCell($tableSpa11, $tableHeight, '', 'LTB', 'C', 0, 0, '', '', true, 0, true);

                    $pdf->SetXY($tableX1, $tableY1);
                    $pdf->setCellPaddings( $left = 0, $top = 1, $right = 0, $bottom = 0);
                    $pdf->MultiCell($tableSpa11, $tableHeight, '', 'LTB', 'C', 0, 0, '', '', true, 0, true);

                    $tableX1 = $tableX1+$tableSpa11;

                    $pdfBig->SetXY($tableX1, $tableY1);
                    $pdfBig->MultiCell($tableSpa22, $tableHeight, '', 'LTB', 'C', 0, 0, '', '', true, 0, true);
                    $pdf->SetXY($tableX1, $tableY1);
                    $pdf->MultiCell($tableSpa22, $tableHeight, '', 'LTB', 'C', 0, 0, '', '', true, 0, true);

                    $tableX1 = $tableX1+$tableSpa22;

                    $pdfBig->SetXY($tableX1, $tableY1);
                    $pdfBig->MultiCell($tableSpa33, $tableHeight, '', 'LTB', 'C', 0, 0, '', '', true, 0, true);

                    $pdf->SetXY($tableX1, $tableY1);
                    $pdf->MultiCell($tableSpa33, $tableHeight, '', 'LTB', 'C', 0, 0, '', '', true, 0, true);

                    $tableX1 = $tableX1+$tableSpa33;

                    $pdfBig->SetXY($tableX1, $tableY1);
                    $pdfBig->MultiCell($tableSpa44, $tableHeight, '', 'LTB', 'C', 0, 0, '', '', true, 0, true);

                    $pdf->SetXY($tableX1, $tableY1);
                    $pdf->MultiCell($tableSpa44, $tableHeight, '', 'LTB', 'C', 0, 0, '', '', true, 0, true);
                    $tableX1 = $tableX1+$tableSpa44;

                    $pdfBig->SetXY($tableX1, $tableY1);
                    $pdfBig->MultiCell($tableSpa55, $tableHeight, '' , 'LTB', 'C', 0, 0, '', '', true, 0, true);
                    $pdf->SetXY($tableX1, $tableY1);
                    $pdf->MultiCell($tableSpa55, $tableHeight, '' , 'LTB', 'C', 0, 0, '', '', true, 0, true);

                    $tableX1 = $tableX1+$tableSpa55;

                    $pdfBig->SetXY($tableX1, $tableY1);
                    $pdfBig->MultiCell($tableSpa66, $tableHeight, '', 'LTB', 'C', 0, 0, '', '', true, 0, true);
                    $pdf->SetXY($tableX1, $tableY1);
                    $pdf->MultiCell($tableSpa66, $tableHeight, '', 'LTB', 'C', 0, 0, '', '', true, 0, true);
                    $tableX1 = $tableX1+$tableSpa66;

                    $pdfBig->SetXY($tableX1, $tableY1);
                    $pdfBig->MultiCell($tableSpa77, $tableHeight, '', 'LTBR', 'C', 0, 0, '', '', true, 0, true);
                    $pdf->SetXY($tableX1, $tableY1);
                    $pdf->MultiCell($tableSpa77, $tableHeight, '', 'LTBR', 'C', 0, 0, '', '', true, 0, true);

                }
            }

            // table second upper heading data
            $newArraySession = [];
            
            foreach($subjectDataOrg1 as $row => $innerArray){
                if($innerArray[0] == $student_id){
                    // if($innerArray[1] == 'V') {
                    //     break;
                    // }
                    $newArraySession[$row][0] = $innerArray[0];
                    $newArraySession[$row][1] = $innerArray[2];
                    $newArraySession[$row][2] = $innerArray[4];
                }
            }
            $first = reset($newArraySession);
            $last = end($newArraySession);




            $pdfBig->SetFont($trebucb, '',10, '', false);
            $pdfBig->SetXY(220, 18);
            $pdfBig->MultiCell(58, 0, "PROGRAMME:", 0, "R", 0, 0, '', '', true, 0, true);

            $pdf->SetFont($trebucb, '',10, '', false);
            $pdf->SetXY(220, 18);
            $pdf->MultiCell(58, 0, "PROGRAMME:", 0, "R", 0, 0, '', '', true, 0, true);


            $pdfBig->SetFont($trebuc, '',10, '', false);
            $pdfBig->SetXY(280, 18);
            $pdfBig->MultiCell(60, 0, $Programme, 0, "L", 0, 0, '', '', true, 0, true);

            $pdf->SetFont($trebuc, '',10, '', false);
            $pdf->SetXY(280, 18);
            $pdf->MultiCell(60, 0, $Programme, 0, "L", 0, 0, '', '', true, 0, true);


            $pdfBig->SetFont($trebucb, '',10, '', false);
            $pdfBig->SetXY(220, 23);
            $pdfBig->MultiCell(58, 0, "MAJOR| MINOR:", 0, "R", 0, 0, '', '', true, 0, true);

            $pdf->SetFont($trebucb, '',10, '', false);
            $pdf->SetXY(220, 23);
            $pdf->MultiCell(58, 0, "MAJOR| MINOR:", 0, "R", 0, 0, '', '', true, 0, true);


            $pdfBig->SetFont($trebuc, '',10, '', false);
            $pdfBig->SetXY(280, 23);
            $pdfBig->MultiCell(60, 0, $major.' |', 0, "L", 0, 0, '', '', true, 0, true);

            $pdf->SetFont($trebuc, '',10, '', false);
            $pdf->SetXY(280, 23);
            $pdf->MultiCell(60, 0, $major.' |', 0, "L", 0, 0, '', '', true, 0, true);





            $pdfBig->SetFont($trebucb, '',10, '', false);
            $pdfBig->SetXY(220, 28);
            $pdfBig->MultiCell(58, 0, "MEDIUM OF INSTRUCTION:   ", 0, "R", 0, 0, '', '', true, 0, true);

            $pdf->SetFont($trebucb, '',10, '', false);
            $pdf->SetXY(220, 28);
            $pdf->MultiCell(58, 0, "MEDIUM OF INSTRUCTION:   ", 0, "R", 0, 0, '', '', true, 0, true);



            $pdfBig->SetFont($trebuc, '',10, '', false);
            $pdfBig->SetXY(280, 28);
            $pdfBig->MultiCell(60, 0, 'ENGLISH', 0, "L", 0, 0, '', '', true, 0, true);

            $pdf->SetFont($trebuc, '',10, '', false);
            $pdf->SetXY(280, 28);
            $pdf->MultiCell(60, 0, 'ENGLISH', 0, "L", 0, 0, '', '', true, 0, true);



            $pdfBig->SetFont($trebucb, '',10, '', false);
            $pdfBig->SetXY(220, 33);
            $pdfBig->MultiCell(58, 0, "DURATION", 0, "R", 0, 0, '', '', true, 0, true);

            $pdf->SetFont($trebucb, '',10, '', false);
            $pdf->SetXY(220, 33);
            $pdf->MultiCell(58, 0, "DURATION", 0, "R", 0, 0, '', '', true, 0, true);
            
            $pdfBig->SetFont($trebuc, '',10, '', false);
            $pdfBig->SetXY(280, 33);
            $pdfBig->MultiCell(60, 0, $first[1].' To '.$last[1] , 0, "L", 0, 0, '', '', true, 0, true);


            $pdf->SetFont($trebuc, '',10, '', false);
            $pdf->SetXY(280, 33);
            $pdf->MultiCell(60, 0, $first[1].' To '.$last[1] , 0, "L", 0, 0, '', '', true, 0, true);

            // table second upper heading data

            // table first under data
            $pdfBig->setCellPaddings( $left = 0, $top = 0.8, $right = 0, $bottom = 0);
            $pdf->setCellPaddings( $left = 0, $top = 0.8, $right = 0, $bottom = 0);

            $y2 = $tableY+5.5;
            $secondY2 = $tableY+5.5;
            
            $pdfBig->SetFont($trebucb, '', 7, '', false);  
            $pdfBig->setCellPaddings( $left = 0, $top = 1.9, $right = 0, $bottom = 0);
            $pdfBig->SetXY(13, $y2);
            $pdfBig->MultiCell(45, 8, 'Total Earned Credits', 'LT', 'C', 0, 0, '', '', true, 0, true);

            $pdf->setCellPaddings( $left = 0, $top = 1.9, $right = 0, $bottom = 0);
            $pdf->SetXY(13, $y2);
            $pdf->MultiCell(45, 8, 'Total Earned Credits', 'LT', 'C', 0, 0, '', '', true, 0, true);



            $pdfBig->setCellPaddings( $left = 0, $top = 1.2, $right = 0, $bottom = 0);
            $pdfBig->SetFont($trebucb, '', 7, '', false);  
            $pdfBig->SetXY(58, $y2);
            $pdfBig->MultiCell(54, 8, 'Earned Credit Points ', 'LT', 'C', 0, 0, '', '', true, 0, true);

            $pdf->setCellPaddings( $left = 0, $top = 1.2, $right = 0, $bottom = 0);
            $pdf->SetFont($trebucb, '', 7, '', false);  
            $pdf->SetXY(58, $y2);
            $pdf->MultiCell(54, 8, 'Earned Credit Points ', 'LT', 'C', 0, 0, '', '', true, 0, true);



            $pdfBig->SetFont($trebucb, '', 5, '', false);  
            $pdfBig->SetXY(58, $y2+3);
            $pdfBig->MultiCell(54, 8, '∑(Credit X Grade Points) ', 0, 'C', 0, 0, '', '', true, 0, true);

            $pdf->SetFont($trebucb, '', 5, '', false);  
            $pdf->SetXY(58, $y2+3);
            $pdf->MultiCell(54, 8, '∑(Credit X Grade Points) ', 0, 'C', 0, 0, '', '', true, 0, true);


            $pdfBig->setCellPaddings( $left = 0, $top = 1.9, $right = 0, $bottom = 0);
            $pdfBig->SetFont($trebucb, '', 7, '', false);  
            $pdfBig->SetXY(112, $y2);
            $pdfBig->MultiCell(60, 8, 'Cumulative Grade Point Average (CGPA) ', 'LTR', 'C', 0, 0, '', '', true, 0, true);

            $pdf->setCellPaddings( $left = 0, $top = 1.9, $right = 0, $bottom = 0);
            $pdf->SetFont($trebucb, '', 7, '', false);  
            $pdf->SetXY(112, $y2);
            $pdf->MultiCell(60, 8, 'Cumulative Grade Point Average (CGPA) ', 'LTR', 'C', 0, 0, '', '', true, 0, true);



            $pdfBig->SetXY(13, $y2 =$y2+8);
            $pdfBig->MultiCell(45, 7,$tec , 'LTB', 'C', 0, 0, '', '', true, 0, true);

            $pdf->SetXY(13, $y2);
            $pdf->MultiCell(45, 7,$tec , 'LTB', 'C', 0, 0, '', '', true, 0, true);



            $pdfBig->SetXY(58, $y2);
            $pdfBig->MultiCell(54, 7, $ecp, 'LTB', 'C', 0, 0, '', '', true, 0, true);

            $pdf->SetXY(58, $y2);
            $pdf->MultiCell(54, 7, $ecp, 'LTB', 'C', 0, 0, '', '', true, 0, true);


            if (is_numeric($last[2])) {
                $cgpa = number_format($last[2],2);
            } else {
                $cgpa = $last[2];
            }
            
            if($cgpa >= 7.5) {
                $cgpaRemark = "First class with honor";
            } elseif (7.49 > $cgpa && $cgpa >= 6.5) {
                $cgpaRemark = "First class";
            } elseif (6.49 > $cgpa && $cgpa >= 5.0) {
                $cgpaRemark = "Second class";
            }elseif (5.0 > $cgpa) {
                $cgpaRemark = "Fail";
            } else {
                $cgpaRemark ="-";
            }


            
            //$pdfBig->setCellPaddings( $left = 0, $top = 0.5, $right = 0, $bottom = 0);
            $pdfBig->SetXY(112, $y2);
            $pdfBig->MultiCell(60, 7,$cgpa .' ['.$cgpaRemark .']' , 'LTBR', 'C', 0, 0, '', '', true, 0, true);

            $pdf->setCellPaddings( $left = 0, $top = 0.5, $right = 0, $bottom = 0);
            $pdf->SetXY(112, $y2);
            $pdf->MultiCell(60, 7,$cgpa .' ['.$cgpaRemark .']'  , 'LTBR', 'C', 0, 0, '', '', true, 0, true);

            $pdfBig->SetFont($trebuc, '', 6, '', false);
            $pdfBig->SetXY(14, $y2+6);
            $pdfBig->MultiCell(60, 7,'CR = Credit | LG = Letter Grade | GP = Grade Point'  , 0, 'L', 0, 0, '', '', true, 0, true);

            
            $pdf->SetXY(14, $y2+6);
            $pdf->MultiCell(60, 7,'CR = Credit | LG = Letter Grade | GP = Grade Point'  , 0, 'L', 0, 0, '', '', true, 0, true);


            $pdfBig->SetFont($trebuc, '', 7, '', false);
            $pdfBig->SetXY(120, $y2+6);
            $pdfBig->MultiCell(60, 7,'ISSUE DATE: '.$doi  , 0, 'C', 0, 0, '', '', true, 0, true);

            
            $pdf->SetXY(120, $y2+6);
            $pdf->MultiCell(60, 7,'ISSUE DATE: ' .$doi , 0, 'C', 0, 0, '', '', true, 0, true);
            
            $serial_no=$GUID=$studentData[6];
			//qr code    
			$dt = date("_ymdHis");
			$str=$GUID.$dt;
            // $codeContents = $QR_Output."\n\n".strtoupper(md5($str));
			// $codeContents = "[".$student_id." - ". $candidate_name ."]"."\n\n" ."\n\n".strtoupper(md5($str));
            $codeContents = "[".$student_id." - ". $candidate_name ."]";
            $codeContents .="\n";
            $codeContents .= "[CGPA - ".$cgpa." (". $cgpaRemark .")]";
            $codeContents .="\n";
            $codeContents .= "[Percentage - ".$Percentage."%]";
            $codeContents .="\n";
			$codeContents .= $Programme."(".$major.")";
            $codeContents .="\n\n".strtoupper(md5($str));
            $encryptedString = strtoupper(md5($str));
			$qr_code_path = public_path().'\\'.$subdomain[0].'\backend\canvas\images\qr\/'.$encryptedString.'.png';
			$qrCodex = 180; 
			$qrCodey = 249;
			$qrCodeWidth =22;
			$qrCodeHeight = 22;
			$ecc = 'L';
			$pixel_Size = 1;
			$frame_Size = 1;  
			// \PHPQRCode\QRcode::png($codeContents, $qr_code_path, $ecc, $pixel_Size, $frame_Size);

            \QrCode::backgroundColor(255, 255, 0)            
                ->format('png')        
                ->size(500)    
                ->generate($codeContents, $qr_code_path);
            
			$pdf->Image($qr_code_path, $qrCodex, $qrCodey, $qrCodeWidth,  $qrCodeHeight, 'png', '', true, false);   
			$pdf->setPageMark(); 
			$pdfBig->Image($qr_code_path, $qrCodex, $qrCodey, $qrCodeWidth,  $qrCodeHeight, 'png', '', true, false); 
			$pdfBig->setPageMark(); 

            


            // table first under data

            $pdfBig->SetFont($trebuc, '', 7, '', false);
            $pdfBig->SetXY(215, 247);
            $pdfBig->MultiCell(200, 0,$remarks , 0, 'L', 0, 0, '', '', true, 0, true);
            

            $pdfBig->SetFont($trebuc, '', 7, '', false);
            $pdfBig->SetXY(215, 250);
            $pdfBig->MultiCell(200, 0,'Percentage of marks scored = (CGPA x10) = '.$Percentage.'%' , 0, 'L', 0, 0, '', '', true, 0, true);

            $newArraySession = [];
            

            $pdfBig->SetFont($trebuc, '', 8, '', false);
            
            $pdfBig->SetXY(220, 269);
            $pdfBig->MultiCell(0, 0,'Ms. Jigisha Patel' , 0, 'L', 0, 0, '', '', true, 0, true);

            $pdfBig->SetXY(220, 272);
            $pdfBig->MultiCell(0, 0,'Exam Assistant' , 0, 'L', 0, 0, '', '', true, 0, true);

            $pdfBig->SetXY(220, 275);
            $pdfBig->MultiCell(0, 0,'Prepared by' , 0, 'L', 0, 0, '', '', true, 0, true);

            $pdf->SetFont($trebuc, '', 8, '', false);
            
            $pdf->SetXY(220, 269);
            $pdf->MultiCell(0, 0,'Ms. Jigisha Patel' , 0, 'L', 0, 0, '', '', true, 0, true);

            $pdf->SetXY(220, 272);
            $pdf->MultiCell(0, 0,'Exam Assistant' , 0, 'L', 0, 0, '', '', true, 0, true);

            $pdf->SetXY(220, 275);
            $pdf->MultiCell(0, 0,'Prepared by' , 0, 'L', 0, 0, '', '', true, 0, true);





            $pdfBig->SetXY(280, 269);
            $pdfBig->MultiCell(0, 0,'Prof. Suhas Toshniwal' , 0, 'L', 0, 0, '', '', true, 0, true);

            $pdfBig->SetXY(280, 272);
            $pdfBig->MultiCell(0, 0,'Manager at Examination' , 0, 'L', 0, 0, '', '', true, 0, true);

            $pdfBig->SetXY(280, 275);
            $pdfBig->MultiCell(0, 0,'Verified by' , 0, 'L', 0, 0, '', '', true, 0, true);

            $pdf->SetXY(280, 269);
            $pdf->MultiCell(0, 0,'Prof. Suhas Toshniwal' , 0, 'L', 0, 0, '', '', true, 0, true);

            $pdf->SetXY(280, 272);
            $pdf->MultiCell(0, 0,'Manager at Examination' , 0, 'L', 0, 0, '', '', true, 0, true);

            $pdf->SetXY(280, 275);
            $pdf->MultiCell(0, 0,'Verified by' , 0, 'L', 0, 0, '', '', true, 0, true);



            $pdfBig->SetXY(340, 269);
            $pdfBig->MultiCell(0, 0,'Prof. Jasmine Gohil' , 0, 'L', 0, 0, '', '', true, 0, true);

            $pdfBig->SetXY(340, 272);
            $pdfBig->MultiCell(0, 0,'Controller of Examination' , 0, 'L', 0, 0, '', '', true, 0, true);

            $pdfBig->SetXY(340, 275);
            $pdfBig->MultiCell(0, 0,'Verified by' , 0, 'L', 0, 0, '', '', true, 0, true);

            $pdf->SetXY(340, 269);
            $pdf->MultiCell(0, 0,'Prof. Jasmine Gohil' , 0, 'L', 0, 0, '', '', true, 0, true);

            $pdf->SetXY(340, 272);
            $pdf->MultiCell(0, 0,'Controller of Examination' , 0, 'L', 0, 0, '', '', true, 0, true);

            $pdf->SetXY(340, 275);
            $pdf->MultiCell(0, 0,'Verified by' , 0, 'L', 0, 0, '', '', true, 0, true);



            
            $pdfBig->setCellPaddings( $left = 1, $top = 1, $right = 1, $bottom = 0);
            $pdf->setCellPaddings( $left = 1, $top = 1, $right = 1, $bottom = 0);


            // back page
            $points = 10;

            

            /*Point Page Start*/
			$pdfBig->AddPage();
			$back_img = public_path().'\\'.$subdomain[0].'\backend\canvas\bg_images\\anu_a3_transcript_back.jpg';   
            
			if($previewPdf==1){
				if($previewWithoutBg!=1){
                    $pdfBig->Image($back_img, 0, 0, '420', '297', "JPG", '', 'R', true);

				}
			}
			$pdfBig->setPageMark();
            
            //$pdfBig->SetLineStyle(array('width' => 0.3, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(109, 113, 115)));
            $pdfBig->SetLineStyle(array('width' => 0.3, 'cap' => 'butt', 'join' => 'miter', 'dash' => 4, 'color' => array(109, 113, 115)));
            $pdfBig->SetFont($trebuc, '', 10, '', false);
            $pdfBig->SetXY(210, 20);
            $pdfBig->MultiCell(1, 225, '', 'L', "L", 0, 0, '', '', true, 0, true);
            $pdfBig->SetLineStyle(array('width' => 0.3, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(109, 113, 115)));
            
			if($points=="4"){
				$grade_title='Grade Points: <span style="font-size:10;font-family:'.$trebuc.';">4 point grading system with corresponding numeric grade & letter grade as given below</span>';
				$grades_count = 13;
				$grades = array (
					array("96-100","A+","4.00","Distinguished"),
					array("91-95","A","3.80","Excellent"),
					array("86-90","A-","3.60","Very Good"),
					array("81-85","B+","3.40","Good"),
					array("76-80","B","3.20","High satisfactory"),
					array("71-75","B-","3.00","Above satisfactory"),
					array("66-70","C+","2.80","Satisfactory"),
					array("61-65","C","2.60","Less than satisfactory"),
					array("56-60","C-","2.40","Low satisfactory"),
					array("50-55","D","2.00","Poor"),
					array("Below 50","F","0.00","Fail"),
					array("Non-Credit","NC","--",""),
					array("Pass","P","--","")
				);	
				$cgpa_count = 5;
				$cgpa_class = array (
					array("GPA ≥ 3.50","Excellent"),
					array("3.50 > GPA ≥ 3.00","Very Good"),
					array("3.00 > GPA ≥ 2.50","Good"),
					array("2.50 > GPA ≥ 2.00","Above Average"),
					array("2.00 > GPA","Unsatisfactory")
				);	
				/*$list_count = 3;
				$list = array (
					array("●&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Percentage of marks scored = (GPA/4) * 100"),
					array("●&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;50% is passing criteria in each course."),
					array("●&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;MEDIUM OF INSTRUCTIONS: ENGLISH")
				);	*/
                $list_count = 2;
                $list = array (
					array("●&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Percentage of marks scored = (GPA/4) * 100"),
					array("●&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;MEDIUM OF INSTRUCTIONS: ENGLISH")
				);
			}
			elseif($points=="10"){
				$grade_title='Grade Points: <span style="font-size:10;font-family:'.$trebuc.';">10 point grading system with corresponding numeric grade & letter grade as given below</span>';
				$grades_count = 9;
				$grades = array (
					array("91-100","A+","10.00"),
					array("81-90","A","9.00"),					
					array("71-80","B+","8.00"),
					array("61-70","B","7.00"),
					array("56-60","C+","6.00"),
					array("50-55","C","5.00"),
					array("G(Grace)","As per University norms","5.00"),
					array("Below 50","F","0.00"),
					array("Absent (AB)","AB","0.00")
				);	
				$cgpa_count = 4;
				$cgpa_class = array (
					array("7.5 and above","First class with honor"),
					array("6.5 to 7.49","First class"),
					array("5.0 to 6.49","Second class"),
					array("Below 5.0","Fail")
				);
				/*$list_count = 3;
				$list = array (
					array("●&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Percentage of marks scored = (CGPA Earned x 10)"),
					array("●&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;50% is passing criteria in each course."),
					array("●&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;MEDIUM OF INSTRUCTIONS: ENGLISH")
				);*/
                $list_count = 2;
                $list = array (
					array("●&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Percentage of marks scored = (CGPA Earned x 10)"),
					array("●&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;MEDIUM OF INSTRUCTIONS: ENGLISH")
				);
			}			
			$denote_count=6;
			$denote = array (
				array("(*)", "= Repeat Course"),
				array("#", "= Audit Course"),
				array("(E)", "= Elective Course"),
				array("(EM)", "= Elective Under Minor Course"),
				array("P", "= Pass"),
				array("NC", "= Non-Credit Course")
			);
			// $note="Note - The grades obtained in an audit course are not considered in the calculation of SGPA or CGPA however they are counted towards the total number of credits earned.";
			$note="Note: The grades obtained in an audit course are not considered in the calculation of SGPA or CGPA; however, they are counted towards the total number of credits earned.";
			
			$university_name='A N A N T&nbsp;&nbsp;&nbsp;N A T I O N A L&nbsp;&nbsp;&nbsp;U N I V E R S I T Y';
			$line2='Sanskardham Campus, Bopal - Ghuma - Sanand Road, Ahmedabad - 382115, Gujarat';
			$line3='Email: registrar@anu.edu.in | Website: www.anu.edu.in';
			
			$pdfBig->SetFont($trebucb, '', 10, '', false);
			$pdfBig->SetTextColor(0, 0, 0);
			$pdfBig->SetXY(12, 25);
            $pdfBig->MultiCell(0, 0, $grade_title, 0, 'L', 0, 0, '', '', true, 0, true);
			if($points=="4"){				
				$pdfBig->SetFont($trebucb, '', 10, '', false);
				$pdfBig->SetXY(55, 33);
				$pdfBig->setCellPaddings( $left = '', $top = '1', $right = '', $bottom = '');
				$pdfBig->MultiCell(22, 11, 'Numeric<br />Grade','LRTB', 'C', 0, 0, '', '', true, 0, true);
				$pdfBig->MultiCell(17, 11, 'Letter<br />Grade','LRTB',  'C', 0, 0, '', '', true, 0, true);
				$pdfBig->setCellPaddings( $left = '', $top = '1.5', $right = '', $bottom = '');
				$pdfBig->MultiCell(25, 11, 'Grade Point<br><span style="font-size:9;font-family:'.$trebucb.';">(4.0 pt scale)</span>','LRTB',  'C', 0, 0, '', '', true, 0, true);
				$pdfBig->setCellPaddings( $left = '', $top = '1', $right = '', $bottom = '');
				$pdfBig->MultiCell(40, 11, 'Descriptive<br />Performance','LRTB',  'L', 0, 0, '', '', true, 0, true);
				$pdfBig->setCellPaddings( $left = '', $top = '0', $right = '', $bottom = '');
				$pdfBig->SetFont($trebuc, '', 10, '', false);
				$y_start=44;	
				for ($grow = 0; $grow < $grades_count; $grow++) {					
					$pdfBig->SetXY(55, $y_start);
					$pdfBig->MultiCell(22, 5, $grades[$grow][0], 'LRTB', 'C', 0, 0);
					$pdfBig->MultiCell(17, 5, $grades[$grow][1], 'LRTB', 'C', 0, 0);
					$pdfBig->MultiCell(25, 5, $grades[$grow][2], 'LRTB', 'C', 0, 0);
					$pdfBig->MultiCell(40, 5, $grades[$grow][3], 'LRTB', 'L', 0, 0);
					$y_start=$y_start+5;
				}	
				
				$pdfBig->SetFont($trebucb, '', 10, '', false);
				$pdfBig->SetXY(55, $y_start+3);
				$pdfBig->MultiCell(50, 5, '','LRTB', 'R', 0, 0, '', '', true, 0, true);
				$pdfBig->MultiCell(54, 5, '','LRTB',  'L', 0, 0, '', '', true, 0, true);
				
				$pdfBig->SetXY(55, $y_start+3);
				$pdfBig->MultiCell(50, 5, 'CGPA',0, 'R', 0, 0, '', '', true, 0, true);
				$pdfBig->SetXY(106, $y_start+3);
				$pdfBig->MultiCell(54, 5, 'Award of Class',0,  'L', 0, 0, '', '', true, 0, true);
				
				$pdfBig->SetFont($trebuc, '', 10, '', false);
				$cy_start=$y_start+8;
				for ($crow = 0; $crow < $cgpa_count; $crow++) {
					$pdfBig->SetXY(55, $cy_start);
					$pdfBig->MultiCell(50, 5, '', 'LRTB', 'R', 0, 0);					
					$pdfBig->MultiCell(54, 5, '', 'LRTB', 'L', 0, 0);
					
					$pdfBig->SetXY(55, $cy_start);
					$pdfBig->MultiCell(49, 5, $cgpa_class[$crow][0], 0, 'R', 0, 0);					
					$pdfBig->SetXY(106, $cy_start);
					$pdfBig->MultiCell(54, 5, $cgpa_class[$crow][1], 0, 'L', 0, 0);
					$cy_start=$cy_start+5;
				}
							
			}
			elseif($points=="10"){
				$pdfBig->SetFont($trebucb, '', 10, '', false);
				$pdfBig->SetXY(55, 33);
				$pdfBig->setCellPaddings( $left = '', $top = '1', $right = '', $bottom = '');
				$pdfBig->MultiCell(29, 11, 'Numeric<br />Grade','LRTB', 'C', 0, 0, '', '', true, 0, true);
				$pdfBig->setCellPaddings( $left = '', $top = '2', $right = '', $bottom = '');
				$pdfBig->MultiCell(46, 11, 'Letter Grade','LRTB',  'C', 0, 0, '', '', true, 0, true);
				$pdfBig->setCellPaddings( $left = '', $top = '1.5', $right = '', $bottom = '');
				$pdfBig->MultiCell(29, 11, 'Grade Point<br><span style="font-size:9;font-family:'.$trebucb.';">(10 pt scale)</span>','LRTB',  'C', 0, 0, '', '', true, 0, true);
				$pdfBig->setCellPaddings( $left = '', $top = '0', $right = '', $bottom = '');				
				$pdfBig->SetFont($trebuc, '', 10, '', false);
				$y_start=44;	
				for ($grow = 0; $grow < $grades_count; $grow++) {
					$pdfBig->SetXY(55, $y_start);
					$pdfBig->MultiCell(29, 5, $grades[$grow][0], 'LRTB', 'C', 0, 0);
					$pdfBig->MultiCell(46, 5, $grades[$grow][1], 'LRTB', 'C', 0, 0);
					$pdfBig->MultiCell(29, 5, $grades[$grow][2], 'LRTB', 'C', 0, 0);
					$y_start=$y_start+5;
				}	
				
				$pdfBig->SetFont($trebucb, '', 10, '', false);				
				$pdfBig->SetXY(55, $y_start+3);
				$pdfBig->MultiCell(50, 5, 'CGPA','LRTB', 'R', 0, 0, '', '', true, 0, true);
				$pdfBig->MultiCell(54, 5, 'Award of Class','LRTB',  'L', 0, 0, '', '', true, 0, true);				
				$pdfBig->SetFont($trebuc, '', 10, '', false);
				$cy_start=$y_start+8;
				for ($crow = 0; $crow < $cgpa_count; $crow++) {
					$pdfBig->SetXY(55, $cy_start);
					$pdfBig->MultiCell(50, 5, '', 'LRTB', 'R', 0, 0);					
					$pdfBig->MultiCell(54, 5, '', 'LRTB', 'L', 0, 0);
					
					$pdfBig->SetXY(55, $cy_start);
					$pdfBig->MultiCell(49, 5, $cgpa_class[$crow][0], 0, 'R', 0, 0);					
					$pdfBig->SetXY(106, $cy_start);
					$pdfBig->MultiCell(54, 5, $cgpa_class[$crow][1], 0, 'L', 0, 0);
					$cy_start=$cy_start+5;
				}				
			}
			
			$pdfBig->SetFont($trebuc, '', 10, '', false);
			$ly_start=$cy_start+5;
			for ($lrow = 0; $lrow < $list_count; $lrow++) {
				$pdfBig->SetXY(13, $ly_start);	
				$pdfBig->MultiCell(0, 7, $list[$lrow][0], 0, 'L', 0, 0, '', '', true, 0, true);
				$ly_start=$ly_start+9;
			}
			$pdfBig->SetFont($trebuc, '', 10, '', false);
			$dy_start=$ly_start+3;
			for ($drow = 0; $drow < $denote_count; $drow++) {
				$pdfBig->SetXY(20.5, $dy_start);	
				$pdfBig->MultiCell(15, 5, $denote[$drow][0], 0, 'L', 0, 0);
				$pdfBig->MultiCell(0, 5, $denote[$drow][1], 0, 'L', 0, 0);
				$dy_start=$dy_start+5;
			}
			$pdfBig->SetFont($trebuc, '', 10, '', false);
			$n_start=$dy_start+5;
			$pdfBig->SetXY(13, $n_start);
			$pdfBig->MultiCell(200, 0, $note, 0, 'L', 0, 0, '', '', true, 0, true);
            // back page





            
        
        }
        $pdfBig->Output('anuA3Certificate.pdf', 'I');


        
        // for testing data


    }

    public function fetchArrayData($sheet1,$highestRow1) {
        $recordData = array();
        $subRecordData = array();
        $rowIndex = 0;
        $subIndex = 0;
        for ($row = 2; $row <= $highestRow1; $row++) {

            $previousColumn = $sheet1->getCellByColumnAndRow(6, $row - 1)->getValue();
            $rollNo = $sheet1->getCellByColumnAndRow(6, $row)->getValue();
            $sem = $sheet1->getCellByColumnAndRow(19, $row)->getValue();
            $yearMonth = $sheet1->getCellByColumnAndRow(16, $row)->getValue() .' - '.$sheet1->getCellByColumnAndRow(15, $row)->getValue();
            $semRef = $sheet1->getCellByColumnAndRow(230, $row)->getValue();
            $cgpa = $sheet1->getCellByColumnAndRow(28, $row)->getValue();

            if ($rollNo == $previousColumn) { 
                // after first line duplicate data
                
                // sub 1
                $incremNu = 5;
                for ($x11 = 33; $x11 <= 45; $x11++) {
                    $subRecordData[$subIndex][0] = $rollNo;
                    $subRecordData[$subIndex][1] = $sem;
                    $subRecordData[$subIndex][2] = $yearMonth;
                    $subRecordData[$subIndex][3] = $semRef;
                    $subRecordData[$subIndex][4] = $cgpa;
                    $subRecordData[$subIndex][$incremNu] = $sheet1->getCellByColumnAndRow($x11, $row)->getValue();
                    $incremNu++;
                }
                $subIndex++;

                // sub 2
                $incremNu = 5;
                for ($x12 = 47; $x12 <= 59; $x12++) {
                    $subRecordData[$subIndex][0] = $rollNo;
                    $subRecordData[$subIndex][1] = $sem;
                    $subRecordData[$subIndex][2] = $yearMonth;
                    $subRecordData[$subIndex][3] = $semRef;
                    $subRecordData[$subIndex][4] = $cgpa;
                    $subRecordData[$subIndex][$incremNu] = $sheet1->getCellByColumnAndRow($x12, $row)->getValue();
                    $incremNu++;
                }
                $subIndex++;

                // sub 3
                $incremNu = 5;
                for ($x13 = 60; $x13 <= 72; $x13++) {
                    $subRecordData[$subIndex][0] = $rollNo;
                    $subRecordData[$subIndex][1] = $sem;
                    $subRecordData[$subIndex][2] = $yearMonth;
                    $subRecordData[$subIndex][3] = $semRef;
                    $subRecordData[$subIndex][4] = $cgpa;
                    $subRecordData[$subIndex][$incremNu] = $sheet1->getCellByColumnAndRow($x13, $row)->getValue();
                    $incremNu++;
                }
                $subIndex++;

                // sub 4
                $incremNu = 5;
                for ($x14 = 73; $x14 <= 85; $x14++) {
                    $subRecordData[$subIndex][0] = $rollNo;
                    $subRecordData[$subIndex][1] = $sem;
                    $subRecordData[$subIndex][2] = $yearMonth;
                    $subRecordData[$subIndex][3] = $semRef;
                    $subRecordData[$subIndex][4] = $cgpa;
                    $subRecordData[$subIndex][$incremNu] = $sheet1->getCellByColumnAndRow($x14, $row)->getValue();
                    $incremNu++;
                }
                $subIndex++;

                // sub 5
                $incremNu = 5;
                for ($x15 = 86; $x15 <= 98; $x15++) {
                    $subRecordData[$subIndex][0] = $rollNo;
                    $subRecordData[$subIndex][1] = $sem;
                    $subRecordData[$subIndex][2] = $yearMonth;
                    $subRecordData[$subIndex][3] = $semRef;
                    $subRecordData[$subIndex][4] = $cgpa;
                    $subRecordData[$subIndex][$incremNu] = $sheet1->getCellByColumnAndRow($x15, $row)->getValue();
                    $incremNu++;
                }
                $subIndex++;
                
                // sub 6
                $incremNu = 5;
                for ($x16 = 99; $x16 <= 111; $x16++) {
                    $subRecordData[$subIndex][0] = $rollNo;
                    $subRecordData[$subIndex][1] = $sem;
                    $subRecordData[$subIndex][2] = $yearMonth;
                    $subRecordData[$subIndex][3] = $semRef;
                    $subRecordData[$subIndex][4] = $cgpa;
                    $subRecordData[$subIndex][$incremNu] = $sheet1->getCellByColumnAndRow($x16, $row)->getValue();
                    $incremNu++;
                }
                $subIndex++;


                // sub 7
                $incremNu = 5;
                for ($x17 = 112; $x17 <= 124; $x17++) {
                    $subRecordData[$subIndex][0] = $rollNo;
                    $subRecordData[$subIndex][1] = $sem;
                    $subRecordData[$subIndex][2] = $yearMonth;
                    $subRecordData[$subIndex][3] = $semRef;
                    $subRecordData[$subIndex][4] = $cgpa;
                    $subRecordData[$subIndex][$incremNu] = $sheet1->getCellByColumnAndRow($x17, $row)->getValue();
                    $incremNu++;
                }
                $subIndex++;

                // sub 8
                $incremNu = 5;
                for ($x18 = 125; $x18 <= 137; $x18++) {
                    $subRecordData[$subIndex][0] = $rollNo;
                    $subRecordData[$subIndex][1] = $sem;
                    $subRecordData[$subIndex][2] = $yearMonth;
                    $subRecordData[$subIndex][3] = $semRef;
                    $subRecordData[$subIndex][4] = $cgpa;
                    $subRecordData[$subIndex][$incremNu] = $sheet1->getCellByColumnAndRow($x18, $row)->getValue();
                    $incremNu++;
                }
                $subIndex++;

                // sub 9
                $incremNu = 5;
                for ($x19 = 138; $x19 <= 150; $x19++) {
                    $subRecordData[$subIndex][0] = $rollNo;
                    $subRecordData[$subIndex][1] = $sem;
                    $subRecordData[$subIndex][2] = $yearMonth;
                    $subRecordData[$subIndex][3] = $semRef;
                    $subRecordData[$subIndex][4] = $cgpa;
                    $subRecordData[$subIndex][$incremNu] = $sheet1->getCellByColumnAndRow($x19, $row)->getValue();
                    $incremNu++;
                }
                $subIndex++;

                // sub 10
                $incremNu = 5;
                for ($x20 = 151; $x20 <= 163; $x20++) {
                    $subRecordData[$subIndex][0] = $rollNo;
                    $subRecordData[$subIndex][1] = $sem;
                    $subRecordData[$subIndex][2] = $yearMonth;
                    $subRecordData[$subIndex][3] = $semRef;
                    $subRecordData[$subIndex][4] = $cgpa;
                    $subRecordData[$subIndex][$incremNu] = $sheet1->getCellByColumnAndRow($x20, $row)->getValue();
                    $incremNu++;
                }
                $subIndex++;

                // sub 11
                $incremNu = 5;
                for ($x21 = 164; $x21 <= 176; $x21++) {
                    $subRecordData[$subIndex][0] = $rollNo;
                    $subRecordData[$subIndex][1] = $sem;
                    $subRecordData[$subIndex][2] = $yearMonth;
                    $subRecordData[$subIndex][3] = $semRef;
                    $subRecordData[$subIndex][4] = $cgpa;
                    $subRecordData[$subIndex][$incremNu] = $sheet1->getCellByColumnAndRow($x21, $row)->getValue();
                    $incremNu++;
                }
                $subIndex++;

                // sub 12
                $incremNu = 5;
                for ($x22 = 177; $x22 <= 189; $x22++) {
                    $subRecordData[$subIndex][0] = $rollNo;
                    $subRecordData[$subIndex][1] = $sem;
                    $subRecordData[$subIndex][2] = $yearMonth;
                    $subRecordData[$subIndex][3] = $semRef;
                    $subRecordData[$subIndex][4] = $cgpa;
                    $subRecordData[$subIndex][$incremNu] = $sheet1->getCellByColumnAndRow($x22, $row)->getValue();
                    $incremNu++;
                }
                $subIndex++;

                // sub 13
                $incremNu = 5;
                for ($x23 = 190; $x23 <= 202; $x23++) {
                    $subRecordData[$subIndex][0] = $rollNo;
                    $subRecordData[$subIndex][1] = $sem;
                    $subRecordData[$subIndex][2] = $yearMonth;
                    $subRecordData[$subIndex][3] = $semRef;
                    $subRecordData[$subIndex][4] = $cgpa;
                    $subRecordData[$subIndex][$incremNu] = $sheet1->getCellByColumnAndRow($x23, $row)->getValue();
                    $incremNu++;
                }
                $subIndex++;


                // sub 14
                $incremNu = 5;
                for ($x24 = 203; $x24 <= 215; $x24++) {
                    $subRecordData[$subIndex][0] = $rollNo;
                    $subRecordData[$subIndex][1] = $sem;
                    $subRecordData[$subIndex][2] = $yearMonth;
                    $subRecordData[$subIndex][3] = $semRef;
                    $subRecordData[$subIndex][4] = $cgpa;
                    $subRecordData[$subIndex][$incremNu] = $sheet1->getCellByColumnAndRow($x24, $row)->getValue();
                    $incremNu++;
                }
                $subIndex++;


                // sub 15
                $incremNu = 5;
                for ($x25 = 216; $x25 <= 228; $x25++) {
                    $subRecordData[$subIndex][0] = $rollNo;
                    $subRecordData[$subIndex][1] = $sem;
                    $subRecordData[$subIndex][2] = $yearMonth;
                    $subRecordData[$subIndex][3] = $semRef;
                    $subRecordData[$subIndex][4] = $cgpa;
                    $subRecordData[$subIndex][$incremNu] = $sheet1->getCellByColumnAndRow($x25, $row)->getValue();
                    $incremNu++;
                }
                $subIndex++;

                continue; 
            } else {

                for ($x1 = 1; $x1 <= 235; $x1++) {
                    $recordData[$rowIndex][$x1] = $sheet1->getCellByColumnAndRow($x1, $row)->getValue();
                }
                $rowIndex++;

                $rollNo = $sheet1->getCellByColumnAndRow(6, $row)->getValue();
                $sem = $sheet1->getCellByColumnAndRow(19, $row)->getValue();
                $yearMonth = $sheet1->getCellByColumnAndRow(16, $row)->getValue() .' - '.$sheet1->getCellByColumnAndRow(15, $row)->getValue();
                $semRef = $sheet1->getCellByColumnAndRow(230, $row)->getValue();
                $cgpa = $sheet1->getCellByColumnAndRow(28, $row)->getValue();
                // first line data
                // sub 1
                $incremNu = 5;
                for ($x11 = 33; $x11 <= 45; $x11++) {
                    $subRecordData[$subIndex][0] = $rollNo;
                    $subRecordData[$subIndex][1] = $sem;
                    $subRecordData[$subIndex][2] = $yearMonth;
                    $subRecordData[$subIndex][3] = $semRef;
                    $subRecordData[$subIndex][4] = $cgpa;
                    $subRecordData[$subIndex][$incremNu] = $sheet1->getCellByColumnAndRow($x11, $row)->getValue();
                    $incremNu++;
                }
                $subIndex++;

                // sub 2
                $incremNu = 5;
                for ($x12 = 47; $x12 <= 59; $x12++) {
                    $subRecordData[$subIndex][0] = $rollNo;
                    $subRecordData[$subIndex][1] = $sem;
                    $subRecordData[$subIndex][2] = $yearMonth;
                    $subRecordData[$subIndex][3] = $semRef;
                    $subRecordData[$subIndex][4] = $cgpa;
                    $subRecordData[$subIndex][$incremNu] = $sheet1->getCellByColumnAndRow($x12, $row)->getValue();
                    $incremNu++;
                }
                $subIndex++;

                // sub 3
                $incremNu = 5;
                for ($x13 = 60; $x13 <= 72; $x13++) {
                    $subRecordData[$subIndex][0] = $rollNo;
                    $subRecordData[$subIndex][1] = $sem;
                    $subRecordData[$subIndex][2] = $yearMonth;
                    $subRecordData[$subIndex][3] = $semRef;
                    $subRecordData[$subIndex][4] = $cgpa;
                    $subRecordData[$subIndex][$incremNu] = $sheet1->getCellByColumnAndRow($x13, $row)->getValue();
                    $incremNu++;
                }
                $subIndex++;

                // sub 4
                $incremNu = 5;
                for ($x14 = 73; $x14 <= 85; $x14++) {
                    $subRecordData[$subIndex][0] = $rollNo;
                    $subRecordData[$subIndex][1] = $sem;
                    $subRecordData[$subIndex][2] = $yearMonth;
                    $subRecordData[$subIndex][3] = $semRef;
                    $subRecordData[$subIndex][4] = $cgpa;
                    $subRecordData[$subIndex][$incremNu] = $sheet1->getCellByColumnAndRow($x14, $row)->getValue();
                    $incremNu++;
                }
                $subIndex++;

                // sub 5
                $incremNu = 5;
                for ($x15 = 86; $x15 <= 98; $x15++) {
                    $subRecordData[$subIndex][0] = $rollNo;
                    $subRecordData[$subIndex][1] = $sem;
                    $subRecordData[$subIndex][2] = $yearMonth;
                    $subRecordData[$subIndex][3] = $semRef;
                    $subRecordData[$subIndex][4] = $cgpa;
                    $subRecordData[$subIndex][$incremNu] = $sheet1->getCellByColumnAndRow($x15, $row)->getValue();
                    $incremNu++;
                }
                $subIndex++;
                
                // sub 6
                $incremNu = 5;
                for ($x16 = 99; $x16 <= 111; $x16++) {
                    $subRecordData[$subIndex][0] = $rollNo;
                    $subRecordData[$subIndex][1] = $sem;
                    $subRecordData[$subIndex][2] = $yearMonth;
                    $subRecordData[$subIndex][3] = $semRef;
                    $subRecordData[$subIndex][4] = $cgpa;
                    $subRecordData[$subIndex][$incremNu] = $sheet1->getCellByColumnAndRow($x16, $row)->getValue();
                    $incremNu++;
                }
                $subIndex++;


                // sub 7
                $incremNu = 5;
                for ($x17 = 112; $x17 <= 124; $x17++) {
                    $subRecordData[$subIndex][0] = $rollNo;
                    $subRecordData[$subIndex][1] = $sem;
                    $subRecordData[$subIndex][2] = $yearMonth;
                    $subRecordData[$subIndex][3] = $semRef;
                    $subRecordData[$subIndex][4] = $cgpa;
                    $subRecordData[$subIndex][$incremNu] = $sheet1->getCellByColumnAndRow($x17, $row)->getValue();
                    $incremNu++;
                }
                $subIndex++;

                // sub 8
                $incremNu = 5;
                for ($x18 = 125; $x18 <= 137; $x18++) {
                    $subRecordData[$subIndex][0] = $rollNo;
                    $subRecordData[$subIndex][1] = $sem;
                    $subRecordData[$subIndex][2] = $yearMonth;
                    $subRecordData[$subIndex][3] = $semRef;
                    $subRecordData[$subIndex][4] = $cgpa;
                    $subRecordData[$subIndex][$incremNu] = $sheet1->getCellByColumnAndRow($x18, $row)->getValue();
                    $incremNu++;
                }
                $subIndex++;

                // sub 9
                $incremNu = 5;
                for ($x19 = 138; $x19 <= 150; $x19++) {
                    $subRecordData[$subIndex][0] = $rollNo;
                    $subRecordData[$subIndex][1] = $sem;
                    $subRecordData[$subIndex][2] = $yearMonth;
                    $subRecordData[$subIndex][3] = $semRef;
                    $subRecordData[$subIndex][4] = $cgpa;
                    $subRecordData[$subIndex][$incremNu] = $sheet1->getCellByColumnAndRow($x19, $row)->getValue();
                    $incremNu++;
                }
                $subIndex++;

                // sub 10
                $incremNu = 5;
                for ($x20 = 151; $x20 <= 163; $x20++) {
                    $subRecordData[$subIndex][0] = $rollNo;
                    $subRecordData[$subIndex][1] = $sem;
                    $subRecordData[$subIndex][2] = $yearMonth;
                    $subRecordData[$subIndex][3] = $semRef;
                    $subRecordData[$subIndex][4] = $cgpa;
                    $subRecordData[$subIndex][$incremNu] = $sheet1->getCellByColumnAndRow($x20, $row)->getValue();
                    $incremNu++;
                }
                $subIndex++;

                // sub 11
                $incremNu = 5;
                for ($x21 = 164; $x21 <= 176; $x21++) {
                    $subRecordData[$subIndex][0] = $rollNo;
                    $subRecordData[$subIndex][1] = $sem;
                    $subRecordData[$subIndex][2] = $yearMonth;
                    $subRecordData[$subIndex][3] = $semRef;
                    $subRecordData[$subIndex][4] = $cgpa;
                    $subRecordData[$subIndex][$incremNu] = $sheet1->getCellByColumnAndRow($x21, $row)->getValue();
                    $incremNu++;
                }
                $subIndex++;

                // sub 12
                $incremNu = 5;
                for ($x22 = 177; $x22 <= 189; $x22++) {
                    $subRecordData[$subIndex][0] = $rollNo;
                    $subRecordData[$subIndex][1] = $sem;
                    $subRecordData[$subIndex][2] = $yearMonth;
                    $subRecordData[$subIndex][3] = $semRef;
                    $subRecordData[$subIndex][4] = $cgpa;
                    $subRecordData[$subIndex][$incremNu] = $sheet1->getCellByColumnAndRow($x22, $row)->getValue();
                    $incremNu++;
                }
                $subIndex++;

                // sub 13
                $incremNu = 5;
                for ($x23 = 190; $x23 <= 202; $x23++) {
                    $subRecordData[$subIndex][0] = $rollNo;
                    $subRecordData[$subIndex][1] = $sem;
                    $subRecordData[$subIndex][2] = $yearMonth;
                    $subRecordData[$subIndex][3] = $semRef;
                    $subRecordData[$subIndex][4] = $cgpa;
                    $subRecordData[$subIndex][$incremNu] = $sheet1->getCellByColumnAndRow($x23, $row)->getValue();
                    $incremNu++;
                }
                $subIndex++;


                // sub 14
                $incremNu = 5;
                for ($x24 = 203; $x24 <= 215; $x24++) {
                    $subRecordData[$subIndex][0] = $rollNo;
                    $subRecordData[$subIndex][1] = $sem;
                    $subRecordData[$subIndex][2] = $yearMonth;
                    $subRecordData[$subIndex][3] = $semRef;
                    $subRecordData[$subIndex][4] = $cgpa;
                    $subRecordData[$subIndex][$incremNu] = $sheet1->getCellByColumnAndRow($x24, $row)->getValue();
                    $incremNu++;
                }
                $subIndex++;


                // sub 15
                $incremNu = 5;
                for ($x25 = 216; $x25 <= 228; $x25++) {
                    $subRecordData[$subIndex][0] = $rollNo;
                    $subRecordData[$subIndex][1] = $sem;
                    $subRecordData[$subIndex][2] = $yearMonth;
                    $subRecordData[$subIndex][3] = $semRef;
                    $subRecordData[$subIndex][4] = $cgpa;
                    $subRecordData[$subIndex][$incremNu] = $sheet1->getCellByColumnAndRow($x25, $row)->getValue();
                    $incremNu++;
                }
                $subIndex++;

                
            }

        }

        return array($recordData, $subRecordData);
        

    }



   
}
