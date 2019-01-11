<?php
	session_start ();
	if (isset($_SESSION['admin'])) // on vérifie que l'on est bien connecté en tant qu'adminsitrateur
	{
		include('template.php');
		include('connexion.php');
		if (isset($_GET['num_vol']) && $_GET['num_vol'] != ''){ // on vérifie qu'un numéro de vol a bien été donné par la requête GET
			$template = new Template('template/');
			$template->set_filenames(array(
				'body' => 'fly_departures.html'
			));
			
			// affichage du titre
			$template->assign_vars(array(
				'num_vol' => $_GET['num_vol']
			));
			
			// recupération des départs lié au vol en question
			$result = $my_db->query("SELECT * FROM depart_complet WHERE num_vol='".$_GET['num_vol']."'");
			$color = '#d1c3ef';
			
			// affichage de ces départs
			while($row = $result->fetch_array()){
				
				if ($color == '#d1c3ef'){
					$color = '#f0ebfa';
				}
				else
				{
					$color = '#d1c3ef';
				}
				$template->assign_block_vars('depart',array(
					'date_depart' => $row['date_depart'],
					'pilote1'  => utf8_encode($row['nom_pilote1']).' '.utf8_encode($row['prenom_pilote1']),
					'pilote2'  => utf8_encode($row['nom_pilote2']).' '.utf8_encode($row['prenom_pilote2']),
					'equipage1'  => utf8_encode($row['nom_equipage1']).' '.utf8_encode($row['prenom_equipage1']),
					'equipage2'  => utf8_encode($row['nom_equipage2']).' '.utf8_encode($row['prenom_equipage2']),
					'nbr_places_libres'  => $row['nbr_places_libres'],
					'nbr_places_occupes'  => $row['nbr_places_occupes'],
					'color' => $color
				));
			
			}
		}
		else{
			header('location: flies.php');
			exit(0);
		}
		$template->pparse('body');
	}
	else{
		header('location: home.php');
		exit(0);
	}
?>