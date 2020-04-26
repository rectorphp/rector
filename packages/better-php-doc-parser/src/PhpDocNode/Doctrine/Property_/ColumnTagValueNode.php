<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNode\Doctrine\Property_;

use Nette\Utils\Strings;
use Rector\BetterPhpDocParser\PhpDocNode\Doctrine\AbstractDoctrineTagValueNode;

final class ColumnTagValueNode extends AbstractDoctrineTagValueNode
{
    /**
     * @var string|null
     */
    private $name;

    /**
     * @var string|null
     */
    private $type;

    /**
     * @var mixed|null
     */
    private $length;

    /**
     * @var int|null
     */
    private $precision;

    /**
     * @var int|null
     */
    private $scale;

    /**
     * @var bool|null
     */
    private $unique;

    /**
     * @var bool|null
     */
    private $nullable;

    /**
     * @var string|null
     */
    private $columnDefinition;

    /**
     * @var bool
     */
    private $isNullableUppercase = false;

    /**
     * @var mixed[]|null
     */
    private $options;

    /**
     * @param mixed[] $options
     * @param mixed|null $length
     */
    public function __construct(
        ?string $name,
        ?string $type,
        $length,
        ?int $precision = null,
        ?int $scale = null,
        ?bool $unique = null,
        ?bool $nullable = null,
        ?array $options = null,
        ?string $columnDefinition = null,
        ?string $originalContent = null
    ) {
        $this->name = $name;
        $this->type = $type;
        $this->length = $length;
        $this->precision = $precision;
        $this->scale = $scale;
        $this->unique = $unique;
        $this->nullable = $nullable;
        $this->options = $options;
        $this->columnDefinition = $columnDefinition;

        if ($originalContent !== null) {
            $this->resolveOriginalContentSpacingAndOrder($originalContent);

            $matchIsNullable = Strings::match($originalContent, '#nullable(\s+)?=(\s+)?(?<value>false|true)#si');
            if ($matchIsNullable) {
                $this->isNullableUppercase = ctype_upper($matchIsNullable['value']);
            }
        }
    }

    public function __toString(): string
    {
        $contentItems = [];

        if ($this->type !== null) {
            $contentItems['type'] = sprintf('type="%s"', $this->type);
        }

        if ($this->name !== null) {
            $contentItems['name'] = sprintf('name="%s"', $this->name);
        }

        if ($this->length !== null) {
            $contentItems['length'] = sprintf('length=%s', $this->length);
        }

        if ($this->precision !== null) {
            $contentItems['precision'] = sprintf('precision=%s', $this->precision);
        }

        if ($this->scale !== null) {
            $contentItems['scale'] = sprintf('scale=%s', $this->scale);
        }

        if ($this->columnDefinition !== null) {
            $contentItems['columnDefinition'] = sprintf('columnDefinition="%s"', $this->columnDefinition);
        }

        if ($this->options) {
            $contentItems['options'] = $this->printArrayItem($this->options, 'options');
        }

        if ($this->unique !== null) {
            $contentItems['unique'] = sprintf('unique=%s', $this->unique ? 'true' : 'false');
        }

        if ($this->nullable !== null) {
            $nullableValue = $this->nullable ? 'true' : 'false';
            if ($this->isNullableUppercase) {
                $nullableValue = strtoupper($nullableValue);
            }

            $contentItems['nullable'] = sprintf('nullable=%s', $nullableValue);
        }

        return $this->printContentItems($contentItems);
    }

    public function changeType(string $type): void
    {
        $this->type = $type;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function isNullable(): ?bool
    {
        return $this->nullable;
    }

    public function getShortName(): string
    {
        return '@ORM\Column';
    }
}
