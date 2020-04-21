<?php

declare(strict_types=1);

namespace Rector\Symfony\ValueObject;

use Rector\Symfony\Contract\Tag\TagInterface;

final class Tag implements TagInterface
{
    /**
     * @var string
     */
    private $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
