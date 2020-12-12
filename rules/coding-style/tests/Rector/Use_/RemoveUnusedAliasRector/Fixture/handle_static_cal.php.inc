<?php

namespace Rector\CodingStyle\Tests\Rector\Use_\RemoveUnusedAliasRector\Fixture;

use Rector\CodingStyle\Tests\Rector\Use_\RemoveUnusedAliasRector\Source\AliasedStaticCall as CallOnMe;

class HandleStaticCal
{
    public function run()
    {
        CallOnMe::someCall();
    }
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\Use_\RemoveUnusedAliasRector\Fixture;

use Rector\CodingStyle\Tests\Rector\Use_\RemoveUnusedAliasRector\Source\AliasedStaticCall;

class HandleStaticCal
{
    public function run()
    {
        AliasedStaticCall::someCall();
    }
}

?>
