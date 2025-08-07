<?php
/**
 * @author    校长bloG <1213235865@qq.com>
 * @github    https://github.com/vpsaz/Mvideo
 */

$config_file = __DIR__ . '/config.php';
$conf = include($config_file);
$source_count = isset($conf['source_count']) ? intval($conf['source_count']) : 1;

function curl_get_contents($url, $timeout = 5) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MvideoBot/1.0)');
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

    $result = curl_exec($ch);
    curl_close($ch);

    return $result === false ? '' : $result;
}

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

function isBrowser() {
    $userAgent = $_SERVER['HTTP_USER_AGENT'];
    return preg_match('/(MSIE|Trident|Edge|Firefox|Chrome|Safari|Opera)/i', $userAgent);
}

if (!isBrowser()) {
    header('Location: ' . $conf['redirect_url']);
    exit();
}

$selected_source = isset($_GET['y']) ? $_GET['y'] : (isset($_COOKIE['selected_source']) ? $_COOKIE['selected_source'] : '1');
$search_query = isset($_GET['search']) ? urlencode($_GET['search']) : '';

$search_results = [];
$movie_details = null;

if (isset($_GET['movie_id']) && $_GET['movie_id'] !== '') {
    $details_url = "https://baiapi.cn/api/ysss/?y={$selected_source}&id=" . urlencode($_GET['movie_id']);
    $details_data = curl_get_contents($details_url);
    if ($details_data) {
        $movie_details = json_decode($details_data, true);
    }
} elseif ($search_query) {
    $search_url = "https://baiapi.cn/api/ysss/?y={$selected_source}&wd={$search_query}";
    $search_data = curl_get_contents($search_url);
    if ($search_data) {
        $search_results = json_decode($search_data, true);
    }
}

$no_results = false;
if ($search_query && isset($search_results['list']) && empty($search_results['list'])) {
    $no_results = true;
}
?>
<!DOCTYPE html>
<html lang="zh-CN" data-theme="<?php echo $initialTheme; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title><?php echo $conf['site_title']; ?></title>
    <meta name="description" content="<?php echo $conf['site_description']; ?>">
    <meta name="keywords" content="<?php echo $conf['site_keywords']; ?>">
    <link rel="shortcut icon" href="https://pic1.imgdb.cn/item/6812e03558cb8da5c8d5d3c3.png" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* CSS styles remain unchanged */
        :root {
            --bg-color: #f7f7f7;
            --text-color: #333;
            /* ... rest of the CSS ... */
        }
    </style>
</head>
<body>
    <button class="theme-toggle" id="playHistoryButton">
        <i class="fas fa-history"></i>
    </button>
    <button class="theme-toggle" id="themeToggle">
        <i class="<?php echo $initialTheme === 'dark' ? 'fas fa-sun' : 'fas fa-moon'; ?>"></i>
    </button>

    <div id="playHistoryModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.5); z-index: 9999;">
        <div class="modal-content">
            <h2>📜 播放记录</h2>
            <div id="playHistoryList" class="history-list-container"></div>
            <div class="history-btn-row">
                <button id="clearHistoryButton">清空</button>
                <button id="closeHistoryButton" class="close-modal-button">关闭</button>
            </div>
        </div>
    </div>

    <div id="announcementModal">
        <div class="modal-content">
            <h2>📢 免责声明</h2>
            <p><?php echo $conf['disclaimers']; ?></p>
            <p><font color="red"><b>请勿相信视频中的任何广告！</b></font></p>
            <button id="closeButton" disabled>
                <span id="countdownText">5</span> 秒后可关闭
            </button>
        </div>
    </div>

    <div id="container">
        <h1 class="main-title"><?php echo $conf['site_title']; ?></h1>

        <div id="searchForm">
            <form action="" method="get" style="display: flex; justify-content: center; align-items: center;">
                <input type="text" name="search" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>" placeholder="请输入影片名称" style="flex-grow: 1; padding: 10px 10px; font-size: 16px; border: 1px solid var(--border-color); border-radius: 5px; margin-right: 10px; background-color: var(--input-bg); color: var(--input-text); height: 40px; box-sizing: border-box;"/>
                <div class="select-wrapper">
                    <select id="sourceSelect" name="y" style="height: 40px; box-sizing: border-box;">
                        <?php for ($i = 1; $i <= $source_count; $i++): ?>
                            <option value="<?php echo $i; ?>" <?php echo ($selected_source == (string)$i) ? 'selected' : ''; ?>>片源<?php echo $i; ?></option>
                        <?php endfor; ?>
                    </select>
                    <div class="select-arrow">
                        <i class="fas fa-angle-down"></i>
                    </div>
                </div>
                <button type="submit" style="padding: 10px 15px; font-size: 16px; color: white; background-color: var(--button-bg); border: none; border-radius: 5px; cursor: pointer; white-space: nowrap; text-align: center; vertical-align: middle; display: inline-flex; justify-content: center; align-items: center; height: 40px; box-sizing: border-box;">搜索</button>
            </form>
        </div>

        <?php if (!isset($_GET['movie_id']) && isset($search_results['list']) && count($search_results['list']) > 0): ?>
            <div class="recommendation-container" id="movieList">
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
            <div class="recommendation-container" id="movieDetails">
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
                </div><p></p>

                <div class="content">
                    <br><h3>💬 影片简介</h3><hr><p><?php echo $movie_details['content']; ?></p>
                </div><br>

                <h3>🔞 播放列表</h3><hr>
                <div class="play-button-container">
                    <?php if (isset($movie_details['play_url']) && is_array($movie_details['play_url'])): ?>
                        <?php foreach ($movie_details['play_url'] as $episode): ?>
                            <a href="<?php echo $conf['player_api_prefix']; ?><?php echo htmlspecialchars($episode['link']); ?>&title=<?php echo htmlspecialchars($movie_details['name']); ?> - <?php echo htmlspecialchars($episode['title']); ?>" class="play-button" target="_blank" title="<?php echo htmlspecialchars($episode['title']); ?>"><?php echo htmlspecialchars($episode['title']); ?></a>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>暂无播放列表。</p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
        <?php echo $conf['announcement']; ?>
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

            const playHistoryButton = document.getElementById('playHistoryButton');
            const playHistoryModal = document.getElementById('playHistoryModal');
            const closeHistoryButton = document.getElementById('closeHistoryButton');
            const clearHistoryButton = document.getElementById('clearHistoryButton');

            playHistoryButton.addEventListener('click', () => {
                playHistoryModal.style.display = 'block';
                loadPlayHistory();
            });

            closeHistoryButton.addEventListener('click', () => {
                playHistoryModal.style.display = 'none';
            });

            clearHistoryButton.addEventListener('click', () => {
                if (confirm('确定要清空播放记录吗？')) {
                    localStorage.removeItem('playHistory');
                    loadPlayHistory();
                }
            });

            const playButtons = document.querySelectorAll('.play-button');
            playButtons.forEach(button => {
                button.addEventListener('click', () => {
                    const timeStr = new Date().toLocaleString();
                    const movieName = `<span class="history-time">${timeStr}</span> <?php echo isset($movie_details['name']) ? htmlspecialchars($movie_details['name']) : ''; ?>`;
                    const episodeTitle = button.title;
                    recordPlayHistory(movieName, episodeTitle);
                });
            });
        });

        function loadPlayHistory() {
            const playHistoryList = document.getElementById('playHistoryList');
            const history = JSON.parse(localStorage.getItem('playHistory')) || [];

            if (history.length === 0) {
                playHistoryList.innerHTML = '<p class="no-history">暂无播放记录</p>';
            } else {
                playHistoryList.innerHTML = history.map(item => `<p>${item}</p>`).join('');
            }
        }

        function recordPlayHistory(movieName, episodeTitle) {
            const history = JSON.parse(localStorage.getItem('playHistory')) || [];
            const record = `${movieName} - ${episodeTitle}`;
            const idx = history.indexOf(record);
            if (idx !== -1) history.splice(idx, 1);
            history.unshift(record);
            localStorage.setItem('playHistory', JSON.stringify(history));
            loadPlayHistory();
        }
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            <?php if ($no_results): ?>
                alert("没有找到相关影片");
            <?php endif; ?>
        });
    </script>
</body>
</html>
