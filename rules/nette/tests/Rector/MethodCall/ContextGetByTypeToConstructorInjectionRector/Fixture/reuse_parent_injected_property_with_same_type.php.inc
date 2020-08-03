<?php

namespace Rector\Nette\Tests\Rector\MethodCall\ContextGetByTypeToConstructorInjectionRector\Fixture;

use Rector\Nette\Tests\Rector\MethodCall\ContextGetByTypeToConstructorInjectionRector\Source\ParentWithInjectedPropertyPresenter;
use Rector\Nette\Tests\Rector\MethodCall\ContextGetByTypeToConstructorInjectionRector\Source\SomeTypeToInject;

class ReuseParentInjectedPropertyWithSameType extends ParentWithInjectedPropertyPresenter
{
    /**
     * @var \Nette\DI\Container
     */
    private $context;

    public function go()
    {
        $someTypeToInject = $this->context->getByType(SomeTypeToInject::class);
    }
}

?>
-----
<?php

namespace Rector\Nette\Tests\Rector\MethodCall\ContextGetByTypeToConstructorInjectionRector\Fixture;

use Rector\Nette\Tests\Rector\MethodCall\ContextGetByTypeToConstructorInjectionRector\Source\ParentWithInjectedPropertyPresenter;
use Rector\Nette\Tests\Rector\MethodCall\ContextGetByTypeToConstructorInjectionRector\Source\SomeTypeToInject;

class ReuseParentInjectedPropertyWithSameType extends ParentWithInjectedPropertyPresenter
{
    /**
     * @var \Nette\DI\Container
     */
    private $context;

    public function go()
    {
        $someTypeToInject = $this->someTypeToInject;
    }
}

?>
