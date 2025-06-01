<?php

declare (strict_types=1);
namespace Rector\ChangesReporting\ValueObject;

use Rector\Contract\Rector\RectorInterface;
use RectorPrefix202506\Symplify\EasyParallel\Contract\SerializableInterface;
use RectorPrefix202506\Webmozart\Assert\Assert;
final class RectorWithLineChange implements SerializableInterface
{
    /**
     * @readonly
     */
    private int $line;
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
    private string $rectorClass;
    /**
     * @param class-string<RectorInterface>|RectorInterface $rectorClass
     */
    public function __construct($rectorClass, int $line)
    {
        $this->line = $line;
        if ($rectorClass instanceof RectorInterface) {
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
    /**
     * @param array<string, mixed> $json
     */
    public static function decode(array $json) : self
    {
        /** @var class-string<RectorInterface> $rectorClass */
        $rectorClass = $json[self::KEY_RECTOR_CLASS];
        Assert::string($rectorClass);
        $line = $json[self::KEY_LINE];
        Assert::integer($line);
        return new self($rectorClass, $line);
    }
    /**
     * @return array{rector_class: class-string<RectorInterface>, line: int}
     */
    public function jsonSerialize() : array
    {
        return [self::KEY_RECTOR_CLASS => $this->rectorClass, self::KEY_LINE => $this->line];
    }
}
