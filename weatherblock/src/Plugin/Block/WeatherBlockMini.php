<?php

namespace Drupal\weatherblock\Plugin\Block;

use Drupal\Core\Block\BlockBase;


/**
 * Provides an weather block for sidebar.
 *
 * @Block(
 *   id = "weatherblockmini",
 *   admin_label = @Translation("weather Block Mini"),
 *   category = @Translation("weatherblockmini")
 * )
 */

class WeatherBlockMini extends BlockBase {
    /**
     * {@inheritdoc}
     */
   
    public function build() {
        
        $url = "http://".$_SERVER["HTTP_HOST"]."/gps";

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

       
        $resp = curl_exec($curl);
        curl_close($curl);
        $dechet = array("[", "]");
        $resp = str_replace($dechet, "", $resp );
        $resp = json_decode($resp, true);
        $lat = explode(",", $resp["field_geolocalisation"])[0];
        $long = explode(",", $resp["field_geolocalisation"])[1];
        
        $meteourl = "https://api.open-meteo.com/v1/meteofrance?current_weather=true&latitude=".$lat."&longitude=".$long;
        $meteourl = str_replace(" ", "", $meteourl );
        $curlmeteo = curl_init($meteourl);
        curl_setopt($curlmeteo, CURLOPT_URL, $meteourl);
        curl_setopt($curlmeteo, CURLOPT_RETURNTRANSFER, true);

       

        $respmeteo = curl_exec($curlmeteo);
        // $respmeteo = json_decode($respmeteo, true);
        curl_close($curlmeteo);
        $string = $respmeteo; //your string
        $string = str_replace('\n', '', $string);
        $string = rtrim($string, ',');
        $string = "[" . trim($string) . "]";
        $json = json_decode($string, true);
        $weather = ($json[0]["current_weather"]);
        $temperature = $weather["temperature"];
        $weathercode = $weather["weathercode"];
        $windspeed = $weather["windspeed"];
        $time = $weather["time"];
        $time = date("d/m/Y H:i", strtotime($time) + 60*60);

        
        switch ($weathercode) {
            case 0:
                $codeIconWeather = "fa-sun";
                $descriptionWeather = "Soleil";
                break;
            case 1:
            case 2:
            case 3:
                $codeIconWeather = "fa-cloud-sun";
                $descriptionWeather = "Nuages";
                break;
            case 45:
            case 48:
                $codeIconWeather = "fa-wind";
                $descriptionWeather = "Vent";
                break;
            case 51: 
            case 53: 
            case 55: 
            case 56: 
            case 57:
                $codeIconWeather = "fa-smog";
                $descriptionWeather = "Brouillard";
                break;
            case 61:
                case 63:
                case 65:
                case 66:
                case 67:
                $codeIconWeather = "fa-cloud-rain";
                $descriptionWeather = "Pluie";
                break;
            case 71:
                case 73:
                case 75:
                case 77:
                $codeIconWeather = "fa-cloud-meatball";
                $descriptionWeather = "Neige";
                break;
            case 80:
            case 81:
            case 82:
                $codeIconWeather = "fa-cloud-showers-heavy";
                $descriptionWeather = "Averses";
                break;
            case 85:
            case 86:
                $codeIconWeather = "fa-snowflake";
                $descriptionWeather = "Neige";

                break;
            case 95:
                case 96:
                case 99:
                $codeIconWeather = "fa-cloud-bolt";
                $descriptionWeather = "Orage";
                break;
        }
        
        

        return [
            '#theme' => 'weather-block-mini',
            
            '#temperature' => $temperature,
            '#codeIcone' => $codeIconWeather,
            '#descriptionWeather' => $descriptionWeather
        ];

    }
    public function getCacheMaxAge() {
        return 0;
    }

}
