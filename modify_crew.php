<?php
	session_start ();
	if (isset($_SESSION['admin']))// on vérifie que l'on est bien connecté en tant qu'administrateur
	{
		include('template.php');
		include('connexion.php');
		if (isset($_POST['modify_crew_request']))
		{	// si la demande de modification a été envoyée
			$nom = (isset($_POST['nom'])?addslashes(utf8_decode($_POST['nom'])):'');
			$prenom = (isset($_POST['prenom'])?addslashes(utf8_decode($_POST['prenom'])):'');
			$adresse = (isset($_POST['adresse'])?addslashes(utf8_decode($_POST['adresse'])):'');
			$salaire = ((isset($_POST['salaire']) && $_POST['salaire']!='')?$_POST['salaire']:'NULL');
			
			// on execute la requete de mise a jour de la table personnel
			$result = $my_db->query("UPDATE personnel SET nom='".$nom."', prenom='".$prenom."', adresse='".$adresse."', salaire='".$salaire."' WHERE num_secu='".$_POST['prev_num_secu']."'");
			if ($result != 1)
			{ // si echec on redirige tout de suite
				$_SESSION['modify_sucess'] = 0;
				header('location: employees.php');
				exit(0);
			}
			
			// on execute la requete de mise a jour de la table pilote
			$result = $my_db->query("UPDATE pilote SET num_licence='".$_POST['num_licence']."' WHERE num_secu='".$_POST['prev_num_secu']."'");
			if ($result == 1)
			{ // succes
				$_SESSION['modify_sucess'] = 1;
				header('location: employees.php');
				exit(0);
			}
			else{ // echec
				$_SESSION['modify_sucess'] = 0;
				header('location: employees.php');
				exit(0);
			}
		}
		elseif (isset($_GET['num_secu']) &&  $_GET['num_secu'] != '') // on vérifie qu'un numéro de sécu a été transmis dans la requête
		{ // sinon on affiche la page
			$template = new Template('template/');
			$template->set_filenames(array( // recup code html
				'body' => 'modify_crew.html'
			));
			
			// recupération et affichage des champs pré-remplis
			$result = $my_db->query("SELECT * FROM equipage_complet WHERE num_secu='".$_GET['num_secu']."'");
			$row = $result->fetch_array();
			$template->assign_vars(array(
				'num_secu' => $row['num_secu'],
				'nom' => utf8_encode($row['nom']),
				'prenom' => utf8_encode($row['prenom']),
				'adresse' => utf8_encode($row['adresse']),
				'salaire' => $row['salaire'],
				'steward_selected' => ($row['fonction'] == 'Steward'?'Selected="Selected"':''),
				'hotesse_selected' => ($row['fonction'] == 'Hotesse'?'Selected="Selected"':''),
				'prev_num_secu' => $_GET['num_secu']
			));
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