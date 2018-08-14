<?php declare(strict_types=1);

namespace Rector\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use Rector\Builder\StatementGlue;
use Rector\Node\NodeFactory;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\ConfiguredCodeSample;
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
     * @var string[]
     */
    private $parentClassToTraits = [];

    /**
     * @param string[] $parentClassToTraits { parent class => [ traits ] }
     */
    public function __construct(
        StatementGlue $statementGlue,
        NodeFactory $nodeFactory,
        array $parentClassToTraits
    ) {
        $this->statementGlue = $statementGlue;
        $this->nodeFactory = $nodeFactory;
        $this->parentClassToTraits = $parentClassToTraits;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Replaces parent class to specific traits', [
            new ConfiguredCodeSample(
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
                ,
                [
                    '$parentClassToTraits' => [
                        'Nette\Object' => ['Nette\SmartObject'],
                    ],
                ]
            ),
        ]);
    }

    public function isCandidate(Node $node): bool
    {
        if (! $node instanceof Class_ || $node->extends === null || $node->isAnonymous()) {
            return false;
        }

        $nodeParentClassName = $this->getClassNodeParentClassName($node);

        return isset($this->parentClassToTraits[$nodeParentClassName]);
    }

    /**
     * @param Class_ $classNode
     */
    public function refactor(Node $classNode): ?Node
    {
        $nodeParentClassName = $this->getClassNodeParentClassName($classNode);
        $traitNames = $this->parentClassToTraits[$nodeParentClassName];

        // keep the Trait order the way it is in config
        $traitNames = array_reverse($traitNames);

        foreach ($traitNames as $traitName) {
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

    private function getClassNodeParentClassName(Class_ $classNode): string
    {
        /** @var FullyQualified $fullyQualifiedName */
        $fullyQualifiedName = $classNode->extends->getAttribute(Attribute::RESOLVED_NAME);

        return $fullyQualifiedName->toString();
    }
}
