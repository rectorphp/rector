<?php declare(strict_types=1);

namespace Rector\Reconstructor\DependencyInjection;

use PhpCsFixer\DocBlock\Annotation;
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
     * @param Class_|Node $classNode
     */
    public function reconstruct(Node $classNode): void
    {
        foreach ($classNode->stmts as $classElementStatement) {
            if (! $classElementStatement instanceof Property) {
                continue;
            }
            $propertyNode = $classElementStatement;

            $propertyDocBlock = $this->createDocBlock($propertyNode);
            $injectAnnotations = $propertyDocBlock->getAnnotationsOfType('inject');
            if (! $injectAnnotations) {
                continue;
            }

            $this->removeInjectAnnotation($injectAnnotations, $propertyNode, $propertyDocBlock);
            $this->makePropertyPrivate($propertyNode);

            $propertyType = $propertyDocBlock->getAnnotationsOfType('var')[0]->getTypes()[0];
            $propertyName = $propertyNode->props[0]->name;
            $this->constructorMethodBuilder->addPropertyAssignToClass($classNode, $propertyType, $propertyName);
        }
    }

    private function createDocBlock(Property $propertyNode): DocBlock
    {
        return new DocBlock($propertyNode->getDocComment());
    }

    /**
     * @param $propertyNode
     * @return int
     */
    private function makePropertyPrivate($propertyNode): int
    {
        return $propertyNode->flags = Class_::MODIFIER_PRIVATE;
    }

    /**
     * @param Annotation[] $injectAnnotations
     */
    private function removeInjectAnnotation(array $injectAnnotations, Property $propertyNode, DocBlock $propertyDocBlock): void
    {
        foreach ($injectAnnotations as $injectAnnotation) {
            $injectAnnotation->remove();
        }

        $propertyNode->setDocComment(new Doc($propertyDocBlock->getContent()));
    }
}
