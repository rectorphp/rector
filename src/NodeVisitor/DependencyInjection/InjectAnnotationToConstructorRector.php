<?php declare(strict_types=1);

namespace Rector\NodeVisitor\DependencyInjection;

use PhpCsFixer\DocBlock\DocBlock;
use PhpParser\Comment\Doc;
use PhpParser\Node;
use PhpParser\Node\Stmt\Property;
use PhpParser\NodeVisitorAbstract;
use Rector\NodeTraverser\TokenSwitcher;

final class InjectAnnotationToConstructorRector extends NodeVisitorAbstract
{
    /**
     * @var string
     */
    private const ANNOTATION_INJECT = 'inject';

    /**
     * @var TokenSwitcher
     */
    private $tokenSwitcher;

//    /**
//     * @var ConstructorMethodBuilder
//     */
//    private $constructorMethodBuilder;
//
//    public function __construct(ConstructorMethodBuilder $constructorMethodBuilder)
//    {
//        $this->constructorMethodBuilder = $constructorMethodBuilder;
//    }

    public function __construct(TokenSwitcher $tokenSwitcher)
    {
        $this->tokenSwitcher = $tokenSwitcher;
    }

    public function enterNode(Node $node): ?Node
    {
        if (! $this->isCandidate($node)) {
            return null;
        }

        return $this->reconstructProperty($node);
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

//    private function reconstruct(Class_ $classNode): Node
//    {
//        dump($classNode);
//        die;
//
//        foreach ($classNode->stmts as $classElementStatement) {
//            if (! $classElementStatement instanceof Property) {
//                continue;
//            }
//
//            $propertyNode = $classElementStatement;
//
//            $propertyDocBlock = $this->createDocBlockFromNode($propertyNode);
//            $injectAnnotations = $propertyDocBlock->getAnnotationsOfType(self::ANNOTATION_INJECT);
//            if (! $injectAnnotations) {
//                continue;
//            }
//
//            $this->removeInjectAnnotationFromProperty($propertyNode, $propertyDocBlock);
//            $this->makePropertyPrivate($propertyNode);
//
//            $propertyType = $propertyDocBlock->getAnnotationsOfType('var')[0]
//                ->getTypes()[0];
//            $propertyName = (string) $propertyNode->props[0]->name;
//
//            $this->constructorMethodBuilder->addPropertyAssignToClass($classNode, $propertyType, $propertyName);
//        }
//
//        return $classNode;
//    }

    private function reconstructProperty($propertyNode): Property
    {
        $propertyDocBlock = $this->createDocBlockFromNode($propertyNode);
        $propertyNode = $this->removeInjectAnnotationFromProperty($propertyNode, $propertyDocBlock);

//        $propertyNode->flags = Class_::MODIFIER_PRIVATE;

        return $propertyNode;
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
}
