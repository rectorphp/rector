<?php

namespace Rector\Renaming\Tests\Rector\Name\RenameClassRector\Fixture;

use Rector\Renaming\Tests\Rector\Name\RenameClassRector\Source\AbstractManualExtension;

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

namespace Rector\Renaming\Tests\Rector\Name\RenameClassRector\Fixture;

use Rector\Renaming\Tests\Rector\Name\RenameClassRector\Source\AbstractManualExtension;

final class UnderscoreDoc extends AbstractManualExtension
{
    /**
     * @var \Twig\Extension\SandboxExtension
     */
    public $sandboxExtension;
}

?>
