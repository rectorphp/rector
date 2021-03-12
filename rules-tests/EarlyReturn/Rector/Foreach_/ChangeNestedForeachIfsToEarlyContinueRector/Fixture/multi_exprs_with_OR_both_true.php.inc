<?php

namespace Rector\Tests\EarlyReturn\Rector\Foreach_\ChangeNestedForeachIfsToEarlyContinueRector\Fixture;

class MultiExprsWithOrBothTruthy
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

namespace Rector\Tests\EarlyReturn\Rector\Foreach_\ChangeNestedForeachIfsToEarlyContinueRector\Fixture;

class MultiExprsWithOrBothTruthy
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
