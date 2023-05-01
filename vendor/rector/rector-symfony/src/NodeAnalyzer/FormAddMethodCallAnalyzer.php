<?php

declare (strict_types=1);
namespace Rector\Symfony\NodeAnalyzer;

use PhpParser\Node\Expr;
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
     * @readonly
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(NodeTypeResolver $nodeTypeResolver, NodeNameResolver $nodeNameResolver)
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->formObjectTypes = [new ObjectType('Symfony\\Component\\Form\\FormBuilderInterface'), new ObjectType('Symfony\\Component\\Form\\FormInterface')];
    }
    public function isMatching(MethodCall $methodCall) : bool
    {
        if (!$this->nodeNameResolver->isName($methodCall->name, 'add')) {
            return \false;
        }
        if (!$this->nodeTypeResolver->isObjectTypes($methodCall->var, $this->formObjectTypes)) {
            return \false;
        }
        // just one argument
        $args = $methodCall->getArgs();
        if (!isset($args[1])) {
            return \false;
        }
        $firstArg = $args[1];
        return $firstArg->value instanceof Expr;
    }
}
