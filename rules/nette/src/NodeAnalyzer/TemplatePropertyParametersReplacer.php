<?php

declare(strict_types=1);

namespace Rector\Nette\NodeAnalyzer;

use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\String_;
use Rector\Nette\ValueObject\TemplateParametersAssigns;

final class TemplatePropertyParametersReplacer
{
    public function replace(TemplateParametersAssigns $magicTemplateParametersAssigns, Variable $variable): void
    {
        foreach ($magicTemplateParametersAssigns->getTemplateParameterAssigns() as $templateParameterAssign) {
            $arrayDimFetch = new ArrayDimFetch(
                $variable,
                new String_($templateParameterAssign->getParameterName())
            );

            $assign = $templateParameterAssign->getAssign();
            $assign->var = $arrayDimFetch;
        }
    }
}
