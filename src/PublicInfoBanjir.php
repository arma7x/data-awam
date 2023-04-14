<?php
declare(strict_types=1);

namespace Arma7x\LocalServices;

use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;

class PublicInfoBanjir {

  static public $StateList = [
    "KDH" => "Kedah",
    "PNG" => "Pulau Pinang",
    "PRK" => "Perak",
    "SEL" => "Selangor",
    "WLH" => "Wilayah Persekutuan Kuala Lumpur",
    "PTJ" => "Wilayah Persekutuan Putrajaya",
    "NSN" => "Negeri Sembilan",
    "MLK" => "Melaka",
    "JHR" => "Johor",
    "PHG" => "Pahang",
    "TRG" => "Terengganu",
    "KEL" => "Kelantan",
    "SRK" => "Sarawak",
    "SAB" => "Sabah",
    "WLP" => "Wilayah Persekutuan Labuan"
  ];

  static public function getRainLevel(string $state = 'KDH', bool $html = false) {
    try {
      // https://publicinfobanjir.water.gov.my/hujan/data-hujan/?state=KEL&lang=en
      $client = new Client(['base_uri' => 'http://publicinfobanjir.water.gov.my']);
      $res = $client->get('/wp-content/themes/shapely/agency/searchresultrainfall.php', ['query' => ['state' => $state, 'district' => 'ALL', 'station' => 'ALL', 'language' => '1', 'loginStatus' => '0'], 'debug' => false]);
      $webpage = '<!DOCTYPE html><html><body>'.(string) $res->getBody().'</body></html>';
      $webpage = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $webpage);
      $js = '<script>function calculate(){var e=new URL(document.location.toString());e.searchParams.set("html",0),fetch(e.toString()).then(e=>e.json()).then(e=>{console.clear();const t={};e.data.forEach(e=>{var a=0;if(null!=e.DailyRainfall&&e.DailyRainfall.length>0){e.DailyRainfall.forEach(e=>{const t=parseFloat(e);t>=0&&(a+=t)}),null==t[e.District]&&(t[e.District]=0);var n=parseFloat(e.RainfallfromMidnight);n>=0&&(t[e.District]+=n),t[e.District]+=a}});var a=[];for(var n in t)a.push({name:n,value:t[n]});a.sort((e,t)=>e.value>t.value?-1:1);const l=new Date,o=l.getDate(),r=l.getMonth()+1;l.setTime(l.getTime()-5184e5);const i=l.getDate(),c=l.getMonth()+1;var s=document.createElement("ul");s.setAttribute("id","total_rainfall");var m=`\nTotal rainfall for 7 consecutive days(${o}/${r} - ${i}/${c}):\n`,u=document.createElement("h3");u.setAttribute("style","margin-left:4px;"),document.body.appendChild(u),u.innerHTML=m,a.forEach(e=>{var t=30-e.name.length;m+=`${e.name}${"-".repeat(t)}-> ${e.value.toFixed(2)}mm\n`;var a=document.createElement("li");s.appendChild(a),a.innerHTML=`${e.name} ${e.value.toFixed(2)}mm\n`}),document.body.appendChild(s),console.log(m)}).catch(e=>{console.error(e)})}calculate();</script>';
      $webpage = preg_replace('#<body(.*?)</body>#is', '<body$1'.$js.'</body>', $webpage);
      $webpage = str_replace('https://maxcdn.bootstrapcdn.com/bootstrap', 'https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap', $webpage);
      if ($html)
        return $webpage;
      $crawler = new Crawler($webpage);
      $table = $crawler->filter('table');
      $headers = [];
      $dailyRailfallHeaders = [];
      $results = [];
      foreach ($table->first()->children() as $idx => $child) {
        if ($idx === 0) {
          foreach($child->childNodes as $idx1 => $child1) {
            if ($idx1 == 2) {
              foreach($child1->childNodes as $idx2 => $child2) {
                $val = trim($child2->textContent);
                if (strlen($val) > 0)
                  array_push($headers, $val);
              }
            } else if ($idx1 == 3) {
              foreach($child1->childNodes as $idx2 => $child2) {
                $val = trim($child2->textContent);
                if (strlen($val) > 0)
                  array_push($dailyRailfallHeaders, $val);
              }
            }
          }
        } else if ($idx === 1) {
          // $h = ["No.","Station ID","Station","District","Last Updated","Daily Rainfall","Rainfall from Midnight","Total 1 Hour(Now)"];
          $data = [];
          $index = 0;
          $daily = [];
          $temp_result = [];
          foreach($child->childNodes as $idx1 => $child1) {
            $val = trim($child1->textContent);
            if (strlen($val) > 0) {
              if ($index <= 4) {
                $temp_result[$headers[$index]] = $val;
              } else if ($index >= 5 && $index <= 10) {
                $daily[$dailyRailfallHeaders[$index-5]] = $child1->textContent;
              } else {
                if ($index == 11)
                  $temp_result[$headers[6]] = $val;
                else if ($index == 12)
                  $temp_result[$headers[7]] = $val;
              }
              $index++;
              if ($index == 13) {
                $temp_result[$headers[5]] = $daily;
                array_push($data, $temp_result);;
                $index = 0;
                $daily = [];
                $temp_result = [];
              }
            }
          }
          $results['data'] = $data;
        }
      }
      return $results;
    } catch(\Exception $e) {
      throw($e);
    }
  }

  static public function getRiverLevel(string $state = 'KDH', bool $html = false) {
    try {
      $client = new Client(['base_uri' => 'http://publicinfobanjir.water.gov.my']);
      $res = $client->get('/aras-air/data-paras-air/aras-air-data/', ['query' => ['state' => $state, 'district' => 'ALL', 'station' => 'ALL', 'lang' => 'en'], 'debug' => false]);
      $webpage = '<!DOCTYPE html><html><body>'.(string) $res->getBody().'</body></html>';
      $webpage = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $webpage);
      if ($html)
        return $webpage;
      $crawler = new Crawler($webpage);
      $table = $crawler->filter('table');
      $headers = [];
      $thresholds = [];
      $temp_result = [];
      $results = [];
      foreach ($table->first()->children() as $idx => $child) {
        if ($idx === 0) {
          foreach($child->childNodes as $idx1 => $child1) {
            if ($idx1 == 2) {
              foreach($child1->childNodes as $idx2 => $child2) {
                $val = trim($child2->textContent);
                if (strlen($val) > 0)
                  array_push($headers, $val);
              }
            } else if ($idx1 == 4) {
              foreach($child1->childNodes as $idx2 => $child2) {
                $val = trim($child2->textContent);
                if (strlen($val) > 0)
                  array_push($thresholds, $val);
              }
            }
          }
        } else if ($idx === 1) {
          foreach($child->childNodes as $idx1 => $child1) {
            if ($idx1 > 0) {
              $data = [];
              foreach ($child1->childNodes as $idx2 => $child2) {
                if ($idx2 < 8)
                  $data[$headers[$idx2]] = trim($child2->textContent);
                else
                  $data[$thresholds[$idx2 - 8]] = trim($child2->textContent);
              }
              array_push($temp_result, $data);
            }
          }
        }
      }
      $results['data'] = $temp_result;
      return $results;
    } catch(\Exception $e) {
      throw($e);
    }
  }

}
