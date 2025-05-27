<?php
/**
 * @author    校长bloG <1213235865@qq.com>
 * @github    https://github.com/vpsaz/Mvideo
 */

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET');

$url = $_POST['url'] ?? $_GET['url'] ?? '';

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

header('Content-type: text/html;charset=utf-8');

$safe_url = htmlspecialchars($url, ENT_QUOTES);
?>
<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $conf['site_title']; ?> - 播放器</title>
    <meta name="description" content="<?php echo $conf['description']; ?>">
    <meta name="keywords" content="<?php echo $conf['keywords']; ?>">
    <link rel="shortcut icon" href="https://pic1.imgdb.cn/item/6812e03558cb8da5c8d5d3c3.png" type="image/x-icon">
    <script src="https://unpkg.com/hls.js@1.4.8/dist/hls.min.js"></script>
    <script src="https://unpkg.com/flv.js@1.6.2/dist/flv.min.js"></script>
    <script src="https://unpkg.com/dashjs@4.7.1/dist/dash.all.min.js"></script>
    <script src="https://unpkg.com/artplayer@5.2.3/dist/artplayer.js"></script>
    <script src="https://unpkg.com/artplayer-plugin-ads@latest/dist/artplayer-plugin-ads.js"></script>
    <style>
        body,
        html {
            width: 100%;
            height: 100%;
            padding: 0;
            margin: 0
        }

        #video {
            height: 100%;
            width: 100%
        }
    </style>
</head>
<body>
    <div id="video"></div>
    <script>
        window.videoUrl = '<?php echo $safe_url; ?>';
    </script>
    <script src="https://caob.tech/qita/ysss/js/player.js"></script>
    <script src="https://caob.tech/qita/ysss/js/WatchTogether.js"></script>
</body>
</html>
