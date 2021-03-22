<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Contract;

use Doctrine\ORM\Mapping\Annotation;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\AbstractTagValueNode;

interface MultiPhpDocNodeFactoryInterface
{
    /**
     * @return array<class-string<AbstractTagValueNode>, class-string<Annotation>>
     */
    public function getTagValueNodeClassesToAnnotationClasses(): array;
}
