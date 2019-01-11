<?php
	session_start ();
	include('template.php');
	include('connexion.php');
	if (isset($_POST['add_passager_request'])) // si la demande de création vient d'être faite
	{
		if($_POST['user']!='' && $_POST['mdp']!='' && $_POST['nom']!='' && $_POST['prenom']!='' && $_POST['adresse']!=''){ // on vérifie que les champs ne sont pas vides
			$result = $my_db->query("SELECT * FROM user WHERE login='".$_POST['user']."'");
			if ($result->num_rows > 0) //le login existe deja
			{
				$_SESSION['login_already_exists'] = 1;
				header('location: create_account.php');
				exit(0);
			}
			else{ // sinon on peut créer d'abord le passager puis le user
				$result = $my_db->query("INSERT INTO passager (nom, prenom, adresse) VALUES ('".utf8_decode($_POST['nom'])."', '".utf8_decode($_POST['prenom'])."', '".utf8_decode($_POST['adresse'])."');");
				$last_id = $my_db->insert_id;
				$result = $my_db->query("INSERT INTO user (login, mot_de_passe, num_passager) VALUES ('".utf8_decode($_POST['user'])."', '".utf8_decode($_POST['mdp'])."', ".$last_id.")");
				$_SESSION['account_well_created'] = 1;
				header('location: home.php');
				exit(0);
			}
		}
		else{ // sinon on redirige vers la même page avec une variable de session permettant d'afficher un message
			$_SESSION['all_fields'] = 1;
			header('location: create_account.php');
			exit(0);
		}
	}	
	else
	{ // sinon on affiche le formulaire
		$template = new Template('template/');
		$template->set_filenames(array(
			'body' => 'create_account.html'
		));
		if (isset($_SESSION['all_fields'])){ // message si certains champs sont vides
			$template->assign_vars(array(
				'message' => '<div class="delete_fail">Veuillez remplir tous les champs !</div>'
			));
			unset($_SESSION['all_fields']);
		}
		if (isset($_SESSION['login_already_exists'])){ // message si le login existe déjà
			$template->assign_vars(array(
				'message' => '<div class="delete_fail">Ce login esxiste déjà...</div>'
			));
			unset($_SESSION['login_already_exists']);
		}
		$template->pparse('body');
	}
?>