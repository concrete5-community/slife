<?php

defined('C5_EXECUTE') or die('Access Denied.');

use Concrete\Core\Support\Facade\Package;

$installedHandles = Package::getInstalledHandles();
$installedSlifePackages = [];
foreach ($installedHandles as $handle) {
    if ($handle === 'slife') {
        continue;
    }

    if (stripos($handle, 'slife') !== false) {
        $installedSlifePackages[] = Package::getByHandle($handle);
    }
}

if (count($installedSlifePackages) === 0) {
    return;
}
?>

<div class="alert alert-danger" role="alert">
    <?php
    echo t("Make sure all Slife extensions are uninstalled first:");
    ?>

    <ul>
        <?php
        foreach ($installedSlifePackages as $pkg) {
            echo '<li>'.$pkg->getPackageName().'</li>';
        }
        ?>
    </ul>
</div>
