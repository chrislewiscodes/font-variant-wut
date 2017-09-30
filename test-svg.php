<?php
namespace FontVariantWut;

require_once("./FontVariantWut.class.php");

$tester = new FontVariantWut();

$tester->useBlockFont();

$square = isset($_GET['px']) && is_numeric($_GET['px']) ? (int)$_GET['px'] : 10;

$totaltests = 0;
$cols = 0;
foreach ($tester->tests as $rule => $info) {
    $totaltests += count($info['values']) + 1; //initial
    $cols = max($cols, count($tester->featuresForRule($rule)));
}

$totalwidth = $square * ($cols + 2);
$totalheight = $square * $totaltests;

header("Content-type: image/svg+xml");
?>
<svg xmlns="http://www.w3.org/2000/svg" version="1.1" width="<?= $totalwidth ?>" height="<?= $totalheight ?>">
    <style>
        <?= $tester->atFontFace(true) ?>

        <?php $i=0; foreach ($tester->tests as $rule => $info): ?>
        .<?= $rule ?> rect.initial {
            stroke: <?= $tester->ruleColor($rule) ?>;
            fill: none;
        }
        
        .<?= $rule ?> text.label {
            fill: <?= $tester->ruleColor($rule) ?>;
            stroke: none;
        }
        <?php endforeach; ?>

        text {
            font-family: "Font Variant Test";
            font-size: <?= $square ?>px;
        }
        
        text.label {
            font-family: sans-serif;
            font-size: <?= $square * 0.8 ?>px;
            text-anchor: end;
            transform: translateX(-<?= $square*0.2 ?>px);
        }
    </style>

    <?php $i=0; foreach ($tester->tests as $rule => $info): ?>
        <g class="<?= $rule ?>">
            <rect class="initial <?= $rule ?>" x="0.5" y="<?= $i * $square+0.5 ?>" width="<?= $totalwidth-1 ?>" height="<?= $square-1 ?>"/> 
            <text class="label <?= $rule ?>" x="<?= $totalwidth ?>" y="<?= ($i+0.75) * $square ?>"><?= str_replace('font-variant-', '', $rule) ?></text>
            <text x="<?= $square ?>" y="<?= ($i+1) * $square ?>" style="<?= $rule ?>:initial"><?= $tester->getTestString($rule) ?></text>
            <?php ++$i; foreach ($info['values'] as $value): ?>
            <text x="<?= $square ?>" y="<?= ($i+1) * $square ?>" style="<?= $rule ?>:<?= $value ?>"><?= $tester->getTestString($rule) ?></text>
            <?php ++$i; endforeach; ?>
        </g>
    <?php endforeach; ?>

</svg>
