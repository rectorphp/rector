<?php

declare (strict_types=1);
/**
 * Copyright (c) 2018-2020 Andreas Möller
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/ergebnis/json-printer
 */
namespace RectorPrefix20211020\Ergebnis\Json\Printer;

final class Printer implements \RectorPrefix20211020\Ergebnis\Json\Printer\PrinterInterface
{
    /**
     * This code is adopted from composer/composer (originally licensed under MIT by Nils Adermann <naderman@naderman.de>
     * and Jordi Boggiano <j.boggiano@seld.be>), who adopted it from a blog post by Dave Perrett (originally licensed
     * under MIT by Dave Perrett <mail@recursive-design.com>).
     *
     * The primary objective of the adoption is
     *
     * - turn static method into an instance method
     * - allow to specify indent
     * - allow to specify new-line character sequence
     *
     * If you observe closely, the options for un-escaping unicode characters and slashes have been removed. Since this
     * package requires PHP 7, there is no need to implement this in user-land code.
     *
     * @see https://github.com/composer/composer/blob/1.6.0/src/Composer/Json/JsonFormatter.php#L25-L126
     * @see https://www.daveperrett.com/articles/2008/03/11/format-json-with-php/
     * @see http://php.net/manual/en/function.json-encode.php
     * @see http://php.net/manual/en/json.constants.php
     *
     * @param string $json
     * @param string $indent
     * @param string $newLine
     *
     * @throws \InvalidArgumentException
     *
     * @return string
     */
    public function print($json, $indent = '    ', $newLine = \PHP_EOL) : string
    {
        if (null === \json_decode($json) && \JSON_ERROR_NONE !== \json_last_error()) {
            throw new \InvalidArgumentException(\sprintf('"%s" is not valid JSON.', $json));
        }
        if (1 !== \preg_match('/^( +|\\t+)$/', $indent)) {
            throw new \InvalidArgumentException(\sprintf('"%s" is not a valid indent.', $indent));
        }
        if (1 !== \preg_match('/^(?>\\r\\n|\\n|\\r)$/', $newLine)) {
            throw new \InvalidArgumentException(\sprintf('"%s" is not a valid new-line character sequence.', $newLine));
        }
        $printed = '';
        $indentLevel = 0;
        $length = \mb_strlen($json);
        $withinStringLiteral = \false;
        $stringLiteral = '';
        $noEscape = \true;
        for ($i = 0; $i < $length; ++$i) {
            /**
             * Grab the next character in the string.
             */
            $character = \mb_substr($json, $i, 1);
            /**
             * Are we inside a quoted string literal?
             */
            if ('"' === $character && $noEscape) {
                $withinStringLiteral = !$withinStringLiteral;
            }
            /**
             * Collect characters if we are inside a quoted string literal.
             */
            if ($withinStringLiteral) {
                $stringLiteral .= $character;
                $noEscape = '\\' === $character ? !$noEscape : \true;
                continue;
            }
            /**
             * Process string literal if we are about to leave it.
             */
            if ('' !== $stringLiteral) {
                $printed .= $stringLiteral . $character;
                $stringLiteral = '';
                continue;
            }
            /**
             * Ignore whitespace outside of string literal.
             */
            if ('' === \trim($character)) {
                continue;
            }
            /**
             * Ensure space after ":" character.
             */
            if (':' === $character) {
                $printed .= ': ';
                continue;
            }
            /**
             * Output a new line after "," character and and indent the next line.
             */
            if (',' === $character) {
                $printed .= $character . $newLine . \str_repeat($indent, $indentLevel);
                continue;
            }
            /**
             * Output a new line after "{" and "[" and indent the next line.
             */
            if ('{' === $character || '[' === $character) {
                ++$indentLevel;
                $printed .= $character . $newLine . \str_repeat($indent, $indentLevel);
                continue;
            }
            /**
             * Output a new line after "}" and "]" and indent the next line.
             */
            if ('}' === $character || ']' === $character) {
                --$indentLevel;
                $trimmed = \rtrim($printed);
                $previousNonWhitespaceCharacter = \mb_substr($trimmed, -1);
                /**
                 * Collapse empty {} and [].
                 */
                if ('{' === $previousNonWhitespaceCharacter || '[' === $previousNonWhitespaceCharacter) {
                    $printed = $trimmed . $character;
                    continue;
                }
                $printed .= $newLine . \str_repeat($indent, $indentLevel);
            }
            $printed .= $character;
        }
        return $printed;
    }
}
