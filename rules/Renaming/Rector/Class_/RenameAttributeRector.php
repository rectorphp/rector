<?php

declare (strict_types=1);
namespace Rector\Renaming\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Attribute;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\Rector\AbstractRector;
use Rector\Renaming\ValueObject\RenameAttribute;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix202409\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\Renaming\Rector\Class_\RenameAttributeRector\RenameAttributeRectorTest
 */
final class RenameAttributeRector extends AbstractRector implements ConfigurableRectorInterface, MinPhpVersionInterface
{
    /**
     * @var RenameAttribute[]
     */
    private $renameAttributes = [];
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Rename attribute class names', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
#[SimpleRoute()]
class SomeClass
{
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
#[BasicRoute()]
class SomeClass
{
}
CODE_SAMPLE
, [new RenameAttribute('SimpleRoute', 'BasicRoute')])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Class_::class, ClassMethod::class, Property::class];
    }
    /**
     * @param Class_|ClassMethod|Property $node
     */
    public function refactor(Node $node) : ?Node
    {
        $hasChanged = \false;
        foreach ($node->attrGroups as $attrGroup) {
            foreach ($attrGroup->attrs as $attr) {
                $newAttributeName = $this->matchNewAttributeName($attr);
                if (!\is_string($newAttributeName)) {
                    continue;
                }
                $attr->name = new FullyQualified($newAttributeName);
                $hasChanged = \true;
            }
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration) : void
    {
        Assert::allIsAOf($configuration, RenameAttribute::class);
        $this->renameAttributes = $configuration;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::ATTRIBUTES;
    }
    private function matchNewAttributeName(Attribute $attribute) : ?string
    {
        foreach ($this->renameAttributes as $renameAttribute) {
            if ($this->isName($attribute->name, $renameAttribute->getOldAttribute())) {
                return $renameAttribute->getNewAttribute();
            }
        }
        return null;
    }
}
