<?php

namespace Rector\Renaming\Tests\Rector\FuncCall\RenameFuncCallToStaticCallRector\Fixture;

class SomeClass
{
    public function run()
    {
        strPee('...');
    }
}

?>
-----
<?php

namespace Rector\Renaming\Tests\Rector\FuncCall\RenameFuncCallToStaticCallRector\Fixture;

class SomeClass
{
    public function run()
    {
        \Strings::strPaa('...');
    }
}

?>
