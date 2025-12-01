<?php

declare (strict_types=1);
namespace Rector\ChangesReporting\ValueObject;

use Rector\Contract\Rector\RectorInterface;
use Rector\PostRector\Contract\Rector\PostRectorInterface;
use RectorPrefix202512\Symplify\EasyParallel\Contract\SerializableInterface;
use RectorPrefix202512\Webmozart\Assert\Assert;
final class RectorWithLineChange implements SerializableInterface
{
    /**
     * @var class-string<RectorInterface|PostRectorInterface>
     * @readonly
     */
    private string $rectorClass;
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
     * @param class-string<RectorInterface|PostRectorInterface> $rectorClass
     */
    public function __construct(string $rectorClass, int $line)
    {
        $this->rectorClass = $rectorClass;
        $this->line = $line;
    }
    /**
     * @return class-string<RectorInterface|PostRectorInterface>
     */
    public function getRectorClass(): string
    {
        return $this->rectorClass;
    }
    /**
     * @param array<string, mixed> $json
     */
    public static function decode(array $json): self
    {
        /** @var class-string<RectorInterface> $rectorClass */
        $rectorClass = $json[self::KEY_RECTOR_CLASS];
        Assert::string($rectorClass);
        $line = $json[self::KEY_LINE];
        Assert::integer($line);
        return new self($rectorClass, $line);
    }
    /**
     * @return array{rector_class: class-string<RectorInterface|PostRectorInterface>, line: int}
     */
    public function jsonSerialize(): array
    {
        return [self::KEY_RECTOR_CLASS => $this->rectorClass, self::KEY_LINE => $this->line];
    }
}
