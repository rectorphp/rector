<?php declare(strict_types=1);

namespace Rector\DoctrinePhpDocParser\Ast\PhpDoc\Property_;

use Nette\Utils\Json;
use Nette\Utils\Strings;
use Rector\DoctrinePhpDocParser\Ast\PhpDoc\AbstractDoctrineTagValueNode;

final class ColumnTagValueNode extends AbstractDoctrineTagValueNode
{
    /**
     * @var string|null
     */
    private $name;

    /**
     * @var mixed
     */
    private $type;

    /**
     * @var mixed
     */
    private $length;

    /**
     * @var int
     */
    private $precision;

    /**
     * @var int
     */
    private $scale;

    /**
     * @var bool
     */
    private $unique = false;

    /**
     * @var bool
     */
    private $nullable = false;

    /**
     * @var mixed[]
     */
    private $options = [];

    /**
     * @var string|null
     */
    private $columnDefinition;

    /**
     * @param mixed[] $options
     * @param mixed $type
     * @param mixed $length
     * @param string[] $orderedVisibleItems
     */
    public function __construct(
        ?string $name,
        $type,
        $length,
        int $precision,
        int $scale,
        bool $unique,
        bool $nullable,
        array $options,
        ?string $columnDefinition,
        array $orderedVisibleItems
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
        $this->orderedVisibleItems = $orderedVisibleItems;
    }

    public function __toString(): string
    {
        $contentItems = [];

        // required
        $contentItems['type'] = sprintf('type="%s"', $this->type);
        $contentItems['name'] = sprintf('name="%s"', $this->name);
        $contentItems['length'] = sprintf('length=%s', $this->length);
        $contentItems['precision'] = sprintf('precision=%s', $this->precision);
        $contentItems['scale'] = sprintf('scale=%s', $this->scale);
        $contentItems['unique'] = sprintf('unique=%s', $this->unique ? 'true' : 'false');
        $contentItems['nullable'] = sprintf('nullable=%s', $this->nullable ? 'true' : 'false');

        if ($this->options !== []) {
            $optionsContent = Json::encode($this->options);
            $optionsContent = Strings::replace($optionsContent, '#,#', ', ');
            $contentItems['options'] = 'options=' . $optionsContent;
        }

        $contentItems['columnDefinition'] = sprintf('columnDefinition="%s"', $this->columnDefinition);

        return $this->printContentItems($contentItems);
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    public function isNullable(): bool
    {
        return $this->nullable;
    }
}
