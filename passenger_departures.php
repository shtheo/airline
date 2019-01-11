<?php
	session_start ();
	if (isset($_SESSION['admin']))// on vérifie que l'on est bien connecté en tant qu'administrateur
	{
		if (isset($_GET['num_vol']) && $_GET['num_vol']!='' && isset($_GET['date_depart']) && $_GET['date_depart']!='')
		{ // on vérifie que l'on a bien un numéro de vol et une date de départ dans le GET pour identifier le départ
			include('template.php');
			include('connexion.php');
			$template = new Template('template/');
			
			// affichage du html
			$template->set_filenames(array(
				'body' => 'passenger_departures.html'
			));
			
			// affichage du titre
			$template->assign_vars(array(
				'num_vol' => $_GET['num_vol'],
				'date_depart' => $_GET['date_depart']
			));
			
			// affichage des départs dans lesquels le passager est inscrit
			$result = $my_db->query("SELECT date_emission, nom, prenom, adresse 
			FROM billet 
			JOIN passager ON billet.num_passager = passager.num_passager 
			WHERE date_depart='".$_GET['date_depart']."' AND num_vol=".$_GET['num_vol']);
			$color = '#d1c3ef';
			$nombre = 0;
			while($row = $result->fetch_array()){
				$nombre += 1;
				if ($color == '#d1c3ef'){
					$color = '#f0ebfa';
				}
				else
				{
					$color = '#d1c3ef';
				}
				$template->assign_block_vars('passager',array(
					'date_emission' => utf8_encode($row['date_emission']),
					'nom'  => utf8_encode($row['nom']),
					'prenom' => utf8_encode($row['prenom']),
					'adresse'  => utf8_encode($row['adresse']),
					'color' => $color,
					'nombre' => $nombre
				));
			
			}
			$template->pparse('body');
		}
		else{
			header('location: departures.php');
			exit(0);
		}
	}
	else{
		header('location: home.php');
		exit(0);
	}
?>