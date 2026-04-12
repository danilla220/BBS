<?php
/**
 * BBS Protect Studio - Главная страница
 * PHP 8.0+ | Без внешних зависимостей (кроме CDN skinview3d)
 */

// Конфигурация директорий
$dirs = [
    'movie'  => __DIR__ . '/movie/',
    'mods'   => __DIR__ . '/mods/',
    'models' => __DIR__ . '/models/',
    'skins'  => __DIR__ . '/skins/'
];

/**
 * Сканирует директорию, фильтрует файлы по расширениям, возвращает метаданные.
 * Источники методов:
 * - glob(): https://www.php.net/manual/ru/function.glob.php
 * - filemtime(): https://www.php.net/manual/ru/function.filemtime.php
 * - filesize(): https://www.php.net/manual/ru/function.filesize.php
 */
function scanDirectory(string $path, array $allowedExt): array {
    $files = [];
    if (!is_dir($path)) return $files;

    $pattern = $path . '*';
    $rawFiles = glob($pattern, GLOB_NOSORT);
    if ($rawFiles === false) return $files;

    foreach ($rawFiles as $filePath) {
        if (!is_file($filePath)) continue;
        
        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedExt, true)) continue;

        $files[] = [
            'name' => pathinfo($filePath, PATHINFO_FILENAME),
            'path' => str_replace(__DIR__ . '/', '', $filePath), // Относительный путь для ссылок
            'date' => date('d.m.Y', filemtime($filePath)),
            'size' => round(filesize($filePath) / 1024, 2) . ' КБ'
        ];
    }

    // Сортировка по имени (ASCII)
    usort($files, fn($a, $b) => strcmp($a['name'], $b['name']));
    return $files;
}

$movies  = scanDirectory($dirs['movie'],  ['mp4', 'mov', 'avi', 'mkv', 'webm']);
$mods    = scanDirectory($dirs['mods'],   ['jar', 'zip', 'rar']);
$models  = scanDirectory($dirs['models'], ['bbmodel', 'json', 'obj', 'gltf']);
$skins   = scanDirectory($dirs['skins'],  ['png']);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BBS Protect Studio</title>
    <style>
        :root {
            --bg: #0a0a0a;
            --card: #111111;
            --text: #e6e6e6;
            --text-muted: #888888;
            --accent: #00ff9d;
            --border: #222222;
            --hover: #1a1a1a;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            background-color: var(--bg);
            color: var(--text);
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
        }
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 2rem;
            border-bottom: 1px solid var(--border);
            background: var(--card);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .logo {
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--accent);
            display: flex;
            align-items: center;
            gap: 0.6rem;
            text-decoration: none;
        }
        .logo img { width: 36px; height: 36px; border-radius: 8px; object-fit: cover; }
        nav { display: flex; gap: 1.2rem; flex-wrap: wrap; }
        nav a {
            color: var(--text);
            text-decoration: none;
            font-size: 0.95rem;
            transition: color 0.2s;
        }
        nav a:hover { color: var(--accent); }
        main { max-width: 1200px; margin: 2rem auto; padding: 0 1.5rem; }
        section { margin-bottom: 3.5rem; }
        h2 {
            font-size: 1.6rem;
            border-left: 4px solid var(--accent);
            padding-left: 0.8rem;
            margin-bottom: 1.5rem;
        }
        .author-card {
            display: flex;
            gap: 1.5rem;
            background: var(--card);
            padding: 1.5rem;
            border-radius: 12px;
            border: 1px solid var(--border);
            align-items: center;
        }
        .author-card img {
            width: 110px; height: 110px; border-radius: 50%; object-fit: cover;
            border: 2px solid var(--accent);
        }
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 1rem;
        }
        .card {
            background: var(--card);
            padding: 1.2rem;
            border-radius: 10px;
            border: 1px solid var(--border);
            transition: transform 0.2s, border-color 0.2s;
        }
        .card:hover { transform: translateY(-3px); border-color: var(--accent); }
        .card h3 { font-size: 1.05rem; margin-bottom: 0.4rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .meta { color: var(--text-muted); font-size: 0.85rem; margin-bottom: 0.9rem; }
        .btn {
            display: inline-block;
            background: var(--accent);
            color: #000;
            padding: 0.45rem 1rem;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            transition: opacity 0.2s;
        }
        .btn:hover { opacity: 0.85; }
        .skin-canvas {
            width: 100%; height: 220px; background: #000;
            border-radius: 8px; margin-bottom: 0.6rem; overflow: hidden;
        }
        footer {
            text-align: center;
            padding: 2rem 1rem;
            border-top: 1px solid var(--border);
            color: var(--text-muted);
            font-size: 0.9rem;
            background: var(--card);
        }
        footer a { color: var(--accent); text-decoration: none; }
        @media (max-width: 700px) {
            .author-card { flex-direction: column; text-align: center; }
            header { flex-direction: column; gap: 1rem; }
        }
    </style>
</head>
<body>
    <header>
        <a href="#" class="logo">
            <img src="logo.png" alt="Логотип BBS Protect Studio">
            <span>BBS Protect Studio</span>
        </a>
        <nav>
            <a href="#projects">Проекты</a>
            <a href="#mods">Моды</a>
            <a href="#models">Модели</a>
            <a href="#skins">Скины</a>
            <a href="policy.html">Политика</a>
            <a href="https://t.me/ВАШ_КАНАЛ" target="_blank" rel="noopener">Telegram</a>
        </nav>
    </header>

    <main>
        <section id="author">
            <h2>Об авторе</h2>
            <div class="author-card">
                <img src="avatar.jpg" alt="Аватар автора">
                <div>
                    <h3>Даниил</h3>
                    <p>Разработчик и создатель контента. Занимаюсь моддингом, 3D-моделированием для Minecraft и публикацией образовательных материалов.</p>
                </div>
            </div>
        </section>

        <section id="projects">
            <h2>Проекты (Видео)</h2>
            <div class="grid">
                <?php if (empty($movies)): ?>
                    <p style="color: var(--text-muted);">Видеофайлы в папке <code>/movie</code> не найдены.</p>
                <?php else: foreach ($movies as $m): ?>
                    <div class="card">
                        <h3><?= htmlspecialchars($m['name'], ENT_QUOTES, 'UTF-8') ?></h3>
                        <p class="meta">📅 <?= $m['date'] ?> • 📦 <?= $m['size'] ?></p>
                        <a class="btn" href="<?= htmlspecialchars($m['path'], ENT_QUOTES, 'UTF-8') ?>" download>Скачать</a>
                    </div>
                <?php endforeach; endif; ?>
            </div>
        </section>

        <section id="mods">
            <h2>Рекомендуемые моды</h2>
            <div class="grid">
                <?php if (empty($mods)): ?>
                    <p style="color: var(--text-muted);">Файлы модов в папке <code>/mods</code> не найдены.</p>
                <?php else: foreach ($mods as $mod): ?>
                    <div class="card">
                        <h3><?= htmlspecialchars($mod['name'], ENT_QUOTES, 'UTF-8') ?></h3>
                        <p class="meta">📅 <?= $mod['date'] ?> • 📦 <?= $mod['size'] ?></p>
                        <a class="btn" href="<?= htmlspecialchars($mod['path'], ENT_QUOTES, 'UTF-8') ?>" download>Скачать мод</a>
                    </div>
                <?php endforeach; endif; ?>
            </div>
        </section>

        <section id="models">
            <h2>3D Модели (Blockbench)</h2>
            <div class="grid">
                <?php if (empty($models)): ?>
                    <p style="color: var(--text-muted);">Модели в папке <code>/models</code> не найдены.</p>
                <?php else: foreach ($models as $model): ?>
                    <div class="card">
                        <h3><?= htmlspecialchars($model['name'], ENT_QUOTES, 'UTF-8') ?></h3>
                        <p class="meta">📅 <?= $model['date'] ?> • 📦 <?= $model['size'] ?></p>
                        <a class="btn" href="<?= htmlspecialchars($model['path'], ENT_QUOTES, 'UTF-8') ?>" download>Скачать модель</a>
                    </div>
                <?php endforeach; endif; ?>
            </div>
        </section>

        <section id="skins">
            <h2>Скины Minecraft (3D-превью)</h2>
            <div class="grid">
                <?php if (empty($skins)): ?>
                    <p style="color: var(--text-muted);">Скины в папке <code>/skins</code> не найдены.</p>
                <?php else: foreach ($skins as $skin): ?>
                    <div class="card">
                        <div class="skin-canvas" id="skin-<?= htmlspecialchars($skin['name'], ENT_QUOTES, 'UTF-8') ?>"></div>
                        <h3><?= htmlspecialchars($skin['name'], ENT_QUOTES, 'UTF-8') ?></h3>
                        <p class="meta">📅 <?= $skin['date'] ?> • 📦 <?= $skin['size'] ?></p>
                        <a class="btn" href="<?= htmlspecialchars($skin['path'], ENT_QUOTES, 'UTF-8') ?>" download>Скачать скин</a>
                    </div>
                <?php endforeach; endif; ?>
            </div>
        </section>
    </main>

    <footer>
        <p>© 2026 BBS Protect, inc. Все права защищены.</p>
        <p><a href="policy.html">Политика конфиденциальности</a> | <a href="https://t.me/ВАШ_КАНАЛ" target="_blank" rel="noopener">Telegram канал</a></p>
    </footer>

    <!-- 3D Skin Viewer -->
    <script src="https://cdn.jsdelivr.net/npm/skinview3d@1.3.0/dist/skinview3d.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Передаём данные из PHP в JS
            const skinsData = <?= json_encode(array_map(fn($s) => ['id' => 'skin-' . $s['name'], 'url' => $s['path']], $skins), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

            skinsData.forEach(skin => {
                const container = document.getElementById(skin.id);
                if (!container) return;

                try {
                    new skinview3d.SkinViewer({
                        domElement: container,
                        skin: skin.url,
                        animation: new skinview3d.WalkingAnimation(),
                        width: container.clientWidth,
                        height: 220,
                        controls: { enableZoom: false, enablePan: false }
                    });
                } catch (e) {
                    console.error('Ошибка рендера скина:', skin.id, e);
                    container.innerHTML = '<p style="color:#888;padding:10px;">Предпросмотр недоступен</p>';
                }
            });
        });
    </script>
</body>
</html>
