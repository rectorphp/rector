<?php

namespace Rector\Tests\CodeQuality\Rector\Array_\CallableThisArrayToAnonymousFunctionRector\Fixture;

final class DefaultOfArray
{
    public function run($values)
    {
        usort($values, [$this, 'sortMe']);
    }

    public function sortMe($values = [])
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\CodeQuality\Rector\Array_\CallableThisArrayToAnonymousFunctionRector\Fixture;

final class DefaultOfArray
{
    public function run($values)
    {
        usort($values, function ($values = []) {
            return $this->sortMe($values);
        });
    }

    private function sortMe($values = [])
    {
    }
}

?>
