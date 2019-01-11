<?php
	session_start ();
	if (isset($_SESSION['admin'])) // on vérifie que la personne est bien connectée en tant qu'administrateur
	{
		include('template.php');
		include('connexion.php');
		if (isset($_POST['add_pilote_request'])) // si l'utilisateur a envoyé le formulaire
		{
			if (isset($_POST['num_secu']) && $_POST['num_secu'] != '') // on vérifie que le num de sécu n'est pas vide
			{
				if(isset($_POST['num_licence']) && $_POST['num_licence'] != '') // on vérifie que le num de licence n'est pas 
				{	
					$nom = (isset($_POST['nom'])?addslashes(utf8_decode($_POST['nom'])):'');
					$prenom = (isset($_POST['prenom'])?addslashes(utf8_decode($_POST['prenom'])):'');
					$adresse = (isset($_POST['adresse'])?addslashes(utf8_decode($_POST['adresse'])):'');
					$salaire = ((isset($_POST['salaire']) && $_POST['salaire']!='')?$_POST['salaire']:'NULL');
					// on insert une ligne dans la table personnel
					$result = $my_db->query("INSERT INTO personnel (num_secu, nom, prenom, adresse, salaire) VALUES ('".$_POST['num_secu']."', '".$nom."', '".$prenom."', '".$adresse."', ".$salaire.")");
					if ($result != 1) // si cela n'a pas marché, on quitte et on redirige
					{
						$_SESSION['insert_sucess'] = 0;
						header('location: employees.php');
						exit(0);
					}
					// on insert une ligne dans la table pilote
					$result = $my_db->query("INSERT INTO pilote (num_secu, num_licence) VALUES ('".$_POST['num_secu']."', '".$_POST['num_licence']."')");
					if ($result == 1) // si cela a marché, on assigne la variable de session pour l'affichage du popup
					{
						$_SESSION['insert_sucess'] = 1;
						header('location: employees.php');
						exit(0);
					}
					else{ // si cela n'a pas marché, on quitte et on redirige, on affecte la variable de session pour l'affichage du popup
						$_SESSION['insert_sucess'] = 0;
						header('location: employees.php');
						exit(0);
					}
				}
				else{ // sinon on redirige vers le formulaire avec une variable de session pour afficher que la licence n'est pas saisie
					$_SESSION['num_licence_vide'] = 1;
					header('location: add_pilote.php');
					exit(0);
				}
			}
			else{ // sinon on redirige vers le formulaire avec une variable de session pour afficher que le num de sécu est vide
				$_SESSION['num_secu_vide'] = 1;
				header('location: add_pilote.php');
				exit(0);
			}
		}
		else{ // sinon on affiche le formulaire
			$template = new Template('template/');
			$template->set_filenames(array(
				'body' => 'add_pilote.html'
			));
			if (isset($_SESSION['num_secu_vide'])) // affichage du popup sur le num de sécu
			{
				$template->assign_vars(array(
					'num_secu_vide' => '<div class="delete_fail">Veuillez entrer un numéro de sécurité social</div>'
				));
				unset($_SESSION['num_secu_vide']);
			}
			if (isset($_SESSION['num_licence_vide'])) // affichage du popup sur le num de licence
			{
				$template->assign_vars(array(
					'num_licence_vide' => '<div class="delete_fail">Veuillez entrer un numéro de licence</div>'
				));
				unset($_SESSION['num_licence_vide']);
			}
			$template->pparse('body');
		}
	}
	else{ // sinon on redirige vers la page d'accueil
		header('location: home.php');
		exit(0);
	}
?>