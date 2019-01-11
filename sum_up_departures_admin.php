<?php
	session_start ();
	if (isset($_SESSION['admin'])) // vérification que l'on est bien connecté en tant qu'admin
	{
		if (isset($_GET['num_passenger']) && $_GET['num_passenger']!='') // vérification que l'on a bien un numéro de passager dans la requete GET
		{
			include('template.php');
			include('connexion.php');
			$template = new Template('template/');
			
			// affichage page html
			$template->set_filenames(array(
				'body' => 'sum_up_departures_admin.html'
			));
			
			// affichage du titre
			$result = $my_db->query("SELECT * FROM passager WHERE num_passager=".$_GET['num_passenger']);
			$row = $result->fetch_array();
			$template->assign_vars(array(
				'nom' => utf8_encode($row['nom']),
				'prenom' => utf8_encode($row['prenom'])
			));
			
			// requete pour avoir les départs lié au passager en question
			$result = $my_db->query("SELECT vol_complet.num_vol AS num_vol, vol_complet.aeroport_depart AS aeroport_depart, vol_complet.ville_depart AS ville_depart,
				vol_complet.aeroport_arrivee AS aeroport_arrivee, vol_complet.ville_arrivee AS ville_arrivee, 
				vol_complet.horaire_depart AS horaire_depart, vol_complet.horaire_arrivee AS horaire_arrivee,
				vol_complet.arrivee_lendemain AS arrivee_lendemain, depart_complet.date_depart AS date_depart, depart_complet.prix as prix,
				billet.num_billet, billet.date_emission
				FROM billet 
				JOIN depart_complet ON (depart_complet.num_vol = billet.num_vol AND depart_complet.date_depart = billet.date_depart)
				JOIN vol_complet ON depart_complet.num_vol = vol_complet.num_vol 
				JOIN depart ON (depart_complet.num_vol = depart.num_vol AND depart_complet.date_depart = depart.date_depart)
				WHERE (billet.num_passager=".$_GET['num_passenger'].") 
				ORDER BY depart_complet.date_depart, vol_complet.horaire_depart");
				$color = '#d1c3ef';
				
			
			if ($result->num_rows > 0){ // si il a pris au moins un billet
				while($row = $result->fetch_array()){ // affichage des départs
					
					if ($color == '#d1c3ef'){
						$color = '#f0ebfa';
					}
					else
					{
						$color = '#d1c3ef';
					}
					$template->assign_block_vars('passager',array(
						'date_depart' => $row['date_depart'],
						'aeroport_depart'  => utf8_encode($row['ville_depart'].' <br/><span style="font-size:11px">'.$row['aeroport_depart'].'</span>'),
						'aeroport_arrivee'  => utf8_encode($row['ville_arrivee'].' <br/><span style="font-size:11px">'.$row['aeroport_arrivee'].'</span>'),
						'horaire_depart'  => $row['horaire_depart'],
						'horaire_arrivee'  => $row['horaire_arrivee'].($row['arrivee_lendemain'] == 1?' (J+1)':''),
						'prix' => $row['prix'],
						'num_billet' => $row['num_billet'],
						'date_emission' => $row['date_emission'],
						'color' => $color
					));
				
				}
			}
				else{ // sinon affichage d'une phrase au lieu d'un tableau vide
					$template->assign_vars(array(
					'message' => '<h3>Ce passager n\'a pas encore acheté de billet...</h3>',
					'hidden' => 'hidden="hidden"'
					));
				}
			$template->pparse('body');
			}
		else{
			header('location: passengers.php');
			exit(0);
		}
	}
	else{
		header('location: home.php');
		exit(0);
	}
?>