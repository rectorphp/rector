<?php

namespace Rector\Tests\EarlyReturn\Rector\Foreach_\ChangeNestedForeachIfsToEarlyContinueRector\Fixture;

class MultiExprsWithBoolOprnds
{
    public function run()
    {
        $partPackageList = [ new \StdClass(), 'string' ];
        $payload = [];

        foreach($partPackageList as $partPackage)
        {
            if(! is_null($partPackage) && ! is_object($partPackage))
            {
                if (1 == '1' || 2 == 't')
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

class MultiExprsWithBoolOprnds
{
    public function run()
    {
        $partPackageList = [ new \StdClass(), 'string' ];
        $payload = [];

        foreach ($partPackageList as $partPackage) {
            if (is_null($partPackage)) {
                continue;
            }
            if (is_object($partPackage)) {
                continue;
            }
            if (1 == '1' || 2 == 't')
            {
                $payload[] = $partPackage;
            }
        }

        return json_encode($payload);
    }
}

?>
