<?php
	session_start ();
	function calcul_temps($result_heures)
	{ // fonction que prend le resultat d'une requete sql et qui en déduite le nombre d'heures réalisé par un employé
		$somme = '0h00';
		while($row = $result_heures->fetch_array()){
			$h_depart = intval(explode(':', $row['horaire_depart'])[0]);
			$h_arrivee = intval(explode(':', $row['horaire_arrivee'])[0]);
			$h_somme = intval(explode('h', $somme)[0]);
			$m_depart = intval(explode(':', $row['horaire_depart'])[1]);
			$m_arrivee = intval(explode(':', $row['horaire_arrivee'])[1]);
			$m_somme = intval(explode('h', $somme)[1]);
			if ($row['arrivee_lendemain'] == 1){
				$h_arrivee += 24;
			}
			$m_diff = $m_arrivee - $m_depart;
			if ($m_diff < 0){
				$m_diff += 60;
				$h_arrivee += -1;
			}
			$h_diff = $h_arrivee - $h_depart;
			$m_somme_new = $m_diff + $m_somme;
			$h_somme_new = $h_diff + $h_somme;
			if ($m_somme_new > 59)
			{
				$m_somme_new += - 60;
				$h_somme_new += 1;
			}
			if ($m_somme_new < 10){
				$somme = strval($h_somme_new).'h0'.strval($m_somme_new);
			}
			else{
				$somme = strval($h_somme_new).'h'.strval($m_somme_new);
			}
		}
		return $somme;
	}
	if (isset($_SESSION['admin'])) // on vérifie que l'on est boen connecté en tant qu'admin
	{
		include('template.php');
		include('connexion.php');
		if (isset($_GET['action']) && isset($_GET['num_secu'])){
			if ($_GET['action'] == 'delete'){ // si l'on a demandé une suppression
				if (isset($_GET['type']) and $_GET['type'] == 'pilote') // si c'est pour un pilote
				{
					$result1 = $my_db->query("DELETE FROM pilote WHERE num_secu='".$_GET['num_secu']."'");
					if ($result1 != 1)
					{
						$_SESSION['delete_sucess'] = 0;
						header('location: employees.php');
						exit(0);
					}
				}
				elseif (isset($_GET['type']) and $_GET['type'] == 'equipage') // sinon si c'est pour un membre d'équipage
				{
					$result1 = $my_db->query("DELETE FROM equipage WHERE num_secu='".$_GET['num_secu']."'");
					if ($result1 != 1)
					{
						$_SESSION['delete_sucess'] = 0;
						header('location: employees.php');
						exit(0);
					}
				}
				
				// que ce soit un pilote ou un membre d'équipage, on doit supprimer la ligne dans la table personnel
				$result2 = $my_db->query("DELETE FROM personnel WHERE num_secu='".$_GET['num_secu']."'");
				if ($result2 == 1)
				{ // succès de la suppression
					$_SESSION['delete_sucess'] = 1;
					header('location: employees.php');
					exit(0);
				}
				else{ // echec
					$_SESSION['delete_sucess'] = 0;
					header('location: employees.php');
					exit(0);
				}
			}
		}
		else
		{ // affichage de la page
			$template = new Template('template/');
			$template->set_filenames(array(
				'body' => 'employees.html'
			));
			if (isset($_SESSION['delete_sucess'])){ // message après suppression
				if ($_SESSION['delete_sucess'] == 1){ // succès
					$template->assign_vars(array(
						'message_delete' => '<div class="delete_success">Cette personne a bien été supprimée</div>'
					));
					unset($_SESSION['delete_sucess']);
				}
				else{ // echec
					$template->assign_vars(array(
						'message_delete' => '<div class="delete_fail">Cette personne n\'a pas pu être supprimée car elle intervient dans des vols</div>'
					));
					unset($_SESSION['delete_sucess']);
				}
			}
			if (isset($_SESSION['insert_sucess'])){ // message après création
				if ($_SESSION['insert_sucess'] == 1){ // succès
					$template->assign_vars(array(
						'message_insert' => '<div class="delete_success">Cette personne a bien été ajoutée</div>'
					));
					unset($_SESSION['insert_sucess']);
				}
				else{ // echec
					$template->assign_vars(array(
						'message_insert' => '<div class="delete_fail">Cette personne n\'a pas pu être ajoutée</div>'
					));
					unset($_SESSION['insert_sucess']);
				}
			}
			if (isset($_SESSION['modify_sucess'])){ // message après modification
				if ($_SESSION['modify_sucess'] == 1){ // succès
					$template->assign_vars(array(
						'message_modify' => '<div class="delete_success">Cette personne a bien été modifiée</div>'
					));
					unset($_SESSION['modify_sucess']);
				}
				else{ // echec
					$template->assign_vars(array(
						'message_modify' => '<div class="delete_fail">Cette personne n\'a pas pu être modifiée</div>'
					));
					unset($_SESSION['modify_sucess']);
				}
			}
			
			// affichage des pilotes
			$result = $my_db->query("SELECT num_secu, nom, prenom, adresse, salaire, num_licence FROM pilote_complet");
			$color = '#d1c3ef';
			while($row = $result->fetch_array()){
				$result_heures = $my_db->query("SELECT vol.horaire_depart, vol.arrivee_lendemain, vol.horaire_arrivee  
				FROM depart 
				JOIN vol ON vol.num_vol=depart.num_vol 
				WHERE depart.num_pilote1='".$row['num_secu']."' OR depart.num_pilote2='".$row['num_secu']."'");
				if ($color == '#d1c3ef'){
					$color = '#f0ebfa';
				}
				else
				{
					$color = '#d1c3ef';
				}
				$template->assign_block_vars('pilote',array(
					'num_secu' => utf8_encode($row['num_secu']),
					'nom'  => utf8_encode($row['nom']),
					'prenom' => utf8_encode($row['prenom']),
					'adresse'  => utf8_encode($row['adresse']),
					'salaire'  => utf8_encode($row['salaire']),
					'num_licence'  => utf8_encode($row['num_licence']),
					'total_heures' => calcul_temps($result_heures),
					'color' => $color
				));
			
			}
			
			// affichage des membres d'équipage
			$result = $my_db->query("SELECT num_secu, nom, prenom, adresse, salaire, fonction FROM equipage_complet");
			$color = '#d1c3ef';
			while($row = $result->fetch_array()){
				$result_heures = $my_db->query("SELECT vol.horaire_depart, vol.arrivee_lendemain, vol.horaire_arrivee  
				FROM depart 
				JOIN vol ON vol.num_vol=depart.num_vol 
				WHERE depart.num_equipage1='".$row['num_secu']."' OR depart.num_equipage2='".$row['num_secu']."'");
				if ($color == '#d1c3ef'){
					$color = '#f0ebfa';
				}
				else
				{
					$color = '#d1c3ef';
				}
				$template->assign_block_vars('equipage',array(
					'num_secu' => utf8_encode($row['num_secu']),
					'nom'  => utf8_encode($row['nom']),
					'prenom' => utf8_encode($row['prenom']),
					'adresse'  => utf8_encode($row['adresse']),
					'salaire'  => utf8_encode($row['salaire']),
					'fonction'  => utf8_encode($row['fonction']),
					'total_heures' => calcul_temps($result_heures),
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