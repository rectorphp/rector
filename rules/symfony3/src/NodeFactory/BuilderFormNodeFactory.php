<?php

declare(strict_types=1);

namespace Rector\Symfony3\NodeFactory;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Param;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use Rector\NodeNameResolver\NodeNameResolver;
use Symplify\Astral\ValueObject\NodeBuilder\MethodBuilder;
use Symplify\Astral\ValueObject\NodeBuilder\ParamBuilder;

final class BuilderFormNodeFactory
{
    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    public function __construct(NodeNameResolver $nodeNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }

    public function create(ClassMethod $constructorClassMethod): ClassMethod
    {
        $formBuilderParam = $this->createBuilderParam();

        $optionsParam = $this->createOptionsParam();

        $classMethodBuilder = new MethodBuilder('buildForm');
        $classMethodBuilder->makePublic();
        $classMethodBuilder->addParam($formBuilderParam);
        $classMethodBuilder->addParam($optionsParam);

        // raw copy stmts from ctor
        $options = $this->replaceParameterAssignWithOptionAssign((array) $constructorClassMethod->stmts, $optionsParam);
        $classMethodBuilder->addStmts($options);

        return $classMethodBuilder->getNode();
    }

    private function createBuilderParam(): Param
    {
        $builderParamBuilder = new ParamBuilder('builder');
        $builderParamBuilder->setType(new FullyQualified('Symfony\Component\Form\FormBuilderInterface'));

        return $builderParamBuilder->getNode();
    }

    private function createOptionsParam(): Param
    {
        $optionsParamBuilder = new ParamBuilder('options');
        $optionsParamBuilder->setType('array');

        return $optionsParamBuilder->getNode();
    }

    /**
     * @param Node[] $nodes
     * @return Node[]
     *
     * $this->value = $value
     * ↓
     * $this->value = $options['value']
     */
    private function replaceParameterAssignWithOptionAssign(array $nodes, Param $param): array
    {
        foreach ($nodes as $expression) {
            if (! $expression instanceof Expression) {
                continue;
            }

            $node = $expression->expr;
            if (! $node instanceof Assign) {
                continue;
            }

            $variableName = $this->nodeNameResolver->getName($node->var);
            if ($variableName === null) {
                continue;
            }

            if ($node->expr instanceof Variable) {
                $node->expr = new ArrayDimFetch($param->var, new String_($variableName));
            }
        }

        return $nodes;
    }
}
