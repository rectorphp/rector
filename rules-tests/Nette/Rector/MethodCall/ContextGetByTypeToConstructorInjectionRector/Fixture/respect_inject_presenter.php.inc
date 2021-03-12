<?php

namespace Rector\Tests\Nette\Rector\MethodCall\ContextGetByTypeToConstructorInjectionRector\Fixture;

use Rector\Tests\Nette\Rector\MethodCall\ContextGetByTypeToConstructorInjectionRector\Source\ISomeInterfaceToInject;

class RespectInjectPresenter
{
    /**
     * @var \Nette\DI\Container
     */
    private $context;

    /**
     * @inject
     * @var \Rector\Tests\Nette\Rector\MethodCall\ContextGetByTypeToConstructorInjectionRector\Source\SomeTypeToInject
     */
    public $otherInjectedService;

    public function run()
    {
        $someTypeToInject = $this->context->getByType(ISomeInterfaceToInject::class);
    }
}

?>
-----
<?php

namespace Rector\Tests\Nette\Rector\MethodCall\ContextGetByTypeToConstructorInjectionRector\Fixture;

use Rector\Tests\Nette\Rector\MethodCall\ContextGetByTypeToConstructorInjectionRector\Source\ISomeInterfaceToInject;

class RespectInjectPresenter
{
    /**
     * @var \Nette\DI\Container
     */
    private $context;

    /**
     * @inject
     * @var \Rector\Tests\Nette\Rector\MethodCall\ContextGetByTypeToConstructorInjectionRector\Source\SomeTypeToInject
     */
    public $otherInjectedService;
    /**
     * @inject
     */
    public \Rector\Tests\Nette\Rector\MethodCall\ContextGetByTypeToConstructorInjectionRector\Source\ISomeInterfaceToInject $someInterfaceToInject;

    public function run()
    {
        $someTypeToInject = $this->someInterfaceToInject;
    }
}

?>
