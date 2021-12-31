<?php

declare (strict_types=1);
namespace Rector\Transform\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix20211231\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\Transform\Rector\Class_\AddInterfaceByTraitRector\AddInterfaceByTraitRectorTest
 */
final class AddInterfaceByTraitRector extends \Rector\Core\Rector\AbstractRector implements \Rector\Core\Contract\Rector\ConfigurableRectorInterface
{
    /**
     * @deprecated
     * @var string
     */
    public const INTERFACE_BY_TRAIT = 'interface_by_trait';
    /**
     * @var array<string, string>
     */
    private $interfaceByTrait = [];
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Add interface by used trait', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample(<<<'CODE_SAMPLE'
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
        return [\PhpParser\Node\Stmt\Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        /** @var Scope $scope */
        $scope = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
        $classReflection = $scope->getClassReflection();
        if (!$classReflection instanceof \PHPStan\Reflection\ClassReflection) {
            return null;
        }
        foreach ($this->interfaceByTrait as $traitName => $interfaceName) {
            if (!$classReflection->hasTraitUse($traitName)) {
                continue;
            }
            foreach ($node->implements as $implement) {
                if ($this->isName($implement, $interfaceName)) {
                    continue 2;
                }
            }
            $node->implements[] = new \PhpParser\Node\Name\FullyQualified($interfaceName);
        }
        return $node;
    }
    /**
     * @todo complex configuration, introduce value object!
     * @param mixed[] $configuration
     */
    public function configure(array $configuration) : void
    {
        $interfaceByTrait = $configuration[self::INTERFACE_BY_TRAIT] ?? $configuration;
        \RectorPrefix20211231\Webmozart\Assert\Assert::isArray($interfaceByTrait);
        \RectorPrefix20211231\Webmozart\Assert\Assert::allString(\array_keys($interfaceByTrait));
        \RectorPrefix20211231\Webmozart\Assert\Assert::allString($interfaceByTrait);
        $this->interfaceByTrait = $interfaceByTrait;
    }
}
