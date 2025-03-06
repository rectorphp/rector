<?php

declare (strict_types=1);
namespace Rector\Symfony\CodeQuality\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Attribute;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use Rector\BetterPhpDocParser\PhpDoc\ArrayItemNode;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\BetterPhpDocParser\PhpDoc\StringNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover;
use Rector\Comments\NodeDocBlock\DocBlockUpdater;
use Rector\Doctrine\NodeAnalyzer\AttrinationFinder;
use Rector\Rector\AbstractRector;
use Rector\Symfony\Enum\FosAnnotation;
use Rector\Symfony\Enum\SymfonyAnnotation;
use Rector\Symfony\Enum\SymfonyAttribute;
use Rector\Symfony\TypeAnalyzer\ControllerAnalyzer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Symfony\Tests\CodeQuality\Rector\Class_\InlineClassRoutePrefixRector\InlineClassRoutePrefixRectorTest
 */
final class InlineClassRoutePrefixRector extends AbstractRector
{
    /**
     * @readonly
     */
    private PhpDocInfoFactory $phpDocInfoFactory;
    /**
     * @readonly
     */
    private PhpDocTagRemover $phpDocTagRemover;
    /**
     * @readonly
     */
    private DocBlockUpdater $docBlockUpdater;
    /**
     * @readonly
     */
    private ControllerAnalyzer $controllerAnalyzer;
    /**
     * @readonly
     */
    private AttrinationFinder $attrinationFinder;
    /**
     * @var string[]
     */
    private const FOS_REST_ANNOTATIONS = [FosAnnotation::REST_POST, FosAnnotation::REST_GET, FosAnnotation::REST_ROUTE];
    /**
     * @var string
     */
    private const PATH = 'path';
    public function __construct(PhpDocInfoFactory $phpDocInfoFactory, PhpDocTagRemover $phpDocTagRemover, DocBlockUpdater $docBlockUpdater, ControllerAnalyzer $controllerAnalyzer, AttrinationFinder $attrinationFinder)
    {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->phpDocTagRemover = $phpDocTagRemover;
        $this->docBlockUpdater = $docBlockUpdater;
        $this->controllerAnalyzer = $controllerAnalyzer;
        $this->attrinationFinder = $attrinationFinder;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Inline class route prefix to all method routes, to make single explicit source for route paths', [new CodeSample(<<<'CODE_SAMPLE'
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api")
 */
class SomeController
{
    /**
     * @Route("/action")
     */
    public function action()
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Component\Routing\Annotation\Route;

class SomeController
{
    /**
     * @Route("/api/action")
     */
    public function action()
    {
    }
}
CODE_SAMPLE
)]);
    }
    public function getNodeTypes() : array
    {
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node) : ?Class_
    {
        if ($this->shouldSkipClass($node)) {
            return null;
        }
        $classRoutePath = null;
        $classRouteName = null;
        // 1. detect attribute
        $routeAttributeOrAnnotation = $this->attrinationFinder->getByMany($node, [SymfonyAttribute::ROUTE, SymfonyAnnotation::ROUTE]);
        if ($routeAttributeOrAnnotation instanceof DoctrineAnnotationTagValueNode) {
            $classRoutePath = $this->resolveRoutePath($routeAttributeOrAnnotation);
            $classRouteName = $this->resolveRouteName($routeAttributeOrAnnotation);
        } elseif ($routeAttributeOrAnnotation instanceof Attribute) {
            $classRoutePath = $this->resolveRoutePathFromAttribute($routeAttributeOrAnnotation);
            $classRouteName = $this->resolveRouteNameFromAttribute($routeAttributeOrAnnotation);
        }
        if ($classRoutePath === null) {
            return null;
        }
        // 2. inline prefix to all method routes
        $hasChanged = \false;
        foreach ($node->getMethods() as $classMethod) {
            if (!$classMethod->isPublic() || $classMethod->isMagic()) {
                continue;
            }
            // can be route method
            $methodRouteAnnotationOrAttributes = $this->attrinationFinder->findManyByMany($classMethod, [SymfonyAttribute::ROUTE, SymfonyAnnotation::ROUTE]);
            foreach ($methodRouteAnnotationOrAttributes as $methodRouteAnnotationOrAttribute) {
                if ($methodRouteAnnotationOrAttribute instanceof DoctrineAnnotationTagValueNode) {
                    $routePathArrayItemNode = $methodRouteAnnotationOrAttribute->getSilentValue() ?? $methodRouteAnnotationOrAttribute->getValue(self::PATH);
                    if (!$routePathArrayItemNode instanceof ArrayItemNode) {
                        continue;
                    }
                    if (!$routePathArrayItemNode->value instanceof StringNode) {
                        continue;
                    }
                    $methodPrefix = $routePathArrayItemNode->value;
                    $newMethodPath = $classRoutePath . $methodPrefix->value;
                    $routePathArrayItemNode->value = new StringNode($newMethodPath);
                    foreach ($methodRouteAnnotationOrAttribute->values as $value) {
                        if ($value->key === 'name' && $value->value instanceof StringNode && \is_string($classRouteName)) {
                            $value->value->value = $classRouteName . $value->value->value;
                        }
                    }
                    $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($classMethod);
                    $hasChanged = \true;
                } elseif ($methodRouteAnnotationOrAttribute instanceof Attribute) {
                    foreach ($methodRouteAnnotationOrAttribute->args as $methodRouteArg) {
                        if ($methodRouteArg->name === null || $methodRouteArg->name->toString() === self::PATH) {
                            if (!$methodRouteArg->value instanceof String_) {
                                continue;
                            }
                            $methodRouteString = $methodRouteArg->value;
                            $methodRouteArg->value = new String_(\sprintf('%s%s', $classRoutePath, $methodRouteString->value));
                            $hasChanged = \true;
                            continue;
                        }
                        if ($methodRouteArg->name->toString() === 'name') {
                            if (!$methodRouteArg->value instanceof String_) {
                                continue;
                            }
                            $methodRouteString = $methodRouteArg->value;
                            $methodRouteArg->value = new String_(\sprintf('%s%s', $classRouteName, $methodRouteString->value));
                            $hasChanged = \true;
                        }
                    }
                }
            }
        }
        if (!$hasChanged) {
            return null;
        }
        if ($routeAttributeOrAnnotation instanceof DoctrineAnnotationTagValueNode) {
            $classPhpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
            $this->phpDocTagRemover->removeTagValueFromNode($classPhpDocInfo, $routeAttributeOrAnnotation);
            $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($node);
        } else {
            foreach ($node->attrGroups as $attrGroupKey => $attrGroup) {
                foreach ($attrGroup->attrs as $attribute) {
                    if ($attribute === $routeAttributeOrAnnotation) {
                        unset($node->attrGroups[$attrGroupKey]);
                    }
                }
            }
        }
        return $node;
    }
    private function shouldSkipClass(Class_ $class) : bool
    {
        if (!$this->controllerAnalyzer->isController($class)) {
            return \true;
        }
        foreach ($class->getMethods() as $classMethod) {
            if (!$classMethod->isPublic() || $classMethod->isMagic()) {
                continue;
            }
            // special cases for FOS rest that should be skipped
            if ($this->attrinationFinder->hasByMany($class, self::FOS_REST_ANNOTATIONS)) {
                return \true;
            }
        }
        return \false;
    }
    private function resolveRoutePath(DoctrineAnnotationTagValueNode $doctrineAnnotationTagValueNode) : ?string
    {
        $classRoutePathNode = $doctrineAnnotationTagValueNode->getSilentValue() ?: $doctrineAnnotationTagValueNode->getValue(self::PATH);
        if (!$classRoutePathNode instanceof ArrayItemNode) {
            return null;
        }
        if (!$classRoutePathNode->value instanceof StringNode) {
            return null;
        }
        return $classRoutePathNode->value->value;
    }
    private function resolveRouteName(DoctrineAnnotationTagValueNode $doctrineAnnotationTagValueNode) : ?string
    {
        $classRouteNameNode = $doctrineAnnotationTagValueNode->getValue('name');
        if (!$classRouteNameNode instanceof ArrayItemNode) {
            return null;
        }
        if (!$classRouteNameNode->value instanceof StringNode) {
            return null;
        }
        return $classRouteNameNode->value->value;
    }
    private function resolveRoutePathFromAttribute(Attribute $attribute) : ?string
    {
        foreach ($attribute->args as $arg) {
            // silent or "path"
            if ($arg->name === null || $arg->name->toString() === self::PATH) {
                $routeExpr = $arg->value;
                if ($routeExpr instanceof String_) {
                    return $routeExpr->value;
                }
            }
        }
        return null;
    }
    private function resolveRouteNameFromAttribute(Attribute $attribute) : ?string
    {
        foreach ($attribute->args as $arg) {
            if ($arg->name === null) {
                continue;
            }
            if ($arg->name->toString() === 'name') {
                $routeExpr = $arg->value;
                if ($routeExpr instanceof String_) {
                    return $routeExpr->value;
                }
            }
        }
        return null;
    }
}
