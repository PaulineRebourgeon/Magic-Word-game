<?php
	$path = "./LANGUAGE RESOURCES/";
	$languages = array(
		"IT" => array("filenames" => "Parts/DicoIT.txt.sql*Id*.sql",
					  "maxId" => 3),
		"FR" => array("filenames" => "DicoFR*Id*.txt.sql",
					  "maxId" => 3),
		"EN" => array("filenames" => "Parts/DicoENN.txt.sql*Id*.sql",
					  "maxId" => 3)
		/*,//not reliable
		"DE" => array("filenames" => "DicoDE.txt.sql*Id*.sql",
					  "maxId" => 63),
		"ES" => array("filenames" => "DicoES*Id*.txt.sql",
					  "maxId" => 1)*/
		);
	foreach ($languages as $code => $data){
		for($i=0;$i<=$data['maxId'];$i++){
			/**/echo $path.$code."/".str_replace('*Id*',$i,$data['filenames'])."<br />";
			load_schema($path.$code."/".str_replace('*Id*',$i,$data['filenames']));
		}
	}
?>