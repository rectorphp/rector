<?php

namespace Rector\EarlyReturn\Tests\Rector\Foreach_\ChangeNestedForeachIfsToEarlyContinueRector\Fixture;

use Rector\Testing\Contract\RunnableInterface;

class MultiExprsWithOrBothTruthy implements RunnableInterface
{
    public function run()
    {
        $partPackageList = [ new \StdClass(), 'string' ];
        $payload = [];

        foreach( $partPackageList as $partPackage )
        {
            if(true)
            {
                if (1 === 1 || 2 === 2)
                {
                    $payload[] = $partPackage;
                }
            }
        }

        return json_encode($payload);
    }
}

?>
-----
<?php

namespace Rector\EarlyReturn\Tests\Rector\Foreach_\ChangeNestedForeachIfsToEarlyContinueRector\Fixture;

use Rector\Testing\Contract\RunnableInterface;

class MultiExprsWithOrBothTruthy implements RunnableInterface
{
    public function run()
    {
        $partPackageList = [ new \StdClass(), 'string' ];
        $payload = [];

        foreach ($partPackageList as $partPackage) {
            if (!true) {
                continue;
            }
            if (!(1 === 1 || 2 === 2)) {
                continue;
            }
            $payload[] = $partPackage;
        }

        return json_encode($payload);
    }
}

?>
