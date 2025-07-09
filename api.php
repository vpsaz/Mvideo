<?php
/**
 * @author    校长bloG <1213235865@qq.com>
 * @github    https://github.com/vpsaz/XzPlayer
 */

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET');

$config_file = __DIR__ . '/config.php';
$conf = include($config_file);

$url = $_POST['url'] ?? $_GET['url'] ?? '';
$titleParam = $_POST['title'] ?? $_GET['title'] ?? '';

if (empty($url)) {
    header('Content-type: application/json;charset=utf-8');
    echo json_encode(['code' => 404, 'msg' => "请输入URL"], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

if (!preg_match('/^https?:\/\//i', $url)) {
    header('Content-type: application/json;charset=utf-8');
    echo json_encode(['code' => 404, 'msg' => "请输入正确的URL"], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

$baiapiApiKey = '1393c25907a85e0549621b829389a548'; // M3U8广告过滤 的密钥
$baiapikeywords = ''; // 自定义广告关键词 (多个使用","分隔)
$baiapiUrl = 'https://baiapi.cn/api/m3u8gl/?apikey=' . $baiapiApiKey . '&url=' . urlencode($url) . '&keywords=' . $baiapikeywords;

$cacheDir = __DIR__ . '/Cache';
if (!is_dir($cacheDir)) {
    mkdir($cacheDir, 0755, true);
}

$cacheFileName = md5($url) . '.m3u8';
$cacheFilePath = $cacheDir . '/' . $cacheFileName;

$cacheExpire = 60;

if (!file_exists($cacheFilePath) || filemtime($cacheFilePath) < (time() - $cacheExpire)) {
    $maxAttempts = 2;
    $success = false;

    for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
        $content = @file_get_contents($baiapiUrl);
        if ($content !== false && strlen($content) > 10) {
            file_put_contents($cacheFilePath, $content);
            $success = true;
            break;
        }
    }

    if (!$success) {
        header('Content-type: application/json;charset=utf-8');
        echo json_encode(['code' => 500, 'msg' => '无法从 BaiAPI 获取播放地址，请稍后再试'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }
}

$now = time();
foreach (glob(__DIR__ . '/Cache/*.m3u8') as $file) {
    if (is_file($file) && filemtime($file) < $now - $cacheExpire) {
        @unlink($file);
    }
}

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'];
$scriptDir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
if (is_file($cacheFilePath)) {
    $safe_url = $protocol . $host . $scriptDir . '/Cache/' . $cacheFileName;
} else {
    header('Content-type: application/json;charset=utf-8');
    echo json_encode(['code' => 500, 'msg' => '缓存文件不存在，无法播放'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

$safe_url = htmlspecialchars($safe_url, ENT_QUOTES);

$currentDomain = $_SERVER['HTTP_HOST'];
$refererDomain = parse_url($_SERVER['HTTP_REFERER'] ?? '', PHP_URL_HOST);

if ($refererDomain === $currentDomain && !empty($titleParam)) {
    $title = '正在播放：' . htmlspecialchars($titleParam, ENT_QUOTES);
} else {
    $title = htmlspecialchars($conf['site_title'], ENT_QUOTES) . ' - 播放器';
}
?>
<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php echo $title; ?></title>
    <meta name="description" content="<?php echo $conf['description']; ?>" />
    <meta name="keywords" content="<?php echo $conf['keywords']; ?>" />
    <link rel="shortcut icon" href="https://pic1.imgdb.cn/item/6812e03558cb8da5c8d5d3c3.png" type="image/x-icon" />
    <script src="https://cdn.jsdelivr.net/npm/hls.js@1.4.8/dist/hls.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flv.js@1.6.2/dist/flv.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/dashjs@4.7.1/dist/dash.all.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/artplayer@5.2.3/dist/artplayer.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/artplayer-plugin-ads@latest/dist/artplayer-plugin-ads.min.js"></script>
    <style>
        html, body {
            width: 100%;
            height: 100%;
            margin: 0;
            padding: 0;
            overflow: hidden;
            background-color: #000;
        }
        #video {
            width: 100%;
            height: 100%;
            position: fixed;
            top: 0;
            left: 0;
        }
    </style>
</head>
<body>
    <div id="video"></div>
    <script>
        window.videoUrl = '<?php echo $safe_url; ?>';
    </script>
    <script src="https://v.vpsaz.cn/js/player.js"></script>
</body>
</html>
