<?php
	session_start ();
	include('template.php');
	include('connexion.php');
	if (isset($_SESSION['admin']))// on vérifie que l'on est bien connecté en tant qu'administrateur
	{
		if (isset($_POST['modify_departure_request'])) // si la demande de modification a été faite
		{	
			if (isset($_POST['date_depart']) && $_POST['date_depart'] != ''){ // on vérifie que la nouvelle date de départ n'est pas nulle
				if($_POST['num_pilote1'] != $_POST['num_pilote2'] && $_POST['num_equipage1'] != $_POST['num_equipage2']){ // on vérifie que les membres de l'équipage sont tous différents
					$num_vol = $_POST['num_vol'];
					$date_depart = $_POST['date_depart'];
					$num_pilote1 = $_POST['num_pilote1'];
					$num_pilote2 = $_POST['num_pilote2'];
					$num_equipage1 = $_POST['num_equipage1'];
					$num_equipage2 = $_POST['num_equipage2'];
					$prix = isset($_POST['prix']) && $_POST['prix'] != ''?$_POST['prix']:'0';
					$nbr_places_libres = isset($_POST['nbr_places_libres']) && $_POST['nbr_places_libres'] != ''?$_POST['nbr_places_libres']:'0';
					// execution de la requete de mise à jour
					$result = $my_db->query("UPDATE depart SET date_depart='".$date_depart."', num_vol='".$num_vol."', num_pilote1='".$num_pilote1."',
					num_pilote2='".$num_pilote2."', num_equipage1='".$num_equipage1."', num_equipage2='".$num_equipage2."',
					nbr_places_libres=".$nbr_places_libres.", prix=".$prix." WHERE date_depart='".$_POST['prev_date_depart']."' AND num_vol='".$_POST['prev_num_vol']."'");
					if ($result == 1)
					{ // succès
						$_SESSION['modify_sucess'] = 1;
						header('location: departures.php');
						exit(0);
					}
					else{ // echec
						$_SESSION['modify_sucess'] = 0;
						header('location: departures.php');
						exit(0);
					}
				}
				else{ // sinon redirection avec message 
					$_SESSION['pilotes_equipage_pourris'] = 0;
					header('location: modify_departure.php?num_vol='.$_POST['num_vol'].'&date_depart='.$_POST['date_depart']);
					exit(0);
				}
			}
			else{// sinon redirection avec message 
					$_SESSION['no_date'] = 0;
					header('location: modify_departure.php');
					exit(0);
				}
		}
		else{ // sinon on affiche la page
			$template = new Template('template/');
			$template->set_filenames(array(
				'body' => 'modify_departure.html'
			));
			
			// recuperation et affichage des champs pré-remplis
			$result_global = $my_db->query("SELECT * FROM depart JOIN vol ON vol.num_vol= depart.num_vol WHERE vol.num_vol='".$_GET['num_vol']."' AND depart.date_depart='".$_GET['date_depart']."'");
			$row_global = $result_global->fetch_array();
			$template->assign_vars(array(
				'date_depart' => $row_global['date_depart'],
				'nbr_places_libres' => $row_global['nbr_places_libres'],
				'prix' => $row_global['prix'],
				'prev_num_vol' => $row_global['num_vol'],
				'prev_date_depart' => $row_global['date_depart'] ,
				'date_min' => $row_global['date_debut_periode'],
				'date_max' => $row_global['date_fin_periode']
			));
			
			// message d'erreur pour date de départ
			if (isset($_SESSION['no_date'])){
				$template->assign_vars(array(
					'message_no_date' => '<div class="delete_fail">La date de départ est obligatoire</div>'
				));
				unset($_SESSION['no_date']);	
			}
			
			// message d'erreur pour le personnel
			if (isset($_SESSION['pilotes_equipage_pourris'])){
				$template->assign_vars(array(
					'message_no_date' => '<div class="delete_fail">Les deux pilotes et les deux membres de l\'équipage doivent être différents...</div>'
				));
				unset($_SESSION['pilotes_equipage_pourris']);	
			}
			
			// affiche des options du select de pilotes
			$result = $my_db->query("SELECT * FROM pilote_complet");
			while($row = $result->fetch_array()){
				$template->assign_block_vars('num_pilote1',array(
					'num_secu' => utf8_encode($row['num_secu']),
					'pilote' => utf8_encode($row['nom'].' '.$row['prenom'].' - '.$row['num_secu']),
					'selected' => (($row_global['num_pilote1'] == $row['num_secu']) ?' selected="selected"' : '')
				));
			}
			
			// affichage de l'autre select de pilotes
			$result = $my_db->query("SELECT * FROM pilote_complet");
			while($row = $result->fetch_array()){
				$template->assign_block_vars('num_pilote2',array(
					'num_secu' => utf8_encode($row['num_secu']),
					'pilote' => utf8_encode($row['nom'].' '.$row['prenom'].' - '.$row['num_secu']),
					'selected' => (($row_global['num_pilote2'] == $row['num_secu'])?'selected="selected"':'')
				));
			}
			
			// affichage du select des membres d'équipages
			$result = $my_db->query("SELECT * FROM equipage_complet");
			while($row = $result->fetch_array()){
				$template->assign_block_vars('num_equipage1',array(
					'num_secu' => utf8_encode($row['num_secu']),
					'equipage' => utf8_encode($row['nom'].' '.$row['prenom'].' - '.$row['num_secu']),
					'selected' => (($row_global['num_equipage1'] == $row['num_secu'])?'selected="selected"':'')
				));
			}
			
			// affichage du deuxièeme select des membres d'équpage
			$result = $my_db->query("SELECT * FROM equipage_complet");
			while($row = $result->fetch_array()){
				$template->assign_block_vars('num_equipage2',array(
					'num_secu' => utf8_encode($row['num_secu']),
					'equipage' => utf8_encode($row['nom'].' '.$row['prenom'].' - '.$row['num_secu']),
					'selected' => (($row_global['num_equipage2'] == $row['num_secu'])?'selected="selected"':'')
				));
			}
			
			// affichage du select des vols
			$result = $my_db->query("SELECT * FROM vol_complet");
			while($row = $result->fetch_array()){
				$template->assign_block_vars('num_vol',array(
					'num_vol' => utf8_encode($row['num_vol']),
					'vol' => utf8_encode($row['num_vol'].' ('.$row['ville_depart'].' - '.$row['ville_arrivee'].')'),
					'selected' => (($row_global['num_vol'] == $row['num_vol']) ? 'selected="selected"' : ''),
					'date_min' => $row['date_debut_periode'], // utilisée par le js pour le champs date de départ
					'date_max' => $row['date_fin_periode'] // utilisée par le js pour le champs date de départ
				));
			}
			$template->pparse('body');
		}
	}
	else{
		header('location: home.php');
		exit(0);
	}
?>