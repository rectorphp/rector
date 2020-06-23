<?php

namespace Rector\SOLID\Tests\Rector\Foreach_\ChangeNestedForeachIfsToEarlyContinueRector\Fixture;

use Rector\Core\Testing\Contract\RunnableInterface;

class MultiExprsWithBoolOprnds implements RunnableInterface
{
    public function run()
    {
        $loaded = true;
        $usePackages = true;
        $notBad = true;
        $partPackageList = [ new \StdClass(), null, 'string', 0, [] ];
        $payload = [];

        foreach( $partPackageList as $partPackage )
        {
            if(! is_null( $partPackage ) && ! is_object($partPackage) )
            {
                if ($loaded || ($usePackages && $notBad))
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

namespace Rector\SOLID\Tests\Rector\Foreach_\ChangeNestedForeachIfsToEarlyContinueRector\Fixture;

use Rector\Core\Testing\Contract\RunnableInterface;

class MultiExprsWithBoolOprnds implements RunnableInterface
{
    public function run()
    {
        $loaded = true;
        $usePackages = true;
        $notBad = true;
        $partPackageList = [ new \StdClass(), null, 'string', 0, [] ];
        $payload = [];

        foreach ($partPackageList as $partPackage) {
            if (!! is_null( $partPackage )) {
            }
            if (!! is_object($partPackage)) {
            }
            if ($loaded && ($usePackages && $notBad)) {
                continue;
            }
            $payload[] = $partPackage;
        }

        return json_encode($payload);
    }
}

?>
