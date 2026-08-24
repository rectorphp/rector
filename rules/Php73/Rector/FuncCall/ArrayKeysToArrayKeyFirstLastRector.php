<?php

declare (strict_types=1);
namespace Rector\Php73\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\PhpVersionFeature;
use Rector\ValueObject\PolyfillPackage;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Rector\VersionBonding\Contract\RelatedPolyfillInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Php73\Rector\FuncCall\ArrayKeysToArrayKeyFirstLastRector\ArrayKeysToArrayKeyFirstLastRectorTest
 */
final class ArrayKeysToArrayKeyFirstLastRector extends AbstractRector implements MinPhpVersionInterface, RelatedPolyfillInterface
{
    /**
     * @var array<string, string>
     */
    private const PREVIOUS_TO_NEW_FUNCTIONS = ['current' => 'array_key_first', 'reset' => 'array_key_first', 'end' => 'array_key_last'];
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Make use of array_key_first() and array_key_last() instead of reading a single key off array_keys()', [new CodeSample(<<<'CODE_SAMPLE'
$firstKey = current(array_keys($items));
$lastKey = end(array_keys($items));
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$firstKey = array_key_first($items);
$lastKey = array_key_last($items);
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [FuncCall::class];
    }
    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node->isFirstClassCallable()) {
            return null;
        }
        $funcName = $this->getName($node);
        if ($funcName === null) {
            return null;
        }
        if (!isset(self::PREVIOUS_TO_NEW_FUNCTIONS[$funcName])) {
            return null;
        }
        $args = $node->getArgs();
        if (count($args) !== 1) {
            return null;
        }
        if ($args[0]->name instanceof Identifier || $args[0]->unpack) {
            return null;
        }
        $arrayKeysFuncCall = $args[0]->value;
        if (!$arrayKeysFuncCall instanceof FuncCall) {
            return null;
        }
        if (!$this->isName($arrayKeysFuncCall, 'array_keys')) {
            return null;
        }
        if ($arrayKeysFuncCall->isFirstClassCallable()) {
            return null;
        }
        $arrayKeysArgs = $arrayKeysFuncCall->getArgs();
        // array_keys() with a search value returns only the matching keys, so the first one is not the array's first key
        if (count($arrayKeysArgs) !== 1) {
            return null;
        }
        if ($arrayKeysArgs[0]->name instanceof Identifier || $arrayKeysArgs[0]->unpack) {
            return null;
        }
        $node->name = new Name(self::PREVIOUS_TO_NEW_FUNCTIONS[$funcName]);
        $node->args = $arrayKeysArgs;
        return $node;
    }
    public function provideMinPhpVersion(): int
    {
        return PhpVersionFeature::ARRAY_KEY_FIRST_LAST;
    }
    public function providePolyfillPackage(): string
    {
        return PolyfillPackage::PHP_73;
    }
}
