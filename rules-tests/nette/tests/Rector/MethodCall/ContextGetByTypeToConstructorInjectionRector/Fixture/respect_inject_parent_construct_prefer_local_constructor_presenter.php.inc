<?php

namespace Rector\Nette\Tests\Rector\MethodCall\ContextGetByTypeToConstructorInjectionRector\Fixture;

use Rector\Nette\Tests\Rector\MethodCall\ContextGetByTypeToConstructorInjectionRector\Source\ConstructorInjectionParentPresenter;
use Rector\Nette\Tests\Rector\MethodCall\ContextGetByTypeToConstructorInjectionRector\Source\ISomeInterfaceToInject;

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

namespace Rector\Nette\Tests\Rector\MethodCall\ContextGetByTypeToConstructorInjectionRector\Fixture;

use Rector\Nette\Tests\Rector\MethodCall\ContextGetByTypeToConstructorInjectionRector\Source\ConstructorInjectionParentPresenter;
use Rector\Nette\Tests\Rector\MethodCall\ContextGetByTypeToConstructorInjectionRector\Source\ISomeInterfaceToInject;

final class RespectInjectParentConstructPreferLocalConstructorPresenter extends ConstructorInjectionParentPresenter
{
    /**
     * @var \Nette\DI\Container
     */
    private $context;

    public function __construct(private \Rector\Nette\Tests\Rector\MethodCall\ContextGetByTypeToConstructorInjectionRector\Source\ISomeInterfaceToInject $someInterfaceToInject)
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
