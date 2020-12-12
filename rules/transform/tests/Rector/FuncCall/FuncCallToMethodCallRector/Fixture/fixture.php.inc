<?php

namespace Rector\Transform\Tests\Rector\FuncCall\FuncCallToMethodCallRector\Fixture;

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

namespace Rector\Transform\Tests\Rector\FuncCall\FuncCallToMethodCallRector\Fixture;

final class Fixture
{
    private \Rector\Transform\Tests\Rector\FuncCall\FuncCallToMethodCallRector\Source\SomeTranslator $someTranslator;
    public function __construct(\Rector\Transform\Tests\Rector\FuncCall\FuncCallToMethodCallRector\Source\SomeTranslator $someTranslator)
    {
        $this->someTranslator = $someTranslator;
    }
    public function run()
    {
        $result = $this->someTranslator->translateMethod('name');
    }
}

?>
