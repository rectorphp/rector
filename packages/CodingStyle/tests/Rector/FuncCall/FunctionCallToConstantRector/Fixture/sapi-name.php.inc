<?php

namespace Rector\CodingStyle\Tests\Rector\FuncCall\FunctionCallToConstantRector\Fixture;

class SapiNameClass
{
    public function run()
    {
        $value = php_sapi_name();
    }
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\FuncCall\FunctionCallToConstantRector\Fixture;

class SapiNameClass
{
    public function run()
    {
        $value = PHP_SAPI;
    }
}

?>
