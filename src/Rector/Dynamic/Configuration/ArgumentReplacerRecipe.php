<?php declare(strict_types=1);

namespace Rector\Rector\Dynamic\Configuration;

use Rector\Exception\Rector\InvalidRectorConfigurationException;
use Rector\Rector\Dynamic\ArgumentReplacerRector;

final class ArgumentReplacerRecipe
{
    /**
     * @var string
     */
    public const TYPE_REMOVED = 'removed';

    /**
     * @var string
     */
    public const TYPE_CHANGED = 'changed';

    /**
     * @var string
     */
    public const TYPE_REPLACED_DEFAULT_VALUE = 'replace_default_value';

    /**
     * @var string
     */
    private $class;

    /**
     * @var string
     */
    private $method;

    /**
     * @var int
     */
    private $position;

    /**
     * @var string
     */
    private $type;

    /**
     * @var mixed
     */
    private $defaultValue;

    /**
     * @var string[]
     */
    private $replaceMap = [];

    /**
     * @param mixed $defaultValue
     * @param string[] $replaceMap
     */
    private function __construct(
        string $class,
        string $method,
        int $position,
        string $type,
        $defaultValue = null,
        array $replaceMap
    ) {
        $this->class = $class;
        $this->method = $method;
        $this->position = $position;
        $this->type = $type;
        $this->defaultValue = $defaultValue;
        $this->replaceMap = $replaceMap;
    }

    /**
     * @param mixed[] $data
     */
    public static function createFromArray(array $data): self
    {
        self::validateArrayData($data);

        return new self(
            $data['class'],
            $data['method'],
            $data['position'],
            $data['type'],
            $data['default_value'] ?? null,
            $data['replace_map'] ?? []
        );
    }

    public function getClass(): string
    {
        return $this->class;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return mixed|null
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    /**
     * @return string[]
     */
    public function getReplaceMap(): array
    {
        return $this->replaceMap;
    }

    /**
     * @param mixed[] $data
     */
    private static function ensureHasKey(array $data, string $key): void
    {
        if (isset($data[$key])) {
            return;
        }

        throw new InvalidRectorConfigurationException(sprintf(
            'Configuration for "%s" Rector should have "%s" key, but is missing.',
            ArgumentReplacerRector::class,
            $key
        ));
    }

    /**
     * @param mixed[] $data
     */
    private static function validateArrayData(array $data): void
    {
        self::ensureHasKey($data, 'class');
        self::ensureHasKey($data, 'class');
        self::ensureHasKey($data, 'method');
        self::ensureHasKey($data, 'position');
        self::ensureHasKey($data, 'type');

        if ($data['type'] === self::TYPE_REPLACED_DEFAULT_VALUE) {
            self::ensureHasKey($data, 'replace_map');
        }
    }
}
