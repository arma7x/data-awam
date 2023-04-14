<?php
declare(strict_types=1);

namespace Arma7x\LocalServices;

use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;

class Jakim {

  public static $DurationType = ['today', 'week', 'year', 'duration'];
  public static $DayTranslation = [
    'sunday' => 'Ahad',
    'monday' => 'Isnin',
    'tuesday' => 'Selasa',
    'wednesday' => 'Rabu',
    'thursday' => 'Khamis',
    'friday' => 'Jumaat',
    'saturday' => 'Sabtu'
  ];

  public static function getWaktuSolatZones(): array {
    $zoneByState = [];
    $client = new Client(['base_uri' => 'https://www.e-solat.gov.my']);
    $res = $client->get('/index.php', ['query' => ['siteId' => 24, 'pageId' => 24], 'debug' => false]);
    $crawler = new Crawler((string) $res->getBody());
    foreach ($crawler->filter('select#inputZone')->first()->children() as $idx => $optgroup) {
      if ($idx > 0) {
        $zoneByState[$optgroup->attributes[0]->value] = [];
        foreach($optgroup->childNodes as $option) {
          $arr = explode('-', $option->textContent);
          $zoneByState[$optgroup->attributes[0]->value][trim($arr[0])] = trim($arr[1]);
        }
      }
    }
    return $zoneByState;
  }

  public static function getWaktuSolat(string $zone, string $duration, string $datestart = '', string $dateend = ''): array {
    $client = new Client(['base_uri' => 'https://www.e-solat.gov.my']);
    $week_n_year = function(string $zone, string $duration, string $datestart = '', string $dateend = '') use($client): array {
      $res = $client->get('/index.php', ['query' => ['r' => 'esolatApi/takwimsolat', 'period' => $duration, 'zone' => $zone], 'debug' => false]);
      return json_decode((string) $res->getBody(), TRUE);
    };
    $cases = [
      'today' => function(string $zone, string $duration, string $datestart = '', string $dateend = '') use($client): array {
        $res = $client->post('/index.php', ['query' => ['r' => 'esolatApi/takwimsolat', 'period' => 'duration', 'zone' => $zone], 'form_params' => ['datestart' => date('Y-m-d'), 'dateend' => date('Y-m-d')], 'debug' => false]);
        return json_decode((string) $res->getBody(), TRUE);
      },
      'duration' => function(string $zone, string $duration, string $datestart = '', string $dateend = '') use($client): array {
        $res = $client->post('/index.php', ['query' => ['r' => 'esolatApi/takwimsolat', 'period' => 'duration', 'zone' => $zone], 'form_params' => ['datestart' => $datestart, 'dateend' => $dateend], 'debug' => false]);
        return json_decode((string) $res->getBody(), TRUE);
      },
      'week' => $week_n_year,
      'year' => $week_n_year,
    ];

    return array_key_exists($duration, $cases) ? $cases[$duration]($zone, $duration, $datestart, $dateend) : throw new \Exception("Unknown duration: $duration");
  }

}
