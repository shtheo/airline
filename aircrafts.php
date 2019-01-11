<?php
	session_start ();
	if (isset($_SESSION['admin'])) // on vérifie qu'on est bien connecté en tant qu'admin
	{
		include('template.php');
		include('connexion.php');
		if (isset($_GET['action']) && isset($_GET['num_immatriculation'])){ 
			if ($_GET['action'] == 'delete'){ // si on a demandé une suppression d'un appareil via une requête GET
				$result = $my_db->query("DELETE FROM appareil WHERE num_immatriculation='".$_GET['num_immatriculation']."'");
				if ($result == 1) // Supression est un succès
				{
					$_SESSION['delete_sucess'] = 1;
					header('location: aircrafts.php');
					exit(0);
				}
				else{ // Suppression est un echec. Ex: avion utilisé dans un vol
					$_SESSION['delete_sucess'] = 0;
					header('location: aircrafts.php');
					exit(0);
				}
			}
		}
		else{ // Sinon on affiche la page
			$template = new Template('template/');
			$template->set_filenames(array(
				'body' => 'aircrafts.html'
			));
			if (isset($_SESSION['delete_sucess'])){ // affichage du message de suppression
				if ($_SESSION['delete_sucess'] == 1){ // cas de suppression réussie
					$template->assign_vars(array(
						'message_delete' => '<div class="delete_success">Cet avion a bien été supprimé</div>'
					));
					unset($_SESSION['delete_sucess']);
				}
				else{ // suppression echec
					$template->assign_vars(array(
						'message_delete' => '<div class="delete_fail">Cet avion ne peut pas être supprimé car il intervient dans des vols</div>'
					));
					unset($_SESSION['delete_sucess']);
				}
			}
			if (isset($_SESSION['insert_sucess'])){ // affichage du message de création
				if ($_SESSION['insert_sucess'] == 1){ // cas du succès de la création
					$template->assign_vars(array(
						'message_insert' => '<div class="delete_success">Cet avion a bien été créé</div>'
					));
					unset($_SESSION['insert_sucess']);
				}
				else{ // cas de l'echec
					$template->assign_vars(array(
						'message_insert' => '<div class="delete_fail">Cet avion n\'a pas pu être créé</div>'
					));
					unset($_SESSION['insert_sucess']);
				}
			}
			if (isset($_SESSION['modify_sucess'])){ // affichage du message de modification
				if ($_SESSION['modify_sucess'] == 1){ // cas du succès
					$template->assign_vars(array(
						'message_modify' => '<div class="delete_success">Cet avion a bien été modifié</div>'
					));
					unset($_SESSION['modify_sucess']);
				}
				else{
					$template->assign_vars(array( // cas de l'echec
						'message_modify' => '<div class="delete_fail">Cet avion n\'a pas pu être modifié</div>'
					));
					unset($_SESSION['modify_sucess']);
				}
			}
			// affichage de la liste d'avions
			$result = $my_db->query("SELECT num_immatriculation, type FROM appareil");
			$color = '#d1c3ef';
			while($row = $result->fetch_array()){
				
				if ($color == '#d1c3ef'){
					$color = '#f0ebfa';
				}
				else
				{
					$color = '#d1c3ef';
				}
				$template->assign_block_vars('appareil',array(
					'num_immatriculation' => $row['num_immatriculation'],
					'type'  => $row['type'],
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