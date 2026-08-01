<?php

declare (strict_types=1);
namespace RectorPrefix202608\Entropy\Reflection;

use RectorPrefix202608\Entropy\Attributes\RelatedTest;
use RectorPrefix202608\Entropy\Tests\Reflection\ParameterOptionMarkerResolver\ParameterOptionMarkerResolverTest;
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
}
