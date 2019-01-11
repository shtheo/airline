<?php
	session_start ();
	if (isset($_SESSION['admin'])) // on vérifie que l'on est bien connecté en tant qu'admin
	{
		include('template.php');
		include('connexion.php');
		if (isset($_GET['code']) && $_GET['code'] != ''){ // on vérifie qu'on a bien un code d'aéroport via la requête GET
		
			$template = new Template('template/');
			$template->set_filenames(array(
				'body' => 'airport_departures.html'
			));
			
			// affichage du titre
			$result_nom = $my_db->query("SELECT nom FROM aeroport WHERE code='".$_GET['code']."'");
			$row_nom = $result_nom->fetch_array();
			$template->assign_vars(array(
				'nom' => utf8_encode($row_nom['nom'])
			));
			
			// requete pour avoir les départs
			$result_depart = $my_db->query("SELECT vol_complet.num_vol AS num_vol,
			vol_complet.aeroport_arrivee AS aeroport_arrivee, vol_complet.ville_arrivee AS ville_arrivee, 
			vol_complet.horaire_depart AS horaire_depart, depart_complet.date_depart AS date_depart, vol_complet.num_appareil AS num_appareil
			FROM depart_complet JOIN vol_complet ON depart_complet.num_vol = vol_complet.num_vol 
			JOIN vol ON vol_complet.num_vol=vol.num_vol 
			WHERE vol.code_aeroport_depart='".$_GET['code']."' ORDER BY date_depart ASC, vol_complet.horaire_depart ASC");
			
			// affichage des départs
			$color = '#d1c3ef';
			while($row_depart = $result_depart->fetch_array()){
				
				if ($color == '#d1c3ef'){
					$color = '#f0ebfa';
				}
				else
				{
					$color = '#d1c3ef';
				}
				
				// affichage d'une ligne de départ
				$template->assign_block_vars('depart',array(
					'date_depart' => $row_depart['date_depart'],
					'horaire_depart'  => $row_depart['horaire_depart'],
					'aeroport_arrivee'  => utf8_encode($row_depart['ville_arrivee'].' <br/><span style="font-size:11px">'.$row_depart['aeroport_arrivee'].'</span>'),
					'num_vol'  => $row_depart['num_vol'],
					'num_appareil'  => $row_depart['num_appareil'],
					'color' => $color
				));
			
			}
			
			// requête pour avoir les vols à l'arrivée
			$result_arrivee = $my_db->query("SELECT vol_complet.num_vol AS num_vol,
			vol_complet.aeroport_depart AS aeroport_depart, vol_complet.ville_depart AS ville_depart, 
			vol_complet.horaire_arrivee AS horaire_arrivee, date_arrivee(depart_complet.date_depart, vol_complet.arrivee_lendemain) AS date_arrivee, vol_complet.num_appareil AS num_appareil
			FROM depart_complet JOIN vol_complet ON depart_complet.num_vol = vol_complet.num_vol 
			JOIN vol ON vol_complet.num_vol=vol.num_vol 
			WHERE vol.code_aeroport_arrivee='".$_GET['code']."' ORDER BY date_arrivee ASC, vol_complet.horaire_depart ASC");
			
			// affichage des arrivées
			$color = '#d1c3ef';
			while($row_arrivee = $result_arrivee->fetch_array()){
				
				if ($color == '#d1c3ef'){
					$color = '#f0ebfa';
				}
				else
				{
					$color = '#d1c3ef';
				}
				
				// affichage d'une ligne arrivée
				$template->assign_block_vars('arrivee',array(
					'date_arrivee' => $row_arrivee['date_arrivee'],
					'horaire_arrivee'  => $row_arrivee['horaire_arrivee'],
					'aeroport_depart'  => utf8_encode($row_arrivee['ville_depart'].' <br/><span style="font-size:11px">'.$row_arrivee['aeroport_depart'].'</span>'),
					'num_vol'  => $row_arrivee['num_vol'],
					'num_appareil'  => $row_arrivee['num_appareil'],
					'color' => $color
				));
			
			}
			$template->pparse('body');
		}
		else{
			header('location: airports.php');
			exit(0);
		}
	}
	else{
		header('location: home.php');
		exit(0);
	}
?>