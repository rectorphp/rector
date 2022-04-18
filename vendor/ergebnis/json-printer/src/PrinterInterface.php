<?php

declare (strict_types=1);
/**
 * Copyright (c) 2018-2021 Andreas Möller
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/ergebnis/json-printer
 */
namespace RectorPrefix20220418\Ergebnis\Json\Printer;

interface PrinterInterface
{
    /**
     * @throws \InvalidArgumentException
     */
    public function print(string $json, string $indent = '    ', string $newLine = \PHP_EOL) : string;
}
