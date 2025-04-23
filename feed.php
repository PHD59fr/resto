<?php
header('Content-Type: application/rss+xml; charset=utf-8');

$baseDir = __DIR__ . '/restaurant/';
$scheme  = isset($_SERVER['HTTPS']) ? 'https' : 'http';
$host    = $_SERVER['HTTP_HOST'];

function clean($text) {
    return htmlspecialchars(trim($text), ENT_QUOTES, 'UTF-8');
}

function findCoverImage(string $folderName): ?string {
    $extensions = ['png', 'jpg', 'jpeg', 'webp', 'gif'];
    foreach ($extensions as $ext) {
        $coverPath = __DIR__ . "/restaurant/{$folderName}/cover." . $ext;
        if (file_exists($coverPath)) {
            return "restaurant/{$folderName}/cover." . $ext;
        }
    }
    return null;
}

$items = [];

if (is_dir($baseDir) && $dh = opendir($baseDir)) {
    while (($file = readdir($dh)) !== false) {
        $path = $baseDir . $file;
        if (!preg_match('/\.json$/', $file)) continue;

        $data = json_decode(file_get_contents($path), true);
        if ($data && isset($data['slug'], $data['name'], $data['description'], $data['visitDate'])) {
            $data['folder'] = basename($file, '.json');
            $items[] = $data;
        }
    }
    closedir($dh);
}

usort($items, function ($a, $b) {
    return strtotime($b['visitDate']) <=> strtotime($a['visitDate']);
});

echo '<?xml version="1.0" encoding="UTF-8" ?>';
?>
<rss version="2.0">
  <channel>
    <title>Critiques Gastronomiques de PH</title>
    <link><?= "$scheme://$host/" ?></link>
    <description>Avis sur les restaurants de la région</description>
    <language>fr</language>
<?php foreach (array_slice($items, 0, 10) as $item):
    $url = "$scheme://$host/" . urlencode($item['slug']);
    $desc = clean(strip_tags($item['description']));
    $img = $item['externalImageTitle'] ?? findCoverImage($item['folder']) ?? 'default-image.jpg';
    $imgUrl = strpos($img, 'http') === 0 ? $img : "$scheme://$host/$img";
?>
    <item>
      <title><?= clean($item['name']) ?></title>
      <link><?= $url ?></link>
      <guid><?= $url ?></guid>
      <description><![CDATA[<p><?= $desc ?></p><p><img src="<?= $imgUrl ?>" alt="" style="max-width:100%;"></p>]]></description>
      <pubDate><?= date(DATE_RSS, strtotime($item['visitDate'])) ?></pubDate>
    </item>
<?php endforeach; ?>
  </channel>
</rss>
