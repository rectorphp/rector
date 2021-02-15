<?php

declare(strict_types=1);

namespace Rector\Nette\NodeFactory;

use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\String_;
use Rector\Nette\Contract\ValueObject\ParameterArrayInterface;

final class RenderParameterArrayFactory
{
    public function createArray(ParameterArrayInterface $parameterArray): ?Array_
    {
        $arrayItems = [];
        foreach ($parameterArray->getTemplateVariables() as $name => $expr) {
            $arrayItems[] = new ArrayItem($expr, new String_($name));
        }

        foreach ($parameterArray->getConditionalVariableNames() as $variableName) {
            $arrayItems[] = new ArrayItem(new Variable($variableName), new String_($variableName));
        }

        if ($arrayItems === []) {
            return null;
        }

        return new Array_($arrayItems);
    }
}
