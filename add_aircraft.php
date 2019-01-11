<?php
	session_start ();
	if (isset($_SESSION['admin'])) // verifie que la personne est bien l'administrateur
	{
		include('template.php');
		include('connexion.php');
		if (isset($_POST['add_aircraft_request'])) // si l'utilisateur valide le formulaire
		{
			if (isset($_POST['num_immatriculation']) && $_POST['num_immatriculation'] != '') // on verifie que la plaque d'immatriculation soit bien saisie
			{
				// requete d'insertion du nouvel avion
				$result = $my_db->query("INSERT INTO appareil (num_immatriculation, type) VALUES ('".$_POST['num_immatriculation']."', '".(isset($_POST['type'])?$_POST['type']:'')."')");
				if ($result == 1) // requete bien executee
				{
					$_SESSION['insert_sucess'] = 1;
					header('location: aircrafts.php');
					exit(0);
				}
				else{ // requete mal executee --> erreur
					$_SESSION['insert_sucess'] = 0;
					header('location: aircrafts.php');
					exit(0);
				}
			}
			else{
				$_SESSION['insert_sucess'] = 0;
				header('location: aircrafts.php');
				exit(0);
			}
		}
		else{
			$template = new Template('template/');
			$template->set_filenames(array(
				'body' => 'add_aircraft.html'
			));
			$template->pparse('body');
		}
	}
	else{ // si la personne pas admin, redirigée vers la page d'accueil
		header('location: home.php');
		exit(0);
	}
?>