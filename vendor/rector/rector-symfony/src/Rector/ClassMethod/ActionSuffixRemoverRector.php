<?php

declare (strict_types=1);
namespace Rector\Symfony\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\Rector\AbstractRector;
use Rector\Renaming\NodeManipulator\IdentifierManipulator;
use Rector\Symfony\Bridge\NodeAnalyzer\ControllerMethodAnalyzer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Symfony\Tests\Rector\ClassMethod\ActionSuffixRemoverRector\ActionSuffixRemoverRectorTest
 */
final class ActionSuffixRemoverRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var \Rector\Symfony\Bridge\NodeAnalyzer\ControllerMethodAnalyzer
     */
    private $controllerMethodAnalyzer;
    /**
     * @var \Rector\Renaming\NodeManipulator\IdentifierManipulator
     */
    private $identifierManipulator;
    public function __construct(\Rector\Symfony\Bridge\NodeAnalyzer\ControllerMethodAnalyzer $controllerMethodAnalyzer, \Rector\Renaming\NodeManipulator\IdentifierManipulator $identifierManipulator)
    {
        $this->controllerMethodAnalyzer = $controllerMethodAnalyzer;
        $this->identifierManipulator = $identifierManipulator;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Removes Action suffixes from methods in Symfony Controllers', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeController
{
    public function indexAction()
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeController
{
    public function index()
    {
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
        return [\PhpParser\Node\Stmt\ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$this->controllerMethodAnalyzer->isAction($node)) {
            return null;
        }
        $this->identifierManipulator->removeSuffix($node, 'Action');
        return $node;
    }
}
