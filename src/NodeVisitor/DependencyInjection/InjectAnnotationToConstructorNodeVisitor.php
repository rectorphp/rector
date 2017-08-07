<?php declare(strict_types=1);

namespace Rector\NodeVisitor\DependencyInjection;

use PhpCsFixer\DocBlock\DocBlock;
use PhpParser\Comment\Doc;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PhpParser\NodeVisitorAbstract;
use Rector\Builder\ConstructorMethodBuilder;

final class InjectAnnotationToConstructorNodeVisitor extends NodeVisitorAbstract
{
    /**
     * @var string
     */
    private const ANNOTATION_INJECT = 'inject';

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
     * Called when entering a node.
     *
     * Return value semantics:
     *  * null
     *        => $node stays as-is
     *  * NodeTraverser::DONT_TRAVERSE_CHILDREN
     *        => Children of $node are not traversed. $node stays as-is
     *  * NodeTraverser::STOP_TRAVERSAL
     *        => Traversal is aborted. $node stays as-is
     *  * otherwise
     *        => $node is set to the return value
     *
     * @return null|int|Node Replacement node (or special return value)
     */
    public function enterNode(Node $node)
    {
        if ($node instanceof Class_) {
            return $this->reconstruct($node);
        }

        return null;
    }

    private function reconstruct(Class_ $classNode): Node
    {
        foreach ($classNode->stmts as $classElementStatement) {
            if (! $classElementStatement instanceof Property) {
                continue;
            }

            $propertyNode = $classElementStatement;

            $propertyDocBlock = $this->createDocBlockFromProperty($propertyNode);
            $injectAnnotations = $propertyDocBlock->getAnnotationsOfType(self::ANNOTATION_INJECT);
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

        return $classNode;
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
        $injectAnnotations = $propertyDocBlock->getAnnotationsOfType(self::ANNOTATION_INJECT);

        foreach ($injectAnnotations as $injectAnnotation) {
            $injectAnnotation->remove();
        }

        $propertyNode->setDocComment(new Doc($propertyDocBlock->getContent()));
    }
}
