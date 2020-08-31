<?php

declare(strict_types=1);

namespace Rector\CodingStyle\ValueObject;

final class ConcatStringAndPlaceholders
{
    /**
     * @var string
     */
    private $content;

    /**
     * @var array
     */
    private $placeholderNodes = [];

    public function __construct(string $content, array $placeholderNodes)
    {
        $this->content = $content;
        $this->placeholderNodes = $placeholderNodes;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * @return mixed[]
     */
    public function getPlaceholderNodes(): array
    {
        return $this->placeholderNodes;
    }
}
