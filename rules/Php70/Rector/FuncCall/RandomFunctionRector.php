<?php

declare (strict_types=1);
namespace Rector\Php70\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\Int_;
use PHPStan\Type\Constant\ConstantIntegerType;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\Rector\AbstractRector;
use Rector\RuleDoc\CodeSample\CodeSample;
use Rector\RuleDoc\RuleDefinition;
use Rector\ValueObject\PhpVersion;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
/**
 * @see \Rector\Tests\Php70\Rector\FuncCall\RandomFunctionRector\RandomFunctionRectorTest
 */
final class RandomFunctionRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     */
    private BetterNodeFinder $betterNodeFinder;
    /**
     * @var array<string, string>
     */
    private const OLD_TO_NEW_FUNCTION_NAMES = ['getrandmax' => 'mt_getrandmax', 'srand' => 'mt_srand', 'rand' => 'random_int'];
    public function __construct(BetterNodeFinder $betterNodeFinder)
    {
        $this->betterNodeFinder = $betterNodeFinder;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Changes rand, srand, and getrandmax to newer alternatives', [new CodeSample('rand();', 'random_int();')]);
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
    public function refactor(Node $node): ?\PhpParser\Node\Expr\FuncCall
    {
        if ($node->isFirstClassCallable()) {
            return null;
        }
        foreach (self::OLD_TO_NEW_FUNCTION_NAMES as $oldFunctionName => $newFunctionName) {
            if (!$this->isName($node, $oldFunctionName)) {
                continue;
            }
            if ($newFunctionName === 'random_int' && !$this->refactorRandArgs($node)) {
                return null;
            }
            $node->name = new Name($newFunctionName);
            return $node;
        }
        return null;
    }
    public function provideMinPhpVersion(): int
    {
        return PhpVersion::PHP_70;
    }
    /**
     * random_int() ignores the seed and throws on min > max, unlike rand()
     */
    private function refactorRandArgs(FuncCall $funcCall): bool
    {
        if ($this->isSeededFile()) {
            return \false;
        }
        $args = $funcCall->getArgs();
        // special case: random_int(); → random_int(0, getrandmax());
        if ($args === []) {
            $funcCall->args[0] = new Arg(new Int_(0));
            $funcCall->args[1] = new Arg($this->nodeFactory->createFuncCall('mt_getrandmax'));
            return \true;
        }
        if (count($args) !== 2) {
            return \true;
        }
        $minType = $this->getType($args[0]->value);
        $maxType = $this->getType($args[1]->value);
        if (!$minType instanceof ConstantIntegerType || !$maxType instanceof ConstantIntegerType) {
            return \false;
        }
        if ($minType->getValue() > $maxType->getValue()) {
            $funcCall->args = [$funcCall->args[1], $funcCall->args[0]];
        }
        return \true;
    }
    private function isSeededFile(): bool
    {
        $seedFuncCall = $this->betterNodeFinder->findFirst($this->getFile()->getNewStmts(), fn(Node $node): bool => $node instanceof FuncCall && $this->isNames($node, ['srand', 'mt_srand']));
        return $seedFuncCall instanceof FuncCall;
    }
}
