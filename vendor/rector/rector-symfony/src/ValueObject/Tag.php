<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Symfony\ValueObject;

use RectorPrefix20220606\Rector\Symfony\Contract\Tag\TagInterface;
final class Tag implements TagInterface
{
    /**
     * @readonly
     * @var string
     */
    private $name;
    /**
     * @var array<string, mixed>
     * @readonly
     */
    private $data = [];
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
