<?php

declare (strict_types=1);
namespace PHPStan\PhpDocParser\Parser;

use function array_reverse;
use function count;
use function implode;
use function intdiv;
use function intval;
use function ltrim;
use function str_replace;
use function str_split;
use function strlen;
use function strspn;
use function strtolower;
use function substr;
class IntegerLiteralNormalizer
{
    /**
     * Converts an integer literal in any base, such as 0x1F, 0b101, 0o17 or 017,
     * to a decimal number of any size, such as 31, so that (int) casts read it correctly.
     */
    public static function normalizeToDecimal(string $literal): string
    {
        $literal = str_replace('_', '', $literal);
        $sign = '';
        if ($literal !== '' && ($literal[0] === '-' || $literal[0] === '+')) {
            $sign = $literal[0] === '-' ? '-' : '';
            $literal = (string) substr($literal, 1);
        }
        $prefix = strtolower(substr($literal, 0, 2));
        if ($prefix === '0x') {
            $base = 16;
            $digits = (string) substr($literal, 2);
        } elseif ($prefix === '0b') {
            $base = 2;
            $digits = (string) substr($literal, 2);
        } elseif ($prefix === '0o') {
            $base = 8;
            $digits = (string) substr($literal, 2);
        } elseif ($literal !== '' && $literal[0] === '0' && strspn($literal, '01234567') === strlen($literal)) {
            $base = 8;
            $digits = $literal;
        } else {
            $base = 10;
            $digits = $literal;
        }
        if ($base === 10) {
            $decimal = ltrim($digits, '0');
        } else {
            // decimal digits, least significant first
            $decimalDigits = [0];
            foreach (str_split($digits) as $character) {
                $carry = intval($character, $base);
                for ($i = 0; $i < count($decimalDigits); $i++) {
                    $product = $decimalDigits[$i] * $base + $carry;
                    $decimalDigits[$i] = $product % 10;
                    $carry = intdiv($product, 10);
                }
                while ($carry > 0) {
                    $decimalDigits[] = $carry % 10;
                    $carry = intdiv($carry, 10);
                }
            }
            $decimal = ltrim(implode('', array_reverse($decimalDigits)), '0');
        }
        if ($decimal === '') {
            return '0';
        }
        return $sign . $decimal;
    }
}
