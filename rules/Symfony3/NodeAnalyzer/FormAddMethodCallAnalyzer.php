<?php

declare(strict_types=1);

namespace Rector\Symfony3\NodeAnalyzer;

use PhpParser\Node\Expr\MethodCall;
use PHPStan\Type\ObjectType;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\NodeTypeResolver;

final class FormAddMethodCallAnalyzer
{
    /**
     * @var ObjectType[]
     */
    private $formObjectTypes = [];

    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    public function __construct(NodeTypeResolver $nodeTypeResolver, NodeNameResolver $nodeNameResolver)
    {
        $this->formObjectTypes = [
            new ObjectType('Symfony\Component\Form\FormBuilderInterface'),
            new ObjectType('Symfony\Component\Form\FormInterface'),
        ];

        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->nodeNameResolver = $nodeNameResolver;
    }

    public function matches(MethodCall $methodCall): bool
    {
        if (! $this->nodeTypeResolver->isObjectTypes($methodCall->var, $this->formObjectTypes)) {
            return false;
        }

        if (! $this->nodeNameResolver->isName($methodCall->name, 'add')) {
            return false;
        }

        // just one argument
        if (! isset($methodCall->args[1])) {
            return false;
        }

        return $methodCall->args[1]->value !== null;
    }
}
