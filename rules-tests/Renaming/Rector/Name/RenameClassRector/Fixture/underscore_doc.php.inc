<?php

namespace Rector\Tests\Renaming\Rector\Name\RenameClassRector\Fixture;

use Rector\Tests\Renaming\Rector\Name\RenameClassRector\Source\AbstractManualExtension;

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

namespace Rector\Tests\Renaming\Rector\Name\RenameClassRector\Fixture;

use Rector\Tests\Renaming\Rector\Name\RenameClassRector\Source\AbstractManualExtension;

final class UnderscoreDoc extends AbstractManualExtension
{
    /**
     * @var \Twig\Extension\SandboxExtension
     */
    public $sandboxExtension;
}

?>
