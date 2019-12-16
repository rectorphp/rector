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

    /**
     * @var array
     */
    private $data = [];

    public function __construct(string $name, array $data)
    {
        $this->name = $name;
        $this->data = $data;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getData(): array
    {
        return $this->data;
    }
}
