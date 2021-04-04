<?php

declare(strict_types=1);

namespace Rector\DependencyInjection\TypeAnalyzer;

use PhpParser\Node\Stmt\Property;
use PHPStan\Type\Type;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\Core\Exception\ShouldNotHappenException;

final class InjectTagValueNodeToServiceTypeResolver
{
    /**
     * @var JMSDITypeResolver
     */
    private $jmsdiTypeResolver;

    public function __construct(JMSDITypeResolver $jmsdiTypeResolver)
    {
        $this->jmsdiTypeResolver = $jmsdiTypeResolver;
    }

    public function resolve(
        Property $property,
        PhpDocInfo $phpDocInfo,
        DoctrineAnnotationTagValueNode $doctrineAnnotationTagValueNode
    ): Type {
        // @see https://github.com/PHP-DI/PHP-DI/blob/master/src/Annotation/Inject.php
        if ($doctrineAnnotationTagValueNode->getAnnotationClass() === 'JMS\DiExtraBundle\Annotation\Inject') {
            return $this->jmsdiTypeResolver->resolve($property, $doctrineAnnotationTagValueNode);
        }

        if ($doctrineAnnotationTagValueNode->getAnnotationClass() === 'Inject') {
            return $phpDocInfo->getVarType();
        }

        throw new ShouldNotHappenException();
    }
}
