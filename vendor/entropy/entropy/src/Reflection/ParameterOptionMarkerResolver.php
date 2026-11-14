<?php

declare (strict_types=1);
namespace RectorPrefix202611\Entropy\Reflection;

use RectorPrefix202611\Entropy\Attribute\RelatedTest;
use RectorPrefix202611\Entropy\Tests\Reflection\ParameterOptionMarkerResolver\ParameterOptionMarkerResolverTest;
use ReflectionMethod;
final class ParameterOptionMarkerResolver
{
    /**
     * @return array<string, true> map: paramName => true for every "@option $name" line
     */
    public static function resolve(ReflectionMethod $reflectionMethod): array
    {
        $doc = $reflectionMethod->getDocComment();
        if ($doc === \false || $doc === '') {
            return [];
        }
        $markers = [];
        $lines = preg_split('/\R/u', $doc) ?: [];
        foreach ($lines as $line) {
            $line = ltrim($line);
            $line = preg_replace('#^\*\s?#', '', $line) ?? $line;
            // Match: @option $name
            if (!preg_match('/^@option\s+\$([A-Za-z_]\w*)\b/', $line, $m)) {
                continue;
            }
            $markers[$m[1]] = \true;
        }
        return $markers;
    }
    /**
     * @return array<string, string> map: short alias => paramName for every "@option $name -x" line
     */
    public static function resolveShortAliases(ReflectionMethod $reflectionMethod): array
    {
        $doc = $reflectionMethod->getDocComment();
        if ($doc === \false || $doc === '') {
            return [];
        }
        $shortAliases = [];
        $lines = preg_split('/\R/u', $doc) ?: [];
        foreach ($lines as $line) {
            $line = ltrim($line);
            $line = preg_replace('#^\*\s?#', '', $line) ?? $line;
            // Match: @option $name -x
            if (!preg_match('/^@option\s+\$([A-Za-z_]\w*)\s+-([A-Za-z])\b/', $line, $m)) {
                continue;
            }
            $shortAliases[$m[2]] = $m[1];
        }
        return $shortAliases;
    }
}
