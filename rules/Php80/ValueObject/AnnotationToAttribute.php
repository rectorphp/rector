<?php

declare(strict_types=1);

namespace Rector\Php80\ValueObject;

use PHPStan\PhpDocParser\Ast\Node;

final class AnnotationToAttribute
{
    /**
     * @var class-string<Node>
     */
    private $tagNodeClass;

    /**
     * @var class-string
     */
    private $attributeClass;

    /**
     * @param class-string<Node> $tagNodeClass
     * @param class-string $attributeClass
     */
    public function __construct(string $tagNodeClass, string $attributeClass)
    {
        $this->tagNodeClass = $tagNodeClass;
        $this->attributeClass = $attributeClass;
    }

    /**
     * @return class-string<Node>
     */
    public function getTagNodeClass(): string
    {
        return $this->tagNodeClass;
    }

    /**
     * @return class-string
     */
    public function getAttributeClass(): string
    {
        return $this->attributeClass;
    }
}
