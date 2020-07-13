<?php

declare(strict_types=1);

namespace Rector\Symfony\NodeFactory;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\PhpParser\Builder\MethodBuilder;
use Rector\Core\PhpParser\Builder\ParamBuilder;

final class ConfigureOptionsNodeFactory
{
    public function create(array $namesToArgs): ClassMethod
    {
        $paramBuilder = new ParamBuilder('resolver');
        $paramBuilder->setType(new FullyQualified('Symfony\Component\OptionsResolver\OptionsResolver'));
        $resolverParam = $paramBuilder->getNode();

        $array = new Array_();
        foreach (array_keys($namesToArgs) as $optionName) {
            $array->items[] = new ArrayItem($this->createNull(), new String_($optionName));
        }
        $args = [new Node\Arg($array)];

        $setDefaultsMethodCall = new MethodCall($resolverParam->var, new Node\Identifier('setDefaults'), $args);

        $methodBuilder = new MethodBuilder('configureOptions');
        $methodBuilder->makePublic();
        $methodBuilder->addParam($resolverParam);
        $methodBuilder->addStmt($setDefaultsMethodCall);

        return $methodBuilder->getNode();
    }

    private function createNull(): Node\Expr\ConstFetch
    {
        return new Node\Expr\ConstFetch(new Node\Name('null'));
    }
}
