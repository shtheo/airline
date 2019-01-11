<?php
	session_start ();
	if (isset($_SESSION['admin'])) // on vérifie que l'on est bien connecté en tant qu'utilisateur
	{
		include('template.php');
		include('connexion.php');
		if (isset($_GET['num_immatriculation']) && $_GET['num_immatriculation'] != ''){ // on vérifie que la page a été appelée avec un num d'immatriculation dans la requete GET
			$template = new Template('template/');
			$template->set_filenames(array(
				'body' => 'aircraft_departures.html'
			));
			// pour l'affichage du titre
			$template->assign_vars(array( 
				'num_immatriculation' => $_GET['num_immatriculation']
			));
			// requete pour avoir des informations sur les départs dans lequel cet avion est impliqué
			$result = $my_db->query("SELECT vol_complet.num_vol AS num_vol, vol_complet.aeroport_depart AS aeroport_depart, vol_complet.ville_depart AS ville_depart,
			vol_complet.aeroport_arrivee AS aeroport_arrivee, vol_complet.ville_arrivee AS ville_arrivee, 
			vol_complet.horaire_depart AS horaire_depart, vol_complet.horaire_arrivee AS horaire_arrivee,
			vol_complet.arrivee_lendemain AS arrivee_lendemain, depart_complet.date_depart AS date_depart
			FROM depart_complet JOIN vol_complet ON depart_complet.num_vol = vol_complet.num_vol WHERE vol_complet.num_appareil='".$_GET['num_immatriculation']."' ORDER BY depart_complet.date_depart");
			$color = '#d1c3ef';
			while($row = $result->fetch_array()){
				
				if ($color == '#d1c3ef'){
					$color = '#f0ebfa';
				}
				else
				{
					$color = '#d1c3ef';
				}
				// affichage d'une ligne de départ
				$template->assign_block_vars('appareil',array(
					'date_depart' => $row['date_depart'],
					'aeroport_depart'  => utf8_encode($row['ville_depart'].' <br/><span style="font-size:11px">'.$row['aeroport_depart'].'</span>'),
					'aeroport_arrivee'  => utf8_encode($row['ville_arrivee'].' <br/><span style="font-size:11px">'.$row['aeroport_arrivee'].'</span>'),
					'horaire_depart'  => $row['horaire_depart'],
					'horaire_arrivee'  => $row['horaire_arrivee'].($row['arrivee_lendemain'] == 1?' (J+1)':''),
					'num_vol'  => $row['num_vol'],
					'color' => $color
				));
			
			}
			$template->pparse('body');
		}
		else{
			header('location: aircrafts.php');
			exit(0);
		}
	}
	else{
		header('location: home.php');
		exit(0);
	}
	
?>