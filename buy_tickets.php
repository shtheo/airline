<?php
	session_start ();
	if (isset($_SESSION['num_passager'])) // on vérifie qu'on est connecté en tant que passager
	{
		include('template.php');
		include('connexion.php');
		if (isset($_GET['num_vol']) && $_GET['date_depart']) // si le passager a cliqué sur acheter
		{
			// on vérifie qu'il reste bien des vols
			$result = $my_db->query("SELECT nbr_places_libres FROM depart WHERE num_vol='".$_GET['num_vol']."' AND date_depart='".$_GET['date_depart']."'");
			$row = $result->fetch_array();
			if ($row['nbr_places_libres'] > 0){  // s'il reste des places, on peut créer le billet
				$result = $my_db->query("INSERT INTO billet (date_emission, num_passager, num_vol, date_depart)
				VALUES ('".date('Y-m-d')."', ".$_SESSION['num_passager'].", ".$_GET['num_vol'].", '".$_GET['date_depart']."')");
				$_SESSION['insert_sucess'] = 1; // pour afficher message de succès
				header('location: buy_tickets.php');
				exit(0);
			}
			else { 
				$_SESSION['insert_sucess'] = 0; // pour afficher message d'echec
				header('location: buy_tickets.php');
				exit(0);
			}
			
		}
		else{ // sinon on affiche la page
			$template = new Template('template/');
			$template->set_filenames(array(
				'body' => 'buy_tickets.html'
			));
			
			// affichage du titre
			$result = $my_db->query("SELECT * FROM passager WHERE num_passager=".$_SESSION['num_passager']);
			$row = $result->fetch_array();
			$template->assign_vars(array(
				'nom' => utf8_encode($row['nom']),
				'prenom' => utf8_encode($row['prenom'])
			));
			if (isset($_SESSION['insert_sucess'])){ // messages suite à la demande d'achat
				if ($_SESSION['insert_sucess'] == 0){ // cas d'echec d'achat
					$template->assign_vars(array(
						'message' => '<div class="delete_fail">Trop tard, tous les billets de ce vol ont déjà été reservés...</div>'
					));
					unset($_SESSION['insert_sucess']);
				}
				else{ // cas du succès
					$template->assign_vars(array(
						'message' => '<div class="delete_success">Ce billet a bien été acheté</div>'
					));
					unset($_SESSION['insert_sucess']);
				}
			}
			
			// affichage des options de tri pour aéroport de départ
			$result = $my_db->query("SELECT * FROM aeroport");
			while($row = $result->fetch_array()){
				$template->assign_block_vars('aeroport_depart',array(
					'code' => utf8_encode($row['code']),
					'name' => utf8_encode($row['ville'].' - '.$row['nom']),
					'select' => ((isset($_POST['aeroport_depart']) && $_POST['aeroport_depart'] == $row['code'])?'selected="selected"':'') // après une recherche on garde selectionné l'option dans le select
				));
			}
			
			// affichage des options de tri pour aéroport d'arrivée
			$result = $my_db->query("SELECT * FROM aeroport");
			while($row = $result->fetch_array()){
				$template->assign_block_vars('aeroport_arrivee',array(
					'code' => utf8_encode($row['code']),
					'name' => utf8_encode($row['ville'].' - '.$row['nom']),
					'select' => ((isset($_POST['aeroport_arrivee']) && $_POST['aeroport_arrivee'] == $row['code'])?'selected="selected"':'') // après une recherche on garde selectionné l'option dans le select
				));
			}
			
			// requete qui permet d'afficher les départs
			$requete = "SELECT * FROM depart_complet JOIN vol_complet ON vol_complet.num_vol = depart_complet.num_vol JOIN vol ON vol_complet.num_vol=vol.num_vol";
			if (isset($_POST['search_departure'])){ // si le passager a cliqué sur chercher
				if ($_POST['aeroport_depart'] != 'all'){ // si on selectionné un aéroport de départ
					$requete .= " WHERE vol.code_aeroport_depart ='".$_POST['aeroport_depart']."'";
					if ($_POST['aeroport_arrivee'] != 'all'){ // si on a aussi selectionné un aéroport d'arrivée
						$requete .= " AND vol.code_aeroport_arrivee ='".$_POST['aeroport_arrivee']."'";
					}
				}
				elseif ($_POST['aeroport_arrivee'] != 'all'){ // si on a selectionné uniquement un aéroport d'arrivée
					$requete .= " WHERE vol.code_aeroport_arrivee ='".$_POST['aeroport_arrivee']."'";
				}
			}
			
			// affichage des départs
			$result = $my_db->query($requete);
			$color = '#d1c3ef';
			while($row = $result->fetch_array()){
				
				if ($color == '#d1c3ef'){
					$color = '#f0ebfa';
				}
				else
				{
					$color = '#d1c3ef';
				}
				$template->assign_block_vars('depart',array(
					'date_depart' => $row['date_depart'],
					'num_vol'  => $row['num_vol'],
					'nbr_places_libres'  => ($row['nbr_places_libres']==0?'COMPLET':$row['nbr_places_libres']),
					'prix' => $row['prix'].'€',
					'aeroport_depart'  => utf8_encode($row['ville_depart'].' <br/><span style="font-size:11px">'.$row['aeroport_depart'].'</span>'),
					'aeroport_arrivee'  => utf8_encode($row['ville_arrivee'].' <br/><span style="font-size:11px">'.$row['aeroport_arrivee'].'</span>'),
					'horaire_depart'  => $row['horaire_depart'],
					'horaire_arrivee'  => $row['horaire_arrivee'].($row['arrivee_lendemain'] == 1?' (J+1)':''),
					'panier' => ($row['nbr_places_libres']==0?'':'<a href="buy_tickets.php?num_vol='.$row['num_vol'].'&date_depart='.$row['date_depart'].'"><img width="30px" height="30px" src="Pictures/panier.png"></a>'),
					'color' => $color
				));
			
			}
			$template->pparse('body');
		}
	}
	else{
		header('location: home.php');
		exit(0);
	}
?>