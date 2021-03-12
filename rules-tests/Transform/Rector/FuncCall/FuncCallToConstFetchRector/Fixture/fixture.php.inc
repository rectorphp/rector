<?php

namespace Rector\Tests\Transform\Rector\FuncCall\FuncCallToConstFetchRector\Fixture;

class Fixture
{
    public function run()
    {
        $value = pi();

        $value2 = php_sapi_name();
    }
}

?>
-----
<?php

namespace Rector\Tests\Transform\Rector\FuncCall\FuncCallToConstFetchRector\Fixture;

class Fixture
{
    public function run()
    {
        $value = M_PI;

        $value2 = PHP_SAPI;
    }
}

?>
