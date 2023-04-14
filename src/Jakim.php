<?php
declare(strict_types=1);

namespace Arma7x\LocalServices;

use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;

class Jakim {

  public static function getWaktuSolatZoneByState(): array {
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

  public static function getWaktuSolat(string $zone, string $duration): array {

  }

}
