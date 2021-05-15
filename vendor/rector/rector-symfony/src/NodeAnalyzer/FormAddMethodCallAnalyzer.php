<?php

declare (strict_types=1);
namespace Rector\Symfony\NodeAnalyzer;

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
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    /**
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(\Rector\NodeTypeResolver\NodeTypeResolver $nodeTypeResolver, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver)
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->formObjectTypes = [new \PHPStan\Type\ObjectType('Symfony\\Component\\Form\\FormBuilderInterface'), new \PHPStan\Type\ObjectType('Symfony\\Component\\Form\\FormInterface')];
    }
    public function isMatching(\PhpParser\Node\Expr\MethodCall $methodCall) : bool
    {
        if (!$this->nodeTypeResolver->isObjectTypes($methodCall->var, $this->formObjectTypes)) {
            return \false;
        }
        if (!$this->nodeNameResolver->isName($methodCall->name, 'add')) {
            return \false;
        }
        // just one argument
        if (!isset($methodCall->args[1])) {
            return \false;
        }
        return $methodCall->args[1]->value !== null;
    }
}
