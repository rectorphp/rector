<?php

declare (strict_types=1);
namespace Rector\Removing\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Interface_;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix202506\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\Removing\Rector\Class_\RemoveInterfacesRector\RemoveInterfacesRectorTest
 */
final class RemoveInterfacesRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string[]
     */
    private array $interfacesToRemove = [];
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Remove interfaces from class', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
class SomeClass implements SomeInterface
{
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
}
CODE_SAMPLE
, ['SomeInterface'])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Class_::class, Interface_::class];
    }
    /**
     * @param Class_|Interface_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($node instanceof Class_) {
            return $this->refactorClass($node);
        }
        return $this->refactorInterface($node);
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration) : void
    {
        Assert::allString($configuration);
        /** @var string[] $configuration */
        $this->interfacesToRemove = $configuration;
    }
    private function refactorClass(Class_ $class) : ?Class_
    {
        if ($class->implements === []) {
            return null;
        }
        $isInterfacesRemoved = \false;
        foreach ($class->implements as $key => $implement) {
            if ($this->isNames($implement, $this->interfacesToRemove)) {
                unset($class->implements[$key]);
                $isInterfacesRemoved = \true;
            }
        }
        if (!$isInterfacesRemoved) {
            return null;
        }
        return $class;
    }
    private function refactorInterface(Interface_ $interface) : ?\PhpParser\Node\Stmt\Interface_
    {
        $isInterfacesRemoved = \false;
        foreach ($interface->extends as $key => $extend) {
            if (!$this->isNames($extend, $this->interfacesToRemove)) {
                continue;
            }
            unset($interface->extends[$key]);
            $isInterfacesRemoved = \true;
        }
        if (!$isInterfacesRemoved) {
            return null;
        }
        return $interface;
    }
}
