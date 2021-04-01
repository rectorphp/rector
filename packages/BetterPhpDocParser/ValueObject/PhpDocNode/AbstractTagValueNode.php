<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\ValueObject\PhpDocNode;

use PHPStan\PhpDocParser\Ast\NodeAttributes;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\TagValueNodeConfiguration;
use Rector\BetterPhpDocParser\ValueObjectFactory\TagValueNodeConfigurationFactory;
use Rector\Core\Exception\ShouldNotHappenException;

abstract class AbstractTagValueNode implements PhpDocTagValueNode
{
    use NodeAttributes;

    /**
     * @var mixed[]
     */
    protected $items = [];

    /**
     * @var TagValueNodeConfiguration
     */
    protected $tagValueNodeConfiguration;

    /**
     * @param array<string, mixed> $items
     */
    public function __construct(array $items = [], ?string $originalContent = null)
    {
        $this->items = $items;
        $this->resolveOriginalContentSpacingAndOrder($originalContent);
    }

    /**
     * Generic fallback
     */
    public function __toString(): string
    {
        throw new ShouldNotHappenException(
            'For back compatibility, use DoctrineAnnotationTagValueNode __toStirng() instead'
        );
    }

    /**
     * @return mixed[]
     */
    public function getItems(): array
    {
        return $this->items;
    }

    protected function resolveOriginalContentSpacingAndOrder(?string $originalContent): void
    {
        $tagValueNodeConfigurationFactory = new TagValueNodeConfigurationFactory();

        // prevent override
        if ($this->tagValueNodeConfiguration !== null) {
            throw new ShouldNotHappenException();
        }

        $this->tagValueNodeConfiguration = $tagValueNodeConfigurationFactory->createFromOriginalContent(
            $originalContent,
            $this
        );
    }
}
