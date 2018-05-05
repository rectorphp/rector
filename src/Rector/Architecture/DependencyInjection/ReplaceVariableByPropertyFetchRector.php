<?php declare(strict_types=1);

namespace Rector\Rector\Architecture\DependencyInjection;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Builder\Class_\VariableInfo;
use Rector\Configuration\Rector\Architecture\DependencyInjection\VariablesToPropertyFetchCollection;
use Rector\Node\Attribute;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class ReplaceVariableByPropertyFetchRector extends AbstractRector
{
    /**
     * @var VariablesToPropertyFetchCollection
     */
    private $variablesToPropertyFetchCollection;

    /**
     * @var VariableInfo|null
     */
    private $activeVariableInfo;

    public function __construct(VariablesToPropertyFetchCollection $variablesToPropertyFetchCollection)
    {
        $this->variablesToPropertyFetchCollection = $variablesToPropertyFetchCollection;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Turns variable to property fetch, as follow up to action injection variable to property change', [
            new CodeSample(
                <<<'CODE_SAMPLE'
final class SomeController
{
    public function default()
    {
        $products = $productRepository->fetchAll();
    } 
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
final class SomeController
{
    public function default()
    {
        $products = $this->productRepository->fetchAll();
    } 
}
CODE_SAMPLE
            ),
        ]);
    }

    public function isCandidate(Node $node): bool
    {
        $this->activeVariableInfo = null;

        if (! $node instanceof Variable) {
            return false;
        }

        if (! Strings::endsWith((string) $node->getAttribute(Attribute::CLASS_NAME), 'Controller')) {
            return false;
        }

        /** @var ClassMethod|null $methodNode */
        $methodNode = $node->getAttribute(Attribute::METHOD_NODE);
        if ($methodNode === null) {
            return false;
        }

        // is probably in controller action
        if (! $methodNode->isPublic()) {
            return false;
        }

        foreach ($this->variablesToPropertyFetchCollection->getVariableInfos() as $variableInfo) {
            if ($node->name !== $variableInfo->getName()) {
                continue;
            }

            if ($node->getAttribute(Attribute::TYPES) === $variableInfo->getTypes()) {
                $this->activeVariableInfo = $variableInfo;
                return true;
            }
        }

        return false;
    }

    /**
     * @param Variable $variableNode
     */
    public function refactor(Node $variableNode): ?Node
    {
        // @todo NodeFactory
        return new PropertyFetch(new Variable('this'), $this->activeVariableInfo->getName());
    }
}
