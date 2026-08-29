<?php
if (!function_exists('normalizer_is_normalized')) {
    function normalizer_is_normalized($input, $form = 4) { return true; }
    function normalizer_normalize($input, $form = 4) { return (string)$input; }
}
if (!class_exists('Normalizer')) {
    class Normalizer {
        const NONE = 1;
        const FORM_D = 2;
        const FORM_KD = 3;
        const FORM_C = 4;
        const FORM_KC = 5;
        const NFD = 2;
        const NFKD = 3;
        const NFC = 4;
        const NFKC = 5;
        public static function isNormalized($input, $form = self::FORM_C) { return true; }
        public static function normalize($input, $form = self::FORM_C) { return (string)$input; }
    }
}
