<?php

declare (strict_types=1);
namespace Rector\Nette\ValueObject;

final class FormField
{
    /**
     * @var string
     */
    private $name;
    /**
     * @var string
     */
    private $type;
    /**
     * @var bool
     */
    private $isRequired;
    public function __construct(string $name, string $type, bool $isRequired)
    {
        $this->name = $name;
        $this->type = $type;
        $this->isRequired = $isRequired;
    }
    public function getName() : string
    {
        return $this->name;
    }
    public function getType() : string
    {
        return $this->type;
    }
    public function isRequired() : bool
    {
        return $this->isRequired;
    }
}
