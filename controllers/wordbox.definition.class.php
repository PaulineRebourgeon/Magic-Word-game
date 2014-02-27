<?php
// pour pouvoir accéder le lien du dictionnaire du mot seléctioné
	if (isset($_GET['url'])) {
		$url=$_GET['url'];
		$handle=fopen($url,"r");
		if ($handle) {
			while (! feof($handle)) {
				echo fgets($handle);
			}
			fclose($handle);
		} else {
			echo "Impossible d'ouvrir $url<br/>";
		}
	}
	return false;
?>
