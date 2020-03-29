<?php

declare(strict_types=1);

namespace Rector\Nette\Rector\FuncCall;

use Nette\Utils\Strings;
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
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see https://www.tomasvotruba.cz/blog/2019/02/07/what-i-learned-by-using-thecodingmachine-safe/#is-there-a-better-way
 *
 * @see \Rector\Nette\Tests\Rector\FuncCall\PregFunctionToNetteUtilsStringsRector\PregFunctionToNetteUtilsStringsRectorTest
 */
final class PregFunctionToNetteUtilsStringsRector extends AbstractRector
{
    /**
     * @var string[]
     */
    private const FUNCTION_NAME_TO_METHOD_NAME = [
        'preg_match' => 'match',
        'preg_match_all' => 'matchAll',
        'preg_split' => 'split',
        'preg_replace' => 'replace',
        'preg_replace_callback' => 'replace',
    ];

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Use Nette\Utils\Strings over bare preg_* functions', [
            new CodeSample(
                <<<'PHP'
class SomeClass
{
    public function run()
    {
        $content = 'Hi my name is Tom';
        preg_match('#Hi#', $content, $matches);
    }
}
PHP
                ,
                <<<'PHP'
use Nette\Utils\Strings;

class SomeClass
{
    public function run()
    {
        $content = 'Hi my name is Tom';
        $matches = Strings::match($content, '#Hi#');
    }
}
PHP
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [FuncCall::class, Identical::class];
    }

    /**
     * @param FuncCall|Identical $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node instanceof Identical) {
            return $this->refactorIdentical($node);
        }

        return $this->refactorFuncCall($node);
    }

    private function refactorIdentical(Identical $identical): ?Bool_
    {
        $parentNode = $identical->getAttribute(AttributeKey::PARENT_NODE);

        if ($identical->left instanceof FuncCall) {
            $refactoredFuncCall = $this->refactorFuncCall($identical->left);
            if ($refactoredFuncCall !== null && $this->isValue($identical->right, 1)) {
                return $this->createBoolCast($parentNode, $refactoredFuncCall);
            }
        }

        if ($identical->right instanceof FuncCall) {
            $refactoredFuncCall = $this->refactorFuncCall($identical->right);
            if ($refactoredFuncCall !== null && $this->isValue($identical->left, 1)) {
                return new Bool_($refactoredFuncCall);
            }
        }

        return null;
    }

    /**
     * @return FuncCall|StaticCall|Assign|null
     */
    private function refactorFuncCall(FuncCall $funcCall): ?Expr
    {
        if (! $this->isNames($funcCall, array_keys(self::FUNCTION_NAME_TO_METHOD_NAME))) {
            return null;
        }

        $currentFunctionName = $this->getName($funcCall);

        $methodName = self::FUNCTION_NAME_TO_METHOD_NAME[$currentFunctionName];
        $matchStaticCall = $this->createMatchStaticCall($funcCall, $methodName);

        // skip assigns, might be used with different return value
        $parentNode = $funcCall->getAttribute(AttributeKey::PARENT_NODE);
        if ($parentNode instanceof Assign) {
            if ($methodName === 'matchAll') {
                // use count
                return new FuncCall(new Name('count'), [new Arg($matchStaticCall)]);
            }

            if ($methodName === 'split') {
                return $this->processSplit($funcCall, $matchStaticCall);
            }

            if ($methodName === 'replace') {
                return $matchStaticCall;
            }

            return null;
        }

        // assign
        if (isset($funcCall->args[2]) && $currentFunctionName !== 'preg_replace') {
            return new Assign($funcCall->args[2]->value, $matchStaticCall);
        }

        return $matchStaticCall;
    }

    /**
     * @param FuncCall|StaticCall|Assign $refactoredFuncCall
     */
    private function createBoolCast(?Node $parentNode, Node $refactoredFuncCall): Bool_
    {
        if ($parentNode instanceof Return_ && $refactoredFuncCall instanceof Assign) {
            $refactoredFuncCall = $refactoredFuncCall->expr;
        }

        return new Bool_($refactoredFuncCall);
    }

    private function createMatchStaticCall(FuncCall $funcCall, string $methodName): StaticCall
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

        return $this->createStaticCall('Nette\Utils\Strings', $methodName, $args);
    }

    /**
     * @return FuncCall|StaticCall
     */
    private function processSplit(FuncCall $funcCall, StaticCall $matchStaticCall): Expr
    {
        $matchStaticCall = $this->compensateNetteUtilsSplitDelimCapture($matchStaticCall);

        if (! isset($funcCall->args[2])) {
            return $matchStaticCall;
        }

        if ($this->isValue($funcCall->args[2]->value, -1)) {
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
    private function compensateNetteUtilsSplitDelimCapture(StaticCall $staticCall): StaticCall
    {
        $patternValue = $this->getValue($staticCall->args[1]->value);
        if (! is_string($patternValue)) {
            return $staticCall;
        }

        // @see https://regex101.com/r/05MPWa/1/
        $match = Strings::match($patternValue, '#[^\\\\]\(#');
        if ($match === null) {
            return $staticCall;
        }

        $delimCaptureConstant = new ConstFetch(new Name('PREG_SPLIT_DELIM_CAPTURE'));
        $negatedDelimCaptureConstantAsInt = new BitwiseAnd(new LNumber(0), new BitwiseNot($delimCaptureConstant));
        $staticCall->args[2] = new Arg($negatedDelimCaptureConstantAsInt);

        return $staticCall;
    }
}
