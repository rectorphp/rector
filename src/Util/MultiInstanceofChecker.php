<?php

declare (strict_types=1);
namespace Rector\Core\Util;

final class MultiInstanceofChecker
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
