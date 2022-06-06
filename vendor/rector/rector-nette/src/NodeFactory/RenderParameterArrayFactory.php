<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Nette\NodeFactory;

use RectorPrefix20220606\PhpParser\Node\Expr\Array_;
use RectorPrefix20220606\PhpParser\Node\Expr\ArrayItem;
use RectorPrefix20220606\PhpParser\Node\Expr\Variable;
use RectorPrefix20220606\PhpParser\Node\Scalar\String_;
use RectorPrefix20220606\Rector\Nette\ValueObject\TemplateParametersAssigns;
final class RenderParameterArrayFactory
{
    public function createArray(TemplateParametersAssigns $templateParametersAssigns) : ?Array_
    {
        $arrayItems = [];
        foreach ($templateParametersAssigns->getTemplateVariables() as $name => $expr) {
            $arrayItems[] = new ArrayItem($expr, new String_($name));
        }
        foreach ($templateParametersAssigns->getConditionalVariableNames() as $variableName) {
            $arrayItems[] = new ArrayItem(new Variable($variableName), new String_($variableName));
        }
        if ($arrayItems === []) {
            return null;
        }
        return new Array_($arrayItems);
    }
}
