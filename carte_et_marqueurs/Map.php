<?php

class Map{
  //fonction permettant d'ajout un shortcode dont le nom est 'affichageCarte' à Wordpress. 
  public function __construct(){
    add_shortcode('affichageCarte', array($this, 'restaurantMap'));
  }

  
  public function restaurantMap($att){
    

    extract(shortcode_atts(array( //fonction permettant de définir deux paramètres au shortcode: resto et ville. elle retourne les variables $resto et $ville
      'resto' => '',
      'ville' => '',
    ), $att, 'affichageCarte'));
  
  ob_start(); //met tout ce qui suit dans un tampon et ne l'envoie pas au navigateur 

    ?>

    <div id="map"></div>
  
  
    <script type="text/javascript">

        // carte avec zoom national par défaut
        var mapZoom = 5;
        var latitudeCarte = 45.7578003;
        var longitudeCarte = 3.143733 ;
    
        <?php
        global $wpdb;
        $townTable = $wpdb->prefix.'ville';
        $restoTable = $wpdb->prefix.'resto';
        
        // carte avec zoom sur le resto 
        if ($resto != ''){
            $SelectedResto = $wpdb->get_results("SELECT latitude, longitude FROM $restoTable WHERE name_code = '$resto' ");
            ?>
            mapZoom = 15;
            latitudeCarte = <?php echo $SelectedResto[0]->latitude;?>;
            longitudeCarte = <?php echo $SelectedResto[0]->longitude;?>;
            <?php
        }
      
        //carte avec zoom sur la ville
        else if ($ville != ''){
            $SelectedTown = $wpdb->get_results("SELECT town_latitude, town_longitude FROM $townTable WHERE town_code = '$ville'");
            ?>mapZoom = 13;
            latitudeCarte = <?php echo $SelectedTown[0]->town_latitude;?>;
            longitudeCarte = <?php echo $SelectedTown[0]->town_longitude;?>;
            <?php
        }
        ?>
        // fonction permettant d'afficher la carte
        function initMap() { 
            var map = new google.maps.Map(document.getElementById('map'), {
                zoom: mapZoom,
                center: {lat: latitudeCarte, lng: longitudeCarte},
            });
      

            <?php
            //affichage des icônes de localisation et des fenêtres d'information
            global $wpdb;
            $townTable = $wpdb->prefix.'ville';
            $restoTable = $wpdb->prefix.'resto';
            $result = $wpdb->get_results("SELECT * FROM $restoTable, $townTable WHERE $townTable.id = $restoTable.town_id");
            
            foreach($result as $ligne){
                echo '
                  var openIcon = \'http://dev30.e-knd.com/wp-content/uploads/2017/08/pointer.png\';
                  var openNextIcon = \'http://dev30.e-knd.com/wp-content/uploads/2017/08/pointer-next.png\';';

                //contenu de la fenêtre d'information d'un resto si celui-ci est ouvert
                if($ligne->restaurant_state == "open"){ 
                  echo'
                      var contentString'.$ligne->name_code.'= \'<div class="contentOpen" style="maxWidth:368px; text-align: center" >\'+          
                      \'<div class="bodyContentOpen" style="maxWidth:368px"><p style="float:left;">Bistro Régent - '.$ligne->name.'<br/>'.$ligne->address.'<br/>';
                      if(!empty($ligne->address_bis)){echo $ligne->address_bis.'<br/>';}
                  echo $ligne->postal_code.' '.$ligne->town.'<br/>Tél : '.$ligne->telephone.'</p>\'+\'<a href="/ou-trouver-les-bistros/'.(($ligne->name_code == "bordeaux_clemenceau" || $ligne->name_code == "bordeaux_st_remi" || $ligne->name_code =="bordeaux_republique" || $ligne->name_code =="bordeaux_condillac" || $ligne->name_code =="bordeaux_la_peniche")? "bordeaux":str_replace('_', '-', $ligne->name_code)).'" target="_blank" style="float:left;"><img src="http://dev30.e-knd.com/wp-content/uploads/2017/08/select-arrow.png"/></a>\'+
                        \'<img src="'.$ligne->image.'" style="float:left;"/>\'+
                      \'</div>\'+
                      \'</div>\';'; 
                } 
                //contenu de la fenêtre d'information d'un resto si celui-ci n'est pas encore ouvert
                else{ 
                  echo'
                      var contentString'.$ligne->name_code.' = \'<div class="contentNotOpen style="maxWidth:368px; text-align: center">\'+          
                      \'<div class="bodyContentNotOpen" style="maxWidth:368px"><p style="float:left;">OUVERTURE PROCHAINE<br/>Bistro Régent - '.$ligne->town.'</p>\'+
                      \'</div>\'+
                      \'</div>\';'; 
                }

                //affichage de la fenêtre d'information et de l'icône
                echo'
                    var infowindow'.$ligne->name_code.' = new google.maps.InfoWindow({
                        content: contentString'.$ligne->name_code.'}); 

                    var Marker'.$ligne->name_code.' = new google.maps.Marker({';
                        //coordonnées si restaurant ouvert
                        if ($ligne->restaurant_state == "open"){
                            echo 'position: {lat: '.$ligne->latitude.', lng: '.$ligne->longitude.'},';
                        } else{
                        // coordonnées si restaurant non ouvert
                            echo 'position: {lat: '.$ligne->town_latitude.', lng: '.$ligne->town_longitude.'},';
                        }
                        echo 'map: map,
                        icon: '.(($ligne->restaurant_state == "open")? "openIcon":"openNextIcon").'
                    });

                google.maps.event.addListener(Marker'.$ligne->name_code.', \'click\', function() {
                infowindow'.$ligne->name_code.'.open(map,Marker'.$ligne->name_code.');
                });';
            }
            ?>
  
map.controls[google.maps.ControlPosition.RIGHT_BOTTOM].push(document.getElementById('legend')); 
        }
  
    </script>
    <script async defer
    src="https://maps.googleapis.com/maps/api/js?key=AIzaSyASlakgZNLMNC1USmYWsXoYspORmeFuefQ&callback=initMap">
    </script>      
  <?php 
  return ob_get_clean(); //fonction lisant le contenu de sortie du tampon créé avec ob_start() puis l'efface
  }  
}
