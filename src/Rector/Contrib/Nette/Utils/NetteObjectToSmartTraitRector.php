<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\Nette\Utils;

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
 * Covers https://doc.nette.org/en/2.4/migration-2-4#toc-nette-smartobject.
 */
final class NetteObjectToSmartTraitRector extends AbstractRector
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
     * @var string
     */
    private $traitName;

    public function __construct(
        StatementGlue $statementGlue,
        NodeFactory $nodeFactory,
        string $parentClass = 'Nette\Object',
        string $traitName = 'Nette\SmartObject'
    ) {
        $this->statementGlue = $statementGlue;
        $this->nodeFactory = $nodeFactory;
        $this->parentClass = $parentClass;
        $this->traitName = $traitName;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Checks all Nette\Object instances and turns parent class to trait', [
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
        $traitUseNode = $this->nodeFactory->createTraitUse($this->traitName);
        $this->statementGlue->addAsFirstTrait($classNode, $traitUseNode);

        $this->removeParentClass($classNode);

        return $classNode;
    }

    private function removeParentClass(Class_ $classNode): void
    {
        $classNode->extends = null;
    }
}
