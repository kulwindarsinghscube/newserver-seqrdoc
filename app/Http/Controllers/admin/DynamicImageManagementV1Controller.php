<?php

//Author :: Aakashi Modi
//Date :: 15-11-2019
namespace App\Http\Controllers\admin;

use App\Events\DynamicImageEvent;
use App\Http\Controllers\Controller;
use App\models\ImageDeleteHistory;
use App\models\TemplateMaster;
use App\models\FieldMaster;
use Config,File;
use Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Auth;
use App\Library\Services\CheckUploadedFileOnAwsORLocalService;
use App\models\SystemConfig;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;
use DB;

class DynamicImageManagementV1Controller extends Controller
{
	//displaying template folder left hand side
    public function index(CheckUploadedFileOnAwsORLocalService $checkUploadedFileOnAwsOrLocal){

    	$domain = \Request::getHost();
        $subdomain = explode('.', $domain);

    	$get_file_aws_local_flag = $checkUploadedFileOnAwsOrLocal->checkUploadedFileOnAwsORLocal();
    	//get data from template master for displaying folder name
        $auth_site_id=Auth::guard('admin')->user()->site_id;
        $systemConfig = SystemConfig::where('site_id',$auth_site_id)->first();

    	$get_template_data = TemplateMaster::select('template_name','id','actual_template_name')->where('status',1)->orderBy('template_name','asc')->where('site_id',$auth_site_id)->get()->toArray();
		
		
		$template_data = [];
    	foreach ($get_template_data as $template_key => $template_value) {
    		//store template name in variable
    		$template_data[$template_key]['template_name'] = $template_value['actual_template_name'];
    		$template_data[$template_key]['template_id'] = $template_value['id'];
    		
			if($get_file_aws_local_flag->file_aws_local == '1'){
				$all_files = Storage::disk('s3')->allFiles($subdomain[0].'/'.\Config::get('constant.template').'/'.$template_value['id']);
				$files = [];
				$ext_array = ['jpg','png','JPG','PNG','JPEG','jpeg','gif'];
				foreach ($all_files as $all_fileskey => $all_filesvalue) {
					$explode_file = explode('.',$all_filesvalue);
					if(in_array($explode_file[1], $ext_array)){
						$files[] = $all_filesvalue;
					}
				}
				$get_template_folder_image_count = count($files);
				
			}
			else{
				$get_template_folder_image_count = count(glob(public_path().'/'.$subdomain[0].'/backend/templates/'.$template_value['id']."/{*.jpg,*.png,*.JPG,*.PNG,*.JPEG,*.jpeg}", GLOB_BRACE));

			}
    		//store count in variable
    		$template_data[$template_key]['count'] = $get_template_folder_image_count;
    	}

        
		if($subdomain[0]=="demo"){
			$get_template_folder_image_count = count(glob(public_path()."/".$subdomain[0]."/backend/templates/excel2pdf_images/*.{jpg,png,JPG,PNG,JPEG,jpeg,gif}", GLOB_BRACE));
            $template_data[$template_key+2]['template_name']='excel2pdf_images';
            $template_data[$template_key+2]['template_id']='excel2pdf_images';
            $template_data[$template_key+2]['count']=$get_template_folder_image_count;
		}

        //Instance images
		if(!isset($template_key)){$template_key=-1;}
		$get_template_folder_image_count = count(glob(public_path()."/".$subdomain[0]."/backend/templates/100/*.{jpg,png,JPG,PNG,JPEG,jpeg,gif}", GLOB_BRACE));
		$template_data[$template_key+1]['template_name']=$subdomain[0].'_custom';
		$template_data[$template_key+1]['template_id']=100;
		$template_data[$template_key+1]['count']=$get_template_folder_image_count;

		$get_template_folder_image_count = count(glob(public_path()."/".$subdomain[0]."/backend/templates/pdf2pdf_images/*.{jpg,png,JPG,PNG,JPEG,jpeg,gif}", GLOB_BRACE));
		$template_data[$template_key+2]['template_name']='pdf2pdf_images';
		$template_data[$template_key+2]['template_id']='pdf2pdf_images';
		$template_data[$template_key+2]['count']=$get_template_folder_image_count;
    	

		$get_excel2pdf_template_data = DB::table('uploaded_pdfs')
		->select('template_name', 'id')
		->where('map_type','=',1)
		->orderBy('template_name','asc')
		->get()->toArray();
		
		$excel2pdf_template_data = [];
    	foreach ($get_excel2pdf_template_data as $excel2pdf_template_key => $excel2pdf_template_value) {
    		//store template name in variable

			// $get_template_data = DB::table('uploaded_pdfs')
			// ->select('template_name', 'id')
			// ->where('map_type','=',1)
			// ->where('id','=',$excel2pdf_template_value->template_id)
			// ->first();

    		$excel2pdf_template_data[$excel2pdf_template_key]['template_name'] = $excel2pdf_template_value->template_name.'_excel2pdf';
    		$excel2pdf_template_data[$excel2pdf_template_key]['template_id'] = $excel2pdf_template_value->id;
    		
			if($get_file_aws_local_flag->file_aws_local == '1'){
				$all_files = Storage::disk('s3')->allFiles($subdomain[0].'/'.\Config::get('constant.template').'/'.$excel2pdf_template_value->id);
				$files = [];
				$ext_array = ['jpg','png','JPG','PNG','JPEG','jpeg','gif'];
				foreach ($all_files as $all_fileskey => $all_filesvalue) {
					$explode_file = explode('.',$all_filesvalue);
					if(in_array($explode_file[1], $ext_array)){
						$files[] = $all_filesvalue;
					}
				}
				$get_template_folder_image_count = count($files);
			}else{
				$get_template_folder_image_count = count(glob(public_path().'/'.$subdomain[0].'/backend/templates/excel2pdf/'.$excel2pdf_template_value->id."/{*.jpg,*.png,*.JPG,*.PNG,*.JPEG,*.jpeg}", GLOB_BRACE));
			}
	    	
    		//store count in variable
    		$excel2pdf_template_data[$excel2pdf_template_key]['count'] = $get_template_folder_image_count;
    	}

    	return view('admin.dynamicImageManagementV1.index',compact('template_data','excel2pdf_template_data'));
    }

    //after selecting image upload in folder and save to db
    public function store(Request $request,CheckUploadedFileOnAwsORLocalService $checkUploadedFileOnAwsOrLocal){
    	//get all data
    	$dynamic_image_data = $request->all();
    	//call event for uploading image in folder and get response from listener that is coming from job
    	
		$get_file_aws_local_flag = $checkUploadedFileOnAwsOrLocal->checkUploadedFileOnAwsORLocal();
        $auth_site_id=\Auth::guard('admin')->user()->site_id;
        $systemConfig = SystemConfig::where('site_id',$auth_site_id)->first();

        $domain = \Request::getHost();
        $subdomain = explode('.', $domain);
        // $dynamic_image_data = $this->dynamic_image_data;
        $folder_name = $dynamic_image_data['folder_name'];

        //valid extensions
        $extensions = array('jpg','png','jpeg','gif','JPG','PNG','JPEG');
        //define path of folder
		if($get_file_aws_local_flag->file_aws_local == '1'){
			$targetDir = '/'.$subdomain[0].'/'.Config::get('constant.template').'/'.$folder_name.'/';
			$target_local_dir='/'.Config::get('constant.template').'/'.$folder_name.'/';
		}
		else{
			$targetDir =  public_path().'/'.$subdomain[0].'/'.Config::get('constant.template').'/'.$folder_name.'/';
			$target_local_dir= public_path().'/'.Config::get('constant.template').'/'.$folder_name.'/';
		}
  
		//if folder name is not exist then make folder
		if (!($target_local_dir)) {
			mkdir($target_local_dir, 0777, true);
		}
		$alreadyExistImages=array();
		//check image array is not empty
		if(!empty($dynamic_image_data['image_upload'])){
			$imageCount = 0;
			foreach($dynamic_image_data['image_upload'] as $image_key=>$image_value){
				//get uploaded image name
				$image_name = $image_value->getClientOriginalName();
				//upload folde path
				$targetFilePath = $targetDir.$image_name;
				//check file already exist in folder if yes then increment count
				
				// check image exists or not
				$fileExists=false;
				if($get_file_aws_local_flag->file_aws_local == '1'){
					if(Storage::disk('s3')->exists($targetFilePath)){
						$fileExists=true;
					}
				}else{
					if(file_exists($targetFilePath)){
						$fileExists=true;
					}
				}

				if(!$fileExists){

					$upload_image=$dynamic_image_data['image_upload'][$image_key];
					//get uploaded image name
					$image_name = $image_value->getClientOriginalName();
					//upload folde path
					$targetFilePath = $targetDir.$image_name;
					//get extension of image
					$ext=pathinfo($targetFilePath,PATHINFO_EXTENSION);
					
					//check extension is in our predefined extension array or not               
					if(in_array($ext, $extensions)){
						
						if($get_file_aws_local_flag->file_aws_local == '1'){
							$filePath ='/'.$subdomain[0].'/'.Config::get('constant.template').'/'.$folder_name.'/'.$image_name;
							Storage::disk('s3')->put($filePath, file_get_contents($upload_image));
						}
						else{
							$filePath = public_path().'/'.$subdomain[0].'/'.Config::get('constant.template').'/'.$folder_name.'/'.$image_name;
							
							$path = public_path($subdomain[0] . '/' . Config::get('constant.template') . '/' . $folder_name);

							if (!File::exists($path)) {
								File::makeDirectory($path, 0777, true, true);
							}

							\File::copy($upload_image,$filePath);

							// Insert into DB (example table: dynamic_image_managemant)
							DB::table('dynamic_image_managemant')->insert([
								'template_id' => $folder_name,
								'filename'    => $image_name,
								'created_at'  => now(),
								'updated_at'  => now(),
							]);
							
						}
						
					}
				}else{//if already exist
					array_push($alreadyExistImages, $image_name);
				}
				$imageCount++;
			}
		}
		
		if($get_file_aws_local_flag->file_aws_local == '1'){
			$targetDir =$subdomain[0].'/'.Config::get('constant.template').'/'.$folder_name;
		}else{
			$targetDir =public_path().'/'.$subdomain[0].'/'.Config::get('constant.template').'/'.$folder_name;
		}
		
		if($get_file_aws_local_flag->file_aws_local == '1'){
			//get image count after uploading image
			$all_files = Storage::disk('s3')->allFiles($targetDir);
			$files = [];
			$ext_array = ['jpg','png','JPG','PNG','JPEG','jpeg'];
			foreach ($all_files as $all_fileskey => $all_filesvalue) {
				$explode_file = explode('.',$all_filesvalue);
				if(in_array($explode_file[1], $ext_array)){
					$files[] = $all_filesvalue;
				}
			}
			$imageCounts = count($files);
		}
		else{
			$files = glob($targetDir."/*.{jpg,png,JPG,PNG,JPEG,'jpeg'}", GLOB_BRACE);
			$imageCounts = count($files);
		}
		
		if(!empty($alreadyExistImages)){
			$namef=$folder_name.date('YmdHis');
			$ext = ".txt";
			$txt='Already Existed Images :'."\n\n";
			$txt .=implode("\n", $alreadyExistImages);
			//$txt=implode("\n", $alreadyExistImages);

			$filename =  $targetDir.'/'.$namef.$ext;

			$file = fopen($filename,"w+");

			fwrite($file,$txt);

			fclose($file);

			// chmod($file,0777);
			$protocol = isset($_SERVER["HTTPS"]) ? 'https' : 'http';
			$path = $protocol.'://'.$subdomain[0].'.'.$subdomain[1].'.com/';
			$alreadyExistImagesFile=$path.$subdomain[0]."/backend/templates/".$folder_name.'/'.$namef.$ext;
		}else{
			$alreadyExistImagesFile="";
		}

		$uploaded_image_response = Array('success' => 'true', 'message' => 'image upload','folder_name'=>$folder_name,'imageCounts'=>$imageCounts,'alreadyExistImages'=>$alreadyExistImagesFile);
        
    	return response()->json(['data'=>$uploaded_image_response]);
    }


	//after selecting image upload in folder and save to db
    public function excel2pdfStore(Request $request,CheckUploadedFileOnAwsORLocalService $checkUploadedFileOnAwsOrLocal){
    	//get all data
    	$dynamic_image_data = $request->all();
    	//call event for uploading image in folder and get response from listener that is coming from job
    	// $uploaded_image_response = Event::dispatch(new DynamicImageEvent($dynamic_image_data));


		$get_file_aws_local_flag = $checkUploadedFileOnAwsOrLocal->checkUploadedFileOnAwsORLocal();
        $auth_site_id=\Auth::guard('admin')->user()->site_id;
        $systemConfig = SystemConfig::where('site_id',$auth_site_id)->first();

        $domain = \Request::getHost();
        $subdomain = explode('.', $domain);

        // $dynamic_image_data = $this->dynamic_image_data;

        $folder_name = $dynamic_image_data['folder_name_excel2pdf'];

		
		
        //valid extensions
        $extensions = array('jpg','png','jpeg','gif','JPG','PNG','JPEG');
        //define path of folder
        
        
		if($get_file_aws_local_flag->file_aws_local == '1'){
			$targetDir = '/'.$subdomain[0].'/backend/templates/excel2pdf/'.$folder_name.'/';
			$target_local_dir='/backend/templates/excel2pdf/'.$folder_name.'/';
		}
		else{
			$targetDir =  public_path().'/'.$subdomain[0].'/backend/templates/excel2pdf/'.$folder_name.'/';
			$target_local_dir= public_path().'/backend/templates/excel2pdf/'.$folder_name.'/';
		}
        
		

       

		//if folder name is not exist then make folder
		// if (!($targetDir)) {
		// 	mkdir($targetDir, 0777, true);
		// }
		// if (!($target_local_dir)) {
		// 	mkdir($target_local_dir, 0777, true);
		// }
		

		if(!is_dir($targetDir)){
			mkdir($targetDir, 0777, true);
		}

		if(!is_dir($target_local_dir)){
			mkdir($target_local_dir, 0777, true);
		}

		$alreadyExistImages=array();
		//check image array is not empty
		if(!empty($dynamic_image_data['image_upload_excel2pdf'])){
			$imageCount = 0;
			foreach($dynamic_image_data['image_upload_excel2pdf'] as $image_key=>$image_value){
				

				//get uploaded image name
				$image_name = $image_value->getClientOriginalName();
				//upload folde path
				$targetFilePath = $targetDir.$image_name;
				
				// echo $targetFilePath;
				// die();
				//check file already exist in folder if yes then increment count
				
				// check image exists or not
				$fileExists=false;
				if($get_file_aws_local_flag->file_aws_local == '1'){
					if(Storage::disk('s3')->exists($targetFilePath)){
						$fileExists=true;
					}
				}else{
					if(file_exists($targetFilePath)){
						$fileExists=true;
					}
				}

				if(!$fileExists){

					$upload_image=$dynamic_image_data['image_upload_excel2pdf'][$image_key];
					//get uploaded image name
					$image_name = $image_value->getClientOriginalName();
					//upload folde path
					$targetFilePath = $targetDir.$image_name;
					//get extension of image
					$ext=pathinfo($targetFilePath,PATHINFO_EXTENSION);
					
					//check extension is in our predefined extension array or not               
					if(in_array($ext, $extensions)){
						
						if($get_file_aws_local_flag->file_aws_local == '1'){
							$filePath ='/'.$subdomain[0].'/backend/templates/excel2pdf/'.$folder_name.'/'.$image_name;
							Storage::disk('s3')->put($filePath, file_get_contents($upload_image));

							// Insert into DB (example table: dynamic_image_managemant)
							DB::table('dynamic_image_managemant')->insert([
								'template_id' => $folder_name,
								'filename'    => $image_name,
								'map_type'    => 1,
								'created_at'  => now(),
								'updated_at'  => now(),
							]);
						}
						else{
							$filePath = public_path().'/'.$subdomain[0].'/backend/templates/excel2pdf/'.$folder_name.'/'.$image_name;
							$filePathFolder = public_path().'/'.$subdomain[0].'/backend/templates/excel2pdf/'.$folder_name;
							if(!is_dir($filePathFolder)){
								mkdir($filePathFolder, 0777,true);
							}

							\File::copy($upload_image,$filePath);
							// Insert into DB (example table: dynamic_image_managemant)
							DB::table('dynamic_image_managemant')->insert([
								'template_id' => $folder_name,
								'filename'    => $image_name,
								'map_type'    => 1,
								'created_at'  => now(),
								'updated_at'  => now(),
							]);

						}
						
					}

				}else{//if already exist
					array_push($alreadyExistImages, $image_name);
				}
				$imageCount++;
			}
		}
        if($get_file_aws_local_flag->file_aws_local == '1'){ 
			$targetDir =$subdomain[0].'/backend/templates/excel2pdf/'.$folder_name;
		}
		else{
			$targetDir =public_path().'/'.$subdomain[0].'/backend/templates/excel2pdf/'.$folder_name;
		}
		
		if($get_file_aws_local_flag->file_aws_local == '1'){
			//get image count after uploading image
			$all_files = Storage::disk('s3')->allFiles($targetDir);
			$files = [];
			$ext_array = ['jpg','png','JPG','PNG','JPEG','jpeg'];
			foreach ($all_files as $all_fileskey => $all_filesvalue) {
				$explode_file = explode('.',$all_filesvalue);
				if(in_array($explode_file[1], $ext_array)){
					$files[] = $all_filesvalue;
				}
			}
			$imageCounts = count($files);
		}
		else{
			$files = glob($targetDir."/*.{jpg,png,JPG,PNG,JPEG,'jpeg'}", GLOB_BRACE);
			$imageCounts = count($files);
		}
            
		if(!empty($alreadyExistImages)){
			$namef=$folder_name.date('YmdHis');
			$ext = ".txt";
			$txt='Already Existed Images :'."\n\n";
			$txt .=implode("\n", $alreadyExistImages);
			//$txt=implode("\n", $alreadyExistImages);

			$filename =  $targetDir.'/'.$namef.$ext;
			$file = fopen($filename,"w+");
			fwrite($file,$txt);
			fclose($file);

			// chmod($file,0777);
			$protocol = isset($_SERVER["HTTPS"]) ? 'https' : 'http';
			$path = $protocol.'://'.$subdomain[0].'.'.$subdomain[1].'.com/';
			$alreadyExistImagesFile=$path.$subdomain[0]."/backend/templates/excel2pdf/".$folder_name.'/'.$namef.$ext;
		}else{
			$alreadyExistImagesFile="";
		}
            

        $message = Array('success' => 'true', 'message' => 'image upload','folder_name'=>$folder_name,'imageCounts'=>$imageCounts,'alreadyExistImages'=>$alreadyExistImagesFile);
       
        // return $message;

    	return response()->json(['data'=>$message]);
    }



    //on selecting folder displaying image
    public function displayImage_old($sortBy,$value,$searchkey='',CheckUploadedFileOnAwsORLocalService $checkUploadedFileOnAwsOrLocal){

    	$get_file_aws_local_flag = $checkUploadedFileOnAwsOrLocal->checkUploadedFileOnAwsORLocal();
    	$auth_site_id=Auth::guard('admin')->user()->site_id;
        $systemConfig = SystemConfig::where('site_id',$auth_site_id)->first();
    	$domain = \Request::getHost();
        $subdomain = explode('.', $domain);

        $template_name = TemplateMaster::where('id',$value)->value('template_name');
        $folder_name = $value;
    
    	//arrray of image name that is inside folder
    	$filenameArray = [];
  
		if($get_file_aws_local_flag->file_aws_local == '1'){
			// get all file to Array
			$filenameArray=Storage::disk('s3')->allFiles($subdomain[0].'/backend/templates/'.$folder_name);
		}
		else{
			if(!empty($searchkey)){
				$filenameArray=glob(public_path().'/'.$subdomain[0].'/backend/templates/'.$folder_name."/".$searchkey."*.{jpg,JPG,png,PNG,jpeg,JPEG}",GLOB_BRACE);
			}else{
				$filenameArray=glob(public_path().'/'.$subdomain[0].'/backend/templates/'.$folder_name."/*.{jpg,JPG,png,PNG,jpeg,JPEG}",GLOB_BRACE);	
			}
		} 
	
		// remove  path on array only get file name
		foreach ($filenameArray as $key => $value) {
			if($get_file_aws_local_flag->file_aws_local == '1'){
				$filenameArray[$key]=str_replace($subdomain[0].'/backend/templates/'.$folder_name.'/','', $value);
			}
			else{
				$filenameArray[$key]=str_replace(public_path().'/'.$subdomain[0].'/backend/templates/'.$folder_name.'/','', $value);
			}
		}
     	
     
		if($sortBy == 'atoz'){
			$sort = natcasesort($filenameArray);    
		    $sortArray = [];
		  	foreach($filenameArray as $x=>$x_value)
		   	{
			   	array_push($sortArray, $x_value);
		   	}
		   	
		   	return response()->json(['data'=>$sortArray,'get_file_aws_local_flag'=>$get_file_aws_local_flag,'systemConfig'=>$systemConfig['sandboxing'],'template_name'=>$template_name]);
		}else{
			//if date wise sorting
			$dateArray = [];
			foreach ($filenameArray as $key => $value) {

	            	if($get_file_aws_local_flag->file_aws_local == '1'){
	            		$gettime = Storage::disk('s3')->lastModified($subdomain[0].'/backend/templates/'.$folder_name.'/'.$value);
	             	}
	             	else{
	             		$gettime = filemtime(public_path().'/'.$subdomain[0].'/backend/templates/'.$folder_name.'/'.$value);
	             	}
	            
				$created_date = date('F d Y H:i:s',$gettime);
				$dateArray[$key]['created_date'] = date("Y-m-d H:i:s", strtotime($created_date));
				$dateArray[$key]['image_name'] = $value;
			}
			//get images by comparing time
			usort($dateArray, array($this, "compareByTimeStamp"));
			//revers image array
			$array_reverse = array_reverse($dateArray);
			
			$sorting_images = [];
			//push data in variable
			foreach ($array_reverse as $key => $sort_array) {
				array_push($sorting_images, $sort_array['image_name']);
			}
			
		   	return response()->json(['data'=>$sorting_images,'get_file_aws_local_flag'=>$get_file_aws_local_flag,'systemConfig'=>$systemConfig['sandboxing'],'template_name'=>$template_name]);
	    }
	}



	public function displayImage($sortBy, $value, $searchkey = '', CheckUploadedFileOnAwsORLocalService $checkUploadedFileOnAwsOrLocal)
	{
		$get_file_aws_local_flag = $checkUploadedFileOnAwsOrLocal->checkUploadedFileOnAwsORLocal();
		$auth_site_id = Auth::guard('admin')->user()->site_id;
		$systemConfig = SystemConfig::where('site_id', $auth_site_id)->first();

		$domain    = \Request::getHost();
		$subdomain = explode('.', $domain);

		
		$template_name = TemplateMaster::where('id', $value)->value('template_name');
		$folder_name   = $value;

		// Base path logic
		if ($systemConfig['sandboxing'] == '1') {
			$basePath = ($get_file_aws_local_flag->file_aws_local == '1')
				? \Config::get('constant.amazone_path') . $subdomain[0] . '/' . Config::get('constant.sandbox') . '/'
				: \Config::get('constant.local_base_path') . $subdomain[0] . '/' . Config::get('constant.sandbox') . '/';
		} else {
			$basePath = ($get_file_aws_local_flag->file_aws_local == '1')
				? \Config::get('constant.amazone_path') . $subdomain[0] . '/' . Config::get('constant.template') . '/'
				: \Config::get('constant.local_base_path') . $subdomain[0] . '/' . Config::get('constant.template') . '/';
		}

		// Fetch images from DB
		$query = DB::table('dynamic_image_managemant')
			->select('filename', 'created_at')
			->where('template_id', $folder_name)
			->where('status', 1);

		if (!empty($searchkey)) {
			$query->where('filename', 'like', $searchkey . '%');
		}

		$images = $query->get();

		// Build array
		$imageArray = [];
		foreach ($images as $img) {
			$imageArray[] = [
				'filename'     => $img->filename,
				// 'path'         => $basePath . $folder_name . '/' . $img->filename,
				'path'         => $img->filename,
				'created_date' => $img->created_at ? date("Y-m-d H:i:s", strtotime($img->created_at)) : date("Y-m-d H:i:s"),
			];
		}

		if ($sortBy == 'atoz') {
			// Sort alphabetically
			usort($imageArray, function ($a, $b) {
				return strcasecmp($a['filename'], $b['filename']);
			});

			$sorted = array_map(function ($img) {
				return $img['path']; // only return path like old code returned filenames
			}, $imageArray);

			
			if(!$template_name && $value == 100 ){
				$template_name = 'Demo_custom';
			}

			return response()->json([
				'data'                  => $sorted,
				'get_file_aws_local_flag' => $get_file_aws_local_flag,
				'systemConfig'          => $systemConfig['sandboxing'],
				'template_name'         => $template_name
			]);
		} else {
			// Sort by created date (latest first)
			usort($imageArray, function ($a, $b) {
				return strtotime($a['created_date']) <=> strtotime($b['created_date']);
			});
			$imageArray = array_reverse($imageArray);

			$sorted = array_map(function ($img) {
				return $img['path'];
			}, $imageArray);


			
			if(!$template_name && $value == 100 ){
				$template_name = 'Demo_custom';
			}

			return response()->json([
				'data'                  => $sorted,
				'get_file_aws_local_flag' => $get_file_aws_local_flag,
				'systemConfig'          => $systemConfig['sandboxing'],
				'template_name'         => $template_name
			]);
		}
	}






	//on selecting folder displaying image
    public function excel2pdfDisplayImage($sortBy,$value,$searchkey='',CheckUploadedFileOnAwsORLocalService $checkUploadedFileOnAwsOrLocal){

    	$get_file_aws_local_flag = $checkUploadedFileOnAwsOrLocal->checkUploadedFileOnAwsORLocal();
		$auth_site_id = Auth::guard('admin')->user()->site_id;
		$systemConfig = SystemConfig::where('site_id', $auth_site_id)->first();

		$domain    = \Request::getHost();
		$subdomain = explode('.', $domain);

		
		$template_name = DB::table('uploaded_pdfs')->where('id', $value)->value('template_name');
		// $template_name = $template_name.'_excel2pdf';
		$folder_name   = $value;

		// Base path logic
		if ($systemConfig['sandboxing'] == '1') {
			$basePath = ($get_file_aws_local_flag->file_aws_local == '1')
				? \Config::get('constant.amazone_path') . $subdomain[0] . '/' . Config::get('constant.sandbox') . '/'
				: \Config::get('constant.local_base_path') . $subdomain[0] . '/backend/templates/excel2pdf/';
		} else {
			$basePath = ($get_file_aws_local_flag->file_aws_local == '1')
				? \Config::get('constant.amazone_path') . $subdomain[0] . '/' . Config::get('constant.template') . '/'
				: \Config::get('constant.local_base_path') . $subdomain[0] . '/backend/templates/excel2pdf/';
		}

		// Fetch images from DB
		$query = DB::table('dynamic_image_managemant')
			->select('filename', 'created_at')
			->where('template_id', $folder_name)
			->where('map_type', 1)
			->where('status', 1);

		if (!empty($searchkey)) {
			$query->where('filename', 'like', $searchkey . '%');
		}

		$images = $query->get();

		// Build array
		$imageArray = [];
		foreach ($images as $img) {
			$imageArray[] = [
				'filename'     => $img->filename,
				// 'path'         => $basePath . $folder_name . '/' . $img->filename,
				'path'         => $img->filename,
				'created_date' => $img->created_at ? date("Y-m-d H:i:s", strtotime($img->created_at)) : date("Y-m-d H:i:s"),
			];
		}

		if ($sortBy == 'atoz') {
			// Sort alphabetically
			usort($imageArray, function ($a, $b) {
				return strcasecmp($a['filename'], $b['filename']);
			});

			$sorted = array_map(function ($img) {
				return $img['path']; // only return path like old code returned filenames
			}, $imageArray);

			
			if(!$template_name && $value == 100 ){
				$template_name = 'Demo_custom';
			}

			return response()->json([
				'data'                  => $sorted,
				'get_file_aws_local_flag' => $get_file_aws_local_flag,
				'systemConfig'          => $systemConfig['sandboxing'],
				'template_name'         => $template_name
			]);
		} else {
			// Sort by created date (latest first)
			usort($imageArray, function ($a, $b) {
				return strtotime($a['created_date']) <=> strtotime($b['created_date']);
			});
			$imageArray = array_reverse($imageArray);

			$sorted = array_map(function ($img) {
				return $img['path'];
			}, $imageArray);


			
			if(!$template_name && $value == 100 ){
				$template_name = 'Demo_custom';
			}

			return response()->json([
				'data'                  => $sorted,
				'get_file_aws_local_flag' => $get_file_aws_local_flag,
				'systemConfig'          => $systemConfig['sandboxing'],
				'template_name'         => $template_name
			]);
		}
	}



	


	//when selecting datewise sorting for image
	function compareByTimeStamp($time1, $time2) 
	{
	    $datetime1 = strtotime($time1['created_date']); 
	    $datetime2 = strtotime($time2['created_date']); 
	   
	    return $datetime1 - $datetime2; 
	} 

	//replace image name
	public function dynamicImageEdit(Request $request,CheckUploadedFileOnAwsORLocalService $checkUploadedFileOnAwsOrLocal){
		//get folder name
		$get_file_aws_local_flag = $checkUploadedFileOnAwsOrLocal->checkUploadedFileOnAwsORLocal();

		$auth_site_id=\Auth::guard('admin')->user()->site_id;
        $systemConfig = SystemConfig::where('site_id',$auth_site_id)->first();

		$domain = \Request::getHost();
        $subdomain = explode('.', $domain);

		$folder_name = $request->folder_name;
		//get old image name
		$old_image_name = $request->old_image_name;
		//seperate extension from image name
		$extc = explode('.', $old_image_name);

		$image_name = $request->image_name;

		//go to target directory of folder name
		
		if($get_file_aws_local_flag->file_aws_local == '1'){
			$targetDir =$subdomain[0].'/backend/templates/'.$folder_name;
		}else{
			$targetDir =public_path().'/'.$subdomain[0].'/backend/templates/'.$folder_name;
		}
		//$path = public_path($subdomain[0] . '/' . Config::get('constant.template') . '/' . $folder_name);
		if (!File::exists($targetDir)) {
			File::makeDirectory($targetDir, 0777, true, true);
		}

        $old_image_name = $targetDir.'/'.$old_image_name;
		$image_name = $image_name.'.'.$extc[1];
		$new_name = $targetDir.'/'.$image_name;
		//compare old image name with new image name
		if($old_image_name == $new_name){
		
			$message = Array('success' => 'true', 'message' => 'rename successfully','image'=>$image_name);
       	
       	    return $message;	        
		}else{
			if($get_file_aws_local_flag->file_aws_local == '1'){
				if(!Storage::disk('s3')->exists($new_name)){
	                
			        //copy new file name
					$data = Storage::disk('s3')->copy($old_image_name,$new_name);
					//update new image name in db
					DB::table('dynamic_image_managemant')
					->where('filename', $request->old_image_name)
					->where('template_id', $folder_name)
					->update([
						'filename'   => $image_name,
						'updated_at' => now(),
					]);

					//delete old file name
					Storage::disk('s3')->delete($old_image_name);
	                
					$message = Array('success' => 'true', 'message' => 'rename successfully','image'=>$image_name);
	       	
	       	    	return $message;
				}else{
					$message = Array('success' => 'false', 'message' => 'image name already exists');
	       	
	       	    	return $message;
				}
			}else{
				if(!file_exists($new_name)){
	                
			        //copy new file name
			        \File::copy($old_image_name,$new_name);
					//delete old file name
					unlink($old_image_name);

					//update new image name in db
					DB::table('dynamic_image_managemant')
					->where('filename', $request->old_image_name)
					->where('template_id', $folder_name)
					->update([
						'filename'   => $image_name,
						'updated_at' => now(),
					]);

					$message = Array('success' => 'true', 'message' => 'rename successfully','image'=>$image_name);
	       	
	       	    	return $message;
				}else{
					$message = Array('success' => 'false', 'message' => 'image name already exists');
	       	
	       	    	return $message;
				}
			}
		}
	}

	//deleting image
	public function delete(Request $request,CheckUploadedFileOnAwsORLocalService $checkUploadedFileOnAwsOrLocal){
		$get_file_aws_local_flag = $checkUploadedFileOnAwsOrLocal->checkUploadedFileOnAwsORLocal();

		$auth_site_id=\Auth::guard('admin')->user()->site_id;
        $systemConfig = SystemConfig::where('site_id',$auth_site_id)->first();

		$domain = \Request::getHost();
        $subdomain = explode('.', $domain);


		$folder_name = $request->folder_name;
		$image_name = $request->image_name;
		
		if($get_file_aws_local_flag->file_aws_local == '1'){
			$targetDir =$subdomain[0].'/backend/templates/'.$folder_name.'/'.$image_name;
			$targetDirImage =$subdomain[0].'/backend/templates/'.$folder_name;
	    }
	    else{
	        $targetDir =$subdomain[0].'/backend/templates/'; 
	    }
		
		//get login user id
		$admin_id = \Auth::guard('admin')->user()->toArray();
		if($get_file_aws_local_flag->file_aws_local == '1'){

			if(Storage::disk('s3')->exists($targetDir)){
				
				Storage::disk('s3')->delete($targetDir);

				// delete image name from db
				DB::table('dynamic_image_managemant')
				->where('filename', $image_name)
				->where('template_id', $folder_name)
				->delete();
				//store delete image data in delete image history table
				ImageDeleteHistory::create(['admin_id'=>$admin_id['id'],'image_name'=>$image_name,'template_name'=>$folder_name]);

				//get image count
				$all_files = Storage::disk('s3')->allFiles($targetDirImage);
    			$files = [];
    			$ext_array = ['jpg','png','JPG','PNG','JPEG','jpeg','gif'];
    			foreach ($all_files as $all_fileskey => $all_filesvalue) {
    				$explode_file = explode('.',$all_filesvalue);
    				if(in_array($explode_file[1], $ext_array)){
    					$files[] = $all_filesvalue;
    				}
    			}
				$imageCounts = count($files);
				
				$message = Array('success' => 'true', 'message' => 'image delete successfully','imageCounts'=>$imageCounts);
	       		return $message;
			}
		}
		else{
			if(file_exists(public_path().'/'.$targetDir.$folder_name.'/'.$image_name)){
				unlink(public_path().'/'.$targetDir.$folder_name.'/'.$image_name);
				//store delete image data in delete image history table
				ImageDeleteHistory::create(['admin_id'=>$admin_id['id'],'image_name'=>$image_name,'template_name'=>$folder_name]);

				// delete image name from db
				DB::table('dynamic_image_managemant')
				->where('filename', $image_name)
				->where('template_id', $folder_name)
				->delete();

				//get image count

				$imageCounts = count(glob(public_path().'/'.$targetDir.$folder_name."/*.{jpg,png,JPG,PNG,JPEG,'jpeg','gif'}", GLOB_BRACE));
				
				$message = Array('success' => 'true', 'message' => 'image delete successfully','imageCounts'=>$imageCounts);
	       		return $message;
			}
		}
	}



	//replace image name
	public function excel2pdfdynamicImageEdit(Request $request,CheckUploadedFileOnAwsORLocalService $checkUploadedFileOnAwsOrLocal){
		//get folder name
		$get_file_aws_local_flag = $checkUploadedFileOnAwsOrLocal->checkUploadedFileOnAwsORLocal();

		$auth_site_id=\Auth::guard('admin')->user()->site_id;
        $systemConfig = SystemConfig::where('site_id',$auth_site_id)->first();

		$domain = \Request::getHost();
        $subdomain = explode('.', $domain);

		$folder_name = $request->folder_name;
		//get old image name
		$old_image_name = $request->old_image_name;
		//seperate extension from image name
		$extc = explode('.', $old_image_name);

		$image_name = $request->image_name;

		//go to target directory of folder name
		
		if($get_file_aws_local_flag->file_aws_local == '1'){

	        $targetDir =$subdomain[0].'/backend/templates/excel2pdf/'.$folder_name;
	        
	    }
	    else{
	    	
	        $targetDir =public_path().'/'.$subdomain[0].'/backend/templates/excel2pdf/'.$folder_name;
	       
	    }
		
        $old_image_name = $targetDir.'/'.$old_image_name;
		$image_name = $image_name.'.'.$extc[1];
		$new_name = $targetDir.'/'.$image_name;
		//compare old image name with new image name
		if($old_image_name == $new_name){
		
			$message = Array('success' => 'true', 'message' => 'rename successfully','image'=>$image_name);
       	
       	    return $message;	        
		}else{
			if($get_file_aws_local_flag->file_aws_local == '1'){
				if(!Storage::disk('s3')->exists($new_name)){
	                
			        //copy new file name
					$data = Storage::disk('s3')->copy($old_image_name,$new_name);

					//update new image name in db
					DB::table('dynamic_image_managemant')
					->where('filename', $request->old_image_name)
					->where('template_id', $folder_name)
					->where('map_type', 1)
					->update([
						'filename'   => $image_name,
						'updated_at' => now(),
					]);

					//delete old file name
					Storage::disk('s3')->delete($old_image_name);
	                
					$message = Array('success' => 'true', 'message' => 'rename successfully','image'=>$image_name);
	       	
	       	    	return $message;
				}else{
					$message = Array('success' => 'false', 'message' => 'image name already exists');
	       	
	       	    	return $message;
				}
			}
			else{
				if(!file_exists($new_name)){
	                
			        //copy new file name
			        \File::copy($old_image_name,$new_name);
					//update new image name in db
					DB::table('dynamic_image_managemant')
					->where('filename', $request->old_image_name)
					->where('template_id', $folder_name)
					->where('map_type', 1)
					->update([
						'filename'   => $image_name,
						'updated_at' => now(),
					]);
					//delete old file name
					unlink($old_image_name);
	                
					$message = Array('success' => 'true', 'message' => 'rename successfully','image'=>$image_name);
	       	
	       	    	return $message;
				}else{
					$message = Array('success' => 'false', 'message' => 'image name already exists');
	       	
	       	    	return $message;
				}
			}
		}
	}

	//deleting image
	public function excel2pdfdelete(Request $request,CheckUploadedFileOnAwsORLocalService $checkUploadedFileOnAwsOrLocal){
		$get_file_aws_local_flag = $checkUploadedFileOnAwsOrLocal->checkUploadedFileOnAwsORLocal();

		$auth_site_id=\Auth::guard('admin')->user()->site_id;
        $systemConfig = SystemConfig::where('site_id',$auth_site_id)->first();

		$domain = \Request::getHost();
        $subdomain = explode('.', $domain);


		$folder_name = $request->folder_name;
		$image_name = $request->image_name;
		
		if($get_file_aws_local_flag->file_aws_local == '1'){
			$targetDir =$subdomain[0].'/backend/templates/excel2pdf/'.$folder_name.'/'.$image_name;
			$targetDirImage =$subdomain[0].'/backend/templates/excel2pdf/'.$folder_name;
	    }
	    else{
	        $targetDir =$subdomain[0].'/backend/templates/excel2pdf/';
	    }
		
		//get login user id
		$admin_id = \Auth::guard('admin')->user()->toArray();
		if($get_file_aws_local_flag->file_aws_local == '1'){

			if(Storage::disk('s3')->exists($targetDir)){
				
				Storage::disk('s3')->delete($targetDir);

				// delete image name from db
				DB::table('dynamic_image_managemant')
				->where('filename', $image_name)
				->where('map_type', 1)
				->where('template_id', $folder_name)
				->delete();


				//store delete image data in delete image history table
				ImageDeleteHistory::create(['admin_id'=>$admin_id['id'],'image_name'=>$image_name,'template_name'=>$folder_name]);

				//get image count
				$all_files = Storage::disk('s3')->allFiles($targetDirImage);
    			$files = [];
    			$ext_array = ['jpg','png','JPG','PNG','JPEG','jpeg','gif'];
    			foreach ($all_files as $all_fileskey => $all_filesvalue) {
    				$explode_file = explode('.',$all_filesvalue);
    				if(in_array($explode_file[1], $ext_array)){
    					$files[] = $all_filesvalue;
    				}
    			}
				$imageCounts = count($files);
				
				$message = Array('success' => 'true', 'message' => 'image delete successfully','imageCounts'=>$imageCounts);
	       		return $message;
			}
		}
		else{
			if(file_exists(public_path().'/'.$targetDir.$folder_name.'/'.$image_name)){
				unlink(public_path().'/'.$targetDir.$folder_name.'/'.$image_name);

				// delete image name from db
				DB::table('dynamic_image_managemant')
				->where('filename', $image_name)
				->where('map_type', 1)
				->where('template_id', $folder_name)
				->delete();
				//store delete image data in delete image history table
				ImageDeleteHistory::create(['admin_id'=>$admin_id['id'],'image_name'=>$image_name,'template_name'=>$folder_name]);

				//get image count

				$imageCounts = count(glob(public_path().'/'.$targetDir.$folder_name."/*.{jpg,png,JPG,PNG,JPEG,'jpeg','gif'}", GLOB_BRACE));
				
				$message = Array('success' => 'true', 'message' => 'image delete successfully','imageCounts'=>$imageCounts);
	       		return $message;
			}
		}
	}

}
