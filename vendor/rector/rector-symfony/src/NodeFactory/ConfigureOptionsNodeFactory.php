<?php

declare (strict_types=1);
namespace Rector\Symfony\NodeFactory;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Param;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\ClassMethod;
use RectorPrefix20211020\Symplify\Astral\ValueObject\NodeBuilder\MethodBuilder;
use RectorPrefix20211020\Symplify\Astral\ValueObject\NodeBuilder\ParamBuilder;
final class ConfigureOptionsNodeFactory
{
    /**
     * @param array<string, Arg> $namesToArgs
     */
    public function create(array $namesToArgs) : \PhpParser\Node\Stmt\ClassMethod
    {
        $resolverParam = $this->createParam();
        $args = $this->createArgs($namesToArgs);
        $setDefaultsMethodCall = new \PhpParser\Node\Expr\MethodCall($resolverParam->var, new \PhpParser\Node\Identifier('setDefaults'), $args);
        $methodBuilder = new \RectorPrefix20211020\Symplify\Astral\ValueObject\NodeBuilder\MethodBuilder('configureOptions');
        $methodBuilder->makePublic();
        $methodBuilder->addParam($resolverParam);
        $methodBuilder->addStmt($setDefaultsMethodCall);
        return $methodBuilder->getNode();
    }
    private function createParam() : \PhpParser\Node\Param
    {
        $paramBuilder = new \RectorPrefix20211020\Symplify\Astral\ValueObject\NodeBuilder\ParamBuilder('resolver');
        $paramBuilder->setType(new \PhpParser\Node\Name\FullyQualified('Symfony\\Component\\OptionsResolver\\OptionsResolver'));
        return $paramBuilder->getNode();
    }
    /**
     * @param Arg[] $namesToArgs
     * @return Arg[]
     */
    private function createArgs(array $namesToArgs) : array
    {
        $array = new \PhpParser\Node\Expr\Array_();
        foreach (\array_keys($namesToArgs) as $optionName) {
            $array->items[] = new \PhpParser\Node\Expr\ArrayItem($this->createNull(), new \PhpParser\Node\Scalar\String_($optionName));
        }
        return [new \PhpParser\Node\Arg($array)];
    }
    private function createNull() : \PhpParser\Node\Expr\ConstFetch
    {
        return new \PhpParser\Node\Expr\ConstFetch(new \PhpParser\Node\Name('null'));
    }
}
