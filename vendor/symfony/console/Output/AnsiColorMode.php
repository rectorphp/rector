<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix202410\Symfony\Component\Console\Output;

use RectorPrefix202410\Symfony\Component\Console\Exception\InvalidArgumentException;
class AnsiColorMode
{
    public const Ansi4 = 'ansi4';
    public const Ansi8 = 'ansi8';
    public const Ansi24 = 'ansi24';
    /**
     * Converts an RGB hexadecimal color to the corresponding Ansi code.
     */
    public function convertFromHexToAnsiColorCode(string $hexColor) : string
    {
        $hexColor = \str_replace('#', '', $hexColor);
        if (3 === \strlen($hexColor)) {
            $hexColor = $hexColor[0] . $hexColor[0] . $hexColor[1] . $hexColor[1] . $hexColor[2] . $hexColor[2];
        }
        if (6 !== \strlen($hexColor)) {
            throw new InvalidArgumentException(\sprintf('Invalid "#%s" color.', $hexColor));
        }
        $color = \hexdec($hexColor);
        $r = $color >> 16 & 255;
        $g = $color >> 8 & 255;
        $b = $color & 255;
        switch ($this) {
            case self::Ansi4:
                return (string) $this->convertFromRGB($r, $g, $b);
            case self::Ansi8:
                return '8;5;' . (string) $this->convertFromRGB($r, $g, $b);
            case self::Ansi24:
                return \sprintf('8;2;%d;%d;%d', $r, $g, $b);
        }
    }
    private function convertFromRGB(int $r, int $g, int $b) : int
    {
        switch ($this) {
            case self::Ansi4:
                return $this->degradeHexColorToAnsi4($r, $g, $b);
            case self::Ansi8:
                return $this->degradeHexColorToAnsi8($r, $g, $b);
            default:
                throw new InvalidArgumentException("RGB cannot be converted to {$this->name}.");
        }
    }
    private function degradeHexColorToAnsi4(int $r, int $g, int $b) : int
    {
        return \round($b / 255) << 2 | \round($g / 255) << 1 | \round($r / 255);
    }
    /**
     * Inspired from https://github.com/ajalt/colormath/blob/e464e0da1b014976736cf97250063248fc77b8e7/colormath/src/commonMain/kotlin/com/github/ajalt/colormath/model/Ansi256.kt code (MIT license).
     */
    private function degradeHexColorToAnsi8(int $r, int $g, int $b) : int
    {
        if ($r === $g && $g === $b) {
            if ($r < 8) {
                return 16;
            }
            if ($r > 248) {
                return 231;
            }
            return (int) \round(($r - 8) / 247 * 24) + 232;
        } else {
            return 16 + 36 * (int) \round($r / 255 * 5) + 6 * (int) \round($g / 255 * 5) + (int) \round($b / 255 * 5);
        }
    }
}
