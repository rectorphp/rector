<?php

namespace Rector\Naming\Tests\Rector\ClassMethod\RenameParamToMatchTypeRector\Fixture;

use Rector\Naming\Tests\Rector\ClassMethod\RenameParamToMatchTypeRector\Source\Twig_Template;

class Underscore
{
    public function method(Twig_Template $total_random)
    {
    }
}

?>
-----
<?php

namespace Rector\Naming\Tests\Rector\ClassMethod\RenameParamToMatchTypeRector\Fixture;

use Rector\Naming\Tests\Rector\ClassMethod\RenameParamToMatchTypeRector\Source\Twig_Template;

class Underscore
{
    public function method(Twig_Template $twigTemplate)
    {
    }
}

?>
