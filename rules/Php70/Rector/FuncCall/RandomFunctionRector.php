<?php

declare (strict_types=1);
namespace Rector\Php70\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\LNumber;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Php70\Rector\FuncCall\RandomFunctionRector\RandomFunctionRectorTest
 */
final class RandomFunctionRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @var array<string, string>
     */
    private const OLD_TO_NEW_FUNCTION_NAMES = ['getrandmax' => 'mt_getrandmax', 'srand' => 'mt_srand', 'mt_rand' => 'random_int', 'rand' => 'random_int'];
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Changes rand, srand and getrandmax by new mt_* alternatives.', [new CodeSample('rand();', 'mt_rand();')]);
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
    public function refactor(Node $node) : ?\PhpParser\Node\Expr\FuncCall
    {
        foreach (self::OLD_TO_NEW_FUNCTION_NAMES as $oldFunctionName => $newFunctionName) {
            if ($this->isName($node, $oldFunctionName)) {
                $node->name = new Name($newFunctionName);
                // special case: random_int(); → random_int(0, getrandmax());
                if ($newFunctionName === 'random_int' && $node->args === []) {
                    $node->args[0] = new Arg(new LNumber(0));
                    $node->args[1] = new Arg($this->nodeFactory->createFuncCall('mt_getrandmax'));
                }
                return $node;
            }
        }
        return null;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::CSPRNG_FUNCTIONS;
    }
}
