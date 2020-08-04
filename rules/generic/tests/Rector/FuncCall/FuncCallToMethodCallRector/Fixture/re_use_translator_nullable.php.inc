<?php

namespace Rector\Generic\Tests\Rector\FuncCall\FuncCallToMethodCallRector\Fixture;

use Rector\Generic\Tests\Rector\FuncCall\FuncCallToMethodCallRector\Source\NullableTranslatorProvider;

final class ReUseTranslatorNullable extends NullableTranslatorProvider
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

use Rector\Generic\Tests\Rector\FuncCall\FuncCallToMethodCallRector\Source\NullableTranslatorProvider;

final class ReUseTranslatorNullable extends NullableTranslatorProvider
{
    public function run()
    {
        $result = $this->getTranslator()->translateMethod('name');
    }
}

?>
