<?php
	session_start ();
	// cette fonction indique si la date1 est inferieure à la date2
	function dates_ok($date1, $date2)
	{
		if ($date1 == '' or $date2 == ''){
			return False;
		}
		else{
			$date1_liste = explode('-', $date1);
			$date2_liste = explode('-', $date2);
			if (intval($date1_liste[0]) < intval($date2_liste[0])){
				return True;
			}
			elseif(intval($date1_liste[0]) == intval($date2_liste[0]) && intval($date1_liste[1]) < intval($date2_liste[1])){
				return True;
			}
			elseif(intval($date1_liste[0]) == intval($date2_liste[0]) && intval($date1_liste[1]) == intval($date2_liste[1]) && intval($date1_liste[2]) <= intval($date2_liste[2])){
				return True;
			}
			else{
				return False;
			}
		}
			
	}
	if (isset($_SESSION['admin'])) // on vérifie que l'on est bien connecté en tant qu'admin
	{
		include('template.php');
		include('connexion.php');
		if (isset($_POST['add_fly_request'])) // si l'utilisateur a envoyé le formulaire
		{
			if (dates_ok($_POST['date_debut_periode'], $_POST['date_fin_periode'])){ // si la date de début est bien plus petite que la date de fin
				if ($_POST['aeroport_depart'] != $_POST['aeroport_arrivee']){ // si l'aéroport de départ différent de l'aéroport d'arrivée
					if ($_POST['arrivee_lendemain'] == '1' || $_POST['horaire_depart'] < $_POST['horaire_arrivee']){ // heure de départ est antérieure à date arrivée
						$code_aeroport_depart = $_POST['aeroport_depart'];
						$code_aeroport_arrivee = $_POST['aeroport_arrivee'];
						$date_debut_periode = $_POST['date_debut_periode'];
						$date_fin_periode = $_POST['date_fin_periode'];
						$horaire_depart = $_POST['horaire_depart'].':00'; 
						$horaire_arrivee = $_POST['horaire_arrivee'].':00'; 
						$arrivee_lendemain = $_POST['arrivee_lendemain'];
						$num_appareil = $_POST['appareil'];
						// on insert dans la table vol
						$result = $my_db->query("INSERT INTO vol (code_aeroport_depart, code_aeroport_arrivee, date_debut_periode, date_fin_periode, horaire_depart, arrivee_lendemain, horaire_arrivee, num_appareil) 
						VALUES ('".$code_aeroport_depart."', '".$code_aeroport_arrivee."', '".$date_debut_periode."', '".$date_fin_periode."', '".$horaire_depart."', ".$arrivee_lendemain.", '".$horaire_arrivee."', '".$num_appareil."')");
						if ($result == 1) // si succes de l'insertion
						{
							$_SESSION['insert_sucess'] = 1;
							header('location: flies.php');
							exit(0);
						}
						else{ // sinon échec
							$_SESSION['insert_sucess'] = 0;
							header('location: flies.php');
							exit(0);
						}
					}
					else{ // sinon en redirige en assignant une variable de session pour afficher un popup
						$_SESSION['horaires_pourris'] = 1;
						header('location: add_fly.php');
						exit(0);
					}
				}
				else{// sinon en redirige en assignant une variable de session pour afficher un popup
					$_SESSION['aeroports_pourris'] = 1;
					header('location: add_fly.php');
					exit(0);
				}
			}
			else{// sinon en redirige en assignant une variable de session pour afficher un popup
				$_SESSION['dates_pourries'] = 1;
				header('location: add_fly.php');
				exit(0);
			}
		}
		else{ // sinon on affiche le formulaire
			$template = new Template('template/');
			$template->set_filenames(array(
				'body' => 'add_fly.html'
			));
			if (isset($_SESSION['horaires_pourris'])){ // affichage cas où horaires ont mal été rentrés
				$template->assign_vars(array(
					'message' => '<div class="delete_fail">Vous avez choisi un horaire d\'arrivée antérieur à l\'horaire de départ</div>'
				));
				unset($_SESSION['horaires_pourris']);
			}
			if (isset($_SESSION['dates_pourries'])){ // affichage de cas où la date de départ a mal été rentrée
				$template->assign_vars(array(
					'message' => '<div class="delete_fail">Veuillez remplir les dates. Faites en sorte que la date de début soit plus petite que la date de fin</div>'
				));
				unset($_SESSION['dates_pourries']);
			}
			if (isset($_SESSION['aeroports_pourris'])){ // affichage cas où les aéroports ont mal été choisis
				$template->assign_vars(array(
					'message' => '<div class="delete_fail">Aéroports de départ et d\'arrivée doivent être différents...</div>'
				));
				unset($_SESSION['aeroports_pourris']);
			}
			// affichage liste aéroports départ
			$result = $my_db->query("SELECT * FROM aeroport");
			while($row = $result->fetch_array()){
				$template->assign_block_vars('aeroport_depart',array(
					'code' => utf8_encode($row['code']),
					'name' => utf8_encode($row['ville'].' - '.$row['nom'])
				));
			}
			// affichage liste aéroports arrivée
			$result = $my_db->query("SELECT * FROM aeroport");
			while($row = $result->fetch_array()){
				$template->assign_block_vars('aeroport_arrivee',array(
					'code' => utf8_encode($row['code']),
					'name' => utf8_encode($row['ville'].' - '.$row['nom'])
				));
			}
			// affichage liste appareils
			$result = $my_db->query("SELECT * FROM appareil");
			while($row = $result->fetch_array()){
				$template->assign_block_vars('appareil',array(
					'num_immatriculation' => utf8_encode($row['num_immatriculation']),
					'appareil' => utf8_encode($row['num_immatriculation'].' - '.$row['type'])
				));
			}
			$template->pparse('body');
		}
	}
	else{ // sinon on redirige vers la page d'accueil
		header('location: home.php');
		exit(0);
	}
?>