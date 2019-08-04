<?php declare(strict_types=1);

namespace Rector\TypeDeclaration\ValueObject;

final class IdentifierValueObject
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var bool
     */
    private $isAlias = false;

    public function __construct(string $name, bool $isAlias)
    {
        $this->name = $name;
        $this->isAlias = $isAlias;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function isAlias(): bool
    {
        return $this->isAlias;
    }
}
