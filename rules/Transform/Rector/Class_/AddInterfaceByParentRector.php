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
use RectorPrefix20220209\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\Transform\Rector\Class_\AddInterfaceByParentRector\AddInterfaceByParentRectorTest
 */
final class AddInterfaceByParentRector extends \Rector\Core\Rector\AbstractRector implements \Rector\Core\Contract\Rector\ConfigurableRectorInterface
{
    /**
     * @deprecated
     * @var string
     */
    public const INTERFACE_BY_PARENT = 'interface_by_parent';
    /**
     * @var array<string, string>
     */
    private $interfaceByParent = [];
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Add interface by parent', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample(<<<'CODE_SAMPLE'
class SomeClass extends SomeParent
{

}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass extends SomeParent implements SomeInterface
{

}
CODE_SAMPLE
, ['SomeParent' => 'SomeInterface'])]);
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
        $parentClassReflection = $classReflection->getParentClass();
        if (!$parentClassReflection instanceof \PHPStan\Reflection\ClassReflection) {
            return null;
        }
        foreach ($this->interfaceByParent as $parentName => $interfaceName) {
            if ($parentName !== $parentClassReflection->getName()) {
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
     * @param mixed[] $configuration
     */
    public function configure(array $configuration) : void
    {
        $interfaceByParent = $configuration[self::INTERFACE_BY_PARENT] ?? $configuration;
        \RectorPrefix20220209\Webmozart\Assert\Assert::isArray($interfaceByParent);
        \RectorPrefix20220209\Webmozart\Assert\Assert::allString(\array_keys($interfaceByParent));
        \RectorPrefix20220209\Webmozart\Assert\Assert::allString($interfaceByParent);
        $this->interfaceByParent = $interfaceByParent;
    }
}
