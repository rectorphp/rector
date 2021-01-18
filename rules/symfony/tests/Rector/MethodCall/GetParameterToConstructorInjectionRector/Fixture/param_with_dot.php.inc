<?php

namespace Rector\Symfony\Tests\Rector\MethodCall\GetParameterToConstructorInjectionRector\Fixture;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

final class ParamWithDot extends Controller
{
    public function render()
    {
        $value = $this->getParameter('kernel.debug');

        return [
            'value' => $this->getParameter('kernel.debug'),
            'value2' => $value,
        ];
    }
}

?>
-----
<?php

namespace Rector\Symfony\Tests\Rector\MethodCall\GetParameterToConstructorInjectionRector\Fixture;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

final class ParamWithDot extends Controller
{
    public function __construct(private string $kernelDebug)
    {
    }
    public function render()
    {
        $value = $this->kernelDebug;

        return [
            'value' => $this->kernelDebug,
            'value2' => $value,
        ];
    }
}

?>
