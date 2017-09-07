<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\Nette;

use PhpCsFixer\DocBlock\DocBlock;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use Rector\Builder\Class_\ClassPropertyCollector;
use Rector\Node\Attribute;
use Rector\NodeAnalyzer\DocBlockAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\Rector\Set\SetNames;

final class InjectPropertyRector extends AbstractRector
{
    /**
     * @var ClassPropertyCollector
     */
    private $classPropertyCollector;

    /**
     * @var DocBlockAnalyzer
     */
    private $docBlockAnalyzer;

    public function __construct(ClassPropertyCollector $classPropertyCollector, DocBlockAnalyzer $docBlockAnalyzer)
    {
        $this->classPropertyCollector = $classPropertyCollector;
        $this->docBlockAnalyzer = $docBlockAnalyzer;
    }

    public function isCandidate(Node $node): bool
    {
        if (! $node instanceof Property) {
            return false;
        }

        if (! $this->docBlockAnalyzer->hasAnnotation($node, 'inject')) {
            return false;
        }

        return true;
    }

    /**
     * @param Property $propertyNode
     */
    public function refactor(Node $propertyNode): Node
    {
        $this->docBlockAnalyzer->removeAnnotationFromNode($propertyNode, 'inject');

        $propertyNode->flags = Class_::MODIFIER_PRIVATE;

        $this->addPropertyToCollector($propertyNode);

        return $propertyNode;
    }

    public function getSetName(): string
    {
        return SetNames::NETTE;
    }

    public function sinceVersion(): float
    {
        return 2.1;
    }

    private function addPropertyToCollector(Property $propertyNode): void
    {
        $propertyDocBlock = new DocBlock($propertyNode->getDocComment());

        $propertyType = $propertyDocBlock->getAnnotationsOfType('var')[0]
            ->getTypes()[0];

        $propertyName = (string) $propertyNode->props[0]->name;

        $this->classPropertyCollector->addPropertyForClass(
            (string) $propertyNode->getAttribute(Attribute::CLASS_NAME),
            $propertyType,
            $propertyName
        );
    }
}
