<?php declare(strict_types=1);

namespace Rector\PhpParser\Node;

use PHPStan\Type\Type;
use PHPStan\Type\VerbosityLevel;

final class VariableInfo
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var Type
     */
    private $type;

    public function __construct(string $name, Type $type)
    {
        $this->name = $name;
        $this->type = $type;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): Type
    {
        return $this->type;
    }

    public function getTypeAsString(): string
    {
        return $this->type->describe(VerbosityLevel::typeOnly());
    }
}
