<?php

namespace Rector\Silverstripe\Tests\Rector\DefineConstantToStaticCallRector\Wrong;

function defineConstantToStaticCall()
{
    if (defined('SS_DATABASE_NAME')) {
        echo SS_DATABASENAME;
    }
}

?>
-----
<?php

namespace Rector\Silverstripe\Tests\Rector\DefineConstantToStaticCallRector\Wrong;

function defineConstantToStaticCall()
{
    if (\Environment::getEnv('SS_DATABASE_NAME')) {
        echo SS_DATABASENAME;
    }
}

?>
