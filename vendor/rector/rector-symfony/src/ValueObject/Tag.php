<?php

declare (strict_types=1);
namespace Rector\Symfony\ValueObject;

use Rector\Symfony\Contract\Tag\TagInterface;
final class Tag implements TagInterface
{
    /**
     * @readonly
     */
    private string $name;
    /**
     * @var array<string, mixed>
     * @readonly
     */
    private array $data = [];
    /**
     * @param array<string, mixed> $data
     */
    public function __construct(string $name, array $data = [])
    {
        $this->name = $name;
        $this->data = $data;
    }
    public function getName() : string
    {
        return $this->name;
    }
    /**
     * @return array<string, mixed>
     */
    public function getData() : array
    {
        return $this->data;
    }
}
