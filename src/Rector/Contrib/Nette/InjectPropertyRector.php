<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\Nette;

use PhpCsFixer\DocBlock\DocBlock;
use PhpParser\Comment\Doc;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use Rector\Builder\Class_\ClassPropertyCollector;
use Rector\Deprecation\SetNames;
use Rector\NodeTraverser\TokenSwitcher;
use Rector\Rector\AbstractRector;

final class InjectPropertyRector extends AbstractRector
{
    /**
     * @var string
     */
    private const ANNOTATION_INJECT = 'inject';

    /**
     * @var TokenSwitcher
     */
    private $tokenSwitcher;

    /**
     * @var ClassPropertyCollector
     */
    private $classPropertyCollector;

    /**
     * @var string
     */
    private $className;

    public function __construct(TokenSwitcher $tokenSwitcher, ClassPropertyCollector $classPropertyCollector)
    {
        $this->tokenSwitcher = $tokenSwitcher;
        $this->classPropertyCollector = $classPropertyCollector;
    }

    /**
     * @param Node[] $nodes
     * @return null|Node[]
     */
    public function beforeTraverse(array $nodes): ?array
    {
        $this->className = null;

        foreach ($nodes as $node) {
            if ($node instanceof Class_) {
                $this->className = (string) $node->name;
            }
        }

        return null;
    }

    public function isCandidate(Node $node): bool
    {
        if (! $node instanceof Property) {
            return false;
        }

        if (! $this->hasInjectAnnotation($node)) {
            return false;
        }

        $this->tokenSwitcher->enable();

        return true;
    }

    /**
     * @param Property $propertyNode
     */
    public function refactor($propertyNode): Node
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

        $this->classPropertyCollector->addPropertyForClass($this->className, $propertyType, $propertyName);
    }
}
