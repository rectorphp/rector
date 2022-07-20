<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\BinaryOp;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\Instanceof_;
use Rector\Core\Rector\AbstractRector;
use Rector\DeadCode\NodeAnalyzer\InstanceOfUniqueKeyResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\BinaryOp\RemoveDuplicatedInstanceOfRector\RemoveDuplicatedInstanceOfRectorTest
 */
final class RemoveDuplicatedInstanceOfRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\DeadCode\NodeAnalyzer\InstanceOfUniqueKeyResolver
     */
    private $instanceOfUniqueKeyResolver;
    public function __construct(InstanceOfUniqueKeyResolver $instanceOfUniqueKeyResolver)
    {
        $this->instanceOfUniqueKeyResolver = $instanceOfUniqueKeyResolver;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Remove duplicated instanceof in one call', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run($value)
    {
        $isIt = $value instanceof A || $value instanceof A;
        $isIt = $value instanceof A && $value instanceof A;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run($value): void
    {
        $isIt = $value instanceof A;
        $isIt = $value instanceof A;
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
        return [BinaryOp::class];
    }
    /**
     * @param BinaryOp $node
     */
    public function refactor(Node $node) : ?Node
    {
        $duplicatedInstanceOfs = $this->resolveDuplicatedInstancesOf($node);
        if ($duplicatedInstanceOfs === []) {
            return null;
        }
        $this->nodeRemover->removeNodes($duplicatedInstanceOfs);
        return $node;
    }
    /**
     * @return Instanceof_[]
     */
    private function resolveDuplicatedInstancesOf(BinaryOp $binaryOp) : array
    {
        $duplicatedInstanceOfs = [];
        /** @var Instanceof_[] $instanceOfs */
        $instanceOfs = $this->betterNodeFinder->find($binaryOp, static function (Node $subNode) : bool {
            if (!$subNode instanceof Instanceof_) {
                return \false;
            }
            $parentNode = $subNode->getAttribute(AttributeKey::PARENT_NODE);
            return $parentNode instanceof BinaryOp;
        });
        $uniqueInstanceOfKeys = [];
        foreach ($instanceOfs as $instanceof) {
            $uniqueKey = $this->instanceOfUniqueKeyResolver->resolve($instanceof);
            if ($uniqueKey === null) {
                continue;
            }
            // already present before → duplicated
            if (\in_array($uniqueKey, $uniqueInstanceOfKeys, \true)) {
                $duplicatedInstanceOfs[] = $instanceof;
            }
            $uniqueInstanceOfKeys[] = $uniqueKey;
        }
        return $duplicatedInstanceOfs;
    }
}
