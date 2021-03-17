<?php

declare(strict_types=1);

namespace Rector\NetteToSymfony;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Param;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Expression;
use Rector\Core\PhpParser\Node\NodeFactory;
use Rector\NodeNameResolver\NodeNameResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

final class SymfonyFormAbstractTypeFactory
{
    /**
     * @var NodeFactory
     */
    private $nodeFactory;

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    public function __construct(NodeFactory $nodeFactory, NodeNameResolver $nodeNameResolver)
    {
        $this->nodeFactory = $nodeFactory;
        $this->nodeNameResolver = $nodeNameResolver;
    }

    /**
     * @api
     * @param MethodCall[] $methodCalls
     */
    public function createFromNetteFormMethodCalls(array $methodCalls): Class_
    {
        $formBuilderVariable = new Variable('formBuilder');

        // public function buildForm(\Symfony\Component\Form\FormBuilderInterface $formBuilder, array $options)
        $buildFormClassMethod = $this->nodeFactory->createPublicMethod('buildForm');
        $buildFormClassMethod->params[] = new Param($formBuilderVariable, null, new FullyQualified(
            'Symfony\Component\Form\FormBuilderInterface'
        ));
        $buildFormClassMethod->params[] = new Param(new Variable('options'), null, new Identifier('array'));

        $symfonyMethodCalls = $this->createBuildFormMethodCalls($methodCalls, $formBuilderVariable);

        $buildFormClassMethod->stmts = $symfonyMethodCalls;

        $formTypeClass = new Class_('SomeFormType');
        $formTypeClass->extends = new FullyQualified('Symfony\Component\Form\AbstractType');

        $formTypeClass->stmts[] = $buildFormClassMethod;

        return $formTypeClass;
    }

    /**
     * @param MethodCall[] $methodCalls
     * @return Expression[]
     */
    private function createBuildFormMethodCalls(array $methodCalls, Variable $formBuilderVariable): array
    {
        $buildFormMethodCalls = [];

        // create symfony form from nette form method calls
        foreach ($methodCalls as $methodCall) {
            if ($this->nodeNameResolver->isName($methodCall->name, 'addText')) {
                $optionsArray = $this->createOptionsArray($methodCall);

                $formTypeClassConstant = $this->nodeFactory->createClassConstReference(TextType::class);

                $args = [$methodCall->args[0], new Arg($formTypeClassConstant)];

                if ($optionsArray instanceof Array_) {
                    $args[] = new Arg($optionsArray);
                }

                $methodCall = new MethodCall($formBuilderVariable, 'add', $args);
                $buildFormMethodCalls[] = new Expression($methodCall);
            }
        }

        return $buildFormMethodCalls;
    }

    private function createOptionsArray(MethodCall $methodCall): ?Array_
    {
        if (! isset($methodCall->args[1])) {
            return null;
        }

        return new Array_([new ArrayItem($methodCall->args[1]->value, new String_('label'))]);
    }
}
