<?php

namespace Rector\Tests\Transform\Rector\FuncCall\FuncCallToMethodCallRector\Fixture;

final class Fixture
{
    public function run()
    {
        $result = \translate('name');
    }
}

?>
-----
<?php

namespace Rector\Tests\Transform\Rector\FuncCall\FuncCallToMethodCallRector\Fixture;

final class Fixture
{
    public function __construct(private \Rector\Tests\Transform\Rector\FuncCall\FuncCallToMethodCallRector\Source\SomeTranslator $someTranslator)
    {
    }
    public function run()
    {
        $result = $this->someTranslator->translateMethod('name');
    }
}

?>
