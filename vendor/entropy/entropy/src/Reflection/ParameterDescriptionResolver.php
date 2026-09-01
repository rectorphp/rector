<?php

declare (strict_types=1);
namespace RectorPrefix202609\Entropy\Reflection;

use RectorPrefix202609\Entropy\Attributes\RelatedTest;
use RectorPrefix202609\Entropy\Tests\Reflection\ParameterDescriptionResolver\ParameterDescriptionResolverTest;
use ReflectionMethod;
final class ParameterDescriptionResolver
{
    /**
     * @return array<string, string> map: paramName => description
     */
    public static function resolve(ReflectionMethod $reflectionMethod): array
    {
        $doc = $reflectionMethod->getDocComment();
        if ($doc === \false || $doc === '') {
            return [];
        }
        $descriptions = [];
        // Parse line-by-line to avoid accidentally spanning/joining multiple @param lines
        $lines = preg_split('/\R/u', $doc) ?: [];
        foreach ($lines as $line) {
            // normalize typical docblock formatting: " * @param ..."
            $line = ltrim($line);
            $line = preg_replace('#^\*\s?#', '', $line) ?? $line;
            // Match a single @param line only
            // Supports: @param Type $name Description...
            // Also tolerates: @param Type $name ..., @param Type ...$name ...
            if (!preg_match('/^@param\s+\S+\s+(?:&?\.\.\.)?\$([A-Za-z_]\w*)\s*(.*)$/', $line, $m)) {
                continue;
            }
            $paramName = $m[1];
            $desc = trim($m[2]);
            if ($desc !== '') {
                $descriptions[$paramName] = $desc;
            }
        }
        return $descriptions;
    }
}
