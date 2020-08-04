<?php

namespace Rector\Generic\Tests\Rector\FuncCall\FuncCallToMethodCallRector\Fixture;

use Rector\Generic\Tests\Rector\FuncCall\FuncCallToMethodCallRector\Source\TranslatorProvider;

final class ReUseTranslator extends TranslatorProvider
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

use Rector\Generic\Tests\Rector\FuncCall\FuncCallToMethodCallRector\Source\TranslatorProvider;

final class ReUseTranslator extends TranslatorProvider
{
    public function run()
    {
        $result = $this->getTranslator()->translateMethod('name');
    }
}

?>
