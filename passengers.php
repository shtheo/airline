<?php
	session_start ();
	if (isset($_SESSION['admin']))// on vérifie que l'on est bien connecté en tant qu'administrateur
	{
		include('template.php');
		include('connexion.php');
		$template = new Template('template/');
		
		// affichage de la page html
		$template->set_filenames(array(
			'body' => 'passengers.html'
		));
		
		// recupération des passagers
		$result = $my_db->query("SELECT num_passager, nom, prenom, adresse FROM passager");
		$color = '#d1c3ef';
		
		// affichage de la requête
		while($row = $result->fetch_array()){
			
			if ($color == '#d1c3ef'){
				$color = '#f0ebfa';
			}
			else
			{
				$color = '#d1c3ef';
			}
			$template->assign_block_vars('passager',array(
				'num_passager' => utf8_encode($row['num_passager']),
				'nom'  => utf8_encode($row['nom']),
				'prenom' => utf8_encode($row['prenom']),
				'adresse'  => utf8_encode($row['adresse']),
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