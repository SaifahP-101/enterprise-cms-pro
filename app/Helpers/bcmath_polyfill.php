<?php

/**
 * Enterprise CMS - Pure PHP BCMath Polyfill
 * ใช้ทำงานแทน PHP BCMath Extension ในกรณีที่เซิร์ฟเวอร์ไม่ได้ติดตั้งไว้
 */

if (!function_exists('bcadd')) {
    function bcadd($left_operand, $right_operand, $scale = null) {
        $scale = $scale ?? 0;
        return number_format((float)$left_operand + (float)$right_operand, (int)$scale, '.', '');
    }
}

if (!function_exists('bcsub')) {
    function bcsub($left_operand, $right_operand, $scale = null) {
        $scale = $scale ?? 0;
        return number_format((float)$left_operand - (float)$right_operand, (int)$scale, '.', '');
    }
}

if (!function_exists('bcmul')) {
    function bcmul($left_operand, $right_operand, $scale = null) {
        $scale = $scale ?? 0;
        return number_format((float)$left_operand * (float)$right_operand, (int)$scale, '.', '');
    }
}

if (!function_exists('bcdiv')) {
    function bcdiv($left_operand, $right_operand, $scale = null) {
        $scale = $scale ?? 0;
        if ((float)$right_operand == 0.0) {
            trigger_error('bcdiv(): Division by zero', E_USER_WARNING);
            return null;
        }
        return number_format((float)$left_operand / (float)$right_operand, (int)$scale, '.', '');
    }
}

if (!function_exists('bccomp')) {
    function bccomp($left_operand, $right_operand, $scale = null) {
        $left = (float)$left_operand;
        $right = (float)$right_operand;
        if ($left == $right) return 0;
        return ($left > $right) ? 1 : -1;
    }
}

if (!function_exists('bcpow')) {
    function bcpow($base, $exponent, $scale = null) {
        $scale = $scale ?? 0;
        return number_format(pow((float)$base, (float)$exponent), (int)$scale, '.', '');
    }
}

if (!function_exists('bcmod')) {
    function bcmod($left_operand, $modulus, $scale = null) {
        return (string)((int)$left_operand % (int)$modulus);
    }
}

if (!function_exists('bcsqrt')) {
    function bcsqrt($operand, $scale = null) {
        $scale = $scale ?? 0;
        return number_format(sqrt((float)$operand), (int)$scale, '.', '');
    }
}