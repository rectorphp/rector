<?php

declare(strict_types=1);

namespace Rector\NetteToSymfony\Routing;

use PhpParser\Node\Stmt\ClassMethod;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Symfony\SymfonyRouteTagValueNode;
use Rector\PostRector\Collector\UseNodesToAddCollector;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;

final class ExplicitRouteAnnotationDecorator
{
    /**
     * @var string
     */
    public const HAS_ROUTE_ANNOTATION = 'has_route_annotation';

    /**
     * @var UseNodesToAddCollector
     */
    private $useNodesToAddCollector;

    /**
     * @var PhpDocInfoFactory
     */
    private $phpDocInfoFactory;

    public function __construct(UseNodesToAddCollector $useNodesToAddCollector, PhpDocInfoFactory $phpDocInfoFactory)
    {
        $this->useNodesToAddCollector = $useNodesToAddCollector;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
    }

    public function decorateClassMethodWithRouteAnnotation(
        ClassMethod $classMethod,
        SymfonyRouteTagValueNode $symfonyRouteTagValueNode
    ): void {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($classMethod);
        $phpDocInfo->addTagValueNodeWithShortName($symfonyRouteTagValueNode);

        $fullyQualifiedObjectType = new FullyQualifiedObjectType(SymfonyRouteTagValueNode::CLASS_NAME);
        $this->useNodesToAddCollector->addUseImport($classMethod, $fullyQualifiedObjectType);

        // remove
        $this->useNodesToAddCollector->removeShortUse($classMethod, 'Route');

        $classMethod->setAttribute(self::HAS_ROUTE_ANNOTATION, true);
    }
}
