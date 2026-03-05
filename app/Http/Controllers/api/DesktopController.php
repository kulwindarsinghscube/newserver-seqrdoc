<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\models\Site;

use DB,Event;
use Auth;

class DesktopController extends Controller
{
	function sanitizeVar($data) {
		$data = trim($data);
		$data = stripslashes($data);
		$data = htmlspecialchars($data);
		return $data;
	}

    public function fetchInstancedetail(Request $request)
    {
		$api_key = '~PLJXP]T2~r8>4-SX!ZL';

		$headers = apache_request_headers();

		
		$status = false;

		
		if (isset($headers['Apikey'])) {
			if (strcasecmp($headers['Apikey'], $api_key) == 0) {
				$status = true;
			} else {
				$message = array('success' => false,'status'=>403, 'message' => 'Api key is mismatch.');
			}
		} else {
			$message = array('success' => false,'status'=>403, 'message' => 'Access forbidden.');
		}

		\DB::disconnect('mysql'); 
		\Config::set("database.connections.mysql", [
			'driver'   => 'mysql',
			'host'     => 'seqrdoc.com',
			"port" => "3306",
			'database' => 'seqr_demo',
			'username' => 'developer',
			'password' => 'developer',
			"unix_socket" => "",
			"charset" => "utf8mb4",
			"collation" => "utf8mb4_unicode_ci",
			"prefix" => "",
			"prefix_indexes" => true,
			"strict" => true,
			"engine" => null,
			"options" => []
		]);
		\DB::reconnect();



		if($status) {
			// $sql = DB::select(DB::raw('SELECT instance_name as value,base_url, instance FROM instance_list WHERE publish="1" ORDER BY instance_name ASC'));
			$siteData = DB::select(DB::raw('SELECT SUBSTRING_INDEX(sites.site_url, ".", 1) as site_instance_name FROM sites ORDER BY site_url ASC'));
			if($siteData) {
				$message = array('success' => true,'status'=>200, 'message' => 'Success','data' => $siteData);
			} else {
				$message = array('success' => false,'status'=>403, 'message' => 'Instances name is available');
			}
		}
		return $message;


    }

	
	public function instanceLogin(Request $request) {

		$rules = [
			'username' => 'required',
			'password' => 'required',
			'instance_name' => 'required'
		];

		$messages = [
			'username.required' => 'User name required',
			'password.required' => 'Password required',
			'instance_name.required' => 'Instance Name required',
		];

		$validator = \Validator::make($request->post(),$rules,$messages);

		if ($validator->fails()) {
			return response()->json(['success'=>false,'status'=>400,'message'=>$validator->errors()],400);
		}
	 
		
		$credential=[

			"username"=>$request->username,
			"password"=>$request->password,
			'publish'=>1
		];

		$instance = $request->instance_name;
		
		//$subdomain = explode('.', $domain);
		if($instance == 'demo')
		{
			$dbName = 'seqr_'.$instance;
		}
		else if($instance == 'apponly'||$instance == 'master')
		{
			$dbName = 'seqr_demo';
		}
		else{
			$dbName = 'seqr_d_'.$instance;
		}

		if($instance !=null)
		{
			\DB::disconnect('mysql'); 
			\Config::set("database.connections.mysql", [
				'driver'   => 'mysql',
				'host'     => 'seqrdoc.com',
				"port" => "3306",
				'database' => 'seqr_demo',
				'username' => 'developer',
				'password' => 'developer',
				"unix_socket" => "",
				"charset" => "utf8mb4",
				"collation" => "utf8mb4_unicode_ci",
				"prefix" => "",
				"prefix_indexes" => true,
				"strict" => true,
				"engine" => null,
				"options" => []
			]);
			\DB::reconnect();

			$site_data = Site::select('site_id','start_date','end_date','new_server')->where(DB::raw('SUBSTRING_INDEX(site_url, ".", 1)'),$instance)->first();
			
			if($site_data['new_server'] == 0) {
				$hostName = 'seqrdoc.com';
				$dbUser = 'developer';
				$dbPass = 'developer';
			} else {
				$hostName = 'localhost';
				$dbUser = 'developer';
				$dbPass = 'developer';
			}

			\DB::disconnect('mysql'); 
			\Config::set("database.connections.mysql", [
				'driver'   => 'mysql',
				'host'     => $hostName,
				"port" => "3306",
				'database' => $dbName,
				'username' => $dbUser,
				'password' => $dbPass,
				"unix_socket" => "",
				"charset" => "utf8mb4",
				"collation" => "utf8mb4_unicode_ci",
				"prefix" => "",
				"prefix_indexes" => true,
				"strict" => true,
				"engine" => null,
				"options" => []
			]);
			\DB::reconnect();


			$site_url = explode(".",$_SERVER['SERVER_NAME']);
			$sitename = ucfirst($site_url[0]);

			$today = date('Y-m-d');
			$start_date = $site_data['start_date'];
			$end_date = $site_data['end_date'];
			/*echo $request->password;
			echo "<br>";
			$site_data['start_date']
			echo  $generate_password = \Hash::make($request->password);*/ 
			$credential['site_id'] = $site_data['site_id'];
			if($today >= $start_date && $today <= $end_date){

				if(Auth::guard('admin')->attempt($credential))
				{

					// //store user's info in session manager when login user
					// $session_manager = new SessionManager();
					
					// $user_id = Auth::guard('admin')->user()->id;
					// $session_id = \Hash::make(rand(1,1000));
					// $session_manager->user_id = $user_id;
					// $session_manager->session_id = $session_id;
					// $session_manager->login_time = date('Y-m-d H:i:s');
					// $session_manager->is_logged = 1;
					// $session_manager->device_type = 'webAdmin';
					// $session_manager->ip = \Request::ip();
					// $session_manager->site_id = $site_id;
					// $session_manager->save();
					
					// $auth_id=Auth::guard('admin')->user()->id; 
					// $insert_id=Admin::where('id',$auth_id)->update(['site_id'=>$site_id]);

					// // put value in session
					// $request->session()->put('session_id',$session_manager['id']);
					// $request->session()->put('site_name',$sitename);

					// $recordToGenerate=0;
					// $checkStatus = CoreHelper::checkMaxCertificateLimit($recordToGenerate);
					$data = [];
					$data['user_id'] = Auth::guard('admin')->user()->id;
					$data['username'] = Auth::guard('admin')->user()->username;
					$data['fullname'] = Auth::guard('admin')->user()->fullname;

					$siteData=[];
					$siteData['site_id']=$site_data['site_id'];

					$siteData['db_index']=$site_data['new_server'];

					// if($site_data['new_server'] == 0) {
						
					// 	$siteData['db_name']=$dbName;
					// 	$siteData['db_host']="CNpc3YcaQvG8PMM6ai3tpV37A5iDcM+ybAmSXtMGhi1mHsIzi2SRKKgEtMdWRciu";
					// 	$siteData['db_user']="uC748uM6LdaHRvR2AhallE5PiGMcoIfCRsQsA+4w0cQ=";
					// 	$siteData['db_hash']="0/vUd2DxXpFgF6rP0OgLtJclT1T9Bnva8aBNmYrDI9I=";
					// }else{
					// 	$siteData['db_name']=$dbName;
					// 	$siteData['db_host']="qPqVA89RZEYj0B3VQcQ2XsxCpIZsMHeMwkLiBGlEKVsurCD9YKUB35pv79ecnRcf";
					// 	$siteData['db_user']="hlkqAf+CTcfx0lNK2Fs91h6WkHnXDj7HcDnQlfnXKJyWgt9PzhRvtnp3+ihPDsDW";
					// 	$siteData['db_hash']="0/vUd2DxXpFgF6rP0OgLtJclT1T9Bnva8aBNmYrDI9I=";
					// }
					if ($site_data['new_server'] == 0) {

						$siteData['db_name'] = $dbName;
						$siteData['db_host'] = "CNpc3YcaQvG8PMM6ai3tpV37A5iDcM+ybAmSXtMGhi1mHsIzi2SRKKgEtMdWRciu";
						$siteData['db_user'] = "uC748uM6LdaHRvR2AhallE5PiGMcoIfCRsQsA+4w0cQ=";
						$siteData['db_hash'] = "0/vUd2DxXpFgF6rP0OgLtJclT1T9Bnva8aBNmYrDI9I=";
						$siteData['ftp_username'] = "6nBTPSZs0wt1/E2hkxlNXlNF9YJBvxp/WOhghIkj7d4=";
						$siteData['ftp_password'] = "TWQRz2WoqFGNBRRzTH92PYpdq8BAqNW48k2TspSTwig=";
						$siteData['Aws_Access_KeyId'] = "SvDVW5ywMSNliS6GnAjasydw/Q3COYUQgJn9htobRyK6reDqDAXFe1ObA8+8gS8V";
						$siteData['Aws_Secret_Accesskey'] = "Unvi8UsehR0woClwld0oRZ+uLxuqi7kAnqqGVm/Calh9qFQCTBrjiunfo5o9tRnC7rijXMWSPSsJs+vG57NApQ==";
						$siteData['Aws_Region_Name'] = "9MzbYv2sGHCT7ZMbjqEd+OloiJYjeVKPuTZdS3/K2e0=";
					} else {
						$siteData['db_name'] = $dbName;
						$siteData['db_host'] = "qPqVA89RZEYj0B3VQcQ2XsxCpIZsMHeMwkLiBGlEKVsurCD9YKUB35pv79ecnRcf";
						$siteData['db_user'] = "hlkqAf+CTcfx0lNK2Fs91h6WkHnXDj7HcDnQlfnXKJyWgt9PzhRvtnp3+ihPDsDW";
						$siteData['db_hash'] = "0/vUd2DxXpFgF6rP0OgLtJclT1T9Bnva8aBNmYrDI9I=";

						$siteData['ftp_username'] = "YZ3dNI69mtCjzqD8yAMFd9oteWsnkjM2rqhXdegi9KMixAXSZ8dOCTFH5EeqFTNL";
						$siteData['ftp_password'] = "fZ2I848VlCyErCZBtY9P6bUGA3sUi5nnT+Sa9OQuFIg=";
						$siteData['Aws_Access_KeyId'] = "SvDVW5ywMSNliS6GnAjasydw/Q3COYUQgJn9htobRyK6reDqDAXFe1ObA8+8gS8V";
						$siteData['Aws_Secret_Accesskey'] = "Unvi8UsehR0woClwld0oRZ+uLxuqi7kAnqqGVm/Calh9qFQCTBrjiunfo5o9tRnC7rijXMWSPSsJs+vG57NApQ==";
						$siteData['Aws_Region_Name'] = "9MzbYv2sGHCT7ZMbjqEd+OloiJYjeVKPuTZdS3/K2e0=";

					}
					
					if( strtolower($sitename) == 'demo') {
							$siteData['is_pantone'] = true;
						} else {
							$siteData['is_pantone'] = false;
						}

					
					$message = array('success' => true,'status'=>200, 'message' => 'Login Successfully','data'=>$data,'siteData'=>$siteData,"s3Instances"=>\Config::get('constant.awsS3Instances'));
					
					// $message = array('success' => true,'status'=>200, 'message' => 'Login Successfully');

					// return response()->json(['success'=>true,'msg'=>'Login Successfully']);   
				}
				else
				{
					$message = array('success' => false,'status'=>403, 'message' => 'These Credentials do not match our records.');

				}
			}else{
				$message = array('success' => false,'status'=>403, 'message' => 'Your service is not started yet or your service is expired please contact service provider');
			}
			return $message;


		}
	}


}
