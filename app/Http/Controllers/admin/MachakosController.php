<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\models\License;
use TCPDF;
use DB;
use App\Jobs\PdfGenerateMachakosJob;

class MachakosController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        if($request->ajax()){
            $where_str    = "1 = ?";
            $where_params = array(1); 

            if (!empty($request->input('sSearch')))
            {
                $search     = $request->input('sSearch');
                $where_str .= " and ( business_name like \"%{$search}%\""
                
                . ")";
            }  
                                                     
              //for serial number
            $iDisplayStart=$request->input('iDisplayStart'); 
            DB::statement(DB::raw('set @rownum='.$iDisplayStart));
            DB::statement(DB::raw('set @rownum=0'));   
            $columns = [DB::raw('@rownum  := @rownum  + 1 AS rownum'),'business_name','created_at','pdf_file','request_mode'];
           

            $font_master_count = License::select($columns)
               
                 ->where('publish',1)
              
                 ->count();
               
            $fontMaster_list = License::select($columns)
                   ->where('publish',1);
               
      
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
            $fontMaster_list = $fontMaster_list->OrderBy('id','desc')->get();
             
            $response['iTotalDisplayRecords'] = $font_master_count;
            $response['iTotalRecords'] = $font_master_count;
            $response['sEcho'] = intval($request->input('sEcho'));
            $response['aaData'] = $fontMaster_list->toArray();
            
            return $response;
        }
        return view('admin.machakos.index');
    }

    public function allRecordAjax(){
        $data = License::orderBy('id', 'desc')->get();
        return $data;
    }

    

    public function uploadJson(Request $request)
{
    $request->validate([
        'pdf_file' => 'required|file',
    ]);

    if ($request->file('pdf_file')) {
        $filePath = $request->file('pdf_file')->store('uploads');
        // Read and decode the JSON file contents
        $jsonContent = file_get_contents(storage_path('app/' . $filePath));
        $jsonData = json_decode($jsonContent, true);
        $link = $this->dispatch(new PdfGenerateMachakosJob($jsonData));

        return response()->json(['success' => 'File uploaded successfully']);
    }

    return response()->json(['error' => 'File upload failed'], 400);
}




    
    
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
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
}
