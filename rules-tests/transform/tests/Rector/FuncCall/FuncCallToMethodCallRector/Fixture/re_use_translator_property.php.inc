<?php

namespace Rector\Transform\Tests\Rector\FuncCall\FuncCallToMethodCallRector\Fixture;

use Rector\Transform\Tests\Rector\FuncCall\FuncCallToMethodCallRector\Source\PropertyTranslatorProvider;

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

namespace Rector\Transform\Tests\Rector\FuncCall\FuncCallToMethodCallRector\Fixture;

use Rector\Transform\Tests\Rector\FuncCall\FuncCallToMethodCallRector\Source\PropertyTranslatorProvider;

final class ReUseTranslatorProperty extends PropertyTranslatorProvider
{
    public function run()
    {
        $result = $this->existingTranslator->translateMethod('name');
    }
}

?>
