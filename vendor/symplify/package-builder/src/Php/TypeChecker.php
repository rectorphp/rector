<?php

declare (strict_types=1);
namespace RectorPrefix20211231\Symplify\PackageBuilder\Php;

/**
 * @api
 */
final class TypeChecker
{
    /**
     * @param array<class-string> $types
     * @param object|string $object
     */
    public function isInstanceOf($object, array $types) : bool
    {
        foreach ($types as $type) {
            if (\is_a($object, $type, \true)) {
                return \true;
            }
        }
        return \false;
    }
}
