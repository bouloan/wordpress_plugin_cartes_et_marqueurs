<?php
/*
Plugin Name: carte et marqueurs
Description: un plugin pour intégrer des cartes Google avec des marqueurs indiquant l'emplacement des restaurants
Version: 0.1
*/

// lien vers le fichier contenant le code CSS de la partie back-office du plugin
wp_enqueue_style('wp_style', '/wp-content/plugins/carte/wp_style.css');

class PluginManagement{
	public function __construct(){
		include_once plugin_dir_path(__FILE__).'/Database.php';
		new Database();
		include_once plugin_dir_path(__FILE__).'/Map.php';
		new Map();
		include_once plugin_dir_path(__FILE__).'/DataRegister.php'; 
		$dataRegister = new DataRegister();
		register_activation_hook(__FILE__, array('Database', 'install'));
		register_uninstall_hook(__FILE__, array('Database', 'uninstall'));
		add_action('admin_menu', array($this, 'adminMenu'));
	}
	
	//menu du plugin apparaissant sur le back-office
	public function adminMenu(){ 
		add_menu_page(
			'plugin carte', 
			'carte et marqueurs', 
			'manage_options', 
			'carte', 
			array($this, 'menu_html'));
		add_submenu_page(
			'carte',
			'carte et marqueurs', 
			'formulaire',
			'manage_options',
			'carte',
			array($this, 'menu_html'));
		add_submenu_page(
			'carte',
			'carte et marqueurs', 
			'liste shortcodes',
			'manage_options',
			'shortcodeList',
			array($this, 'submenu_html'));	
	}
	
	//formulaire permettant d'entrée les nouveaux restos
	public function menu_html(){ 
		echo'<h1>Placement de marqueurs sur une carte Google</h1>';

		if (!empty($_POST)){
			echo 'Les informations ont été enregistrées';
		} else{
		?>
			<h2>Enregistrement d'un nouveau restaurant ou modification de statut d'un restaurant déjà enregistré</h2>
			<form enctype="multipart/form-data" method="post" action="">
				<label class="pluginLabel">Nom*</label>
				<input type="text" size="60" name="name" autofocus="autofocus" required="required"/><br/>
				<label class="pluginLabel">Adresse</label>
				<input type="text" size="60" name="address"/><br/>
				<label class="pluginLabel">Adresse bis</label>
				<input type="text" size="60" name="addressBis"/><br/>
				<label class="pluginLabel">Code postal*</label>
				<input type="text" size="60" pattern="[0-9]{5}" name="postalCode" required="required"/><br/>
				<label class="pluginLabel">Ville*</label>
				<input type="text" size="60" name="town" required="required"/><br/>
				<label class="pluginLabel">N° téléphone (ex: 01 01 01 01 01)</label>
				<input type="text" size="60" pattern="^(\d\d\s){4}(\d\d)$" name="telephone"/><br/>
				<label class="pluginLabel">Latitude Ville*</label>
				<input type="text" name="townLatitude" required="required"/><br/>
				<label class="pluginLabel">Longitude Ville*</label>
				<input type="text" name="townLongitude" required="required"/><br/>
				<label class="pluginLabel">Latitude Restaurant</label>
				<input type="text" name="latitude"/><br/>
				<label class="pluginLabel">Longitude Restaurant</label>
				<input type="text" name="longitude"/><br/>
				<label class="pluginLabel">URL de l'image</label>
				<input type="text" name="image" size="60"/><br/>
				<label class="pluginLabel">Restaurant</label>
				<label>ouvert</label>
				<input class="pluginLabelRadio" type="radio" name="restaurantState" value="open"/>
				<label>pas encore ouvert</label>
				<input class="pluginLabelRadio" type="radio" name="restaurantState" value="notOpen" checked="checked" /><br/><br/>
				<input type="submit" name="record" value="Enregistrement"/><br/>
			</form>
			<h2>Suppression d'un restaurant existant (si nécessaire, se reporter au sous-menu "<i>liste shortcodes</i>"" pour les informations à renseigner dans le formulaire ci-dessous)</h2>
			<form enctype="multipart/form-data" method="post" action="">
				<label class="pluginLabel">Nom*</label>
				<input type="text" size="60" name="name"  autofocus="autofocus" required="required"/><br/>
				<label class="pluginLabel">Code postal</label>
				<input type="text" size="60" pattern="[0-9]{5}" name="postalCode"/><br/>
				<label class="pluginLabel">Ville</label>
				<input type="text" size="60" name="town"/><br/><br/>
				<input type="submit" name="erase" value="Suppression"/><br/>
				
			</form>

		<?php
		}
	}
	
	//tableau affichant les shortcodes à placer dans le wordpress
	public function submenu_html(){
		global $wpdb;
		$townTable = $wpdb->prefix.'ville';
        $restoTable = $wpdb->prefix.'resto';
		$nameResult = $wpdb->get_results("SELECT town, name, name_code, town_code, postal_code, restaurant_state FROM $restoTable, $townTable WHERE $townTable.id = $restoTable.town_id ORDER BY town" );
	
		echo '<h1>Liste des shortcodes pour afficher les cartes</h1>';
		echo '<p>Les shortcodes ci-dessous permettent d\'afficher des cartes aux échelles de la ville et du restaurant. Pour afficher une carte à <b>l\'échelle nationale</b>, le shortcode à renseigner est [affichageCarte]</p>';
		echo '<table id="shortcodeTable">
				<tr>
					<th>Ville</th>
					<th>Code Postal</th>
					<th>Nom Restaurant</th>
					<th>Shortcode carte zoom Ville</th>
					<th>Shortcode carte zoom Restaurant</th>
					<th>Statut Restaurant</th>
				</tr>
				<tr>';
		for ($i=0; $i<count($nameResult); $i++){
			echo '<tr>
					<td>'.$nameResult[$i]->town.'</td>
					<td>'.$nameResult[$i]->postal_code.'</td>
					<td>'.$nameResult[$i]->name.'</td>
					<td>[affichageCarte ville = "'.$nameResult[$i]->town_code.'"]</td>
					<td>'; echo ($nameResult[$i]->restaurant_state == 'notOpen'?('non concerné'):('[affichageCarte resto = "'.$nameResult[$i]->name_code.'"]'));
			echo '</td>
					<td>'.$nameResult[$i]->restaurant_state.'</td>
				</tr>';
		}
		echo  '</table>';
	}
}

new PluginManagement();

