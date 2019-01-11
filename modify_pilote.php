<?php
	session_start ();
	if (isset($_SESSION['admin']))// on vérifie que l'on est bien connecté en tant qu'administrateur
	{
		include('template.php');
		include('connexion.php');
		if (isset($_POST['modify_pilote_request'])) // si l'on demande à modifier le pilote
		{
			if(isset($_POST['num_licence']) && $_POST['num_licence'] != '') // on vérifie que la nouvelle licence est valide
			{	
				$nom = (isset($_POST['nom'])?addslashes(utf8_decode($_POST['nom'])):'');
				$prenom = (isset($_POST['prenom'])?addslashes(utf8_decode($_POST['prenom'])):'');
				$adresse = (isset($_POST['adresse'])?addslashes(utf8_decode($_POST['adresse'])):'');
				$salaire = ((isset($_POST['salaire']) && $_POST['salaire']!='')?$_POST['salaire']:'NULL');
				
				// on execute la requete de mise à jour de la table personnel
				$result = $my_db->query("UPDATE personnel SET nom='".$nom."', prenom='".$prenom."', adresse='".$adresse."', salaire='".$salaire."' WHERE num_secu='".$_POST['prev_num_secu']."'");
				if ($result != 1) // si echec redirection directe
				{
					$_SESSION['modify_sucess'] = 0;
					header('location: employees.php');
					exit(0);
				}
				
				// ensuite on met à jour la table pilote
				$result = $my_db->query("UPDATE pilote SET num_licence='".$_POST['num_licence']."' WHERE num_secu='".$_POST['prev_num_secu']."'");
				if ($result == 1) // succes
				{
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
			else{
				$_SESSION['num_licence_vide'] = 1;
				header('location: add_pilote.php');
				exit(0);
			}
		}
		elseif (isset($_GET['num_secu']) &&  $_GET['num_secu'] != '')
		{ // sinon on vérifie que l'on a bien un numéro de sécurité sociale et on affiche la page
			$template = new Template('template/');
			
			// affichage de la page html
			$template->set_filenames(array(
				'body' => 'modify_pilote.html'
			));
			
			// message si num securité social vide
			if (isset($_SESSION['num_secu_vide']))
			{
				$template->assign_vars(array(
					'num_secu_vide' => '<div class="delete_fail">Veuillez entrer un numéro de sécurité social</div>'
				));
				unset($_SESSION['num_secu_vide']);
			}
			
			// message si num licence est vide
			if (isset($_SESSION['num_licence_vide']))
			{
				$template->assign_vars(array(
					'num_licence_vide' => '<div class="delete_fail">Veuillez entrer un numéro de licence</div>'
				));
				unset($_SESSION['num_licence_vide']);
			}
			
			// recupération des information et affichage des champs pré-remplis
			$result = $my_db->query("SELECT * FROM pilote_complet WHERE num_secu='".$_GET['num_secu']."'");
			$row = $result->fetch_array();
			$template->assign_vars(array(
				'num_secu' => $row['num_secu'],
				'nom' => utf8_encode($row['nom']),
				'prenom' => utf8_encode($row['prenom']),
				'adresse' => utf8_encode($row['adresse']),
				'salaire' => $row['salaire'],
				'num_licence' => $row['num_licence'],
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