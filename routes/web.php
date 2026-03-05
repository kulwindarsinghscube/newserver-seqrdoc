<?php


use App\models\TemplateMaster;
use App\models\SiteDocuments;
use App\models\User;
use App\models\StudentTable;
use App\models\ScannedHistory;
use Illuminate\Http\Request;
use App\Http\Controllers\PaymentControllernew;
use App\Http\Controllers\Admin\WordToPdfController;
use App\Http\Controllers\ConvoStudentImportController;


// use DB;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
// $domain = \Request::getHost();
// $subdomain = explode('.', $domain);
// dd($subdomain);


// phpinfo();exit();

//use Illuminate\Support\Facades\Artisan;


Route::get('testmandar',function(){


$taskName = "RecycleDefaultPool";
$command = 'schtasks /run /tn "' . $taskName . '"';
exec($command . ' 2>&1', $output, $return_var);
echo "<pre>" . implode("\n", $output) . "</pre>";
return "Yes";
	
});

//Clear cache:
Route::get('/clear-cache', function() {
    $exitCode = Artisan::call('cache:clear');
    // return what you want
     return '<h1>Clear Cache cleared</h1>';
});

//Clear Config cache:
Route::get('/config-cache', function() {
    $exitCode = Artisan::call('config:cache');
    return '<h1>Clear Config cleared</h1>';
});

Route::get('tes-cron',function(){
	\Artisan::call('api:day');
});

Route::get('phpinfo',function(){
	phpinfo();
	
});
Route::get('hash',function(){
	$hash = \Hash::make('seqr@ghrcemp');
	dd($hash);
});

Route::get('verify_blockchain_document',function(Request $request){
	if ($request->has('run') && $request->query('run') === 'yes') {
		\Artisan::call('blockchain:verify-documents');
		return '<h1>Run Successfully</h1>';
	}else{
		return '<h1>Invalid or Missing Query Parameter</h1>';
	}
	
});


Route::get('/raisoni/{url}', 'WebsiteController@index');

Route::get('upload_to_aws',function(){
	\Artisan::call('aws:upload-files');
});


Route::group(['middleware'=>'domain.check'], function(){
Route::get('/tpsdi-process','admin\TpsdiCronController@index');
Route::get('/send-to-print','admin\TpsdiCronController@send_ToPrint');
	//global student web login route
	// Route::get('/global-student','auth\LoginController@showWebStudentLogin')->name('gswebapp.index')->middleware('WebStudent');
	// Route::post('/gslogin','auth\LoginController@webStudentLogin')->name('gswebapp.login');
	// Route::get('/gslogout','auth\LoginController@webStudentLogout')->name('gswebapp.logout');
	//institute student web login route
	Route::get('/institute-student','auth\LoginController@showWebInstStudentLogin')->name('inswebapp.index')->middleware('WebInstStudent');
	Route::post('/inslogin','auth\LoginController@webInstStudentLogin')->name('inswebapp.login');
	Route::get('/inslogout','auth\LoginController@webInstStudentLogout')->name('inswebapp.logout');


	// define web Institute Student Route	
	Route::group(['middleware'=>'webinststudent.check','prefix'=>'student-wallet','namespace'=>'InstituteStudentWallet'],function(){

		Route::get('/insdashboard','DashboardController@dashboard')->name('inswebapp.dashboard');
		Route::post('/insdashboard','DashboardController@store')->name('insdashboard.store');

		Route::get('/insprofile','ProfileController@index')->name('inswebapp.profile.showprofile');
		// Route::post('/insprofile','ProfileController@index')->name('inswebapp.profile.updatephoto');
	    Route::post('/insprofile-update','ProfileController@changePassword')->name('inswebapp.profile.changepassword');
	    Route::post('/insupdateprofile','ProfileController@updateprofile')->name('inswebapp.updateprofile');
	  	Route::get('/insdocuments','DocumentsController@index')->name('insdocuments');
	  	Route::get('/get-institute-studnt-certificates', 'DocumentsController@getInstituteCertificates')->name('get.instituteStud.certificates');
		Route::get('/insdoc/{id}', 'DocumentsController@viewDocument')->name('insview.document');
	  	Route::get('/importJsonData','DocumentsController@importJsonData');

	});
	// define web Global Student Route	
   	// Route::group(['middleware'=>'webstudent.check','prefix'=>'student-wallet','namespace'=>'StudentWallet'],function(){

	// 	Route::get('/gsdashboard','DashboardController@dashboard')->name('gswebapp.dashboard');
	// 	Route::get('/gsprofile','ProfileController@index')->name('gswebapp.profile.showprofile');
	//     Route::post('/gsprofile-update','ProfileController@changePassword')->name('gswebapp.profile.changepassword');
	//   	Route::get('/gsdocuments','DocumentsController@index')->name('gsdocuments');
	//   	Route::get('/get-institute-certificates', 'DocumentsController@getInstituteCertificates')->name('get.institute.certificates');
	//   	Route::get('/gsdoc/{id}', 'DocumentsController@viewDocument')->name('gsview.document');

	// });

	Route::get('remove-qr',function(){
		$data  = \App\models\StudentTable::get()->toArray();	
		$domain =$_SERVER['HTTP_HOST'];
		$subdomain = explode('.', $domain);
		
		$get_template_folder_image = glob(public_path().'/'.$subdomain[0].'/backend/canvas/images/qr/*.{png}', GLOB_BRACE);
		
		$folder_array = [];
		foreach ($get_template_folder_image as $key => $value) {
			
			$basename = basename($value);
			array_push($folder_array, $basename);

		}
			
		$table_array = [];
		foreach ($data as $k => $v) {

			array_push($table_array, $v['key'].'.png');
		}
		
		
		// echo "<pre>";
		// print_r($folder_array);
		// exit();
		
		foreach ($folder_array as $key => $value) {
			
			if (in_array($value, $table_array)) {

				echo "<br>";
				echo "in array";
				echo "<br>";
				print_r($value);
				echo "<br>";
			}else{
				// dd(public_path().'/'.$subdomain[0].'/backend/pdf_file/'.$value);
				if(file_exists(public_path().'/'.$subdomain[0].'/backend/canvas/images/qr/'.$value)){

					unlink(public_path().'/'.$subdomain[0].'/backend/canvas/images/qr/'.$value);
				}
				// echo "<br>";
				// echo "not in array";
				// echo "<br>";
				// print_r($value);

			}

		}
		dd('done');
	});
	Route::get('remove-files',function(){
		$data  = \App\models\StudentTable::where('status',1)->get()->toArray();	
		$domain =$_SERVER['HTTP_HOST'];
		$subdomain = explode('.', $domain);
		
		$get_template_folder_image = glob(public_path().'/'.$subdomain[0].'/backend/pdf_file/*.{pdf}', GLOB_BRACE);
		
		$folder_array = [];
		foreach ($get_template_folder_image as $key => $value) {
			
			$basename = basename($value);
			array_push($folder_array, $basename);

		}
			
		$table_array = [];
		foreach ($data as $k => $v) {

			array_push($table_array, $v['certificate_filename']);
		}
		
		foreach ($folder_array as $key => $value) {
			
			if (in_array($value, $table_array)) {

				echo "<br>";
				echo "in array";
				echo "<br>";
				print_r($value);
				echo "<br>";
			}else{
				// dd(public_path().'/'.$subdomain[0].'/backend/pdf_file/'.$value);
				unlink(public_path().'/'.$subdomain[0].'/backend/pdf_file/'.$value);
				// echo "<br>";
				// echo "not in array";
				// echo "<br>";
				// print_r($value);

			}

		}
	});


	Route::get('/admin/renewInstance','admin\TestController@renewInstance');
	Route::get('/anuCopyPdfs','TestController@anuCopyPdfs');
	Route::get('/mail_check','TestController@mailcheck');
	Route::get('/curl_test','TestController@curl_test');
	//Route::get('/apitraker','TestController@apitracker');
		
	Route::get('/sftpTesting','TestController@sftpTesting');
	Route::get('/testAWS','TestController@testAWS');

	Route::get('/checkfiles','TestController@checkfiles');
	Route::get('/column-insert','TestController@columInsert');


	Route::get('/blockchain_rnd','TestController@blockchain_rnd');
	Route::get('/view-pdf/{certificate?}','admin\PDFController@viewPDF');



	Route::get('/excel-read','TestController@excelRead');

	Route::get('/today-rnd','TestController@todayRnd')->middleware('domain.check');

	Route::get('/image-checking','TestController@imageCheck');

	// Blockchain RND
	// 25-11-2025
	Route::get('/blockchain/pinataToLighthouseV1','BlockchainRNDController@pinataToLighthouseV1');

	Route::get('/blockchain/pinataToLighthouse','BlockchainRNDController@pinataToLighthouse');
	Route::get('/blockchain/fetchToken','BlockchainRNDController@fetchToken');
	Route::get('/blockchain/fetchTokenV1','BlockchainRNDController@fetchTokenOptimize');
	Route::get('/blockchain/fetchTokenPython','BlockchainRNDController@fetchTokenPython');

	Route::get('/blockchain/anuminting','BlockchainRNDController@anumintData');

	Route::get('/sftp-connect','SFTPController@index');
	Route::get('/sftp/listFiles','SFTPController@listFiles');
	Route::get('/sftp/downloadFile','SFTPController@downloadFile');
	Route::get('/sftp/uploadFile','SFTPController@uploadFile');
	


	// BMCC File Upload 
	Route::get('/admin/upload-file-azure','admin\BMCCFileUploadController@index');
	Route::post('/admin/file-azure','admin\BMCCFileUploadController@azureFiles')->name('bmcc.upload-file-azure');
	Route::get('/admin/file-azure-list','admin\BMCCFileUploadController@azureFileList');
	
	

	Route::get('admin/mitwpu/pdf-view/{prnNo?}','admin\MITWPUPDFController@pdfView')->name('mitwpu.pdf-view');
	Route::get('admin/mitwpu/pdf-view-phd/{prnNo?}','admin\MITWPUPDFController@pdfViewPhd')->name('mitwpu.pdf-view');

	
	Route::get('/', function () {
	    return view('admin.layout.layout');
	});

	Route::get('/access-denied',function(){
	    return view('permission_denied');
	});

	Route::get('/result-search',function(){
	    return view('Search.index');
	});
	Route::get('/bverify', 'VerifyController@bverify')->name('bverify')->middleware('domain.check');
	Route::get('/bverify-new', 'VerifyController@bverify_new')->name('bverify_new')->middleware('domain.check');
	
	// Backup old url 07-03-2025 by rohit 

	Route::get('/bverifyV1/{token}', 'api\blockchain\PdfController@showDetails')->middleware('domain.check');
	
	Route::get('/bverify-preview/{token}', 'api\blockchain\PdfController@PreviewDetails')->middleware('domain.check');

	Route::get('/testpdf','admin\TestCertificateController@testPdf');

	Route::get('/bverifyV1', 'VerifyV1Controller@bverify')->name('bverifyV1');
	

	// Rohit Added
	// Route::get('/bverify/{token}', 'api\blockchain\PdfNewController@showDetails')->middleware('domain.check');
	
	// 10-10-2025
	Route::get('/bverify/{token}', 'api\blockchain\PdfNewV1Controller@index')->middleware('domain.check');
	Route::get('/bverify-new/show_details/{token}', 'api\blockchain\PdfNewV1Controller@showDetails')->middleware('domain.check');
	

	Route::get('/bverify-new/show_details-v1/{token}', 'api\blockchain\PdfNewV2Controller@showDetails')->middleware('domain.check');

	Route::get('admin/kewitest','admin\TestController@kewiTest')->name('kewi.test');


	Route::get('/testpdf','admin\PDFController@testPdf');
	
	Route::post('/result-search/result','Search\SearchResultController@index')->name('search.result');

	//super admin login route
	Route::get('/superadmin/login','superadmin\SuperAdminLoginController@index')->name('superadmin.index')->middleware('superadmin.basic');
	Route::post('/superadmin/login','superadmin\SuperAdminLoginController@SuperLogin')->name('superadmin.login');
	//End super admin login route
	// superadmin count route added 29-03-2024
	Route::get('/superadmin/getMigrateDataDetails', 'superadmin\SitePermissionController@getMigrateDataDetails')->name('superadmin.getMigrateDataDetails');
	// superadmin count route added 29-03-2024
	
	Route::get('document/{serial_no}', 'DocumentPdfController@showPdf');
	

	// Added Route By Rohit - 11 -06-2025
	//route for sortby or datewise dynamic image
	Route::get('dynamic-image-management/excel2pdfDisplayImage/{sortby}/{value}','admin\DynamicImageManagementController@excel2pdfDisplayImage')->name('dynamic-image-management.excel2pdf.Display');

	Route::get('dynamic-image-management/excel2pdfDisplayImage/{sortby}/{value}/{searchkey}','admin\DynamicImageManagementController@excel2pdfDisplayImage')->name('dynamic-image-management.excel2pdf.Display');
	Route::post('dynamic-image-management/excel2pdfStore','admin\DynamicImageManagementController@excel2pdfStore')->name('dynamic-image-management.excel2pdfStore');
	//route for edit dynamic image name
	Route::post('dynamic-image-management/excel2pdf/dynamicImageEdit','admin\DynamicImageManagementController@excel2pdfdynamicImageEdit')->name('dynamic-image-management.excel2pdfupdatingImage');
	//route for delete dynamic image name
	
	Route::post('dynamic-image-management/excel2pdf/delete','admin\DynamicImageManagementController@excel2pdfdelete')->name('dynamic-image-management.excel2pdfdeleting');


	// define super admin route
	Route::group(['middleware'=>'superadmin.check','namespace'=>'superadmin'], function(){

	  Route::get('/superadmin/logout','SuperAdminLoginController@logout')->name('superadmin.logout');
	  Route::get('/superadmin/dashboard','SuperAdminLoginController@dashboard')->name('superadmin.dashboard'); 
	  Route::get('/superadmin/masterdata','MasterDataController@index')->name('superadmin.masterdata');
	  Route::get('/superadmin/infography','InfoGraphyController@index')->name('superadmin.infography');
	  Route::get('/superadmin/monthlyconsumption','InfoGraphyController@monthlyConsumption')->name('superadmin.monthlyconsumption');
	  Route::get('/superadmin/getmothlyconsumption', 'InfoGraphyController@getMonthlyConsumptionData')->name('superadmin.getmothlyconsumption');
	  Route::post('/superadmin/mothlyconsumptionexport', 'InfoGraphyController@monthlyConsumptionDataExport')->name('superadmin.mothlyconsumptionexport');
	  Route::get('/superadmin/mothlyconsumptiondetail', 'InfoGraphyController@getDetailData')->name('superadmin.mothlyconsumptiondetail');
	  Route::get('/superadmin/consumptioncomparison','InfoGraphyController@consumptionComparison')->name('superadmin.consumptioncomparison');
	  Route::get('/superadmin/getconsumptioncomparison', 'InfoGraphyController@getComparisonData')->name('superadmin.getconsumptioncomparison');
	  Route::post('/superadmin/getfilesdetails', 'SitePermissionController@getFilesDetails')->name('superadmin.getfilesdetails');



	  Route::get('/superadmin/masterdata/masterData','MasterDataController@excelExport')->name('masterData.excelExport');

	      Route::resource('/website-permission', 'SitePermissionController',['except'=>['update','destroy','show']]);
	      Route::post('/website-permission/update/{id}', 'SitePermissionController@update')->name('website-permission.update');
	      Route::get('/website-permission/delete/{id}', 'SitePermissionController@destroy')->name('website-permission.destroy');

	      /*Start Mandar */
	      Route::resource('/copy-template', 'CopyTemplateController',['except'=>['update','destroy','show']]);
	    Route::post('/copy-template/update/{id}', 'CopyTemplateController@update')->name('copy-template.update');
	    Route::get('/copy-template/delete/{id}', 'CopyTemplateController@destroy')->name('copy-template.destroy');
	    Route::get('/copy-template/viewtemplates/{id}', 'CopyTemplateController@viewtemplates')->name('copy-template.viewtemplates');
		Route::get('/copy-template/viewtemplateslist', 'CopyTemplateController@viewtemplateslist')->name('copy-template.viewtemplateslist');
		Route::post('/copy-template/copy-template', 'CopyTemplateController@copyTemplate')->name('copy-template.copy-template');
		Route::get('/copy-template/viewtemplatespdf2pdf/{id}', 'CopyTemplateController@viewtemplatespdf2pdf')->name('copy-template.viewtemplatespdf2pdf');
		Route::get('/copy-template/viewtemplateslistpdf2pdf', 'CopyTemplateController@viewtemplateslistpdf2pdf')->name('copy-template.viewtemplateslistpdf2pdf');
		Route::post('/copy-template/copy-templatepdf2pdf', 'CopyTemplateController@copyTemplatePdf2pdf')->name('copy-template.copy-templatepdf2pdf');


		// FontMaster Route
	    Route::resource('/superfontmaster','FontMasterController',['except'=>['update','show','create','destroy','assignfont']]);
	    Route::post('/superfontmaster/{id}','FontMasterController@update')->name('superfontmaster.updates');
	    Route::get('/superfontmaster/{id}','FontMasterController@destroy')->name('superfontmaster.destroy');
	    Route::post('/superfontassign','FontMasterController@assignfont')->name('superfontmaster.assignfont');
	    
		/* End Mandar */

	});
	//End define super admin route


	//permission not required
	Route::get('/routes','admin\AdminManagementController@RouteList')->name('show.routes');
	//permission not required end

	Route::get('admin/test/idcard-image','admin\IdCardStatusController@TestR')->name('image.test');
	Route::get('admin/import/answerbooklet','admin\ImportCustomMandar@importData')->name('import.answerbooklet');

	// admin login route
	Route::get('/admin/login','admin\auth\AdminLoginController@showLoginForm')->middleware('guest');
	Route::post('/admin/login','admin\auth\AdminLoginController@login')->name('admin.login');
	Route::get('/admin/logout','admin\auth\AdminLoginController@logout')->name('admin.logout');
	// end admin login route

	// call autologout route
	Route::post('/autologout', 'Admin\auth\AdminLoginController@autoLogout')->name('admin.autologout');
	// end autologout route

	// call autologout route
	Route::post('webapp/autologout', 'auth\LoginController@autoLogout')->name('webapp.autologout');
	// end autologout route

	// blockchain authorization route
	Route::get('/admin/BlockchainAuthorization/live','admin\BlockchainAuthorizationController@live')->name('BlockchainAuthorization.live');
	Route::get('/admin/BlockchainAuthorization/test','admin\BlockchainAuthorizationController@test')->name('BlockchainAuthorization.test');
	// blockchain authorization route
	
	//web login route
	Route::get('/','auth\LoginController@showWebUserLogin')->name('webapp.index')->middleware('WebUser');
	Route::post('/login','auth\LoginController@webuserLogin')->name('webapp.login');
	Route::get('/logout','auth\LoginController@webLogout')->name('webapp.logout');
	Route::post('webapp/register','auth\RegisterController@userRegister')->name('webapp.register');
	 Route::get('/verifywebuser/{token}','auth\VerificationController@showVerify')->name('webapp.verify');
	Route::get('/webapp/verify/{token}','auth\VerificationController@verifyUser');
	Route::post('resendverificationlink','auth\VerificationController@resendVerificationLink')->name('webapp.resendverificationlink');
	Route::post('checkverificationstatus','auth\VerificationController@checkVerificationStatus')->name('webapp.checkverificationstatus');

	
	// Rohit 19/07/2023
	// Password reset link request routes...
	Route::post('authVerificationLink','auth\VerificationController@authVerificationLink')->name('authVerificationLink');
	Route::get('/auth/password_reset/{token}','auth\VerificationController@authUserPasswordreset');
	Route::post('passwordResetUpdate','auth\VerificationController@passwordResetUpdate')->name('passwordResetUpdate');
	// rohit 19/07/2023

	Route::get('/webapp/verifywebuser/{token}','auth\VerificationController@showVerify')->name('webapp.verify');
	//end web login route


	/*Route::get('webapp/login', 'Auth\LoginController@showUserLoginForm');
	Route::post('webapp/login', [ 'as' => 'webapp.login','uses' => 'Auth\LoginController@userLogin']);*/
	//define route webuser

	    // Scan History routes
		Route::get('webapp/scan-history','webapp\ScanHistoryController@index')->name('scan-history.index');
		Route::get('webapp/scan-history/show','webapp\ScanHistoryController@show')->name('scanningHistory.show');
		
  	// define web User Route	
   	Route::group(['middleware'=>'web.check','prefix'=>'webapp','namespace'=>'webapp'],function(){

		Route::get('/dashboard','DashboardController@dashboard')->name('webapp.dashboard');
	  	Route::post('/dashboard','DashboardController@store')->name('dashboard.store');
	  	
	  	Route::post('/dashboardV1','DashboardV1Controller@storeV1')->name('dashboard.storeV1');

	   Route::get('/paystack/{key}/{amount}/{student_name}','DashboardController@paystack')->name('payment.paystack');
       Route::post('/payment-gateway/paystack', 'DashboardController@paymentPaystack')->name('payment.gateway.paystack');
	   Route::get('/paystack/callback', 'DashboardController@paystackCallback')->name('paystack.callback');
		



	  	Route::get('/payment-gateway/paytm','DashboardController@paymentPayTm')->name('payment.gateway.paytm');
	  	Route::get('/payment-gateway/paytm/response','DashboardController@PayTmResponse')->name('payment.paytm.response');



	  	 ///////////////////////// rohit payment testing /////////////////////////
		Route::get('/payment-dashboard','PaymentDashboardController@dashboard')->name('webapp.paymentDashboard');
		Route::post('/payment-dashboard','PaymentDashboardController@store')->name('dashboard.paymentStore');
	
		
		Route::post('/rohit-payment/add-transaction','PaymentDashboardController@addTransaction')->name('rohitPayment.transaction');

		// paytm payment gateway routes	
		Route::get('/rohit-paytm/{key}/{amount}/{student_name}/{pg_id}','PaymentDashboardController@paytm')->name('payment.rohitPaytm');
		Route::post('/payment-gateway/rohit-paytm/response','PaymentDashboardController@PayTmResponse')->name('payment.rohitPaytm.response');
		
		// payuBiz payment gateway
		Route::get('/rohit-payubiz/{key}/{amount}/{student_name}/{pg_id}','PaymentDashboardController@PayUBiz')->name('payment.rohitPayuBiz');
		Route::get('/payment-gateway/rohit-payubiz/response','PaymentDashboardController@PayUBizResponse')->name('paymentResponse.rohitPayubiz');

		// payuBiz payment gateway
		Route::get('/rohit-payumoney/{key}/{amount}/{student_name}/{pg_id}','PaymentDashboardController@PayuMoney')->name('payment.rohitPayuMoney');
		Route::get('/payment-gateway/rohit-payumoney/response','PaymentDashboardController@PayuMoneyResponse')->name('paymentResponse.rohitPayuMoney');

		// Omniware payment gateway
		Route::get('/rohit-omniware/{key}/{amount}/{student_name}/{pg_id}','PaymentDashboardController@omniware')->name('payment.rohitOmniware');
		Route::post('/payment-gateway/rohit-omniware/response','PaymentDashboardController@omniwareResponse')->name('paymentResponse.rohitOmniware');


		// instamojo payment gateway
		Route::get('/rohit-instamojo/{key}/{amount}/{student_name}/{pg_id}','PaymentDashboardController@instaMojo')->name('payment.rohitInstamojo');
	  	Route::post('/payment-gateway/rohit-instamojo','PaymentDashboardController@paymentinstaMojo')->name('paymentGateway.rohitInstamojo');
	  	Route::get('/payment-gateway/rohit-instamojo/response','PaymentDashboardController@instaMojoResponse')->name('paymentResponse.rohitInstamojo');


		Route::get('/rohit-eazypay/{key}/{amount}/{student_name}/{pg_id}','PaymentDashboardController@eazypay')->name('payment.rohitEazypay');
		
	  	// Route::get('/payment-gateway/get-qr-code/{code}','DashboardController@getQrCode')->name('pgconfig_fetch_dropdown_value.paytm.getQrCode');

	    // webapp profile route

	  // 23-05-2024
		Route::get('/mpesa/{key}/{amount}/{student_name}/{pg_id}','DashboardController@mPesa')->name('payment.mPesa');
		Route::post('/mpesaCall','DashboardController@mpesaCall')->name('payment.mpesaCall');
		Route::post('/mpesa-add-transaction','DashboardController@addMpesaTransaction')->name('payment.addMpesaTransaction');
		Route::get('/mpesa/mPesaTransactionStatus','DashboardController@mPesaTransactionStatus')->name('payment.mPesaTransactionStatus');
		Route::post('/mpesa/mPesaTransactionProcess','DashboardController@mPesaTransactionProcess')->name('payment.mPesaTransactionProcess');
		Route::get('/mpesa/mPesaResponse','DashboardController@mPesaResponse')->name('payment.mPesaResponse');
		Route::post('/mpesa/transactionUpdate','DashboardController@addMpesaTransactionUpdate')->name('mpesa.transactionUpdate');

	    Route::get('/profile','ProfileController@index')->name('webapp.profile.showprofile');
	    Route::post('/profile-update','ProfileController@changePassword')->name('webapp.profile.changepassword');
	    // End webapp profile route
	  	// paytm payment gateway routes	
	  	Route::get('/paytm/{key}/{amount}/{student_name}','DashboardController@paytm')->name('payment.paytm');

	  	Route::post('/payment-gateway/paytm','DashboardController@paymentPayTm')->name('payment.gateway.paytm');

	  	Route::post('/payment-gateway/paytm/response','DashboardController@PayTmResponse')->name('payment.paytm.response');

	  	// pauBiz payment gateway
	  	Route::get('/payuGiz/{key}/{amount}/{student_name}','DashboardController@PayUGiz')->name('payubiz.payment');

	  	Route::post('/payment-gateway/payuGiz','DashboardController@paymentPayUGiz')->name('payubiz.paymentGateway');
	  	Route::get('/payment-gateway/payuGiz/response','DashboardController@PayUGizResponse')->name('payubiz.paymentResponse');

	  	// pauBiz payment gateway
	  	Route::get('/instamojo/{key}/{amount}/{student_name}','DashboardController@instaMojo')->name('instamojo.payment');

	  	Route::post('/payment-gateway/instamojo','DashboardController@paymentinstaMojo')->name('instamojo.paymentGateway');
	  	Route::get('/payment-gateway/instamojo/response','DashboardController@instaMojoResponse')->name('instamojo.paymentResponse');


	  	Route::get('/easypay/{key}/{amount}/{student_name}','DashboardController@easypay')->name('easypay.payment');

		Route::get('/paystackCron','DashboardController@paystackCron')->name('paystackCron');

	  	// add transaction history

	  	// Route::post('/payment/add-transaction','PaymentTransactionController@addTransaction')->name('payubiz.transaction');

	  	Route::post('/payment/add-transaction','PaymentTransactionController@addTransaction')->name('paystack.transaction');
	// Route::post('/verify/transactions/add','verify\RequestVerificationController@AddTransactions')->name('request.verification.add.transaction');


	  	 // student route
	  	 Route::get('/subscribed','StudentSubscribedController@index')->name('studentSubscribed.index');
	  	 Route::post('/subscribed-getdata','StudentSubscribedController@getData')->name('studentSubscribed.getdata');

	  	 Route::get('/session-manager','SessionManagerController@index')->name('webapp-sessionManager');
	  	 Route::post('/session-getsession','SessionManagerController@getSessions')->name('webapp.getSessions');
	  	 Route::post('/session-logoutsingle','SessionManagerController@logoutSingle')->name('webapp.logoutsingle');
	  	 Route::post('/session-logoutAll','SessionManagerController@logoutAll')->name('webapp.logoutAll');
	});
	// Route::post('/system-settings/update-aws-local-file','admin\SystemSettingController@uploadFileAwsORLocal')->name('file-aws-local.update-value');
	Route::get('/verify/login','verify\LoginController@index')->name('verify.login');
	Route::post('/verify/login','verify\LoginController@login')->name('raisoni.login');
	Route::post('/verify/forgot-password','verify\LoginController@ForgotPassword')->name('forgotpassword');
	Route::post('/verify/raisoni-degree-master','verify\LoginController@getDegreeName')->name('raisoni.degree');
	Route::post('/verify/raisoni-branch-master','verify\LoginController@getBranchName')->name('raisoni.degree.branch');
	Route::post('/verify/raisoni-registration','verify\LoginController@SignUp')->name('raisoni.registration');
	Route::post('/verify/verification','verify\LoginController@updateVerification')->name('verification');//khushi
	Route::post('/verify/resend-otp','verify\LoginController@resend_otp')->name('resend-otp');//khushi
	
	Route::get('/verify/payment/paytm','verify\RequestVerificationController@payTmPayment')->name('request.verification.payment.paytm');
	Route::post('/verify/transactions/add','verify\RequestVerificationController@AddTransactions')->name('request.verification.add.transaction');
	
	Route::post('/verify/payment/paytm/response-success','verify\RequestVerificationController@PayTmResponseSuccess')->name('request.verification.paytm.response.success');
	Route::post('/verify/payment/paytm/response-success-qr','verify\RequestVerificationController@PayTmResponseSuccessQR')->name('request.verification.paytm.response.success.qr');

	Route::post('/verify/payment/paytm/response-success-mobile','verify\RequestVerificationController@PayTmResponseSuccessMobile')->name('request.verification.paytm.response.success.qr');
	Route::post('/verify/payment/paytm/response-success-mobile-qr','verify\RequestVerificationController@PayTmResponseSuccessMobileQR')->name('request.verification.paytm.response.success.qr');
	Route::get('/verify/payment/paytm/transaction-status-check','verify\RequestVerificationController@paytmTransStatusCheck')->name('request.verification.payment.paytm.status');
	Route::get('/verify/payment/paytm/trans-status-update-cron','verify\RequestVerificationController@paytmTransStatusUpdatCron');


	# PAYSTACK PAYMENT
	Route::get('/verify/payment/new-paystack','verify\ManagePaymentController@paystackPayment')->name('request.verification.payment.paystack');
	Route::get('/verify/payment/new-paystack/response-success-mobile','verify\ManagePaymentController@paystackResponseSuccessMobile')->name('request.verification.paystack.response.success.qr');
	Route::get('/verify/payment/new-paystack/response-success-mobile-qr','verify\ManagePaymentController@paystackResponseSuccessMobileQR')->name('request.verification.paystack.response.success.qr');

	// paystack

	// Route::get('/verify/payment/paystack','verify\RequestVerificationController@paystackPayment')->name('request.verification.paystack.paystack');
	// Route::post('/verify/payment/paystack/response-success','verify\RequestVerificationController@paystackResponseSuccess')->name('request.verification.paystack.response.success');
	// Route::post('/verify/payment/paystack/response-success-qr','verify\RequestVerificationController@paystackResponseSuccessQR')->name('request.verification.paystack.response.success.qr');
	// Route::post('/verify/payment/paystack/response-success-mobile','verify\RequestVerificationController@paystackResponseSuccessMobile')->name('request.verification.paystack.response.success.qr');
	// Route::post('/verify/payment/paystack/response-success-mobile-qr','verify\RequestVerificationController@paystackResponseSuccessMobileQR')->name('request.verification.paystack.response.success.qr');
	// Route::get('/verify/payment/paystack/transaction-status-check','verify\RequestVerificationController@paystackTransStatusCheck')->name('request.verification.payment.paystack.status');
	// Route::get('/verify/payment/paystack/trans-status-update-cron','verify\RequestVerificationController@paystackTransStatusUpdatCron');
	

	//Route::any('/verify/payu-money-payment', 'verify\RequestVerificationController@redirectToPayU')->name('redirectToPayU');
	Route::any('/verify/payu-money-payment-failure', 'verify\RequestVerificationController@failurePayU')->name('payumoney-failure');
	Route::any('/verify/payu-money-payment-success', 'verify\RequestVerificationController@successPayU')->name('payumoney-success');

// mobile payment api 
	

	Route::group(['prefix'=>'verify','namespace'=>'verify','middleware'=>'web.check'],function(){

		// Route::get('/login','LoginController@index')->name('verify.login');
		// Route::post('/login','LoginController@login')->name('raisoni.login');
		// Route::post('/forgot-password','LoginController@ForgotPassword')->name('forgotpassword');
		// Route::post('/raisoni-degree-master','LoginController@getDegreeName')->name('raisoni.degree');
		// Route::post('/raisoni-branch-master','LoginController@getBranchName')->name('raisoni.degree.branch');
		Route::get('/home','LoginController@Dashboard')->name('verify.home');
		Route::get('/dashboard','ScanqrController@dashboard')->name('verify.dashboard');
		Route::post('/home','ScanqrController@scandata')->name('raisoni.store');
		// Route::get('/login','LoginController@index')->name('verify.login');
		// Route::get('/home','LoginController@home')->name('verify.home');

		Route::get('/request-verification','RequestVerificationController@index')->name('request.verification.index');
		Route::post('/request-verification/type','RequestVerificationController@getDropDown')->name('request.verification.type');
		Route::post('/request-verification/save-request','RequestVerificationController@saveRequest')->name('request.verification.save.request');
		Route::get('/request-verification/success-request','RequestVerificationController@SuccessRequest')->name('request.verification.success.request');
		/*Route::get('/payment/paytm','RequestVerificationController@payTmPayment')->name('request.verification.payment.paytm');
		Route::post('/transactions/add','RequestVerificationController@AddTransactions')->name('request.verification.add.transaction');
		
 		Route::post('/payment/paytm/response-success','RequestVerificationController@PayTmResponseSuccess')->name('request.verification.paytm.response.success');
 		Route::post('/payment/paytm/response-success-qr','RequestVerificationController@PayTmResponseSuccessQR')->name('request.verification.paytm.response.success.qr');*/
		/*Route::get('/request-verification/document-verification/request','RequestVerificationController@DocumentVerificationRequest')->name('request.verification.document.verification.request');*/
		
		Route::get('/request-verification/test-emaill','RequestVerificationControllerTest@testEmail')->name('request.verification.test.email');

		
		

		Route::get('/pending-payments','LoginController@pendingPayments')->name('verify.pending.payments');
		Route::post('/pending-payments/info','LoginController@pendingPaymentsInfo')->name('verify.pending.payments.info');
		Route::get('/pending-payments/payment/paytm/{request_no}','LoginController@paymentPaytm')->name('verify.payments.Paytm');
		Route::post('/pending-payments/remove','LoginController@pendingPaymentsRemove')->name('verify.payments.remove');

		Route::get('/verification-status','VerificationStatusController@index')->name('verify.verification.status');
		Route::post('/verification-status/info','VerificationStatusController@info')->name('verify.verification.status.info');

		Route::get('/scan-history','ScanHistoryController@index')->name('verify.scan.history');
		Route::post('/scan-history/info','ScanHistoryController@info')->name('verify.scan.history.info');

		Route::get('/sessionmanager','SeesionManagerController@index')->name('sessionmanager');
		Route::post('/sessionmanager','SeesionManagerController@getData')->name('raisoni.sessiondata');
		//profile
		Route::get('/profile','ProfileController@index')->name('profile');
		Route::post('/profile','ProfileController@changePassword')->name('raisoni.changepassword');

		Route::post('/logout','LoginController@logout')->name('verify.logout');
	});


	// rohit code 24/07/2023 dashboard
	Route::post('AdminCount','DashboardAjaxController@AdminCount')->name('AdminCount');
	Route::post('instituteUserCount','DashboardAjaxController@instituteUserCount')->name('instituteUserCount');
	Route::post('templateCount','DashboardAjaxController@templateCount')->name('templateCount');
	Route::post('userCount','DashboardAjaxController@userCount')->name('userCount');
	Route::post('certificateCount','DashboardAjaxController@certificateCount')->name('certificateCount');
	Route::post('appAndroidCount','DashboardAjaxController@appAndroidCount')->name('appAndroidCount');
	Route::post('appIosCount','DashboardAjaxController@appIosCount')->name('appIosCount');
	Route::post('appWebCount','DashboardAjaxController@appWebCount')->name('appWebCount');
	Route::post('appGrandCount','DashboardAjaxController@appGrandCount')->name('appGrandCount');
	Route::post('transactionData','DashboardAjaxController@transactionData')->name('transactionData');
	Route::post('scanData','DashboardAjaxController@scanData')->name('scanData');

	// rohit code 24/07/2023 dashboard

	// ROHIT ADDED 08/04/2024 for ttd
	Route::get('admin/ttd/getDetailAjax/{id?}','admin\TtdCertificateController@getDetailAjax')->name('ttd.getDetailAjax');
	Route::post('admin/ttd/getAllRecords','admin\TtdCertificateController@getAllRecords')->name('ttd.getAllRecords');
	Route::post('admin/ttd/status-update','admin\TtdCertificateController@statusUpdate')->name('ttd.statusUpdate');
	Route::post('admin/ttd/ajaxUpdate','admin\TtdCertificateController@ajaxUpdate')->name('ttd.ajaxUpdate');
	
    //Machakos
    Route::resource('admin/machakos-certificate/machakos', admin\MachakosController::class);
    Route::post('admin/machakos-certificate/upload-json', 'admin\MachakosController@uploadJson')->name('upload.json');

	//Transliteration Routes
	Route::get('/text-translator','admin\TextTranslator@index')->name('text-translator.view');
	Route::post('/text-translator','admin\TextTranslator@textTranslate')->name('text-translator.save');

	//Mandar 16-04-2025
	Route::get('admin/exceltopdfnew/uploadpage','admin\ExcelToPdfNewController@uploadpage')->name('exceltopdfnew.uploadpage');
	
	// define web Admin Route
	Route::group(['prefix'=>'admin','namespace'=>'admin','middleware'=>['admin.check','acl.permitted']], 
		function(){

		Route::get('/dashboard','DashboardController@create')->name('admin.dashboard');

		//canvas maker route
		Route::get('template-master/canvas','TemplateMasterController@canvas')->name('canvasmaker.create');
		//excel validation
		Route::post('template-master/excelvalidation','TemplateMasterController@excelvalidation')->name('excel.validation');
		//check excel
		Route::post('template-master/excelcheck','TemplateMasterController@excelcheck')->name('excel.check');

		//store for preview
		Route::post('template-master/saveforpreview','TemplateMasterController@saveforpreview')->name('preview.store');
		//template master route
		Route::resource('template-master','TemplateMasterController',['except'=>'show']);
		//checklimit route
		Route::get('template-master/checkLimit','TemplateMasterController@checkLimit')->name('templateMaster.checkLimit');
		//excel report
		Route::get('template-master/excelreport','TemplateMasterController@excelreport')->name('template-master.excelreport');
		
		//on barcodeimage change
		Route::post('template-master/barcodeimagechange','TemplateMasterController@barcodeimagechange')->name('templateMaster.barcodeImageChange');
		//on genearte pdf click check max certificate validation
		Route::get('template-master/checkmaxcertificate','TemplateMasterController@checkmaxcertificate')->name('templateMaster.maxcerti');
		//check template is mapped or not
		Route::get('template-master/checktemplatemapped','TemplateMasterController@checktemplatemapped')->name('templateMaster.map');
		
		//upload file
		Route::post('template-master/uploadfile','TemplateMasterController@uploadfile')->name('template-master.uplodafile');
		
		//preview pdf
		Route::get('template-master/previewpdf','TemplateMasterController@previewpdf')->name('template-master.preview.pdf');
		Route::get('template-master/demo','TemplateMasterController@demo')->name('demo');
		Route::get('template-master/demo1','TemplateMasterController@demo1')->name('demo1');
		Route::post('template-master/copy-template','TemplateMasterController@copyTemplate')->name('template-master.copyTemplate.copy');
	    // Route::post('template-master/copy-template','TemplateMasterController@copyTemplate')->name('template-master.copy');
		//on background template change
		Route::get('template-master/bgtemplate','TemplateMasterController@bgtemplate')->name('templateMaster.bgtemplatechanges');
		//on font change
		Route::post('template-master/font','TemplateMasterController@font')->name('templateMaster.fontChange');
		// template map

		Route::get('template-master/template-map/{id}','TemplateMasterController@templateMap')->name('template-master.template-map.edit');

		Route::get('template-master/sandboxing','TemplateMasterController@SandBoxCheck')->name('templateMaster.check-sandbox');


		// upload map file
		Route::post('template-map/uploadmap','TemplateMasterController@uploadMapFile')->name('templateMaster.template-map.uploadmap');
	    // upload mapping columns
		Route::post('template-map/mapcolumn','TemplateMasterController@uploadMapColumns')->name('templateMaster.template-map.uploadcolumns');
	    // Map From Database
		Route::post('template-map/mapdatabse','TemplateMasterController@mapFromDatabase')->name('templateMaster.template-map.mapdatabase');

		Route::get('template-map/maptable/{template_id}','MappingTableController@index')->name('template-map.index');

		Route::get('template-map/print','MappingTableController@print')->name('template-map.print');

		// dic routes start
		//canvas maker route
		Route::get('dic-template-master/canvas','DICTemplateMasterController@canvas')->name('diccanvasmaker.create');
		//excel validation
		Route::post('dic-template-master/excelvalidation','DICTemplateMasterController@excelvalidation')->name('dicexcel.validation');
		//check excel
		Route::post('dic-template-master/excelcheck','DICTemplateMasterController@excelcheck')->name('dicexcel.check');

		//store for preview
		Route::post('dic-template-master/saveforpreview','DICTemplateMasterController@saveforpreview')->name('dicpreview.store');
		//template master route
		Route::resource('dic-template-master','DICTemplateMasterController',['except'=>'show']);
		//checklimit route
		Route::get('dic-template-master/checkLimit','DICTemplateMasterController@checkLimit')->name('dictemplateMaster.checkLimit');
		//excel report
		Route::get('dic-template-master/excelreport','DICTemplateMasterController@excelreport')->name('dictemplate-master.excelreport');
		
		//on barcodeimage change
		Route::post('dic-template-master/barcodeimagechange','DICTemplateMasterController@barcodeimagechange')->name('dictemplateMaster.barcodeImageChange');
		//on genearte pdf click check max certificate validation
		Route::get('dictemplate-master/checkmaxcertificate','DICTemplateMasterController@checkmaxcertificate')->name('dictemplateMaster.maxcerti');
		//check template is mapped or not
		Route::get('dic-template-master/checktemplatemapped','DICTemplateMasterController@checktemplatemapped')->name('dictemplateMaster.map');
		
		//upload file
		Route::post('dic-template-master/uploadfile','DICTemplateMasterController@uploadfile')->name('dictemplate-master.uplodafile');
		

		//upload sample pdf file
		Route::post('dic-template-master/uploadsamplefile','DICTemplateMasterController@uploadSamplePdf')->name('dictemplate-master.uploadsamplefile');
		
		//preview pdf
		Route::get('dic-template-master/previewpdf','DICTemplateMasterController@previewpdf')->name('dictemplate-master.preview.pdf');
		Route::get('dic-template-master/demo','DICTemplateMasterController@demo')->name('dicdemo');
		Route::get('dic-template-master/demo1','DICTemplateMasterController@demo1')->name('dicdemo1');
		Route::post('dic-template-master/copy-template','DICTemplateMasterController@copyTemplate')->name('dictemplate-master.copyTemplate.copy');
	    // Route::post('template-master/copy-template','DICTemplateMasterController@copyTemplate')->name('template-master.copy');
		//on background template change
		Route::get('dic-template-master/bgtemplate','DICTemplateMasterController@bgtemplate')->name('dictemplateMaster.bgtemplatechanges');
		//on font change
		Route::post('dic-template-master/font','DICTemplateMasterController@font')->name('dictemplateMaster.fontChange');
		// template map

		Route::get('dic-template-master/template-map/{id}','DICTemplateMasterController@templateMap')->name('dictemplate-master.template-map.edit');

		Route::get('dic-template-master/sandboxing','DICTemplateMasterController@SandBoxCheck')->name('dictemplateMaster.check-sandbox');


		// upload map file
		Route::post('dic-template-map/uploadmap','DICTemplateMasterController@uploadMapFile')->name('dictemplateMaster.template-map.uploadmap');
	    // upload mapping columns
		Route::post('dic-template-map/mapcolumn','DICTemplateMasterController@uploadMapColumns')->name('dictemplateMaster.template-map.uploadcolumns');
	    // Map From Database
		Route::post('dic-template-map/mapdatabse','DICTemplateMasterController@mapFromDatabase')->name('dictemplateMaster.template-map.mapdatabase');

		Route::get('dic-template-map/maptable/{template_id}','DICMappingTableController@index')->name('dictemplate-map.index');

		Route::get('dic-template-map/print','DICMappingTableController@print')->name('dictemplate-map.print');
		// dic routes end
		
		// Route::get('/layout', '/HomeController@index')->name('home');

		//route for dynamic image management
		Route::resource('dynamic-image-management','DynamicImageManagementController',['except'=>['destroy','create','show','edit','update']]);
		//route for sortby or datewise dynamic image
		Route::get('dynamic-image-management/displayImage/{sortby}/{value}','DynamicImageManagementController@displayImage')->name('dynamic-image-management.Display');
		Route::get('dynamic-image-management/displayImage/{sortby}/{value}/{searchkey}','DynamicImageManagementController@displayImage')->name('dynamic-image-management.Display');
		//route for edit dynamic image name
		Route::post('dynamic-image-management/dynamicImageEdit','DynamicImageManagementController@dynamicImageEdit')->name('dynamic-image-management.updatingImage');
		//route for delete dynamic image name
		
		Route::post('dynamic-image-management/delete','DynamicImageManagementController@delete')->name('dynamic-image-management.deleting');

		//institute master route
		Route::resource('institutemaster','InstituteMasterController',['except'=>['destroy','create','edit','show']]);
		//institute master delete route
		Route::post('institutemaster/delete','InstituteMasterController@delete')->name('institutemaster.delete');
	    Route::post('institutemaster/{id}','InstituteMasterController@update')->name('institutemaster.update'); 

		//lab master route
		Route::resource('labmaster','LabMasterController',['except'=>['destroy','create','edit','show']]);
		//lab master delete route
		Route::post('labmaster/delete','LabMasterController@delete')->name('labmaster.delete');
	    Route::post('labmaster/{id}','LabMasterController@update')->name('labmaster.update');         
        
	    // FontMaster Route
	    Route::resource('/fontmaster','FontMasterController',['except'=>['update','show','create','destroy']]);
	    Route::post('/fontMaster/{id}','FontMasterController@update')->name('fontmaster.updates');
	     Route::get('/fontMaster/{id}','FontMasterController@destroy')->name('fontmaster.destroy');
	    

	    // session-manager Route
	    Route::resource('/session-manager','SessionManagerController',['except'=>['update','show','create','edit','store']]);

	    // template data
	    Route::get('/template-data','TemplateDataController@index')->name('template-data.index');

	    // payment gateway config Route
	    route::get('/pg-config','PaymentGatewayConfigController@index')->name('pgconfig.index');
	    route::get('/pg-show','PaymentGatewayConfigController@show')->name('pgconfig_fetch_dropdown_value.show');
	    route::post('/pg-update','PaymentGatewayConfigController@update')->name('pgconfig.update');
	    // End route payment gateway config

	    //payment gateway integration
	    Route::resource('pgmaster','PaymentGatewayController',['except'=>['show','create','update','destroy']]);

	    Route::post('/pgmaster/{id}','PaymentGatewayController@update')->name('pgmaster.update');
	    Route::get('/pgmaster/{id}','PaymentGatewayController@destroy')->name('pgmaster.destroy');


	    //payment gateway integration
	    Route::resource('pgmaster_new','PaymentGatewayNewController',['except'=>['show','create','update','destroy']]);

	    Route::post('/pgmaster_new/{id}','PaymentGatewayNewController@update')->name('pgmaster_new.update');
	    Route::get('/pgmaster_new/{id}','PaymentGatewayNewController@destroy')->name('pgmaster_new.destroy');


		// payment gateway config Route
	    route::get('/pg_new-config','PaymentGatewayNewConfigController@index')->name('pg_newconfig.index');
	    route::get('/pg_new-show','PaymentGatewayNewConfigController@show')->name('pg_newconfig_fetch_dropdown_value.show');
	    route::post('/pg_new-update','PaymentGatewayNewConfigController@update')->name('pg_newconfig.update');

	    
		// background temaplate master
	    Route::resource('background-master','BackgroundTemplateMasterController',['except'=>['show','destroy']]);
		
		Route::get('background-master/show','BackgroundTemplateMasterController@show')->name('background-master.show');

		Route::get('background-master/showDetail','BackgroundTemplateMasterController@showDetail')->name('background-master.showDetail');
		// Route::get('background-master/showDetail/{id}','BackgroundTemplateMasterController@showDetail')->name('background-master.showDetail');
		
		Route::get('background-master/excelExport','BackgroundTemplateMasterController@excelExport')->name('background-master.excelExport');

	    //  UserManagement route 
		Route::resource('/usermaster','UserManagmentController',['except'=>['update','destroy']]);

		Route::post('/usermaster/{id}','UserManagmentController@update')->name('usermaster.update');
		
		Route::post('/usermaster/logout/{id}','UserManagmentController@logout')->name('usermaster.logout');

		Route::get('/userMaster/{id}','UserManagmentController@destroy')->name('usermaster.delete');

	    Route::get('/get-role-Name','UserManagmentController@getRoleName')->name('user-master.role.get');
	    

		

		// StudentManagement Route
		Route::get('/student','StudentManagementController@index')->name('student.index');
		Route::post('/student-upload','StudentManagementController@fileUpload')->name('student.upload');

		// Profile Route
	    Route::get('/profile','ProfileController@index')->name('admin.profile.showprofile');
	    Route::post('/profile-update','ProfileController@changePassword')->name('admin.profile.changepassword');
		// End Profile Route


		Route::get('/manual','ManualController@index')->name('admin.manual.showmanual');


	    // Printing Details Route
	     Route::get("/printing-details",'PrintingDetailController@index')->name('printing-detail.index');
	     Route::get("/printing-getdetails",'PrintingDetailController@getDetail')->name('printing-detail.getdetail');
		// End Printing Details Route

	   	//transacation route
	    Route::get('/transaction','TransactionController@index')->name('transaction.index');  
	    Route::get('/transactionVerification','TransactionVerificationController@index')->name('transactionVerification.index');  
	   	//end transaction route  

	  	//SystemSettingController Route
	   	Route::get('/system-settings','SystemSettingController@index')->name('systemconfig.index');
	   	Route::post('/system-settings','SystemSettingController@store')->name('systemconfig.store');
	   	Route::post('/system-settings/sandboxing','SystemSettingController@sandboxing')->name('sand-box.update-value');
	   	Route::post('/system-settings/sandboxing/update-varification','SystemSettingController@varificationUpdate')->name('varification.sandboxing.update-value');
	   	Route::post('/system-settings/update-aws-local-file','SystemSettingController@uploadFileAwsORLocal')->name('file-aws-local.update-value');

		// prodess excel route
	    Route::get('process-excel','ProcessExcelController@index')->name('processExcel.index');
	    Route::post('process-excel','ProcessExcelController@mergeExcel')->name('processExcel.merge');

		// PrinterReport Route
		Route::get('/printer-report','PrinterReportController@index')->name('printer-report.index');
		
		// CertificateManagement Route
		Route::get('/certificate-management','CertificateManagementController@index')->name('certificateManagement.index');
		
		Route::get('certificate-management/excelExport','CertificateManagementController@excelExport')->name('certificateManagement.excelExport');

		Route::post('certificate-management/update','CertificateManagementController@update')->name('certificateManagement.update');

		Route::post('/certificate-management','CertificateManagementController@generateQRCode')->name('certificateManagement.generateQRCode');

		// IDcards Route
		Route::get('/generate-idcards','IdCardController@index')->name('idcards.index');
		Route::post('/idcard-manageExcel','IdCardController@manageExcel')->name('idcards.manageExcel');
		Route::post('/idcard-excelcheck','IdCardController@excelcheck')->name('idcards_validation.excelcheck');
		Route::post('/idcard-excelValidation','IdCardController@excelvalidation')->name('idcards_validation.excelvalidation');
		Route::post('/idcard-softcopy','IdCardController@generateSoftcopy')->name('idcards.generate.softcopy');
		Route::post('/idcard-softcopy-process','IdCardController@processPdfSoftCopy')->name('idcards.generate.softcopygenerate');

		//digital id card
		Route::post('/digital-idcard-manageExcel','DigitalIdCardController@manageExcel')->name('dicidcards.manageExcel');
		Route::post('/digital-idcard-excelcheck','DigitalIdCardController@excelcheck')->name('dicidcards_validation.excelcheck');
		Route::post('/digital-idcard-excelValidation','DigitalIdCardController@excelvalidation')->name('dicidcards_validation.excelvalidation');
		Route::post('/digital-idcard-softcopy','DigitalIdCardController@generateSoftcopy')->name('dicidcards.generate.softcopy');
		Route::post('/digital-idcard-softcopy-process','DigitalIdCardController@processPdfSoftCopy')->name('dicidcards.generate.softcopygenerate');

		// Payment Transaction
		Route::get('/idcards-status','IdCardStatusController@index')->name('idcard-status.index');
		Route::post('/idcards-status/revoke-request','IdCardStatusController@revokeRequest')->name('idcard-status.revoke');
		Route::post('/idcards-status/update-status','IdCardStatusController@updateStatusToAcknowledge')->name('idcard-status.update.acknowledge');
		Route::post('/idcards-status/update-status-complete','IdCardStatusController@updateStatusToComplete')->name('idcard-status.update.complete');
		Route::post('/idcards-status/process-pdf','IdCardStatusController@processPdf')->name('idcard-status.processPdf');
		Route::post('/idcard-image','IdCardStatusController@checkImage')->name('idcard-status.viewImage');

		


		Route::get('/transaction','TransactionController@index')->name('transaction.index');

	    // scan history route
		Route::get('/scan-history','ScanHistoryController@index')->name('scanHistory.index');
		Route::post('/scan-history','ScanHistoryController@getData')->name('scanningHistory.getdata');
		
		Route::get('/scan-history/updateUserIds','ScanHistoryController@updateUserIds')->name('scanningHistory.updateuserids');


		        // -----ROLES ROUTE-----//
	            Route::resource('/roles','RoleController',['except'=>['show','update','destroy']]);
	            Route::post('/roles/{id}','RoleController@update')->name('roles.update');
	            Route::get('/roles/{id}','RoleController@destroy')->name('roles.destroy');
	            // -----ROLES ROUTE END-----//

	            // -----USER ROUTE-----//
	            Route::resource('/adminmaster', 'AdminManagementController',['except'=>['show','destroy','update']]);
	   Route::get('adminmaster/get-roles','AdminManagementController@getRoleName')->name('admin-master.role.get');
	   Route::post('adminmaster/{id}','AdminManagementController@update')->name('adminmaster.update');
	   Route::get('adminmaster/{id}','AdminManagementController@destroy')->name('adminmaster.destroy');
        Route::get('/adminmaster/adminlab-assign/{id}', 'AdminManagementController@AssignLab')->name('adminmaster.AssignLab');
        Route::put('/adminmaster/adminlab-save/{id}', 'AdminManagementController@AssignLabSave')->name('adminmaster.AssignLabSave');	

        Route::get('/adminmaster/adminsupplier-assign/{id}', 'AdminManagementController@AssignSupplier')->name('adminmaster.AssignSupplier');
        Route::put('/adminmaster/adminsupplier-save/{id}', 'AdminManagementController@AssignSupplierSave')->name('adminmaster.AssignSupplierSave');	
		Route::get('/adminmaster/adminagent-assign/{id}', 'AdminManagementController@AssignAgent')->name('adminmaster.AssignAgent');
        Route::put('/adminmaster/adminagent-save/{id}', 'AdminManagementController@AssignAgentSave')->name('adminmaster.AssignAgentSave');
			
                // -----USER ROUTE END-----//



				Route::get('/import-excel', function () {
    return view('import'); // a simple upload form
});

Route::post('/import-excel', [ConvoStudentImportController::class, 'importExcel'])->name('import.excel');



	   Route::get('/sandboxing/certificate','SandBoxingController@Certificate')->name('sandboxing.certificate.index');

	   Route::post('/sandbox/generate-qr','SandBoxingController@generateQRCode')->name('sandboxing.certificate.generateQR');
	   
	   Route::post('/sandbox/certificate/reprint','SandBoxingController@rePrint')->name('sandboxing.certificate.reprint');

	   Route::post('/sandbox/certificate/unlink-data','SandBoxingController@unlinkData')->name('sandboxing.certificate.unlink');

	   Route::post('/sandbox/certificate/update','SandBoxingController@CertificateUpdate')->name('sandboxing.certificate.update');
		
		
	   Route::get('/sandboxing/printing-detail','SandBoxingController@PrintingDetails')->name('sandboxing.printingDetails.index');

	   Route::get('/sandboxing/printing-detail/getData','SandBoxingController@getDetail')->name('sand-box.printingDetails.getdata');
	   
	   Route::get('/sandboxing/template-data','SandBoxingController@TemplateData')->name('sandboxing.templateData.index');
	   Route::get('/sandboxing/printing-report','SandBoxingController@PrintingReport')->name('sandboxing.printingReport.index');
	   Route::get('/sandboxing/scan-history','SandBoxingController@ScanHistory')->name('sandboxing.scanHistory.index');

	    Route::post('/sandboxing/scan-history/getdata','SandBoxingController@ScanHistoryGetData')->name('sand-box.scanHistory.getdata');

	   Route::get('/sandboxing/payment-transation','SandBoxingController@PaymentTransaction')->name('sandboxing.paymentTransaction.index');
	   		/*______SCRIPT Started Aakashi Modi_________*/
	   		/* Start Rushik work for document verification*/

	   	Route::get('/old-verification','raisoni\OldDocumentsController@index')->name('oldverification.index');
	   	Route::post('/old-verification/document-count','raisoni\OldDocumentsController@documentCount')->name('oldverification.document.count');
	   	Route::post('/old-verification/info-data','raisoni\OldDocumentsController@infoData')->name('oldverification.info-data');
	   	Route::post('/old-verification/edit-data','raisoni\OldDocumentsController@editData')->name('oldverification.edit-data');
	   	Route::post('/old-verification/semester','raisoni\OldDocumentsController@semester')->name('oldverification.semester');
	   	Route::post('/old-verification/exam','raisoni\OldDocumentsController@exam')->name('oldverification.exam');
	   	Route::post('/old-verification/update-form','raisoni\OldDocumentsController@updateForm')->name('oldverification.update-form');
	   	Route::post('/old-verification/report/non-qr','raisoni\OldDocumentsController@ReportNonQr')->name('oldverification.report.nonqr');
	   	Route::post('/old-verification/report/summary','raisoni\OldDocumentsController@ReportSummary')->name('oldverification.report.summary');


	   	//start route for seqrdocuments request 
		Route::get('/seqr_document_requests','raisoni\SeqrDocumentRequestController@index')->name('request_testing');

		Route::post('/seqr-document-requests/transaction/report','raisoni\SeqrDocumentRequestController@TransactionReport')->name('seqr-document-requests.report.transaction');
		Route::post('/seqr-document-requests/summary/report','raisoni\SeqrDocumentRequestController@SummaryReport')->name('seqr-document-requests.report.summary');

		Route::post('/document-request/','raisoni\SeqrDocumentRequestController@show')->name('document-request-data');

		

		//end route for seqrdocuments request 
	   	/* End Rushik work for document verification*/

	   //masters
		   //semester
		  	Route::resource('/semester','raisoni\SemesterController',['except'=>['show','destroy','create','update']]);
		  	Route::post('/semester/{id}','raisoni\SemesterController@update')->name('semester.update');

		  	//branch

		  	Route::resource('/branch','raisoni\BranchController',['except'=>['show','destroy','create','update']]);
		  	Route::post('/branch/{id}','raisoni\BranchController@update')->name('branch.update');
		  	Route::get('/getDegree','raisoni\BranchController@getDegreeName')->name('raisoniMaster.get.degree');

		//stock
		  	Route::resource('/stationarystock','raisoni\StationaryStockController',['except'=>['show','destroy','create','update']]);
		  	//excel report
			Route::post('stationarystock/excelreport','raisoni\StationaryStockController@excelreport')->name('stationarystock.excelreport');

			Route::resource('/damagedstock','raisoni\DamagedStockController',['except'=>['destroy','create','update']]);
			Route::get('/getDamagedDtockDegree','raisoni\DamagedStockController@getDegreeName')->name('raisoniMaster.get.damagedStockDegree');
			Route::get('/getDamagedDtockExam','raisoni\DamagedStockController@getExamName')->name('raisoniMaster.get.damagedStockExam');
			Route::get('/getDamagedDtockBranch','raisoni\DamagedStockController@getBranchName')->name('raisoniMaster.get.damagedStockBranch');
			Route::get('/getDamagedDtockSemester','raisoni\DamagedStockController@getSemesterName')->name('raisoniMaster.get.damagedStockSemester');
			Route::get('/damagedStock/{id}','raisoni\DamagedStockController@destroy')->name('damagedstock.delete');
		  	//excel report
			Route::post('damagedstock/excelreport','raisoni\DamagedStockController@excelreport')->name('damagedstock.excelreport');



		  	//consumption report
			Route::resource('/consumptionreport','raisoni\ConsumptionReportController',['except'=>['destroy','create','update','show','store','edit']]);
			Route::get('/getConsumptionReportSection','raisoni\ConsumptionReportController@getSectionName')->name('raisoniMaster.get.consumptionReportSection');
			Route::get('/getConsumptionReportScheme','raisoni\ConsumptionReportController@getSchemeName')->name('raisoniMaster.get.consumptionReportScheme');
			Route::get('/getConsumptionReportStudentType','raisoni\ConsumptionReportController@getStudentType')->name('raisoniMaster.get.consumptionReportStudentType');
			Route::post('consumptionreport/updateSerialNo','raisoni\ConsumptionReportController@updateSerialNo')->name('consumptionreport.updateSerialNo');


			//consumption report export
			Route::resource('consumptionreportexport','raisoni\ConsumptionReportExportController',['except'=>['destroy','create','update','show','store','edit']]);
			Route::get('/consumptionreportexport/getDegreeBranchName','raisoni\ConsumptionReportExportController@getDegreeBranchName')->name('raisoniMaster.get.getDegreeBranchName');
			Route::get('/consumptionreportexport/getBranch','raisoni\ConsumptionReportExportController@getBranchName')->name('raisoniMaster.get.consumptionReportExportBranch');
		  	//excel report
			Route::post('consumptionreportexport/generateReportConsumption','raisoni\ConsumptionReportExportController@generateReportConsumption')->name('consumptionreportexport.generateReportConsumption');
			Route::post('consumptionreportexport/generateReportConsumptionExam','raisoni\ConsumptionReportExportController@generateReportConsumptionExam')->name('consumptionreportexport.generateReportConsumptionExam');
			Route::post('consumptionreportexport/generateReportConsumptionBranch','raisoni\ConsumptionReportExportController@generateReportConsumptionBranch')->name('consumptionreportexport.generateReportConsumptionBranch');
			Route::post('consumptionreportexport/generateReportConsumptionSemester','raisoni\ConsumptionReportExportController@generateReportConsumptionSemester')->name('consumptionreportexport.generateReportConsumptionSemester');
			Route::post('consumptionreportexport/generateReportConsumptionAllCount','raisoni\ConsumptionReportExportController@generateReportConsumptionAllCount')->name('consumptionreportexport.generateReportConsumptionAllCount');
		  	/*______SCRIPT End Aakashi Modi _________*/

		  	/*______SCRIPT Started Mandar Gawade_________*/
			//Sessions Master
		    Route::resource('sessionsmaster','raisoni\SessionsMasterController',['except'=>['show','create','update','destroy']]);
			Route::post('/sessionsmaster/{id}','raisoni\SessionsMasterController@update')->name('sessionsmaster.update');
		    Route::get('/sessionsmaster/{id}','raisoni\SessionsMasterController@destroy')->name('sessionsmaster.destroy');
		    //Degree Master
		    Route::resource('degreemaster','raisoni\DegreeMasterController',['except'=>['show','create','update','destroy']]);
			Route::post('/degreemaster/{id}','raisoni\DegreeMasterController@update')->name('degreemaster.update');
		    Route::get('/degreemaster/{id}','raisoni\DegreeMasterController@destroy')->name('degreemaster.destroy');


		    // payment gateway config Route Documents Rate Master
		route::get('/documents-rate-master','raisoni\DocumentsRateMasterController@index')->name('documentsratemaster.index');
		route::post('/documents-rate-master','raisoni\DocumentsRateMasterController@update')->name('documentsratemaster.update');

		// payment gateway config Route QR Template Rate Master
		route::get('/template-rate-master','raisoni\TemplateRateMasterController@index')->name('templateratemaster.index');
		route::post('/template-rate-master','raisoni\TemplateRateMasterController@update')->name('templateratemaster.update');
		    /*______SCRIPT End Mandar Gawade_________*/


		    /*______SCRIPT Started Aakashi Modi_________*/
		    //galgotias university degree certificate generation route
		    
			
		    Route::resource('/degree-certifiate','DegreeCertificateController',['except'=>['show','destroy','create','update','store','edit']]);
			//upload file
			Route::post('/degree-certifiate/uploadfile','DegreeCertificateController@uploadfile')->name('degree-certifiate.uploadfile');
			//on genearte pdf click check max certificate validation
			Route::get('/degree-certifiate/checkmaxcertificate','DegreeCertificateController@checkmaxcertificate')->name('degreeCertificate.maxcerti');
			//excel validation
			Route::post('/degree-certifiate/degree-certifiate/excelvalidation','DegreeCertificateController@excelvalidation')->name('degreeCertificate.validation');
			//check excel
			Route::post('/degree-certifiate/excelcheck','DegreeCertificateController@excelcheck')->name('degreeCertificate.check');
			Route::post('/degree-certifiate/pdfGenerate','DegreeCertificateController@pdfGenerate');


			/*______SCRIPT End Aakashi Modi _________*/
			
			//excel to pdf
			Route::get('/exceltopdf/uploadpage','ExcelToPdfController@uploadpage')->name('exceltopdf.uploadpage');
			// IMT Ceriticate Custome
			Route::resource('/imt', ImtCertificateController::class);

			// IMT TTD Ceriticate Custome
			Route::resource('/ttd', TtdCertificateController::class);
			
			// Print Serial Number vgujaipur
			Route::resource('/vgujaipur', vgujaipurController::class);

			/*****Utility*************/
			Route::get('/utility/pdfSample','UtilityController@pdfSample');
			Route::get('/utility/templates','UtilityController@showTemplates')->name('utility.templates');
			Route::post('/utility/validateExcel','UtilityController@validateExcel')->name('utility.validateexcel');
			Route::post('/utility/uploadfile','UtilityController@uploadfile')->name('utility.uploadfile'); 

			/*****Minosha***********/
			Route::get('/minosha-certificate/uploadpage','MinoshaCertificateController@uploadpage')->name('minosha-certificate.uploadpage');
			Route::post('/minosha-certificate/validateExcel','MinoshaCertificateController@validateExcel')->name('minosha-certificate.validateexcel');
			Route::post('/minosha-certificate/uploadfile','MinoshaCertificateController@uploadfile')->name('minosha-certificate.uploadfile');
			Route::get('/minosha-certificate/pdfGenerate','MinoshaCertificateController@pdfGenerate');	

			//Answer booklet
		Route::resource('/answerbooklet','AnswerBookletController',['except'=>['show','create','update','destroy']]);
		//excel report
		Route::post('/answerbooklet/excelreport','AnswerBookletController@excelreport')->name('answerbooklet.excelreport');
	
		Route::get('/anu/mintdata','AnuCertificateController@mintData')->name('anu.mintdata');
		Route::get('/demo/mintdata','DemoCertificateController@mintData')->name('demo.mintdata');
		
	

	});	
	
	
	
	Route::get('/admin/anu/mintDataV1','admin\AnuCertificateController@mintDataV1')->name('anu.mintDataV1');

		


	Route::post('admin/answerbooklet/storeBatchWise','admin\AnswerBookletController@storeBatchWise')->name('answerbooklet.storeBatchWise');
	
	Route::get('admin/answerbooklet/batch-export','admin\AnswerBookletController@getBatchExport');
	Route::get('admin/answerbooklet/batch-exportV1','ExcelExportController@export');


	Route::get('admin/mitwpu/mintdata','admin\MITWPUCertificateController@mintData')->name('mitwpu.mintdata');

	Route::get('admin/mitwpu/mintDataV1','admin\MITWPUCertificateController@mintDataV1')->name('mitwpu.mintDataV1');
	// Route::get('admin/mitwpu/mintDataV2','admin\MITWPUCertificateController@mintDataV2')->name('mitwpu.mintDataV2');
	
	Route::get('/admin/idcards-status/process-pdf-custom','admin\IdCardStatusController@processPdfCustom')->name('idcard-status.processPdfCustom');
	
	Route::get('/admin/old-verification/test-emaill2','admin\raisoni\OldDocumentsController@testEmail2')->name('response.test.email2');

		
		//excel to pdf
		//Route::get('admin/exceltopdf/uploadpage','admin\ExcelToPdfController@uploadpage')->name('exceltopdf.uploadpage');
	
		//For custom Loader 
		Route::post('deleteloaderjson','admin\TemplateMasterController@deleteLoaderFile')->name('deleteloaderjson.delete');

		Route::post('/admin/degreemaster-dropdown','admin\UserManagmentController@getDegreeMaster')->name('degreemaster-dropdown');
		Route::post('/admin/branchmaster-dropdown','admin\UserManagmentController@getBranchMaster')->name('branchmaster-dropdown');

	// /*PDF 2 PDF Mandar*/
	// //template master route
	// //Route::resource('/pdf2pdf-template-maker','admin\pdf2pdf\TemplateDataController',['except'=>'show']);
	// Route::post('/admin/pdf2pdf-template-maker-edit','admin\pdf2pdf\TemplateDataController@edit')->name('pdf2pdf.templatemakeredit');
	// Route::get('/admin/pdf2pdf-template-maker','admin\pdf2pdf\TemplateDataController@templateMaker')->name('pdf2pdf.templatemaker');
	// Route::get('/admin/pdf2pdf-template-list','admin\pdf2pdf\TemplateDataController@index')->name('pdf2pdf.templatelist');
    // Route::get('/admin/pdf2pdf-assign/{id}', 'admin\pdf2pdf\TemplateDataController@AssignLab')->name('pdf2pdf.AssignLab');
    // Route::put('/admin/pdf2pdf-labsave/{id}', 'admin\pdf2pdf\TemplateDataController@AssignLabSave')->name('pdf2pdf.AssignLabSave');
	
	// Route::post('/admin/pdf2pdf-create-template','admin\pdf2pdf\TemplateDataController@createTemplate')->name('pdf2pdf.createtemplate');
	// Route::post('/admin/pdf2pdf-create-textfile','admin\pdf2pdf\TemplateDataController@createTextFile')->name('pdf2pdf.createtextfile');
	// Route::post('/admin/pdf2pdf-proccess-pdf','admin\pdf2pdf\TemplateDataController@processPdf')->name('pdf2pdf.processpdf');
	// Route::post('/admin/pdf2pdf-proccess-pdf-again','admin\pdf2pdf\TemplateDataController@processPdfAgain')->name('pdf2pdf.processpdfagain');
	// Route::get('admin/store-file','admin\pdf2pdf\TemplateDataController@storeFile')->name('pdf2pdf.storefile');
	// Route::get('admin/upload-file-s3','admin\pdf2pdf\TemplateDataController@uploadFilesToS3')->name('pdf2pdf.uplodfiles3');
	// Route::get('admin/test-s3','admin\pdf2pdf\TemplateDataController@testS3')->name('pdf2pdf.tests3');
	
	// Route::get('/admin/pdf2pdf-test','admin\pdf2pdf\TemplateDataController@test')->name('pdf2pdf.test');
    // Route::post('/admin/pdf2pdf-duplicate-template','admin\pdf2pdf\TemplateDataController@duplicateTemplate')->name('pdf2pdf.duplicatetemplate');
    // Route::post('/admin/pdf2pdf-ActiveInactiveTemplate','admin\pdf2pdf\TemplateDataController@ActiveInactiveTemplate')->name('pdf2pdf.ActiveInactiveTemplate');
	// Route::get('/session-data','admin\SessionManagerController@getSessionData')->name('session-data');

	
	// Route::post('/admin/pdf2pdf-image-save','admin\pdf2pdf\TemplateDataController@imageFormSave')->name('pdf2pdf.imagesave');
	// Route::post('/admin/pdf2pdf-image-edit','admin\pdf2pdf\TemplateDataController@imageFormEdit')->name('pdf2pdf.imageedit');
	// Route::post('/admin/pdf2pdf-image-list','admin\pdf2pdf\TemplateDataController@imageList')->name('pdf2pdf.imagelist');
    
    // Route::post('/admin/pdf2pdf-templatemaster-update/{id}','admin\pdf2pdf\TemplateDataController@updateTitle')->name('pdf2pdf.templatemasterupdate'); 
    // Route::post('/admin/pdf2pdf-templatebg-update','admin\pdf2pdf\TemplateDataController@updateAssignBg')->name('pdf2pdf.templatebgupdate'); 
	
	// Route::get('/admin/pdf2pdf-report','admin\Pdf2pdfReportController@index')->name('pdf2pdf-report.index');
	// Route::post('/admin/pdf2pdf-preview','admin\pdf2pdf\TemplateDataController@pdfPreview')->name('pdf2pdf.pdfPreview');
	// /*End pd2pdf*/
	

	// /*EXCEL 2 PDF ROHIT*/
	// //template master route
	// //Route::resource('/excel2pdf-template-maker','admin\excel2pdf\TemplateDataController',['except'=>'show']);
	// Route::post('/admin/excel2pdf-template-maker-edit','admin\excel2pdf\TemplateDataController@edit')->name('excel2pdf.templatemakeredit');
	// Route::get('/admin/excel2pdf-template-maker','admin\excel2pdf\TemplateDataController@templateMaker')->name('excel2pdf.templatemaker');
	// Route::get('/admin/excel2pdf-template-list','admin\excel2pdf\TemplateDataController@index')->name('excel2pdf.templatelist');
	// Route::get('/admin/excel2pdf-assign/{id}', 'admin\excel2pdf\TemplateDataController@AssignLab')->name('excel2pdf.AssignLab');
	// Route::put('/admin/excel2pdf-labsave/{id}', 'admin\excel2pdf\TemplateDataController@AssignLabSave')->name('excel2pdf.AssignLabSave');

	// Route::post('/admin/excel2pdf-create-template','admin\excel2pdf\TemplateDataController@createTemplate')->name('excel2pdf.createtemplate');
	// Route::post('/admin/excel2pdf-create-textfile','admin\excel2pdf\TemplateDataController@createTextFile')->name('excel2pdf.createtextfile');
	// Route::post('/admin/excel2pdf-proccess-pdf','admin\excel2pdf\TemplateDataController@processPdf')->name('excel2pdf.processpdf');
	// Route::post('/admin/excel2pdf-proccess-pdf-again','admin\excel2pdf\TemplateDataController@processPdfAgain')->name('excel2pdf.processpdfagain');
	// Route::get('admin/store-file','admin\excel2pdf\TemplateDataController@storeFile')->name('excel2pdf.storefile');
	// Route::get('admin/upload-file-s3','admin\excel2pdf\TemplateDataController@uploadFilesToS3')->name('excel2pdf.uplodfiles3');
	// Route::get('admin/test-s3','admin\excel2pdf\TemplateDataController@testS3')->name('excel2pdf.tests3');

	// Route::get('/admin/excel2pdf-test','admin\excel2pdf\TemplateDataController@test')->name('excel2pdf.test');
	// Route::post('/admin/excel2pdf-duplicate-template','admin\excel2pdf\TemplateDataController@duplicateTemplate')->name('excel2pdf.duplicatetemplate');
	// Route::post('/admin/excel2pdf-ActiveInactiveTemplate','admin\excel2pdf\TemplateDataController@ActiveInactiveTemplate')->name('excel2pdf.ActiveInactiveTemplate');
	// Route::get('/session-data','admin\SessionManagerController@getSessionData')->name('session-data');


	// Route::post('/admin/excel2pdf-image-save','admin\excel2pdf\TemplateDataController@imageFormSave')->name('excel2pdf.imagesave');
	// Route::post('/admin/excel2pdf-image-edit','admin\excel2pdf\TemplateDataController@imageFormEdit')->name('excel2pdf.imageedit');
	// Route::post('/admin/excel2pdf-image-list','admin\excel2pdf\TemplateDataController@imageList')->name('excel2pdf.imagelist');

	// Route::post('/admin/excel2pdf-templatemaster-update/{id}','admin\excel2pdf\TemplateDataController@updateTitle')->name('excel2pdf.templatemasterupdate'); 
	// Route::post('/admin/excel2pdf-templatebg-update','admin\excel2pdf\TemplateDataController@updateAssignBg')->name('excel2pdf.templatebgupdate'); 

	// Route::get('/admin/excel2pdf-report','admin\Excel2pdfReportController@index')->name('excel2pdf-report.index');
	// Route::post('/admin/excel2pdf-preview','admin\excel2pdf\TemplateDataController@pdfPreview')->name('excel2pdf.pdfPreview');
	// /*End excel2pdf*/


	// /*Excel2pdf*/

	// Route::get('/admin/excel2pdf','admin\pdf2pdf\TemplateDataController@excelToPdf')->name('pdf2pdf.excel2pdf');
	// Route::get('/admin/pdf2pdf-non-minted','admin\pdf2pdf\TemplateDataController@mintNonMintedRecords')->name('pdf2pdf.mintnonmintedrecords');


		Route::group(['prefix'=>'admin','namespace'=>'admin','middleware'=>['admin.check']], function(){

			/*PDF 2 PDF Mandar*/
			//template master route
			Route::resource('/pdf2pdf-template-maker','admin\pdf2pdf\TemplateDataController',['except'=>'show']);
			Route::post('pdf2pdf-template-maker-edit','pdf2pdf\TemplateDataController@edit')->name('pdf2pdf.templatemakeredit');
			Route::get('pdf2pdf-template-maker','pdf2pdf\TemplateDataController@templateMaker')->name('pdf2pdf.templatemaker');
			Route::get('pdf2pdf-template-list','pdf2pdf\TemplateDataController@index')->name('pdf2pdf.templatelist');
			Route::get('pdf2pdf-assign/{id}', 'pdf2pdf\TemplateDataController@AssignLab')->name('pdf2pdf.AssignLab');
			Route::put('pdf2pdf-labsave/{id}', 'pdf2pdf\TemplateDataController@AssignLabSave')->name('pdf2pdf.AssignLabSave');
			
			Route::post('pdf2pdf-create-template','pdf2pdf\TemplateDataController@createTemplate')->name('pdf2pdf.createtemplate');
			Route::post('pdf2pdf-create-textfile','pdf2pdf\TemplateDataController@createTextFile')->name('pdf2pdf.createtextfile');
			Route::post('pdf2pdf-proccess-pdf','pdf2pdf\TemplateDataController@processPdf')->name('pdf2pdf.processpdf');
			Route::post('pdf2pdf-proccess-pdf-again','pdf2pdf\TemplateDataController@processPdfAgain')->name('pdf2pdf.processpdfagain');
			Route::get('store-file','pdf2pdf\TemplateDataController@storeFile')->name('pdf2pdf.storefile');
			Route::get('upload-file-s3','pdf2pdf\TemplateDataController@uploadFilesToS3')->name('pdf2pdf.uplodfiles3');
			Route::get('upload-file-s3-council','pdf2pdf\TemplateDataController@uploadFilesToS3_council')->name('pdf2pdf.uplodfiles3council');
			Route::get('test-s3','pdf2pdf\TemplateDataController@testS3')->name('pdf2pdf.tests3');
			
			Route::get('pdf2pdf-test','pdf2pdf\TemplateDataController@test')->name('pdf2pdf.test');
			Route::post('pdf2pdf-duplicate-template','pdf2pdf\TemplateDataController@duplicateTemplate')->name('pdf2pdf.duplicatetemplate');
			Route::post('pdf2pdf-ActiveInactiveTemplate','pdf2pdf\TemplateDataController@ActiveInactiveTemplate')->name('pdf2pdf.ActiveInactiveTemplate');
			Route::get('/session-data','SessionManagerController@getSessionData')->name('session-data');

			
			Route::post('pdf2pdf-image-save','pdf2pdf\TemplateDataController@imageFormSave')->name('pdf2pdf.imagesave');
			Route::post('pdf2pdf-image-edit','pdf2pdf\TemplateDataController@imageFormEdit')->name('pdf2pdf.imageedit');
			Route::post('pdf2pdf-image-list','pdf2pdf\TemplateDataController@imageList')->name('pdf2pdf.imagelist');
			
			Route::post('pdf2pdf-templatemaster-update/{id}','pdf2pdf\TemplateDataController@updateTitle')->name('pdf2pdf.templatemasterupdate'); 
			Route::post('pdf2pdf-templatebg-update','pdf2pdf\TemplateDataController@updateAssignBg')->name('pdf2pdf.templatebgupdate'); 
			
			Route::get('pdf2pdf-report','Pdf2pdfReportController@index')->name('pdf2pdf-report.index');
			Route::post('pdf2pdf-preview','pdf2pdf\TemplateDataController@pdfPreview')->name('pdf2pdf.pdfPreview');
			/*End pd2pdf*/
			
			/*EXCEL 2 PDF Rohit*/
			//template master route
			//Route::resource('/excel2pdf-template-maker','admin\excel2pdf\TemplateDataController',['except'=>'show']);
			Route::post('excel2pdf-template-maker-edit','excel2pdf\TemplateDataController@edit')->name('excel2pdf.templatemakeredit');
			Route::get('excel2pdf-template-maker','excel2pdf\TemplateDataController@templateMaker')->name('excel2pdf.templatemaker');
			Route::get('excel2pdf-template-list','excel2pdf\TemplateDataController@index')->name('excel2pdf.templatelist');
			Route::get('excel2pdf-assign/{id}', 'excel2pdf\TemplateDataController@AssignLab')->name('excel2pdf.AssignLab');
			Route::put('excel2pdf-labsave/{id}', 'excel2pdf\TemplateDataController@AssignLabSave')->name('excel2pdf.AssignLabSave');

			Route::post('excel2pdf-create-template','excel2pdf\TemplateDataController@createTemplate')->name('excel2pdf.createtemplate');
			Route::post('excel2pdf-create-textfile','excel2pdf\TemplateDataController@createTextFile')->name('excel2pdf.createtextfile');
			Route::post('excel2pdf-proccess-pdf','excel2pdf\TemplateDataController@processPdf')->name('excel2pdf.processpdf');
			Route::post('excel2pdf-proccess-pdf-again','excel2pdf\TemplateDataController@processPdfAgain')->name('excel2pdf.processpdfagain');
			Route::get('store-file','excel2pdf\TemplateDataController@storeFile')->name('excel2pdf.storefile');
			Route::get('upload-file-s3','excel2pdf\TemplateDataController@uploadFilesToS3')->name('excel2pdf.uplodfiles3');
			Route::get('test-s3','excel2pdf\TemplateDataController@testS3')->name('excel2pdf.tests3');

			Route::get('excel2pdf-test','excel2pdf\TemplateDataController@test')->name('excel2pdf.test');
			Route::post('excel2pdf-duplicate-template','excel2pdf\TemplateDataController@duplicateTemplate')->name('excel2pdf.duplicatetemplate');
			Route::post('excel2pdf-ActiveInactiveTemplate','excel2pdf\TemplateDataController@ActiveInactiveTemplate')->name('excel2pdf.ActiveInactiveTemplate');
			Route::get('/session-data','admin\SessionManagerController@getSessionData')->name('session-data');


			Route::post('excel2pdf-image-save','excel2pdf\TemplateDataController@imageFormSave')->name('excel2pdf.imagesave');
			Route::post('excel2pdf-image-edit','excel2pdf\TemplateDataController@imageFormEdit')->name('excel2pdf.imageedit');
			Route::post('excel2pdf-image-list','excel2pdf\TemplateDataController@imageList')->name('excel2pdf.imagelist');

			Route::post('excel2pdf-templatemaster-update/{id}','excel2pdf\TemplateDataController@updateTitle')->name('excel2pdf.templatemasterupdate'); 
			Route::post('excel2pdf-templatebg-update','excel2pdf\TemplateDataController@updateAssignBg')->name('excel2pdf.templatebgupdate'); 

			Route::get('excel2pdf-report','Excel2pdfReportController@index')->name('excel2pdf-report.index');
			Route::post('excel2pdf-preview','excel2pdf\TemplateDataController@pdfPreview')->name('excel2pdf.pdfPreview');
			/*End EXCEL 2 PDF*/
			
		});

	Route::group(['middleware'=>['admin.check','acl.permitted']], function(){
		
		// yuvaparivartan template
		Route::get('/yuvaparivartan-certificate/uploadpage','admin\YuvaparivartanCertificateController@uploadpage')->name('yuvaparivartan-certificate.uploadpage');
		Route::post('/yuvaparivartan-certificate/validateExcel','admin\YuvaparivartanCertificateController@validateExcel')->name('yuvaparivartan-certificate.validateexcel');
		Route::post('/yuvaparivartan-certificate/uploadfile','admin\YuvaparivartanCertificateController@uploadfile')->name('yuvaparivartan-certificate.uploadfile'); 
		Route::get('/yuvaparivartan-certificate/pdfGenerate','admin\YuvaparivartanCertificateController@pdfGenerate');
		Route::get('/yuvaparivartan-records/uploadpage','admin\YuvaparivartanCertificateController@index')->name('yuvaparivartan-records.uploadpage');
		Route::post('/yuvaparivartan-records/verification','admin\YuvaparivartanCertificateController@verification')->name('yuvaparivartan-records.verification');

	});

	
	//galgotias certificate
	Route::get('/galgotias-certificate/uploadpage','admin\DegreeCertificateController@uploadpage')->name('galgotias-certificate.uploadpage');
	Route::post('/galgotias-certificate/validateexcel','admin\DegreeCertificateController@validateExcel')->name('galgotias-certificate.validateexcel');
	Route::post('/galgotias-certificate/uploadfile','admin\DegreeCertificateController@uploadfile')->name('galgotias-certificate.uploadfile');
	Route::get('/degree-certifiate/pdfGenerate','admin\DegreeCertificateController@pdfGenerate');
	Route::get('/degree-certifiate/database','admin\DegreeCertificateController@databaseGenerate');
	Route::get('/degree-certifiate/dbUploadfile','admin\DegreeCertificateController@dbUploadfile')->name('degree-certifiate.dbUploadfile');
	Route::get('/galgotias-certificate/checkimagepage','admin\DegreeCertificateController@uploadCheckImagePage')->name('galgotias-certificate.checkimagepage');
	Route::post('/galgotias-certificate/checkimageexist','admin\DegreeCertificateController@checkImageExist')->name('galgotias-certificate.checkimageexist');
	
	//Route::get("fetchdetail",[FetchdetailsController::class,"fetchdetail"]);
	//UASB Certificates
	Route::get('/uasb-certificate/index','admin\UASBCertificateController@index')->name('uasb-certificate.index');
	Route::get('/uasb-certifiate/pdfGenerate','admin\UASBCertificateController@pdfGenerate');
	Route::get('/uasb-certifiate/databaseGenerate','admin\UASBCertificateController@databaseGenerate');

	Route::get('/uasb-certifiate/dbUploadUgpg','admin\UASBCertificateController@dbUploadUgpg')->name('uasb-certifiate.dbUploadfileugpg');
	Route::get('/uasb-certifiate/dbUploadGold','admin\UASBCertificateController@dbUploadGold')->name('uasb-certifiate.dbUploadfilegold');
	Route::post('/uasb-certificate/uploadfile','admin\UASBCertificateController@uploadfile')->name('uasb-certificate.uploadfile');
	Route::post('/uasb-certificate/validateExcel','admin\UASBCertificateController@validateExcel')->name('uasb-certificate.validateExcel');
	//BMCC SOM 
	Route::get('/bmcc-certificate/uploadpage','admin\BMCCCertificateController@uploadpage')->name('bmcc-certificate.uploadpage');
	Route::post('/bmcc-certificate/validateExcel','admin\BMCCCertificateController@validateExcel')->name('bmcc-certificate.validateexcel');
	Route::post('/bmcc-certificate/uploadfile','admin\BMCCCertificateController@uploadfile')->name('bmcc-certificate.uploadfile');
	Route::get('/bmcc-certificate/pdfGenerate','admin\BMCCCertificateController@pdfGenerate');
	Route::get('/bmcc-certificate/pdfSample','admin\BMCCCertificateController@pdfSample');
	Route::get('/bmcc-certificate/uploadpagePassing','admin\BMCCCertificateController@uploadpagePassing')->name('bmcc-certificate.uploadpagePassing');
	Route::post('/bmcc-certificate/validateExcelPassing','admin\BMCCCertificateController@validateExcelPassing')->name('bmcc-certificate.validateexcelPassing');
	Route::post('/bmcc-certificate/uploadfilePassing','admin\BMCCCertificateController@uploadfilePassing')->name('bmcc-certificate.uploadfilePassing');

	//WOXSEN
	Route::get('/woxsen-certificate/uploadpage','admin\WOXSENCertificateController@uploadpage')->name('woxsen-certificate.uploadpage');
	Route::post('/woxsen-certificate/validateExcel','admin\WOXSENCertificateController@validateExcel')->name('woxsen-certificate.validateexcel');
	Route::post('/woxsen-certificate/uploadfile','admin\WOXSENCertificateController@uploadfile')->name('woxsen-certificate.uploadfile');
	Route::get('/woxsen-certificate/pdfGenerate','admin\WOXSENCertificateController@pdfGenerate');


	
	Route::get('/woxsen-certificate/pdfGenerate1','admin\WOXSENCertificateController@pdfGenerate1');

	//Ferguson SOM
	Route::get('/fergusson-certificate/uploadpage','admin\FergussonCertificateController@uploadpage')->name('fergusson-certificate.uploadpage');
	Route::post('/fergusson-certificate/validateExcel','admin\FergussonCertificateController@validateExcel')->name('fergusson-certificate.validateexcel');
	Route::post('/fergusson-certificate/uploadfile','admin\FergussonCertificateController@uploadfile')->name('fergusson-certificate.uploadfile');
	Route::get('/fergusson-certificate/pdfGenerate','admin\FergussonCertificateController@pdfGenerate');


	Route::get('/passing-certificate/uploadpage','admin\PassingCertificateController@uploadPage')->name('passing-certificate.uploadpage');
	Route::post('/passing-certificate/validateExcel','admin\PassingCertificateController@validateExcel')->name('passing-certificate.validateexcel');
	Route::post('/passing-certificate/uploadfile','admin\PassingCertificateController@uploadfile')->name('passing-certificate.uploadfile');

	//KESSC Certificate 
	Route::get('/kessc-certificate/uploadpage','admin\KESSCCertificateController@uploadpage')->name('kessc-certificate.uploadpage');
	Route::post('/kessc-certificate/validateExcel','admin\KESSCCertificateController@validateExcel')->name('kessc-certificate.validateexcel');
	Route::post('/kessc-certificate/uploadfile','admin\KESSCCertificateController@uploadfile')->name('kessc-certificate.uploadfile');
	Route::get('/kessc-certificate/pdfGenerate','admin\KESSCCertificateController@pdfGenerate');
	Route::get('/kessc-addserial/uploadpage','admin\KESSCAddserialController@uploadpage')->name('kessc-addserial.uploadpage');
	Route::post('/kessc-addserial/uploadfile','admin\KESSCAddserialController@uploadfile')->name('kessc-addserial.uploadfile');

	//AURO Certificate 
	Route::get('/auro-certificate/uploadpage','admin\AUROCertificateController@uploadpage')->name('auro-certificate.uploadpage');
	Route::post('/auro-certificate/validateExcel','admin\AUROCertificateController@validateExcel')->name('auro-certificate.validateexcel');
	Route::post('/auro-certificate/uploadfile','admin\AUROCertificateController@uploadfile')->name('auro-certificate.uploadfile');
	Route::get('/auro-certificate/pdfGenerate','admin\AUROCertificateController@pdfGenerate');

	//RRMU Certificate
	Route::get('/rrmu-certificate/uploadpage','admin\RRMUCertificateController@uploadpage')->name('rrmu-certificate.uploadpage');
	Route::post('/rrmu-certificate/validateExcel','admin\RRMUCertificateController@validateExcel')->name('rrmu-certificate.validateexcel');
	Route::post('/rrmu-certificate/uploadfile','admin\RRMUCertificateController@uploadfile')->name('rrmu-certificate.uploadfile'); 
	Route::get('/rrmu-certificate/pdfGenerate','admin\RRMUCertificateController@pdfGenerate');

	//Cavendish Certificate
	Route::get('/cavendish-certificate/uploadpage','admin\CavendishCertificateController@uploadpage')->name('cavendish-certificate.uploadpage');
	Route::post('/cavendish-certificate/validateExcel','admin\CavendishCertificateController@validateExcel')->name('cavendish-certificate.validateexcel');
	Route::post('/cavendish-certificate/uploadfile','admin\CavendishCertificateController@uploadfile')->name('cavendish-certificate.uploadfile'); 
	Route::get('/cavendish-certificate/pdfGenerate','admin\CavendishCertificateController@pdfGenerate');
	
	
	//iccs Certificate 
	Route::get('/iccs-certificate/uploadpage','admin\ICCSCertificateController@uploadpage')->name('iccs-certificate.uploadpage');
	Route::post('/iccs-certificate/validateExcel','admin\ICCSCertificateController@validateExcel')->name('iccs-certificate.validateexcel');
	Route::post('/iccs-certificate/uploadfile','admin\ICCSCertificateController@uploadfile')->name('iccs-certificate.uploadfile');
	Route::get('/iccs-certificate/pdfGenerate','admin\ICCSCertificateController@pdfGenerate');

	//MONAD Certificate 
	Route::get('/monad-certificate/uploadpage','admin\MONADCertificateController@uploadpage')->name('monad-certificate.uploadpage');
	Route::post('/monad-certificate/validateExcel','admin\MONADCertificateController@validateExcel')->name('monad-certificate.validateexcel');
	Route::post('/monad-certificate/uploadfile','admin\MONADCertificateController@uploadfile')->name('monad-certificate.uploadfile');
	Route::get('/monad-certificate/pdfGenerate','admin\MONADCertificateController@pdfGenerate');
	
	//SPIT Certificate 
	Route::get('/spit-certificate/uploadpage','admin\SpitCertificateController@uploadpage')->name('spit-certificate.uploadpage');
	Route::post('/spit-certificate/validateExcel','admin\SpitCertificateController@validateExcel')->name('spit-certificate.validateexcel');
	Route::post('/spit-certificate/uploadfile','admin\SpitCertificateController@uploadfile')->name('spit-certificate.uploadfile');
	Route::get('/spit-certificate/pdfGenerate','admin\SpitCertificateController@pdfGenerate');
	
	//VBIT Certificate 
	Route::get('/vbit-certificate/uploadpage','admin\VbitCertificateController@uploadpage')->name('vbit-certificate.uploadpage');
	Route::post('/vbit-certificate/validateExcel','admin\VbitCertificateController@validateExcel')->name('vbit-certificate.validateexcel');
	Route::post('/vbit-certificate/uploadfile','admin\VbitCertificateController@uploadfile')->name('vbit-certificate.uploadfile');
	Route::get('/vbit-certificate/pdfGenerate','admin\VbitCertificateController@pdfGenerate');
	
	//Sadabai Raisoni Nagpur Certificate 
	Route::get('/srwcnagpur-certificate/uploadpage','admin\SrwcnagpurCertificateController@uploadpage')->name('srwcnagpur-certificate.uploadpage');
	Route::post('/srwcnagpur-certificate/validateExcel','admin\SrwcnagpurCertificateController@validateExcel')->name('srwcnagpur-certificate.validateexcel');
	Route::post('/srwcnagpur-certificate/uploadfile','admin\SrwcnagpurCertificateController@uploadfile')->name('srwcnagpur-certificate.uploadfile');
	Route::get('/srwcnagpur-certificate/pdfGenerate','admin\SrwcnagpurCertificateController@pdfGenerate');
	
	//Rawatpura Certificate 
	Route::get('/rawatpura-certificate/uploadpage','admin\RawatpuraCertificateController@uploadpage')->name('rawatpura-certificate.uploadpage');
	Route::post('/rawatpura-certificate/validateExcel','admin\RawatpuraCertificateController@validateExcel')->name('rawatpura-certificate.validateexcel');
	Route::post('/rawatpura-certificate/uploadfile','admin\RawatpuraCertificateController@uploadfile')->name('rawatpura-certificate.uploadfile');
	Route::get('/rawatpura-certificate/pdfGenerate','admin\RawatpuraCertificateController@pdfGenerate');
	
	//CEDP Certificate 
	Route::get('/cedp-certificate/uploadpage','admin\CedpCertificateController@uploadpage')->name('cedp-certificate.uploadpage');
	Route::post('/cedp-certificate/validateExcel','admin\CedpCertificateController@validateExcel')->name('cedp-certificate.validateexcel');
	Route::post('/cedp-certificate/uploadfile','admin\CedpCertificateController@uploadfile')->name('cedp-certificate.uploadfile');
	Route::get('/cedp-certificate/pdfGenerate','admin\CedpCertificateController@pdfGenerate');

	//ISCN Certificate 
	Route::get('/iscn-certificate/uploadpage','admin\IscnCertificateController@uploadpage')->name('iscn-certificate.uploadpage');
	Route::post('/iscn-certificate/validateExcel','admin\IscnCertificateController@validateExcel')->name('iscn-certificate.validateexcel');
	Route::post('/iscn-certificate/uploadfile','admin\IscnCertificateController@uploadfile')->name('iscn-certificate.uploadfile');
	Route::get('/iscn-certificate/pdfGenerate','admin\IscnCertificateController@pdfGenerate');
	
	//Ghribmjal Certificate 
	Route::get('/ghribmjal-certificate/uploadpage','admin\GhribmjalCertificateController@uploadpage')->name('ghribmjal-certificate.uploadpage');
	Route::post('/ghribmjal-certificate/validateExcel','admin\GhribmjalCertificateController@validateExcel')->name('ghribmjal-certificate.validateexcel');
	Route::post('/ghribmjal-certificate/uploadfile','admin\GhribmjalCertificateController@uploadfile')->name('ghribmjal-certificate.uploadfile');
	Route::get('/ghribmjal-certificate/pdfGenerate','admin\GhribmjalCertificateController@pdfGenerate')->name('ghribmjal-certificate.pdfGenerate');    
	
	//MOLWA Certificate
	Route::get('/molwa-certificate/index','admin\MOLWACertificateController@index')->name('molwa-certificate.index');
	Route::get('/molwa-certificate/pdfGenerate','admin\MOLWACertificateController@pdfGenerate')->name('molwa-certificate.pdfGenerate');
	Route::get('/molwa-certificate/importData','admin\MOLWACertificateController@importData')->name('molwa-certificate.importData');
	Route::get('/molwa-certificate/getProgess','admin\MOLWACertificateController@getProgess')->name('molwa-certificate.getProgess');
	Route::get('/molwa-certificate/getIdProgess','admin\MOLWACertificateController@getIdProgess')->name('molwa-certificate.getIdProgess');
	Route::get('/molwa-certificate/getCtProgess','admin\MOLWACertificateController@getCtProgess')->name('molwa-certificate.getCtProgess');
	Route::get('/molwa-certificate/ImportPrint','admin\MOLWACertificateController@ImportPrint')->name('molwa-certificate.ImportPrint'); 
	Route::get('/molwa-certificate/importFfData','admin\MOLWACertificateController@importFfData')->name('molwa-certificate.importFfData');
	Route::get('/molwa-certificate/ExportToExcel/{id}/{flag}','admin\MOLWACertificateController@ExportToExcel')->name('molwa-certificate.ExportToExcel');
	Route::get('/molwa-certificate/IdGenerate','admin\MOLWACertificateController@IdGenerate')->name('molwa-certificate.IdGenerate');
	Route::get('/molwa-certificate/CertificateGenerate/','admin\MOLWACertificateController@CertificateGenerate')->name('molwa-certificate.CertificateGenerate');
	Route::get('/molwa-certificate/StatusComplete/','admin\MOLWACertificateController@StatusComplete')->name('molwa-certificate.StatusComplete');
	Route::post('/molwa-certificate/ViewImportFF/','admin\MOLWACertificateController@ViewImportFF')->name('molwa-certificate.ViewImportFF');
	Route::post('/molwa-certificate/ViewFF/','admin\MOLWACertificateController@ViewFF')->name('molwa-certificate.ViewFF');
	Route::post('/molwa-certificate/ActiveInactiveRecord/','admin\MOLWACertificateController@ActiveInactiveRecord')->name('molwa-certificate.ActiveInactiveRecord');
	Route::get('/molwa-certificate/ReImportPrint','admin\MOLWACertificateController@ReImportPrint')->name('molwa-certificate.ReImportPrint'); 
	Route::post('/molwa-certificate/REimportFfData','admin\MOLWACertificateController@REimportFfData')->name('molwa-certificate.REimportFfData');
	Route::get('/molwa-certificate/getReProgess','admin\MOLWACertificateController@getReProgess')->name('molwa-certificate.getReProgess');
	Route::post('/molwa-certificate/ViewReImportFF/','admin\MOLWACertificateController@ViewReImportFF')->name('molwa-certificate.ViewReImportFF');
	Route::get('/molwa-certificate/IdReGenerate','admin\MOLWACertificateController@IdReGenerate')->name('molwa-certificate.IdReGenerate');
	Route::get('/molwa-certificate/getIdReProgess','admin\MOLWACertificateController@getIdReProgess')->name('molwa-certificate.getIdReProgess');
	Route::get('/molwa-certificate/CertificateReGenerate/','admin\MOLWACertificateController@CertificateReGenerate')->name('molwa-certificate.CertificateReGenerate');
	Route::get('/molwa-certificate/getCtReProgess','admin\MOLWACertificateController@getCtReProgess')->name('molwa-certificate.getCtReProgess');
	Route::get('/molwa-certificate/StatusReComplete/','admin\MOLWACertificateController@StatusReComplete')->name('molwa-certificate.StatusReComplete');
	Route::post('/molwa-certificate/editFfData','admin\MOLWACertificateController@editFfData')->name('molwa-certificate.editFfData');
	Route::post('/molwa-certificate/CertificateGeneratePreview/','admin\MOLWACertificateController@CertificateGeneratePreview')->name('molwa-certificate.CertificateGeneratePreview');
	Route::post('/molwa-certificate/IdGeneratePreview/','admin\MOLWACertificateController@IdGeneratePreview')->name('molwa-certificate.IdGeneratePreview');
	Route::get('/molwa-certificate/molwaReport','admin\MOLWACertificateController@molwaReport')->name('molwa-certificate.molwaReport');	
	Route::post('/molwa-certificate/molwaReportGet','admin\MOLWACertificateController@molwaReportGet')->name('molwa-certificate.molwaReportGet');	
	
	Route::get('/molwa-certificate/ViewImportDetails','admin\MOLWACertificateController@ViewImportDetails')->name('molwa-certificate.ViewImportDetails');
	Route::get('/api/FFinfo','api\FFController@FFinfo')->name('api.FFinfo');
	Route::get('/api/FFhistory','api\FFController@FFhistory')->name('api.FFhistory');
	Route::get('/api/FFhistoryBulk','api\FFController@FFhistoryBulk')->name('api.FFhistoryBulk');
	Route::get('/api/FFEnableDisable','api\FFController@FFEnableDisable')->name('api.FFEnableDisable');	
	Route::get('/molwa-certificate/molwaReport','admin\MOLWACertificateController@molwaReport')->name('molwa-certificate.molwaReport');	
	Route::post('/molwa-certificate/molwaReportGet','admin\MOLWACertificateController@molwaReportGet')->name('molwa-certificate.molwaReportGet');	
	Route::get('/molwa-certificate/molwaRemarks','admin\MolwaFFremarkController@index')->name('molwa-certificate.molwaRemarks');	
	
	Route::get('/molwa-certificate/getCtCompleteProgess','admin\MOLWACertificateController@getCtCompleteProgess')->name('molwa-certificate.getCtCompleteProgess');
	Route::get('/molwa-certificate/sendUpdateToMolwa/','admin\MOLWACertificateController@sendUpdateToMolwa')->name('molwa-certificate.sendUpdateToMolwa');
	//Secura
	Route::get('/secura-certificate/uploadpage','admin\SecuraCertificateController@uploadpage')->name('secura-certificate.uploadpage');
	Route::post('/secura-certificate/validateExcel','admin\SecuraCertificateController@validateExcel')->name('secura-certificate.validateexcel');
	Route::post('/secura-certificate/uploadfile','admin\SecuraCertificateController@uploadfile')->name('secura-certificate.uploadfile');
	Route::get('/secura-certificate/pdfGenerate','admin\SecuraCertificateController@pdfGenerate')->name('secura-certificate.pdfGenerate');
	
	
	//KMTC Certificate 
	Route::get('/kmtc-certificate/uploadpage','admin\KmtcCertificateController@uploadpage')->name('kmtc-certificate.uploadpage');
	Route::post('/kmtc-certificate/validateExcel','admin\KmtcCertificateController@validateExcel')->name('kmtc-certificate.validateexcel');
	Route::post('/kmtc-certificate/uploadfile','admin\KmtcCertificateController@uploadfile')->name('kmtc-certificate.uploadfile');
	Route::get('/kmtc-certificate/pdfGenerate','admin\KmtcCertificateController@pdfGenerate');
	
	
	//Bestiu Certificate 
	Route::get('/bestiu-certificate/uploadpage','admin\BestiuCertificateController@uploadpage')->name('bestiu-certificate.uploadpage');
	Route::post('/bestiu-certificate/validateExcel','admin\BestiuCertificateController@validateExcel')->name('bestiu-certificate.validateexcel');
	Route::post('/bestiu-certificate/uploadfile','admin\BestiuCertificateController@uploadfile')->name('bestiu-certificate.uploadfile');
	Route::get('/bestiu-certificate/pdfGenerate','admin\BestiuCertificateController@pdfGenerate');
	

	//Bestiu Certificate 
	Route::get('/bestiu-e-document/uploadpage','admin\BestiuEDocumentController@uploadpage')->name('bestiu-e-document.uploadpage');
	Route::post('/bestiu-e-document/validateExcel','admin\BestiuEDocumentController@validateExcel')->name('bestiu-e-document.validateexcel');
	Route::post('/bestiu-e-document/uploadfile','admin\BestiuEDocumentController@uploadfile')->name('bestiu-e-document.uploadfile');


//sbcity college


		Route::get('/sbcity-certificate/uploadpage', 'admin\SbcityCertificateController@uploadpage')->name('sbcity-certificate.uploadpage');
	Route::post('/sbcity-certificate/validateExcel', 'admin\SbcityCertificateController@validateExcel')->name('sbcity-certificate.validateexcel');
	Route::post('/sbcity-certificate/uploadfile', 'admin\SbcityCertificateController@uploadfile')->name('sbcity-certificate.uploadfile');
	Route::get('/sbcity-certificate/pdfGenerate', 'admin\SbcityCertificateController@pdfGenerate');
	
	//sgrsa Certificate 
	Route::get('/sgrsa-certificate/addform','admin\SgrsaCertificateController@addForm')->name('sgrsa-certificate.addform');	
	Route::post('/sgrsa-certificate/recallData','admin\SgrsaCertificateController@recallData')->name('sgrsa-certificate.recallData');
	Route::get('/sgrsa-certificate/RecallList','admin\SgrsaCertificateController@index')->name('sgrsa-certificate.RecallList'); 
	Route::get('/sgrsa-certificate/getUnitsrno','admin\SgrsaCertificateController@getUnitsrno')->name('sgrsa-certificate.getUnitsrno'); 
	Route::get('/sgrsa-certificate/getRecordid','admin\SgrsaCertificateController@getRecordid')->name('sgrsa-certificate.getRecordid'); 
	Route::post('/sgrsa-certificate/RenewRecall','admin\SgrsaCertificateController@RenewRecall')->name('sgrsa-certificate.RenewRecall'); 
	Route::get('/sgrsa-governor/addform','admin\SgrsaGovernorController@addForm')->name('sgrsa-governor.addform');
	Route::get('/sgrsa-governor/saveData','admin\SgrsaGovernorController@saveData')->name('sgrsa-governor.saveData');
	Route::get('/sgrsa-governor/GovernorList','admin\SgrsaGovernorController@index')->name('sgrsa-governor.GovernorList');
	Route::get('/sgrsa-governor/adduform','admin\SgrsaGovernorController@addUForm')->name('sgrsa-governor.adduform');
	Route::get('/sgrsa-governor/saveUData','admin\SgrsaGovernorController@saveUData')->name('sgrsa-governor.saveUData');
	Route::get('/sgrsa-governor/UnitList','admin\SgrsaGovernorController@Unitindex')->name('sgrsa-governor.UnitList'); 
	Route::get('/sgrsa-supplier/addform','admin\SgrsaSupplierController@addForm')->name('sgrsa-supplier.addform');
	Route::post('/sgrsa-supplier/supplierData','admin\SgrsaSupplierController@supplierData')->name('sgrsa-supplier.supplierData');
	Route::get('/sgrsa-supplier/SupplierList','admin\SgrsaSupplierController@index')->name('sgrsa-supplier.SupplierList'); 	
	Route::get('/sgrsa-supplier/EditRecord','admin\SgrsaSupplierController@EditRecord')->name('sgrsa-supplier.EditRecord'); 
	Route::post('/sgrsa-supplier/editData','admin\SgrsaSupplierController@editData')->name('sgrsa-supplier.editData');	
	Route::get('/sgrsa-supplier/editform','admin\SgrsaSupplierController@editForm')->name('sgrsa-supplier.editform');
	Route::get('/sgrsa-agent/addform','admin\SgrsaAgentController@addForm')->name('sgrsa-agent.addform');
	Route::get('/sgrsa-agent/agentData','admin\SgrsaAgentController@agentData')->name('sgrsa-agent.agentData');
	Route::get('/sgrsa-agent/AgentList','admin\SgrsaAgentController@index')->name('sgrsa-agent.AgentList'); 
	Route::get('/sgrsa-agent/EditRecord','admin\SgrsaAgentController@EditRecord')->name('sgrsa-agent.EditRecord'); 
	Route::get('/sgrsa-agent/editData','admin\SgrsaAgentController@editData')->name('sgrsa-agent.editData');
	Route::post('/api/AddInfo','api\RecallController@AddInfo')->name('api.AddInfo');
	Route::get('/sgrsa-certificate/srnoGenerate','admin\SgrsaCertificateController@srnoGenerate');
	Route::post('/sgrsa-certificate/RenewForm','admin\SgrsaCertificateController@RenewForm')->name('sgrsa-certificate.RenewForm');
	Route::get('/sgrsa-allot/addform','admin\SgrsaAllotmentController@addForm')->name('sgrsa-allot.addform');	
	Route::post('/sgrsa-allot/allotData','admin\SgrsaAllotmentController@allotData')->name('sgrsa-allot.allotData');
	Route::get('/sgrsa-allot/RecallList','admin\SgrsaAllotmentController@index')->name('sgrsa-allot.RecallList');
	Route::get('/supplier-allot/addform','admin\SgrsaSupplierAllotmentController@addForm')->name('supplier-allot.addform');	
	Route::post('/supplier-allot/allotData','admin\SgrsaSupplierAllotmentController@allotData')->name('supplier-allot.allotData');
	Route::get('/supplier-allot/RecallList','admin\SgrsaSupplierAllotmentController@index')->name('supplier-allot.RecallList'); 
	Route::post('/supplier-allot/ApproveRejectRecord','admin\SgrsaSupplierAllotmentController@ApproveRejectRecord')->name('supplier-allot.ApproveRejectRecord');
	Route::post('/supplier-viewallotments/ViewAllotments','admin\SgrsaSupplierAllotmentController@ViewAllotments')->name('supplier-viewallotments.ViewAllotments');
	Route::get('/agent-allot/RecallList','admin\SgrsaAgentAllotmentController@index')->name('agent-allot.RecallList'); 
	Route::get('/allot-edit/editform','admin\SgrsaSupplierAllotmentController@editform')->name('allot-edit.editform'); 
	Route::get('/allot-edit/RecallList','admin\SgrsaSupplierAllotmentController@AgentAllotments')->name('allot-edit.RecallList');
	Route::post('/allot-edit/editData','admin\SgrsaSupplierAllotmentController@editData')->name('allot-edit.editData');
	
	
	Route::get('/supplier-uploadexcel/uploadExcelForm','admin\SgrsaPreviousCertificateController@uploadExcelForm')->name('supplier-uploadexcel.uploadExcelForm'); 
	Route::get('/supplier-uploadexcel/RecallList','admin\SgrsaPreviousCertificateController@index')->name('supplier-uploadexcel.RecallList');
	Route::post('/supplier-uploadexcel/saveExcelData','admin\SgrsaPreviousCertificateController@saveExcelData')->name('supplier-uploadexcel.saveExcelData');
	Route::post('/supplier-uploadexcel/viewRecord','admin\SgrsaPreviousCertificateController@viewRecord')->name('supplier-uploadexcel.viewRecord');
	Route::post('/supplier-uploadexcel/recallData','admin\SgrsaPreviousCertificateController@recallData')->name('supplier-uploadexcel.recallData');
	Route::get('/supplier-uploadexcel/indexPC','admin\SgrsaPreviousCertificateController@indexPC')->name('sgrsa-previouscertificate.indexPC');	
	Route::get('/supplier-uploadexcel/addform','admin\SgrsaPreviousCertificateController@addform')->name('sgrsa-previouscertificate.addform');	
	Route::get('/supplier-uploadexcel/searchVehicleNo','admin\SgrsaPreviousCertificateController@searchVehicleNo')->name('sgrsa-previouscertificate.searchVehicleNo');	
	//PJLCP
	Route::get('/pjlcp-certificate/uploadpage','admin\PjlcpCertificateController@uploadpage')->name('pjlcp-certificate.uploadpage');
	Route::post('/pjlcp-certificate/validateExcel','admin\PjlcpCertificateController@validateExcel')->name('pjlcp-certificate.validateexcel');
	Route::post('/pjlcp-certificate/uploadfile','admin\PjlcpCertificateController@uploadfile')->name('pjlcp-certificate.uploadfile');
	Route::get('/pjlcp-certificate/pdfGenerate','admin\PjlcpCertificateController@pdfGenerate');
	//SDM
	Route::get('/sdm-certificate/uploadpage','admin\SdmCertificateController@uploadpage')->name('sdm-certificate.uploadpage');
	Route::post('/sdm-certificate/validateExcel','admin\SdmCertificateController@validateExcel')->name('sdm-certificate.validateexcel');
	Route::post('/sdm-certificate/uploadfile','admin\SdmCertificateController@uploadfile')->name('sdm-certificate.uploadfile');
	Route::get('/sdm-certificate/pdfGenerate','admin\SdmCertificateController@pdfGenerate');

	// SDM RESIT
	Route::get('/sdm-resit/uploadpage','admin\SdmResitController@uploadpage')->name('sdm-resit.uploadpage');
	Route::post('/sdm-resit/validateExcel','admin\SdmResitController@validateExcel')->name('sdm-resit.validateexcel');
	Route::post('/sdm-resit/uploadfile','admin\SdmResitController@uploadfile')->name('sdm-resit.uploadfile');
	Route::get('/sdm-resit/pdfGenerate','admin\SdmResitController@pdfGenerate');

	
	//ANU Certificate 
	Route::get('/anu-certificate/uploadpage','admin\AnuCertificateController@uploadpage')->name('anu-certificate.uploadpage');
	Route::post('/anu-certificate/validateExcel','admin\AnuCertificateController@validateExcel')->name('anu-certificate.validateexcel');
	Route::post('/anu-certificate/uploadfile','admin\AnuCertificateController@uploadfile')->name('anu-certificate.uploadfile');
	Route::get('/anu-certificate/pdfGenerate','admin\AnuCertificateController@pdfGenerate');
	
		//sjcit Certificate 
	Route::get('/sjcit-certificate/uploadpage','admin\SjcitCertificateController@uploadpage')->name('sjcit-certificate.uploadpage');
	Route::post('/sjcit-certificate/validateExcel','admin\SjcitCertificateController@validateExcel')->name('sjcit-certificate.validateexcel');
	Route::post('/sjcit-certificate/uploadfile','admin\SjcitCertificateController@uploadfile')->name('sjcit-certificate.uploadfile');
	Route::get('/sjcit-certificate/pdfGenerate','admin\SjcitCertificateController@pdfGenerate');

	//SURANA
	Route::get('/surana-certificate/uploadpage','admin\SuranaCertificateController@uploadpage')->name('surana-certificate.uploadpage');
	Route::post('/surana-certificate/validateExcel','admin\SuranaCertificateController@validateExcel')->name('surana-certificate.validateexcel');
	Route::post('/surana-certificate/uploadfile','admin\SuranaCertificateController@uploadfile')->name('surana-certificate.uploadfile');
	Route::get('/surana-certificate/pdfGenerate','admin\SuranaCertificateController@pdfGenerate');
	
	//SANGAM
	Route::get('/sangamui-certificate/uploadpage','admin\SangamuniCertificateController@uploadpage')->name('sangamui-certificate.uploadpage');
	Route::post('/sangamui-certificate/validateExcel','admin\SangamuniCertificateController@validateExcel')->name('sangamui-certificate.validateexcel');
	Route::post('/sangamui-certificate/uploadfile','admin\SangamuniCertificateController@uploadfile')->name('sangamui-certificate.uploadfile');
	Route::get('/sangamui-certificate/pdfGenerate','admin\SangamuniCertificateController@pdfGenerate');
	
	//IMT
	Route::get('/imt-certificate/uploadpage','admin\ImtCertificateController@uploadpage')->name('imt-certificate.uploadpage');
	Route::post('/imt-certificate/validateExcel','admin\ImtCertificateController@validateExcel')->name('imt-certificate.validateexcel');
	Route::post('/imt-certificate/uploadfile','admin\ImtCertificateController@uploadfile')->name('imt-certificate.uploadfile');
	Route::get('/imt-certificate/pdfGenerate','admin\ImtCertificateController@pdfGenerate');
	
	//bnmit
	Route::get('/bnm-certificate/uploadpage','admin\BNMCertificateController@uploadpage')->name('bnm-certificate.uploadpage');
	Route::post('/bnm-certificate/validateExcel','admin\BNMCertificateController@validateExcel')->name('bnm-certificate.validateexcel');
	Route::post('/bnm-certificate/uploadfile','admin\BNMCertificateController@uploadfile')->name('bnm-certificate.uploadfile');
	Route::get('/bnm-certificate/pdfGenerate','admin\BNMCertificateController@pdfGenerate');	

	//UNEB
	Route::get('/uneb-certificate/uploadpage','admin\UNEBCertificateController@uploadpage')->name('uneb-certificate.uploadpage');
	Route::post('/uneb-certificate/validateExcel','admin\UNEBCertificateController@validateExcel')->name('uneb-certificate.validateexcel');
	Route::post('/uneb-certificate/uploadfile','admin\UNEBCertificateController@uploadfile')->name('uneb-certificate.uploadfile');
	Route::get('/uneb-certificate/pdfGenerate','admin\UNEBCertificateController@pdfGenerate');	

	//Saiu Certificate 
	Route::get('/saiu-certificate/uploadpage','admin\SaiuCertificateController@uploadpage')->name('saiu-certificate.uploadpage');
	Route::post('/saiu-certificate/validateExcel','admin\SaiuCertificateController@validateExcel')->name('saiu-certificate.validateexcel');
	Route::post('/saiu-certificate/uploadfile','admin\SaiuCertificateController@uploadfile')->name('saiu-certificate.uploadfile');
	Route::get('/saiu-certificate/pdfGenerate','admin\SaiuCertificateController@pdfGenerate');

	//Bihar eStamp Certificate 
	Route::get('/bihar-estamp-certificate/uploadpage','admin\BiharEstampCertificateController@uploadpage')->name('bihar-estamp-certificate.uploadpage');
	Route::post('/bihar-estamp-certificate/validateExcel','admin\BiharEstampCertificateController@validateExcel')->name('bihar-estamp-certificate.validateexcel');
	Route::post('/bihar-estamp-certificate/uploadfile','admin\BiharEstampCertificateController@uploadfile')->name('bihar-estamp-certificate.uploadfile');
	Route::get('/bihar-estamp-certificate/pdfGenerate','admin\BiharEstampCertificateController@pdfGenerate')->name('bihar-estamp-certificate.pdfGenerate');

	Route::get('/bihar-estamp-certificate/uploadpagePrinter','admin\BiharEstampCertificateController@uploadpagePrinter')->name('bihar-estamp-certificate.uploadpageprinter');
	Route::post('/bihar-estamp-certificate/validateExcelPrinter','admin\BiharEstampCertificateController@validateExcelPrinter')->name('bihar-estamp-certificate.validateexcelprinter');
	Route::post('/bihar-estamp-certificate/uploadfilePrinter','admin\BiharEstampCertificateController@uploadfilePrinter')->name('bihar-estamp-certificate.uploadfileprinter');

	Route::get('/bihar-estamp-certificate/uploadpageUtility','admin\BiharEstampCertificateController@uploadpageUtility')->name('bihar-estamp-certificate.uploadpageutility');
	Route::post('/bihar-estamp-certificate/validateExcelutility','admin\BiharEstampCertificateController@validateExcelUtility')->name('bihar-estamp-certificate.validateexcelutility');
	Route::post('/bihar-estamp-certificate/uploadfileUtility','admin\BiharEstampCertificateController@uploadfileUtility')->name('bihar-estamp-certificate.uploadfileutility'); 
	Route::get('/bihar-estamp-certificate/uploadpagePrinterUtility','admin\BiharEstampCertificateController@uploadpagePrinterUtility')->name('bihar-estamp-certificate.uploadpageprinterutility');

	Route::get('/bihar-estamp-certificate/uploadpageApiUtility','admin\BiharEstampCertificateController@uploadpageApiUtility')->name('bihar-estamp-certificate.uploadpageapiutility');
	
	//Demo ERP
	Route::resource('demo-erp','admin\DemoErpController',['except'=>'show']);
	Route::post('/demo-erp/generate','admin\DemoErpController@generate')->name('demo-erp.generate');
	Route::post('/demo-erp/apicall','admin\DemoErpController@apiCall')->name('demo-erp.apicall');

	Route::get('/certificate/testing','admin\TestCertificateController@insertData');


	Route::get('/admin/old-verification/testmail','admin\raisoni\OldDocumentsController@testMail')->name('oldverification.testmail');



	//LNCT Bhopal Certificate 
	Route::get('/lnct-certificate/uploadpage','admin\LNCTCertificateController@uploadpage')->name('lnct-certificate.uploadpage');
	Route::post('/lnct-certificate/validateExcel','admin\LNCTCertificateController@validateExcel')->name('lnct-certificate.validateexcel');
	Route::post('/lnct-certificate/uploadfile','admin\LNCTCertificateController@uploadfile')->name('lnct-certificate.uploadfile');
	Route::get('/lnct-certificate/pdfGenerate','admin\LNCTCertificateController@pdfGenerate');
	


	//Mitwpu Certificate 
	Route::get('/mitwpu-certificate/uploadpage','admin\MitwpuCertificateController@uploadpage')->name('mitwpu-certificate.uploadpage');
	Route::post('/mitwpu-certificate/validateExcel','admin\MitwpuCertificateController@validateExcel')->name('mitwpu-certificate.validateexcel');
	Route::post('/mitwpu-certificate/uploadfile','admin\MitwpuCertificateController@uploadfile')->name('mitwpu-certificate.uploadfile');
	Route::get('/mitwpu-certificate/pdfGenerate','admin\MitwpuCertificateController@pdfGenerate');
	
		

	Route::get('/chanakya-certificate/uploadpage','admin\ChanakyaertificateController@uploadpage')->name('chanakya-certificate.uploadpage');
	Route::post('/chanakya-certificate/validateExcel','admin\ChanakyaertificateController@validateExcel')->name('chanakya-certificate.validateexcel');
	Route::post('/chanakya-certificate/uploadfile','admin\ChanakyaertificateController@uploadfile')->name('chanakya-certificate.uploadfile'); 
	Route::get('/chanakya-certificate/pdfGenerate','admin\ChanakyaertificateController@pdfGenerate');


	// Peaple University
	Route::get('/peoplesuni-certificate/uploadpage','admin\PeoplesuniCertificateController@uploadpage')->name('peoplesuni-certificate.uploadpage');
	Route::post('/peoplesuni-certificate/validateExcel','admin\PeoplesuniCertificateController@validateExcel')->name('peoplesuni-certificate.validateexcel');
	Route::post('/peoplesuni-certificate/uploadfile','admin\PeoplesuniCertificateController@uploadfile')->name('peoplesuni-certificate.uploadfile');
	Route::get('/peoplesuni-certificate/pdfGenerate','admin\PeoplesuniCertificateController@pdfGenerate');


	//DSRRAU Rajsthan
	Route::get('/dsrrau-certificate-415/pdfSample','admin\DSRRAU_Rajsthan415CertificateController@pdfSample');
	Route::get('/dsrrau-certificate-486/pdfSample','admin\DSRRAU_Rajsthan486CertificateController@pdfSample');
	Route::get('/dsrrau-certificate-487/pdfSample','admin\DSRRAU_Rajsthan487CertificateController@pdfSample');
	Route::get('/dsrrau-certificate-488/pdfSample','admin\DSRRAU_Rajsthan488CertificateController@pdfSample');
	Route::get('/dsrrau-certificate-489/pdfSample','admin\DSRRAU_Rajsthan489CertificateController@pdfSample');
	Route::get('/dsrrau-certificate-795/pdfSample','admin\DSRRAU_Rajsthan795CertificateController@pdfSample');
	


	//KSG
	Route::get('/ksg-batch/uploadpage','admin\KsgBatchController@uploadpage')->name('ksg-batch.uploadpage');
	Route::post('/ksg-batch/uploadpage','admin\KsgBatchController@index')->name('ksg-batch.datatable');
	Route::post('/add-batch','admin\KsgBatchController@store')->name('add-batch');
	Route::post('/update-batch','admin\KsgBatchController@update')->name('update-batch');
	Route::get('/edit-batch/{id}','admin\KsgBatchController@edit')->name('edit-batch');
	Route::get('/delete-batch/{id}','admin\KsgBatchController@destroy')->name('delete-batch');
	Route::get('/get-batch','admin\KsgBatchController@getBatch')->name('get-batch');
	Route::post('/update-batch-status','admin\KsgBatchController@updatestatus')->name('update-batch-status');
	Route::post('/ksg/uploadfile','admin\KsgBatchRecordController@uploadfile')->name('ksg.uploadfile');
	// Route::get('/records/view/{id}/{flag}/{breadcrums}','admin\KsgBatchRecordController@view')->name('view-records');
	Route::get('/records/view/{id}/{flag}/{breadcrums}', 'admin\KsgBatchRecordController@view')->name('view-records');

	Route::post('/ksg-batch-records/uploadpage','admin\KsgBatchRecordController@index')->name('ksg-batch-records.datatable');
	Route::post('/add-record','admin\KsgBatchRecordController@store')->name('add-record');
	Route::post('/update-record','admin\KsgBatchRecordController@update')->name('update-record');
	Route::get('/edit-record/{id}','admin\KsgBatchRecordController@edit')->name('edit-record');
	Route::get('/delete-record/{id}','admin\KsgBatchRecordController@destroy')->name('delete-record');
	Route::post('/update-record-status','admin\KsgBatchRecordController@updatestatus')->name('update-record-status');

	Route::get('/ksg-approval/page','admin\KsgBatchController@viewpage')->name('ksg-approval.page');
	Route::post('/ksg-approved-batch/uploadpage','admin\KsgBatchController@getApprovalBatchData')->name('ksg-approved-batch.datatable');
	Route::post('/PrintBatchRecord','admin\KsgBatchRecordController@BatchRecordToPrint')->name('PrintBatchRecord');
	Route::post('/loderfile/{flag}','admin\KsgBatchRecordController@loderfile')->name('loderfile');
	// Route::get('/Batch', 'admin\KsgBatchController@uploadpage')->name('ksg-batch.uploadpage');
	Route::post('/approve-selected','admin\KsgBatchRecordController@approveSelected')->name('approve-selected');

	Route::get('/ksg-branch/uploadpage','admin\KsgBranchController@uploadpage')->name('ksg-branch.uploadpage');
	Route::post('/add-branch','admin\KsgBranchController@store')->name('add-branch');
	Route::post('/update-branch','admin\KsgBranchController@update')->name('update-branch');
	Route::get('/edit-branch/{id}','admin\KsgBranchController@edit')->name('edit-branch');
	Route::post('/ksg-branch/uploadpage','admin\KsgBranchController@index')->name('ksg-branch.datatable');
	Route::get('/delete-branch/{id}','admin\KsgBranchController@destroy')->name('delete-branch');
	Route::get('/get-branch/{id}','admin\KsgBranchController@getBranch')->name('get-branch');
	Route::post('/assign-branch','admin\KsgBranchController@assignBranch')->name('assign-branch');
	Route::get('/export-records/{id}','admin\KsgBatchRecordController@exportRecords')->name('export.records');
	Route::get('/download-document','admin\KsgBatchController@downloadDocument')->name('download-document');

	Route::get('/ksg-print/page','admin\KsgPrintController@viewpage')->name('ksg-print.page');
	Route::post('/ksg-print','admin\KsgPrintController@index')->name('ksg-printView');
	Route::get('/ksg-home', 'admin\KsgBatchController@home')->name('ksg-home');
	Route::post('/send-otp', 'admin\KsgBatchController@sendOtp')->name('send.otp');


	// Ksg Custome
	Route::get('/ksg-certificate/uploadpage','admin\KsgCustomController@uploadpage')->name('ksg-certificate.uploadpage');
	Route::post('/ksg-certificate/validateExcel','admin\KsgCustomController@validateExcel')->name('ksg-certificate.validateexcel');
	Route::post('/ksg-certificate/uploadfile','admin\KsgCustomController@uploadfile')->name('ksg-certificate.uploadfile');
	Route::get('/ksg-certificate/pdfGenerate','admin\KsgCustomController@pdfGenerate');

	
	//JNTU Anantapur College
	Route::get('/jntu-certificate/uploadpage','admin\JNTUCertificateController@uploadpage')->name('jntu-certificate.uploadpage');
	Route::post('/jntu-certificate/validateExcel','admin\JNTUCertificateController@validateExcel')->name('jntu-certificate.validateexcel');
	Route::post('/jntu-certificate/uploadfile','admin\JNTUCertificateController@uploadfile')->name('jntu-certificate.uploadfile');
	Route::get('/jntu-certificate/pdfGenerate','admin\AnuCertificateController@pdfGenerate');
	Route::get('admin/unikbp/mintdata','admin\UNIKBPCertificateController@mintData')->name('unikbp.mintdata');
	Route::get('admin/unikbp/add-record','admin\UNIKBPCertificateController@addRecord');
	Route::get('admin/unikbp/mintDataVerified','admin\UNIKBPCertificateController@mintDataVerified');


	Route::get('admin/unikbp/mintDataV1','admin\UNIKBPCertificateController@mintDataV1')->name('unikbp.mintDataV1');


	//word to pdf
	Route::get('/wordtopdf/uploadpage', [WordToPdfController::class, 'uploadpage'])->name('wordtopdf.uploadpage');
	Route::post('/wordtopdf/uploadfile', [WordToPdfController::class, 'uploadfile'])->name('wordtopdf.uploadfile');


	Route::get('/wordtopdf/download/{file}', [WordToPdfController::class, 'download'])->name('wordtopdf.download');
	
		


        //KDKN 
	Route::get('/kdkn-certificate/uploadpage','admin\KDKNCertificateController@uploadpage')->name('kdkn-certificate.uploadpage');
	Route::post('/kdkn-certificate/validateExcel','admin\KDKNCertificateController@validateExcel')->name('kdkn-certificate.validateexcel');
	Route::post('/kdkn-certificate/uploadfile','admin\KDKNCertificateController@uploadfile')->name('kdkn-certificate.uploadfile');
	Route::get('/kdkn-certificate/pdfGenerate','admin\AnuCertificateController@pdfGenerate');

        //scube

        Route::get('/scube-certificate/uploadpage','admin\ScubeCertificateController@uploadpage')->name('scube-certificate.uploadpage');
	Route::post('/scube-certificate/validateExcel','admin\ScubeCertificateController@validateExcel')->name('scube-certificate.validateexcel');
	Route::post('/scube-certificate/uploadfile','admin\ScubeCertificateController@uploadfile')->name('scube-certificate.uploadfile');
	Route::get('/scube-certificate/pdfGenerate','admin\AnuCertificateController@pdfGenerate');



	//ghrusaikheda//
Route::get('/ghrusaikheda-certificate/uploadpage','admin\GhrusaikhedaCertificateController@uploadpage')->name('ghrusaikheda-certificate.uploadpage');
	Route::post('/ghrusaikheda-certificate/validateExcel','admin\GhrusaikhedaCertificateController@validateExcel')->name('ghrusaikheda-certificate.validateexcel');
	Route::post('/ghrusaikheda-certificate/uploadfile','admin\GhrusaikhedaCertificateController@uploadfile')->name('ghrusaikheda-certificate.uploadfile');
	Route::get('/ghrusaikheda-certificate/pdfGenerate','admin\AnuCertificateController@pdfGenerate');


	// Aiims Nagpur Certitificate
	Route::get('/aiims-certificate/uploadpage','admin\AiimsCertificateController@uploadpage')->name('aiims-certificate.uploadpage');
	Route::post('/aiims-certificate/validateExcel','admin\AiimsCertificateController@validateExcel')->name('aiims-certificate.validateexcel');
	Route::post('/aiims-certificate/uploadfile','admin\AiimsCertificateController@uploadfile')->name('aiims-certificate.uploadfile'); 
	Route::get('/aiims-certificate/pdfGenerate','admin\AiimsCertificateController@pdfGenerate');
	Route::get('/aiims-certificate/mintdata','admin\AiimsCertificateController@mintData');
	
	

	// Malla Reddy Certitificate
	Route::get('/mallareddy-certificate/uploadpage','admin\MallareddyCertificateController@uploadpage')->name('mallareddy-certificate.uploadpage');
	Route::post('/mallareddy-certificate/validateExcel','admin\MallareddyCertificateController@validateExcel')->name('mallareddy-certificate.validateexcel');
	Route::post('/mallareddy-certificate/uploadfile','admin\MallareddyCertificateController@uploadfile')->name('mallareddy-certificate.uploadfile'); 
	

		// Cambridge Reddy Certitificate
	Route::get('/cambridge-certificate/uploadpage','admin\CambridgeCertificateController@uploadpage')->name('cambridge-certificate.uploadpage');
	Route::post('/cambridge-certificate/validateExcel','admin\CambridgeCertificateController@validateExcel')->name('cambridge-certificate.validateexcel');
	Route::post('/cambridge-certificate/uploadfile','admin\CambridgeCertificateController@uploadfile')->name('cambridge-certificate.uploadfile'); 
	
	//SURYODAYA COLLEGE
	Route::get('/suryodaya-certificate/uploadpage','admin\SuryodayaCertificateController@uploadpage')->name('suryodaya-certificate.uploadpage');
	Route::post('/suryodaya-certificate/validateExcel','admin\SuryodayaCertificateController@validateExcel')->name('suryodaya-certificate.validateExcel');
	Route::post('/suryodaya-certificate/uploadfile','admin\SuryodayaCertificateController@uploadfile')->name('suryodaya-certificate.uploadfile'); 
	Route::get('/suryodaya-certificate/pdfGenerate','admin\SuryodayaCertificateController@pdfGenerate');
	


	
	Route::get('/admin/getTextVerification','admin\pdf2pdf\TemplateDataController@getTextVerification')->name('getTextVerification');
	Route::post('/admin/saveTextVerification','admin\pdf2pdf\TemplateDataController@saveTextVerification')->name('saveTextVerification');
	
	//mvsr Certificate 
	Route::get('/mvsr-certificate/uploadpage','admin\MvsrCertificateController@uploadpage')->name('mvsr-certificate.uploadpage');
	Route::post('/mvsr-certificate/validateExcel','admin\MvsrCertificateController@validateExcel')->name('mvsr-certificate.validateexcel');
	Route::post('/mvsr-certificate/uploadfile','admin\MvsrCertificateController@uploadfile')->name('mvsr-certificate.uploadfile');
	Route::get('/mvsr-certificate/pdfGenerate','admin\MvsrCertificateController@pdfGenerate');


	//atriau
	Route::get('/atriau-certificate/uploadpage','admin\AtriauCertificateController@uploadpage')->name('atriau-certificate.uploadpage');
	Route::post('/atriau-certificate/validateExcel','admin\AtriauCertificateController@validateExcel')->name('atriau-certificate.validateexcel');
	Route::post('/atriau-certificate/uploadfile','admin\AtriauCertificateController@uploadfile')->name('atriau-certificate.uploadfile');
	Route::get('/atriau-certificate/pdfGenerate','admin\AtriauCertificateController@pdfGenerate');

	//East Point Certificate
	Route::get('/eastpoint-certificate/uploadpage','admin\EastPointCertificateController@uploadpage')->name('eastpoint-certificate.uploadpage');
	Route::post('/eastpoint-certificate/validateExcel','admin\EastPointCertificateController@validateExcel')->name('eastpoint-certificate.validateexcel');
	Route::post('/eastpoint-certificate/uploadfile','admin\EastPointCertificateController@uploadfile')->name('eastpoint-certificate.uploadfile'); 
	Route::get('/eastpoint-certificate/pdfGenerate','admin\EastPointCertificateController@pdfGenerate');
	
	// tpsdi template
	Route::get('/tpsdi-certificate/uploadpage','admin\TPSDICertificateController@uploadpage')->name('tpsdi-certificate.uploadpage');
	Route::post('/tpsdi-certificate/validateExcel','admin\TPSDICertificateController@validateExcel')->name('tpsdi-certificate.validateexcel');
	Route::post('/tpsdi-certificate/uploadfile','admin\TPSDICertificateController@uploadfile')->name('tpsdi-certificate.uploadfile');

	//CSCACS Certificate 
	Route::get('/cscacs-certificate/uploadpage','admin\CSCACSCertificateController@uploadpage')->name('cscacs-certificate.uploadpage');
	Route::post('/cscacs-certificate/validateExcel','admin\CSCACSCertificateController@validateExcel')->name('cscacs-certificate.validateexcel');
	Route::post('/cscacs-certificate/uploadfile','admin\CSCACSCertificateController@uploadfile')->name('cscacs-certificate.uploadfile');
	Route::get('/cscacs-certificate/pdfGenerate','admin\CSCACSCertificateController@pdfGenerate');

	//Ghrstu Certificate 
	Route::get('/ghrstu-certificate/uploadpage','admin\GhrstuCertificateController@uploadpage')->name('ghrstu-certificate.uploadpage');
	Route::post('/ghrstu-certificate/validateExcel','admin\GhrstuCertificateController@validateExcel')->name('ghrstu-certificate.validateexcel');
	Route::post('/ghrstu-certificate/uploadfile','admin\GhrstuCertificateController@uploadfile')->name('ghrstu-certificate.uploadfile');
//nnp//


		Route::get('/nnp-certificate/uploadpage', 'admin\NnpCertificateController@uploadpage')->name('nnp-certificate.uploadpage');
	Route::post('/nnp-certificate/validateExcel', 'admin\NnpCertificateController@validateExcel')->name('nnp-certificate.validateexcel');
	Route::post('/nnp-certificate/uploadfile', 'admin\NnpCertificateController@uploadfile')->name('nnp-certificate.uploadfile');
	Route::get('/nnp-certificate/pdfGenerate', 'admin\NnpCertificateController@pdfGenerate');



	Route::get('/payment', [PaymentControllernew::class, 'index']);
	Route::post('/payment/request', [PaymentControllernew::class, 'paymentRequest'])->name('payment.request');
	Route::post('/payment/response', [PaymentControllernew::class, 'paymentResponse'])->name('payment.response');
	Route::get('/payment/cancel', [PaymentControllernew::class, 'paymentCancel'])->name('payment.cancel');
	
	Route::group(['middleware'=>['admin.check','acl.permitted']], 
		function(){


		Route::get('datamapping','admin\FunctionalUsersController@mapOldData')->name('functionalusers.datamapping');
		Route::post('postdatamapping','admin\FunctionalUsersController@mapOldData')->name('functionalusers.postdatamapping');
		Route::get('get-templates/{doc_type}', 'admin\FunctionalUsersController@getTemplates')->name('functionalusers.getTemplates');
		Route::get('idcard-report','admin\FunctionalUsersController@idCardReport')->name('functionalusers.idcard_report');
		Route::get('cards-listing','admin\FunctionalUsersController@cards_listing')->name('functionalusers.cards_listing');
		Route::get('functionalusers','admin\FunctionalUsersController@index')->name('functionalusers.index');
		Route::post('functionalusers/{id}','admin\FunctionalUsersController@update')->name('functionalusers.update');
		Route::get('functionalusers/{id}','admin\FunctionalUsersController@edit')->name('functionalusers.edit');
		Route::post('functionalusers-upload','admin\FunctionalUsersController@fileUpload')->name('functionalusers.upload'); 
		Route::get('export-functional-user-login', 'admin\FunctionalUsersController@exportLoggedInFunctionalUserHistory')->name('functionalusers.exportLoginHistory');
			
		Route::post('functional-users-expiring-cards','admin\FunctionalUsersController@expiringCards')->name('functionalusers.expiringcards');
		Route::post('functional-users-created-cards','admin\FunctionalUsersController@createdCardReport')->name('functionalusers.createdCardReport');
		Route::post('card-cycle-time-report','admin\FunctionalUsersController@cardCycleTimeReport')->name('functionalusers.cardCycleTimeReport');
		Route::post('functional-users-created-certificate','admin\FunctionalUsersController@certificateCreateReport')->name('functionalusers.certificateCreateReport');
	});	
	
});





