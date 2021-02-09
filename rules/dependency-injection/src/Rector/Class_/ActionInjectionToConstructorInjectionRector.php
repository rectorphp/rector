<?php

declare(strict_types=1);

namespace Rector\DependencyInjection\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Rector\DependencyInjection\Collector\VariablesToPropertyFetchCollection;
use Rector\Symfony\ServiceMapProvider;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\DependencyInjection\Tests\Rector\Class_\ActionInjectionToConstructorInjectionRector\ActionInjectionToConstructorInjectionRectorTest
 */
final class ActionInjectionToConstructorInjectionRector extends AbstractRector
{
    /**
     * @var VariablesToPropertyFetchCollection
     */
    private $variablesToPropertyFetchCollection;

    /**
     * @var ServiceMapProvider
     */
    private $applicationServiceMapProvider;

    public function __construct(
        ServiceMapProvider $applicationServiceMapProvider,
        VariablesToPropertyFetchCollection $variablesToPropertyFetchCollection
    ) {
        $this->variablesToPropertyFetchCollection = $variablesToPropertyFetchCollection;
        $this->applicationServiceMapProvider = $applicationServiceMapProvider;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Turns action injection in Controllers to constructor injection',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
final class SomeController
{
    public function default(ProductRepository $productRepository)
    {
        $products = $productRepository->fetchAll();
    }
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
final class SomeController
{
    /**
     * @var ProductRepository
     */
    private $productRepository;
    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    public function default()
    {
        $products = $this->productRepository->fetchAll();
    }
}
CODE_SAMPLE
                ),
            ]
        );
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }

    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isName($node, '*Controller')) {
            return null;
        }

        foreach ($node->getMethods() as $classMethod) {
            $this->processClassMethod($node, $classMethod);
        }

        return $node;
    }

    private function processClassMethod(Class_ $class, ClassMethod $classMethod): void
    {
        foreach ($classMethod->params as $key => $paramNode) {
            if (! $this->isActionInjectedParamNode($paramNode)) {
                continue;
            }

            $paramNodeType = $this->getObjectType($paramNode);

            /** @var string $paramName */
            $paramName = $this->getName($paramNode->var);
            $this->addConstructorDependencyToClass($class, $paramNodeType, $paramName);

            $this->nodeRemover->removeParam($classMethod, $key);

            $this->variablesToPropertyFetchCollection->addVariableNameAndType($paramName, $paramNodeType);
        }
    }

    private function isActionInjectedParamNode(Param $param): bool
    {
        if ($param->type === null) {
            return false;
        }

        $typehint = $this->getName($param->type);
        if ($typehint === null) {
            return false;
        }

        $paramStaticType = $this->getObjectType($param);
        if (! $paramStaticType instanceof ObjectType) {
            return false;
        }

        $serviceMap = $this->applicationServiceMapProvider->provide();

        return $serviceMap->hasService($paramStaticType->getClassName());
    }
}
