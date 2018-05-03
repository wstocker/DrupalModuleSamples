<?php
if (!function_exists('getallheaders')) {
  function getallheaders()
  {
    $headers = '';
    foreach ($_SERVER as $name => $value) {
      if (substr($name, 0, 5) == 'HTTP_') {
        $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
      }
    }
    return $headers;
  }
}
$headers = getallheaders();

// safety check
if (!class_exists('Redis')) {
  header('X-Failure-Reason: redis-class-not-exist');
  exit;
}
// validation
if (empty($_GET['url']) && empty($_POST['urls'])) {
  header('X-Failure-Reason: empty-get-or-post');
  exit;
}
$urls = array();
if (!empty($_GET['url'])) {
  $urls[] = $_GET['url'];
} else {
  if (!is_array($_POST['urls'])) {
    header('X-Failure-Reason: post-not-array');
    exit;
  }
  $urls = $_POST['urls'];
}
foreach ($urls as &$url) {
  $url = urldecode($url);
  if (
    !filter_var($url, FILTER_VALIDATE_URL) ||
    (
      $_SERVER['HTTP_HOST'] != 'local.nationalreview.com' &&
      !preg_match('/^' . $_SERVER['HTTP_HOST'] . '$/', parse_url($url, PHP_URL_HOST))
    )
  ) {
    header('X-Failure-Reason: url-not-match-host-or-validate');
    exit;
  }
  // replace hash
  $url = preg_replace('/#.*$/', '', $url);
}
unset($url);

// setup our default response
$counts = array();

// setup redis
$redis = new Redis();
$settings = json_decode(@$_SERVER["PRESSFLOW_SETTINGS"]);
if (empty($settings)) {
  $settings = new stdClass();
  $settings->conf = new stdClass();
  $settings->conf->redis_client_host = 'removed_for_security';
  $settings->conf->redis_client_port = 12345;
  $settings->conf->redis_client_password = 'removed_for_security';
}
try {
  $redis->connect($settings->conf->redis_client_host, $settings->conf->redis_client_port);
  $redis->auth($settings->conf->redis_client_password);
} catch (Exception $e) {
  header('X-Failure-Reason: redis-exception');
  exit;
}

$cache_keys = [];
foreach ($urls as $url) {
  $cache_keys[] = get_cache_key($url);
}
$cached_counts = $redis->getMultiple($cache_keys);
if (!empty($headers['If-None-Match']) && substr($headers['If-None-Match'], 0, 1) == array_sum($cached_counts)) {
  not_modified($headers['If-None-Match']);
}
$i = 0;
$fetched = 0;
foreach ($cached_counts as $cached_count) {
  $url = $urls[$i];
  $json = array(
    'url' => $url,
    'cached' => false,
    'count' => 0,
  );
  if ($cached_count !== false) {
    $json['count'] = $cached_count;
  } else if ($fetched < 1) {
    // to limit the impact of refreshing the cache, only fetch 1 new count in a single request
    foreach (array('googleplus', 'twitter') as $source) {
      $json['count'] += get_count($url, $source);
    }
    $redis->setex(get_cache_key($url), 300, $json['count']);
    ++$fetched;
  }
  $counts[] = $json;
  ++$i;
}
print_response($counts);

function get_count($url, $source)
{
  switch ($source) {
    case 'googleplus':
      $ch = curl_init('https://plusone.google.com/u/0/_/+1/fastbutton?url=' . urlencode($url) . '&count=true');
      curl_setopt_array($ch, array(
        CURLOPT_RETURNTRANSFER => true, // return web page
        CURLOPT_HEADER => false, // don't return headers
        CURLOPT_FOLLOWLOCATION => true, // follow redirects
        CURLOPT_ENCODING => "", // handle all encodings
        CURLOPT_USERAGENT => 'share_counts', // who am i
        CURLOPT_AUTOREFERER => true, // set referer on redirect
        CURLOPT_CONNECTTIMEOUT => 5, // timeout on connect
        CURLOPT_TIMEOUT => 10, // timeout on response
        CURLOPT_MAXREDIRS => 3, // stop after 3 redirects
      ));
      $result = curl_exec($ch);
      curl_close($ch);
      preg_match('/window\.__SSR = {c: ([\d]+)/', $result, $matches);
      if (isset($matches[0])) {
        return (int)str_replace('window.__SSR = {c: ', '', $matches[0]);
      }
      return 0;
      break;
    case 'twitter':
      $ch = curl_init('http://opensharecount.com/count.json?url=' . urlencode($url));
      curl_setopt_array($ch, array(
        CURLOPT_RETURNTRANSFER => true, // return web page
        CURLOPT_HEADER => false, // don't return headers
        CURLOPT_FOLLOWLOCATION => true, // follow redirects
        CURLOPT_ENCODING => "", // handle all encodings
        CURLOPT_USERAGENT => 'share_counts', // who am i
        CURLOPT_AUTOREFERER => true, // set referer on redirect
        CURLOPT_CONNECTTIMEOUT => 5, // timeout on connect
        CURLOPT_TIMEOUT => 10, // timeout on response
        CURLOPT_MAXREDIRS => 3, // stop after 3 redirects
      ));
      $result = json_decode(trim(curl_exec($ch)));
      curl_close($ch);
      if ($result->count) {
        return (int)$result->count;
      }
      return 0;
      break;
  }
  return 0;
}

function get_cache_key($url) {
  return 'share_counts_' . sha1($url);
}

function print_response($counts)
{
  $total = 0;
  foreach ($counts as $count) {
    $total += $count['count'];
  }
  header('Content-Type: application/json');
  header('Etag: ' . $total . substr(md5(rand()), 0, 7));
  if (count($counts) == 1) {
    $counts = reset($counts);
  }
  echo json_encode($counts);
  exit;
}

function not_modified($etag)
{
  header('304 Not Modified', true, 304);
  header('Etag: ' . $etag);
  exit;
}