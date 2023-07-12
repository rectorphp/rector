<?php

declare (strict_types=1);
namespace Rector\PHPUnit\CodeQuality\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use Rector\Core\Rector\AbstractRector;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://github.com/symfony/symfony/pull/29685/files
 * @see \Rector\PHPUnit\Tests\CodeQuality\Rector\MethodCall\UseSpecificWillMethodRector\UseSpecificWillMethodRectorTest
 */
final class UseSpecificWillMethodRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer
     */
    private $testsNodeAnalyzer;
    /**
     * @var array<string, string>
     */
    private const NESTED_METHOD_TO_RENAME_MAP = ['returnArgument' => 'willReturnArgument', 'returnCallback' => 'willReturnCallback', 'returnSelf' => 'willReturnSelf', 'returnValue' => 'willReturn', 'returnValueMap' => 'willReturnMap', 'onConsecutiveCalls' => 'willReturnOnConsecutiveCalls', 'throwException' => 'willThrowException'];
    public function __construct(TestsNodeAnalyzer $testsNodeAnalyzer)
    {
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Changes $mock->will() call to more specific method', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass extends PHPUnit\Framework\TestCase
{
    public function test()
    {
        $translator = $this->createMock('SomeClass');
        $translator->expects($this->any())
            ->method('trans')
            ->will($this->returnValue('translated max {{ max }}!'));
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass extends PHPUnit\Framework\TestCase
{
    public function test()
    {
        $translator = $this->createMock('SomeClass');
        $translator->expects($this->any())
            ->method('trans')
            ->willReturnValue('translated max {{ max }}!');
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
        return [MethodCall::class, StaticCall::class];
    }
    /**
     * @param MethodCall|StaticCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->testsNodeAnalyzer->isInTestClass($node)) {
            return null;
        }
        // we cannot check caller types, as on old PHPUnit version, this the magic ->method() call result to a mixed type
        if (!$this->isName($node->name, 'will')) {
            return null;
        }
        if ($node->isFirstClassCallable()) {
            return null;
        }
        $callArgs = $node->getArgs();
        if (!$callArgs[0]->value instanceof MethodCall) {
            return null;
        }
        $nestedMethodCall = $callArgs[0]->value;
        foreach (self::NESTED_METHOD_TO_RENAME_MAP as $oldMethodName => $newParentMethodName) {
            if (!$this->isName($nestedMethodCall->name, $oldMethodName)) {
                continue;
            }
            $node->name = new Identifier($newParentMethodName);
            // move args up
            $node->args = $nestedMethodCall->args;
            return $node;
        }
        return null;
    }
}
