<?php
// Përfshij të gjithë dosjet dhe skedarët nga direktoria 'Static_Full_Version'
$static_directory = __DIR__ . '/Static_Full_Version';

$files = scandir($static_directory);

foreach ($files as $file) {
    // Kontrollo nëse është skedar i vërtetë dhe jo direktori ose skedar i fshehur
    if (is_file($static_directory . '/' . $file)) {
        include $static_directory . '/' . $file;
    }
}
?>
