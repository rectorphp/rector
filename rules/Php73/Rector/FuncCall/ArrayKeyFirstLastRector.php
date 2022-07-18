<?php

declare (strict_types=1);
namespace Rector\Php73\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Expression;
use PHPStan\Reflection\ReflectionProvider;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://tomasvotruba.com/blog/2018/08/16/whats-new-in-php-73-in-30-seconds-in-diffs/#2-first-and-last-array-key
 *
 * This needs to removed 1 floor above, because only nodes in arrays can be removed why traversing,
 * see https://github.com/nikic/PHP-Parser/issues/389
 *
 * @see \Rector\Tests\Php73\Rector\FuncCall\ArrayKeyFirstLastRector\ArrayKeyFirstLastRectorTest
 */
final class ArrayKeyFirstLastRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @var string
     */
    private const ARRAY_KEY_FIRST = 'array_key_first';
    /**
     * @var string
     */
    private const ARRAY_KEY_LAST = 'array_key_last';
    /**
     * @var array<string, string>
     */
    private const PREVIOUS_TO_NEW_FUNCTIONS = ['reset' => self::ARRAY_KEY_FIRST, 'end' => self::ARRAY_KEY_LAST];
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    public function __construct(ReflectionProvider $reflectionProvider)
    {
        $this->reflectionProvider = $reflectionProvider;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Make use of array_key_first() and array_key_last()', [new CodeSample(<<<'CODE_SAMPLE'
reset($items);
$firstKey = key($items);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$firstKey = array_key_first($items);
CODE_SAMPLE
), new CodeSample(<<<'CODE_SAMPLE'
end($items);
$lastKey = key($items);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$lastKey = array_key_last($items);
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [FuncCall::class];
    }
    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }
        $nextExpression = $this->getNextExpression($node);
        if (!$nextExpression instanceof Node) {
            return null;
        }
        $resetOrEndFuncCall = $node;
        $keyFuncCall = $this->betterNodeFinder->findFirst($nextExpression, function (Node $node) use($resetOrEndFuncCall) : bool {
            if (!$node instanceof FuncCall) {
                return \false;
            }
            if (!$this->isName($node, 'key')) {
                return \false;
            }
            return $this->nodeComparator->areNodesEqual($resetOrEndFuncCall->args[0], $node->args[0]);
        });
        if (!$keyFuncCall instanceof FuncCall) {
            return null;
        }
        $newName = self::PREVIOUS_TO_NEW_FUNCTIONS[$this->getName($node)];
        $keyFuncCall->name = new Name($newName);
        $this->removeNode($node);
        return $node;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::ARRAY_KEY_FIRST_LAST;
    }
    private function shouldSkip(FuncCall $funcCall) : bool
    {
        if (!$this->isNames($funcCall, ['reset', 'end'])) {
            return \true;
        }
        if (!$this->reflectionProvider->hasFunction(new Name(self::ARRAY_KEY_FIRST), null)) {
            return \true;
        }
        return !$this->reflectionProvider->hasFunction(new Name(self::ARRAY_KEY_LAST), null);
    }
    private function getNextExpression(FuncCall $funcCall) : ?Node
    {
        $currentStmt = $this->betterNodeFinder->resolveCurrentStatement($funcCall);
        if (!$currentStmt instanceof Expression) {
            return null;
        }
        if ($currentStmt->expr !== $funcCall) {
            return null;
        }
        return $currentStmt->getAttribute(AttributeKey::NEXT_NODE);
    }
}
