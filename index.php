<?php

while (true) {
  include "/var/www/html/twitter/twitinfo.php";
  //country codes which i like get twits from
  $contry_code_list = array("us", "gb", "il", "nl", "tr", "fr", "de", "dk", "cz", "be", "bg");

  try {

    // Connect to MongoDB
    $conn = new Mongo('localhost');
    // connect to test database
    $db = $conn->twit_warehouse;
 
    
    $collection = $db->countries_and_cities_coordinates;
    $collection2 = $db->twitts_of_all_places;
    $collection3 = $db->twits_collected;

    //To get counter of program i want to store in a text doc.
    $f = @fopen("sayac", "r");
    $sayacNumarasi = @fread($f, filesize("sayac"));
    @fclose($f);
    $sayacNumarasi = str_replace("\n", "", $sayacNumarasi);

    for ($fd = 0; $fd < count($contry_code_list); $fd++) {

      $query = array('country_code' => $contry_code_list[$fd]);
      $all_countries = $collection->find($query);
      foreach ($all_countries as $country_detais) {
        $country_detais['Latitude'];
        $country_detais['Longitude'];
        //echo $country_detais['Latitude']."-"."\n";continue;
        $json_twit_message = 0;
        $coords = "geocode=" . $country_detais['Latitude'] . "," . str_replace(array("\n", " "), array("", ""), $country_detais['Longitude']) . ",5mi";

        $twitdegisken = new TwitterOAuth($consumer, $consumersecret, $accestoken, $accestokensecret);
        $kriter = "";
        $location = "&" . $coords; //"&geocode=41.024491, 28.998447,30km";
        $language_search = ""; //"&lang=tr";
        $result_type = "&result_type=recent";
        $count_of_tweets = "&count=80";
        $nerede = ""; //rawurlencode("near:'İstanbul' within:20km");

        $twitler = $twitdegisken->get2("https://api.twitter.com/1.1/search/tweets.json?q=" . $kriter . "" . $location . "" . $language_search . "" . $count_of_tweets . "" . $nerede . "" . $result_type . "");

        $json_twit_message = json_decode($twitler, true);
        $json_twit_message = $json_twit_message['statuses'];

        for ($x = 0; $x < count($json_twit_message); $x++) {
          $twitter_datas = $json_twit_message[$x];

          $dataArray = array();
          $dataArray['twit_emotion_type'] = "";
          $dataArray['twit_emotion_keyword'] = "";
          $dataArray['twit_text'] = $twitter_datas['text'];
          $dataArray['twit_id'] = $twitter_datas['id'];
          $dataArray['twit_id_str'] = $twitter_datas['id_str'];
          $dataArray['twit_tarih_saat'] = $twitter_datas['created_at'];
          $dataArray['twit_geo'] = $twitter_datas['geo'];
          $dataArray['twit_coordinates'] = $twitter_datas['coordinates'];
          $dataArray['twit_place'] = $twitter_datas['place'];
          $dataArray['twit_language'] = $twitter_datas['lang'];


          $dataArray['twit_user_id'] = $twitter_datas['user']['id'];
          $dataArray['twit_user_id_str'] = $twitter_datas['user']['id_str'];
          $dataArray['twit_user_name'] = $twitter_datas['user']['name'];
          $dataArray['twit_user_screen_name'] = $twitter_datas['user']['screen_name'];
          $dataArray['twit_user_location'] = $twitter_datas['user']['location'];
          $dataArray['twit_user_p_location'] = $twitter_datas['user']['profile_location'];
          $dataArray['twit_user_description'] = $twitter_datas['user']['description'];
          $dataArray['twit_user_fol_counts'] = $twitter_datas['user']['followers_count'];
          $dataArray['twit_user_fri_counts'] = $twitter_datas['user']['friends_count'];
          $dataArray['twit_user_timezone'] = $twitter_datas['user']['time_zone'];
          $dataArray['twit_user_language'] = $twitter_datas['user']['lang'];
          $dataArray['twit_user_p_image'] = $twitter_datas['user']['profile_image_url'];
          $dataArray['twit_user_timezone'] = $twitter_datas['user']['time_zone'];
          $dataArray['twit_se_country_code'] = $country_detais['country_code'];
          $dataArray['twit_se_city'] = $country_detais['city'];
          $dataArray['twit_se_region'] = $country_detais['Region'];
          $dataArray['twit_se_coords'] = $country_detais['Latitude'] . "," . $country_detais['Longitude'];


          //$collection2->insert( $dataArray );
          $detay = $collection3->insert($dataArray);
        }




        $sayacNumarasi += 80;
        //Update new counter 
        $f = fopen("sayac", "w");
        fwrite($f, $sayacNumarasi, strlen($sayacNumarasi));
        fclose($f);
        if ($sayacNumarasi % 480 == 0) {
          sleep(900);
        }
      }
    }


    $conn->close();
  } catch (MongoConnectionException $e) {
    // if there was an error, we catch and display the problem here
    echo $e->getMessage();
  } catch (MongoException $e) {
    echo $e->getMessage();
  }
}
?>

