<?php
	session_start ();
	if (isset($_SESSION['admin'])) // on vérifie que l'on est bien connecté en tant qu'administrateur
	{
		include('template.php');
		include('connexion.php');
		if (isset($_POST['add_aircraft_request'])) // si l'utilisateur a demandé la modification d'un appareil
		{
			if (isset($_POST['num_immatriculation']) && $_POST['num_immatriculation'] != '') // on vérifie que ne nouveau numéro d'immatriculation est valide
			{
				// on update la table appareil
				$result = $my_db->query("UPDATE appareil SET num_immatriculation='".$_POST['num_immatriculation']."' , type='".(isset($_POST['type'])?$_POST['type']:'')."' WHERE num_immatriculation='".$_POST['prev_num_immatriculation']."' ");
				if ($result == 1) // succès de la requete
				{
					$_SESSION['modify_sucess'] = 1;
					header('location: aircrafts.php');
					exit(0);
				}
				else{ // echec
					$_SESSION['modify_sucess'] = 0;
					header('location: aircrafts.php');
					exit(0);
				}
			}
			else{
				$_SESSION['modify_sucess'] = 0;
				header('location: aircrafts.php');
				exit(0);
			}
		}
		elseif (isset($_GET['num_immatriculation']) &&  $_GET['num_immatriculation'] != '')
		{ // sinon on vérifie que l'on a bien un numéro d'immatriculation dans le GET et on affiche la page
			$template = new Template('template/');
			$template->set_filenames(array( // appel du code html
				'body' => 'modify_aircraft.html'
			));
			
			// recupération des infos sur l'avion a modifier
			$result = $my_db->query("SELECT num_immatriculation, type FROM appareil WHERE num_immatriculation='".$_GET['num_immatriculation']."'");
			$row = $result->fetch_array();
			
			// affichage des champs pré-remplis
			$template->assign_vars(array(
				'num_immatriculation' => $row['num_immatriculation'],
				'type' => $row['type'],
				'prev_num_immatriculation' => $_GET['num_immatriculation']
			));
			$template->pparse('body');
		}
		else{
			header('location: aircrafts.php');
			exit(0);
		}
	}
	else{
		header('location: home.php');
		exit(0);
	}
?>