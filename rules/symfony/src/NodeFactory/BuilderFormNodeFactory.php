<?php

declare(strict_types=1);

namespace Rector\Symfony\NodeFactory;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Param;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use Rector\Core\PhpParser\Builder\MethodBuilder;
use Rector\Core\PhpParser\Builder\ParamBuilder;
use Rector\NodeNameResolver\NodeNameResolver;

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
        $formBuilderParamBuilder = new ParamBuilder('builder');
        $formBuilderParamBuilder->setType(new FullyQualified('Symfony\Component\Form\FormBuilderInterface'));

        $formBuilderParam = $formBuilderParamBuilder->getNode();

        $optionsParamBuilder = new ParamBuilder('options');
        $optionsParamBuilder->setType('array');

        $optionsParam = $optionsParamBuilder->getNode();

        $buildFormClassMethodBuilder = new MethodBuilder('buildForm');
        $buildFormClassMethodBuilder->makePublic();
        $buildFormClassMethodBuilder->addParam($formBuilderParam);
        $buildFormClassMethodBuilder->addParam($optionsParam);

        // raw copy stmts from ctor
        $buildFormClassMethodBuilder->addStmts(
            $this->replaceParameterAssignWithOptionAssign((array) $constructorClassMethod->stmts, $optionsParam)
        );

        return $buildFormClassMethodBuilder->getNode();
    }

    /**
     * @param Node[] $nodes
     * @return Node[] $this->value = $value
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
