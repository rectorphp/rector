<?php

namespace SomeNamespace;

use Rector\Symfony\Tests\Rector\Source\SymfonyController;

final class ClassWithNamedService1 extends SymfonyController
{
    public function render()
    {
        $value = $this->getParameter('some_parameter');

        return [
            'value' => $this->getParameter('some_parameter'),
            'value2' => $value,
        ];
    }
}

?>
-----
<?php

namespace SomeNamespace;

use Rector\Symfony\Tests\Rector\Source\SymfonyController;

final class ClassWithNamedService1 extends SymfonyController
{
    /**
     * @var string
     */
    private $someParameter;
    public function __construct(string $someParameter)
    {
        $this->someParameter = $someParameter;
    }
    public function render()
    {
        $value = $this->someParameter;

        return [
            'value' => $this->someParameter,
            'value2' => $value,
        ];
    }
}

?>
