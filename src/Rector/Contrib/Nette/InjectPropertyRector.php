<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\Nette;

use PhpCsFixer\DocBlock\DocBlock;
use PhpParser\Comment\Doc;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use Rector\Builder\Class_\ClassPropertyCollector;
use Rector\Rector\Set\SetNames;
use Rector\Node\Attribute;
use Rector\Rector\AbstractRector;

final class InjectPropertyRector extends AbstractRector
{
    /**
     * @var string
     */
    private const ANNOTATION_INJECT = 'inject';

    /**
     * @var ClassPropertyCollector
     */
    private $classPropertyCollector;

    public function __construct(ClassPropertyCollector $classPropertyCollector)
    {
        $this->classPropertyCollector = $classPropertyCollector;
    }

    public function isCandidate(Node $node): bool
    {
        if (! $node instanceof Property) {
            return false;
        }

        if (! $this->hasInjectAnnotation($node)) {
            return false;
        }

        return true;
    }

    /**
     * @param Property $propertyNode
     */
    public function refactor(Node $propertyNode): Node
    {
        $propertyDocBlock = $this->createDocBlockFromNode($propertyNode);
        $propertyNode = $this->removeInjectAnnotationFromProperty($propertyNode, $propertyDocBlock);

        $propertyNode->flags = Class_::MODIFIER_PRIVATE;

        $this->addPropertyToCollector($propertyNode, $propertyDocBlock);

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

    private function hasInjectAnnotation(Property $propertyNode): bool
    {
        $propertyDocBlock = $this->createDocBlockFromNode($propertyNode);

        return (bool) $propertyDocBlock->getAnnotationsOfType(self::ANNOTATION_INJECT);
    }

    private function createDocBlockFromNode(Node $node): DocBlock
    {
        return new DocBlock($node->getDocComment());
    }

    private function removeInjectAnnotationFromProperty(Property $propertyNode, DocBlock $propertyDocBlock): Property
    {
        $injectAnnotations = $propertyDocBlock->getAnnotationsOfType(self::ANNOTATION_INJECT);
        foreach ($injectAnnotations as $injectAnnotation) {
            $injectAnnotation->remove();
        }

        $propertyNode->setDocComment(new Doc($propertyDocBlock->getContent()));

        return $propertyNode;
    }

    private function addPropertyToCollector(Property $propertyNode, DocBlock $propertyDocBlock): void
    {
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
