<?php
header('Content-Type: application/xml');

$baseDir = __DIR__ . '/restaurant/';
$restaurants = [];

if (is_dir($baseDir) && $dh = opendir($baseDir)) {
    while (($file = readdir($dh)) !== false) {
        $path = $baseDir . $file;
        if (!preg_match('/\.json$/', $file)) {
            continue;
        }

        $json = file_get_contents($path);
        $data = json_decode($json, true);
        if ($data && isset($data['slug'])) {
            $restaurants[] = $data['slug'];
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
    <changefreq>daily</changefreq>
    <priority>1.0</priority>
  </url>

  <?php foreach ($restaurants as $slug): ?>
  <url>
    <loc>https://resto.deph.fr/<?= urlencode($slug) ?></loc>
    <changefreq>daily</changefreq>
    <priority>0.9</priority>
  </url>
  <?php endforeach; ?>

  <?php for ($page = 2; $page <= $totalPages; $page++): ?>
  <url>
    <loc>https://resto.deph.fr/?page=<?= $page ?></loc>
    <changefreq>daily</changefreq>
    <priority>0.6</priority>
  </url>
  <?php endfor; ?>

</urlset>

