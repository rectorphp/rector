<?php

namespace Rector\Nette\Tests\Rector\MethodCall\ContextGetByTypeToConstructorInjectionRector\Fixture;

use Rector\Nette\Tests\Rector\MethodCall\ContextGetByTypeToConstructorInjectionRector\Source\ISomeInterfaceToInject;

class ExistingConstructorParam
{
    /**
     * @var \Nette\DI\Container
     */
    private $context;

    /**
     * @var \Rector\Nette\Tests\Rector\MethodCall\ContextGetByTypeToConstructorInjectionRector\Source\ISomeInterfaceToInject
     */
    private $someInterfaceToInject;

    public function __construct(ISomeInterfaceToInject $someInterfaceToInject)
    {
        $this->someInterfaceToInject = $someInterfaceToInject;
    }

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

class ExistingConstructorParam
{
    /**
     * @var \Nette\DI\Container
     */
    private $context;

    /**
     * @var \Rector\Nette\Tests\Rector\MethodCall\ContextGetByTypeToConstructorInjectionRector\Source\ISomeInterfaceToInject
     */
    private $someInterfaceToInject;

    public function __construct(ISomeInterfaceToInject $someInterfaceToInject)
    {
        $this->someInterfaceToInject = $someInterfaceToInject;
    }

    public function run()
    {
        $someTypeToInject = $this->someInterfaceToInject;
    }
}

?>
