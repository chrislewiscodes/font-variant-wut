<?php
namespace FontVariantWut;

/*
    Browser font-variant support tester by Chris Lewis https://chrislewis.codes/
    Brilliant test font by David Jonathan Ross https://djr.com/
    
    Licensed under the MIT License. Code available on GitHub:
    https://github.com/chrissam42/font-variant-wut
*/

class FontVariantWut {
    public $allfeatures;
    private $whichfont = "tags";

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
        $url = "fonts/{$this->whichfont}.woff";
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

    public function useBlockFont() {
        $this->whichfont = "block";
    }
    
    public function useTagFont() {
        $this->whichfont = "tags";
    }

    public function ruleColor($rule, $format='hex') {
        $hex = substr(md5($rule), 0, 6);
        switch ($format) {
            case 'rgb':
                return array(
                    hexdec(substr($hex, 0, 2)),
                    hexdec(substr($hex, 2, 2)),
                    hexdec(substr($hex, 4, 2)),
                );
            default:
                return "#$hex";
        }
    }

    public function featuresForRule($rule) {
        $test = $this->tests[$rule]['features'];
        if (is_array($test)) {
            return $test;
        } else {
            $result = array();
            foreach ($this->allfeatures as $f) {
                if (preg_match('/' . $test . '/', $f)) {
                    $result[] = $f;
                }
            }
            return $result;
        }
    }

    public function testString($rule) {
        $result = $this->featuresForRule($rule);
        if ($this->whichfont === "block") {
            return implode("", $result);
        } else {
            return "<span>" . implode("</span><span>", $result) . "<span>";
        }
    }
    
    public function browser() {
        $ua = $_SERVER['HTTP_USER_AGENT'];
        
        $b = "Unknown";
        $p = "Unknown";
        $v = "";
        if (preg_match('/MSIE (\d+(?:\.\d+)?)/', $ua, $m)) {
            $p = "Windows";
            $b = "Internet Explorer";
            $v = $m[1];
        } else if (preg_match(':Trident/\d+:', $ua) and preg_match('/v:(\d+(?:\.\d+))/', $ua, $m)) {
            $p = "Windows";
            $b = "Internet Explorer";
            $v = $m[1];
        } else {
            foreach (explode('|', 'CriOS|Edge|Firefox|Chrome|Chromium|Safari|OPR|Opera') as $try) {
                if (preg_match('~' . $try . '/(\d+(?:\.\d+))~', $ua, $m)) {
                    $b = $try;
                    $v = $m[1];
                    switch ($b) {
                        case 'OPR': $b = 'Opera'; break;
                        case 'CriOS': $p = "iOS"; $b = 'Chrome'; break;
                        case 'Safari':
                    }
                    break;
                }
            }
        }

        if (preg_match(':Version/([\d\.]+):', $ua, $m)) {
            $v = $m[1];
        }
        
        if ($p === "Unknown") {
            if (preg_match('/Android/', $ua)) {
                $p = 'Android';
            } else if (preg_match('/Windows NT/', $ua)) {
                $p = 'Windows';
            } else if (preg_match('/iPhone OS \d+/', $ua)) {
                $p = 'iOS';
            } else if (preg_match('/Mac OS X/', $ua)) {
                $p = 'Mac';
            }
        }
        
        return array(
            'platform' => $p, 
            'browser' => $b, 
            'version' => $v,
            'userAgent' => $_SERVER['HTTP_USER_AGENT'],
        );
    }
}
