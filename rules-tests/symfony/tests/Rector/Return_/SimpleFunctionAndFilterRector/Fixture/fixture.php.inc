<?php

namespace Rector\Symfony\Tests\Rector\Return_\SimpleFunctionAndFilterRector\Fixture;

final class SomeExtension extends \Twig_Extension
{
    public function getFunctions()
    {
        return [
            'is_mobile' => new \Twig_Function_Method($this, 'isMobile'),
        ];
    }
}

?>
-----
<?php

namespace Rector\Symfony\Tests\Rector\Return_\SimpleFunctionAndFilterRector\Fixture;

final class SomeExtension extends \Twig_Extension
{
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('is_mobile', [$this, 'isMobile']),
        ];
    }
}

?>
