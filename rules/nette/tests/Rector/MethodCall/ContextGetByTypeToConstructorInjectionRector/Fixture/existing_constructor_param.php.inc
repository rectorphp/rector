<?php

namespace Rector\Nette\Tests\Rector\MethodCall\ContextGetByTypeToConstructorInjectionRector\Fixture;

use Rector\Nette\Tests\Rector\MethodCall\ContextGetByTypeToConstructorInjectionRector\Fixture\Source\ISomeInterfaceToInject;

class ExistingConstructorParam
{
    /**
     * @var \Nette\DI\Container
     */
    private $context;

    /**
     * @var ISomeInterfaceToInject
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

use Rector\Nette\Tests\Rector\MethodCall\ContextGetByTypeToConstructorInjectionRector\Fixture\Source\ISomeInterfaceToInject;

class ExistingConstructorParam
{
    /**
     * @var \Nette\DI\Container
     */
    private $context;

    /**
     * @var ISomeInterfaceToInject
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
