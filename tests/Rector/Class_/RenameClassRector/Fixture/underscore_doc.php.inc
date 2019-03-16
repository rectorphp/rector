<?php

namespace Rector\Tests\Rector\Class_\RenameClassRector\Fixture;

use Rector\Tests\Rector\Class_\RenameClassRector\Source\AbstractManualExtension;

final class UnderscoreDoc extends AbstractManualExtension
{
    /**
     * @var \Twig_Extension_Sandbox
     */
    public $sandboxExtension;
}

?>
-----
<?php

namespace Rector\Tests\Rector\Class_\RenameClassRector\Fixture;

use Rector\Tests\Rector\Class_\RenameClassRector\Source\AbstractManualExtension;

final class UnderscoreDoc extends AbstractManualExtension
{
    /**
     * @var \Twig\Extension\SandboxExtension
     */
    public $sandboxExtension;
}

?>
