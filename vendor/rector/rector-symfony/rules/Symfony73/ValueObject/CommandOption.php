<?php

declare (strict_types=1);
namespace Rector\Symfony\Symfony73\ValueObject;

final class CommandOption
{
    /**
     * @readonly
     */
    private string $name;
    public function __construct(string $name)
    {
        $this->name = $name;
    }
    public function getName() : string
    {
        return $this->name;
    }
}
