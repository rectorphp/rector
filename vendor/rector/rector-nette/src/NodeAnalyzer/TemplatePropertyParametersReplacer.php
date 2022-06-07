<?php

declare (strict_types=1);
namespace Rector\Nette\NodeAnalyzer;

use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\String_;
use Rector\Nette\ValueObject\TemplateParametersAssigns;
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
