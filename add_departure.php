<?php
	session_start ();
	if (isset($_SESSION['admin'])) // on regarde que l'on est bien connecté en tant qu'admin
	{
		include('template.php');
		include('connexion.php');
		if (isset($_POST['add_departure_request'])) // si l'utilisateur a envoyé le formulaire
		{	
			if (isset($_POST['date_depart']) && $_POST['date_depart'] != ''){ // on verifie que la date de départ n'est pas vide
				if($_POST['num_pilote1'] != $_POST['num_pilote2'] && $_POST['num_equipage1'] != $_POST['num_equipage2']){ // on vérifie que les deux pilotes sont différents et que les membres de l'équipage aussi
					$num_vol = $_POST['num_vol'];
					$date_depart = $_POST['date_depart'];
					$num_pilote1 = $_POST['num_pilote1'];
					$num_pilote2 = $_POST['num_pilote2'];
					$num_equipage1 = $_POST['num_equipage1'];
					$num_equipage2 = $_POST['num_equipage2'];
					$nbr_places_libres = isset($_POST['nbr_places_libres']) && $_POST['nbr_places_libres'] != ''?$_POST['nbr_places_libres']:'0';
					$prix = isset($_POST['prix']) && $_POST['prix'] != ''?$_POST['prix']:'0';
					// insertion dans la table départ
					$result = $my_db->query("INSERT INTO depart (date_depart, num_vol, num_pilote1, num_pilote2, num_equipage1, num_equipage2, nbr_places_libres, nbr_places_occupes, prix) 
					VALUES ('".$date_depart."', '".$num_vol."', '".$num_pilote1."', '".$num_pilote2."', '".$num_equipage1."', ".$num_equipage2.", ".$nbr_places_libres.", 0, ".$prix.")");
					if ($result == 1)
					{
						// on redirige vers le recap des depart avec la variable de session pour afficher le popup de succès
						$_SESSION['insert_sucess'] = 1; 
						header('location: departures.php');
						exit(0);
					}
					else{
						// on redirige vers le recap des depart avec la variable de session pour afficher le popup d'echec
						$_SESSION['insert_sucess'] = 0;
						header('location: departures.php');
						exit(0);
					}
				}
				else{ // on redirige vers la même page avec variable de session pour afficher un message pour mieux renseigner les pilotes
					$_SESSION['pilotes_equipage_pourris'] = 0;
					header('location: add_departure.php');
					exit(0);
				}
			}
			else{
					// on redirige vers la meme page en demandant d remplir la date
					$_SESSION['no_date'] = 0;
					header('location: add_departure.php');
					exit(0);
				}
		}
		else{ // sinon on affiche le formulaire
			$template = new Template('template/');
			$template->set_filenames(array(
				'body' => 'add_departure.html'
			));
			if (isset($_SESSION['no_date'])){ // affichage du popup sur la date de départ
				$template->assign_vars(array(
					'message_no_date' => '<div class="delete_fail">La date de départ est obligatoire</div>'
				));
				unset($_SESSION['no_date']);	
			}
			if (isset($_SESSION['pilotes_equipage_pourris'])){ // affichage du popup sur les pilotes et membres
				$template->assign_vars(array(
					'message_no_date' => '<div class="delete_fail">Les deux pilotes et les deux membres de l\'équipage doivent être différents...</div>'
				));
				unset($_SESSION['pilotes_equipage_pourris']);	
			}
			// affichage de la liste des pilotes
			$result = $my_db->query("SELECT * FROM pilote_complet");
			while($row = $result->fetch_array()){
				$template->assign_block_vars('num_pilote1',array(
					'num_secu' => utf8_encode($row['num_secu']),
					'pilote' => utf8_encode($row['nom'].' '.$row['prenom'].' - '.$row['num_secu'])
				));
			}
			// affichage de la seconde liste des pilotes
			$result = $my_db->query("SELECT * FROM pilote_complet");
			while($row = $result->fetch_array()){
				$template->assign_block_vars('num_pilote2',array(
					'num_secu' => utf8_encode($row['num_secu']),
					'pilote' => utf8_encode($row['nom'].' '.$row['prenom'].' - '.$row['num_secu'])
				));
			}
			// affichage de la liste des membres d'équipage
			$result = $my_db->query("SELECT * FROM equipage_complet");
			while($row = $result->fetch_array()){
				$template->assign_block_vars('num_equipage1',array(
					'num_secu' => utf8_encode($row['num_secu']),
					'equipage' => utf8_encode($row['nom'].' '.$row['prenom'].' - '.$row['num_secu'])
				));
			}
			// affichage de la seconde liste des membres d'équipage
			$result = $my_db->query("SELECT * FROM equipage_complet");
			while($row = $result->fetch_array()){
				$template->assign_block_vars('num_equipage2',array(
					'num_secu' => utf8_encode($row['num_secu']),
					'equipage' => utf8_encode($row['nom'].' '.$row['prenom'].' - '.$row['num_secu'])
				));
			}
			// affichage de la liste des vols
			$result = $my_db->query("SELECT * FROM vol_complet");
			$first_loop = True;
			while($row = $result->fetch_array()){
				if ($first_loop){ // pour initialiser les dates min et max du champs date départ
					$template->assign_vars(array(
						'date_min' => $row['date_debut_periode'],
						'date_max' => $row['date_fin_periode']
					));
					$first_loop = False;
				}
				// date min et max servent au code js pour mettre a jour les bornes de la date de départ
				$template->assign_block_vars('num_vol',array(
					'num_vol' => utf8_encode($row['num_vol']),
					'vol' => utf8_encode($row['num_vol'].' ('.$row['ville_depart'].' - '.$row['ville_arrivee'].')'),
					'date_min' => $row['date_debut_periode'], 
					'date_max' => $row['date_fin_periode']
				));
			}
			$template->pparse('body');
		}
	}
	else{ // si pas connecté on redirige vers la page d'accueil
		header('location: home.php');
		exit(0);
	}
?>