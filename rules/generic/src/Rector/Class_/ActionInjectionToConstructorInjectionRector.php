<?php

declare(strict_types=1);

namespace Rector\Generic\Rector\Class_;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Type\ObjectType;
use Rector\Core\Configuration\Collector\VariablesToPropertyFetchCollection;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Symfony\ServiceMapProvider;

/**
 * @see \Rector\Generic\Tests\Rector\Class_\ActionInjectionToConstructorInjectionRector\ActionInjectionToConstructorInjectionRectorTest
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

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
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
        if (! Strings::endsWith((string) $node->name, 'Controller')) {
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

            $this->removeParam($classMethod, $key);

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
