<?php declare(strict_types=1);

namespace Rector\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use Rector\Builder\StatementGlue;
use Rector\Node\Attribute;
use Rector\Node\NodeFactory;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * Can handle cases like:
 * - https://doc.nette.org/en/2.4/migration-2-4#toc-nette-smartobject
 * - https://github.com/silverstripe/silverstripe-upgrader/issues/71#issue-320145944
 */
final class ParentClassToTraitsRector extends AbstractRector
{
    /**
     * @var StatementGlue
     */
    private $statementGlue;

    /**
     * @var NodeFactory
     */
    private $nodeFactory;

    /**
     * @var string
     */
    private $parentClass;

    /**
     * @var string[]
     */
    private $traitNames = [];

    /**
     * @param string[] $traitNames
     */
    public function __construct(
        StatementGlue $statementGlue,
        NodeFactory $nodeFactory,
        string $parentClass = 'Nette\Object',
        array $traitNames = ['Nette\SmartObject']
    ) {
        $this->statementGlue = $statementGlue;
        $this->nodeFactory = $nodeFactory;
        $this->parentClass = $parentClass;
        $this->traitNames = $traitNames;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Replaces parent class to specific traits', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeClass extends Nette\Object
{
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
class SomeClass
{
    use Nette\SmartObject;
}
CODE_SAMPLE
            ),
        ]);
    }

    public function isCandidate(Node $node): bool
    {
        if (! $node instanceof Class_ || $node->extends === null || $node->isAnonymous()) {
            return false;
        }

        /** @var FullyQualified $fullyQualifiedName */
        $fullyQualifiedName = $node->extends->getAttribute(Attribute::RESOLVED_NAME);

        return $fullyQualifiedName->toString() === $this->parentClass;
    }

    /**
     * @param Class_ $classNode
     */
    public function refactor(Node $classNode): ?Node
    {
        foreach ($this->traitNames as $traitName) {
            $traitUseNode = $this->nodeFactory->createTraitUse($traitName);
            $this->statementGlue->addAsFirstTrait($classNode, $traitUseNode);
        }

        $this->removeParentClass($classNode);

        return $classNode;
    }

    private function removeParentClass(Class_ $classNode): void
    {
        $classNode->extends = null;
    }
}
