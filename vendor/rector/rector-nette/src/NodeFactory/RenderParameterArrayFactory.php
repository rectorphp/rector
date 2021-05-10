<?php

declare (strict_types=1);
namespace Rector\Nette\NodeFactory;

use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\String_;
use Rector\Nette\Contract\ValueObject\ParameterArrayInterface;
final class RenderParameterArrayFactory
{
    public function createArray(\Rector\Nette\Contract\ValueObject\ParameterArrayInterface $parameterArray) : ?\PhpParser\Node\Expr\Array_
    {
        $arrayItems = [];
        foreach ($parameterArray->getTemplateVariables() as $name => $expr) {
            $arrayItems[] = new \PhpParser\Node\Expr\ArrayItem($expr, new \PhpParser\Node\Scalar\String_($name));
        }
        foreach ($parameterArray->getConditionalVariableNames() as $variableName) {
            $arrayItems[] = new \PhpParser\Node\Expr\ArrayItem(new \PhpParser\Node\Expr\Variable($variableName), new \PhpParser\Node\Scalar\String_($variableName));
        }
        if ($arrayItems === []) {
            return null;
        }
        return new \PhpParser\Node\Expr\Array_($arrayItems);
    }
}
