<?php

namespace Rector\Nette\Tests\Rector\MethodCall\ContextGetByTypeToConstructorInjectionRector\Fixture;

use Rector\Nette\Tests\Rector\MethodCall\ContextGetByTypeToConstructorInjectionRector\Source\ConstructorInjectionParentPresenter;
use Rector\Nette\Tests\Rector\MethodCall\ContextGetByTypeToConstructorInjectionRector\Source\ISomeInterfaceToInject;

final class RespectInjectParentConstructPresenter extends ConstructorInjectionParentPresenter
{
    /**
     * @var \Nette\DI\Container
     */
    private $context;

    public function go()
    {
        $someTypeToInject = $this->context->getByType(ISomeInterfaceToInject::class);
    }
}

?>
-----
<?php

namespace Rector\Nette\Tests\Rector\MethodCall\ContextGetByTypeToConstructorInjectionRector\Fixture;

use Rector\Nette\Tests\Rector\MethodCall\ContextGetByTypeToConstructorInjectionRector\Source\ConstructorInjectionParentPresenter;
use Rector\Nette\Tests\Rector\MethodCall\ContextGetByTypeToConstructorInjectionRector\Source\ISomeInterfaceToInject;

final class RespectInjectParentConstructPresenter extends ConstructorInjectionParentPresenter
{
    /**
     * @var \Nette\DI\Container
     */
    private $context;
    /**
     * @var \Rector\Nette\Tests\Rector\MethodCall\ContextGetByTypeToConstructorInjectionRector\Source\ISomeInterfaceToInject
     * @inject
     */
    public $someInterfaceToInject;

    public function go()
    {
        $someTypeToInject = $this->someInterfaceToInject;
    }
}

?>
