<?php

declare (strict_types=1);
namespace Rector\PHPUnit\PHPUnit70\Rector\Class_;

use RectorPrefix202506\Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\Class_;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\PHPUnit\NodeFinder\DataProviderClassMethodFinder;
use Rector\PHPUnit\PhpDoc\DataProviderMethodRenamer;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://stackoverflow.com/a/46693675/1348344
 *
 * @see \Rector\PHPUnit\Tests\PHPUnit70\Rector\Class_\RemoveDataProviderTestPrefixRector\RemoveDataProviderTestPrefixRectorTest
 */
final class RemoveDataProviderTestPrefixRector extends AbstractRector
{
    /**
     * @readonly
     */
    private TestsNodeAnalyzer $testsNodeAnalyzer;
    /**
     * @readonly
     */
    private DataProviderClassMethodFinder $dataProviderClassMethodFinder;
    /**
     * @readonly
     */
    private DataProviderMethodRenamer $dataProviderMethodRenamer;
    public function __construct(TestsNodeAnalyzer $testsNodeAnalyzer, DataProviderClassMethodFinder $dataProviderClassMethodFinder, DataProviderMethodRenamer $dataProviderMethodRenamer)
    {
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
        $this->dataProviderClassMethodFinder = $dataProviderClassMethodFinder;
        $this->dataProviderMethodRenamer = $dataProviderMethodRenamer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Data provider methods cannot start with "test" prefix', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass extends PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider testProvideData()
     */
    public function test()
    {
        $nothing = 5;
    }

    public function testProvideData()
    {
        return ['123'];
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass extends PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test()
    {
        $nothing = 5;
    }

    public function provideData()
    {
        return ['123'];
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
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->testsNodeAnalyzer->isInTestClass($node)) {
            return null;
        }
        $hasChanged = \false;
        $dataProviderClassMethods = $this->dataProviderClassMethodFinder->find($node);
        foreach ($dataProviderClassMethods as $dataProviderClassMethod) {
            $dataProviderClassMethodName = $dataProviderClassMethod->name->toString();
            if (\strncmp($dataProviderClassMethodName, 'test', \strlen('test')) !== 0) {
                continue;
            }
            $shortMethodName = Strings::substring($dataProviderClassMethodName, 4);
            $shortMethodName = \lcfirst($shortMethodName);
            $dataProviderClassMethod->name = new Identifier($shortMethodName);
            $hasChanged = \true;
        }
        $this->dataProviderMethodRenamer->removeTestPrefix($node);
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
}
