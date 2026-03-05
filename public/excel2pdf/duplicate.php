<?php 
require('connection.php'); 

if($_POST['id'] != ''){
	$id = $_POST['id'];		
	$query = $conn->prepare("SELECT * FROM `uploaded_pdfs` WHERE id = $id");
	$query->execute();				
	$data = $query->fetch(PDO::FETCH_ASSOC);
	$path=$data['file_name'];
	$extractor_boxes=$data['extractor_details'];
	$placer_boxes=$data['placer_details'];
	$ep_boxes=$data['ep_details'];
    $template_name=$data['template_name'];
    $pdf_page=$data['pdf_page'];
    $generated_by=$data['generated_by'];
	if(empty($data)){
        $result=array('rstatus' => 'invalid');
        echo json_encode($result);	
        return;
	}	
    if (strpos($template_name, '-copy-') !== false) {
        list($before, $after) = explode('-copy-', $template_name);    
        $qry = $conn->prepare("SELECT * FROM `uploaded_pdfs` WHERE template_name LIKE '".$before."-copy-%' order by id desc");
        $qry->execute();				
        $records = $qry->fetch(PDO::FETCH_ASSOC);	
        list($new_before, $new_after) = explode('-copy-', $records['template_name']); 
        $count=$new_after+1;
        $source="documents/".$template_name.".json";
        $dest="documents/".$new_before."-copy-".$count.".json";
        $template_name=$new_before."-copy-".$count;
        
    }else{
        $qry = $conn->prepare("SELECT * FROM `uploaded_pdfs` WHERE template_name LIKE '".$template_name."-copy-%' order by id desc");
        $qry->execute();				
        $records = $qry->fetch(PDO::FETCH_ASSOC);	
        if(empty($records)){
            $source="documents/".$template_name.".json";
            $dest="documents/".$template_name."-copy-1.json";    
            $template_name=$template_name."-copy-1";            
        }else{            
            list($new_before, $new_after) = explode('-copy-', $records['template_name']); 
            $count=$new_after+1;        
            $source="documents/".$template_name.".json";
            $dest="documents/".$template_name."-copy-".$count.".json"; 
            $template_name=$new_before."-copy-".$count;
        }        
    }
    
    copy($source, $dest);
    $conn->query("INSERT INTO uploaded_pdfs (file_name, extractor_details, placer_details, ep_details, template_name, pdf_page, generated_by) 
                VALUES ('".$path."','".$extractor_boxes."','".$placer_boxes."','".$ep_boxes."','".$template_name."', '".$pdf_page."',".$generated_by.")");    
    
    $result=array('rstatus' => 'Success', 'template_name' => $template_name);
    echo json_encode($result);	
}
else{ 
	$result=array('rstatus' => 'missing');
    echo json_encode($result);
	return;
}

?>