<?php

declare(strict_types=1);

namespace Rector\Symfony3\NodeFactory;

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
use Symplify\Astral\ValueObject\NodeBuilder\MethodBuilder;
use Symplify\Astral\ValueObject\NodeBuilder\ParamBuilder;

final class ConfigureOptionsNodeFactory
{
    /**
     * @param array<string, Arg> $namesToArgs
     */
    public function create(array $namesToArgs): ClassMethod
    {
        $resolverParam = $this->createParam();
        $args = $this->createArgs($namesToArgs);

        $setDefaultsMethodCall = new MethodCall($resolverParam->var, new Identifier('setDefaults'), $args);

        $methodBuilder = new MethodBuilder('configureOptions');
        $methodBuilder->makePublic();
        $methodBuilder->addParam($resolverParam);
        $methodBuilder->addStmt($setDefaultsMethodCall);

        return $methodBuilder->getNode();
    }

    private function createParam(): Param
    {
        $paramBuilder = new ParamBuilder('resolver');
        $paramBuilder->setType(new FullyQualified('Symfony\Component\OptionsResolver\OptionsResolver'));

        return $paramBuilder->getNode();
    }

    /**
     * @param Arg[] $namesToArgs
     * @return Arg[]
     */
    private function createArgs(array $namesToArgs): array
    {
        $array = new Array_();
        foreach (array_keys($namesToArgs) as $optionName) {
            $array->items[] = new ArrayItem($this->createNull(), new String_($optionName));
        }

        return [new Arg($array)];
    }

    private function createNull(): ConstFetch
    {
        return new ConstFetch(new Name('null'));
    }
}
