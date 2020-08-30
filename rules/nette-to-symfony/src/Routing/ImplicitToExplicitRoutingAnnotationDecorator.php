<?php

declare(strict_types=1);

namespace Rector\NetteToSymfony\Routing;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Type\ObjectType;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocNode\Symfony\SymfonyRouteTagValueNode;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PHPStan\Type\AliasedObjectType;
use Rector\PHPStan\Type\FullyQualifiedObjectType;
use Rector\PostRector\Collector\UseNodesToAddCollector;

final class ImplicitToExplicitRoutingAnnotationDecorator
{
    /**
     * @var string
     */
    public const HAS_ROUTE_ANNOTATION = 'has_route_annotation';

    /**
     * @var UseNodesToAddCollector
     */
    private $useNodesToAddCollector;

    public function __construct(UseNodesToAddCollector $useNodesToAddCollector)
    {
        $this->useNodesToAddCollector = $useNodesToAddCollector;
    }

    public function decorateClassMethodWithRouteAnnotation(
        ClassMethod $classMethod,
        SymfonyRouteTagValueNode $symfonyRouteTagValueNode
    ): void {
        /** @var PhpDocInfo $phpDocInfo */
        $phpDocInfo = $classMethod->getAttribute(AttributeKey::PHP_DOC_INFO);
        $phpDocInfo->addTagValueNodeWithShortName($symfonyRouteTagValueNode);

        $fullyQualifiedObjectType = new FullyQualifiedObjectType(SymfonyRouteTagValueNode::CLASS_NAME);
        $this->addUseType($fullyQualifiedObjectType, $classMethod);

        // remove
        $this->useNodesToAddCollector->removeShortUse($classMethod, 'Route');

        $classMethod->setAttribute(self::HAS_ROUTE_ANNOTATION, true);
    }

    /**
     * @param FullyQualifiedObjectType|AliasedObjectType $objectType
     */
    private function addUseType(ObjectType $objectType, Node $positionNode): void
    {
        assert($objectType instanceof FullyQualifiedObjectType || $objectType instanceof AliasedObjectType);

        $this->useNodesToAddCollector->addUseImport($positionNode, $objectType);
    }
}
