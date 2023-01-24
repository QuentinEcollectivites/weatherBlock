<?php

namespace Drupal\weatherblock\Plugin\Block;

use Drupal\Core\Block\BlockBase;


/**
 * Provides an weather block for sidebar.
 *
 * @Block(
 *   id = "weatherblockweekly",
 *   admin_label = @Translation("weather Block Weekly"),
 *   category = @Translation("Bloc Météo Hebdomadaire")
 * )
 */

class WeatherBlockWeekly extends BlockBase {
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
        $titleCity = $resp["title"];
        $lat = explode(",", $resp["field_geolocalisation"])[0];
        $long = explode(",", $resp["field_geolocalisation"])[1];
        
        $meteourl = "https://api.open-meteo.com/v1/meteofrance?current_weather=true&latitude=".$lat."&longitude=".$long."&daily=weathercode,temperature_2m_max,temperature_2m_min&timezone=auto";
        $meteourl = str_replace(" ", "", $meteourl );
        $curlmeteo = curl_init($meteourl);
        curl_setopt($curlmeteo, CURLOPT_URL, $meteourl);
        curl_setopt($curlmeteo, CURLOPT_RETURNTRANSFER, true);
        // var_dump($meteourl);
        
       

        $respmeteo = curl_exec($curlmeteo);
        $respmeteo = json_decode($respmeteo, true);
        
        
        curl_close($curlmeteo);
        $weather = $respmeteo["current_weather"];
        // var_dump($respmeteo["daily"]);
        $temperature = $weather["temperature"];
        if( !$temperature ){
            $temperature = "Aucune donnée disponible.";
        }
        $weathercode = $weather["weathercode"];
        if( !$weathercode ){
            $weathercode = "fa-circle-info";
            
        }
        $windspeed = $weather["windspeed"];
        $time = $weather["time"];
        $time = date("d/m/Y H:i", strtotime($time));

        $allWeekTime = $respmeteo["daily"]["time"];
        $allWeekWeatherCode = $respmeteo["daily"]["weathercode"];
        $allWeektemperatureMax = $respmeteo["daily"]["temperature_2m_max"];
        $allWeektemperatureMin = $respmeteo["daily"]["temperature_2m_min"];
        
        $arrayWeather = array();
        for ($i = 0; $i < count($respmeteo["daily"]["time"]); $i++) {
            $dailyDate = date(" d/m ", strtotime($allWeekTime[$i]));
            $dailyWeatherCode = $allWeekWeatherCode[$i];
            
            $dailyTempMin = $allWeektemperatureMin[$i];
            if( !$dailyTempMin ){
                $dailyTempMin = "Aucune donnée disponible.";
            }
            $dailyTempMax = $allWeektemperatureMax[$i];
            if( !$dailyTempMax ){
                $dailyTempMax = "Aucune donnée disponible.";
            }
            switch ($dailyWeatherCode) {
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
                case 'NULL':
                    $codeIconWeather = "fa-circle-info";
                    $descriptionWeather = "Aucune données";
                    break;
            }
            $arrayWeather[$i] = [$dailyDate, $codeIconWeather, $descriptionWeather, $dailyTempMin, $dailyTempMax];
        } 
        $HtmlWeather = '';
        foreach($arrayWeather as $temp){
            $HtmlWeather .= '<a class="col d-flex card flex-column align-items-center justify-content-center" href="https://www.meteorama.fr/meteo-'.$titleCity.'.html">
            <h5>'.$temp[0].'</h5>
            <h6>T° Min/Max : '.$temp[3].'/'.$temp[4].' °C</h6>
            <div style="width: 100px; height: 100px;">
                <i class="fas '.$temp[1].' fa-fw" style="color: var(--primary-color); width: 100px; height: 100px;"></i>
            </div>
            <h3>'.$temp[2].'</h3>
            
            
          </a>';
        }
   

        return [
            '#theme' => 'weather-block-weekly',
            
            '#temperature' => $temperature,
            '#code' => $weathercode,
            '#vent' => $windspeed,
            '#codeIcone' => $codeIconWeather,
            '#descriptionWeather' => $descriptionWeather,
            '#date' => $time,
            '#city' => $titleCity,
            '#arrayweather' => $HtmlWeather
        ];

    }
    public function getCacheMaxAge() {
        return 0;
    }

}
