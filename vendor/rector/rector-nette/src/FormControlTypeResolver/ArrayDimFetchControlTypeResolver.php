<?php

declare (strict_types=1);
namespace Rector\Nette\FormControlTypeResolver;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Type\TypeWithClassName;
use Rector\Core\PhpParser\AstResolver;
use Rector\Nette\Contract\FormControlTypeResolverInterface;
use Rector\Nette\Naming\NetteControlNaming;
use Rector\Nette\NodeAnalyzer\ControlDimFetchAnalyzer;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer;
final class ArrayDimFetchControlTypeResolver implements \Rector\Nette\Contract\FormControlTypeResolverInterface
{
    /**
     * @readonly
     * @var \Rector\Nette\NodeAnalyzer\ControlDimFetchAnalyzer
     */
    private $controlDimFetchAnalyzer;
    /**
     * @readonly
     * @var \Rector\Nette\Naming\NetteControlNaming
     */
    private $netteControlNaming;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer
     */
    private $returnTypeInferer;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\AstResolver
     */
    private $astResolver;
    public function __construct(\Rector\Nette\NodeAnalyzer\ControlDimFetchAnalyzer $controlDimFetchAnalyzer, \Rector\Nette\Naming\NetteControlNaming $netteControlNaming, \Rector\NodeTypeResolver\NodeTypeResolver $nodeTypeResolver, \Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer $returnTypeInferer, \Rector\Core\PhpParser\AstResolver $astResolver)
    {
        $this->controlDimFetchAnalyzer = $controlDimFetchAnalyzer;
        $this->netteControlNaming = $netteControlNaming;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->returnTypeInferer = $returnTypeInferer;
        $this->astResolver = $astResolver;
    }
    /**
     * @return array<string, string>
     */
    public function resolve(\PhpParser\Node $node) : array
    {
        if (!$node instanceof \PhpParser\Node\Expr\ArrayDimFetch) {
            return [];
        }
        $controlShortName = $this->controlDimFetchAnalyzer->matchName($node);
        if ($controlShortName === null) {
            return [];
        }
        $createComponentClassMethod = $this->matchCreateComponentClassMethod($node, $controlShortName);
        if (!$createComponentClassMethod instanceof \PhpParser\Node\Stmt\ClassMethod) {
            return [];
        }
        $createComponentClassMethodReturnType = $this->returnTypeInferer->inferFunctionLike($createComponentClassMethod);
        if (!$createComponentClassMethodReturnType instanceof \PHPStan\Type\TypeWithClassName) {
            return [];
        }
        return [$controlShortName => $createComponentClassMethodReturnType->getClassName()];
    }
    private function matchCreateComponentClassMethod(\PhpParser\Node\Expr\ArrayDimFetch $arrayDimFetch, string $controlShortName) : ?\PhpParser\Node\Stmt\ClassMethod
    {
        $callerType = $this->nodeTypeResolver->getType($arrayDimFetch->var);
        if (!$callerType instanceof \PHPStan\Type\TypeWithClassName) {
            return null;
        }
        $methodName = $this->netteControlNaming->createCreateComponentClassMethodName($controlShortName);
        return $this->astResolver->resolveClassMethod($callerType->getClassName(), $methodName);
    }
}
