<?php

namespace Rector\Naming\Tests\Rector\Class_\RenamePropertyToMatchTypeRector\Fixture;

use Rector\Naming\Tests\Rector\Class_\RenamePropertyToMatchTypeRector\Source\Twig_Template;

class Underscore
{
    public function method(Twig_Template $total_random)
    {
    }
}

?>
-----
<?php

namespace Rector\Naming\Tests\Rector\Class_\RenamePropertyToMatchTypeRector\Fixture;

use Rector\Naming\Tests\Rector\Class_\RenamePropertyToMatchTypeRector\Source\Twig_Template;

class Underscore
{
    public function method(Twig_Template $twigTemplate)
    {
    }
}

?>
