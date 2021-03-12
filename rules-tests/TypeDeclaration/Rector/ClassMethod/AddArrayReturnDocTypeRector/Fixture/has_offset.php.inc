<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

final class HasOffset
{
    private function convertArguments($service)
    {
        if (! is_array($service)) {
            return $service;
        }

        if (! isset($service['arguments'])) {
            return $service;
        }

        return $service;
    }
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

final class HasOffset
{
    /**
     * @return mixed|mixed[]
     */
    private function convertArguments($service)
    {
        if (! is_array($service)) {
            return $service;
        }

        if (! isset($service['arguments'])) {
            return $service;
        }

        return $service;
    }
}

?>
