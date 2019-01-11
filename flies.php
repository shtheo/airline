<?php
	session_start ();
	if (isset($_SESSION['admin'])) // on vérifie que l'on est boen connecté en tant qu'administrateur
	{
		include('template.php');
		include('connexion.php');
		if (isset($_GET['action']) && isset($_GET['num_vol'])){ // si l'on veut supprimer un vol
			if ($_GET['action'] == 'delete'){
				$result = $my_db->query("DELETE FROM vol WHERE num_vol='".$_GET['num_vol']."'");
				if ($result == 1) // succès de la suppression
				{
					$_SESSION['delete_sucess'] = 1;
					header('location: flies.php');
					exit(0);
				}
				else{ // echec
					$_SESSION['delete_sucess'] = 0;
					header('location: flies.php');
					exit(0);
				}
			}
		}
		else{ // sinon on affiche la page
			$template = new Template('template/');
			$template->set_filenames(array(
				'body' => 'flies.html'
			));
			if (isset($_SESSION['delete_sucess'])){ // affichage du message après suppression
				if ($_SESSION['delete_sucess'] == 1){ // cas du succès
					$template->assign_vars(array(
						'message_delete' => '<div class="delete_success">Ce vol a bien été supprimé</div>'
					));
					unset($_SESSION['delete_sucess']);
				}
				else{ // echec
					$template->assign_vars(array(
						'message_delete' => '<div class="delete_fail">Ce vol n\'a pas pu être supprimé car il est impliqué dans un certain nombre de départs</div>'
					));
					unset($_SESSION['delete_sucess']);
				}
			}
			if (isset($_SESSION['insert_sucess'])){ // message après création d'un vol
				if ($_SESSION['insert_sucess'] == 1){ // succès
					$template->assign_vars(array(
						'message_insert' => '<div class="delete_success">Ce vol a bien été créé</div>'
					));
					unset($_SESSION['insert_sucess']);
				}
				else{ // echec
					$template->assign_vars(array(
						'message_insert' => '<div class="delete_fail">Ce vol n\'a pas pu être créé</div>'
					));
					unset($_SESSION['insert_sucess']);
				}
			}
			if (isset($_SESSION['modify_sucess'])){ // message après la modification
				if ($_SESSION['modify_sucess'] == 1){ // succès
					$template->assign_vars(array(
						'message_modify' => '<div class="delete_success">Ce vol a bien été modifié</div>'
					));
					unset($_SESSION['modify_sucess']);
				}
				else{ // echec
					$template->assign_vars(array(
						'message_modify' => '<div class="delete_fail">Cet vol n\'a pas pu être modifié</div>'
					));
					unset($_SESSION['modify_sucess']);
				}
			}
			
			// requete pour avoir les vols
			$result = $my_db->query("SELECT num_vol, aeroport_depart, aeroport_arrivee, ville_depart, ville_arrivee, date_debut_periode, date_fin_periode, horaire_depart, horaire_arrivee, arrivee_lendemain, num_appareil FROM vol_complet");
			$color = '#d1c3ef'; // pour alterner les couleurs du tableau
			
			// affichage des vols
			while($row = $result->fetch_array()){
				
				if ($color == '#d1c3ef'){
					$color = '#f0ebfa';
				}
				else
				{
					$color = '#d1c3ef';
				}
				$template->assign_block_vars('vol',array(
					'num_vol' => utf8_encode($row['num_vol']),
					'aeroport_depart'  => utf8_encode($row['ville_depart'].' <br/><span style="font-size:11px">'.$row['aeroport_depart'].'</span>'),
					'aeroport_arrivee' => utf8_encode($row['ville_arrivee'].' <br/><span style="font-size:11px">'.$row['aeroport_arrivee'].'</span>'),
					'date_debut_periode' => utf8_encode($row['date_debut_periode']),
					'date_fin_periode' => utf8_encode($row['date_fin_periode']),
					'horaire_depart' => utf8_encode($row['horaire_depart']),
					'horaire_arrivee' => utf8_encode($row['horaire_arrivee']),
					'arrivee_lendemain' => ($row['arrivee_lendemain'] == 1? 'Oui':'Non'),
					'num_appareil' => utf8_encode($row['num_appareil']),
					'color' => $color
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