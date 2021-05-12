<?php

declare(strict_types=1);

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

/**
 * @see \Rector\Tests\Transform\Rector\Class_\AddInterfaceByParentRector\AddInterfaceByParentRectorTest
 */
final class AddInterfaceByParentRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const INTERFACE_BY_PARENT = 'interface_by_parent';

    /**
     * @var array<string, string>
     */
    private array $interfaceByParent = [];

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add interface by parent', [
            new ConfiguredCodeSample(
                <<<'CODE_SAMPLE'
class SomeClass extends SomeParent
{

}
CODE_SAMPLE
,
                <<<'CODE_SAMPLE'
class SomeClass extends SomeParent implements SomeInterface
{

}
CODE_SAMPLE
            ,
                [
                    self::INTERFACE_BY_PARENT => [
                        'SomeParent' => 'SomeInterface',
                    ],
                ]
            ),
        ]);
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }

    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        /** @var Scope $scope */
        $scope = $node->getAttribute(AttributeKey::SCOPE);

        $classReflection = $scope->getClassReflection();
        if (! $classReflection instanceof ClassReflection) {
            return null;
        }

        $parentClassReflection = $classReflection->getParentClass();
        if (! $parentClassReflection) {
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

            $node->implements[] = new FullyQualified($interfaceName);
        }

        return $node;
    }

    /**
     * @param array<string, array<string, string>> $configuration
     */
    public function configure(array $configuration): void
    {
        $this->interfaceByParent = $configuration[self::INTERFACE_BY_PARENT] ?? [];
    }
}
