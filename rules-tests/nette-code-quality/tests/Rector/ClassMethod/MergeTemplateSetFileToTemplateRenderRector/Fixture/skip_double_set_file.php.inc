<?php

namespace Rector\NetteCodeQuality\Tests\Rector\ClassMethod\MergeTemplateSetFileToTemplateRenderRector\Fixture;

use Nette\Application\UI\Control;

final class SkipDoubleSetFile extends Control
{
    public function render()
    {
        if (mt_rand(0, 100)) {
            $this->template->setFile(__DIR__ . '/someFile.latte');
        } else {
            $this->template->setFile(__DIR__ . '/anotherFile.latte');
        }

        $this->template->render();
    }
}
