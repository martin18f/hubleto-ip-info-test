<?php

namespace Hubleto\App\Custom\IpInfoTest\Controllers;

class Home extends \Hubleto\Erp\Controller
{
  public function getBreadcrumbs(): array
  {
    return array_merge(parent::getBreadcrumbs(), [
      [ 'url' => 'ipinfotest', 'content' => 'IpInfoTest' ],
    ]);
  }

  private function fetchIpGeo(string $ip): array
{
  $endpoints = [
    // Techniknews často blokuje server-side (Cloudflare challenge). Skúsime, ale máme fallback.
    [ 'name' => 'techniknews', 'url' => 'https://api.techniknews.net/ipgeo/' . rawurlencode($ip) ],
    // Spoľahlivý free fallback
    [ 'name' => 'ipwho.is', 'url' => 'https://ipwho.is/' . rawurlencode($ip) ],
  ];

  $lastError = null;

  foreach ($endpoints as $ep) {
    try {
      $data = $this->httpGetJson($ep['url']);
      if (!is_array($data)) throw new \RuntimeException('Invalid JSON');

      // ipwho.is vracia success=false
      if (($ep['name'] === 'ipwho.is') && isset($data['success']) && $data['success'] === false) {
        $msg = $data['message'] ?? 'Unknown error';
        throw new \RuntimeException('API error: ' . $msg);
      }

      $data['_source'] = $ep['name'];
      return $data;
    } catch (\Throwable $e) {
      $lastError = $ep['name'] . ': ' . $e->getMessage();
    }
  }

  throw new \RuntimeException($lastError ?? 'All API endpoints failed');
}

private function httpGetJson(string $url): array
{
  // Prefer cURL (lepší error reporting)
  if (function_exists('curl_init')) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_TIMEOUT => 12,
      CURLOPT_CONNECTTIMEOUT => 6,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTPHEADER => [
        'Accept: application/json',
        'User-Agent: Mozilla/5.0 (HubletoIpInfoTest/1.0)',
      ],
    ]);

    $body = curl_exec($ch);
    $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr = curl_error($ch);
    curl_close($ch);

    if ($body === false) {
      throw new \RuntimeException('cURL failed: ' . ($curlErr ?: 'unknown'));
    }
    if ($httpCode >= 400) {
      throw new \RuntimeException('HTTP ' . $httpCode);
    }

    $decoded = json_decode($body, true);
    if (!is_array($decoded)) {
      throw new \RuntimeException('Invalid JSON response');
    }
    return $decoded;
  }

  // Fallback (menej ideálne)
  $body = @file_get_contents($url);
  if ($body === false) throw new \RuntimeException('Request failed');

  $decoded = json_decode($body, true);
  if (!is_array($decoded)) throw new \RuntimeException('Invalid JSON response');
  return $decoded;
}


  public function prepareView(): void
  {
    parent::prepareView();

    $ip = trim((string) ($_GET['ip'] ?? ''));
    $this->viewParams['now'] = date('Y-m-d H:i:s');
    $this->viewParams['ip'] = $ip;
    $this->viewParams['error'] = null;
    $this->viewParams['result'] = null;

    if ($ip !== '') {
      $isValidIp = filter_var($ip, FILTER_VALIDATE_IP) !== false;
      if (!$isValidIp) {
        $this->viewParams['error'] = 'Neplatná IP adresa. Skús napr. 8.8.8.8';
      } else {
        try {
          $this->viewParams['result'] = $this->fetchIpGeo($ip);
        } catch (\Throwable $e) {
          $this->viewParams['error'] = 'Nepodarilo sa získať údaje z API: ' . $e->getMessage();
        }
      }
    }

    $this->setView('@Hubleto:App:Custom:IpInfoTest/Home.twig');
  }
}
