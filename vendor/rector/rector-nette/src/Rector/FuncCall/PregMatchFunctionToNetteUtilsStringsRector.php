<?php

declare (strict_types=1);
namespace Rector\Nette\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\Cast\Bool_;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\Rector\AbstractRector;
use Rector\Nette\NodeAnalyzer\PregMatchAllAnalyzer;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://tomasvotruba.com/blog/2019/02/07/what-i-learned-by-using-thecodingmachine-safe/#is-there-a-better-way
 *
 * @see \Rector\Nette\Tests\Rector\FuncCall\PregMatchFunctionToNetteUtilsStringsRector\PregMatchFunctionToNetteUtilsStringsRectorTest
 */
final class PregMatchFunctionToNetteUtilsStringsRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var array<string, string>
     */
    private const FUNCTION_NAME_TO_METHOD_NAME = ['preg_match' => 'match', 'preg_match_all' => 'matchAll'];
    /**
     * @var \Rector\Nette\NodeAnalyzer\PregMatchAllAnalyzer
     */
    private $pregMatchAllAnalyzer;
    public function __construct(\Rector\Nette\NodeAnalyzer\PregMatchAllAnalyzer $pregMatchAllAnalyzer)
    {
        $this->pregMatchAllAnalyzer = $pregMatchAllAnalyzer;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Use Nette\\Utils\\Strings over bare preg_match() and preg_match_all() functions', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $content = 'Hi my name is Tom';
        preg_match('#Hi#', $content, $matches);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Nette\Utils\Strings;

class SomeClass
{
    public function run()
    {
        $content = 'Hi my name is Tom';
        $matches = Strings::match($content, '#Hi#');
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @param FuncCall|Identical $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($node instanceof \PhpParser\Node\Expr\BinaryOp\Identical) {
            return $this->refactorIdentical($node);
        }
        return $this->refactorFuncCall($node);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\FuncCall::class, \PhpParser\Node\Expr\BinaryOp\Identical::class];
    }
    public function refactorIdentical(\PhpParser\Node\Expr\BinaryOp\Identical $identical) : ?\PhpParser\Node\Expr\Cast\Bool_
    {
        $parentNode = $identical->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        if ($identical->left instanceof \PhpParser\Node\Expr\FuncCall) {
            $refactoredFuncCall = $this->refactorFuncCall($identical->left);
            if ($refactoredFuncCall !== null && $this->valueResolver->isValue($identical->right, 1)) {
                return $this->createBoolCast($parentNode, $refactoredFuncCall);
            }
        }
        if ($identical->right instanceof \PhpParser\Node\Expr\FuncCall) {
            $refactoredFuncCall = $this->refactorFuncCall($identical->right);
            if ($refactoredFuncCall !== null && $this->valueResolver->isValue($identical->left, 1)) {
                return $this->createBoolCast($parentNode, $refactoredFuncCall);
            }
        }
        return null;
    }
    /**
     * @return FuncCall|StaticCall|Assign|null
     */
    public function refactorFuncCall(\PhpParser\Node\Expr\FuncCall $funcCall) : ?\PhpParser\Node\Expr
    {
        $methodName = $this->nodeNameResolver->matchNameFromMap($funcCall, self::FUNCTION_NAME_TO_METHOD_NAME);
        if ($methodName === null) {
            return null;
        }
        $matchStaticCall = $this->createMatchStaticCall($funcCall, $methodName);
        // skip assigns, might be used with different return value
        $parentNode = $funcCall->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        if ($parentNode instanceof \PhpParser\Node\Expr\Assign) {
            if ($methodName === 'matchAll') {
                // use count
                return new \PhpParser\Node\Expr\FuncCall(new \PhpParser\Node\Name('count'), [new \PhpParser\Node\Arg($matchStaticCall)]);
            }
            return null;
        }
        // assign
        if (isset($funcCall->args[2])) {
            return new \PhpParser\Node\Expr\Assign($funcCall->args[2]->value, $matchStaticCall);
        }
        return $matchStaticCall;
    }
    /**
     * @param Expr $expr
     */
    private function createBoolCast(?\PhpParser\Node $node, \PhpParser\Node $expr) : \PhpParser\Node\Expr\Cast\Bool_
    {
        if ($node instanceof \PhpParser\Node\Stmt\Return_ && $expr instanceof \PhpParser\Node\Expr\Assign) {
            $expr = $expr->expr;
        }
        return new \PhpParser\Node\Expr\Cast\Bool_($expr);
    }
    private function createMatchStaticCall(\PhpParser\Node\Expr\FuncCall $funcCall, string $methodName) : \PhpParser\Node\Expr\StaticCall
    {
        $args = [];
        $args[] = $funcCall->args[1];
        $args[] = $funcCall->args[0];
        $args = $this->pregMatchAllAnalyzer->compensateEnforcedFlag($methodName, $funcCall, $args);
        return $this->nodeFactory->createStaticCall('Nette\\Utils\\Strings', $methodName, $args);
    }
}
