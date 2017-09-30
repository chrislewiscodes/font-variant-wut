<?php
namespace FontVariantWut;

class FontVariantWut {
    public $allfeatures;

    public $tests = array(
        "font-variant-alternates" => array(
            'values' => array('normal', 'historical-forms', 'stylistic(1)', 'styleset(1)', 'character-variant(1)', 'swash(1)', 'ornaments(1)', 'annotation(1)'),
            'features' => '.alt|ss\d\d|cv\d\d|swsh|cswh|hist|ornm',
        ),
        "font-variant-caps" => array(
            'values' => array('normal', 'small-caps', 'all-small-caps', 'petite-caps', 'all-petite-caps', 'unicase', 'titling-caps'),
            'features' => 'c2pc|c2sc|pcap|smcp|unic|titl',
        ),
        "font-variant-ligatures" => array(
            'values' => array('none', 'common-ligatures', 'discretionary-ligatures', 'historical-ligatures', 'contextual'),
            'features' => '.lig|liga',
        ),
        "font-variant-numeric" => array(
            'values' => array('normal', 'lining-nums', 'oldstyle-nums', 'proportional-nums', 'tabular-nums', 'diagonal-fractions', 'stacked-fractions', 'ordinal', 'slashed-zero'),
            'features' => '.num|frac|afrc|zero|ordn',
        ),
        "font-variant-position" => array(
            'values' => array('normal', 'sub', 'super'),
            'features' => 'subs|sups',
        ),
    );
    
    public function __construct() {
        preg_match_all('/\bfeature\s+(\w{4})\s*\{/', file_get_contents(dirname(__FILE__) . '/fonts/OTTestFont-Regular.ufo/features.fea'), $m, PREG_PATTERN_ORDER);
        $this->allfeatures = $m[1];
        sort($this->allfeatures);
    }

    public function atFontFace($base64=false) {
        $url = "fonts/test.woff";
        if ($base64) {
            $url = "data:application/font-woff;base64," . base64_encode(file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/' . $url));
        }
        return <<<EOF
        @font-face {
            src: url("$url");
            font-family: "Font Variant Test";
            font-weight: normal;
            font-style: normal;
        }
EOF;
    }

    public function getTestString($features) {
        if (is_array($features)) {
            return implode("\n", $features);
        } else {
            $result = array();
            foreach ($this->allfeatures as $f) {
                if (preg_match('/' . $features . '/', $f)) {
                    $result[] = $f;
                }
            }
            return implode("\n", $result);
        }
    }
    
    public function getBrowser() {
        if (preg_match('/MSIE (\d+(?:\.\d+)?)/', $_SERVER['HTTP_USER_AGENT'], $m)) {
            return "Internet Explorer {$m[1]}";
        }
        if (preg_match('~(Firefox|Chrome|Chromium|Safari|OPR|Opera)/(\d+(?:\.\d+))~', $_SERVER['HTTP_USER_AGENT'], $m)) {
            if ($m[1] === 'OPR') {
                $m[1] = 'Opera';
            }
            return "{$m[1]} {$m[2]}";
        }
        return "Unknown";
    }
}
