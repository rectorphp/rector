<?php

namespace Rector\Nette\Tests\Rector\MethodCall\ContextGetByTypeToConstructorInjectionRector\Fixture;

use Rector\Nette\Tests\Rector\MethodCall\ContextGetByTypeToConstructorInjectionRector\Source\ISomeInterfaceToInject;

class RespectInjectPresenter
{
    /**
     * @var \Nette\DI\Container
     */
    private $context;

    /**
     * @inject
     * @var \Rector\Nette\Tests\Rector\MethodCall\ContextGetByTypeToConstructorInjectionRector\Source\SomeTypeToInject
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

namespace Rector\Nette\Tests\Rector\MethodCall\ContextGetByTypeToConstructorInjectionRector\Fixture;

use Rector\Nette\Tests\Rector\MethodCall\ContextGetByTypeToConstructorInjectionRector\Source\ISomeInterfaceToInject;

class RespectInjectPresenter
{
    /**
     * @var \Nette\DI\Container
     */
    private $context;

    /**
     * @inject
     * @var \Rector\Nette\Tests\Rector\MethodCall\ContextGetByTypeToConstructorInjectionRector\Source\SomeTypeToInject
     */
    public $otherInjectedService;
    /**
     * @inject
     */
    public \Rector\Nette\Tests\Rector\MethodCall\ContextGetByTypeToConstructorInjectionRector\Source\ISomeInterfaceToInject $someInterfaceToInject;

    public function run()
    {
        $someTypeToInject = $this->someInterfaceToInject;
    }
}

?>
