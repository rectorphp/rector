<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\ValueObjectFactory\PhpDocNode\Symfony;

use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\BetterPhpDocParser\Printer\ArrayPartPhpDocTagPrinter;

final class SymfonyRouteTagValueNodeFactory
{
    /**
     * @var ArrayPartPhpDocTagPrinter
     */
    private $arrayPartPhpDocTagPrinter;

    public function __construct(ArrayPartPhpDocTagPrinter $arrayPartPhpDocTagPrinter)
    {
        $this->arrayPartPhpDocTagPrinter = $arrayPartPhpDocTagPrinter;
    }

    public function create(): DoctrineAnnotationTagValueNode
    {
        return $this->createFromItems([]);
    }

    /**
     * @param array<string, mixed> $items
     */
    public function createFromItems(array $items): DoctrineAnnotationTagValueNode
    {
        return new DoctrineAnnotationTagValueNode(
            $this->arrayPartPhpDocTagPrinter,
            'Symfony\Component\Routing\Annotation\Route',
            null,
            $items,
            'path'
        );
    }
}
