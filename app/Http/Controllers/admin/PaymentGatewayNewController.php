<?php
/**
 *
 *  Author : Ketan valand 
 *  Date  : 16/11/2019
 *  Use   : listing of PaymentGateway & store and update & delete PaymentGateway  
 *
**/

namespace App\Http\Controllers\admin;

use App\Events\PaymentGatewayEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\PaymentGatewayRequest;
use App\models\PaymentGatewayNew;
use Illuminate\Http\Request;
use App\models\PaymentGatewayNewConfig;
use DB;
use Auth;
class PaymentGatewayNewController extends Controller
{
    /**
     * Display a listing of the PaymentGateway.
     *
     * @param  \Illuminate\Http\Request  $request
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
                $where_str .= " and (pg_name like \"%{$search}%\""
                . ")";
            }  
           $status=$request->get('status');
            if($status==1)
            {
                $status='1';
                $where_str.= " and (payment_gateway_new.status =$status)";
            }
            else if($status==0)
            {
                $status='0';
                $where_str.=" and (payment_gateway_new.status= $status)";
            }                                               
              //for serial number
            $auth_site_id=Auth::guard('admin')->user()->site_id;

            DB::statement(DB::raw('set @rownum=0'));   
            $columns = [DB::raw('@rownum  := @rownum  + 1 AS rownum'),'pg_name','pg_title','id','updated_at'];

            $font_master_count = PaymentGatewayNew::select($columns)
                 ->whereRaw($where_str, $where_params)
                 ->where('site_id',$auth_site_id)
                 ->where('publish',1)
                 ->count();
  
            $fontMaster_list = PaymentGatewayNew::select($columns)
                ->where('publish',1)
                ->where('site_id',$auth_site_id)
                ->whereRaw($where_str, $where_params);
      
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
            $fontMaster_list = $fontMaster_list->get();
             
            $response['iTotalDisplayRecords'] = $font_master_count;
            $response['iTotalRecords'] = $font_master_count;
            $response['sEcho'] = intval($request->input('sEcho'));
            $response['aaData'] = $fontMaster_list->toArray();
            
            return $response;
        }
        return view('admin.paymentGatewayNew.index');
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
     * Store a newly created PaymentGateway in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $id=\Request::segment(3);
        $site_id=Auth::guard('admin')->user()->site_id; 
        
        $rules = [
            'pg_title'=>'required|max:100|unique:payment_gateway_new,pg_name,'.$id.',id,site_id,'.$site_id,
            'opt_status'=>'required',
            'pg_title'=>'required'
        ];

        $messages = [
            'pg_name.required'=>'Enter Name',
            'pg_name.unique'=>'Payment Gateway name alreday taken',
            'pg_title.required'=>'Enter Title Here',
            'opt_status.required'=>'Select Status'
         ];

        $validated = $request->validate($rules,$messages);

        $pg_data=$request->all();
        
        $id=null;
        // check id empty or not
        if(!empty($pg_data['pg_id']))
        {
            $id=$pg_data['pg_id'];
        }
        
        // get Admin id
        $auth_id=Auth::guard('admin')->user()->id;
        $auth_site_id=Auth::guard('admin')->user()->site_id;    
        // save PaymentGateway data on database
        $pg_obj=PaymentGatewayNew::firstOrNew(['id'=>$id]);

        $pg_obj->pg_name=$pg_data['pg_name'];
        $pg_obj->pg_title=$pg_data['pg_title'];
        $pg_obj->merchant_key=$pg_data['merchant_key'];
        $pg_obj->salt=$pg_data['merchant_salt'];
        $pg_obj->test_merchant_key=$pg_data['test_merchant_key'];
        $pg_obj->test_salt=$pg_data['test_merchant_salt'];
        $pg_obj->website=$pg_data['website'];
        $pg_obj->channel=$pg_data['channel'];
        $pg_obj->payment_mode=$pg_data['pg_mode'];
        $pg_obj->industry_type=$pg_data['industry_type'];

        $pg_obj->updated_by=$auth_id;
        $pg_obj->site_id=$auth_site_id;
        $pg_obj->status=$pg_data['opt_status'];
        $pg_obj->save();
        
        $pg_obj=$pg_obj->toArray(); 
        $pg_config=PaymentGatewayNewConfig::firstOrNew(['id'=>$id]);
        $pg_config->pg_id=$pg_obj['id'];
        $pg_config->updated_by=$auth_id;
        $pg_config->amount = $pg_data['amount'] ? $pg_data['amount'] : '1';
        $pg_config->pg_status=$pg_obj['status'];
        $pg_config->save();
        return response()->json(['success'=>true]);

    }

    /**
     * Display the specified PaymentGatewayNew.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified PaymentGatewayNew.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $pg_data=PaymentGatewayNew::select('*')
                 ->where('id',$id)
                 ->get()->toArray();
        $pg_data=head($pg_data);

        return $pg_data;
    }

    /**
     * Update the specified PaymentGatewayNew in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
       
        $site_id=Auth::guard('admin')->user()->site_id; 

        $rules = [
            'pg_title'=>'required|max:100|unique:payment_gateway_new,pg_name,'.$id.',id,site_id,'.$site_id,
            'pg_name'=>'required',
            'opt_status'=>'required'
        ];

        $messages = [
            'pg_name.required'=>'Enter Name',
            'pg_name.unique'=>'Payment Gateway name alreday taken',
            'pg_title.required'=>'Enter Payment Title',
            'opt_status.required'=>'Select Status'
         ];

        $validated = $request->validate($rules,$messages);

        $pg_data=$request->all();
        // print_r($pg_data);
        $id=null;
        // check id empty or not
        if(!empty($pg_data['pg_id']))
        {
            $id=$pg_data['pg_id'];
        }
        
        // get Admin id
        $auth_id=Auth::guard('admin')->user()->id;
        $auth_site_id=Auth::guard('admin')->user()->site_id;    
        // save PaymentGateway data on database
        $pg_obj=PaymentGatewayNew::firstOrNew(['id'=>$id]);
        //$pg_obj->fill($pg_data);
        $pg_obj->pg_name=$pg_data['pg_name'];
        $pg_obj->pg_title=$pg_data['pg_title'];
        $pg_obj->merchant_key=$pg_data['merchant_key'];
        $pg_obj->salt=$pg_data['merchant_salt'];
        $pg_obj->test_merchant_key=$pg_data['test_merchant_key'];
        $pg_obj->test_salt=$pg_data['test_merchant_salt'];
        $pg_obj->website=$pg_data['website'];
        $pg_obj->channel=$pg_data['channel'];
        $pg_obj->payment_mode=$pg_data['pg_mode'];
        $pg_obj->industry_type=$pg_data['industry_type'];

        $pg_obj->updated_by=$auth_id;
        $pg_obj->site_id=$auth_site_id;
        $pg_obj->status=$pg_data['opt_status'];
        $pg_obj->save();
         
        $pg_obj=$pg_obj->toArray(); 
        $pg_config=PaymentGatewayNewConfig::firstOrNew(['id'=>$id]);
        $pg_config->pg_id=$pg_obj['id'];
        $pg_config->updated_by=$auth_id;
        $pg_config->amount = $pg_data['amount'] ? $pg_data['amount'] : '1';
        $pg_config->pg_status=$pg_obj['status'];
        $pg_config->save();

        return response()->json(['success'=>true]);   
    }
    /**
     * Remove the specified PaymentGatewayNew from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
       $result=PaymentGatewayNew::where('id',$id)->delete();
       $pgc=PaymentGatewayNewConfig::where('pg_id',$id)->delete();
       return  $result ? response()->json(['success'=>true]) :"false"; 
    }
}
