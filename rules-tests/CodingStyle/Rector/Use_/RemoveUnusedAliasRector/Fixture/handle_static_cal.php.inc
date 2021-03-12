<?php

namespace Rector\Tests\CodingStyle\Rector\Use_\RemoveUnusedAliasRector\Fixture;

use Rector\Tests\CodingStyle\Rector\Use_\RemoveUnusedAliasRector\Source\AliasedStaticCall as CallOnMe;

class HandleStaticCall
{
    public function run()
    {
        CallOnMe::someCall();
    }
}

?>
-----
<?php

namespace Rector\Tests\CodingStyle\Rector\Use_\RemoveUnusedAliasRector\Fixture;

use Rector\Tests\CodingStyle\Rector\Use_\RemoveUnusedAliasRector\Source\AliasedStaticCall;

class HandleStaticCall
{
    public function run()
    {
        AliasedStaticCall::someCall();
    }
}

?>
