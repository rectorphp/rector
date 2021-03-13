<?php

declare(strict_types=1);

namespace Rector\NetteToSymfony\NodeFactory;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Expression;
use Rector\Core\PhpParser\Node\NodeFactory;
use Rector\NodeNameResolver\NodeNameResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

final class SymfonyMethodCallsFactory
{
    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var NodeFactory
     */
    private $nodeFactory;

    public function __construct(NodeNameResolver $nodeNameResolver, NodeFactory $nodeFactory)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->nodeFactory = $nodeFactory;
    }

    /**
     * @param MethodCall[] $onFormVariableMethodCalls
     * @return Expression[]
     */
    public function create(array $onFormVariableMethodCalls, Variable $formBuilderVariable): array
    {
        $symfonyMethodCalls = [];

        // create symfony form from nette form method calls
        foreach ($onFormVariableMethodCalls as $onFormVariableMethodCall) {
            if (! $this->nodeNameResolver->isName($onFormVariableMethodCall->name, 'addText')) {
                continue;
            }
            // text input
            $inputName = $onFormVariableMethodCall->args[0];
            $formTypeClassConstant = $this->nodeFactory->createClassConstReference(TextType::class);

            $args = $this->createAddTextArgs($inputName, $formTypeClassConstant, $onFormVariableMethodCall);
            $methodCall = new MethodCall($formBuilderVariable, 'add', $args);

            $symfonyMethodCalls[] = new Expression($methodCall);
        }

        return $symfonyMethodCalls;
    }

    /**
     * @return Arg[]
     */
    private function createAddTextArgs(
        Arg $arg,
        ClassConstFetch $classConstFetch,
        MethodCall $onFormVariableMethodCall
    ): array {
        $args = [$arg, new Arg($classConstFetch)];

        if (isset($onFormVariableMethodCall->args[1])) {
            $optionsArray = new Array_([
                new ArrayItem($onFormVariableMethodCall->args[1]->value, new String_('label')),
            ]);

            $args[] = new Arg($optionsArray);
        }

        return $args;
    }
}
