<?php

declare (strict_types=1);
namespace Rector\ChangesReporting\ValueObject;

use Rector\Core\Contract\Rector\RectorInterface;
use RectorPrefix20211123\Symplify\EasyParallel\Contract\SerializableInterface;
final class RectorWithLineChange implements \RectorPrefix20211123\Symplify\EasyParallel\Contract\SerializableInterface
{
    /**
     * @var string
     */
    private const KEY_RECTOR_CLASS = 'rector_class';
    /**
     * @var string
     */
    private const KEY_LINE = 'line';
    /**
     * @var class-string<RectorInterface>
     */
    private $rectorClass;
    /**
     * @var int
     */
    private $line;
    /**
     * @param \Rector\Core\Contract\Rector\RectorInterface|string $rectorClass
     */
    public function __construct($rectorClass, int $line)
    {
        $this->line = $line;
        if ($rectorClass instanceof \Rector\Core\Contract\Rector\RectorInterface) {
            $rectorClass = \get_class($rectorClass);
        }
        $this->rectorClass = $rectorClass;
    }
    /**
     * @return class-string<RectorInterface>
     */
    public function getRectorClass() : string
    {
        return $this->rectorClass;
    }
    public function getLine() : int
    {
        return $this->line;
    }
    /**
     * @param array<string, mixed> $json
     */
    public static function decode($json) : \RectorPrefix20211123\Symplify\EasyParallel\Contract\SerializableInterface
    {
        return new self($json[self::KEY_RECTOR_CLASS], $json[self::KEY_LINE]);
    }
    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize() : array
    {
        return [self::KEY_RECTOR_CLASS => $this->rectorClass, self::KEY_LINE => $this->line];
    }
}
