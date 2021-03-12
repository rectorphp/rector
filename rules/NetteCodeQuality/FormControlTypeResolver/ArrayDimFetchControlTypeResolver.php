<?php

declare(strict_types=1);

namespace Rector\NetteCodeQuality\FormControlTypeResolver;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Type\TypeWithClassName;
use Rector\NetteCodeQuality\Contract\FormControlTypeResolverInterface;
use Rector\NetteCodeQuality\Naming\NetteControlNaming;
use Rector\NetteCodeQuality\NodeAnalyzer\ControlDimFetchAnalyzer;
use Rector\NodeCollector\NodeCollector\NodeRepository;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer;

final class ArrayDimFetchControlTypeResolver implements FormControlTypeResolverInterface
{
    /**
     * @var ControlDimFetchAnalyzer
     */
    private $controlDimFetchAnalyzer;

    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    /**
     * @var NetteControlNaming
     */
    private $netteControlNaming;

    /**
     * @var ReturnTypeInferer
     */
    private $returnTypeInferer;

    /**
     * @var NodeRepository
     */
    private $nodeRepository;

    public function __construct(
        ControlDimFetchAnalyzer $controlDimFetchAnalyzer,
        NetteControlNaming $netteControlNaming,
        NodeTypeResolver $nodeTypeResolver,
        ReturnTypeInferer $returnTypeInferer,
        NodeRepository $nodeRepository
    ) {
        $this->controlDimFetchAnalyzer = $controlDimFetchAnalyzer;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->netteControlNaming = $netteControlNaming;
        $this->returnTypeInferer = $returnTypeInferer;
        $this->nodeRepository = $nodeRepository;
    }

    /**
     * @return array<string, string>
     */
    public function resolve(Node $node): array
    {
        if (! $node instanceof ArrayDimFetch) {
            return [];
        }

        $controlShortName = $this->controlDimFetchAnalyzer->matchName($node);
        if ($controlShortName === null) {
            return [];
        }

        $createComponentClassMethod = $this->matchCreateComponentClassMethod($node, $controlShortName);
        if (! $createComponentClassMethod instanceof ClassMethod) {
            return [];
        }

        $createComponentClassMethodReturnType = $this->returnTypeInferer->inferFunctionLike(
            $createComponentClassMethod
        );

        if (! $createComponentClassMethodReturnType instanceof TypeWithClassName) {
            return [];
        }

        return [
            $controlShortName => $createComponentClassMethodReturnType->getClassName(),
        ];
    }

    private function matchCreateComponentClassMethod(
        ArrayDimFetch $arrayDimFetch,
        string $controlShortName
    ): ?ClassMethod {
        $callerType = $this->nodeTypeResolver->getStaticType($arrayDimFetch->var);
        if (! $callerType instanceof TypeWithClassName) {
            return null;
        }

        $createComponentClassMethodName = $this->netteControlNaming->createCreateComponentClassMethodName(
            $controlShortName
        );

        return $this->nodeRepository->findClassMethod(
            $callerType->getClassName(),
            $createComponentClassMethodName
        );
    }
}
