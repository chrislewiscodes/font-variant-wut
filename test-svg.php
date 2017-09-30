<?php
namespace FontVariantWut;

require_once("./FontVariantWut.class.php");

$tester = new FontVariantWut();

$svgtestsize = 24;
$totaltests = 0;
foreach ($tester->tests as $rule => $info) {
    $totaltests += count($info['values']) + 1; //initial
}

$totalwidth = 300;
$totalheight = $svgtestsize * $totaltests;

header("Content-type: image/svg+xml");
?>
<svg xmlns="http://www.w3.org/2000/svg" version="1.1" width="<?= $totalwidth ?>" height="<?= $totalheight ?>">
    <style>
        <?= $tester->atFontFace(true) ?>

        text {
            font-family: "Font Variant Test";
            font-size: <?= $svgtestsize ?>;
        }
        
        text.label {
            font-family: "Source Code Pro", sans-serif;
            font-size: 16;
            text-anchor: end;
        }
        
        .initial {
            fill: #CCF;
        }
    </style>

    <?php $i=0; foreach ($tester->tests as $rule => $info): ?>
        <rect class="initial" x="0" y="<?= $i*$svgtestsize ?>" width="<?= $totalwidth ?>" height="<?= $svgtestsize ?>"/> 
        <text x="10" y="<?= ($i+0.7) * $svgtestsize ?>" style="<?= $rule ?>:initial"><?= $tester->getTestString($info['features']) ?></text>
        <?php ++$i; foreach ($info['values'] as $value): ?>
        <text x="10" y="<?= ($i+0.7) * $svgtestsize ?>" style="<?= $rule ?>:<?= $value ?>"><?= $tester->getTestString($info['features']) ?></text>
        <?php ++$i; endforeach; ?>
    <?php endforeach; ?>

</svg>
