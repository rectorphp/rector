<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\DeadCode\ValueObject\TargetRemoveClassMethod;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix202212\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\DeadCode\Rector\Class_\TargetRemoveClassMethodRector\TargetRemoveClassMethodRectorTest
 */
final class TargetRemoveClassMethodRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var TargetRemoveClassMethod[]
     */
    private $targetRemoveClassMethods = [];
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Remove defined class method', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
}
CODE_SAMPLE
, [new TargetRemoveClassMethod('SomeClass', 'run')])]);
    }
    public function getNodeTypes() : array
    {
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($node->isAnonymous()) {
            return null;
        }
        foreach ($this->targetRemoveClassMethods as $targetRemoveClassMethod) {
            if (!$this->isName($node, $targetRemoveClassMethod->getClassName())) {
                continue;
            }
            $classMethod = $node->getMethod($targetRemoveClassMethod->getMethodName());
            if (!$classMethod instanceof ClassMethod) {
                continue;
            }
            $this->removeNode($classMethod);
            return null;
        }
        return null;
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration) : void
    {
        Assert::notEmpty($configuration);
        Assert::allIsInstanceOf($configuration, TargetRemoveClassMethod::class);
        $this->targetRemoveClassMethods = $configuration;
    }
}
