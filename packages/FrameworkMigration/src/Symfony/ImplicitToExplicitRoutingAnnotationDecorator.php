<?php

declare(strict_types=1);

namespace Rector\FrameworkMigration\Symfony;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Type\ObjectType;
use Rector\BetterPhpDocParser\PhpDocNode\Symfony\SymfonyRouteTagValueNode;
use Rector\CodingStyle\Application\UseAddingCommander;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockManipulator;
use Rector\PHPStan\Type\AliasedObjectType;
use Rector\PHPStan\Type\FullyQualifiedObjectType;

final class ImplicitToExplicitRoutingAnnotationDecorator
{
    /**
     * @var string
     */
    public const HAS_ROUTE_ANNOTATION = 'has_route_annotation';

    /**
     * @var DocBlockManipulator
     */
    private $docBlockManipulator;

    /**
     * @var UseAddingCommander
     */
    private $useAddingCommander;

    public function __construct(DocBlockManipulator $docBlockManipulator, UseAddingCommander $useAddingCommander)
    {
        $this->docBlockManipulator = $docBlockManipulator;
        $this->useAddingCommander = $useAddingCommander;
    }

    public function decorateClassMethodWithRouteAnnotation(
        ClassMethod $classMethod,
        SymfonyRouteTagValueNode $symfonyRouteTagValueNode
    ): void {
        $this->docBlockManipulator->addTagValueNodeWithShortName($classMethod, $symfonyRouteTagValueNode);

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
