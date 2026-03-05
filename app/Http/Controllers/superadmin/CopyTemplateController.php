<?php

namespace App\Http\Controllers\superadmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SuperAdminRoleRequest;
use App\Jobs\SuperAdminRolePermissionJob;
use App\Models\Site;
use App\Models\demo\SitesSuperdata;
use App\Models\SitePermission;
use App\models\AclPermission;
use App\models\SuperAdmin;
use App\models\SuperAdminLogin;
use App\models\SystemConfig;
use App\models\UserPermission;
use App\models\RolePermission;
use App\models\Role;
use App\models\PaymentGateway;
use App\models\PaymentGatewayConfig;
use App\models\Superapp\InstanceList;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Admin;
use Hash;
use App\Models\TemplateMaster;
use App\models\FieldMaster;
use Auth;
use App\Library\Services\CheckUploadedFileOnAwsORLocalService;

class CopyTemplateController extends Controller
{
    
    public function index(Request $request){

       if($request->ajax()){

            $where_str    = "1 = ?";
            $where_params = array(1); 

            if (!empty($request->input('sSearch')))
            {
                $search     = $request->input('sSearch');
                $where_str .= " and ( sites_name like \"%{$search}%\""
               
                . ")";
            }  
           $status=$request->get('status');
           /* if($status==1)
            {
                $status='1';
                $where_str.= " and (sites_superdata.status =$status)";
            }
            else if($status==0)
            {
                $status='0';
                $where_str.=" and (sites_superdata.status= $status)";
            } */
                                                    
             //for serial number
            DB::statement(DB::raw('set @rownum=0'));  
           // DB::statement(DB::raw('set @site_url=SUBSTRING_INDEX(site_url, ".", 1)'));   
            $columns = ['id','sites_name','template_count','pdf2pdf_template_count','custom_templates','site_id'];

            $columnsQuery = [DB::raw('@rownum  := @rownum  + 1 AS rownum'),DB::raw('@site_url :=SUBSTRING_INDEX(sites_superdata.sites_name, ".", 1) AS site_url'),DB::raw('(template_number+inactive_template_number) AS template_count'),DB::raw('(pdf2pdf_active_templates+pdf2pdf_inactive_templates) AS pdf2pdf_template_count'),'custom_templates','site_id'];
            
            /*$columnsQuery = [DB::raw('@rownum  := @rownum  + 1 AS rownum'),DB::raw('@site_url :=SUBSTRING_INDEX(sites.site_url, ".", 1) AS site_url'),DB::raw('@dbInstance :=IF(@site_url="demo","seqr_demo",CONCAT("seqr_d","_",@site_url)) as dbInstance'),DB::raw('(SELECT count(*) FROM `@dbInstance`.`template_master` WHERE site_id=sites.site_id) as template_count'),'site_id'];
            */
            $font_master_count = SitesSuperdata::select($columnsQuery)
                ->whereRaw($where_str, $where_params)
               
                ->count();
  
            $fontMaster_list = SitesSuperdata::select($columnsQuery)
                 
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
            $fontMaster_list_array=$fontMaster_list->toArray();
/*             print_r($fontMaster_list_array);

            if($fontMaster_list_array){
                foreach ($fontMaster_list_array as $key => $value) {
                    
                   
                    $subdomain = explode('.', $value['site_url']);
                    //$value['site_url']=$subdomain[0];
                    if($subdomain[0] == 'demo')
                    {
                        $dbName = 'seqr_'.$subdomain[0];
                    }
                    else{
                        $dbName = 'seqr_d_'.$subdomain[0];
                    }
                     
                    $data=DB::select(DB::raw('SELECT count(*) as template_count FROM `'.$dbName.'`.`template_master`'));
                    if(isset($data[0])){
                        $template_count=$data[0]->template_count;
                    }else{
                        $template_count=0;
                    }
			
                    $value = array_slice($value, 0, 2, true) +
                            array("template_count" => $template_count) +
                            array_slice($value, 2, count($value) - 1, true) ;

                   
                    $fontMaster_list_array[$key]=$value;
                    

                }
            }*/
             
            $response['iTotalDisplayRecords'] = $font_master_count;
            $response['iTotalRecords'] = $font_master_count;
            $response['sEcho'] = intval($request->input('sEcho'));
            $response['aaData'] = $fontMaster_list_array;
            

            
            return $response;
        }
        return view('superadmin.copytemplate.index');
    }
     /**
     * Show the form for creating a new Role.
     *
     * @return view response
     */
     

    public function viewtemplates($instance)
    {
        $instancesList = Site::select(DB::raw('SUBSTRING_INDEX(sites.site_url, ".", 1) as site_url'));
        $instancesList = $instancesList->orderBy('site_url','ASC');   
        $instancesList = $instancesList->get();
        $instancesListArray=$instancesList->toArray();
        return view('superadmin.copytemplate.templateslist',compact('instance','instancesListArray'));
    }

    public function viewtemplateslist(Request $request){

       if($request->ajax()){

            $where_str    = "1 = ?";
            $where_params = array(1); 

            if (!empty($request->input('sSearch')))
            {
                $search     = $request->input('sSearch');
                $where_str .= " and ( template_name like \"%{$search}%\"
                                OR  status like \"%{$search}%\""
                . ")";
            }  
            $instance = $request->get('instance');

            $subdomain = explode('.', $instance);

       
            if($subdomain[0] == 'demo')
            {
                $dbName = 'seqr_'.$subdomain[0];
            }
            else{
                $dbName = 'seqr_d_'.$subdomain[0];
            }
            
            $siteData = Site::select('new_server')->where(DB::raw('SUBSTRING_INDEX(site_url, ".", 1)'),$instance)->first();

            $dbCred = '';
            if($siteData->new_server == 0) {
                $dbCred = 'mysql';
            } else {
                $dbCred = 'mysql_new';
            }

            //for serial number
            DB::connection($dbCred)->statement(DB::connection($dbCred)->raw('set @rownum=0'));   
            $columns = [DB::connection($dbCred)->raw('@rownum  := @rownum  + 1 AS rownum'),'template_name','status','id'];
            
           

            $font_master_count = DB::connection($dbCred)->table($dbName.'.template_master')
                        ->whereRaw($where_str, $where_params)
                        ->count();

        
            $fontMaster_list = DB::connection($dbCred)->table($dbName.'.template_master')->select($columns)
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
            $fontMaster_list_array=$fontMaster_list->toArray();
           
             
            $response['iTotalDisplayRecords'] = $font_master_count;
            $response['iTotalRecords'] = $font_master_count;
            $response['sEcho'] = intval($request->input('sEcho'));
            $response['aaData'] = $fontMaster_list_array;
            

            
            return $response;
        }
        return view('superadmin.copytemplate.templateslist');
    }

    public function copyTemplate(Request $request,CheckUploadedFileOnAwsORLocalService $checkUploadedFileOnAwsOrLocal){

        $domainSource=$request->source_instance;
        $subdomainSource = explode('.', $domainSource);

        if($subdomainSource[0] == 'demo')
        {
            $dbNameSource = 'seqr_'.$subdomainSource[0];
        }
        else{
            $dbNameSource = 'seqr_d_'.$subdomainSource[0];
        }

        // source database credientials
        $siteDataSource = Site::select('new_server')->where(DB::raw('SUBSTRING_INDEX(site_url, ".", 1)'),$subdomainSource[0])->first();
        $dbCredSource = '';
        if($siteDataSource->new_server == 0) {
            $dbCredSource = 'mysql';
        } else {
            $dbCredSource = 'mysql_new';
        }

        // -----------------------\\

        $domainDest=$request->instance;
        $subdomainDest = explode('.', $domainDest);

        if($subdomainDest[0] == 'demo')
        {
            $dbNameDest = 'seqr_'.$subdomainDest[0];
        }
        else{
            $dbNameDest = 'seqr_d_'.$subdomainDest[0];
        }
        
        // destination database credientials
        $siteDataDest = Site::select('new_server')->where(DB::raw('SUBSTRING_INDEX(site_url, ".", 1)'),$subdomainDest[0])->first();
        $dbCredDest = '';
        if($siteDataDest->new_server == 0) {
            $dbCredDest = 'mysql';
        } else {
            $dbCredDest = 'mysql_new';
        }        

        // echo "destinDB Connectipon :".$dbCredDest;
        // echo "<br>";
        $get_file_aws_local_flag = $checkUploadedFileOnAwsOrLocal->checkUploadedFileOnAwsORLocal();

        if(!empty($request['template_id']))
        {
         
            $template_id=$request['template_id'];
        
            //Source Template Data
            $templateData=DB::connection($dbCredSource)->select(DB::connection($dbCredSource)->raw('SELECT * FROM `'.$dbNameSource.'`.`template_master` WHERE id ="'.$request['template_id'].'"'));
            $copyTemplate_data=(array)$templateData[0];
            unset($copyTemplate_data['id']);

            
            $old_Template_name=$copyTemplate_data['template_name'];
            
            $copyTemplate_data['actual_template_name']=$copyTemplate_data['actual_template_name'];
            $copyTemplate_data['template_name']=$copyTemplate_data['template_name'];  
        
            $alreadyCopyData=DB::connection($dbCredDest)->select(DB::connection($dbCredDest)->raw('SELECT * FROM `'.$dbNameDest.'`.`template_master` WHERE actual_template_name ="'.$copyTemplate_data['actual_template_name'].'"'));             
            
            
            $ftpNewHost = \Config::get('constant.seqr_new_ftp_host');
            $ftpNewPort = \Config::get('constant.seqr_new_ftp_port');
            $ftpNewUsername = \Config::get('constant.seqr_new_ftp_username');
            $ftpNewPassword = \Config::get('constant.seqr_new_ftp_pass'); 

            $ftpUrl = 'ftp://'.$ftpNewUsername.':'.$ftpNewPassword.'@'.$ftpNewHost.'/public';
            
            // echo $ftpUrl;
            // echo "<br>";

            $filenameExist = public_path().'/superadmin_changes_file.txt';            
            // unlink($filenameExist);

            if (file_exists($filenameExist)) {
                unlink($filenameExist);
            }
    
            $contents = '/********************* This Files to move manually *********************\ ';
            #already_copy check
            if(empty($alreadyCopyData))
            {
               
                $siteUrl=$domainDest.'.seqrdoc.com';
                //Site id
                $siteData=DB::connection($dbCredDest)->select(DB::connection($dbCredDest)->raw('SELECT site_id FROM `seqr_demo`.`sites` WHERE site_url ="'.$siteUrl.'"'));
                $site_id=$siteData[0]->site_id;

                //Copy BG data 
                $newBgTemplateId=0;
                if(!empty($copyTemplate_data['bg_template_id'])){
                    $bgData=DB::connection($dbCredSource)->select(DB::connection($dbCredSource)->raw('SELECT * FROM `'.$dbNameSource.'`.`background_template_master` WHERE id ="'.$copyTemplate_data['bg_template_id'].'"'));
                    $copyBgTemplate_data=(array)$bgData[0];
                    $copyBgTemplate_data['site_id']=$site_id;
                    unset($copyBgTemplate_data['id']);
                    $newBgTemplateId=DB::connection($dbCredDest)->table($dbNameDest.'.background_template_master')->insertGetId($copyBgTemplate_data);


                    if($siteDataSource->new_server == 0 && $siteDataDest->new_server == 0) {   // old sever ==> old sever
                        $old_BgTemplate_file=public_path().'/'.$subdomainSource[0].'/'.\Config::get('constant.bg_images').'/'.$copyBgTemplate_data['image_path'];
                        $new_BgTemplate_file=public_path().'/'.$subdomainDest[0].'/'.\Config::get('constant.bg_images').'/'.$copyBgTemplate_data['image_path'];
                        if (!file_exists($new_BgTemplate_file)) {   
                            copy($old_BgTemplate_file,$new_BgTemplate_file);
                        }
                    } 
                    elseif ($siteDataSource->new_server == 0 && $siteDataDest->new_server == 1) { // old sever ==> new server 
                        $old_BgTemplate_file=public_path().'/'.$subdomainSource[0].'/'.\Config::get('constant.bg_images').'/'.$copyBgTemplate_data['image_path'];
                        $new_BgTemplate_file='/'.$subdomainDest[0].'/'.\Config::get('constant.bg_images').'/'.$copyBgTemplate_data['image_path'];
                        copy($old_BgTemplate_file, $ftpUrl.$new_BgTemplate_file);
                    }
                    elseif ($siteDataSource->new_server == 1 && $siteDataDest->new_server == 0) { // new server ==> old sever 
                        $old_BgTemplate_file='/'.$subdomainSource[0].'/'.\Config::get('constant.bg_images').'/'.$copyBgTemplate_data['image_path'];
                        $new_BgTemplate_file='/'.$subdomainDest[0].'/'.\Config::get('constant.bg_images').'/'.$copyBgTemplate_data['image_path'];
                        // $new_BgTemplate_file=public_path().'/'.$subdomainDest[0].'/'.\Config::get('constant.bg_images').'/'.$copyBgTemplate_data['image_path'];

                        // $copyFtpUrl = $ftpUrl.''.$old_BgTemplate_file;
                        // copy($copyFtpUrl, $new_BgTemplate_file);
                        
                        
                        $contents .= "\n";
                        $contents .= 'All Source Template Files list : ';
                        $contents .= "\n";
                        $contents .= "From (new server) : ".$old_BgTemplate_file;
                        $contents .= "\n";
                        $contents .= "To (old server) : ".$new_BgTemplate_file;
                        $contents .= "\n";
                        $contents .= "\n";
                        
                    }
                    elseif ($siteDataSource->new_server == 1 && $siteDataDest->new_server == 1) { // new server ==> new server 

                        $old_BgTemplate_file='/'.$subdomainSource[0].'/'.\Config::get('constant.bg_images').'/'.$copyBgTemplate_data['image_path'];
                        $new_BgTemplate_file='/'.$subdomainDest[0].'/'.\Config::get('constant.bg_images').'/'.$copyBgTemplate_data['image_path'];
                        // copy($ftpUrl.$old_BgTemplate_file, $ftpUrl.$new_BgTemplate_file);

                        $contents = '/********************* This Files to move manually *********************\ ';
                        $contents .= "\n";
                        $contents .= 'All Source Template Files list : ';
                        $contents .= "\n";
                        $contents .= "From  (new server) : ".$old_BgTemplate_file;
                        $contents .= "\n";
                        $contents .= "To (new server) : ".$new_BgTemplate_file;
                        $contents .= "\n";
                        $contents .= "\n";
                        
                    }


                    

                }

                //Old Field Data
                $field_data=DB::connection($dbCredSource)->select(DB::connection($dbCredSource)->raw('SELECT * FROM `'.$dbNameSource.'`.`fields_master` WHERE template_id ="'.$request['template_id'].'"'));                      
                $field_data=(array)$field_data;

                
                $copyTemplate_data['site_id']=$site_id;
                if(!empty($newBgTemplateId)){
                    $copyTemplate_data['bg_template_id']=$newBgTemplateId;
                }
                
                $copyTemplate_data['status']=1;
                unset($copyTemplate_data['scanning_fee']);

                //Destination template id
                $newTemplateId=DB::connection($dbCredDest)->table($dbNameDest.'.template_master')->insertGetId($copyTemplate_data);
            
                foreach ($field_data as $key => $value) {

                    $field_data[$key]=(array)$field_data[$key];

                    $field_data[$key]['template_id']=$newTemplateId;
                    if(empty($field_data[$key]['is_transparent_image'])){
                        $field_data[$key]['is_transparent_image']=0;
                    }
                    unset($field_data[$key]['id']);

                    unset($field_data[$key]['character_spacing']);
                    unset($field_data[$key]['increment_step_value']);

                    DB::connection($dbCredDest)->table($dbNameDest.'.fields_master')->insert($field_data[$key]);
                }

                $font_data=DB::connection($dbCredSource)->select(DB::connection($dbCredSource)->raw('SELECT DISTINCT font_id FROM `'.$dbNameSource.'`.`fields_master` WHERE template_id ="'.$request['template_id'].'" AND font_id!=0'));  
                if($font_data){

                    foreach ($font_data as $readFont) {
                        
                    
                        $fontMaster_data=DB::connection($dbCredSource)->select(DB::connection($dbCredSource)->raw('SELECT * FROM `'.$dbNameSource.'`.`font_master` WHERE id ="'.$readFont->font_id.'"'));                      
                        $fontMaster_data=(array)$fontMaster_data[0];
                        $oldFontId=$fontMaster_data['id'];


                        $fontMaster_data_dest=DB::connection($dbCredSource)->select(DB::connection($dbCredSource)->raw('SELECT * FROM `'.$dbNameSource.'`.`font_master` WHERE id ="'.$readFont->font_id.'"'));             
                        if($fontMaster_data_dest){
                            
                            $fontMaster_data_dest=(array)$fontMaster_data_dest[0];
                            DB::connection($dbCredDest)->select(DB::connection($dbCredDest)->raw('UPDATE `'.$dbNameDest.'`.`fields_master` SET font_id="'.$fontMaster_data_dest['id'].'"   WHERE template_id ="'.$newTemplateId.'" AND font_id="'.$oldFontId.'"'));
                        

                            if(!empty($fontMaster_data['font_filename_N'])&&empty($fontMaster_data_dest['font_filename_N'])){


                                if($siteDataSource->new_server == 0 && $siteDataDest->new_server == 0) {   // old sever ==> old sever
                                    $old_fontTemplate_file=public_path().'/'.$subdomainSource[0].'/'.\Config::get('constant.fonts').'/'.$fontMaster_data['font_filename_N'];
                                    $new_fontTemplate_file=public_path().'/'.$subdomainDest[0].'/'.\Config::get('constant.fonts').'/'.$fontMaster_data['font_filename_N'];
                                    if (!file_exists($new_fontTemplate_file)) {
                                        \File::copy($old_fontTemplate_file,$new_fontTemplate_file);
                                    }
                                } 
                                elseif ($siteDataSource->new_server == 0 && $siteDataDest->new_server == 1) { // old sever ==> new server 
                                    $old_fontTemplate_file=public_path().'/'.$subdomainSource[0].'/'.\Config::get('constant.fonts').'/'.$fontMaster_data['font_filename_N'];
                                    $new_fontTemplate_file='/'.$subdomainDest[0].'/'.\Config::get('constant.fonts').'/'.$fontMaster_data['font_filename_N'];
                                    copy($old_fontTemplate_file, $ftpUrl.$new_fontTemplate_file);
                                }
                                elseif ($siteDataSource->new_server == 1 && $siteDataDest->new_server == 0) { // new server ==> old sever 
                                    $old_fontTemplate_file='/'.$subdomainSource[0].'/'.\Config::get('constant.fonts').'/'.$fontMaster_data['font_filename_N'];
                                    $new_fontTemplate_file='/'.$subdomainDest[0].'/'.\Config::get('constant.fonts').'/'.$fontMaster_data['font_filename_N'];
                                    
                                    //copy($ftpUrl.$old_fontTemplate_file, $new_fontTemplate_file);

                                    $contents .= 'Font List : ';
                                    $contents .= "\n";
                                    $contents .= "From  (new server) : ".$old_fontTemplate_file;
                                    $contents .= "\n";
                                    $contents .= "To (old server) : ".$new_fontTemplate_file;
                                    $contents .= "\n";
                                    $contents .= "\n";

                                }
                                elseif ($siteDataSource->new_server == 1 && $siteDataDest->new_server == 1) { // new server ==> new server 
                                    $old_fontTemplate_file='/'.$subdomainSource[0].'/'.\Config::get('constant.fonts').'/'.$fontMaster_data['font_filename_N'];
                                    $new_fontTemplate_file='/'.$subdomainDest[0].'/'.\Config::get('constant.fonts').'/'.$fontMaster_data['font_filename_N'];
                                    //copy($ftpUrl.$old_fontTemplate_file, $ftpUrl.$new_fontTemplate_file);

                                    $contents .= 'Font List : ';
                                    $contents .= "\n";
                                    $contents .= "From  (new server) : ".$old_fontTemplate_file;
                                    $contents .= "\n";
                                    $contents .= "To (new server) : ".$new_fontTemplate_file;
                                    $contents .= "\n";
                                    $contents .= "\n";

                                }
                                

                                DB::connection($dbCredDest)->select(DB::connection($dbCredDest)->raw('UPDATE `'.$dbNameDest.'`.`font_master` SET font_filename_N="'.$fontMaster_data['font_filename_N'].'" WHERE id="'.$fontMaster_data_dest['id'].'"')); 
                            }
                            if(!empty($fontMaster_data['font_filename_B'])&&empty($fontMaster_data_dest['font_filename_B'])){

                                if($siteDataSource->new_server == 0 && $siteDataDest->new_server == 0) {   // old sever ==> old sever
                                    $old_fontTemplate_file=public_path().'/'.$subdomainSource[0].'/'.\Config::get('constant.fonts').'/'.$fontMaster_data['font_filename_B'];
                                    $new_fontTemplate_file=public_path().'/'.$subdomainDest[0].'/'.\Config::get('constant.fonts').'/'.$fontMaster_data['font_filename_B'];
                                    if (!file_exists($new_fontTemplate_file)) {
                                        \File::copy($old_fontTemplate_file,$new_fontTemplate_file);
                                    }
                                } 
                                elseif ($siteDataSource->new_server == 0 && $siteDataDest->new_server == 1) { // old sever ==> new server 
                                    $old_fontTemplate_file=public_path().'/'.$subdomainSource[0].'/'.\Config::get('constant.fonts').'/'.$fontMaster_data['font_filename_B'];
                                    $new_fontTemplate_file='/'.$subdomainDest[0].'/'.\Config::get('constant.fonts').'/'.$fontMaster_data['font_filename_B'];
                                    copy($old_fontTemplate_file, $ftpUrl.$new_fontTemplate_file);
                                }
                                elseif ($siteDataSource->new_server == 1 && $siteDataDest->new_server == 0) { // new server ==> old sever 
                                    $old_fontTemplate_file='/'.$subdomainSource[0].'/'.\Config::get('constant.fonts').'/'.$fontMaster_data['font_filename_B'];
                                    $new_fontTemplate_file='/'.$subdomainDest[0].'/'.\Config::get('constant.fonts').'/'.$fontMaster_data['font_filename_B'];
                                    // $new_fontTemplate_file=public_path().'/'.$subdomainDest[0].'/'.\Config::get('constant.fonts').'/'.$fontMaster_data['font_filename_B'];
                                    // copy($ftpUrl.$old_fontTemplate_file, $new_fontTemplate_file);

                                    $contents .= 'Font List : ';
                                    $contents .= "\n";
                                    $contents .= "From  (new server) : ".$old_fontTemplate_file;
                                    $contents .= "\n";
                                    $contents .= "To (old server) : ".$new_fontTemplate_file;
                                    $contents .= "\n";
                                    $contents .= "\n";

                                }
                                elseif ($siteDataSource->new_server == 1 && $siteDataDest->new_server == 1) { // new server ==> new server 
                                    $old_fontTemplate_file='/'.$subdomainSource[0].'/'.\Config::get('constant.fonts').'/'.$fontMaster_data['font_filename_B'];
                                    $new_fontTemplate_file='/'.$subdomainDest[0].'/'.\Config::get('constant.fonts').'/'.$fontMaster_data['font_filename_B'];
                                    copy($ftpUrl.$old_fontTemplate_file, $ftpUrl.$new_fontTemplate_file);

                                    $contents .= 'Font List : ';
                                    $contents .= "\n";
                                    $contents .= "From  (new server) : ".$old_fontTemplate_file;
                                    $contents .= "\n";
                                    $contents .= "To (new server) : ".$new_fontTemplate_file;
                                    $contents .= "\n";
                                    $contents .= "\n";

                                }

                                DB::connection($dbCredDest)->select(DB::connection($dbCredDest)->raw('UPDATE `'.$dbNameDest.'`.`font_master` SET font_filename_B="'.$fontMaster_data['font_filename_B'].'" WHERE id="'.$fontMaster_data_dest['id'].'"'));  
                            }  
                            if(!empty($fontMaster_data['font_filename_I'])&&empty($fontMaster_data_dest['font_filename_B'])){

                                
                                // 
                                if($siteDataSource->new_server == 0 && $siteDataDest->new_server == 0) {   // old sever ==> old sever
                                    $old_fontTemplate_file=public_path().'/'.$subdomainSource[0].'/'.\Config::get('constant.fonts').'/'.$fontMaster_data['font_filename_I'];
                                    $new_fontTemplate_file=public_path().'/'.$subdomainDest[0].'/'.\Config::get('constant.fonts').'/'.$fontMaster_data['font_filename_I'];
                                    if (!file_exists($new_fontTemplate_file)) {
                                        
                                        \File::copy($old_fontTemplate_file,$new_fontTemplate_file);
                                    }
                                } 
                                elseif ($siteDataSource->new_server == 0 && $siteDataDest->new_server == 1) { // old sever ==> new server 
                                    $old_fontTemplate_file=public_path().'/'.$subdomainSource[0].'/'.\Config::get('constant.fonts').'/'.$fontMaster_data['font_filename_I'];
                                    $new_fontTemplate_file='/'.$subdomainDest[0].'/'.\Config::get('constant.fonts').'/'.$fontMaster_data['font_filename_I'];
                                    copy($old_fontTemplate_file, $ftpUrl.$new_fontTemplate_file);
                                }
                                elseif ($siteDataSource->new_server == 1 && $siteDataDest->new_server == 0) { // new server ==> old sever 
                                    $old_fontTemplate_file='/'.$subdomainSource[0].'/'.\Config::get('constant.fonts').'/'.$fontMaster_data['font_filename_I'];
                                    $new_fontTemplate_file='/'.$subdomainDest[0].'/'.\Config::get('constant.fonts').'/'.$fontMaster_data['font_filename_I'];
                                    
                                    // $new_fontTemplate_file=public_path().'/'.$subdomainDest[0].'/'.\Config::get('constant.fonts').'/'.$fontMaster_data['font_filename_I'];
                                    // copy($ftpUrl.$old_fontTemplate_file, $new_fontTemplate_file);

                                    $contents .= 'Font List : ';
                                    $contents .= "\n";
                                    $contents .= "From  (new server) : ".$old_fontTemplate_file;
                                    $contents .= "\n";
                                    $contents .= "To (old server) : ".$new_fontTemplate_file;
                                    $contents .= "\n";
                                    $contents .= "\n";

                                }
                                elseif ($siteDataSource->new_server == 1 && $siteDataDest->new_server == 1) { // new server ==> new server 
                                    $old_fontTemplate_file='/'.$subdomainSource[0].'/'.\Config::get('constant.fonts').'/'.$fontMaster_data['font_filename_I'];
                                    $new_fontTemplate_file='/'.$subdomainDest[0].'/'.\Config::get('constant.fonts').'/'.$fontMaster_data['font_filename_I'];
                                    //copy($ftpUrl.$old_fontTemplate_file, $ftpUrl.$new_fontTemplate_file);

                                    $contents .= 'Font List : ';
                                    $contents .= "\n";
                                    $contents .= "From  (new server) : ".$old_fontTemplate_file;
                                    $contents .= "\n";
                                    $contents .= "To (new server) : ".$new_fontTemplate_file;
                                    $contents .= "\n";
                                    $contents .= "\n";
                                }


                                DB::connection($dbCredDest)->select(DB::connection($dbCredDest)->raw('UPDATE `'.$dbNameDest.'`.`font_master` SET font_filename_I="'.$fontMaster_data['font_filename_I'].'" WHERE id="'.$fontMaster_data_dest['id'].'"'));
                            }  
                            if(!empty($fontMaster_data['font_filename_BI'])&&empty($fontMaster_data_dest['font_filename_BI'])){

                            
                                if($siteDataSource->new_server == 0 && $siteDataDest->new_server == 0) {   // old sever ==> old sever
                                    $old_fontTemplate_file=public_path().'/'.$subdomainSource[0].'/'.\Config::get('constant.fonts').'/'.$fontMaster_data['font_filename_BI'];
                                    $new_fontTemplate_file=public_path().'/'.$subdomainDest[0].'/'.\Config::get('constant.fonts').'/'.$fontMaster_data['font_filename_BI'];
                                    if (!file_exists($new_fontTemplate_file)) {
                                        \File::copy($old_fontTemplate_file,$new_fontTemplate_file);
                                    }
                                } 
                                elseif ($siteDataSource->new_server == 0 && $siteDataDest->new_server == 1) { // old sever ==> new server 
                                    $old_fontTemplate_file=public_path().'/'.$subdomainSource[0].'/'.\Config::get('constant.fonts').'/'.$fontMaster_data['font_filename_BI'];
                                    $new_fontTemplate_file='/'.$subdomainDest[0].'/'.\Config::get('constant.fonts').'/'.$fontMaster_data['font_filename_BI'];
                                    copy($old_fontTemplate_file, $ftpUrl.$new_fontTemplate_file);
                                }
                                elseif ($siteDataSource->new_server == 1 && $siteDataDest->new_server == 0) { // new server ==> old sever 
                                    $old_fontTemplate_file='/'.$subdomainSource[0].'/'.\Config::get('constant.fonts').'/'.$fontMaster_data['font_filename_BI'];
                                    $new_fontTemplate_file='/'.$subdomainDest[0].'/'.\Config::get('constant.fonts').'/'.$fontMaster_data['font_filename_BI'];
                                    
                                    // $new_fontTemplate_file=public_path().'/'.$subdomainDest[0].'/'.\Config::get('constant.fonts').'/'.$fontMaster_data['font_filename_BI'];
                                    // copy($ftpUrl.$old_fontTemplate_file, $new_fontTemplate_file);

                                    $contents .= 'Font List : ';
                                    $contents .= "\n";
                                    $contents .= "From  (new server) : ".$old_fontTemplate_file;
                                    $contents .= "\n";
                                    $contents .= "To (old server) : ".$new_fontTemplate_file;
                                    $contents .= "\n";
                                    $contents .= "\n";


                                }
                                elseif ($siteDataSource->new_server == 1 && $siteDataDest->new_server == 1) { // new server ==> new server 
                                    $old_fontTemplate_file='/'.$subdomainSource[0].'/'.\Config::get('constant.fonts').'/'.$fontMaster_data['font_filename_BI'];
                                    $new_fontTemplate_file='/'.$subdomainDest[0].'/'.\Config::get('constant.fonts').'/'.$fontMaster_data['font_filename_BI'];
                                    //copy($ftpUrl.$old_fontTemplate_file, $ftpUrl.$new_fontTemplate_file);

                                    $contents .= 'Font List : ';
                                    $contents .= "\n";
                                    $contents .= "From  (new server) : ".$old_fontTemplate_file;
                                    $contents .= "\n";
                                    $contents .= "To (new server) : ".$new_fontTemplate_file;
                                    $contents .= "\n";
                                    $contents .= "\n";

                                }

                                DB::connection($dbCredDest)->select(DB::connection($dbCredDest)->raw('UPDATE `'.$dbNameDest.'`.`font_master` SET font_filename_BI="'.$fontMaster_data['font_filename_BI'].'" WHERE id="'.$fontMaster_data_dest['id'].'"'));
                            }
                        }else{
                        
                            unset($fontMaster_data['id']);
                            $fontMaster_data['site_id']=$site_id;
                            $font_id_new=DB::connection($dbCredDest)->table($dbNameDest.'.font_master')->insertGetId($fontMaster_data);

                            DB::connection($dbCredDest)->select(DB::connection($dbCredDest)->raw('UPDATE `'.$dbNameDest.'`.`fields_master` SET font_id="'.$font_id_new.'"   WHERE template_id ="'.$newTemplateId.'" AND font_id="'.$oldFontId.'"'));
                            
                            if(!empty($fontMaster_data['font_filename_N'])){

                                if($siteDataSource->new_server == 0 && $siteDataDest->new_server == 0) {   // old sever ==> old sever
                                    $old_fontTemplate_file=public_path().'/'.$subdomainSource[0].'/'.\Config::get('constant.fonts').'/'.$fontMaster_data['font_filename_N'];
                                    $new_fontTemplate_file=public_path().'/'.$subdomainDest[0].'/'.\Config::get('constant.fonts').'/'.$fontMaster_data['font_filename_N'];
                                    if (!file_exists($new_fontTemplate_file)) { 
                                        \File::copy($old_fontTemplate_file,$new_fontTemplate_file);
                                    }
                                } 
                                elseif ($siteDataSource->new_server == 0 && $siteDataDest->new_server == 1) { // old sever ==> new server 
                                    $old_fontTemplate_file=public_path().'/'.$subdomainSource[0].'/'.\Config::get('constant.fonts').'/'.$fontMaster_data['font_filename_N'];
                                    $new_fontTemplate_file='/'.$subdomainDest[0].'/'.\Config::get('constant.fonts').'/'.$fontMaster_data['font_filename_N'];
                                    copy($old_fontTemplate_file, $ftpUrl.$new_fontTemplate_file);
                                }
                                elseif ($siteDataSource->new_server == 1 && $siteDataDest->new_server == 0) { // new server ==> old sever 
                                    $old_fontTemplate_file='/'.$subdomainSource[0].'/'.\Config::get('constant.fonts').'/'.$fontMaster_data['font_filename_N'];
                                    $new_fontTemplate_file='/'.$subdomainDest[0].'/'.\Config::get('constant.fonts').'/'.$fontMaster_data['font_filename_N'];
                                    
                                    // $new_fontTemplate_file=public_path().'/'.$subdomainDest[0].'/'.\Config::get('constant.fonts').'/'.$fontMaster_data['font_filename_N'];
                                    // copy($ftpUrl.$old_fontTemplate_file, $new_fontTemplate_file);

                                    $contents .= 'Font List : ';
                                    $contents .= "\n";
                                    $contents .= "From  (new server) : ".$old_fontTemplate_file;
                                    $contents .= "\n";
                                    $contents .= "To (old server) : ".$new_fontTemplate_file;
                                    $contents .= "\n";
                                    $contents .= "\n";

                                }
                                elseif ($siteDataSource->new_server == 1 && $siteDataDest->new_server == 1) { // new server ==> new server 
                                    $old_fontTemplate_file='/'.$subdomainSource[0].'/'.\Config::get('constant.fonts').'/'.$fontMaster_data['font_filename_N'];
                                    $new_fontTemplate_file='/'.$subdomainDest[0].'/'.\Config::get('constant.fonts').'/'.$fontMaster_data['font_filename_N'];
                                    //copy($ftpUrl.$old_fontTemplate_file, $ftpUrl.$new_fontTemplate_file);

                                    $contents .= 'Font List : ';
                                    $contents .= "\n";
                                    $contents .= "From  (new server) : ".$old_fontTemplate_file;
                                    $contents .= "\n";
                                    $contents .= "To (new server) : ".$new_fontTemplate_file;
                                    $contents .= "\n";
                                    $contents .= "\n";

                                }

                            }
                            if(!empty($fontMaster_data['font_filename_B'])){

                                if($siteDataSource->new_server == 0 && $siteDataDest->new_server == 0) {   // old sever ==> old sever
                                    $old_fontTemplate_file=public_path().'/'.$subdomainSource[0].'/'.\Config::get('constant.fonts').'/'.$fontMaster_data['font_filename_B'];
                                    $new_fontTemplate_file=public_path().'/'.$subdomainDest[0].'/'.\Config::get('constant.fonts').'/'.$fontMaster_data['font_filename_B'];
                                    if (!file_exists($new_fontTemplate_file)) {
                                        
                                        \File::copy($old_fontTemplate_file,$new_fontTemplate_file);
                                    }
                                } 
                                elseif ($siteDataSource->new_server == 0 && $siteDataDest->new_server == 1) { // old sever ==> new server 
                                    $old_fontTemplate_file=public_path().'/'.$subdomainSource[0].'/'.\Config::get('constant.fonts').'/'.$fontMaster_data['font_filename_B'];
                                    $new_fontTemplate_file='/'.$subdomainDest[0].'/'.\Config::get('constant.fonts').'/'.$fontMaster_data['font_filename_B'];
                                    copy($old_fontTemplate_file, $ftpUrl.$new_fontTemplate_file);
                                }
                                elseif ($siteDataSource->new_server == 1 && $siteDataDest->new_server == 0) { // new server ==> old sever 
                                    $old_fontTemplate_file='/'.$subdomainSource[0].'/'.\Config::get('constant.fonts').'/'.$fontMaster_data['font_filename_B'];
                                    $new_fontTemplate_file='/'.$subdomainDest[0].'/'.\Config::get('constant.fonts').'/'.$fontMaster_data['font_filename_B'];
                                    //$new_fontTemplate_file=public_path().'/'.$subdomainDest[0].'/'.\Config::get('constant.fonts').'/'.$fontMaster_data['font_filename_B'];
                                    //copy($ftpUrl.$old_fontTemplate_file, $new_fontTemplate_file);
                                    
                                    $contents .= 'Font List : ';
                                    $contents .= "\n";
                                    $contents .= "From  (new server) : ".$old_fontTemplate_file;
                                    $contents .= "\n";
                                    $contents .= "To (old server) : ".$new_fontTemplate_file;
                                    $contents .= "\n";
                                    $contents .= "\n";

                                }
                                elseif ($siteDataSource->new_server == 1 && $siteDataDest->new_server == 1) { // new server ==> new server 
                                    $old_fontTemplate_file='/'.$subdomainSource[0].'/'.\Config::get('constant.fonts').'/'.$fontMaster_data['font_filename_B'];
                                    $new_fontTemplate_file='/'.$subdomainDest[0].'/'.\Config::get('constant.fonts').'/'.$fontMaster_data['font_filename_B'];
                                    //copy($ftpUrl.$old_fontTemplate_file, $ftpUrl.$new_fontTemplate_file);

                                    $contents .= 'Font List : ';
                                    $contents .= "\n";
                                    $contents .= "From  (new server) : ".$old_fontTemplate_file;
                                    $contents .= "\n";
                                    $contents .= "To (new server) : ".$new_fontTemplate_file;
                                    $contents .= "\n";
                                    $contents .= "\n";

                                }
                                
                            }  
                            if(!empty($fontMaster_data['font_filename_I'])){

                                if($siteDataSource->new_server == 0 && $siteDataDest->new_server == 0) {   // old sever ==> old sever
                                    $old_fontTemplate_file=public_path().'/'.$subdomainSource[0].'/'.\Config::get('constant.fonts').'/'.$fontMaster_data['font_filename_I'];
                                    $new_fontTemplate_file=public_path().'/'.$subdomainDest[0].'/'.\Config::get('constant.fonts').'/'.$fontMaster_data['font_filename_I'];
                                    if (!file_exists($new_fontTemplate_file)) {
                                        
                                        \File::copy($old_fontTemplate_file,$new_fontTemplate_file);
                                    }
                                } 
                                elseif ($siteDataSource->new_server == 0 && $siteDataDest->new_server == 1) { // old sever ==> new server 
                                    $old_fontTemplate_file=public_path().'/'.$subdomainSource[0].'/'.\Config::get('constant.fonts').'/'.$fontMaster_data['font_filename_I'];
                                    $new_fontTemplate_file='/'.$subdomainDest[0].'/'.\Config::get('constant.fonts').'/'.$fontMaster_data['font_filename_I'];
                                    copy($old_fontTemplate_file, $ftpUrl.$new_fontTemplate_file);
                                }
                                elseif ($siteDataSource->new_server == 1 && $siteDataDest->new_server == 0) { // new server ==> old sever 
                                    $old_fontTemplate_file='/'.$subdomainSource[0].'/'.\Config::get('constant.fonts').'/'.$fontMaster_data['font_filename_I'];
                                    $new_fontTemplate_file='/'.$subdomainDest[0].'/'.\Config::get('constant.fonts').'/'.$fontMaster_data['font_filename_I'];
                                    
                                    // $new_fontTemplate_file=public_path().'/'.$subdomainDest[0].'/'.\Config::get('constant.fonts').'/'.$fontMaster_data['font_filename_I'];
                                    // copy($ftpUrl.$old_fontTemplate_file, $new_fontTemplate_file);
                                    $contents .= 'Font List : ';
                                    $contents .= "\n";
                                    $contents .= "From  (new server) : ".$old_fontTemplate_file;
                                    $contents .= "\n";
                                    $contents .= "To (old server) : ".$new_fontTemplate_file;
                                    $contents .= "\n";
                                    $contents .= "\n";

                                }
                                elseif ($siteDataSource->new_server == 1 && $siteDataDest->new_server == 1) { // new server ==> new server 
                                    $old_fontTemplate_file='/'.$subdomainSource[0].'/'.\Config::get('constant.fonts').'/'.$fontMaster_data['font_filename_I'];
                                    $new_fontTemplate_file='/'.$subdomainDest[0].'/'.\Config::get('constant.fonts').'/'.$fontMaster_data['font_filename_I'];
                                    //copy($ftpUrl.$old_fontTemplate_file, $ftpUrl.$new_fontTemplate_file);

                                    $contents .= 'Font List : ';
                                    $contents .= "\n";
                                    $contents .= "From  (new server) : ".$old_fontTemplate_file;
                                    $contents .= "\n";
                                    $contents .= "To (new server) : ".$new_fontTemplate_file;
                                    $contents .= "\n";
                                    $contents .= "\n";

                                }

                            }  
                            if(!empty($fontMaster_data['font_filename_BI'])){

                                if($siteDataSource->new_server == 0 && $siteDataDest->new_server == 0) {   // old sever ==> old sever
                                    $old_fontTemplate_file=public_path().'/'.$subdomainSource[0].'/'.\Config::get('constant.fonts').'/'.$fontMaster_data['font_filename_BI'];
                                    $new_fontTemplate_file=public_path().'/'.$subdomainDest[0].'/'.\Config::get('constant.fonts').'/'.$fontMaster_data['font_filename_BI'];
                                    if (!file_exists($new_fontTemplate_file)) {
                                        
                                        \File::copy($old_fontTemplate_file,$new_fontTemplate_file);
                                    }
                                } 
                                elseif ($siteDataSource->new_server == 0 && $siteDataDest->new_server == 1) { // old sever ==> new server 
                                    $old_fontTemplate_file=public_path().'/'.$subdomainSource[0].'/'.\Config::get('constant.fonts').'/'.$fontMaster_data['font_filename_BI'];
                                    $new_fontTemplate_file='/'.$subdomainDest[0].'/'.\Config::get('constant.fonts').'/'.$fontMaster_data['font_filename_BI'];
                                    copy($old_fontTemplate_file, $ftpUrl.$new_fontTemplate_file);
                                }
                                elseif ($siteDataSource->new_server == 1 && $siteDataDest->new_server == 0) { // new server ==> old sever 
                                    $old_fontTemplate_file='/'.$subdomainSource[0].'/'.\Config::get('constant.fonts').'/'.$fontMaster_data['font_filename_BI'];
                                    $new_fontTemplate_file='/'.$subdomainDest[0].'/'.\Config::get('constant.fonts').'/'.$fontMaster_data['font_filename_BI'];
                                    
                                    // $new_fontTemplate_file=public_path().'/'.$subdomainDest[0].'/'.\Config::get('constant.fonts').'/'.$fontMaster_data['font_filename_BI'];
                                    // copy($ftpUrl.$old_fontTemplate_file, $new_fontTemplate_file);
                                    $contents .= 'Font List : ';
                                    $contents .= "\n";
                                    $contents .= "From  (new server) : ".$old_fontTemplate_file;
                                    $contents .= "\n";
                                    $contents .= "To (old server) : ".$new_fontTemplate_file;
                                    $contents .= "\n";
                                    $contents .= "\n";

                                }
                                elseif ($siteDataSource->new_server == 1 && $siteDataDest->new_server == 1) { // new server ==> new server 
                                    $old_fontTemplate_file='/'.$subdomainSource[0].'/'.\Config::get('constant.fonts').'/'.$fontMaster_data['font_filename_BI'];
                                    $new_fontTemplate_file='/'.$subdomainDest[0].'/'.\Config::get('constant.fonts').'/'.$fontMaster_data['font_filename_BI'];
                                    //copy($ftpUrl.$old_fontTemplate_file, $ftpUrl.$new_fontTemplate_file);

                                    $contents .= 'Font List : ';
                                    $contents .= "\n";
                                    $contents .= "From  (new server) : ".$old_fontTemplate_file;
                                    $contents .= "\n";
                                    $contents .= "To (new server) : ".$new_fontTemplate_file;
                                    $contents .= "\n";
                                    $contents .= "\n";

                                }

                            }           
                        

                        }
                    }
                }
                
                if($get_file_aws_local_flag->file_aws_local == '1'){
                    /*file get all inside folder*/
                    $copy_Template_file=Storage::disk('s3')->allFiles($subdomainSource[0].'/'.\Config::get('constant.canvas').'/'.$template_id);
                    /*new Folder to Copy All File*/
                    $new_Template_file="backend/templates/".$copyTemplate_data['template_name'].'/';
                }
                else{

                    if($siteDataSource->new_server == 0 && $siteDataDest->new_server == 0) {   // old sever ==> old sever
                        $copy_Template_file= glob(public_path().'/'.$subdomainSource[0].'/backend/templates/'.$template_id."/*");
                        $new_Template_file=public_path().'/'.$subdomainDest[0]."/backend/templates/".$newTemplateId.'/';
                        if(!is_dir($new_Template_file)){
                            mkdir($new_Template_file, 0777);
                        }
                    } 
                    elseif ($siteDataSource->new_server == 0 && $siteDataDest->new_server == 1) { // old sever ==> new server 
                        $copy_Template_file= glob(public_path().'/'.$subdomainSource[0].'/backend/templates/'.$template_id."/*");
                        $new_Template_file='/'.$subdomainDest[0]."/backend/templates/".$newTemplateId.'/';

                        $folderExist = \Storage::disk('ftp_new')->exists($new_Template_file);
                        if(!$folderExist) {
                            // open an FTP connection
                            $connId = ftp_connect($ftpNewHost,$ftpNewPort) or die("Couldn't connect to $ftpHost");
                            // login to FTP server
                            $ftpLogin = ftp_login($connId, $ftpNewUsername, $ftpNewPassword);
                            
                            ftp_mkdir($connId, 'public'.$new_Template_file);
                        }


                    }
                    elseif ($siteDataSource->new_server == 1 && $siteDataDest->new_server == 0) { // new server ==> old sever 
                        $copy_Template_file= \Storage::disk('ftp_new')->files('/'.$subdomainSource[0].'/backend/templates/'.$template_id);

                        $new_Template_file=public_path().'/'.$subdomainDest[0]."/backend/templates/".$newTemplateId.'/';

                        if(!is_dir($new_Template_file)){
                            mkdir($new_Template_file, 0777);
                        }

                        $new_Template_file='/'.$subdomainDest[0]."/backend/templates/".$newTemplateId.'/';                        

                        
                    }
                    elseif ($siteDataSource->new_server == 1 && $siteDataDest->new_server == 1) { // new server ==> new server 
                        $copy_Template_file= \Storage::disk('ftp_new')->files('/'.$subdomainSource[0].'/backend/templates/'.$template_id);

                        //$copy_Template_file= \Storage::disk('ftp_new')->get(glob('/'.$subdomainSource[0].'/backend/templates/'.$template_id."/*"));
                        $new_Template_file='/'.$subdomainDest[0]."/backend/templates/".$newTemplateId.'/';

                        $folderExist = \Storage::disk('ftp_new')->exists($new_Template_file);
                        if(!$folderExist) {
                            // open an FTP connection
                            $connId = ftp_connect($ftpNewHost,$ftpNewPort) or die("Couldn't connect to $ftpHost");
                            // login to FTP server
                            $ftpLogin = ftp_login($connId, $ftpNewUsername, $ftpNewPassword);
                            
                            ftp_mkdir($connId, 'public'.$new_Template_file);
                        }

                    }

                    
                }

                foreach ($copy_Template_file as $key => $value) {

                    if($get_file_aws_local_flag->file_aws_local == '1'){
                        $image_name_get=str_replace('/'.$subdomainSource[0].'/'.\Config::get('constant.canvas').'/'.$old_Template_name.'/','', $value);

                        if (!Storage::disk('s3')->exists($value,$new_Template_file.$image_name_get)) {
                            # code...
                            Storage::disk('s3')->copy($value,$new_Template_file.$image_name_get);
                        }
                    } else {
                        
                        if($siteDataSource->new_server == 0 && $siteDataDest->new_server == 0) {   // old sever ==> old sever
                            $image_name_get=str_replace(public_path().'/'.$subdomainSource[0].'/backend/templates/'.$template_id.'/','', $value);
                            $destFile=$new_Template_file.$image_name_get;
                            if (!file_exists($destFile)) {
                                # code...    
                                \File::copy($value,$destFile);
                            }

                        } 
                        elseif ($siteDataSource->new_server == 0 && $siteDataDest->new_server == 1) { // old sever ==> new server 
                            $image_name_get=str_replace(public_path().'/'.$subdomainSource[0].'/backend/templates/'.$template_id.'/','', $value);
                            $destFile=$new_Template_file.$image_name_get;
                            copy($value, 'ftp://'.$ftpNewUsername.':'.$ftpNewPassword.'@'.$ftpNewHost.'/public'.$destFile);


                        
                        }
                        elseif ($siteDataSource->new_server == 1 && $siteDataDest->new_server == 0) { // new server ==> old sever 
                            $destFile=$new_Template_file.$image_name_get;
                            $contents .= 'Template files List : ';
                            $contents .= "\n";
                            $contents .= "From  (new server) : ".$value;
                            $contents .= "\n";
                            $contents .= "To (old server) : ".$new_Template_file;
                            $contents .= "\n";
                            $contents .= "\n";
                            // $image_name_get=str_replace(\Storage::disk('ftp_new')->get('/'.$subdomainSource[0].'/backend/templates/'.$template_id.'/'),'', $value);
                            // $destFile=$new_Template_file.$image_name_get;

                            // copy('ftp://'.$ftpNewUsername.':'.$ftpNewPassword.'@'.$ftpNewHost.'/public'.$value, $destFile);

                        }
                        elseif ($siteDataSource->new_server == 1 && $siteDataDest->new_server == 1) { // new server ==> new server 
                            $destFile=$new_Template_file.$image_name_get;
                            $contents .= 'Template files List : ';
                            $contents .= "\n";
                            $contents .= "From  (new server) : ".$value;
                            $contents .= "\n";
                            $contents .= "To (new server) : ".$new_Template_file;
                            $contents .= "\n";
                            $contents .= "\n";
                            // $image_name_get=str_replace(\Storage::disk('ftp_new')->get('/'.$subdomainSource[0].'/backend/templates/'.$template_id.'/'),'', $value);
                            // $destFile=$new_Template_file.$image_name_get;
                            // copy('ftp://'.$ftpNewUsername.':'.$ftpNewPassword.'@'.$ftpNewHost.'/public'.$value, 'ftp://'.$ftpNewUsername.':'.$ftpNewPassword.'@'.$ftpNewHost.'/public'.$destFile);
                        
                        }




                    }

                }

                file_put_contents(public_path().'/superadmin_changes_file.txt',$contents);
                return response()->json(['success'=>true,'msg'=>'Template copied successfully']);

            }
            else
            {
                return response()->json(['success'=>false,'msg'=>'This template already copied']);
            }


        }
    }

    public function textFileDownload(){
        $filename = public_path().'/superadmin_changes_file.txt';
  
        if (file_exists($filename)) {
            return \Response::download($filename);
        }
        unlink($filename);
  
      }
      


     public function viewtemplatespdf2pdf($instance)
    {
        $instancesList = Site::select(DB::raw('SUBSTRING_INDEX(sites.site_url, ".", 1) as site_url'));
        $instancesList = $instancesList->orderBy('site_url','ASC');   
         $instancesList = $instancesList->get();
            $instancesListArray=$instancesList->toArray();
        return view('superadmin.copytemplate.templateslistpdf2pdf',compact('instance','instancesListArray'));
    
        
    }

 public function searchForId($id, $array,$keyCheck) {
   foreach ($array as $key => $val) {
//    print_r($val);

       if (preg_replace('/\s+/', '',$val->$keyCheck) === preg_replace('/\s+/', '',$id)) {
           return $key;
       }
   }
   return -1;
}
    public function viewtemplateslistpdf2pdf(Request $request){

       if($request->ajax()){

            $where_str    = "1 = ?";
            $where_params = array(1); 

            if (!empty($request->input('sSearch')))
            {
                $search     = $request->input('sSearch');
                $where_str .= " and ( template_name like \"%{$search}%\"
                                 OR  publish like \"%{$search}%\""
                . ")";
            }  
           $instance=$request->get('instance');


        $subdomain = explode('.', $instance);

       
       if($subdomain[0] == 'demo')
        {
            $dbName = 'seqr_'.$subdomain[0];
        }
        else{
            $dbName = 'seqr_d_'.$subdomain[0];
        }
                            
             //for serial number
            DB::statement(DB::raw('set @rownum=0'));   
            $columns = [DB::raw('@rownum  := @rownum  + 1 AS rownum'),'template_name','publish','id'];
            
           

            $font_master_count = DB::table($dbName.'.uploaded_pdfs')
                        ->whereRaw($where_str, $where_params)
                        ->count();

            $fontMaster_list = DB::table($dbName.'.uploaded_pdfs')->select($columns)
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
            $fontMaster_list_array=$fontMaster_list->toArray();
           
             
            $response['iTotalDisplayRecords'] = $font_master_count;
            $response['iTotalRecords'] = $font_master_count;
            $response['sEcho'] = intval($request->input('sEcho'));
            $response['aaData'] = $fontMaster_list_array;
            

            
            return $response;
        }
        return view('superadmin.copytemplate.templateslistpdf2pdf');
    }

    public function copyTemplatePdf2pdf(Request $request,CheckUploadedFileOnAwsORLocalService $checkUploadedFileOnAwsOrLocal){
        
        $domainSource=$request->source_instance;
        $subdomainSource = explode('.', $domainSource);

        $domainDest=$request->instance;
        $subdomainDest = explode('.', $domainDest);

        if($subdomainSource[0] == 'demo')
        {
            $dbNameSource = 'seqr_'.$subdomainSource[0];
        }
        else{
            $dbNameSource = 'seqr_d_'.$subdomainSource[0];
        }

        // source database credientials
        $siteDataSource = Site::select('new_server')->where(DB::raw('SUBSTRING_INDEX(site_url, ".", 1)'),$subdomainSource[0])->first();
        $dbCredSource = '';
        if($siteDataSource->new_server == 0) {
            $dbCredSource = 'mysql';
        } else {
            $dbCredSource = 'mysql_new';
        }

        if($subdomainDest[0] == 'demo')
        {
            $dbNameDest = 'seqr_'.$subdomainDest[0];
        }
        else{
            $dbNameDest = 'seqr_d_'.$subdomainDest[0];
        }

        // destination database credientials
        $siteDataDest = Site::select('new_server')->where(DB::raw('SUBSTRING_INDEX(site_url, ".", 1)'),$subdomainDest[0])->first();
        $dbCredDest = '';
        if($siteDataDest->new_server == 0) {
            $dbCredDest = 'mysql';
        } else {
            $dbCredDest = 'mysql_new';
        } 

        $get_file_aws_local_flag = $checkUploadedFileOnAwsOrLocal->checkUploadedFileOnAwsORLocal();

        if(!empty($request['template_id']))
        {
         
            $template_id=$request['template_id'];
        
            //Source Template Data
            // $templateData=DB::connection($dbCredSource)->select(DB::connection($dbCredSource)->raw('SELECT * FROM `'.$dbNameSource.'`.`uploaded_pdfs` WHERE id ="'.$request['template_id'].'"'));
            // $copyTemplate_data=(array)$templateData[0];
            // unset($copyTemplate_data['id']);
            $sourceTableColumList = DB::connection($dbCredSource)->select(DB::connection($dbCredSource)->raw('SELECT GROUP_CONCAT(column_name ORDER BY ordinal_position) as columNames FROM information_schema.columns WHERE table_schema = "'.$dbNameSource.'" AND table_name = "uploaded_pdfs"'));
            $sourceTableColumListArray = (array)$sourceTableColumList[0];
            $sourceTableColumListArrayV1 =  explode(",",$sourceTableColumListArray['columNames']);


            $destTableColumList = DB::connection($dbCredDest)->select(DB::connection($dbCredDest)->raw('SELECT GROUP_CONCAT(column_name ORDER BY ordinal_position) as columNames FROM information_schema.columns WHERE table_schema = "'.$dbNameDest.'" AND table_name = "uploaded_pdfs"'));
            $destTableColumListArray = (array)$destTableColumList[0];
            $destTableColumListArrayV1 =  explode(",",$destTableColumListArray['columNames']);
            
            $arr = array_intersect($sourceTableColumListArrayV1,$destTableColumListArrayV1);
            
            $columnNames = implode(",", $arr);
            
            $templateData=DB::connection($dbCredSource)->select(DB::connection($dbCredSource)->raw('SELECT '.$columnNames.' FROM `'.$dbNameSource.'`.`uploaded_pdfs` WHERE id ="'.$request['template_id'].'"'));
            $copyTemplate_data=(array)$templateData[0];
            unset($copyTemplate_data['id']);

            

            $old_Template_name=$copyTemplate_data['template_name'];
            
            $copyTemplate_data['template_name']=$copyTemplate_data['template_name'];
            $copyTemplate_data['publish']=1;

            $extractor_details=$copyTemplate_data['extractor_details'];
            $placer_details=$copyTemplate_data['placer_details'];

            $alreadyCopyData=DB::connection($dbCredDest)->select(DB::connection($dbCredDest)->raw('SELECT * FROM `'.$dbNameDest.'`.`uploaded_pdfs` WHERE template_name ="'.$copyTemplate_data['template_name'].'"'));             
            
            $ftpNewHost = \Config::get('constant.seqr_new_ftp_host');
            $ftpNewPort = \Config::get('constant.seqr_new_ftp_port');
            $ftpNewUsername = \Config::get('constant.seqr_new_ftp_username');
            $ftpNewPassword = \Config::get('constant.seqr_new_ftp_pass'); 

            $ftpUrl = 'ftp://'.$ftpNewUsername.':'.$ftpNewPassword.'@'.$ftpNewHost.'/public';
            
            $filenameExist = public_path().'/superadmin_changes_file.txt';            
            if (file_exists($filenameExist)) {
                unlink($filenameExist);
            }

            $contents = '/********************* This Files to move manually *********************\ ';
            #already_copy check
            if(empty($alreadyCopyData))
            {
                $siteUrl=$domainDest.'.seqrdoc.com';
                //Site id
                $siteData=DB::connection($dbCredDest)->select(DB::connection($dbCredDest)->raw('SELECT site_id FROM `seqr_demo`.`sites` WHERE site_url ="'.$siteUrl.'"'));
                $site_id=$siteData[0]->site_id;

                if($siteDataSource->new_server == 0) { // old sever
                    $source_path_pdf=public_path()."/".$subdomainSource[0]."/".$copyTemplate_data['file_name'];
                } elseif($siteDataSource->new_server == 1) {  //new server 
                    $source_path_pdf=$ftpUrl."/".$subdomainSource[0]."/".$copyTemplate_data['file_name'];
                }
            
                //Destination template id
                $newTemplateId=DB::connection($dbCredDest)->table($dbNameDest.'.uploaded_pdfs')->insertGetId($copyTemplate_data);
                
                if($siteDataDest->new_server == 0) { // old server folder creation 
                    //Create required directories
                    $dest_path_pdf=public_path()."/".$subdomainDest[0]."/uploads/pdfs";
                    if(!is_dir($dest_path_pdf)){
                        //Directory does not exist, then create it.
                        mkdir($dest_path_pdf, 0777);
                    }

                    $dest_path_json=public_path()."/".$subdomainDest[0]."/documents";
                    if(!is_dir($dest_path_json)){
                        //Directory does not exist, then create it.
                        mkdir($dest_path_json, 0777);
                    }

                    $dest_path_images=public_path()."/".$subdomainDest[0]."/backend/templates/pdf2pdf_images";
                    if(!is_dir($dest_path_images)){
                        //Directory does not exist, then create it.
                        mkdir($dest_path_images, 0777);
                    }

                } elseif ($siteDataDest->new_server == 1) {  // new server folder creation  using FTP
                    // open an FTP connection
                    $ConnId = ftp_connect($ftpNewHost,$ftpNewPort) or die("Couldn't connect to $ftpHost");
                    // login to FTP server
                    $ftpLogin = ftp_login($ConnId, $ftpNewUsername, $ftpNewPassword);

                    $dest_path_pdf="/".$subdomainDest[0]."/uploads/pdfs";
                    $pdfFolderExist = \Storage::disk('ftp_new')->exists($dest_path_pdf);
                    
                    if(!$pdfFolderExist) {
                        ftp_mkdir($ConnId, 'public'.$dest_path_pdf);
                    }


                    // open an FTP connection
                    $ConnId1 = ftp_connect($ftpNewHost,$ftpNewPort) or die("Couldn't connect to $ftpHost");
                    // login to FTP server
                    $ftpLogin1 = ftp_login($ConnId1, $ftpNewUsername, $ftpNewPassword);
                    $dest_path_json="/".$subdomainDest[0]."/documents";
                    $jsonFolderExist = \Storage::disk('ftp_new')->exists($dest_path_json);
                    if(!$jsonFolderExist) {
                        ftp_mkdir($ConnId1, 'public'.$dest_path_json);
                    }

                    // open an FTP connection
                    $ConnId2 = ftp_connect($ftpNewHost,$ftpNewPort) or die("Couldn't connect to $ftpHost");
                    // login to FTP server
                    $ftpLogin2 = ftp_login($ConnId2, $ftpNewUsername, $ftpNewPassword);
                    $dest_path_images="/".$subdomainDest[0]."/backend/templates/pdf2pdf_images";
                    $imageFolderExist = \Storage::disk('ftp_new')->exists($dest_path_images);
                    if(!$imageFolderExist) {
                        ftp_mkdir($ConnId2, 'public'.$dest_path_images);
                    }

                }
                

                //PDF
                $fileNamePdf=$copyTemplate_data['file_name'];

                $tempFile = pathinfo($source_path_pdf);
                //$fileName=$newTemplateId.'_'.$tempFile['basename'];
                $fileName=$tempFile['basename'];

                $dest_path_pdf=$dest_path_pdf.'/'.$fileName;
                
                if($siteDataSource->new_server == 0 && $siteDataDest->new_server == 0) {   // old sever ==> old sever
                    if (!file_exists($dest_path_pdf)) {
                        # code...
                        \File::copy($source_path_pdf,$dest_path_pdf);
                    }
                }
                elseif ($siteDataSource->new_server == 0 && $siteDataDest->new_server == 1) { // old sever ==> new server 
                    $folderExist = \Storage::disk('ftp_new')->exists($dest_path_pdf);
                    if(!$folderExist) {
                        copy($source_path_pdf, $ftpUrl.$dest_path_pdf);
                    }
                }
                elseif ($siteDataSource->new_server == 1 && $siteDataDest->new_server == 0) { // new server ==> old sever 
                    $contents = '/********************* This Files to move manually *********************\ ';
                    $contents .= "\n";
                    $contents .= 'All PDF2PDF Template PDF files list : ';
                    $contents .= "\n";
                    $contents .= "From (new server) : ".$source_path_pdf;
                    $contents .= "\n";
                    $contents .= "To (old server) : ".$dest_path_pdf;
                    $contents .= "\n";
                    $contents .= "\n";

                }
                elseif ($siteDataSource->new_server == 1 && $siteDataDest->new_server == 1) { // new server ==> new server 
                    $contents = '/********************* This Files to move manually *********************\ ';
                    $contents .= "\n";
                    $contents .= 'All PDF2PDF Template PDF files list : ';
                    $contents .= "\n";
                    $contents .= "From  (new server) : ".$source_path_pdf;
                    $contents .= "\n";
                    $contents .= "To (new server) : ".$dest_path_pdf;
                    $contents .= "\n";
                    $contents .= "\n";

                }

                if($siteDataSource->new_server == 0) { // old server folder creation 
                    $source_path_json=public_path()."/".$subdomainSource[0]."/documents/".$copyTemplate_data['template_name'].'.json';
                }
                elseif($siteDataSource->new_server == 1) {
                    $source_path_json=$ftpUrl."/".$subdomainSource[0]."/documents/".$copyTemplate_data['template_name'].'.json';
                }
                

                $tempFile = pathinfo($source_path_json);
                //$fileName=$newTemplateId.'_'.$tempFile['basename'];
                $fileName=$tempFile['basename'];

                $dest_path_json=$dest_path_json.'/'.$fileName;
                
                if($siteDataSource->new_server == 0 && $siteDataDest->new_server == 0) {   // old sever ==> old sever
                    if (!file_exists($dest_path_json)) {
                        # code...
                        \File::copy($source_path_json,$dest_path_json);
                    }
                }
                elseif ($siteDataSource->new_server == 0 && $siteDataDest->new_server == 1) { // old sever ==> new server 
                    $folderExist = \Storage::disk('ftp_new')->exists($dest_path_json);
                    if(!$folderExist) {
                        copy($source_path_json, $ftpUrl.$dest_path_json);
                    }
                }
                elseif ($siteDataSource->new_server == 1 && $siteDataDest->new_server == 0) { // new server ==> old sever 
                    
                    $contents .= 'All PDF2PDF Template Json files list : ';
                    $contents .= "\n";
                    $contents .= "From (new server) : ".$source_path_json;
                    $contents .= "\n";
                    $contents .= "To (old server) : ".$dest_path_json;
                    $contents .= "\n";
                    $contents .= "\n";

                }
                elseif ($siteDataSource->new_server == 1 && $siteDataDest->new_server == 1) { // new server ==> new server 
                    
                    $contents .= 'All PDF2PDF Template Json files list : ';
                    $contents .= "\n";
                    $contents .= "From  (new server) : ".$source_path_json;
                    $contents .= "\n";
                    $contents .= "To (new server) : ".$dest_path_json;
                    $contents .= "\n";
                    $contents .= "\n";

                }



                if(!empty($placer_details)&&$placer_details!="[]"){
                    $data=json_decode($placer_details);
                    foreach ($data as $readData) {
                        if(isset($readData->image_path)&&!empty($readData->image_path)){
                            if($siteDataSource->new_server == 0 && $siteDataDest->new_server == 0) {   // old sever ==> old sever
                                $source_path_image=public_path()."/".$subdomainSource[0]."/backend/templates/pdf2pdf_images/".$readData->image_path;
                                $dest_path_image=public_path()."/".$subdomainDest[0]."/backend/templates/pdf2pdf_images/".$readData->image_path;
                                if (!file_exists($dest_path_image)) {
                                    # code...
                                    \File::copy($source_path_image,$dest_path_image);
                                }
                            }

                            elseif ($siteDataSource->new_server == 0 && $siteDataDest->new_server == 1) { // old sever ==> new server 
                                $source_path_image=public_path()."/".$subdomainSource[0]."/backend/templates/pdf2pdf_images/".$readData->image_path;
                                $dest_path_image="/".$subdomainDest[0]."/backend/templates/pdf2pdf_images/".$readData->image_path;
                                $folderExist = \Storage::disk('ftp_new')->exists($dest_path_image);
                                if (!$folderExist) {
                                    # code...
                                    \File::copy($source_path_image,$ftpUrl.$dest_path_image);
                                }
                            }
                            elseif ($siteDataSource->new_server == 1 && $siteDataDest->new_server == 0) { // new server ==> old sever 
                                $source_path_image="/".$subdomainSource[0]."/backend/templates/pdf2pdf_images/".$readData->image_path;
                                $dest_path_image=public_path()."/".$subdomainDest[0]."/backend/templates/pdf2pdf_images/".$readData->image_path;
                                $contents .= 'All PDF2PDF Template Image files list : ';
                                $contents .= "\n";
                                $contents .= "From (new server) : ".$source_path_image;
                                $contents .= "\n";
                                $contents .= "To (old server) : ".$dest_path_image;
                                $contents .= "\n";
                                $contents .= "\n";
                            }
                            elseif ($siteDataSource->new_server == 1 && $siteDataDest->new_server == 1) { // new server ==> new server 
                                $source_path_image="/".$subdomainSource[0]."/backend/templates/pdf2pdf_images/".$readData->image_path;
                                $dest_path_image="/".$subdomainDest[0]."/backend/templates/pdf2pdf_images/".$readData->image_path;
                                $contents .= 'All PDF2PDF Template Image files list : ';
                                $contents .= "\n";
                                $contents .= "From  (new server) : ".$source_path_image;
                                $contents .= "\n";
                                $contents .= "To (new server) : ".$dest_path_image;
                                $contents .= "\n";
                                $contents .= "\n";
                
                            }

                        }
                    }
                }

                if(!empty($extractor_details)&&$extractor_details!="[]"){
                    $data=json_decode($extractor_details);
                    foreach ($data as $readData) {
                        if(isset($readData->image_path)&&!empty($readData->image_path)){
                            if($siteDataSource->new_server == 0 && $siteDataDest->new_server == 0) {   // old sever ==> old sever
                                $source_path_image=public_path()."/".$subdomainSource[0]."/backend/templates/pdf2pdf_images/".$readData->image_path;
                                $dest_path_image=public_path()."/".$subdomainDest[0]."/backend/templates/pdf2pdf_images/".$readData->image_path;
                                if (!file_exists($dest_path_image)) {
                                    # code...       
                                    \File::copy($source_path_image,$dest_path_image);
                                }
                            }
                            elseif ($siteDataSource->new_server == 0 && $siteDataDest->new_server == 1) { // old sever ==> new server 
                                $source_path_image=public_path()."/".$subdomainSource[0]."/backend/templates/pdf2pdf_images/".$readData->image_path;
                                $dest_path_image="/".$subdomainDest[0]."/backend/templates/pdf2pdf_images/".$readData->image_path;
                                $folderExist = \Storage::disk('ftp_new')->exists($dest_path_image);
                                if (!$folderExist) {
                                    # code...
                                    \File::copy($source_path_image,$ftpUrl.$dest_path_image);
                                }

                            }
                            elseif ($siteDataSource->new_server == 1 && $siteDataDest->new_server == 0) { // new server ==> old sever 
                                $source_path_image="/".$subdomainSource[0]."/backend/templates/pdf2pdf_images/".$readData->image_path;
                                $dest_path_image=public_path()."/".$subdomainDest[0]."/backend/templates/pdf2pdf_images/".$readData->image_path;

                                $contents .= 'All PDF2PDF Template Image files list : ';
                                $contents .= "\n";
                                $contents .= "From  (new server) : ".$source_path_image;
                                $contents .= "\n";
                                $contents .= "To (new server) : ".$dest_path_image;
                                $contents .= "\n";
                                $contents .= "\n";
                            }
                            elseif ($siteDataSource->new_server == 1 && $siteDataDest->new_server == 1) { // new server ==> new server 
                                $source_path_image="/".$subdomainSource[0]."/backend/templates/pdf2pdf_images/".$readData->image_path;
                                $dest_path_image="/".$subdomainDest[0]."/backend/templates/pdf2pdf_images/".$readData->image_path;
                                $contents .= 'All PDF2PDF Template Image files list : ';
                                $contents .= "\n";
                                $contents .= "From  (new server) : ".$source_path_image;
                                $contents .= "\n";
                                $contents .= "To (new server) : ".$dest_path_image;
                                $contents .= "\n";
                                $contents .= "\n";
                            }

                        }
                    }
                }
                    
                
                DB::connection($dbCredDest)->select(DB::connection($dbCredDest)->raw('UPDATE `'.$dbNameDest.'`.`uploaded_pdfs` SET file_name="'.$fileNamePdf.'" WHERE id="'.$newTemplateId.'"')); 
                file_put_contents(public_path().'/superadmin_changes_file.txt',$contents);

                return response()->json(['success'=>true,'msg'=>'Template copied successfully']);

            }
            else
            {
                return response()->json(['success'=>false,'msg'=>'This template already copied']);
            }

        }
    }
}
