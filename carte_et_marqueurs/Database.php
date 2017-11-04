<?php

class Database{

// Création de deux tables lors de l'installation du plugin
	public static function install(){
		global $wpdb;
		$townTable = $wpdb->prefix.'ville'; //table contenant les informations sur la ville où est localisé le resto
		$restoTable = $wpdb->prefix.'resto'; //table contenant les informations sur le resto
		
		$wpdb->query(
			"CREATE TABLE IF NOT EXISTS $townTable (
			
			id 				INT AUTO_INCREMENT PRIMARY KEY, 
			town 			VARCHAR(255) NOT NULL, 
			town_code		VARCHAR(255) NOT NULL,
			postal_code 	VARCHAR(5) NOT NULL, 
			town_latitude 	VARCHAR(10) NOT NULL, 
			town_longitude 	VARCHAR(10) NOT NULL
			
			)
			ENGINE = InnoDB
			DEFAULT CHARACTER SET = utf8
			COLLATE = utf8_general_ci"
			);	


		$wpdb->query(
			"CREATE TABLE IF NOT EXISTS $restoTable (

				id 					INT AUTO_INCREMENT PRIMARY KEY, 
				town_id 			INT  NOT NULL,
				name 				VARCHAR(255) NOT NULL, 
				name_code 			VARCHAR(255) NOT NULL,
				address 			VARCHAR(255), 
				address_bis			VARCHAR(255),
				telephone 			VARCHAR(14), 
				latitude 			VARCHAR(10), 
				longitude 			VARCHAR(10), 
				image 				VARCHAR(255),
				restaurant_state 	VARCHAR(7) NOT NULL,
				CONSTRAINT fk_ville_id
					FOREIGN KEY (town_id)
					REFERENCES $townTable(id)
			) 
			ENGINE = InnoDB
			DEFAULT CHARACTER SET = utf8
			COLLATE = utf8_general_ci"
			);	


		$wpdb->query("ALTER TABLE $restoTable
			ADD CONSTRAINT fk_ville_id
			FOREIGN KEY (town_id)
			REFERENCES $townTable(id)
			");
	}

// Supression de deux tables lors de la désinstallation du plugin
	public static function uninstall(){
		global $wpdb;
		$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}resto;");
	
		$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}ville;");
	}
}
?>