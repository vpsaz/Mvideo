<?php
function isBrowser() {
    $userAgent = $_SERVER['HTTP_USER_AGENT'];

    if (preg_match('/(MSIE|Trident|Edge|Firefox|Chrome|Safari|Opera)/i', $userAgent)) {
        return true;
    }

    return false;
}

if (!isBrowser()) {
    header("Location: https://cn.bing.com/search?q=%E8%AF%B7%E4%BD%BF%E7%94%A8%E6%B5%8F%E8%A7%88%E5%99%A8%E6%89%93%E5%BC%80");
    exit();
}

$selected_source = isset($_GET['y'])? $_GET['y'] : (isset($_COOKIE['selected_source'])? $_COOKIE['selected_source'] : '1');
$search_query = isset($_GET['search'])? urlencode($_GET['search']) : '';

$search_results = [];
if ($search_query) {
    $search_url = "https://baiapi.cn/api/ysss?y={$selected_source}&wd={$search_query}";
    $search_data = @file_get_contents($search_url);
    if ($search_data) {
        $search_results = json_decode($search_data, true);
    }
}

$movie_details = null;
if (isset($_GET['movie_id'])) {
    $details_url = "https://baiapi.cn/api/ysss?y={$selected_source}&id=". urlencode($_GET['movie_id']);
    $details_data = @file_get_contents($details_url);
    if ($details_data) {
        $movie_details = json_decode($details_data, true);
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>影视搜索</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            background-color: #f7f7f7;
            margin: 0;
            padding: 0;
            background-image: url(), url(https://xxx.cn/bj.svg); // 背景图
            background-position: right bottom, left top;
            background-repeat: no-repeat, repeat;
        }
        #container {
            max-width: 800px;
            width: 100%;
            padding: 20px;
        }
        h1 {
            text-align: center;
            color: #333;
        }
        #searchForm {
            text-align: center;
            margin-bottom: 20px;
        }
        #searchForm input[type="text"] {
            width: 70%;
            padding: 8px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        #searchForm button {
            padding: 8px 15px;
            font-size: 16px;
            color: #fff;
            background-color: #007bff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        #movieList, #movieDetails {
            background-color: #fff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        #movieList button {
            display: block;
            width: 100%;
            margin: 5px 0;
            padding: 10px;
            text-align: left;
            background-color: #f0f0f0;
            border: 1px solid #ddd;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            color: #333;
        }
        #movieList button:hover {
            background-color: #e9ecef;
        }
        #movieDetails img {
            width: 210px;
            height: 290px;
            margin-top: 10px;
            display: block;
            border-radius: 8px;
        }
        table {
            width: 100%;
            margin: 20px 0;
            border-collapse: collapse;
            border-radius: 8px;
            overflow: hidden;
        }
        table th, table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
            white-space: nowrap;
        }
        table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        table td {
            background-color: #fafafa;
        }
        #movieDetails .details {
            max-height: 300px;
            overflow-x: auto;
            overflow-y: auto;
            margin-top: 10px;
        }
        .movie-info {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }
        .movie-info .details {
            width: 70%;
        }
        .movie-info .poster {
            width: 28%;
        }
        .movie-info .content {
            width: 100%;
            margin-top: 20px;
        }
        #movieDetails .play-button {
            display: inline-block;
            margin: 5px;
            padding: 8px 12px;
            font-size: 16px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            cursor: pointer;
        }
        #movieDetails .play-button:hover {
            background-color: #218838;
        }
        @media (max-width: 768px) {
            #searchForm input[type="text"] {
                width: 50%;
            }
            .movie-info {
                flex-direction: column;
            }
            .movie-info .details {
                width: 100%;
            }
            .movie-info .poster {
                width: 100%;
                text-align: center;
            }
            table th, table td {
                font-size: 14px;
            }
            #movieList button {
                font-size: 14px;
            }
        }
        @media (max-width: 480px) {
            #searchForm input[type="text"] {
                width: 50%;
            }
            .movie-info .details {
                font-size: 14px;
            }
            table th, table td {
                font-size: 12px;
            }
            #movieList button {
                font-size: 14px;
            }
        }
        hr {
            border: 0;
            height: 1px;
            background: #ddd;
            margin: 20px 0;
            position: relative;
        }
        hr::before {
            content: "";
            position: absolute;
            top: -5px;
            left: 50%;
            transform: translateX(-50%);
            width: 50px;
            height: 2px;
            background-color: #007bff;
            border-radius: 2px;
        }
        #announcementModal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 9999;
        }
        .modal-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            max-width: 600px;
            width: 80%;
            text-align: left;
        }
        .modal-content h2 {
            margin-bottom: 20px;
        }
        #closeButton {
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 20px;
            margin-left: auto;
            display: block;
        }
        #closeButton:hover {
            background-color: #0056b3;
        }
        .countdown {
            font-size: 18px;
            color: #fff;
            margin-left: 10px;
        }
        #LA-DATA-WIDGET {
            display: block;
            margin: 0 auto;
            text-align: center;
        }
    </style>
</head>
<body>
    <div id="announcementModal">
        <div class="modal-content">
            <h2>📢 免责声明</h2>
            <p>本站所有内容均来自互联网，本站不会保存、复制或传播任何视频文件，也不对本站上的任何内容负法律责任。如果本站部分内容侵犯您的版权请告知，在必要证明文件下我们第一时间撤除。</p>
            <p><font color="red"><b>请勿相信视频中的任何广告！</b></font></p>
            <p><b>开源地址：</b><a href="https://github.com/vpsaz/ysss">GitHub</a></p>
            <button id="closeButton" disabled>
                <span id="countdownText">5</span> 秒后可关闭
            </button>
        </div>
    </div>

    <div id="container">
        <h1>影视搜索</h1>

        <div id="searchForm">
            <form action="" method="get" style="display: flex; justify-content: center; align-items: center;">
                <input type="text" name="search" value="<?php echo isset($_GET['search'])? htmlspecialchars($_GET['search']) : '';?>" placeholder="请输入影片名称" style="flex-grow: 1; padding: 10px 10px; font-size: 16px; border: 1px solid #ccc; border-radius: 5px; margin-right: 10px;"/>
                
                <select id="sourceSelect" name="y" style="flex-grow: 1; padding: 10px 10px; font-size: 16px; border: 1px solid #ccc; border-radius: 5px; margin-right: 10px;background:white url('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAYAAADgdz34AAAACXBIWXMAAAsTAAALEwEAmpwYAAAAaklEQVR4nO2PQQqAMAwEpwff2otWwY/Ym48VFSFCkIBQAr1kIOSQZbaFIAi6MzhlTCbgkP2XGVsKFuACTqAY9yK3JzO3FCRgUyVakpV8lyxeJdlLrkuqKnnl1UNu/cTt5V8ScMq4y4OgIzfe6R6N01DwigAAAABJRU5ErkJggg==') no-repeat right 10px center;-webkit-appearance:none;-moz-appearance:none;appearance:none;cursor:pointer;padding-right:35px;">
                    <option value="1" <?php echo ($selected_source == '1')? 'selected' : '';?>>片源1</option>
                    <option value="2" <?php echo ($selected_source == '2')? 'selected' : '';?>>片源2</option>
                    <option value="3" <?php echo ($selected_source == '3')? 'selected' : '';?>>片源3</option>
                    <option value="4" <?php echo ($selected_source == '4')? 'selected' : '';?>>片源4</option>
                    <option value="5" <?php echo ($selected_source == '5')? 'selected' : '';?>>片源5</option>
                    <option value="6" <?php echo ($selected_source == '6')? 'selected' : '';?>>片源6</option>
                </select>

                <button type="submit" style="padding: 10px 15px; font-size: 16px; color: white; background-color: #007bff; border: none; border-radius: 5px; cursor: pointer; white-space: nowrap; text-align: center; vertical-align: middle; display: inline-flex; justify-content: center; align-items: center;">搜索</button>
            </form>
        </div>

        <?php if (!isset($_GET['movie_id']) && isset($search_results['list']) && count($search_results['list']) > 0):?>
            <div id="movieList">
                <h3>🔍 搜索结果</h3>
                <?php foreach ($search_results['list'] as $movie):?>
                    <form action="" method="get">
                        <input type="hidden" name="movie_id" value="<?php echo htmlspecialchars($movie['vod_id']);?>">
                        <input type="hidden" name="search" value="<?php echo htmlspecialchars($_GET['search']);?>">
                        <input type="hidden" name="y" value="<?php echo htmlspecialchars($selected_source);?>">
                        <button type="submit"><?php echo htmlspecialchars($movie['vod_name']). ' - '. htmlspecialchars($movie['vod_remarks']);?></button>
                    </form>
                <?php endforeach;?>
            </div>
        <?php elseif (isset($_GET['movie_id']) && $movie_details && isset($movie_details['name'])):?>
            <div id="movieDetails">
                <h3>🎬 影片详情</h3><hr>
                <div class="movie-info">
                    <div class="details">
                        <table>
                            <tr><th>导演</th><td><?php echo htmlspecialchars($movie_details['director']);?></td></tr>
                            <tr><th>类型</th><td><?php echo htmlspecialchars($movie_details['class']);?></td></tr>
                            <tr><th>日期</th><td><?php echo htmlspecialchars($movie_details['pubdate']);?></td></tr>
                            <tr><th>评分</th><td><?php echo htmlspecialchars($movie_details['douban_score']);?></td></tr>
                            <tr><th>地区</th><td><?php echo htmlspecialchars($movie_details['area']);?></td></tr>
                        </table>
                    </div>
                    <div class="poster">
                        <img src="<?php echo htmlspecialchars($movie_details['pic']);?>" alt="<?php echo htmlspecialchars($movie_details['name']);?>" style="max-width: 100%;">
                    </div>
                </div>

                <div class="content">
                    <h3>💬 影片简介</h3><hr><p><?php echo $movie_details['content'];?></p>
                </div><br>

                <h3>🔞 播放列表</h3><hr>
                <div>
                    <?php if (isset($movie_details['play_url']) && is_array($movie_details['play_url'])):?>
                        <?php foreach ($movie_details['play_url'] as $episode):?>
                            <a href="https://baiapi.cn/api/webbfq?&apiKey=f1423be3a0552383607175dd0b3eb4c3&url=<?php echo htmlspecialchars($episode['link']);?>" class="play-button" target="_blank"><!-- 将 f1423be3a0552383607175dd0b3eb4c3 更换为您自己的key即可 -->
                                <?php echo htmlspecialchars($episode['title']);?>
                            </a>
                        <?php endforeach;?>
                    <?php else:?>
                        <p>暂无播放列表。</p>
                    <?php endif;?>
                </div>
            </div>
        <?php endif;?>
    </div>

    <script>
        function shouldShowAnnouncement() {
            const lastShownDate = localStorage.getItem('lastShownDate');
            const today = new Date().toLocaleDateString();

            if (lastShownDate !== today) {
                return true;
            }
            return false;
        }

        function showAnnouncement() {
            const modal = document.getElementById('announcementModal');
            const closeButton = document.getElementById('closeButton');
            const countdownText = document.getElementById('countdownText');
            let countdown = 5;

            modal.style.display = 'block';

            const timer = setInterval(function() {
                countdown--;
                countdownText.textContent = countdown;

                if (countdown > 0) {
                    closeButton.disabled = true;
                    closeButton.textContent = `${countdown} 秒后可关闭`;
                }

                if (countdown <= 0) {
                    clearInterval(timer);
                    closeButton.disabled = false;
                    closeButton.textContent = '关闭公告';
                }
            }, 1000);

            closeButton.onclick = function() {
                localStorage.setItem('lastShownDate', new Date().toLocaleDateString());
                modal.style.display = 'none'; 
            };
        }

        window.onload = function() {
            if (shouldShowAnnouncement()) {
                showAnnouncement();
            }
        };
    </script>
</body>
</html>
