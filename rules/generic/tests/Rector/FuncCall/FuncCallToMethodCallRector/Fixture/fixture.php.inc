<?php

namespace Rector\Generic\Tests\Rector\FuncCall\FuncCallToMethodCallRector\Fixture;

final class SomeClass
{
    public function run()
    {
        $result = \translate('name');
    }
}

?>
-----
<?php

namespace Rector\Generic\Tests\Rector\FuncCall\FuncCallToMethodCallRector\Fixture;

final class SomeClass
{
    private \Rector\Generic\Tests\Rector\FuncCall\FuncCallToMethodCallRector\Source\SomeTranslator $someTranslator;
    public function __construct(\Rector\Generic\Tests\Rector\FuncCall\FuncCallToMethodCallRector\Source\SomeTranslator $someTranslator)
    {
        $this->someTranslator = $someTranslator;
    }
    public function run()
    {
        $result = $this->someTranslator->translateMethod('name');
    }
}

?>
