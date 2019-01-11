<?php
	session_start ();
	if (isset($_SESSION['num_passager'])) // vérification que l'on est bien connecté en tant que passager
	{
		include('template.php');
		include('connexion.php');
		if (isset($_POST['modify_passager_request'])) // si le passager a envoyé le formulaire de demande de modifications d'informations le concernant
		{
			if($_POST['nom']!='' && $_POST['prenom']!='' && $_POST['adresse']!=''){ // on vérifie que les champs ne soient pas vides
				//var_dump("UPDATE passager nom='".$_POST['nom']."', prenom='".$_POST['prenom']."', adresse='".$_POST['adresse']."' WHERE num_passager=".$_SESSION['num_passager']);exit(0);
				$result = $my_db->query("UPDATE passager SET nom='".utf8_decode($_POST['nom'])."', prenom='".utf8_decode($_POST['prenom'])."', adresse='".utf8_decode($_POST['adresse'])."' WHERE num_passager=".$_SESSION['num_passager']);
				$_SESSION['account_well_modified'] = 1;
				header('location: update_profile.php');
				exit(0);
			}
			else{ // sinon on redirige et on affiche un message d'erreur
				$_SESSION['all_fields'] = 1;
				header('location: update_profile.php');
				exit(0);
			}
		}	
		else
		{ // sinon on affiche la page
			$template = new Template('template/');
			$template->set_filenames(array(
				'body' => 'update_profile.html'
			));
			
			// affichage du message si tous les champs ne sont pas remplis
			if (isset($_SESSION['all_fields'])){
				$template->assign_vars(array(
					'message' => '<div class="delete_fail">Veuillez remplir tous les champs !</div>'
				));
				unset($_SESSION['all_fields']);
			}
			
			// affichage du message de succès de la modification
			if (isset($_SESSION['account_well_modified'])){
				$template->assign_vars(array(
					'message' => '<div class="delete_success">Les informations ont bien été modifiées</div>'
				));
				unset($_SESSION['account_well_modified']);
			}
			
			// récupération des infos et affichage des champs pré-remplis
			$result = $my_db->query("SELECT * FROM passager WHERE num_passager=".$_SESSION['num_passager']);
			$row = $result->fetch_array();
			$template->assign_vars(array(
					'nom' => utf8_encode($row['nom']),
					'prenom' => utf8_encode($row['prenom']),
					'adresse' => utf8_encode($row['adresse'])
				));
			$template->pparse('body');
		}
	}
	else{
		header('location: home.php');
		exit(0);
	}
?>