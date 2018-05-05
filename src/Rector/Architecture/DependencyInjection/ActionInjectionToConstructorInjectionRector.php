<?php declare(strict_types=1);

namespace Rector\Rector\Architecture\DependencyInjection;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Builder\Class_\VariableInfoFactory;
use Rector\Builder\ConstructorMethodBuilder;
use Rector\Builder\PropertyBuilder;
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

    public function __construct(
        PropertyBuilder $propertyBuilder,
        ConstructorMethodBuilder $constructorMethodBuilder,
        VariableInfoFactory $variableInfoFactory
    ) {
        $this->propertyBuilder = $propertyBuilder;
        $this->constructorMethodBuilder = $constructorMethodBuilder;
        $this->variableInfoFactory = $variableInfoFactory;
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
        foreach ($classMethodNode->params as $paramNode) {
            if (! $this->isActionInjectedParamNode($paramNode)) {
                continue;
            }

            $propertyInfo = $this->variableInfoFactory->createFromNameAndTypes(
                $paramNode->var->name,
                [(string) $paramNode->type]
            );

            // add property
            $this->propertyBuilder->addPropertyToClass($classNode, $propertyInfo);

            // pass via constructor
            $this->constructorMethodBuilder->addSimplePropertyAssignToClass($classNode, $propertyInfo);
        }
    }

    private function isActionInjectedParamNode(Param $paramNode): bool
    {
        $typehint = (string) $paramNode->type;

        if (empty($typehint)) {
            return false;
        }

        if (Strings::endsWith($typehint, 'Request')) {
            return false;
        }

        // skip non-classy types
        if (! ctype_upper($typehint[0])) {
            return false;
        }

        return true;
    }
}
