<?php

declare (strict_types=1);
namespace Rector\Core\Util;

final class MultiInstanceofChecker
{
    /**
     * @param array<class-string> $types
     */
    public function isInstanceOf(object $object, array $types) : bool
    {
        foreach ($types as $type) {
            if ($object instanceof $type) {
                return \true;
            }
        }
        return \false;
    }
}
