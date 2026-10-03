<?php

declare (strict_types=1);
namespace RectorPrefix202610\Entropy\Reflection;

use RectorPrefix202610\Entropy\Attribute\RelatedTest;
use RectorPrefix202610\Entropy\Tests\Reflection\ClassNameResolver\ClassNameResolverTest;
final class ClassNameResolver
{
    /**
     * Return the single class-like FQN declared in a PHP file, or null when the file
     * declares none or more than one.
     *
     * @return class-string|null
     */
    public static function resolveFromFilePath(string $filePath): ?string
    {
        $fqns = self::resolveNamesFromFilePath($filePath);
        if (count($fqns) === 1) {
            return $fqns[0];
        }
        return null;
    }
    /**
     * Return all class-like FQNs (class, interface, trait, enum) declared in a PHP file,
     * without autoloading or PHP-Parser. Uses token_get_all().
     *
     * @api
     * @return class-string[]
     */
    public static function resolveNamesFromFilePath(string $filePath): array
    {
        if (!is_file($filePath)) {
            return [];
        }
        $code = file_get_contents($filePath);
        if ($code === \false) {
            return [];
        }
        $tokens = token_get_all($code);
        $namespace = '';
        $fqns = [];
        $count = count($tokens);
        for ($i = 0; $i < $count; ++$i) {
            $t = $tokens[$i];
            if (!is_array($t)) {
                continue;
            }
            // Handle "namespace Foo\Bar;" and "namespace Foo\Bar {"
            if ($t[0] === \T_NAMESPACE) {
                $namespace = '';
                for ($j = $i + 1; $j < $count; ++$j) {
                    $tj = $tokens[$j];
                    if ($tj === ';' || $tj === '{') {
                        break;
                    }
                    if (!is_array($tj)) {
                        continue;
                    }
                    // PHP 8+: T_NAME_QUALIFIED can appear; PHP 7: it's T_STRING + T_NS_SEPARATOR
                    if ($tj[0] === \T_STRING || $tj[0] === \T_NS_SEPARATOR || defined('T_NAME_QUALIFIED') && $tj[0] === \T_NAME_QUALIFIED) {
                        $namespace .= $tj[1];
                    }
                }
                $namespace = trim($namespace, "\\ \t\n\r\x00\v");
                continue;
            }
            // Detect class-likes (class/interface/trait/enum)
            $isEnum = defined('T_ENUM') && $t[0] === \T_ENUM;
            if (!in_array($t[0], [\T_CLASS, \T_INTERFACE, \T_TRAIT], \true) && !$isEnum) {
                continue;
            }
            $prev = self::previousNonWhitespaceToken($tokens, $i);
            // Skip "::class" constant
            if (is_array($prev) && $prev[0] === \T_DOUBLE_COLON) {
                continue;
            }
            // Skip anonymous classes: "new class (...)"
            if (is_array($prev) && $prev[0] === \T_NEW) {
                continue;
            }
            // Next T_STRING is the getName (skip whitespace, attributes/comments, etc.)
            $name = null;
            for ($j = $i + 1; $j < $count; ++$j) {
                $tj = $tokens[$j];
                if (!is_array($tj)) {
                    continue;
                }
                if ($tj[0] === \T_STRING) {
                    $name = $tj[1];
                    break;
                }
                // If we hit "{" or "(" before getName, something is off (e.g. anonymous or invalid)
                if ($tj[0] === ord('{') || $tj[0] === ord('(')) {
                    break;
                }
            }
            if ($name === null) {
                continue;
            }
            $fqns[] = $namespace !== '' ? $namespace . '\\' . $name : $name;
        }
        // De-duplicate while preserving order (in case of weird tokenization)
        /** @var class-string[] $uniqueFqns */
        $uniqueFqns = array_values(array_unique($fqns));
        return $uniqueFqns;
    }
    /**
     * @param array<int, mixed> $tokens
     * @return array{0:int,1:string,2:int}|string|null
     */
    private static function previousNonWhitespaceToken(array $tokens, int $index)
    {
        for ($i = $index - 1; $i >= 0; --$i) {
            $t = $tokens[$i];
            if (is_array($t) && in_array($t[0], [\T_WHITESPACE, \T_COMMENT, \T_DOC_COMMENT], \true)) {
                continue;
            }
            return $t;
        }
        return null;
    }
}
