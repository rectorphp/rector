<?php

declare (strict_types=1);
namespace Rector\Renaming\Rector\PropertyFetch;

use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Identifier;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Renaming\ValueObject\RenameProperty;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix20210827\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\Renaming\Rector\PropertyFetch\RenamePropertyRector\RenamePropertyRectorTest
 */
final class RenamePropertyRector extends \Rector\Core\Rector\AbstractRector implements \Rector\Core\Contract\Rector\ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const RENAMED_PROPERTIES = 'old_to_new_property_by_types';
    /**
     * @var RenameProperty[]
     */
    private $renamedProperties = [];
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Replaces defined old properties by new ones.', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample('$someObject->someOldProperty;', '$someObject->someNewProperty;', [self::RENAMED_PROPERTIES => [new \Rector\Renaming\ValueObject\RenameProperty('SomeClass', 'someOldProperty', 'someNewProperty')]])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\PropertyFetch::class];
    }
    /**
     * @param PropertyFetch $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        foreach ($this->renamedProperties as $renamedProperty) {
            if (!$this->isObjectType($node->var, $renamedProperty->getObjectType())) {
                continue;
            }
            if (!$this->isName($node, $renamedProperty->getOldProperty())) {
                continue;
            }
            $node->name = new \PhpParser\Node\Identifier($renamedProperty->getNewProperty());
            return $node;
        }
        return null;
    }
    /**
     * @param array<string, RenameProperty[]> $configuration
     */
    public function configure(array $configuration) : void
    {
        $renamedProperties = $configuration[self::RENAMED_PROPERTIES] ?? [];
        \RectorPrefix20210827\Webmozart\Assert\Assert::allIsInstanceOf($renamedProperties, \Rector\Renaming\ValueObject\RenameProperty::class);
        $this->renamedProperties = $renamedProperties;
    }
}
