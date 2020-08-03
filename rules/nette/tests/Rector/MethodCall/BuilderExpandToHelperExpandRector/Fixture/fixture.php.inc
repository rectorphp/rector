<?php

namespace Rector\Nette\Tests\Rector\MethodCall\BuilderExpandToHelperExpandRector\Fixture;

use Nette\DI\CompilerExtension;

final class SomeClass extends CompilerExtension
{
    public function loadConfiguration()
    {
        $value = $this->getContainerBuilder()->expand('%value');
    }
}

?>
-----
<?php

namespace Rector\Nette\Tests\Rector\MethodCall\BuilderExpandToHelperExpandRector\Fixture;

use Nette\DI\CompilerExtension;

final class SomeClass extends CompilerExtension
{
    public function loadConfiguration()
    {
        $value = \Nette\DI\Helpers::expand('%value', $this->getContainerBuilder()->parameters);
    }
}

?>
