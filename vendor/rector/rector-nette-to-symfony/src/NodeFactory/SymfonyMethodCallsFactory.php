<?php

declare (strict_types=1);
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
use RectorPrefix20210514\Symfony\Component\Form\Extension\Core\Type\TextType;
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
    public function __construct(\Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\Core\PhpParser\Node\NodeFactory $nodeFactory)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->nodeFactory = $nodeFactory;
    }
    /**
     * @param MethodCall[] $onFormVariableMethodCalls
     * @return Expression[]
     */
    public function create(array $onFormVariableMethodCalls, \PhpParser\Node\Expr\Variable $formBuilderVariable) : array
    {
        $symfonyMethodCalls = [];
        // create symfony form from nette form method calls
        foreach ($onFormVariableMethodCalls as $onFormVariableMethodCall) {
            if (!$this->nodeNameResolver->isName($onFormVariableMethodCall->name, 'addText')) {
                continue;
            }
            // text input
            $inputName = $onFormVariableMethodCall->args[0];
            $formTypeClassConstant = $this->nodeFactory->createClassConstReference(\RectorPrefix20210514\Symfony\Component\Form\Extension\Core\Type\TextType::class);
            $args = $this->createAddTextArgs($inputName, $formTypeClassConstant, $onFormVariableMethodCall);
            $methodCall = new \PhpParser\Node\Expr\MethodCall($formBuilderVariable, 'add', $args);
            $symfonyMethodCalls[] = new \PhpParser\Node\Stmt\Expression($methodCall);
        }
        return $symfonyMethodCalls;
    }
    /**
     * @return Arg[]
     */
    private function createAddTextArgs(\PhpParser\Node\Arg $arg, \PhpParser\Node\Expr\ClassConstFetch $classConstFetch, \PhpParser\Node\Expr\MethodCall $onFormVariableMethodCall) : array
    {
        $args = [$arg, new \PhpParser\Node\Arg($classConstFetch)];
        if (isset($onFormVariableMethodCall->args[1])) {
            $optionsArray = new \PhpParser\Node\Expr\Array_([new \PhpParser\Node\Expr\ArrayItem($onFormVariableMethodCall->args[1]->value, new \PhpParser\Node\Scalar\String_('label'))]);
            $args[] = new \PhpParser\Node\Arg($optionsArray);
        }
        return $args;
    }
}
