<?php

declare(strict_types=1);

namespace Rector\NetteToSymfony\Routing;

use PhpParser\Node\Stmt\ClassMethod;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;

final class ExplicitRouteAnnotationDecorator
{
    /**
     * @var string
     */
    public const HAS_ROUTE_ANNOTATION = 'has_route_annotation';

    /**
     * @var PhpDocInfoFactory
     */
    private $phpDocInfoFactory;

    public function __construct(PhpDocInfoFactory $phpDocInfoFactory)
    {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
    }

    public function decorateClassMethodWithRouteAnnotation(
        ClassMethod $classMethod,
        DoctrineAnnotationTagValueNode $doctrineAnnotationTagValueNode
    ): void {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($classMethod);
        $phpDocInfo->addTagValueNode($doctrineAnnotationTagValueNode);
        $phpDocInfo->makeMultiLined();

        $classMethod->setAttribute(self::HAS_ROUTE_ANNOTATION, true);
    }
}
