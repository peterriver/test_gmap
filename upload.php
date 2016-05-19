<?php 
  header('Content-Type: application/json');
  
  $message = '';
  $csv_sep = ';';
  $datas = array();

  if(isset($_FILES['file'])){
  	$myfile = $_FILES['file'];
  	$ext = pathinfo($myfile['name'], PATHINFO_EXTENSION);
  	if($myfile["type"] != 'application/vnd.ms-excel' && $myfile["type"] != 'text/comma-separated-values' || strtolower($ext) != 'csv'){
  	  $message = '!!! Veuillez déposer un fichier de type CSV !!!';
  	} else if($myfile["error"] != 0) {
	  $message = 'Une erreur est survenue avec votre fichier';
  	} else {
      if (($handle = fopen($myfile["tmp_name"], "r")) !== FALSE) {
		while (($tmp_data = fgetcsv($handle, 1000, $csv_sep)) !== FALSE) {
		  if(isset($tmp_data[0]) && isset($tmp_data[1]) && isset($tmp_data[2])) {
		  	if(strtolower($tmp_data[0])!="addresse" && strtolower($tmp_data[1]!="code_postal") && strtolower($tmp_data[2])!="commune") {
		  		  $address = trim($tmp_data[0]);
		  		  $zip = trim($tmp_data[1]);
		  		  $city = trim($tmp_data[2]);
		  		  $datas[] = array('address' => $address, 'zip' => $zip, 'city' => $city);
		  	}
		  }
		}
		fclose($handle);
		$datas = json_encode($datas);
		echo $datas;
	  }
  	}
  }