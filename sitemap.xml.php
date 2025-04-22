<?php
header('Content-Type: application/xml');

$baseDir = __DIR__ . '/restaurant/';
$restaurants = [];

if (is_dir($baseDir) && $dh = opendir($baseDir)) {
    while (($folder = readdir($dh)) !== false) {
        $path = $baseDir . $folder;
        if ($folder === '.' || $folder === '..' || !is_dir($path)) {
            continue;
        }
        $infoFile = $path . '/info.json';
        if (file_exists($infoFile)) {
            $json = file_get_contents($infoFile);
            $data = json_decode($json, true);
            if ($data && isset($data['slug'])) {
                // Store slug for sitemap entries
                $restaurants[] = $data['slug'];
            }
        }
    }
    closedir($dh);
}

$itemsPerPage = 5;
$totalItems   = count($restaurants);
$totalPages   = max(1, ceil($totalItems / $itemsPerPage));

echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">

  <url>
    <loc>https://resto.deph.fr/</loc>
    <changefreq>weekly</changefreq>
    <priority>1.0</priority>
  </url>

  <?php foreach ($restaurants as $slug): ?>
  <url>
    <loc>https://resto.deph.fr/?restaurant=<?= urlencode($slug) ?></loc>
    <changefreq>monthly</changefreq>
    <priority>0.8</priority>
  </url>
  <?php endforeach; ?>

  <?php for ($page = 2; $page <= $totalPages; $page++): ?>
  <url>
    <loc>https://resto.deph.fr/?page=<?= $page ?></loc>
    <changefreq>weekly</changefreq>
    <priority>0.5</priority>
  </url>
  <?php endfor; ?>

</urlset>
