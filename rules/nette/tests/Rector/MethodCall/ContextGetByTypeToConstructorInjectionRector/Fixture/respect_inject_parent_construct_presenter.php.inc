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
     * @inject
     */
    public \Rector\Nette\Tests\Rector\MethodCall\ContextGetByTypeToConstructorInjectionRector\Source\ISomeInterfaceToInject $someInterfaceToInject;

    public function go()
    {
        $someTypeToInject = $this->someInterfaceToInject;
    }
}

?>
