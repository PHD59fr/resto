<?php
// Redirect any /index.php request to the root
if (strpos($_SERVER['REQUEST_URI'], '/index.php') !== false) {
    header('Location: /', true, 301);
    exit();
}

// Base directory for restaurants
$baseDir = __DIR__ . '/restaurant/';
$restaurants = [];
$mode = 'list';
$current = null;

// French month names mapping
$monthNames = [
    '01' => 'Janvier', '02' => 'FÃ©vrier', '03' => 'Mars',
    '04' => 'Avril',   '05' => 'Mai',      '06' => 'Juin',
    '07' => 'Juillet', '08' => 'AoÃ»t',     '09' => 'Septembre',
    '10' => 'Octobre', '11' => 'Novembre', '12' => 'DÃ©cembre'
];

// Function to find the cover image with any extension
function findCoverImage(string $folderPath, string $relativeUrlPrefix = ''): ?string {
    $extensions = ['png', 'jpg', 'jpeg', 'webp', 'gif'];
    foreach ($extensions as $ext) {
        $coverPath = $folderPath . '/cover.' . $ext;
        if (file_exists($coverPath)) {
            return $relativeUrlPrefix . 'cover.' . $ext;
        }
    }
    return null;
}

if (is_dir($baseDir) && $dh = opendir($baseDir)) {
    while (($folder = readdir($dh)) !== false) {
        $path = $baseDir . $folder;
        if ($folder === '.' || $folder === '..' || !is_dir($path)) continue;
        $infoFile = $path . '/info.json';
        if (file_exists($infoFile)) {
            $data = json_decode(file_get_contents($infoFile), true);
            if ($data && isset($data['slug'])) {
                $data['folder'] = $folder;
                $restaurants[] = $data;
            }
        }
    }
    closedir($dh);
}

if (isset($_GET['restaurant'])) {
    foreach ($restaurants as $r) {
        if ($r['slug'] === $_GET['restaurant']) {
            $current = $r;
            $mode = 'single';
            break;
        }
    }
}

$itemsPerPage = 5;
$totalItems    = count($restaurants);
$totalPages    = max(1, ceil($totalItems / $itemsPerPage));
$page          = max(1, min($totalPages, ($_GET['page'] ?? 1)));
$toShow        = array_slice($restaurants, ($page-1)*$itemsPerPage, $itemsPerPage);

$scheme     = isset($_SERVER['HTTPS']) ? 'https' : 'http';
$currentUrl = "$scheme://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";

if ($mode === 'single') {
    $pageTitle = htmlspecialchars($current['name']) . " - Critique Gastronomique de PH";
    $pageDesc  = htmlspecialchars($current['description']);

    if (!empty($current['externalImageTitle'])) {
        $pageImage = $current['externalImageTitle'];
    } else {
        $cover     = findCoverImage($baseDir . $current['folder'], "restaurant/{$current['folder']}/");
        $pageImage = $cover
            ? "$scheme://{$_SERVER['HTTP_HOST']}/$cover"
            : "$scheme://{$_SERVER['HTTP_HOST']}/default-image.jpg";
    }
} else {
    $pageTitle = "Critiques Gastronomiques de PH";
    $pageDesc  = "Avis honnÃªtes et sans censure sur les restaurants de la rÃ©gion.";
    $pageImage = "$scheme://{$_SERVER['HTTP_HOST']}/default-image.jpg";
}

function cleanAndTruncateDescription($text, $maxLength = 250) {
    $text = preg_replace('/\s+/', ' ', $text);
    $text = trim($text);
    if (strlen($text) <= $maxLength) {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
    $text = substr($text, 0, $maxLength);
    $lastSpace = strrpos($text, ' ');
    if ($lastSpace !== false) {
        $text = substr($text, 0, $lastSpace);
    }
    return htmlspecialchars($text . '...', ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?= $pageTitle ?></title>
  <meta name="description" content="<?= cleanAndTruncateDescription($pageDesc) ?>">
  <link rel="canonical" href="<?= $currentUrl ?>">
  <meta property="og:title" content="<?= $pageTitle ?>">
  <meta property="og:description" content="<?= cleanAndTruncateDescription($pageDesc) ?>">
  <meta property="og:image" content="<?= $pageImage ?>">
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="<?= $pageTitle ?>">
  <meta name="twitter:description" content="<?= cleanAndTruncateDescription($pageDesc) ?>">
  <meta name="twitter:image" content="<?= $pageImage ?>">
  <style>
    /* Base styles */
    body { font-family: Arial, sans-serif; margin:0; padding:0; background:#fafafa; }
    header { background:#333; color:#fff; padding:1rem; text-align:center; }
    .container { max-width:1200px; margin:1rem auto; padding:0 0.5rem; }

    /* Review card */
    .review {
      background:#fff; border-radius:8px; padding:1rem; margin-bottom:1rem;
      box-shadow:0 2px 8px rgba(0,0,0,0.1);
      transition:box-shadow .3s, transform .3s;
    }
    .review:hover {
      box-shadow:0 6px 20px rgba(0,0,0,0.2);
      transform:translateY(-3px);
    }
    .review.single { display:block; }

    /* ---- LIST MODE: fixed cover size, full image visible, centered ---- */
    .review:not(.single) {
      display: flex;            /* horizontal layout */
      align-items: center;          /* center vertically */
      gap: 15px;                /* space between cover and content */
    }
    .review:not(.single) .image-wrapper {
      width: 200px;             /* fixed desktop width */
      height: 150px;            /* fixed desktop height */
      flex: 0 0 auto;           /* no flex grow/shrink */
      overflow: hidden;         /* hide overflow */
      border-radius: 8px;       /* rounded corners */
      background-color: #fff;   /* background behind image */
    }
    .review:not(.single) .list-cover {
      display: block;               /* remove inline spacing */
      width: 100%;                  /* fill wrapper width */
      height: 100%;                 /* fill wrapper height */
      object-fit: contain;          /* show full image */
      object-position: center center; /* center image in wrapper */
    }

    /* Ratings on one line */
    .ratings { margin-top:0.5rem; }
    .ratings span { display:block; margin:0.2rem 0; color:#f39c12; font-size:1rem; }

    /* Default image wrapper (single & gallery) */
    .image-wrapper { overflow:hidden; border-radius:8px; }
    .list-cover { transition: transform .3s; }
    .list-cover:hover { transform: none; }

    /* Single page cover */
    .cover {
      width:100%; height:auto; max-width:1200px; max-height:450px;
      object-fit:contain; border-radius:8px; margin-bottom:1rem;
      transition:transform .3s;
    }
    .cover:hover { transform:scale(1.02); }

    .review-content { flex:1; }
    .review-content h2 a {
      text-decoration:none; color:#333; transition:color .3s;
    }
    .review-content h2 a:hover { color:#f39c12; }

    .gallery { display:flex; flex-wrap:wrap; gap:10px; margin-top:1rem; justify-content:center; }
    .gallery img {
      width: 30%;
      height: 200px;
      object-fit: cover;
      margin: 5px;
      border-radius:8px;
      box-shadow:0 2px 5px rgba(0,0,0,0.1);
      cursor: pointer;
      transition: transform .3s;
    }
    .gallery img:hover { transform:scale(1.05); }

    .pagination {
      text-align:center; margin-top:2rem;
    }
    .pagination a {
      margin:0 5px; padding:8px 12px; background:#333; color:#fff;
      text-decoration:none; border-radius:4px; transition:background .3s;
    }
    .pagination a:hover { background:#f39c12; }
    .pagination a.disabled { background:#aaa; pointer-events:none; }

    .back-link {
      display:block; margin:2rem 0; text-align:center;
    }
    .back-link a {
      text-decoration:none; background:#333; color:#fff;
      padding:10px 15px; border-radius:4px; transition:background .3s;
    }
    .back-link a:hover { background:#f39c12; }

    /* ---- MOBILE OVERRIDES (<=600px) ---- */
    @media (max-width: 600px) {
      .review:not(.single) {
        flex-direction: column;  /* stack vertically */
        align-items: center;     /* center everything */
        text-align: center;
      }
      .review:not(.single) .image-wrapper {
        width: 150px;            /* mobile square */
        height: 150px;
        margin-bottom: 1rem;     /* space under image */
      }
      .review:not(.single) .list-cover {
        width: 100%;             /* fill wrapper */
        height: 100%;
        object-fit: contain;     /* show full image */
      }
      /* mobile ratings override */
      .review:not(.single) .ratings {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 0.5rem 1rem;
        margin-top: 1rem;
      }
      .gallery img {
        width: 100%;
        height: 200px;
        object-fit: cover;
      }
    }
  </style>
</head>
<body>
<header>
  <h1>Critiques Gastronomiques de PH</h1>
  <p>DÃ©couvrez des avis authentiques et sans censure&nbsp;!</p>
</header>
<div class="container">
<?php if ($mode==='single'): ?>
  <div class="review single">
    <?php
    // Use externalImageTitle if available
    $coverImage = !empty($current['externalImageTitle'])
      ? htmlspecialchars($current['externalImageTitle'])
      : (findCoverImage($baseDir . $current['folder'], "restaurant/{$current['folder']}/") ?? 'default-image.jpg');
    ?>
    <img class="cover" src="<?= $coverImage ?>" alt="<?= htmlspecialchars($current['name']) ?>">
    <h2><?= htmlspecialchars($current['name']) ?></h2>
    <?php if (!empty($current['website'])): ?>
      <p><strong>Site web :</strong> <a href="<?= htmlspecialchars($current['website']) ?>" target="_blank" rel="noopener"><?= htmlspecialchars(parse_url($current['website'], PHP_URL_HOST)) ?></a></p>
    <?php endif; ?>
    
    <?php if (!empty($current['address'])): ?>
      <p><strong>Adresse :</strong> <?= htmlspecialchars($current['address']) ?></p>
    <?php endif; ?>
    <p><strong>CatÃ©gorie :</strong> <?= htmlspecialchars($current['category'] ?? 'â€”') ?></p>
    <p><?= nl2br(htmlspecialchars($current['description'])) ?></p>
    <?php
    $dt  = DateTime::createFromFormat('Y-m-d', $current['visitDate'] ?? '');
    $fmt = $dt ? ($monthNames[$dt->format('m')] . ' ' . $dt->format('Y')) : 'â€”';
    ?>
    <p><em>VisitÃ© en <?= $fmt ?></em></p>
    <div class="ratings">
      <?php foreach (['cuisine','service','ambiance'] as $cat) {
        $s = (int)($current['ratings'][$cat] ?? 0);
      ?>
        <span><?= ucfirst($cat) ?>: <?= str_repeat('â˜…',$s) . str_repeat('â˜†',5-$s) ?></span>
      <?php } ?>
    </div>
    <?php
    $imgs = array_filter(
      scandir($baseDir . $current['folder']),
      fn($f) => preg_match('/\.(png|jpe?g|webp|gif)$/i',$f)
            && !preg_match('/^cover\.(png|jpe?g|webp|gif)$/i',$f)
    );
    $externalImgs = $current['externalImages'] ?? [];
    if ($imgs || $externalImgs):
    ?>
    <div class="gallery">
      <?php
      foreach ($imgs as $i) {
        echo '<img src="restaurant/' . htmlspecialchars($current['folder'].'/'.$i) . '" alt="">';
      }
      foreach ($externalImgs as $url) {
        echo '<img src="' . htmlspecialchars($url) . '" alt="">';
      }
      ?>
    </div>
    <?php endif; ?>
  </div>
  <div class="back-link"><a href="/">â† Retour</a></div>
<?php else: foreach ($toShow as $r):
  $dt = DateTime::createFromFormat('Y-m-d', $r['visitDate'] ?? '');
  $v  = $dt ? ($monthNames[$dt->format('m')] . ' ' . $dt->format('Y')) : 'â€”';
?>
  <div class="review">
    <?php
    $coverImage = !empty($r['externalImageTitle'])
      ? htmlspecialchars($r['externalImageTitle'])
      : (findCoverImage($baseDir . $r['folder'], "restaurant/{$r['folder']}/") ?? 'default-image.jpg');
    ?>
    <a title="<?= htmlspecialchars($r['name']) ?>" href="?restaurant=<?= urlencode($r['slug']) ?>">
      <div class="image-wrapper">
        <img class="list-cover" src="<?= $coverImage ?>" alt="<?= htmlspecialchars($r['name']) ?>">
      </div>
    </a>
    <div class="review-content">
      <h2><a title="<?= htmlspecialchars($r['name']) ?>" href="?restaurant=<?= urlencode($r['slug']) ?>"><?= htmlspecialchars($r['name']) ?></a></h2>
      <p><strong>CatÃ©gorie :</strong> <?= htmlspecialchars($r['category'] ?? 'â€”') ?></p>
      <p>VisitÃ© en <?= $v ?></p>
      <?php
      $shortDesc = mb_substr(strip_tags($r['description'] ?? ''), 0, 135);
      if (mb_strlen(strip_tags($r['description'] ?? '')) > 135) {
          $shortDesc .= 'â€¦';
      }
      ?>
      <p><i><?= htmlspecialchars($shortDesc) ?></i></p>
      <div class="ratings">
        <?php foreach (['cuisine','service','ambiance'] as $cat) {
          $s = (int)($r['ratings'][$cat] ?? 0);
        ?>
          <span><?= ucfirst($cat) ?>: <?= str_repeat('â˜…',$s) . str_repeat('â˜†',5-$s) ?></span>
        <?php } ?>
      </div>
    </div>
  </div>
<?php endforeach; ?>
  <div class="pagination">
    <?php if ($page>1): ?>
      <a href="?page=<?= $page-1 ?>">PrÃ©cÃ©dent</a>
    <?php else: ?>
      <a class="disabled">PrÃ©cÃ©dent</a>
    <?php endif; ?>
    <?php if ($page<$totalPages): ?>
      <a href="?page=<?= $page+1 ?>">Suivant</a>
    <?php else: ?>
      <a class="disabled">Suivant</a>
    <?php endif; ?>
  </div>
<?php endif; ?>
</div>

<div id="lightbox" style="position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.8);display:none;justify-content:center;align-items:center;z-index:1000;">
  <img src="" alt="AperÃ§u" style="max-width:90%;max-height:90%;border-radius:8px;">
</div>

<script>
const lb = document.getElementById('lightbox');
const li = lb?.querySelector('img');
const galleryImages = Array.from(document.querySelectorAll('.gallery img'));
let currentIndex = -1;

galleryImages.forEach((img, index) => {
  img.addEventListener('click', () => {
    if (lb && li) {
      li.src = img.src;
      lb.style.display = 'flex';
      currentIndex = index;
    }
  });
});

function showImage(index) {
  if (index >= 0 && index < galleryImages.length) {
    li.src = galleryImages[index].src;
    currentIndex = index;
  }
}

document.addEventListener('keydown', (e) => {
  if (lb.style.display === 'flex') {
    if (e.key === 'ArrowRight') showImage((currentIndex + 1) % galleryImages.length);
    if (e.key === 'ArrowLeft') showImage((currentIndex - 1 + galleryImages.length) % galleryImages.length);
    if (e.key === 'Escape') {
      lb.style.display = 'none';
      li.src = '';
    }
  }
});

lb?.addEventListener('click', () => {
  lb.style.display = 'none';
  li.src = '';
});
</script>
</body>
</html>

