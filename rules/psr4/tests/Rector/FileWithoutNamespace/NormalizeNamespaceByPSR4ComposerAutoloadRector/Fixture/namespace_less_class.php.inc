<?php

declare(strict_types=1);

class NamespaceLessClass
{
    public function someMethod()
    {
        $someClass = new Rector\PSR4\Tests\Rector\FileWithoutNamespace\NormalizeNamespaceByPSR4ComposerAutoloadRector\Source\SomeClass();
    }
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\PSR4\Tests\Rector\FileWithoutNamespace\NormalizeNamespaceByPSR4ComposerAutoloadRector\Fixture;

class NamespaceLessClass
{
    public function someMethod()
    {
        $someClass = new \Rector\PSR4\Tests\Rector\FileWithoutNamespace\NormalizeNamespaceByPSR4ComposerAutoloadRector\Source\SomeClass();
    }
}

?>
