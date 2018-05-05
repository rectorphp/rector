<?php declare(strict_types=1);

namespace Rector\Rector\Architecture\DependencyInjection;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Builder\Class_\VariableInfo;
use Rector\Configuration\Rector\Architecture\DependencyInjection\VariablesToPropertyFetchCollection;
use Rector\Node\Attribute;
use Rector\Node\PropertyFetchNodeFactory;
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

    /**
     * @var PropertyFetchNodeFactory
     */
    private $propertyFetchNodeFactory;

    public function __construct(
        VariablesToPropertyFetchCollection $variablesToPropertyFetchCollection,
        PropertyFetchNodeFactory $propertyFetchNodeFactory
    ) {
        $this->variablesToPropertyFetchCollection = $variablesToPropertyFetchCollection;
        $this->propertyFetchNodeFactory = $propertyFetchNodeFactory;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Turns variable to property fetch, as follow up to action injection variable to property change',
            [
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
            ]
        );
    }

    public function isCandidate(Node $node): bool
    {
        $this->activeVariableInfo = null;

        if (! $node instanceof Variable) {
            return false;
        }

        if (! $this->isInControllerActionMethod($node)) {
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
        return $this->propertyFetchNodeFactory->createLocalWithPropertyName($this->activeVariableInfo->getName());
    }

    private function isInControllerActionMethod(Node $node): bool
    {
        if (! Strings::endsWith((string) $node->getAttribute(Attribute::CLASS_NAME), 'Controller')) {
            return false;
        }

        /** @var ClassMethod|null $methodNode */
        $methodNode = $node->getAttribute(Attribute::METHOD_NODE);
        if ($methodNode === null) {
            return false;
        }

        // is probably in controller action
        return $methodNode->isPublic();
    }
}
