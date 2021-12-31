<?php

declare (strict_types=1);
namespace Rector\ChangesReporting\ValueObject;

use Rector\Core\Contract\Rector\RectorInterface;
use RectorPrefix20211231\Symplify\EasyParallel\Contract\SerializableInterface;
use RectorPrefix20211231\Webmozart\Assert\Assert;
final class RectorWithLineChange implements \RectorPrefix20211231\Symplify\EasyParallel\Contract\SerializableInterface
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
     * @readonly
     */
    private $rectorClass;
    /**
     * @readonly
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
    public static function decode(array $json) : \RectorPrefix20211231\Symplify\EasyParallel\Contract\SerializableInterface
    {
        $rectorClass = $json[self::KEY_RECTOR_CLASS];
        \RectorPrefix20211231\Webmozart\Assert\Assert::string($rectorClass);
        $line = $json[self::KEY_LINE];
        \RectorPrefix20211231\Webmozart\Assert\Assert::integer($line);
        return new self($rectorClass, $line);
    }
    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize() : array
    {
        return [self::KEY_RECTOR_CLASS => $this->rectorClass, self::KEY_LINE => $this->line];
    }
}
