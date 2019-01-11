<?php
	session_start ();
	if (isset($_SESSION['admin'])) // on vérifie que l'on est bien connecté en tant qu'administrateur
	{
		include('template.php');
		include('connexion.php');
		if (isset($_GET['action']) && isset($_GET['num_vol']) && isset($_GET['date_depart'])){
			if ($_GET['action'] == 'delete'){ // si une suppression a été demandée
				$result = $my_db->query("DELETE FROM depart WHERE num_vol=".$_GET['num_vol']." AND date_depart='".$_GET['date_depart']."'");
				if ($result == 1) // succès de la suppression
				{
					$_SESSION['delete_sucess'] = 1;
					header('location: departures.php');
					exit(0);
				}
				else{ // echec de la suppression
					$_SESSION['delete_sucess'] = 0;
					header('location: departures.php');
					exit(0);
				}
			}
		}
		else{ // sinon on affiche les départs
			$template = new Template('template/');
			$template->set_filenames(array(
				'body' => 'departures.html'
			));
			if (isset($_SESSION['delete_sucess'])){ // message pour la suppression
				if ($_SESSION['delete_sucess'] == 1){ // succès
					$template->assign_vars(array(
						'message_delete' => '<div class="delete_success">Ce départ a bien été supprimé</div>'
					));
					unset($_SESSION['delete_sucess']);
				}
				else{
					$template->assign_vars(array( // echec
						'message_delete' => '<div class="delete_fail">Ce départ n\'a pas pu être supprimé car il est impliqué dans un certain nombre de billets</div>'
					));
					unset($_SESSION['delete_sucess']);
				}
			}
			if (isset($_SESSION['insert_sucess'])){ // message pour l'insertion
				if ($_SESSION['insert_sucess'] == 1){ // succès
					$template->assign_vars(array(
						'message_insert' => '<div class="delete_success">Ce départ a bien été créé</div>'
					));
					unset($_SESSION['insert_sucess']);
				}
				else{ // echec
					$template->assign_vars(array(
						'message_insert' => '<div class="delete_fail">Ce départ n\'a pas pu être créé</div>'
					));
					unset($_SESSION['insert_sucess']);
				}
			}
			if (isset($_SESSION['modify_sucess'])){ // message de modification
				if ($_SESSION['modify_sucess'] == 1){ // succès
					$template->assign_vars(array(
						'message_modify' => '<div class="delete_success">Ce départ a bien été modifié</div>'
					));
					unset($_SESSION['modify_sucess']);
				}
				else{ // echec
					$template->assign_vars(array(
						'message_modify' => '<div class="delete_fail">Cet départ n\'a pas pu être modifié</div>'
					));
					unset($_SESSION['modify_sucess']);
				}
			}
			
			// affichage des départs
			$result = $my_db->query("SELECT * FROM depart_complet");
			$color = '#d1c3ef';
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
					'num_vol'  => $row['num_vol'],
					'pilote1'  => utf8_encode($row['nom_pilote1']).' '.utf8_encode($row['prenom_pilote1']),
					'pilote2'  => utf8_encode($row['nom_pilote2']).' '.utf8_encode($row['prenom_pilote2']),
					'equipage1'  => utf8_encode($row['nom_equipage1']).' '.utf8_encode($row['prenom_equipage1']),
					'equipage2'  => utf8_encode($row['nom_equipage2']).' '.utf8_encode($row['prenom_equipage2']),
					'nbr_places_libres'  => $row['nbr_places_libres'],
					'nbr_places_occupes'  => $row['nbr_places_occupes'],
					'prix' => $row['prix'],
					'color' => $color
				));
			
			}
		}
		$template->pparse('body');
	}
	else{
		header('location: home.php');
		exit(0);
	}
?>