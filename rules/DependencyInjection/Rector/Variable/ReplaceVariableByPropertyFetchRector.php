<?php

declare (strict_types=1);
namespace Rector\DependencyInjection\Rector\Variable;

use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Rector\DependencyInjection\Collector\VariablesToPropertyFetchCollection;
use Rector\DependencyInjection\NodeAnalyzer\ControllerClassMethodAnalyzer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DependencyInjection\Rector\Class_\ActionInjectionToConstructorInjectionRector\ActionInjectionToConstructorInjectionRectorTest
 */
final class ReplaceVariableByPropertyFetchRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var \Rector\DependencyInjection\Collector\VariablesToPropertyFetchCollection
     */
    private $variablesToPropertyFetchCollection;
    /**
     * @var \Rector\DependencyInjection\NodeAnalyzer\ControllerClassMethodAnalyzer
     */
    private $controllerClassMethodAnalyzer;
    public function __construct(\Rector\DependencyInjection\Collector\VariablesToPropertyFetchCollection $variablesToPropertyFetchCollection, \Rector\DependencyInjection\NodeAnalyzer\ControllerClassMethodAnalyzer $controllerClassMethodAnalyzer)
    {
        $this->variablesToPropertyFetchCollection = $variablesToPropertyFetchCollection;
        $this->controllerClassMethodAnalyzer = $controllerClassMethodAnalyzer;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Turns variable in controller action to property fetch, as follow up to action injection variable to property change.', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
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
        $products = $productRepository->fetchAll();
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
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
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\Variable::class];
    }
    /**
     * @param Variable $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$this->controllerClassMethodAnalyzer->isInControllerActionMethod($node)) {
            return null;
        }
        foreach ($this->variablesToPropertyFetchCollection->getVariableNamesAndTypes() as $name => $type) {
            if (!$this->isName($node, $name)) {
                continue;
            }
            /** @var ObjectType $type */
            if (!$this->isObjectType($node, $type)) {
                continue;
            }
            return $this->nodeFactory->createPropertyFetch('this', $name);
        }
        return null;
    }
}
