<?php

declare(strict_types=1);

namespace Rector\Php80\ValueObject;

final class AnnotationToAttribute
{
    /**
     * @var class-string<\PHPStan\PhpDocParser\Ast\Node>
     */
    private $tagNodeClass;

    /**
     * @var class-string
     */
    private $attributeClass;

    /**
     * @param class-string<\PHPStan\PhpDocParser\Ast\Node> $tagNodeClass
     * @param class-string $attributeClass
     */
    public function __construct(string $tagNodeClass, string $attributeClass)
    {
        $this->tagNodeClass = $tagNodeClass;
        $this->attributeClass = $attributeClass;
    }

    /**
     * @return class-string<\PHPStan\PhpDocParser\Ast\Node>
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
