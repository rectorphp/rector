<?php

namespace Rector\Tests\Nette\Rector\MethodCall\ContextGetByTypeToConstructorInjectionRector\Fixture;

use Rector\Tests\Nette\Rector\MethodCall\ContextGetByTypeToConstructorInjectionRector\Source\ConstructorInjectionParentPresenter;
use Rector\Tests\Nette\Rector\MethodCall\ContextGetByTypeToConstructorInjectionRector\Source\ISomeInterfaceToInject;

final class RespectInjectParentConstructPreferLocalConstructorPresenter extends ConstructorInjectionParentPresenter
{
    /**
     * @var \Nette\DI\Container
     */
    private $context;

    public function __construct()
    {
        $some = 100;
    }

    public function go()
    {
        $someTypeToInject = $this->context->getByType(ISomeInterfaceToInject::class);
    }
}

?>
-----
<?php

namespace Rector\Tests\Nette\Rector\MethodCall\ContextGetByTypeToConstructorInjectionRector\Fixture;

use Rector\Tests\Nette\Rector\MethodCall\ContextGetByTypeToConstructorInjectionRector\Source\ConstructorInjectionParentPresenter;
use Rector\Tests\Nette\Rector\MethodCall\ContextGetByTypeToConstructorInjectionRector\Source\ISomeInterfaceToInject;

final class RespectInjectParentConstructPreferLocalConstructorPresenter extends ConstructorInjectionParentPresenter
{
    /**
     * @var \Nette\DI\Container
     */
    private $context;

    public function __construct(private \Rector\Tests\Nette\Rector\MethodCall\ContextGetByTypeToConstructorInjectionRector\Source\ISomeInterfaceToInject $someInterfaceToInject)
    {
        $some = 100;
        parent::__construct();
    }

    public function go()
    {
        $someTypeToInject = $this->someInterfaceToInject;
    }
}

?>
