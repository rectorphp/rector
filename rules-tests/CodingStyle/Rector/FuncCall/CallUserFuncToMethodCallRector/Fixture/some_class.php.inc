<?php

namespace Rector\Tests\CodingStyle\Rector\FuncCall\CallUserFuncToMethodCallRector\Fixture;

final class SomeClass
{
    public function run()
    {
        $result = \call_user_func([$this->property, 'method'], $args);
    }
}

?>
-----
<?php

namespace Rector\Tests\CodingStyle\Rector\FuncCall\CallUserFuncToMethodCallRector\Fixture;

final class SomeClass
{
    public function run()
    {
        $result = $this->property->method($args);
    }
}

?>
