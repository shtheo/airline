<?php
	session_start ();
	if (isset($_SESSION['num_passager'])) // vérification que l'on est bien connecté en tant que passager 
	{
		include('template.php');
		include('connexion.php');
		if (isset($_GET['num_billet']) && $_GET['num_billet']!= ''){
			$result = $my_db->query("SELECT * FROM billet WHERE num_billet=".$_GET['num_billet']." AND num_passager=".$_SESSION['num_passager']);
			if ($result->num_rows > 0){ // si le billet que l'on veut supprimer existe bien et qu'il appratient bien à la bonne personne
				$result = $my_db->query("DELETE FROM billet WHERE num_billet=".$_GET['num_billet']." AND num_passager=".$_SESSION['num_passager']);
				if ($result == 1){ // si la suppression a bien marché
					$_SESSION['delete_success'] = 1;
					header('location: sum_up_departures.php');
					exit(0);
				}
				else{
					$_SESSION['delete_success'] = 0;
					header('location: sum_up_departures.php');
					exit(0);
				}
			}
			else{ // si le billet que l'on veut supprimer n'existe pas
				$_SESSION['delete_success'] = 0;
				header('location: sum_up_departures.php');
				exit(0);
			}
		}
		else{ // sinon on affiche la page
			$template = new Template('template/');
			
			// affichage de la page html
			$template->set_filenames(array(
				'body' => 'sum_up_departures.html'
			));
			
			// affichage du titre de la page
			$result = $my_db->query("SELECT * FROM passager WHERE num_passager=".$_SESSION['num_passager']);
			$row = $result->fetch_array();
			$template->assign_vars(array(
				'nom' => utf8_encode($row['nom']),
				'prenom' => utf8_encode($row['prenom'])
			));
			
			// affichage d'un message suite à la suppression d'un billet
			if (isset($_SESSION['delete_success'])){ 
				if ($_SESSION['delete_success'] == 1){ // succès
					$template->assign_vars(array(
						'message' => '<div class="delete_success">Ce billet a bien été supprimé</div>'
					));
					unset($_SESSION['delete_success']);
				}
				else{ // echec
					$template->assign_vars(array(
						'message' => '<div class="delete_fail">Cet billet n\'a pas pu être supprimé</div>'
					));
					unset($_SESSION['delete_success']);
				}
			}
			
			// recupération des billets vols et départs dans lequels le passager s'est engagé
			$result = $my_db->query("SELECT vol_complet.num_vol AS num_vol, vol_complet.aeroport_depart AS aeroport_depart, vol_complet.ville_depart AS ville_depart,
				vol_complet.aeroport_arrivee AS aeroport_arrivee, vol_complet.ville_arrivee AS ville_arrivee, 
				vol_complet.horaire_depart AS horaire_depart, vol_complet.horaire_arrivee AS horaire_arrivee,
				vol_complet.arrivee_lendemain AS arrivee_lendemain, depart_complet.date_depart AS date_depart, depart_complet.prix as prix,
				billet.num_billet, billet.date_emission
				FROM billet 
				JOIN depart_complet ON (depart_complet.num_vol = billet.num_vol AND depart_complet.date_depart = billet.date_depart)
				JOIN vol_complet ON depart_complet.num_vol = vol_complet.num_vol 
				JOIN depart ON (depart_complet.num_vol = depart.num_vol AND depart_complet.date_depart = depart.date_depart)
				WHERE (billet.num_passager=".$_SESSION['num_passager'].") 
				ORDER BY depart_complet.date_depart, vol_complet.horaire_depart");
				$color = '#d1c3ef';
				
				// affichage de ces départs / billets
				if ($result->num_rows > 0)
				{
					while($row = $result->fetch_array()){
						if ($color == '#d1c3ef'){
							$color = '#f0ebfa';
						}
						else
						{
							$color = '#d1c3ef';
						}
						$template->assign_block_vars('passager',array(
							'date_depart' => $row['date_depart'],
							'aeroport_depart'  => utf8_encode($row['ville_depart'].' <br/><span style="font-size:11px">'.$row['aeroport_depart'].'</span>'),
							'aeroport_arrivee'  => utf8_encode($row['ville_arrivee'].' <br/><span style="font-size:11px">'.$row['aeroport_arrivee'].'</span>'),
							'horaire_depart'  => $row['horaire_depart'],
							'horaire_arrivee'  => $row['horaire_arrivee'].($row['arrivee_lendemain'] == 1?' (J+1)':''),
							'prix' => $row['prix'],
							'num_billet' => $row['num_billet'],
							'date_emission' => $row['date_emission'],
							'color' => $color
						));
					}	
				}
				else{ // si pas encore de billets, affichage d'une phrase plutôt qu'un tableau vide
					$template->assign_vars(array(
					'message' => '<h3>Vous n\'avez pas encore reservé de vol... <br/>
									Allez dans la rubrique reservez un vol pour acheter votre premier billet !</h3>',
					'hidden' => 'hidden="hidden"'
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