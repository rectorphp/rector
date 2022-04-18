<?php

declare (strict_types=1);
namespace Rector\Renaming\Rector\ClassConstFetch;

use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Renaming\Contract\RenameClassConstFetchInterface;
use Rector\Renaming\ValueObject\RenameClassAndConstFetch;
use Rector\Renaming\ValueObject\RenameClassConstFetch;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix20220418\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\Renaming\Rector\ClassConstFetch\RenameClassConstFetchRector\RenameClassConstFetchRectorTest
 */
final class RenameClassConstFetchRector extends \Rector\Core\Rector\AbstractRector implements \Rector\Core\Contract\Rector\ConfigurableRectorInterface
{
    /**
     * @var RenameClassConstFetchInterface[]
     */
    private $renameClassConstFetches = [];
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Replaces defined class constants in their calls.', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample(<<<'CODE_SAMPLE'
$value = SomeClass::OLD_CONSTANT;
$value = SomeClass::OTHER_OLD_CONSTANT;
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$value = SomeClass::NEW_CONSTANT;
$value = DifferentClass::NEW_CONSTANT;
CODE_SAMPLE
, [new \Rector\Renaming\ValueObject\RenameClassConstFetch('SomeClass', 'OLD_CONSTANT', 'NEW_CONSTANT'), new \Rector\Renaming\ValueObject\RenameClassAndConstFetch('SomeClass', 'OTHER_OLD_CONSTANT', 'DifferentClass', 'NEW_CONSTANT')])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\ClassConstFetch::class];
    }
    /**
     * @param ClassConstFetch $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node\Expr\ClassConstFetch
    {
        foreach ($this->renameClassConstFetches as $renameClassConstFetch) {
            if (!$this->isObjectType($node->class, $renameClassConstFetch->getOldObjectType())) {
                continue;
            }
            if (!$this->isName($node->name, $renameClassConstFetch->getOldConstant())) {
                continue;
            }
            if ($renameClassConstFetch instanceof \Rector\Renaming\ValueObject\RenameClassAndConstFetch) {
                return $this->createClassAndConstFetch($renameClassConstFetch);
            }
            $node->name = new \PhpParser\Node\Identifier($renameClassConstFetch->getNewConstant());
            return $node;
        }
        return null;
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration) : void
    {
        \RectorPrefix20220418\Webmozart\Assert\Assert::allIsAOf($configuration, \Rector\Renaming\Contract\RenameClassConstFetchInterface::class);
        $this->renameClassConstFetches = $configuration;
    }
    private function createClassAndConstFetch(\Rector\Renaming\ValueObject\RenameClassAndConstFetch $renameClassAndConstFetch) : \PhpParser\Node\Expr\ClassConstFetch
    {
        return new \PhpParser\Node\Expr\ClassConstFetch(new \PhpParser\Node\Name\FullyQualified($renameClassAndConstFetch->getNewClass()), new \PhpParser\Node\Identifier($renameClassAndConstFetch->getNewConstant()));
    }
}
