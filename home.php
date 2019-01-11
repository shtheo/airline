<?php
	session_start ();
	include('template.php');
	include('connexion.php');
	if (isset($_SESSION['num_passager'])){ // pour déconnecter un passager
		unset($_SESSION['num_passager']);
	}
	if (isset($_SESSION['admin'])){ // pour d"connecter l'admin
		unset($_SESSION['admin']);
	}
	// $my_db = new mysqli('localhost:3308', 'root', '', 'airline');
	if (isset($_POST['login_request'])) // if the connection request has been sent
	{
		if (isset($_POST['login']) && isset($_POST['password']) && $_POST['login']!='' && $_POST['password']!='' ) // si login et mot de passe non vides
		{
			// requête pour voir le login et mot de passe existent bien dans la base
			$query = "SELECT login, mot_de_passe, num_passager FROM user WHERE login='".$_POST['login']."' AND mot_de_passe='".$_POST['password']."'";
			$result = $my_db->query($query);
			if ($result->num_rows > 0) // si oui
			{
				$row = $result->fetch_array();
				if ($row['num_passager'] == NULL) // si c'est l'admin
				{
					$_SESSION['admin'] = 1; // variable de session admin
					header('location: airports.php');
					exit(0);
				}
				else{ // si c'est un passager
					$_SESSION['num_passager'] = $row['num_passager']; // variable de session passager
					header('location: sum_up_departures.php');
					exit(0);
				}
			}
			else // sinon echec de connexion
			{
				$_SESSION['error_connexion'] = 'Mauvais mot de passe ou login'; // affectation d'un message d'erreur
				header('location: home.php');
				exit(0);
			}
		
		}
		else{
			$_SESSION['error_connexion'] = 'Veuillez remplir les champs login ET mot de passe'; // affectation d'un message d'erreur
			header('location: home.php');
			exit(0);
		}
	}
	
	else
	{ // sinon on affiche la page
		$template = new Template('template/');
		$template->set_filenames(array( // recuperation du code html de la page
			'body' => 'home.html'
		));
		if (isset($_SESSION['error_connexion'])) // message suite à une erreur de connexion
		{
			$template->assign_block_vars('error_message',array(
				'message' => $_SESSION['error_connexion'],
			));
			unset($_SESSION['error_connexion']);
		}
		if (isset($_SESSION['account_well_created'])){ // message qui dit que le compte a bien été créé
			$template->assign_vars(array(
				'message' => '<div class="delete_success">Le compte a bien été créé</div>'
			));
			unset($_SESSION['account_well_created']);
		}
		$template->pparse('body');
	}
?>