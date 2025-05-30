<?php

declare (strict_types=1);
namespace Rector\Symfony\Symfony53\Rector\StaticPropertyFetch;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Name;
use Rector\Rector\AbstractRector;
use Rector\Symfony\NodeAnalyzer\SymfonyTestCaseAnalyzer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Symfony\Tests\Symfony53\Rector\StaticPropertyFetch\KernelTestCaseContainerPropertyDeprecationRector\KernelTestCaseContainerPropertyDeprecationRectorTest
 */
final class KernelTestCaseContainerPropertyDeprecationRector extends AbstractRector
{
    /**
     * @readonly
     */
    private SymfonyTestCaseAnalyzer $symfonyTestCaseAnalyzer;
    public function __construct(SymfonyTestCaseAnalyzer $symfonyTestCaseAnalyzer)
    {
        $this->symfonyTestCaseAnalyzer = $symfonyTestCaseAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Simplify use of assertions in WebTestCase', [new CodeSample(<<<'CODE_SAMPLE'
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class SomeTest extends KernelTestCase
{
    protected function setUp(): void
    {
        $container = self::$container;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class SomeTest extends KernelTestCase
{
    protected function setUp(): void
    {
        $container = self::getContainer();
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
        return [StaticPropertyFetch::class];
    }
    /**
     * @param StaticPropertyFetch $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->symfonyTestCaseAnalyzer->isInKernelTestCase($node)) {
            return null;
        }
        if ($this->getName($node->name) !== 'container') {
            return null;
        }
        if (!$node->class instanceof Name || (string) $node->class !== 'self') {
            return null;
        }
        return $this->nodeFactory->createStaticCall('self', 'getContainer');
    }
}
