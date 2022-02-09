<?php

declare (strict_types=1);
namespace Rector\Nette\Rector\FuncCall;

use RectorPrefix20220209\Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp\BitwiseAnd;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BitwiseNot;
use PhpParser\Node\Expr\Cast\Bool_;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://tomasvotruba.com/blog/2019/02/07/what-i-learned-by-using-thecodingmachine-safe/#is-there-a-better-way
 *
 * @see \Rector\Nette\Tests\Rector\FuncCall\PregFunctionToNetteUtilsStringsRector\PregFunctionToNetteUtilsStringsRectorTest
 */
final class PregFunctionToNetteUtilsStringsRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var array<string, string>
     */
    private const FUNCTION_NAME_TO_METHOD_NAME = ['preg_split' => 'split', 'preg_replace' => 'replace', 'preg_replace_callback' => 'replace'];
    /**
     * @see https://regex101.com/r/05MPWa/1/
     * @var string
     */
    private const SLASH_REGEX = '#[^\\\\]\\(#';
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Use Nette\\Utils\\Strings over bare preg_split() and preg_replace() functions', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $content = 'Hi my name is Tom';
        $splitted = preg_split('#Hi#', $content);
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
        $splitted = \Nette\Utils\Strings::split($content, '#Hi#');
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
        $parent = $identical->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        if (!$parent instanceof \PhpParser\Node) {
            return null;
        }
        if ($identical->left instanceof \PhpParser\Node\Expr\FuncCall) {
            $refactoredFuncCall = $this->refactorFuncCall($identical->left);
            if ($refactoredFuncCall !== null && $this->valueResolver->isValue($identical->right, 1)) {
                return $this->createBoolCast($parent, $refactoredFuncCall);
            }
        }
        if ($identical->right instanceof \PhpParser\Node\Expr\FuncCall) {
            $refactoredFuncCall = $this->refactorFuncCall($identical->right);
            if ($refactoredFuncCall !== null && $this->valueResolver->isValue($identical->left, 1)) {
                return new \PhpParser\Node\Expr\Cast\Bool_($refactoredFuncCall);
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
            if ($methodName === 'split') {
                return $this->processSplit($funcCall, $matchStaticCall);
            }
            if ($methodName === 'replace') {
                return $matchStaticCall;
            }
            return null;
        }
        $currentFunctionName = $this->getName($funcCall);
        // assign
        if (isset($funcCall->args[2]) && $currentFunctionName !== 'preg_replace') {
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
        if ($methodName === 'replace') {
            $args[] = $funcCall->args[2];
            $args[] = $funcCall->args[0];
            $args[] = $funcCall->args[1];
        } else {
            $args[] = $funcCall->args[1];
            $args[] = $funcCall->args[0];
        }
        return $this->nodeFactory->createStaticCall('Nette\\Utils\\Strings', $methodName, $args);
    }
    /**
     * @return FuncCall|StaticCall
     */
    private function processSplit(\PhpParser\Node\Expr\FuncCall $funcCall, \PhpParser\Node\Expr\StaticCall $matchStaticCall) : \PhpParser\Node\Expr
    {
        $this->compensateNetteUtilsSplitDelimCapture($matchStaticCall);
        if (!isset($funcCall->args[2])) {
            return $matchStaticCall;
        }
        if ($this->valueResolver->isValue($funcCall->args[2]->value, -1)) {
            if (isset($funcCall->args[3])) {
                $matchStaticCall->args[] = $funcCall->args[3];
            }
            return $matchStaticCall;
        }
        return $funcCall;
    }
    /**
     * Handles https://github.com/rectorphp/rector/issues/2348
     */
    private function compensateNetteUtilsSplitDelimCapture(\PhpParser\Node\Expr\StaticCall $staticCall) : void
    {
        $patternValue = $this->valueResolver->getValue($staticCall->args[1]->value);
        if (!\is_string($patternValue)) {
            return;
        }
        $match = \RectorPrefix20220209\Nette\Utils\Strings::match($patternValue, self::SLASH_REGEX);
        if ($match === null) {
            return;
        }
        $constFetch = new \PhpParser\Node\Expr\ConstFetch(new \PhpParser\Node\Name('PREG_SPLIT_DELIM_CAPTURE'));
        $bitwiseAnd = new \PhpParser\Node\Expr\BinaryOp\BitwiseAnd(new \PhpParser\Node\Scalar\LNumber(0), new \PhpParser\Node\Expr\BitwiseNot($constFetch));
        $staticCall->args[2] = new \PhpParser\Node\Arg($bitwiseAnd);
    }
}
