<?php

declare (strict_types=1);
namespace Rector\Transform\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractScopeAwareRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix202312\Webmozart\Assert\Assert;
/**
 * @api used in rector-doctrine
 * @see \Rector\Tests\Transform\Rector\Class_\AddInterfaceByTraitRector\AddInterfaceByTraitRectorTest
 */
final class AddInterfaceByTraitRector extends AbstractScopeAwareRector implements ConfigurableRectorInterface
{
    /**
     * @var array<string, string>
     */
    private $interfaceByTrait = [];
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Add interface by used trait', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    use SomeTrait;
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass implements SomeInterface
{
    use SomeTrait;
}
CODE_SAMPLE
, ['SomeTrait' => 'SomeInterface'])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactorWithScope(Node $node, Scope $scope) : ?Node
    {
        $classReflection = $scope->getClassReflection();
        if (!$classReflection instanceof ClassReflection) {
            return null;
        }
        $hasChanged = \false;
        foreach ($this->interfaceByTrait as $traitName => $interfaceName) {
            if (!$classReflection->hasTraitUse($traitName)) {
                continue;
            }
            if ($classReflection->implementsInterface($interfaceName)) {
                continue;
            }
            $node->implements[] = new FullyQualified($interfaceName);
            $hasChanged = \true;
        }
        if (!$hasChanged) {
            return null;
        }
        return $node;
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration) : void
    {
        Assert::allString(\array_keys($configuration));
        Assert::allString($configuration);
        $this->interfaceByTrait = $configuration;
    }
}
