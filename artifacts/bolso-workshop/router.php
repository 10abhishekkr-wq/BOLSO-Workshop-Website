<?php
declare(strict_types=1);

$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

if ($requestPath === '/assets/images/work.jpg' || $requestPath === '/assets/images/work2.jpg') {
    header('Content-Type: image/svg+xml; charset=utf-8');
    $accent = $requestPath === '/assets/images/work.jpg' ? '#7c2639' : '#98ad86';
    $secondary = $requestPath === '/assets/images/work.jpg' ? '#d8a52a' : '#bd5c3e';
    echo '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 700 900"><defs><linearGradient id="g" x1="0" y1="0" x2="1" y2="1"><stop stop-color="' . $secondary . '"/><stop offset=".58" stop-color="' . $accent . '"/><stop offset="1" stop-color="#1f2d3d"/></linearGradient></defs><rect width="700" height="900" fill="url(#g)"/><circle cx="130" cy="150" r="84" fill="#f6f0e6" opacity=".13"/><circle cx="560" cy="700" r="120" fill="#d8a52a" opacity=".55"/><path d="M80 610 C180 410 340 520 420 275 S570 240 650 80" fill="none" stroke="#f6f0e6" stroke-width="20" opacity=".6"/><path d="M70 690 C260 560 340 760 640 490" fill="none" stroke="#d8a52a" stroke-width="9" opacity=".82"/><text x="350" y="470" fill="#f6f0e6" font-size="145" font-family="Georgia" font-style="italic" text-anchor="middle">B</text><text x="350" y="535" fill="#f6f0e6" font-size="16" font-family="Arial" letter-spacing="4" text-anchor="middle">BOLSO STUDIO</text></svg>';
    return true;
}

$file = __DIR__ . $requestPath;
if (is_file($file)) {
    return false;
}

return false;