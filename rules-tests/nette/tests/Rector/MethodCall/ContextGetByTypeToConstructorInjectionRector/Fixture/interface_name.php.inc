<?php

namespace Rector\Nette\Tests\Rector\MethodCall\ContextGetByTypeToConstructorInjectionRector\Fixture;

use Rector\Nette\Tests\Rector\MethodCall\ContextGetByTypeToConstructorInjectionRector\Source\ISomeInterfaceToInject;

class InterfaceName
{
    /**
     * @var \Nette\DI\Container
     */
    private $context;

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

class InterfaceName
{
    /**
     * @var \Nette\DI\Container
     */
    private $context;
    public function __construct(private \Rector\Nette\Tests\Rector\MethodCall\ContextGetByTypeToConstructorInjectionRector\Source\ISomeInterfaceToInject $someInterfaceToInject)
    {
    }

    public function run()
    {
        $someTypeToInject = $this->someInterfaceToInject;
    }
}

?>
