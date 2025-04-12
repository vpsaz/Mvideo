<?php
function getInitialTheme() {
    if (isset($_COOKIE['theme_preference'])) {
        return $_COOKIE['theme_preference'] === 'dark' ? 'dark' : 'light';
    }
    if (isset($_SERVER['HTTP_ACCEPT'])) {
        $accept = $_SERVER['HTTP_ACCEPT'];
        if (strpos($accept, 'prefers-color-scheme: dark') !== false) {
            return 'dark';
        }
    }
    return 'light';
}

$initialTheme = getInitialTheme();

$selected_source = isset($_GET['y']) ? $_GET['y'] : (isset($_COOKIE['selected_source']) ? $_COOKIE['selected_source'] : '1');
$search_query = isset($_GET['search']) ? urlencode($_GET['search']) : '';

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
<html lang="zh-CN" data-theme="<?php echo $initialTheme; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>影视搜索</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --bg-color: #f7f7f7;
            --text-color: #333;
            --container-bg: #fff;
            --button-bg: #007bff;
            --button-hover: #0056b3;
            --movie-list-bg: #f0f0f0;
            --movie-list-hover: #e9ecef;
            --table-header: #f2f2f2;
            --table-cell: #fafafa;
            --border-color: #ddd;
            --play-button: #007bff;
            --play-button-hover: #0056b3;
            --select-bg: white;
            --input-bg: white;
            --input-text: #333;
            --modal-bg: #fff;
            --modal-text: #333;
        }

        [data-theme="dark"] {
            --bg-color: #121212;
            --text-color: #e0e0e0;
            --container-bg: #1e1e1e;
            --button-bg: #1a73e8;
            --button-hover: #1765cc;
            --movie-list-bg: #2d2d2d;
            --movie-list-hover: #3d3d3d;
            --table-header: #2d2d2d;
            --table-cell: #252525;
            --border-color: #444;
            --play-button: #1a73e8;
            --play-button-hover: #1765cc;
            --select-bg: #2d2d2d;
            --input-bg: #2d2d2d;
            --input-text: #e0e0e0;
            --modal-bg: #2d2d2d;
            --modal-text: #e0e0e0;
        }

        html {
            transition: background-color 0.3s ease, color 0.3s ease;
        }
        
        body {
            transition: background-color 0.3s ease;
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            background-color: var(--bg-color);
            color: var(--text-color);
            margin: 0;
            padding: 0;
            background-image: url(), url(https://.../bj.svg);
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
            color: var(--text-color);
            transition: color 0.3s ease;
        }

        #searchForm {
            text-align: center;
            margin-bottom: 20px;
        }

        #searchForm input[type="text"] {
            width: 70%;
            padding: 8px;
            font-size: 16px;
            border: 1px solid var(--border-color);
            border-radius: 5px;
            background-color: var(--input-bg);
            color: var(--input-text);
        }

        #searchForm button {
            padding: 8px 15px;
            font-size: 16px;
            color: #fff;
            background-color: var(--button-bg);
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        #searchForm button:hover {
            background-color: var(--button-hover);
        }

        .select-wrapper {
            position: relative;
            flex-grow: 1;
            margin-right: 10px;
        }
        
        #sourceSelect {
            width: 100%;
            padding: 10px 35px 10px 10px;
            font-size: 16px;
            border: 1px solid var(--border-color);
            border-radius: 5px;
            background-color: var(--select-bg);
            color: var(--input-text);
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            cursor: pointer;
        }
        
        .select-arrow {
            position: absolute;
            top: 50%;
            right: 10px;
            transform: translateY(-50%);
            pointer-events: none;
            color: var(--input-text);
        }

        #movieList, #movieDetails {
            background-color: var(--container-bg);
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
            background-color: var(--movie-list-bg);
            border: 1px solid var(--border-color);
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            color: var(--text-color);
        }

        #movieList button:hover {
            background-color: var(--movie-list-hover);
        }

        #movieDetails img {
            width: 152px;
            height: 230px;
            margin-top: 3px;
            display: block;
            border-radius: 8px;
        }

        table {
            width: 100%;
            margin: 0px 0;
            border-collapse: collapse;
            border-radius: 8px;
            overflow: hidden;
        }

        table th, table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
            white-space: nowrap;
        }

        table th {
            background-color: var(--table-header);
            font-weight: bold;
        }

        table td {
            background-color: var(--table-cell);
        }

        #movieDetails .details {
            max-height: 300px;
            overflow-x: auto;
            overflow-y: auto;
            margin-top: 3px;
        }

        .movie-info {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .movie-info .details {
            width: 78%;
        }

        .movie-info .poster {
            width: 20%;
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
            background-color: var(--play-button);
            color: white;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        #movieDetails .play-button:hover {
            background-color: var(--play-button-hover);
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
            background: var(--border-color);
            margin: 20px 0;
            position: relative;
            transition: background-color 0.3s ease;
        }

        hr::before {
            content: "";
            position: absolute;
            top: -5px;
            left: 50%;
            transform: translateX(-50%);
            width: 50px;
            height: 2px;
            background-color: var(--button-bg);
            border-radius: 2px;
            transition: background-color 0.3s ease;
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
            background-color: var(--modal-bg);
            color: var(--modal-text);
            padding: 20px;
            border-radius: 10px;
            max-width: 600px;
            width: 80%;
            text-align: left;
        }

        .modal-content h2 {
            margin-bottom: 20px;
            transition: color 0.3s ease;
        }

        #closeButton {
            background-color: var(--button-bg);
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 20px;
            margin-left: auto;
            display: block;
            transition: background-color 0.3s ease;
        }

        #closeButton:hover {
            background-color: var(--button-hover);
        }

        .countdown {
            font-size: 18px;
            color: #fff;
            margin-left: 10px;
        }

        .theme-toggle {
            position: fixed;
            top: 20px;
            right: 20px;
            background: var(--button-bg);
            color: white;
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 1000;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            transition: background-color 0.3s ease;
            font-size: 16px;
        }

        #LA-DATA-WIDGET {
            display: block;
            margin: 0 auto;
            text-align: center;
        }
    </style>
</head>
<body>

    <button class="theme-toggle" id="themeToggle">
        <i class="<?php echo $initialTheme === 'dark' ? 'fas fa-sun' : 'fas fa-moon'; ?>"></i>
    </button>

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
                <input type="text" name="search" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>" placeholder="请输入影片名称" style="flex-grow: 1; padding: 10px 10px; font-size: 16px; border: 1px solid var(--border-color); border-radius: 5px; margin-right: 10px; background-color: var(--input-bg); color: var(--input-text);"/>
                
                <div class="select-wrapper">
                    <select id="sourceSelect" name="y">
                        <option value="1" <?php echo ($selected_source == '1') ? 'selected' : ''; ?>>片源1</option>
                        <option value="2" <?php echo ($selected_source == '2') ? 'selected' : ''; ?>>片源2</option>
                        <option value="3" <?php echo ($selected_source == '3') ? 'selected' : ''; ?>>片源3</option>
                        <option value="4" <?php echo ($selected_source == '4') ? 'selected' : ''; ?>>片源4</option>
                        <option value="5" <?php echo ($selected_source == '5') ? 'selected' : ''; ?>>片源5</option>
                        <option value="6" <?php echo ($selected_source == '6') ? 'selected' : ''; ?>>片源6</option>
                    </select>
                    <div class="select-arrow">
                        <i class="fas fa-angle-down"></i>
                    </div>
                </div>

                <button type="submit" style="padding: 10px 15px; font-size: 16px; color: white; background-color: var(--button-bg); border: none; border-radius: 5px; cursor: pointer; white-space: nowrap; text-align: center; vertical-align: middle; display: inline-flex; justify-content: center; align-items: center;">搜索</button>
            </form>
        </div>

        <?php if (!isset($_GET['movie_id']) && isset($search_results['list']) && count($search_results['list']) > 0): ?>
            <div id="movieList">
                <h3>🔍 搜索结果</h3>
                <?php foreach ($search_results['list'] as $movie): ?>
                    <form action="" method="get">
                        <input type="hidden" name="movie_id" value="<?php echo htmlspecialchars($movie['vod_id']); ?>">
                        <input type="hidden" name="search" value="<?php echo htmlspecialchars($_GET['search']); ?>">
                        <input type="hidden" name="y" value="<?php echo htmlspecialchars($selected_source); ?>">
                        <button type="submit"><?php echo htmlspecialchars($movie['vod_name']) . ' - ' . htmlspecialchars($movie['vod_remarks']); ?></button>
                    </form>
                <?php endforeach; ?>
            </div>
        <?php elseif (isset($_GET['movie_id']) && $movie_details && isset($movie_details['name'])): ?>
            <div id="movieDetails">
                <h3>🎬 影片详情</h3><hr>
                <div class="movie-info">
                    <div class="details">
                        <table>
                            <tr><th>导演</th><td><?php echo htmlspecialchars($movie_details['director']); ?></td></tr>
                            <tr><th>类型</th><td><?php echo htmlspecialchars($movie_details['class']); ?></td></tr>
                            <tr><th>日期</th><td><?php echo htmlspecialchars($movie_details['pubdate']); ?></td></tr>
                            <tr><th>评分</th><td><?php echo htmlspecialchars($movie_details['douban_score']); ?></td></tr>
                            <tr><th>地区</th><td><?php echo htmlspecialchars($movie_details['area']); ?></td></tr>
                        </table>
                    </div>
                    <div class="poster">
                        <img src="<?php echo htmlspecialchars($movie_details['pic']); ?>" alt="<?php echo htmlspecialchars($movie_details['name']); ?>" style="max-width: 100%;">
                    </div>
                </div>

                <div class="content">
                    <h3>💬 影片简介</h3><hr><p><?php echo $movie_details['content']; ?></p>
                </div><br>

                <h3>🔞 播放列表</h3><hr>
                <div>
                    <?php if (isset($movie_details['play_url']) && is_array($movie_details['play_url'])): ?>
                        <?php foreach ($movie_details['play_url'] as $episode): ?>
                            <a href="https://baiapi.cn/api/webbfq?&apiKey=b458d0b622fc7634bf24c5e6a956d352&url=<?php echo htmlspecialchars($episode['link']); ?>" class="play-button" target="_blank">
                                <?php echo htmlspecialchars($episode['title']); ?>
                            </a>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>暂无播放列表。</p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
        const themeToggle = document.getElementById('themeToggle');
        const htmlElement = document.documentElement;
        
        function toggleTheme() {
            const currentTheme = htmlElement.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            
            htmlElement.setAttribute('data-theme', newTheme);
            themeToggle.innerHTML = newTheme === 'dark' ? '<i class="fas fa-sun"></i>' : '<i class="fas fa-moon"></i>';
            
            document.cookie = `theme_preference=${newTheme}; path=/; max-age=${60*60*24*30}`;
        }
        
        themeToggle.addEventListener('click', toggleTheme);
        
        const colorSchemeQuery = window.matchMedia('(prefers-color-scheme: dark)');
        colorSchemeQuery.addEventListener('change', (e) => {
            if (!document.cookie.includes('theme_preference')) {
                const newTheme = e.matches ? 'dark' : 'light';
                htmlElement.setAttribute('data-theme', newTheme);
                themeToggle.innerHTML = newTheme === 'dark' ? '<i class="fas fa-sun"></i>' : '<i class="fas fa-moon"></i>';
            }
        });

        function shouldShowAnnouncement() {
            const lastShownDate = localStorage.getItem('lastShownDate');
            const today = new Date().toLocaleDateString();
            return lastShownDate !== today;
        }

        function showAnnouncement() {
            const modal = document.getElementById('announcementModal');
            const closeButton = document.getElementById('closeButton');
            const countdownText = document.getElementById('countdownText');
            let countdown = 5;

            modal.style.display = 'block';

            const timer = setInterval(() => {
                countdown--;
                countdownText.textContent = countdown;

                if (countdown > 0) {
                    closeButton.disabled = true;
                    closeButton.textContent = `${countdown} 秒后可关闭`;
                } else {
                    clearInterval(timer);
                    closeButton.disabled = false;
                    closeButton.textContent = '关闭公告';
                }
            }, 1000);

            closeButton.onclick = () => {
                localStorage.setItem('lastShownDate', new Date().toLocaleDateString());
                modal.style.display = 'none'; 
            };
        }

        document.addEventListener('DOMContentLoaded', () => {
            if (shouldShowAnnouncement()) {
                showAnnouncement();
            }
        });
    </script>
</body>
</html>
