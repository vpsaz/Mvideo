<?php
/**
 * @author    校长bloG <1213235865@qq.com>
 * @github    https://github.com/vpsaz/ysss
 */

session_start();

$config_file = __DIR__ . '/config.php';
$conf = include($config_file);
$conf = include('config.php');

if (isset($_POST['login'])) {
    if ($_POST['password'] === $conf['admin_password']) {
        $_SESSION['admin_logged_in'] = true;
    } else {
        $error = '密码错误，请重试。';
    }
}

if (isset($_POST['save']) && isset($_SESSION['admin_logged_in'])) {
    $conf['site_title']     = $_POST['site_title'];
    $conf['redirect_url']   = $_POST['redirect_url'];
    $conf['background_image_url']   = $_POST['background_image_url'];
    $conf['player_api_prefix']     = $_POST['player_api_prefix'];
    $conf['disclaimers']    = $_POST['disclaimers'];
    $conf['site_description']    = $_POST['site_description'];
    $conf['site_keywords']       = $_POST['site_keywords'];
    $conf['source_count']   = $_POST['source_count'];
    $conf['announcement']   = $_POST['announcement'];
    $conf['baiapi_key'] = $_POST['baiapi_key'];
    $conf['admin_password'] = $_POST['admin_password'];

    $export = "<?php\nreturn " . var_export($conf, true) . ";\n";
    file_put_contents($config_file, $export);
    $message = '配置已保存！';
}

if (!isset($_SESSION['admin_logged_in'])) {
?>
<!DOCTYPE html>
<html lang="zh-CN">

<head>
    <meta charset="UTF-8">
    <title>登录</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
        }

        .login-card {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.9);
            border-radius: 16px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.15);
        }

        .input-field {
            transition: all 0.3s ease;
            border: 2px solid #e2e8f0;
        }

        .input-field:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
        }

        .btn-primary {
            background: linear-gradient(to right, #3b82f6, #1d4ed8);
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(59, 130, 246, 0.3);
        }
    </style>
</head>

<body class="gradient-bg min-h-screen flex items-center justify-center p-4">
    <div class="login-card p-8 w-full max-w-md">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">欢迎回来</h1>
            <p class="text-gray-600">请输入密码登录管理系统</p>
        </div>

        <?php if (isset($error)): ?>
        <div class="mb-6 p-3 bg-red-50 text-red-600 rounded-lg text-sm flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor"> d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
            </svg>
            <?php echo htmlspecialchars($error); ?>
        </div>
        <?php endif; ?>

        <form method="post" class="space-y-6">
            <div>
                <div class="relative">
                    <input type="password" name="password" placeholder="请输入密码" class="input-field w-full px-5 py-3 rounded-lg text-gray-700 focus:outline-none">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 absolute right-3 top-3.5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                    </svg>
                </div>
            </div>

            <button type="submit" name="login" class="btn-primary w-full text-white font-semibold py-3 rounded-lg">登录系统</button>
        </form>
    </div>
</body>

</html>
<?php
    exit;
}
?>
<!DOCTYPE html>
<html lang="zh-CN">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?php echo $conf['site_title']; ?> - 后台
    </title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary: #3b82f6;
            --primary-light: #60a5fa;
            --dark: #1e293b;
            --light: #f8fafc;
        }

        body {
            background-color: #f1f5f9;
            color: var(--dark);
        }

        .card {
            background: white;
            border-radius: 0.5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        .form-input {
            transition: all 0.3s ease;
            border: 1px solid #e2e8f0;
        }

        .form-input:focus {
            border-color: var(--primary-light);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
        }

        .btn {
            transition: all 0.3s ease;
        }

        .btn-primary {
            background-color: var(--primary);
        }

        .btn-primary:hover {
            background-color: #2563eb;
            transform: translateY(-1px);
        }
    </style>
</head>

<body class="font-sans">
    <div class="min-h-screen flex flex-col">
        <main class="flex-1 p-6">
            <?php if (isset($message)): ?>
            <div class="mb-6 p-4 bg-green-50 text-green-700 rounded-lg border border-green-200 flex items-center">
                <i class="fas fa-check-circle mr-3 text-green-500"></i>
                <?php echo htmlspecialchars($message); ?>
            </div>
            <?php endif; ?>

            <div class="card p-6 mb-6">
                <h3 class="text-lg font-semibold mb-4 flex items-center"><i class="fas fa-cog mr-2 text-blue-500"></i>系统设置</h3>
                <form method="post" class="space-y-5">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">网站标题</label>
                            <input type="text" name="site_title" value="<?php echo htmlspecialchars($conf['site_title']); ?>" class="form-input w-full px-4 py-2 rounded-lg focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">网站描述</label>
                            <input type="text" name="site_description" value="<?php echo htmlspecialchars($conf['site_description']); ?>" class="form-input w-full px-4 py-2 rounded-lg focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">网站关键词</label>
                            <input type="text" name="site_keywords" value="<?php echo htmlspecialchars($conf['site_keywords']); ?>" class="form-input w-full px-4 py-2 rounded-lg focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">片源数量</label>
                            <input type="text" name="source_count" value="<?php echo htmlspecialchars($conf['source_count']); ?>" class="form-input w-full px-4 py-2 rounded-lg focus:outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">跳转地址</label>
                        <input type="text" name="redirect_url"  value="<?php echo htmlspecialchars($conf['redirect_url']); ?>" class="form-input w-full px-4 py-2 rounded-lg focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">搜索接口APIKEY (<font color="#FF0000">必填</font>)</label>
                        <div class="flex items-center">
                            <input type="text" name="baiapi_key" value="<?php echo htmlspecialchars($conf['baiapi_key']); ?>" class="form-input flex-1 px-4 py-2 rounded-lg focus:outline-none">
                            <a href="https://baiapi.cn/api_doc.php?id=5" target="_blank" class="ml-2 px-3 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm hover:bg-gray-200"><i class="fas fa-external-link-alt mr-1"></i>文档</a>
                        </div>
                    </div>
                    
                    <div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">播放接口</label>
                            <input type="text" name="player_api_prefix" value="<?php echo htmlspecialchars($conf['player_api_prefix']); ?>" class="form-input w-full px-4 py-2 rounded-lg focus:outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">背景图片</label>
                        <input type="text" name="background_image_url" value="<?php echo htmlspecialchars($conf['background_image_url']); ?>" class="form-input w-full px-4 py-2 rounded-lg focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">免责公告内容</label>
                        <input type="text" name="disclaimers" value="<?php echo htmlspecialchars($conf['disclaimers']); ?>" class="form-input w-full px-4 py-2 rounded-lg focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">网站页脚 (支持HTML)</label>
                        <textarea name="announcement" rows="6" class="form-input w-full px-4 py-2 rounded-lg focus:outline-none"><?php echo htmlspecialchars($conf['announcement']); ?></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">后台登录密码</label>
                        <div class="relative">
                            <input type="password" name="admin_password" value="<?php echo htmlspecialchars($conf['admin_password']); ?>" class="form-input w-full px-4 py-2 rounded-lg focus:outline-none">
                            <button type="button" class="absolute right-3 top-2.5 text-gray-400 hover:text-gray-600" onclick="togglePassword(this)"><i class="fas fa-eye"></i></button>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-gray-100">
                        <button type="submit" name="save" class="btn btn-primary text-white px-6 py-2.5 rounded-lg font-medium hover:shadow-md"><i class="fas fa-save mr-2"></i>保存设置</button>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script>
        function togglePassword(button) {
            const input = button.previousElementSibling;
            const icon = button.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }
    </script>
</body>
</html>
