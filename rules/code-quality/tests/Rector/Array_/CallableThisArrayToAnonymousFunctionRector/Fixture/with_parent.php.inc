<?php

namespace Rector\CodeQuality\Tests\Rector\Array_\CallableThisArrayToAnonymousFunctionRector\Fixture;

use Rector\CodeQuality\Tests\Rector\Array_\CallableThisArrayToAnonymousFunctionRector\Source\ParentClass;

class WithParent extends ParentClass
{
    public function run(array $values)
    {
        usort($values, [$this, 'compareSize']);

        return $values;
    }

    private function compareSize(int $first, $second): bool
    {
        return $first <=> $second;
    }
}

?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\Array_\CallableThisArrayToAnonymousFunctionRector\Fixture;

use Rector\CodeQuality\Tests\Rector\Array_\CallableThisArrayToAnonymousFunctionRector\Source\ParentClass;

class WithParent extends ParentClass
{
    public function run(array $values)
    {
        usort($values, function (int $first, $second) : bool {
            return $this->compareSize($first, $second);
        });

        return $values;
    }

    private function compareSize(int $first, $second): bool
    {
        return $first <=> $second;
    }
}

?>
