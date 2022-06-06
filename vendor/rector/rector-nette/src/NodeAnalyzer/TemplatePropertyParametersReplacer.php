<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Nette\NodeAnalyzer;

use RectorPrefix20220606\PhpParser\Node\Expr\ArrayDimFetch;
use RectorPrefix20220606\PhpParser\Node\Expr\Variable;
use RectorPrefix20220606\PhpParser\Node\Scalar\String_;
use RectorPrefix20220606\Rector\Nette\ValueObject\TemplateParametersAssigns;
final class TemplatePropertyParametersReplacer
{
    public function replace(TemplateParametersAssigns $magicTemplateParametersAssigns, Variable $variable) : void
    {
        foreach ($magicTemplateParametersAssigns->getTemplateParameterAssigns() as $alwaysTemplateParameterAssign) {
            $arrayDimFetch = new ArrayDimFetch($variable, new String_($alwaysTemplateParameterAssign->getParameterName()));
            $assign = $alwaysTemplateParameterAssign->getAssign();
            $assign->var = $arrayDimFetch;
        }
    }
}
