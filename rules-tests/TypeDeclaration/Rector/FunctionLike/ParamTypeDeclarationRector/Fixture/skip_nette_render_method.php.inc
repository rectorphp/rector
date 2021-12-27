<?php

namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ParamTypeDeclarationRector\Fixture;

use Nette\Application\UI\Presenter;

final class SkipNetteRenderMethod extends Presenter
{
    /**
     * @param int $id
     */
    public function render($id)
    {
    }
}
