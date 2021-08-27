<?php

declare (strict_types=1);
namespace RectorPrefix20210827\Symplify\PackageBuilder\Php;

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
