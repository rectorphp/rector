<?php

namespace Rector\NetteCodeQuality\Tests\Rector\ClassMethod\MergeTemplateSetFileToTemplateRenderRector\Fixture;

use Nette\Application\UI\Control;

final class SkipAlreadyRender extends Control
{
    public function render()
    {
        $this->template->setFile(__DIR__ . '/someFile.latte');
        $this->template->render('another_path');
    }
}
