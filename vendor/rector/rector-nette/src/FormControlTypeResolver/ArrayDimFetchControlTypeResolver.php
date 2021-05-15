<?php

declare (strict_types=1);
namespace Rector\Nette\FormControlTypeResolver;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Type\TypeWithClassName;
use Rector\Nette\Contract\FormControlTypeResolverInterface;
use Rector\Nette\Naming\NetteControlNaming;
use Rector\Nette\NodeAnalyzer\ControlDimFetchAnalyzer;
use Rector\NodeCollector\NodeCollector\NodeRepository;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer;
final class ArrayDimFetchControlTypeResolver implements \Rector\Nette\Contract\FormControlTypeResolverInterface
{
    /**
     * @var \Rector\Nette\NodeAnalyzer\ControlDimFetchAnalyzer
     */
    private $controlDimFetchAnalyzer;
    /**
     * @var \Rector\Nette\Naming\NetteControlNaming
     */
    private $netteControlNaming;
    /**
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    /**
     * @var \Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer
     */
    private $returnTypeInferer;
    /**
     * @var \Rector\NodeCollector\NodeCollector\NodeRepository
     */
    private $nodeRepository;
    public function __construct(\Rector\Nette\NodeAnalyzer\ControlDimFetchAnalyzer $controlDimFetchAnalyzer, \Rector\Nette\Naming\NetteControlNaming $netteControlNaming, \Rector\NodeTypeResolver\NodeTypeResolver $nodeTypeResolver, \Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer $returnTypeInferer, \Rector\NodeCollector\NodeCollector\NodeRepository $nodeRepository)
    {
        $this->controlDimFetchAnalyzer = $controlDimFetchAnalyzer;
        $this->netteControlNaming = $netteControlNaming;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->returnTypeInferer = $returnTypeInferer;
        $this->nodeRepository = $nodeRepository;
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
        $callerType = $this->nodeTypeResolver->getStaticType($arrayDimFetch->var);
        if (!$callerType instanceof \PHPStan\Type\TypeWithClassName) {
            return null;
        }
        $createComponentClassMethodName = $this->netteControlNaming->createCreateComponentClassMethodName($controlShortName);
        return $this->nodeRepository->findClassMethod($callerType->getClassName(), $createComponentClassMethodName);
    }
}
