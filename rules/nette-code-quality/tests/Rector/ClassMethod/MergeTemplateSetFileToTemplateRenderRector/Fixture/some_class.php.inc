<?php

namespace Rector\NetteCodeQuality\Tests\Rector\ClassMethod\MergeTemplateSetFileToTemplateRenderRector\Fixture;

use Nette\Application\UI\Control;

final class SomeControl extends Control
{
    public function render()
    {
        $this->template->setFile(__DIR__ . '/someFile.latte');
        $this->template->render();
    }
}

?>
-----
<?php

namespace Rector\NetteCodeQuality\Tests\Rector\ClassMethod\MergeTemplateSetFileToTemplateRenderRector\Fixture;

use Nette\Application\UI\Control;

final class SomeControl extends Control
{
    public function render()
    {
        $this->template->render(__DIR__ . '/someFile.latte');
    }
}

?>
