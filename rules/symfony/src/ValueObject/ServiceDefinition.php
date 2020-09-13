<?php

declare(strict_types=1);

namespace Rector\Symfony\ValueObject;

use Rector\Symfony\Contract\Tag\TagInterface;

final class ServiceDefinition
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var bool
     */
    private $isPublic = false;

    /**
     * @var bool
     */
    private $isSynthetic = false;

    /**
     * @var TagInterface[]
     */
    private $tags = [];

    /**
     * @var string|null
     */
    private $class;

    /**
     * @var string|null
     */
    private $alias;

    /**
     * @param TagInterface[] $tags
     */
    public function __construct(string $id, ?string $class, bool $public, bool $synthetic, ?string $alias, array $tags)
    {
        $this->id = $id;
        $this->class = $class;
        $this->isPublic = $public;
        $this->isSynthetic = $synthetic;
        $this->alias = $alias;
        $this->tags = $tags;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getClass(): ?string
    {
        return $this->class;
    }

    public function isPublic(): bool
    {
        return $this->isPublic;
    }

    public function isSynthetic(): bool
    {
        return $this->isSynthetic;
    }

    public function getAlias(): ?string
    {
        return $this->alias;
    }

    /**
     * @return TagInterface[]
     */
    public function getTags(): array
    {
        return $this->tags;
    }

    public function getTag(string $name): ?TagInterface
    {
        foreach ($this->tags as $tag) {
            if ($tag->getName() !== $name) {
                continue;
            }

            return $tag;
        }

        return null;
    }
}
