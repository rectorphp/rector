<?php

declare (strict_types=1);
namespace Rector\PHPUnit\CodeQuality\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\PHPUnit\Tests\CodeQuality\Rector\ClassMethod\DataProviderArrayItemsNewLinedRector\DataProviderArrayItemsNewLinedRectorTest
 */
final class DataProviderArrayItemsNewLinedRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer
     */
    private $testsNodeAnalyzer;
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    public function __construct(TestsNodeAnalyzer $testsNodeAnalyzer, BetterNodeFinder $betterNodeFinder)
    {
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
        $this->betterNodeFinder = $betterNodeFinder;
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

    public static function provideData(): array
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

    public static function provideData(): array
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
        if (\strncmp($node->name->toString(), 'test', \strlen('test')) === 0) {
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
            if (!$this->shouldRePrint($array)) {
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
    private function shouldRePrint(Array_ $array) : bool
    {
        foreach ($array->items as $key => $item) {
            if (!$item instanceof ArrayItem) {
                continue;
            }
            if (!isset($array->items[$key + 1])) {
                continue;
            }
            if ($array->items[$key + 1]->getStartLine() !== $item->getEndLine()) {
                continue;
            }
            return \true;
        }
        return \false;
    }
}
