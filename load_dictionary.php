<?php
	$path = "./LANGUAGE RESOURCES/";
	$languages = array(
		"IT" => array("filenames" => "dico_it_expe.sql",
					  "maxId" => 0),
		"FR" => array("filenames" => "dico_fr_expe.sql",
					  "maxId" => 0),
		"EN" => array("filenames" => "dico_en_expe.sql",
					  "maxId" => 0)
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