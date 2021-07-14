<?php

declare(strict_types=1);

final class NoPrefixConsts
{
    public function someMethod()
    {
        $parentClass = parent::someMethod();

        $value = true;
        $value = FALSE;
        $value = __DIR__;

        $native = PREG_GREP_INVERT;

        $localConstant = LOCAL_CONSTANT;
    }
}

?>
-----
<?php

namespace Rector\Tests\PSR4\Rector\FileWithoutNamespace\NormalizeNamespaceByPSR4ComposerAutoloadRector\Fixture;

final class NoPrefixConsts
{
    public function someMethod()
    {
        $parentClass = parent::someMethod();

        $value = true;
        $value = FALSE;
        $value = __DIR__;

        $native = PREG_GREP_INVERT;

        $localConstant = \LOCAL_CONSTANT;
    }
}
