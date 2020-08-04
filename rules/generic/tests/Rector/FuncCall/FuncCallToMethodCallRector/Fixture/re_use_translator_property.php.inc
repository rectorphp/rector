<?php

namespace Rector\Generic\Tests\Rector\FuncCall\FuncCallToMethodCallRector\Fixture;

use Rector\Generic\Tests\Rector\FuncCall\FuncCallToMethodCallRector\Source\PropertyTranslatorProvider;

final class ReUseTranslatorProperty extends PropertyTranslatorProvider
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

use Rector\Generic\Tests\Rector\FuncCall\FuncCallToMethodCallRector\Source\PropertyTranslatorProvider;

final class ReUseTranslatorProperty extends PropertyTranslatorProvider
{
    public function run()
    {
        $result = $this->existingTranslator->translateMethod('name');
    }
}

?>
