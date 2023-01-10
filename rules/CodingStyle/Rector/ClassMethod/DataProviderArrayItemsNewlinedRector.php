<?php

declare (strict_types=1);
namespace Rector\CodingStyle\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodingStyle\Rector\ClassMethod\DataProviderArrayItemsNewlinedRector\DataProviderArrayItemsNewlinedRectorTest
 */
final class DataProviderArrayItemsNewlinedRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer
     */
    private $testsNodeAnalyzer;
    public function __construct(TestsNodeAnalyzer $testsNodeAnalyzer)
    {
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change data provider in PHPUnit test case to newline per item', [new CodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class ImageBinaryTest extends TestCase
{
    /**
     * @dataProvider provideData()
     */
    public function testGetBytesSize(string $content, int $number): void
    {
        // ...
    }

    public function provideData(): array
    {
        return [['content', 8], ['content123', 11]];
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class ImageBinaryTest extends TestCase
{
    /**
     * @dataProvider provideData()
     */
    public function testGetBytesSize(string $content, int $number): void
    {
        // ...
    }

    public function provideData(): array
    {
        return [
            ['content', 8],
            ['content123', 11]
        ];
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
        return [ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$node->isPublic()) {
            return null;
        }
        if (!$this->testsNodeAnalyzer->isInTestClass($node)) {
            return null;
        }
        // skip test methods
        if ($this->isName($node, 'test*')) {
            return null;
        }
        // find array in data provider - must contain a return node
        /** @var Return_[] $returns */
        $returns = $this->betterNodeFinder->findInstanceOf((array) $node->stmts, Return_::class);
        $hasChanged = \false;
        foreach ($returns as $return) {
            if (!$return->expr instanceof Array_) {
                continue;
            }
            $array = $return->expr;
            if ($array->items === []) {
                continue;
            }
            // ensure newlined printed
            $array->setAttribute(AttributeKey::NEWLINED_ARRAY_PRINT, \true);
            // invoke reprint
            $array->setAttribute(AttributeKey::ORIGINAL_NODE, null);
            $hasChanged = \true;
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
}
