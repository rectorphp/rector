<?php

declare(strict_types=1);

namespace Rector\DoctrineCodeQuality\NodeAnalyzer;

use PhpParser\Node\Stmt\Class_;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\DoctrineCodeQuality\TypeAnalyzer\TypeFinder;

final class EntityObjectTypeResolver
{
    /**
     * @var PhpDocInfoFactory
     */
    private $phpDocInfoFactory;

    /**
     * @var TypeFinder
     */
    private $typeFinder;

    public function __construct(PhpDocInfoFactory $phpDocInfoFactory, TypeFinder $typeFinder)
    {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->typeFinder = $typeFinder;
    }

    public function resolveFromRepositoryClass(Class_ $repositoryClass): Type
    {
        foreach ($repositoryClass->getMethods() as $classMethod) {
            if (! $classMethod->isPublic()) {
                continue;
            }

            $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($classMethod);

            $returnType = $phpDocInfo->getReturnType();
            $objectType = $this->typeFinder->find($returnType, ObjectType::class);
            if (! $objectType instanceof ObjectType) {
                continue;
            }

            return $objectType;
        }

        return new MixedType();
    }
}
