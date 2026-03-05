 <?php

	include(app_path('Library/sftp/Net/SFTP.php'));

	ini_set('display_errors', 1); 
	ini_set('display_startup_errors', 1); 
	error_reporting(E_ALL);

	ini_set('upload_max_filesize', '1024M');
	ini_set('post_max_size', '1024M');
	ini_set('max_execution_time', '600');
	ini_set('max_input_time', '600');
	ini_set('memory_limit', -1);
	error_reporting(E_ALL ^ E_NOTICE);
	
	
	function mySftpConnect_old() {

		$domain = $_SERVER['HTTP_HOST'];
    	$subdomain = explode('.', $domain);

		if($subdomain[0] =='newserver' || $subdomain[0] =='anu') {
			$host = \Config::get('constant.anu_sftp_host');
			$username = \Config::get('constant.anu_sftp_username');
			$password = \Config::get('constant.anu_sftp_password');
		}		

		if($host) {

			$sftp = new Net_SFTP($host);
			if (!$sftp->login($username, $password)) {
				error_log("SFTP Login Failed to $host with $username");
				$ftp_flag='Not Connected';
			} else {
				$ftp_flag='Connected';
			}

			// $sftp = new Net_SFTP($host);
			// if (!$sftp->login($username, $password)) {
			// 	// echo 'Login Failed';
			// 	$ftp_flag='Not Connected';
			// } else {
			// 	// echo 'Login Success';
			// 	// echo "<br>";
			// 	$ftp_flag='Connected';
			// }
		} else {
			$ftp_flag='Not Connected';
			// echo 'Not Exist host';
			// echo "<br>";
		}
		if($ftp_flag=='Connected'){
           $arrResp=array('status'=>true,'type' => 'success',"message"=>"Server connected!","ftpHost"=>$host,"ftp_flag"=>$ftp_flag);
        }else{
           $arrResp=array('status'=>false,'type' => 'error',"message"=>"Failed to connect to Anant National University Server. Please try again after sometime.","ftpHost"=>$ftpHost,"ftp_flag"=>$ftp_flag); 
        }

        return $arrResp;
		// die();
	}


	function mySftpConnect() {
		$domain = $_SERVER['HTTP_HOST'];
		$subdomain = explode('.', $domain);

		$host = $username = $password = null;

		if ($subdomain[0] == 'newserver' || $subdomain[0] == 'anu') {
			$host = \Config::get('constant.anu_sftp_host');
			$username = \Config::get('constant.anu_sftp_username');
			$password = \Config::get('constant.anu_sftp_password');
		}

		$ftp_flag = 'Not Connected';
		$ftpHost = $host;

		if ($host) {
			try {
				$sftp = new Net_SFTP($host);

				if (!$sftp->login($username, $password)) {
					// Login failed, log it
					error_log("SFTP Login Failed: Host: $host | Username: $username");
				} else {
					$ftp_flag = 'Connected';
				}
			} catch (\Exception $e) {
				// Catch any exceptions and log them
				error_log("SFTP Connection Exception: " . $e->getMessage());
			}
		}

		if ($ftp_flag == 'Connected') {
			return [
				'status' => true,
				'type' => 'success',
				'message' => 'Server connected!',
				'ftpHost' => $ftpHost,
				'ftp_flag' => $ftp_flag
			];
		} else {
			return [
				'status' => false,
				'type' => 'error',
				'message' => 'Failed to connect to Anant National University Server. Please try again after sometime.',
				'ftpHost' => $ftpHost,
				'ftp_flag' => $ftp_flag
			];
		}
	}



	function uploadFileOnServer($localFile) {

		$domain = $_SERVER['HTTP_HOST'];
    	$subdomain = explode('.', $domain);

		if($subdomain[0] =='newserver' || $subdomain[0] =='anu') {
			$host = \Config::get('constant.anu_sftp_host');
			$username = \Config::get('constant.anu_sftp_username');
			$password = \Config::get('constant.anu_sftp_password');
		}

		if(!file_exists($localFile)) {
			echo 'Local File Not Found!';
			exit();
		}
		$localFileName = basename($localFile, '.pdf');
		$serverFile = '/html/verify/seqrdoc/pdf_file/';
		$remoteFile = $serverFile.$localFileName.'.pdf';
		$sftp = new Net_SFTP($host);
		if (!$sftp->login($username, $password)) {
			echo 'Login Failed';
			die();

		} else {
			// echo 'Login Success';
			// echo "<br>";
			
		}

		$pdftext = file_get_contents($localFile);

		$sftp->put($remoteFile, $pdftext);

		// echo 'File uploaded successfully!';


	}





	function uploadFileOnServerKmTC($localFile) {

		$domain = $_SERVER['HTTP_HOST'];
    	$subdomain = explode('.', $domain);

		
		$host = \Config::get('constant.kmtc_sftp_host');
		$username = \Config::get('constant.kmtc_sftp_username');
		$password = \Config::get('constant.kmtc_sftp_password');
		

		if(!file_exists($localFile)) {
			echo 'Local File Not Found!';
			exit();
		}
		$localFileName = basename($localFile, '.pdf');
		$serverFile = '/public/kmtc/backend/pdf_file/';
		$remoteFile = $serverFile.$localFileName.'.pdf';
		$sftp = new Net_SFTP($host);
		if (!$sftp->login($username, $password)) {
			echo 'Login Failed';
			die();

		} else {
			// echo 'Login Success';
			// echo "<br>";
			
		}


		$pdftext = file_get_contents($localFile);

		$sftp->put($remoteFile, $pdftext);

		// echo 'File uploaded successfully!';


	}





	function uploadFileOnServerAnu($sftpData) {

		$domain = $_SERVER['HTTP_HOST'];
    	$subdomain = explode('.', $domain);

		if($subdomain[0] =='newserver' || $subdomain[0] =='anu') {
			$host = \Config::get('constant.anu_sftp_host');
			$username = \Config::get('constant.anu_sftp_username');
			$password = \Config::get('constant.anu_sftp_password');
		}

		$sftp = new Net_SFTP($host);
		if (!$sftp->login($username, $password)) {
			echo 'Login Failed';
			die();

		}

		if(count($sftpData) > 0 ) {
			
			foreach($sftpData as $data) {
				$localFile = public_path().'/'.$subdomain[0].'/backend/pdf_file/'.$data;
				$localFileName = basename($localFile, '.pdf');
				$serverFile = '/html/verify/seqrdoc/pdf_file/';
				$remoteFile = $serverFile.$localFileName.'.pdf';
				

				$pdftext = file_get_contents($localFile);

				$sftp->put($remoteFile, $pdftext);

			}

			

		} else {
			echo 'Data Not Found!';
			exit();
		}
		// if(!file_exists($sftpData)) {
		// 	echo 'Local File Not Found!';
		// 	exit();
		// }
		

		// echo 'File uploaded successfully!';


	}
	

	function listFileOnServerKMTC() {

		// SFTP Credentials
		$host = \Config::get('constant.kmtc_sftp_host');
		$username = \Config::get('constant.kmtc_sftp_username');
		$password = \Config::get('constant.kmtc_sftp_password');

		// Remote directory to list
		$remoteDirectory = '/public/kmtc/backend/pdf_file/';

		$sftp = new Net_SFTP($host);
		if (!$sftp->login($username, $password)) {
			echo 'Login Failed';
			die();

		}
		// echo "SFTP Login Success<br><br>";

		// List files
		$fileList = $sftp->nlist($remoteDirectory); // Just names
		// $fileList = $sftp->rawlist($remoteDirectory); // Detailed info

		if ($fileList === false) {
		    echo "Failed to read directory or directory does not exist.";
		} else {
		    echo "Files in SFTP Directory '$remoteDirectory':<br>";
		    foreach ($fileList as $file) {
		        // Skip current and parent folder entries
		        if ($file === '.' || $file === '..') continue;

		        echo $file . "<br>";
		    }
		}

	}


	function getFileOnServerKMTC($certName) {

		$domain = $_SERVER['HTTP_HOST'];
    	$subdomain = explode('.', $domain);


		// SFTP Credentials
		$host = \Config::get('constant.kmtc_sftp_host');
		$username = \Config::get('constant.kmtc_sftp_username');
		$password = \Config::get('constant.kmtc_sftp_password');

		// Remote directory to list
		$remoteDirectory = '/public/kmtc/backend/pdf_file/';

		$sftp = new Net_SFTP($host);
		if (!$sftp->login($username, $password)) {
			echo 'Login Failed';
			die();

		}	

		$remoteFilePath = '/public/kmtc/backend/pdf_file/' . $certName;


		// Temp local path
		// $target_path = sys_get_temp_dir() . '/' . uniqid('pdf_', true) . '.pdf';
		$target_path = public_path().'/'.$subdomain[0].'/backend/'.$certName;
		if(file_exists($target_path)) {
			unlink($target_path);
		}
		// Get the file and save it temporarily
		if ($sftp->get($remoteFilePath, $target_path)) {

			$path = 'https://'.$subdomain[0].'.seqrdoc.com/';
		    $file_url = $path.''.$subdomain[0].'/backend/'.$certName;

		    // Append data in line
		    $lineToAdd = time() . '|' . $target_path . PHP_EOL;
			$filePath = public_path() . '/kmtc/kmtc_delete_me.txt';
			if (!file_exists($filePath)) {
			    // Create an empty file
			    file_put_contents($filePath, '');
			}
			// Read file and check if file path already listed
			$shouldAdd = true;
			if (file_exists($filePath)) {
			    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
			    foreach ($lines as $line) {
			        list($timestamp, $file) = explode('|', trim($line));
			        if ($file === $target_path) {
			            $shouldAdd = false;
			            break;
			        }
			    }
			}

			// Append with new line only if not already present
			if ($shouldAdd) {
			    file_put_contents($filePath, $lineToAdd, FILE_APPEND);
			}

		    // Append data in line
		    return $file_url;


		    
		    // echo "File downloaded successfully to: " . $target_path;
		    
		    // // Example: To output the file in browser as a download
		    // header('Content-Type: application/pdf');
		    // header('Content-Disposition: attachment; filename="' . basename($fileName) . '"');
		    // readfile($target_path);
		    // // Optionally delete temp file after sending
		    // unlink($target_path);
		} else {
		    // echo "Failed to download the file.";
		}


	}


	function getFileOnServerKMTCV1($certName) {

		$domain = $_SERVER['HTTP_HOST'];
    	$subdomain = explode('.', $domain);


		// SFTP Credentials
		$host = \Config::get('constant.kmtc_sftp_host');
		$username = \Config::get('constant.kmtc_sftp_username');
		$password = \Config::get('constant.kmtc_sftp_password');

		// Remote directory to list
		// $remoteDirectory = '/public/kmtc/backend/pdf_file/';
		$remotePath = '/public/kmtc/backend/pdf_file/' . $certName;

		$sftp = new Net_SFTP($host);
		if (!$sftp->login($username, $password)) {
			echo 'Login Failed';
			die();

		}	

		// Fetch PDF file contents
		$pdfData = $sftp->get($remotePath);

		if ($pdfData === false) {
		    http_response_code(404);
		    echo "File not found on SFTP server";
		    exit;
		}

		// Serve PDF to browser
		header("Content-Type: application/pdf");
		header("Content-Disposition: inline; filename=\"$filename\"");
		echo $pdfData;
		exit;


	}



    // // List files in the remote directory
    // $remoteDirectory = '/var/www/verify.anu.edu.in/verify/seqrdoc/';

    // $localDirectory = "uploads_sftp/";
	// $files = $sftp->nlist($remoteDirectory);

	// if ($files === false) {
	//     exit('Failed to list directory');
	// }

	// // Copy each file from the SFTP server to the local directory
	// foreach ($files as $file) {
	//     // Skip '.' and '..' entries
	//     if ($file === '.' || $file === '..') {
	//         continue;
	//     }
	    
	//     $remoteFile = "$remoteDirectory/$file";
	//     $localFile = "$localDirectory/$file";

	//     // Download the file to the local directory
	//     // if (!$sftp->get($remoteFile, $localFile)) {
	//     //     echo "Failed to download $file\n";
	//     // } else {
	//     //     echo "Downloaded $file successfully\n";
	//     // }
	// }


    // $sftp->get('/var/www/verify.anu.edu.in/verify/seqrdoc/'.$fileName, $target_path);


	
?>
