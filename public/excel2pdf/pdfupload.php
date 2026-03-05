<?php 
    require('login_session.php');

    //require('connection.php'); 
    include_once 'db.php';
    $valid_extensions = array('pdf'); // valid extensions
    $path = 'uploads/'; // upload directory
    $template_id = $_POST['template_id'];

    //echo "<pre>";
    //print_r($_FILES);
    if($_FILES['pdf_upload']) {
        $img = $_FILES['pdf_upload']['name'];
        $tmp = $_FILES['pdf_upload']['tmp_name'];
        $temps = explode(".", $_FILES["pdf_upload"]["name"]);
        $newfilename = $temps[0]."_".round(microtime(true)) . '.' . end($temps);
        // get uploaded file's extension
        $ext = strtolower(pathinfo($img, PATHINFO_EXTENSION));
        // can upload same image using rand function
        // check's valid format
        if(in_array($ext, $valid_extensions)) { 
            //$path = $path.strtolower($img); 
            $path = $path.strtolower($newfilename); 
            if(move_uploaded_file($tmp,$path)) {
                $extractor_boxes = $_POST['extractor_boxes'];
                $placer_boxes = $_POST['placer_boxes'];
                $ep_boxes = $_POST['ep_boxes'];
                $template_name = $_POST['template_name'];
                $template_id = $_POST['template_id'];
                $pdf_page = $_POST['pdf_page'];
                $pdf_data = $_POST['pdf_data'];
               
                //print_r($extractor_boxes);
                //print_r($placer_boxes);
                //print_r($ep_boxes);
                //include database configuration file
                
                $query = "SELECT `template_name` FROM `uploaded_pdfs` WHERE template_name=?";
                if ($stmt = $db->prepare($query)){
                    $stmt->bind_param("s", $template_name);
                    if($stmt->execute()){
                        $stmt->store_result();
                        $template_name_check= "";         
                        $stmt->bind_result($template_name_check);
                        $stmt->fetch();
                        if ($stmt->num_rows == 1){
                            if ($template_id > 0){
                                $edit=$db->query("Update uploaded_pdfs 
                                                Set extractor_details = '$extractor_boxes', placer_details = '$placer_boxes', ep_details = '$ep_boxes'
                                                Where template_name = '$template_name'
                                                ") ;
                                //echo $edit?'edit':'err'; 
                                //$folder="documents/". $template_name;
                                $folder="documents";
                                unlink($folder."/".$template_name.".json");
                                $myfile = fopen($folder."/".$template_name.".json", "w") or die("Unable to open file!");
                                fwrite($myfile, $pdf_data);              
                                fclose($myfile);
                                $result=array('rstatus' => 'edit');                              
                                echo json_encode($result);                                
                            }else{
                                $result=array('rstatus' => 'exist');
                                echo json_encode($result);  
                            }
                            exit;
                        }else{
                            $insert = $db->query("INSERT INTO uploaded_pdfs (file_name, extractor_details, placer_details, ep_details, template_name, pdf_page,generated_by) VALUES ('".$path."', '".$extractor_boxes."', '".$placer_boxes."', '".$ep_boxes."', '".$template_name."', '".$pdf_page."',".$logged_in_user_id.")");
                            
                            $id=$db -> insert_id;
                            //$folder="documents/". $template_name;
                            $folder="documents";
                            if (!file_exists($folder)) {
                                mkdir($folder);
                            }                           
                            $myfile = fopen($folder."/".$template_name.".json", "w") or die("Unable to open file!");
                            fwrite($myfile, $pdf_data);              
                            fclose($myfile);                                 
                            $result=array('rstatus' => 'insert','id' => $id);
                            echo json_encode($result);
                            //echo $insert?'insert':'err';     
                        }
                    }
                }                 
            }
        } 
        else 
        {
            $extractor_boxes = $_POST['extractor_boxes'];
            $placer_boxes = $_POST['placer_boxes'];
            $ep_boxes = $_POST['ep_boxes'];
            $template_name = $_POST['template_name'];
            $template_id = $_POST['template_id'];
            $pdf_data = $_POST['pdf_data'];       

            if($template_id>0){
                $query = "SELECT `template_name` FROM `uploaded_pdfs` WHERE template_name=?";
                if ($stmt = $db->prepare($query)){
                    $stmt->bind_param("s", $template_name);
                    if($stmt->execute()){
                        $stmt->store_result();
                        $template_name_check= "";         
                        $stmt->bind_result($template_name_check);
                        $stmt->fetch();
                        if ($stmt->num_rows == 1){
                            if ($template_id > 0){
                                $edit=$db->query("Update uploaded_pdfs 
                                                Set extractor_details = '$extractor_boxes', placer_details = '$placer_boxes', ep_details = '$ep_boxes'
                                                Where template_name = '$template_name'
                                                ") ;
                                //echo $edit?'edit':'err'; 
                                //$folder="documents/". $template_name;
                                $folder="documents";
                                /*
                                $folder2="documents/copy_json";
                                if (!file_exists($folder2)) {
                                    mkdir($folder2);
                                }
                                copy( $folder."/".$template_name.".json", $folder."/copy_json/".$template_name."_".time()."_".uniqid().".json" ); //source, destination
                                */
                                unlink($folder."/".$template_name.".json");
                                $myfile = fopen($folder."/".$template_name.".json", "w") or die("Unable to open file!");
                                fwrite($myfile, $pdf_data);              
                                fclose($myfile); 
                                $result=array('rstatus' => 'edit');
                                echo json_encode($result);                                
                            }else{
                                $result=array('rstatus' => 'exist');
                                echo json_encode($result);  
                            }
                            exit;
                        }else{
                            $result=array('rstatus' => 'invalid');
                            echo json_encode($result);
                        }
                    }
                }            
            }else{
                $result=array('rstatus' => 'invalid');
                echo json_encode($result);
            }
        }
    }
