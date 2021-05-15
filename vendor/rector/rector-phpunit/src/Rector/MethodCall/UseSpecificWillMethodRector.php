<?php

declare (strict_types=1);
namespace Rector\PHPUnit\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://github.com/FriendsOfPHP/PHP-CS-Fixer/issues/4160
 * @see https://github.com/symfony/symfony/pull/29685/files
 * @see \Rector\PHPUnit\Tests\Rector\MethodCall\UseSpecificWillMethodRector\UseSpecificWillMethodRectorTest
 */
final class UseSpecificWillMethodRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var array<string, string>
     */
    private const NESTED_METHOD_TO_RENAME_MAP = ['returnArgument' => 'willReturnArgument', 'returnCallback' => 'willReturnCallback', 'returnSelf' => 'willReturnSelf', 'returnValue' => 'willReturn', 'returnValueMap' => 'willReturnMap', 'throwException' => 'willThrowException'];
    /**
     * @var \Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer
     */
    private $testsNodeAnalyzer;
    public function __construct(\Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer $testsNodeAnalyzer)
    {
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Changes ->will($this->xxx()) to one specific method', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeClass extends PHPUnit\Framework\TestCase
{
    public function test()
    {
        $translator = $this->getMockBuilder('Symfony\Component\Translation\TranslatorInterface')->getMock();
        $translator->expects($this->any())
            ->method('trans')
            ->with($this->equalTo('old max {{ max }}!'))
            ->will($this->returnValue('translated max {{ max }}!'));
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass extends PHPUnit\Framework\TestCase
{
    public function test()
    {
        $translator = $this->getMockBuilder('Symfony\Component\Translation\TranslatorInterface')->getMock();
        $translator->expects($this->any())
            ->method('trans')
            ->with('old max {{ max }}!')
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
        return [\PhpParser\Node\Expr\MethodCall::class, \PhpParser\Node\Expr\StaticCall::class];
    }
    /**
     * @param MethodCall|StaticCall $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$this->testsNodeAnalyzer->isInTestClass($node)) {
            return null;
        }
        $callerNode = $node instanceof \PhpParser\Node\Expr\StaticCall ? $node->class : $node->var;
        if (!$this->isObjectType($callerNode, new \PHPStan\Type\ObjectType('PHPUnit\\Framework\\MockObject\\Builder\\InvocationMocker'))) {
            return null;
        }
        if ($this->isName($node->name, 'with')) {
            return $this->processWithCall($node);
        }
        if ($this->isName($node->name, 'will')) {
            return $this->processWillCall($node);
        }
        return null;
    }
    /**
     * @param MethodCall|StaticCall $node
     * @return MethodCall|StaticCall
     */
    private function processWithCall(\PhpParser\Node $node) : \PhpParser\Node
    {
        foreach ($node->args as $i => $argNode) {
            if (!$argNode->value instanceof \PhpParser\Node\Expr\MethodCall) {
                continue;
            }
            $methodCall = $argNode->value;
            if (!$this->isName($methodCall->name, 'equalTo')) {
                continue;
            }
            $node->args[$i] = $methodCall->args[0];
        }
        return $node;
    }
    /**
     * @param MethodCall|StaticCall $node
     * @return MethodCall|StaticCall|null
     */
    private function processWillCall(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$node->args[0]->value instanceof \PhpParser\Node\Expr\MethodCall) {
            return null;
        }
        $nestedMethodCall = $node->args[0]->value;
        foreach (self::NESTED_METHOD_TO_RENAME_MAP as $oldMethodName => $newParentMethodName) {
            if (!$this->isName($nestedMethodCall->name, $oldMethodName)) {
                continue;
            }
            $node->name = new \PhpParser\Node\Identifier($newParentMethodName);
            // move args up
            $node->args = $nestedMethodCall->args;
            return $node;
        }
        return null;
    }
}
