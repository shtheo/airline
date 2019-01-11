<?php
	session_start ();
	if (isset($_SESSION['admin'])) // on verifie que la personne est bien l'administrateur
	{
		include('template.php');
		include('connexion.php');
		if (isset($_POST['add_crew_request'])) // si l'utilisateur a fait une demande d'ajout de membre d'équipage
		{
			if (isset($_POST['num_secu']) && $_POST['num_secu'] != '') // on vérifie que le num de secu est bien rempli
			{	
				// requete d'insertion du nouveau membre d'équipage dans la table mere personnel
				// le addslashes permet de prendre en compte des chaines avec des guillemets
				// le utf8 encode permet de prendre en compte des chaines avec des caractères accentués
				$nom = (isset($_POST['nom'])?addslashes(utf8_decode($_POST['nom'])):'');
				$prenom = (isset($_POST['prenom'])?addslashes(utf8_decode($_POST['prenom'])):'');
				$adresse = (isset($_POST['adresse'])?addslashes(utf8_decode($_POST['adresse'])):'');
				$salaire = ((isset($_POST['salaire']) && $_POST['salaire']!='')?$_POST['salaire']:'NULL');
				$result = $my_db->query("INSERT INTO personnel (num_secu, nom, prenom, adresse, salaire) VALUES ('".$_POST['num_secu']."', '".$nom."', '".$prenom."', '".$adresse."', ".$salaire.")");
				if ($result != 1) // si requete bien passee
				{
					$_SESSION['insert_sucess'] = 0;
					header('location: employees.php');
					exit(0);
				}
				// requete d'insertion du nouveau membre d'équipage dans la fille equipage
				$result = $my_db->query("INSERT INTO equipage (num_secu, fonction) VALUES ('".$_POST['num_secu']."', '".$_POST['fonction']."')");
				if ($result == 1) // requete s'est bien passee
				{
					$_SESSION['insert_sucess'] = 1;
					header('location: employees.php');
					exit(0);
				}
				else{ // requete s'est mal passee
					$_SESSION['insert_sucess'] = 0;
					header('location: employees.php');
					exit(0);
				}
			}
			else{
				$_SESSION['num_secu_vide'] = 1;
				header('location: add_pilote.php');
				exit(0);
			}
		}
		else{
			$template = new Template('template/');
			$template->set_filenames(array(
				'body' => 'add_crew.html'
			));
			if (isset($_SESSION['num_secu_vide'])) // si le coup d'avant, l'utilisateur avait envoyé une requete avec un num de secu invalide
			{
				// on affiche un message d'erreur
				$template->assign_vars(array(
					'num_secu_vide' => '<div class="delete_fail">Veuillez entrer un numéro de sécurité social</div>'
				));
				unset($_SESSION['num_secu_vide']);
			}
			$template->pparse('body');
		}
	}
	else{
		header('location: home.php');
		exit(0);
	}
?>