<?php declare(strict_types=1);

namespace Rector\Rector\Architecture\DependencyInjection;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Bridge\Contract\ServiceTypeForNameProviderInterface;
use Rector\Builder\Class_\VariableInfo;
use Rector\Builder\Class_\VariableInfoFactory;
use Rector\Builder\ConstructorMethodBuilder;
use Rector\Builder\PropertyBuilder;
use Rector\Configuration\Rector\Architecture\DependencyInjection\VariablesToPropertyFetchCollection;
use Rector\Node\Attribute;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class ActionInjectionToConstructorInjectionRector extends AbstractRector
{
    /**
     * @var PropertyBuilder
     */
    private $propertyBuilder;

    /**
     * @var ConstructorMethodBuilder
     */
    private $constructorMethodBuilder;

    /**
     * @var VariableInfoFactory
     */
    private $variableInfoFactory;

    /**
     * @var VariablesToPropertyFetchCollection
     */
    private $variablesToPropertyFetchCollection;

    /**
     * @var ServiceTypeForNameProviderInterface
     */
    private $serviceTypeForNameProvider;

    public function __construct(
        PropertyBuilder $propertyBuilder,
        ConstructorMethodBuilder $constructorMethodBuilder,
        VariableInfoFactory $variableInfoFactory,
        VariablesToPropertyFetchCollection $variablesToPropertyFetchCollection,
        ServiceTypeForNameProviderInterface $serviceTypeForNameProvider
    ) {
        $this->propertyBuilder = $propertyBuilder;
        $this->constructorMethodBuilder = $constructorMethodBuilder;
        $this->variableInfoFactory = $variableInfoFactory;
        $this->variablesToPropertyFetchCollection = $variablesToPropertyFetchCollection;
        $this->serviceTypeForNameProvider = $serviceTypeForNameProvider;
    }

    public function isCandidate(Node $node): bool
    {
        if (! $node instanceof Class_) {
            return false;
        }

        return Strings::endsWith((string) $node->name, 'Controller');
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Turns action injection in Controllers to constructor injection', [
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
        ]);
    }

    /**
     * @param Class_ $classNode
     */
    public function refactor(Node $classNode): ?Node
    {
        foreach ($classNode->stmts as $stmt) {
            if (! $stmt instanceof ClassMethod) {
                continue;
            }

            $this->processClassMethod($classNode, $stmt);
        }

        return $classNode;
    }

    private function processClassMethod(Class_ $classNode, ClassMethod $classMethodNode): void
    {
        foreach ($classMethodNode->params as $key => $paramNode) {
            if (! $this->isActionInjectedParamNode($paramNode)) {
                continue;
            }

            $variableInfo = $this->variableInfoFactory->createFromNameAndTypes(
                $paramNode->var->name,
                [(string) $paramNode->type]
            );

            $this->addConstructorDependencyToClassNode($classNode, $variableInfo);

            // remove arguments
            unset($classMethodNode->params[$key]);

            $this->variablesToPropertyFetchCollection->addVariableInfo($variableInfo);
        }
    }

    private function isActionInjectedParamNode(Param $paramNode): bool
    {
        $typehint = (string) $paramNode->type;

        if (empty($typehint)) {
            return false;
        }

        $typehint = (string) $paramNode->getAttribute(Attribute::TYPES)[0] ?? null;
        if ($typehint === null) {
            return false;
        }

        dump($typehint);
        $hasService = (bool) $this->serviceTypeForNameProvider->provideTypeForName($typehint);
        dump($hasService);

//
//        $serviceType = $this->serviceTypeForNameProvider->provideTypeForName($typehint);
//        dump($serviceType);
        die;

        if (Strings::endsWith($typehint, 'Request')) {
            return false;
        }

        // skip non-classy types
        if (! ctype_upper($typehint[0])) {
            return false;
        }

        return true;
    }

    private function addConstructorDependencyToClassNode(Class_ $classNode, VariableInfo $variableInfo): void
    {
        // add property
        $this->propertyBuilder->addPropertyToClass($classNode, $variableInfo);

        // pass via constructor
        $this->constructorMethodBuilder->addSimplePropertyAssignToClass($classNode, $variableInfo);
    }
}
