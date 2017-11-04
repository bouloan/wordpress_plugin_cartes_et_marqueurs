
<?php

class DataRegister{//enregistre les données entrées dans le formulaire du back-office 

	public function __construct(){
		
		$this->modif_db(); 
	}
	
	function strToNoAccent($var) {
		$var = str_replace(
			array(
				'à', 'â', 'ä', 'á', 'ã', 'å',
				'î', 'ï', 'ì', 'í', 
				'ô', 'ö', 'ò', 'ó', 'õ', 'ø', 
				'ù', 'û', 'ü', 'ú', 
				'é', 'è', 'ê', 'ë', 
				'ç', 'ÿ', 'ñ', 
			),
			array(
				'a', 'a', 'a', 'a', 'a', 'a', 
				'i', 'i', 'i', 'i', 
				'o', 'o', 'o', 'o', 'o', 'o', 
				'u', 'u', 'u', 'u', 
				'e', 'e', 'e', 'e', 
				'c', 'y', 'n', 
			),$var);

		return $var;
		}
		
	function strToDash($var) {
		$var = str_replace(
			array(
				' ', '\'','\\', '-',
			),
			array(
				'_', '_', '', '_',
			),$var);

		return $var;
		} 

	public function modif_db(){//ajout des données dans les tables prefix.'ville' et prefix.'town'

		//création des variables
		global $wpdb;
		$townTable = $wpdb->prefix.'ville';
        $restoTable = $wpdb->prefix.'resto';
		$name = ucwords(mb_strtolower($_POST['name']));	
		$name = implode("'", array_map('ucfirst', explode("'", $name))); /*ajoute une majuscule à un mot placé dernière un apostrophe*/
		$name = implode('-', array_map('ucfirst', explode('-', $name))); /*ajoute une majuscule à un mot placé dernière un trait d'union*/
		$nameCode = $this->strToDash($this->strToNoAccent(mb_strtolower($name)));
		$address =  $_POST['address'];
		$addressBis =  $_POST['addressBis'];
		$townLatitude = $_POST['townLatitude'];
		$townLongitude = $_POST['townLongitude'];
		$town =  mb_strtoupper($this->strToNoAccent(mb_strtolower($_POST['town'])));
		$townCode = $this->strToDash($this->strToNoAccent(mb_strtolower($town)));
		$postalCode = $_POST['postalCode'];
		$telephone = $_POST['telephone'];
		$latitude = $_POST['latitude'];
		$longitude = $_POST['longitude'];
		$zoom = $_POST['zoom'];
		$image = $_POST['image'];
		$restaurantState = $_POST['restaurantState'];
		$checkTown = $wpdb->get_var("SELECT id FROM $townTable WHERE town_code = '$townCode' AND postal_code = '$postalCode'");
		$checkResto = $wpdb->get_var("SELECT id FROM $restoTable WHERE name_code = '$nameCode'");

		// si le formulaire d'enregistrement est rempli
		if(!empty($_POST['record']) && empty($_POST['erase'])){
			//si la ville du resto est enregistrée pour la première fois 
			if($checkTown == NULL && !empty($townCode) && !empty($postalCode) && !empty($townLatitude) && !empty($townLongitude)){
				$wpdb->insert(
					$townTable,
					array(
						'town' => $town,
						'town_code' => $townCode,
						'postal_code' => $postalCode,
						'town_latitude' => $townLatitude,
						'town_longitude' => $townLongitude
						));

				$checkTown = $wpdb->get_var("SELECT id FROM $townTable WHERE town_code = '$townCode' AND postal_code = '$postalCode'");
			}

			//si le nom du restaurant est enregistré pour la première fois 
			if ($checkResto == NULL && !empty($nameCode)){
				$wpdb->insert(
				$restoTable, 
				array(
					'town_id' => $checkTown,
					'name' => $name,
					'name_code' => $nameCode,
					'address' => $address,
					'address_bis' => $addressBis,
					'telephone' => $telephone,
					'latitude' => $latitude,
					'longitude' => $longitude,
					'image' =>$image,
					'restaurant_state' => $restaurantState
					));
			
			//si le statut du restaurant passe du statut pas ouvert ("not open) à ouvert ("open") 	
			} elseif ($checkResto != NULL && $restaurantState == "open"){ 
				$wpdb->update(
				$restoTable, 
				array(
					'address' => $address,
					'address_bis' => $addressBis,
					'telephone' => $telephone,
					'latitude' => $latitude,
					'longitude' => $longitude,
					'image' =>$image,
					'restaurant_state' => $restaurantState
					), 
				array(
					'name_code' => $nameCode,
					));
			}

		//si le formulaire de suppression est soumis
		} elseif(!empty($_POST['erase']) && empty($_POST['record'])){
			$wpdb->delete(
				$restoTable, 
				array(
					'id' =>$checkResto
					));
			$wpdb->delete(
				$townTable, 
				array(
					'id' =>$checkTown
					));
		}
	}
}