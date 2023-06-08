<?php

declare (strict_types=1);
namespace Rector\Php73\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Expression;
use PHPStan\Reflection\ReflectionProvider;
use Rector\Core\Contract\PhpParser\Node\StmtsAwareInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
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
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
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
        return [StmtsAwareInterface::class];
    }
    /**
     * @param StmtsAwareInterface $node
     */
    public function refactor(Node $node) : ?StmtsAwareInterface
    {
        return $this->processArrayKeyFirstLast($node, \false);
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::ARRAY_KEY_FIRST_LAST;
    }
    private function processArrayKeyFirstLast(StmtsAwareInterface $stmtsAware, bool $hasChanged, int $jumpToKey = 0) : ?StmtsAwareInterface
    {
        if ($stmtsAware->stmts === null) {
            return null;
        }
        /** @var int $totalKeys */
        \end($stmtsAware->stmts);
        /** @var int $totalKeys */
        $totalKeys = \key($stmtsAware->stmts);
        for ($key = $jumpToKey; $key < $totalKeys; ++$key) {
            if (!isset($stmtsAware->stmts[$key], $stmtsAware->stmts[$key + 1])) {
                break;
            }
            if (!$stmtsAware->stmts[$key] instanceof Expression) {
                continue;
            }
            /** @var Expression $stmt */
            $stmt = $stmtsAware->stmts[$key];
            if ($this->shouldSkip($stmt)) {
                continue;
            }
            $nextStmt = $stmtsAware->stmts[$key + 1];
            /** @var FuncCall $resetOrEndFuncCall */
            $resetOrEndFuncCall = $stmt->expr;
            $keyFuncCall = $this->resolveKeyFuncCall($nextStmt, $resetOrEndFuncCall);
            if (!$keyFuncCall instanceof FuncCall) {
                continue;
            }
            if ($this->hasPrevCallNext($stmtsAware, $key + 2, $totalKeys, $keyFuncCall)) {
                continue;
            }
            $newName = self::PREVIOUS_TO_NEW_FUNCTIONS[$this->getName($stmt->expr)];
            $keyFuncCall->name = new Name($newName);
            unset($stmtsAware->stmts[$key]);
            $hasChanged = \true;
            return $this->processArrayKeyFirstLast($stmtsAware, $hasChanged, $key + 2);
        }
        if ($hasChanged) {
            return $stmtsAware;
        }
        return null;
    }
    private function resolveKeyFuncCall(Stmt $nextStmt, FuncCall $resetOrEndFuncCall) : ?FuncCall
    {
        if ($resetOrEndFuncCall->isFirstClassCallable()) {
            return null;
        }
        /** @var FuncCall|null */
        return $this->betterNodeFinder->findFirst($nextStmt, function (Node $subNode) use($resetOrEndFuncCall) : bool {
            if (!$subNode instanceof FuncCall) {
                return \false;
            }
            if (!$this->isName($subNode, 'key')) {
                return \false;
            }
            if ($subNode->isFirstClassCallable()) {
                return \false;
            }
            return $this->nodeComparator->areNodesEqual($resetOrEndFuncCall->getArgs()[0], $subNode->getArgs()[0]);
        });
    }
    private function hasPrevCallNext(StmtsAwareInterface $stmtsAware, int $nextKey, int $totalKeys, FuncCall $funcCall) : bool
    {
        for ($key = $nextKey; $key <= $totalKeys; ++$key) {
            if (!isset($stmtsAware->stmts[$key])) {
                continue;
            }
            $hasPrevCallNext = (bool) $this->betterNodeFinder->findFirst($stmtsAware->stmts[$key], function (Node $subNode) use($funcCall) : bool {
                if (!$subNode instanceof FuncCall) {
                    return \false;
                }
                if (!$this->isName($subNode, 'prev')) {
                    return \false;
                }
                if ($subNode->isFirstClassCallable()) {
                    return \true;
                }
                return $this->nodeComparator->areNodesEqual($subNode->getArgs()[0]->value, $funcCall->getArgs()[0]->value);
            });
            if ($hasPrevCallNext) {
                return \true;
            }
        }
        return \false;
    }
    private function shouldSkip(Expression $expression) : bool
    {
        if (!$expression->expr instanceof FuncCall) {
            return \true;
        }
        if (!$this->isNames($expression->expr, ['reset', 'end'])) {
            return \true;
        }
        if (!$this->reflectionProvider->hasFunction(new Name(self::ARRAY_KEY_FIRST), null)) {
            return \true;
        }
        return !$this->reflectionProvider->hasFunction(new Name(self::ARRAY_KEY_LAST), null);
    }
}
