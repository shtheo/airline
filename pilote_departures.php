<?php
	session_start ();
	if (isset($_SESSION['admin']))// on vérifie que l'on est bien connecté en tant qu'administrateur
	{
		include('template.php');
		include('connexion.php');
		if (isset($_GET['num_secu']) && $_GET['num_secu'] != ''){ // on vérifie qu'un numéro de sécu a bien été donné dans la requete GET
			$template = new Template('template/');
			
			// affichage de la page html
			$template->set_filenames(array(
				'body' => 'pilote_departures.html'
			));
			
			// affichage du titre
			$result_name = $my_db->query("SELECT * FROM personnel WHERE num_secu='".$_GET['num_secu']."'");
			$row_name = $result_name->fetch_array();
			$template->assign_vars(array(
				'nom' => utf8_encode($row_name['nom']),
				'prenom' => utf8_encode($row_name['prenom'])
			));
			
			// requete pour trouver départs lié au pilote en question
			$result = $my_db->query("SELECT vol_complet.num_vol AS num_vol, vol_complet.aeroport_depart AS aeroport_depart, vol_complet.ville_depart AS ville_depart,
			vol_complet.aeroport_arrivee AS aeroport_arrivee, vol_complet.ville_arrivee AS ville_arrivee, 
			vol_complet.horaire_depart AS horaire_depart, vol_complet.horaire_arrivee AS horaire_arrivee,
			vol_complet.arrivee_lendemain AS arrivee_lendemain, depart_complet.date_depart AS date_depart
			FROM depart_complet JOIN vol_complet ON depart_complet.num_vol = vol_complet.num_vol JOIN depart ON (depart_complet.num_vol = depart.num_vol AND depart_complet.date_depart = depart.date_depart)
			WHERE (depart.num_pilote1='".$_GET['num_secu']."' OR depart.num_pilote2='".$_GET['num_secu']."') 
			ORDER BY depart_complet.date_depart");
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
				$template->assign_block_vars('pilote',array(
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
			header('location: employees.php');
			exit(0);
		}
	}
	else{
		header('location: home.php');
		exit(0);
	}
?>