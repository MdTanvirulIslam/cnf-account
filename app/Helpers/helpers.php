<?php

if (!function_exists('convertToTakaWords')) {
    function convertToTakaWords($amount) {
        $ones = [
            0 => "zero",
            1 => "one",
            2 => "two",
            3 => "three",
            4 => "four",
            5 => "five",
            6 => "six",
            7 => "seven",
            8 => "eight",
            9 => "nine",
            10 => "ten",
            11 => "eleven",
            12 => "twelve",
            13 => "thirteen",
            14 => "fourteen",
            15 => "fifteen",
            16 => "sixteen",
            17 => "seventeen",
            18 => "eighteen",
            19 => "nineteen"
        ];

        $tens = [
            2 => "twenty",
            3 => "thirty",
            4 => "forty",
            5 => "fifty",
            6 => "sixty",
            7 => "seventy",
            8 => "eighty",
            9 => "ninety"
        ];

        $scales = ["", "thousand", "lakh", "crore"];

        if ($amount == 0) {
            return "Zero Taka Only";
        }

        $amount = number_format($amount, 2, '.', '');
        $parts = explode('.', $amount);

        $taka  = (int) $parts[0];
        $paisa = isset($parts[1]) ? (int) $parts[1] : 0;

        // Function to convert up to 999
        $convert_hundred = function($num) use ($ones, $tens) {
            $out = "";
            if ($num > 99) {
                $out .= $ones[(int)($num / 100)] . " hundred";
                $num = $num % 100;
                if ($num) $out .= " and ";
            }
            if ($num > 19) {
                $out .= $tens[(int)($num / 10)];
                $num = $num % 10;
                if ($num) $out .= "-" . $ones[$num];
            } elseif ($num > 0) {
                $out .= $ones[$num];
            }
            return $out;
        };

        // Convert taka part
        $words = [];
        $position = 0;
        while ($taka > 0) {
            if ($position == 0) {
                $chunk = $taka % 1000;
                $taka = (int)($taka / 1000);
            } else {
                $chunk = $taka % 100;
                $taka = (int)($taka / 100);
            }
            if ($chunk) {
                $words[] = $convert_hundred($chunk) . " " . $scales[$position];
            }
            $position++;
        }

        $takaWords = implode(" ", array_reverse($words));
        $takaWords = trim($takaWords) . " Taka";

        if ($paisa > 0) {
            $takaWords .= " and " . $convert_hundred($paisa) . " Paisa";
        }

        return $takaWords . " Only";
    }
}
