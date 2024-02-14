<?php

declare (strict_types=1);
namespace Rector\Doctrine\CodeQuality\Helper;

use Rector\BetterPhpDocParser\PhpDoc\StringNode;
final class NodeValueNormalizer
{
    /**
     * @param mixed $value
     * @return mixed
     */
    public static function normalize($value)
    {
        if (\is_bool($value)) {
            return $value ? 'true' : 'false';
        }
        if (\is_numeric($value)) {
            return $value;
        }
        return new StringNode($value);
    }
}
