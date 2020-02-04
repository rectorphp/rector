<?php

declare(strict_types=1);

namespace Rector\FrameworkMigration\Symfony;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Type\ObjectType;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocNode\Symfony\SymfonyRouteTagValueNode;
use Rector\CodingStyle\Application\UseAddingCommander;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PHPStan\Type\AliasedObjectType;
use Rector\PHPStan\Type\FullyQualifiedObjectType;

final class ImplicitToExplicitRoutingAnnotationDecorator
{
    /**
     * @var string
     */
    public const HAS_ROUTE_ANNOTATION = 'has_route_annotation';

    /**
     * @var UseAddingCommander
     */
    private $useAddingCommander;

    public function __construct(UseAddingCommander $useAddingCommander)
    {
        $this->useAddingCommander = $useAddingCommander;
    }

    public function decorateClassMethodWithRouteAnnotation(
        ClassMethod $classMethod,
        SymfonyRouteTagValueNode $symfonyRouteTagValueNode
    ): void {
        /** @var PhpDocInfo $phpDocInfo */
        $phpDocInfo = $classMethod->getAttribute(AttributeKey::PHP_DOC_INFO);
        $phpDocInfo->addTagValueNodeWithShortName($symfonyRouteTagValueNode);

        $symfonyRouteUseObjectType = new FullyQualifiedObjectType(SymfonyRouteTagValueNode::CLASS_NAME);
        $this->addUseType($symfonyRouteUseObjectType, $classMethod);

        // remove
        $this->useAddingCommander->removeShortUse($classMethod, 'Route');

        $classMethod->setAttribute(self::HAS_ROUTE_ANNOTATION, true);
    }

    /**
     * @param FullyQualifiedObjectType|AliasedObjectType $objectType
     */
    private function addUseType(ObjectType $objectType, Node $positionNode): void
    {
        assert($objectType instanceof FullyQualifiedObjectType || $objectType instanceof AliasedObjectType);

        $this->useAddingCommander->addUseImport($positionNode, $objectType);
    }
}
