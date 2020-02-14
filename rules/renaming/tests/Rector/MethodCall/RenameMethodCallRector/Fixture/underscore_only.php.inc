<?php

namespace Rector\Renaming\Tests\Rector\MethodCall\RenameMethodCallRector\Fixture;

use Rector\Renaming\Tests\Rector\MethodCall\RenameMethodCallRector\Source\ClassMethodToBeSkipped;
use Rector\Renaming\Tests\Rector\MethodCall\RenameMethodCallRector\Source\SomeTranslator;

class UnderScoreOnly
{
    /**
     * @var SomeTranslator
     */
    private $translator;

    private function createHtml()
    {
        $this->translator->__('...');
        $this->translator->getLocale();
    }
}

?>
-----
<?php

namespace Rector\Renaming\Tests\Rector\MethodCall\RenameMethodCallRector\Fixture;

use Rector\Renaming\Tests\Rector\MethodCall\RenameMethodCallRector\Source\ClassMethodToBeSkipped;
use Rector\Renaming\Tests\Rector\MethodCall\RenameMethodCallRector\Source\SomeTranslator;

class UnderScoreOnly
{
    /**
     * @var SomeTranslator
     */
    private $translator;

    private function createHtml()
    {
        $this->translator->trans('...');
        $this->translator->getLocale();
    }
}

?>
