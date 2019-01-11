<?php
	session_start ();
	function dates_ok($date1, $date2) // vérifie que date < date2
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
	if (isset($_SESSION['admin']))// on vérifie que l'on est bien connecté en tant qu'administrateur
	{
		include('template.php');
		include('connexion.php');
		if (isset($_POST['modify_fly_request'])) // si la modification de vol a été demandée
		{	
			$num_vol = $_POST['num_vol'];
			if (dates_ok($_POST['date_debut_periode'], $_POST['date_fin_periode'])){ // vérification des dates de départ et fin de période
				if ($_POST['aeroport_depart'] != $_POST['aeroport_arrivee']){ // vérification que les aéroports de départ et d'arrivée sont différents
					if ($_POST['arrivee_lendemain'] == '1' || $_POST['horaire_depart'] < $_POST['horaire_arrivee']){ // vérification que l'horaire de départ esta ntérieur à l'horaire d'arrivée
						$code_aeroport_depart = $_POST['aeroport_depart'];
						$code_aeroport_arrivee = $_POST['aeroport_arrivee'];
						$date_debut_periode = $_POST['date_debut_periode'];
						$date_fin_periode = $_POST['date_fin_periode'];
						$horaire_depart = $_POST['horaire_depart']; 
						$horaire_arrivee = $_POST['horaire_arrivee']; 
						$arrivee_lendemain = $_POST['arrivee_lendemain'];
						$num_appareil = $_POST['appareil'];
						// execution de la requete de mise à jour
						$result = $my_db->query("UPDATE vol SET code_aeroport_depart='".$code_aeroport_depart."', 
						code_aeroport_arrivee='".$code_aeroport_arrivee."', date_debut_periode='".$date_debut_periode."', 
						date_fin_periode='".$date_fin_periode."', horaire_depart='".$horaire_depart."', horaire_arrivee='".$horaire_arrivee."', 
						arrivee_lendemain=".$arrivee_lendemain.", num_appareil='".$num_appareil."' WHERE num_vol='".$num_vol."'");
						if ($result == 1)
						{ // succes
							$_SESSION['modify_sucess'] = 1;
							header('location: flies.php');
							exit(0);
						}
						else{ // echec
							$_SESSION['modify_sucess'] = 0;
							header('location: flies.php');
							exit(0);
						}
					}
					else{ 
						$_SESSION['horaires_pourris'] = 1;
						header('location: modify_fly.php?num_vol='.$num_vol);
						exit(0);
					}
				}
				else{
					$_SESSION['aeroports_pourris'] = 1;
					header('location: modify_fly.php?num_vol='.$num_vol);
					exit(0);
				}
			}
			else{
				$_SESSION['dates_pourries'] = 1;
				header('location: modify_fly.php?num_vol='.$num_vol);
				exit(0);
			}
		}
		elseif (isset($_GET['num_vol']) &&  $_GET['num_vol'] != '')
		{ // sinon on verifie qu'on a bien un numéro de vol avec la requête GET et on affiche la page
			$template = new Template('template/');
			$template->set_filenames(array( // recuperation du code html
				'body' => 'modify_fly.html'
			));
			
			// affichage du message d'erreur sur les horaires
			if (isset($_SESSION['horaires_pourris'])){ 
				$template->assign_vars(array(
					'message' => '<div class="delete_fail">Vous avez choisi un horaire d\'arrivée antérieur à l\'horaire de départ</div>'
				));
				unset($_SESSION['horaires_pourris']);
			}
			
			// message d'erreur sur les dates
			if (isset($_SESSION['dates_pourries'])){
				$template->assign_vars(array(
					'message' => '<div class="delete_fail">Veuillez remplir les dates. Faites en sorte que la date de début soit plus petite que la date de fin</div>'
				));
				unset($_SESSION['dates_pourries']);
			}
			
			// message d'erreur sur les aéroports
			if (isset($_SESSION['aeroports_pourris'])){
				$template->assign_vars(array(
					'message' => '<div class="delete_fail">Aéroports de départ et d\'arrivée doivent être différents...</div>'
				));
				unset($_SESSION['aeroports_pourris']);
			}
			
			// récupération et affichage des champs pré-remplis
			$result = $my_db->query("SELECT * FROM vol WHERE num_vol='".$_GET['num_vol']."'");
			$row = $result->fetch_array();
			$template->assign_vars(array(
				'num_vol' => $row['num_vol'],
				'date_debut_periode' => $row['date_debut_periode'],
				'date_fin_periode' => $row['date_fin_periode'],
				'horaire_depart' => $row['horaire_depart'],
				'horaire_arrivee' => $row['horaire_arrivee'],
				'selected_oui' => ($row['arrivee_lendemain'] == '1'?'Selected="Selected"':''),
				'selected_non' => ($row['arrivee_lendemain'] == '0'?'Selected="Selected"':''),
			));
			
			// affichage de la liste des aéroports de départ
			$result2 = $my_db->query("SELECT * FROM aeroport");
			while($row2 = $result2->fetch_array()){
				$template->assign_block_vars('aeroport_depart', array(
					'code' => utf8_encode($row2['code']),
					'name' => utf8_encode($row2['ville'].' - '.$row2['nom']),
					'selected' => $row['code_aeroport_depart'] == $row2['code'] ? 'selected="selected"' : ''
				));
			}
			
			// affichage de la liste des aéroports d'arrivée
			$result3 = $my_db->query("SELECT * FROM aeroport");
			while($row3 = $result3->fetch_array()){
				$template->assign_block_vars('aeroport_arrivee', array(
					'code' => utf8_encode($row3['code']),
					'name' => utf8_encode($row3['ville'].' - '.$row3['nom']),
					'selected' => $row['code_aeroport_arrivee'] == $row3['code'] ? 'selected="selected"' : ''
				));
			}
			
			// affichage de la liste des appareils
			$result4 = $my_db->query("SELECT * FROM appareil");
			while($row4 = $result4->fetch_array()){
				$template->assign_block_vars('appareil', array(
					'num_immatriculation' => utf8_encode($row4['num_immatriculation']),
					'appareil' => utf8_encode($row4['num_immatriculation'].' - '.$row4['type']),
					'selected' => $row['num_appareil'] == $row4['num_immatriculation'] ? 'selected="selected"' : ''
				));
			}
			$template->pparse('body');
		}
		else{
			header('location: flies.php');
			exit(0);
		}
	}
	else{
		header('location: home.php');
		exit(0);
	}
?>