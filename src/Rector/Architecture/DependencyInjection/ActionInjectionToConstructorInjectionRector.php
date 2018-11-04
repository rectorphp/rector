<?php declare(strict_types=1);

namespace Rector\Rector\Architecture\DependencyInjection;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Bridge\Contract\AnalyzedApplicationContainerInterface;
use Rector\Configuration\Rector\Architecture\DependencyInjection\VariablesToPropertyFetchCollection;
use Rector\PhpParser\Node\Builder\VariableInfo;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class ActionInjectionToConstructorInjectionRector extends AbstractRector
{
    /**
     * @var VariablesToPropertyFetchCollection
     */
    private $variablesToPropertyFetchCollection;

    /**
     * @var AnalyzedApplicationContainerInterface
     */
    private $analyzedApplicationContainer;

    public function __construct(
        VariablesToPropertyFetchCollection $variablesToPropertyFetchCollection,
        AnalyzedApplicationContainerInterface $analyzedApplicationContainer
    ) {
        $this->variablesToPropertyFetchCollection = $variablesToPropertyFetchCollection;
        $this->analyzedApplicationContainer = $analyzedApplicationContainer;
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

        foreach ($node->stmts as $stmt) {
            if ($stmt instanceof ClassMethod) {
                $this->processClassMethod($node, $stmt);
            }
        }

        return $node;
    }

    private function processClassMethod(Class_ $classNode, ClassMethod $classMethodNode): void
    {
        foreach ($classMethodNode->params as $key => $paramNode) {
            if (! $this->isActionInjectedParamNode($paramNode)) {
                continue;
            }

            $paramNodeTypes = $this->getTypes($paramNode);

            $variableInfo = new VariableInfo($this->getName($paramNode->var), $paramNodeTypes);
            $this->addConstructorDependency($classNode, $variableInfo);

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

        $typehint = $this->getTypes($paramNode)[0] ?? null;
        if ($typehint === null) {
            return false;
        }

        // skip non-classy types
        if (! ctype_upper($typehint[0])) {
            return false;
        }

        /** @var string $typehint */
        return $this->analyzedApplicationContainer->hasService($typehint);
    }
}
