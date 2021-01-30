<?php

declare(strict_types=1);

namespace Rector\Nette\Rector\FuncCall;

use Nette\Utils\Strings;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\Minus;
use PhpParser\Node\Expr\Cast\Bool_;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\LNumber;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see https://www.tomasvotruba.cz/blog/2019/02/07/what-i-learned-by-using-thecodingmachine-safe/#is-there-a-better-way
 *
 * @see \Rector\Nette\Tests\Rector\FuncCall\PregMatchFunctionToNetteUtilsStringsRector\PregMatchFunctionToNetteUtilsStringsRectorTest
 */
final class PregMatchFunctionToNetteUtilsStringsRector extends AbstractPregToNetteUtilsStringsRector
{
    /**
     * @var array<string, string>
     */
    private const FUNCTION_NAME_TO_METHOD_NAME = [
        'preg_match' => 'match',
        'preg_match_all' => 'matchAll',
    ];

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Use Nette\Utils\Strings over bare preg_match() and preg_match_all() functions',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $content = 'Hi my name is Tom';
        preg_match('#Hi#', $content, $matches);
    }
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
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
                ),

            ]);
    }

    public function refactorIdentical(Identical $identical): ?Bool_
    {
        $parentNode = $identical->getAttribute(AttributeKey::PARENT_NODE);

        if ($identical->left instanceof FuncCall) {
            $refactoredFuncCall = $this->refactorFuncCall($identical->left);
            if ($refactoredFuncCall !== null && $this->valueResolver->isValue($identical->right, 1)) {
                return $this->createBoolCast($parentNode, $refactoredFuncCall);
            }
        }

        if ($identical->right instanceof FuncCall) {
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
    public function refactorFuncCall(FuncCall $funcCall): ?Expr
    {
        $methodName = $this->matchFuncCallRenameToMethod($funcCall, self::FUNCTION_NAME_TO_METHOD_NAME);
        if ($methodName === null) {
            return null;
        }

        $matchStaticCall = $this->createMatchStaticCall($funcCall, $methodName);

        // skip assigns, might be used with different return value
        $parentNode = $funcCall->getAttribute(AttributeKey::PARENT_NODE);
        if ($parentNode instanceof Assign) {
            if ($methodName === 'matchAll') {
                // use count
                return new FuncCall(new Name('count'), [new Arg($matchStaticCall)]);
            }

            return null;
        }

        // assign
        if (isset($funcCall->args[2])) {
            return new Assign($funcCall->args[2]->value, $matchStaticCall);
        }

        return $matchStaticCall;
    }

    private function createMatchStaticCall(FuncCall $funcCall, string $methodName): StaticCall
    {
        $args = [];
        $args[] = $funcCall->args[1];
        $args[] = $funcCall->args[0];

        $args = $this->compensateMatchAllEnforcedFlag($methodName, $funcCall, $args);

        return $this->nodeFactory->createStaticCall('Nette\Utils\Strings', $methodName, $args);
    }

    /**
     * Compensate enforced flag https://github.com/nette/utils/blob/e3dd1853f56ee9a68bfbb2e011691283c2ed420d/src/Utils/Strings.php#L487
     * See https://stackoverflow.com/a/61424319/1348344
     *
     * @param Arg[] $args
     * @return Arg[]
     */
    private function compensateMatchAllEnforcedFlag(string $methodName, FuncCall $funcCall, array $args): array
    {
        if ($methodName !== 'matchAll') {
            return $args;
        }

        if (count($funcCall->args) !== 3) {
            return $args;
        }

        $constFetch = new ConstFetch(new Name('PREG_SET_ORDER'));
        $minus = new Minus($constFetch, new LNumber(1));
        $args[] = new Arg($minus);

        return $args;
    }
}
