<?php 
    use Illuminate\Support\Facades\Route; 
    use App\Http\Controllers\convodataverification\ConvoStudentAuthController;
    use App\Http\Controllers\convodataverification\ConvoAdminController;
    use App\Http\Controllers\convodataverification\ConvoStudentController;
    use Illuminate\Http\Request;
    use App\Http\Controllers\convodataverification\ConvoAdminKmtcController;



Route::group(['middleware'=>'domain.check'], function(){
    Route::group(['prefix'=>'admin/convocation'],  function(){
    // Route::group(['prefix'=>'admin/convocation','middleware'=>['admin.check','acl.permitted']],  function(){
    // Group routes related to convocation
    // Route::prefix('admin/convocation')->group(function () {
        
        Route::group(['middleware'=>'admin.check'], function(){
            Route::get('/', [ConvoAdminController::class, 'index'])->name('convocation.dashboard');

            Route::post('/upload', [ConvoAdminController::class, 'uploadStudent'])->name('convocation.admin.upload_student');

            Route::post('/uploadImage', [ConvoAdminController::class, 'uploadImage'])->name('convocation.admin.upload_studentImage');

            Route::get('/get_non_verified_student_data', [ConvoAdminController::class, 'nonVerifiedStudentData'])->name('convo_student.get_non_verified_student_data');


            Route::post('/upload', [ConvoAdminController::class, 'uploadStudent'])->name('convocation.admin.upload_student');

             //kmtc

             Route::get('/convocationkmtc', [ConvoAdminKmtcController::class, 'index'])->name('convocationkmtc.dashboard');
             Route::post('/uploadkmtc', [ConvoAdminKmtcController::class, 'uploadStudent'])->name('convocationkmtc.admin.upload_student');
             Route::get('/studentkmtc_edit/{id}', [ConvoAdminKmtcController::class, 'edit'])->name('convo_studentkmtc.edit');
        
            Route::get('convo_students_datatable', [ConvoAdminController::class, 'index'])->name('convo_students.index');
            
            Route::get('/student_edit/{id}', [ConvoAdminController::class, 'edit'])->name('convo_student.edit');

            Route::post('/student_update/{id}', [ConvoAdminController::class, 'update'])->name('convo_student.update');
            
            Route::get('/export-convo-students', [ConvoAdminController::class, 'exportStudent'])->name('convo_student.export_convo_students');

            Route::get('/export-convo-students-transaction', [ConvoAdminController::class, 'exportStudentTransaction'])->name('convo_student.export_convo_students_transaction');

            Route::get('/export-custom-convo-students', [ConvoAdminController::class, 'exportCustomStudent'])->name('convo_student.export_custom_convo_students');


            Route::get('/status-counts', [ConvoAdminController::class, 'getStatusCounts'])->name('convo_student.status_counts');
            Route::get('/quillpad_Api', [ConvoAdminController::class, 'quillpad_Api'])->name('convo_student.quillpad_Api');

        });

        Route::get('/send-registration-email-to-students', function (Request $request) {
            if ($request->has('run') && $request->query('run') === 'yes') {
                $exitCode = Artisan::call('emails:send-student');
                return '<h1>Registration Emails Sent to Student Successfully</h1>';
            }
            return '<h1>Invalid or Missing Query Parameter</h1>';
        });
    
        Route::get('/send-summary-email-to-admin', function (Request $request) {
            if ($request->has('run') && $request->query('run') === 'yes') {
                $exitCode = Artisan::call('emails:send-admin');
                return '<h1>Summary Email Sent to Admin Successfully</h1>';
            }
            return '<h1>Invalid or Missing Query Parameter</h1>';
        });

        // Route::get('/process-pending-payment', function() {
        //     $exitCode = Artisan::call('payment:verify-status');
        //     return '<h1>Processed Pending Payment</h1>';
        // });
        Route::get('/process-pending-payment', function (Request $request) {
            if ($request->has('run') && $request->query('run') === 'yes') {
                $exitCode = Artisan::call('payment:verify-status');
                return '<h1>Processed Pending Payment</h1>';
            }
            return '<h1>Invalid or Missing Query Parameter</h1>';
        });

        Route::get('/send-reminder-email-to-students', function (Request $request) {
            if ($request->has('run') && $request->query('run') === 'yes') {
                $exitCode = Artisan::call('emails:send-reminder');
                return '<h1>Registration Reminder Emails Sent to Student Successfully</h1>';
            }
            return '<h1>Invalid or Missing Query Parameter</h1>';
        });
    

     Route::get('/updategeneratedPdf', [ConvoStudentController::class, 'updategeneratedPdf'])->name('convo_student.updategeneratedPdf');

    });


    // Route::group(['prefix'=>'convo_student','middleware'=>['convo_student']],  function(){

        Route::get('convo_student/login',[ConvoStudentAuthController::class, 'index'])->name('convo_student.login');

        // Route::get('/login', function () {
        //     return view('convodataverification.student.auth.maintenance');
        // })->name('convo_student.login');

        Route::post('convo_student/login', [ConvoStudentAuthController::class, 'login'])->name('convo_student.login');


        Route::get('convo_student/reset-password/{prnNO?}', [ConvoStudentAuthController::class, 'resetPassword'])->name('convo_student.reset_password');
        
        Route::post('convo_student/reset-password-update', [ConvoStudentAuthController::class, 'resetPasswordUpdate'])->name('convo_student.resetPasswordUpdate');
        
        Route::get('convo_student/reset_password', [ConvoStudentAuthController::class, 'resetPasswordWithAllDetails'])->name('convo_student.reset_password_all_details');
        
        Route::get('convo_student/reset_password_token/{token?}', [ConvoStudentAuthController::class, 'resetPasswordViewWithAllDetails'])->name('convo_student.reset_password_token');

        Route::post('convo_student/send_password_reset_request', [ConvoStudentAuthController::class, 'sendPasswordResetRequest'])->name('convo_student.send_password_reset_request');

    // });

    // Route::prefix('convo_student')->group(function () {
    Route::group(['prefix'=>'convo_student','middleware'=>['convo_student']],  function(){

            
            

            // Route::group(['middleware'=>'convo_student'],function(){

                Route::get('/logout', [ConvoStudentAuthController::class, 'logout'])->name('convo_student.logout');

                Route::get('/dashboard', [ConvoStudentController::class, 'index'])->name('convo_student.dashboard');

                Route::post('/verify-details', [ConvoStudentController::class, 'verifyDetails'])->name('convo_student.verify_details');
                
                Route::get('/payment', [ConvoStudentController::class, 'payment'])->name('convo_student.payment');

                Route::post('/payment_response', [ConvoStudentController::class, 'payment_response'])->name('convo_student.payment_response');
                
                Route::get('/approve_pdf_preview', [ConvoStudentController::class, 'approvePdfPreview'])->name('convo_student.approve_pdf_preview');
                
                Route::get('/changeCollectionModePayment/{size?}', [ConvoStudentController::class, 'changeCollectionModePayment'])->name('convo_student.change_collection_mode_payment'); 

              
            // });  
    });


});