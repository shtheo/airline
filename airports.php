<?php
	session_start ();
	if (isset($_SESSION['admin'])) // on vérifie que l'on est bien connecté en tant qu'admin
	{
		include('template.php');
		include('connexion.php');
		$template = new Template('template/');
		$template->set_filenames(array(
			'body' => 'airports.html'
		));
		
		// requete pour avoir tous les aéroports
		$result = $my_db->query("SELECT code, nom, ville, pays FROM aeroport");
		$color = '#d1c3ef';
		
		// affichage des aéroports
		while($row = $result->fetch_array()){
			
			if ($color == '#d1c3ef'){
				$color = '#f0ebfa';
			}
			else
			{
				$color = '#d1c3ef';
			}
			$template->assign_block_vars('aeroport',array(
				'code' => utf8_encode($row['code']),
				'nom'  => utf8_encode($row['nom']),
				'ville' => utf8_encode($row['ville']),
				'pays'  => '<img width="30px" height="20px" src="pictures/'.utf8_encode($row['pays']).'.png"> '.utf8_encode($row['pays']),
				'color' => $color
			));
		
		}
		$template->pparse('body');
	}
	else{
		header('location: home.php');
		exit(0);
	}
?>