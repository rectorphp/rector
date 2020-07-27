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
use Rector\NodeCollector\NodeFinder\FunctionLikeParsedNodesFinder;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer;

final class ArrayDimFetchControlTypeResolver implements FormControlTypeResolverInterface
{
    /**
     * @var FunctionLikeParsedNodesFinder
     */
    private $functionLikeParsedNodesFinder;

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

    public function __construct(
        ControlDimFetchAnalyzer $controlDimFetchAnalyzer,
        FunctionLikeParsedNodesFinder $functionLikeParsedNodesFinder,
        NetteControlNaming $netteControlNaming,
        NodeTypeResolver $nodeTypeResolver,
        ReturnTypeInferer $returnTypeInferer
    ) {
        $this->functionLikeParsedNodesFinder = $functionLikeParsedNodesFinder;
        $this->controlDimFetchAnalyzer = $controlDimFetchAnalyzer;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->netteControlNaming = $netteControlNaming;
        $this->returnTypeInferer = $returnTypeInferer;
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
        if ($createComponentClassMethod === null) {
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

        return $this->functionLikeParsedNodesFinder->findClassMethod(
            $createComponentClassMethodName,
            $callerType->getClassName()
        );
    }
}
