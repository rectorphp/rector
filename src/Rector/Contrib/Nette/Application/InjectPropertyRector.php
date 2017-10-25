<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\Nette\Application;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use Rector\Builder\Class_\ClassPropertyCollector;
use Rector\Node\Attribute;
use Rector\Rector\AbstractRector;
use Rector\ReflectionDocBlock\NodeAnalyzer\DocBlockAnalyzer;

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

    private function addPropertyToCollector(Property $propertyNode): void
    {
        $propertyTypes = $propertyNode->getAttribute(Attribute::TYPES);

        $propertyName = (string) $propertyNode->props[0]->name;

        $this->classPropertyCollector->addPropertyForClass(
            (string) $propertyNode->getAttribute(Attribute::CLASS_NAME),
            $propertyTypes,
            $propertyName
        );
    }
}
