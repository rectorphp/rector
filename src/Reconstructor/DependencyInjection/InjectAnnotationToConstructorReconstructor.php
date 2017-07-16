<?php declare(strict_types=1);

namespace Rector\Reconstructor\DependencyInjection;

use PhpCsFixer\DocBlock\DocBlock;
use PhpParser\Comment\Doc;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use Rector\Builder\ConstructorMethodBuilder;
use Rector\Contract\Dispatcher\ReconstructorInterface;

final class InjectAnnotationToConstructorReconstructor implements ReconstructorInterface
{
    /**
     * @var ConstructorMethodBuilder
     */
    private $constructorMethodBuilder;

    public function __construct(ConstructorMethodBuilder $constructorMethodBuilder)
    {
        $this->constructorMethodBuilder = $constructorMethodBuilder;
    }

    public function isCandidate(Node $node): bool
    {
        return $node instanceof Class_;
    }

    /**
     * @param Class_ $classNode
     */
    public function reconstruct(Node $classNode): void
    {
        foreach ($classNode->stmts as $classElementStatement) {
            if (! $classElementStatement instanceof Property) {
                continue;
            }
            $propertyNode = $classElementStatement;

            $propertyDocBlock = $this->createDocBlockFromProperty($propertyNode);
            $injectAnnotations = $propertyDocBlock->getAnnotationsOfType('inject');
            if (! $injectAnnotations) {
                continue;
            }

            $this->removeInjectAnnotationFromProperty($propertyNode, $propertyDocBlock);
            $this->makePropertyPrivate($propertyNode);

            $propertyType = $propertyDocBlock->getAnnotationsOfType('var')[0]
                ->getTypes()[0];
            $propertyName = (string) $propertyNode->props[0]->name;

            $this->constructorMethodBuilder->addPropertyAssignToClass($classNode, $propertyType, $propertyName);
        }
    }

    private function createDocBlockFromProperty(Property $propertyNode): DocBlock
    {
        return new DocBlock($propertyNode->getDocComment());
    }

    private function makePropertyPrivate(Property $propertyNode): void
    {
        $propertyNode->flags = Class_::MODIFIER_PRIVATE;
    }

    private function removeInjectAnnotationFromProperty(Property $propertyNode, DocBlock $propertyDocBlock): void
    {
        $injectAnnotations = $propertyDocBlock->getAnnotationsOfType('inject');

        foreach ($injectAnnotations as $injectAnnotation) {
            $injectAnnotation->remove();
        }

        $propertyNode->setDocComment(new Doc($propertyDocBlock->getContent()));
    }
}
