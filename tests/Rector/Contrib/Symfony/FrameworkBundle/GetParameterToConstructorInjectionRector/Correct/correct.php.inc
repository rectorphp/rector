<?php declare (strict_types=1);

namespace SomeNamespace;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

final class ClassWithNamedService1 extends Controller
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
