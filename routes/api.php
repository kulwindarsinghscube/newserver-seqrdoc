<?php

use Illuminate\Http\Request;
use App\Http\Controllers\api\MachakosController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/



Route::middleware('auth:api')->get('/user', function (Request $request) {
   
});

Route::post('test','api\TestController@test1');



Route::post('fetch-statement', 'api\StatementController@fetchStatement');

Route::post('desktop/instances-list', 'api\DesktopController@fetchInstancedetail');
Route::post('desktop/instance-login', 'api\DesktopController@instanceLogin');

Route::post('callPdfData', 'VerifyV1Controller@callPdfData');

Route::group(['middleware'=>'domain.check'], function(){

	Route::post('scanV2', 'api\PaymentScanController@scan');
	
	Route::get('pdf/{token}/{type}/{subtype}', 'api\PdfController@showPdf');//type : 1 -> Indivisual 2: printable //subtype : 1 Non-Sandbox, 2:Sandbox, 3:preview
	Route::post('verifyPdf', 'api\Blockchain\PdfController@verifyPdf');

	Route::post('callPdfDataV1', 'api\Blockchain\PdfController@callPdfDataV1');
	// test route for bverify
	Route::post('verifyV1Pdf', 'VerifyV1Controller@verifyPdf');
	Route::post('callPdfData', 'VerifyV1Controller@callPdfData');

	// rohit Update webapp
	Route::post('passwordReset','api\PasswordResetController@passwordReset');
	// rohit Update webapp
	Route::post('get-profile', 'api\GetProfileController@GetProfileData');
	Route::post('user-login', 'api\UserLoginController@login');
	Route::post('webapp-login', 'api\UserLoginController@webAppLogin');
	Route::post('user-register', 'api\RegisterController@userRegister');
	Route::post('institute-login', 'api\InstituteController@get_login');
	Route::post('institute-verify-doc', 'api\InstituteController@instituteVerifyDoc');//institute doc verify
	Route::post('student-login', 'api\StudentController@login');
	Route::post('cedp/student-login', 'api\StudentController@login');
	Route::post('mobile-no-verify', 'api\RegisterController@mobileNoVerify');
	Route::post('resend-otp', 'api\RegisterController@resendOtp');
	Route::post('delete-user', 'api\UserLoginController@deleteUser');
	Route::post('fetchdetail','api\FetchdetailsController@fetchdetail');
	Route::post('delete-user-acc', 'api\DeleteAccController@deleteUserAccount');
	/*************************** APP Only API ***********************************************/
	Route::post('app-only-scan-iim', 'apponly\AppOnlyController@getIIMCertificates');
	Route::post('app-only-scan-isbs', 'apponly\AppOnlyController@getISBSCertificates');
	Route::post('molwa-document', 'apponly\AppOnlyController@getMolwaDocuments');
	Route::post('biharestamp-document', 'apponly\AppOnlyController@getBiharEstampDocuments');
	/*************************** END APP Only API ***********************************************/

	/*************************** EStamp API ***********************************************/	
	Route::post('generate-estamp','api\EStampController@generateEstamp');
	Route::post('generate-ecourt', 'api\EStampController@generateEcourt');
	Route::post('generate-estamp-test','api\EStampTestController@generateEstamp');//Test
	Route::post('generate-ecourt-test', 'api\EStampTestController@generateEcourt');//Test
	Route::post('estampScan', 'api\ScanController@estampScan');
	Route::post('generate-court-fee', 'api\EStampController@generateCourtFee');
	Route::post('generate-court-fee-test', 'api\EStampTestController@generateCourtFee');
	Route::post('storefile-to-local', 'api\EStampController@storeFileToLocal');
	Route::post('fileDelete', 'api\EStampController@fileDelete');
	/***************************END EStamp API ***********************************************/	
	Route::post('VerifyDocument', 'api\CouncilController@VerifyDocument');//council scan
    Route::post('sendMail', 'api\CouncilController@sendMail');//council send email
	 
	Route::group(['middleware' => 'APIToken'], function(){


		// nidan api start

		Route::post('nidan/scan-certificate', 'api\ScanController@nidanScanCertificate'); // 2.0 app intitute scan certificate for all intsances uses.
        Route::post('nidan/proscan-certificate', 'api\ScanController_proBg@nidanScanCertificate');
        Route::post('proscan', 'api\ScanController_proBg@scan');
		//Institute login nidan/scan-certificate
		// nidan api completed

		//verify api start

		// Route::group(['prefix'=>'verify','namespace'=>'verify'],function(){

			Route::post('/verify/document-prices','api\verify\DocumentPricesController@index');
			
			Route::post('/verify/user-profile','api\verify\UserProfileController@user_profile');
			Route::post('/verify/delete','api\verify\RegistrationController@delete');
			Route::post('/verify/change-password','api\verify\ChangePasswordController@ChangePassword');
			Route::post('/verify/request-verification','api\verify\RequestVerificationController@RequestVerification');
			//khushi
			Route::post('/verify/request-verification-galgotias','api\verify\RequestVerificationController@RequestVerification_galgotias');
			//khushi
			Route::post('/verify/scan-model','api\verify\ScanModelController@ScanModel');

			//Monad Request
			Route::post('/verify/monad/scan-model','api\verify\ScanModelMonadController@ScanModel');
			
		// });

		//verify api end

		Route::post('student-data', 'api\StudentController@get_data');
		Route::post('all-student-data', 'api\StudentController@get_all_student');
		
		Route::post('student-certificate', 'api\StudentController@studentCertificate');
		Route::post('cedp/student-certificate', 'api\StudentController@studentCertificateCedp');

		Route::post('payment-getway', 'api\PaymentGatewayConfigController@get_paymentgateway');

		Route::post('scan', 'api\ScanController@scan');//student scan
		Route::post('verify-doc', 'api\ScanController@verify_doc');//student verify doc
		Route::post('scanCEDP', 'api\ScanController@scanCEDP');
		Route::post('scan-certificate', 'api\ScanController@scanViewCertificate'); // institute
		// Route::post('nidan/scan-certificate', 'api\ScanController@NidanScanViewCertificate');
		Route::post('scan-certificate-iitjammu-institute', 'api\ScanController@scanViewCertificateiitjammuinstitute');
		Route::post('scan-iitjammu-verifier', 'api\ScanController@scaniitjammuverifier');
		Route::post('scan-audit-trail', 'api\ScanController@scanViewAuditTrail'); //institute barcode scan
		Route::post('scan-history','api\ScanController@scanHistory');

		Route::post('jssaher/scan-answer-book','api\AnswerBookletController@scan');


		Route::post('seqr-print-login', 'api\SecureDocumentController@printLogin');

		Route::post('transaction', 'api\TransactionController@transaction');

		Route::post('login-verify', 'api\UserLoginController@loginVerify');
		Route::post('logout', 'api\UserLoginController@logout');
		Route::post('cedp/student-logout', 'api\StudentController@cedpStudentLogout');

		Route::post('all-templates','api\threeApi\AdminLoginController@getAllTemplate');
		Route::post('generate-seqrdocs','api\threeApi\AdminLoginController@generateSeqrocs');
		Route::post('admin-logout','api\threeApi\AdminLoginController@logout');

		Route::post('instamojoPayment','api\PaymentController@instamojoPayment');
		/*************************** Third Party API ***********************************************/		
		Route::post('seqrdoc-template-list','api\SeqrdocThirdPartyController@getTemplatesList');
		Route::post('seqrdoc-template-columns','api\SeqrdocThirdPartyController@getTemplatesColumns');
		Route::post('seqrdoc-generate','api\SeqrdocThirdPartyController@generateDocuments');
		Route::post('seqrdoc-process-docs','api\SeqrdocThirdPartyController@processDocuments');
		Route::post('seqrdoc-process-docs-custom','api\SeqrdocThirdPartyController@processDocumentsCustomTemplates');
		Route::post('seqrdoc-logout', 'api\SeqrdocThirdPartyController@logout');

		//super admin start
		Route::post('cards-pdf','api\TpsdiUserLoginController@Cards_Listing_Pdf');
		Route::post('searchcards','api\TpsdiUserLoginController@searchCards');
		Route::post('searchcertificates','api\TpsdiUserLoginController@searchCertificates');
		Route::post('expiringcards','api\TpsdiUserLoginController@expiringCards');
		//super admin end

		/*************************** END Third Party API ***********************************************/
		
		
	});
	
	Route::get('instamojoResponse','api\PaymentController@instamojoResponse');
	Route::post('instamojoResponse','api\PaymentController@instamojoResponse');
	Route::post('verify/login','api\verify\LoginController@login'); // to connect respected database KHUSHI
	Route::post('verify/registration','api\verify\RegistrationController@registration'); // to connect respected database KHUSHI
	Route::post('/verify/dropdown','api\verify\DropdownController@dropdown');
	Route::post('vehicleReg', 'api\SgrsaVehicleController@RecallScanViewCertificate');
	Route::post('LrmisAddInfo', 'api\LRMIScontroller@AddInfo'); //add record into student_table
	Route::post('LrmisAddInfoTest', 'api\LRMIScontrollerTest@AddInfo'); //add record into student_table
    Route::post('FFUpdate','api\FFController@FFUpdate');
    Route::get('scan-pdf', 'api\ScanCertificateController@ScanViewCertificate');

    Route::get('/fetch-data', [MachakosController::class, 'fetchData']);
    Route::post('/upload-json', [MachakosController::class, 'uploadJson']);

    /*************************** TPSDI API START**********************************************/
	Route::middleware(['TpsdiAuth'])->group(function () {
	// Route::group(['prefix' => 'api','namespace'=>'api'], function () {
		Route::get('logout/{id}','api\TpsdiUserLoginController@logout');
		Route::get('getuserdetails/{id}','api\TpsdiUserLoginController@get_user_details');
		Route::post('listcards','api\TpsdiUserLoginController@listCards');
		Route::post('listcertificates','api\TpsdiUserLoginController@listCertificates');
		// Route::post('searchcards','api\TpsdiUserLoginController@searchCards');
		// Route::post('expiringcards','api\TpsdiUserLoginController@expiringCards');

	// });
	});
	Route::post('login','api\TpsdiUserLoginController@authenticate');
	Route::post('setpin','api\TpsdiUserLoginController@setPIN');
	Route::post('verifyOtp','api\TpsdiUserLoginController@verifyOtp');
	Route::post('checkpin','api\TpsdiUserLoginController@CheckPIN');
	Route::post('sendotp','api\TpsdiUserLoginController@sendotp');
	Route::post('expireotp','api\TpsdiUserLoginController@expireotp');
	Route::get('test', 'api\TpsdiUserLoginController@pass');
	/*************************** TPSDI API END ***********************************************/	

	
	/***************************END Third Party API ***********************************************/	
	Route::post('seqrdoc-login','api\SeqrdocThirdPartyController@login');
	Route::get('/seqrdoc-check-status/{token}','api\SeqrdocThirdPartyController@checkRequestStatus');
	Route::post('/call-back-url','api\SeqrdocThirdPartyController@callBackUrl');
	Route::post('/call-back-url-demo-erp','api\SeqrdocThirdPartyController@callBackUrlDemo');

	
	Route::post('seqrdoc-refresh-token','api\SeqrdocThirdPartyController@userRefreshToken');

	Route::post('seqrdoc-files-upload', 'api\SeqrdocThirdPartyController@uploadFiles');
	


});




Route::post('admin-login','api\threeApi\AdminLoginController@login');
//Route::post('verify/registration','api\verify\RegistrationController@registration'); old place
//Route::post('verify/login','api\verify\LoginController@login'); old place 
//Route::post('/verify/dropdown','api\verify\DropdownController@dropdown'); old place

/*************************** Third Party API ***********************************************/	
// Route::post('seqrdoc-login','api\SeqrdocThirdPartyController@login');
// Route::get('/seqrdoc-check-status/{token}','api\SeqrdocThirdPartyController@checkRequestStatus');
// Route::post('/call-back-url','api\SeqrdocThirdPartyController@callBackUrl');
// Route::post('/call-back-url-demo-erp','api\SeqrdocThirdPartyController@callBackUrlDemo');

/***************************END Third Party API ***********************************************/	



Route::post('/verify/uploadTest','api\verify\TestController@uploadTest');

