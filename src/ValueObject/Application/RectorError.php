<?php

declare (strict_types=1);
namespace Rector\Core\ValueObject\Application;

use Rector\Core\Contract\Rector\RectorInterface;
use RectorPrefix20211123\Symplify\EasyParallel\Contract\SerializableInterface;
final class RectorError implements \RectorPrefix20211123\Symplify\EasyParallel\Contract\SerializableInterface
{
    /**
     * @var string
     */
    private $message;
    /**
     * @var string
     */
    private $relativeFilePath;
    /**
     * @var int|null
     */
    private $line;
    /**
     * @var class-string<\Rector\Core\Contract\Rector\RectorInterface>|null
     */
    private $rectorClass;
    /**
     * @param class-string<RectorInterface>|null $rectorClass
     */
    public function __construct(string $message, string $relativeFilePath, ?int $line = null, ?string $rectorClass = null)
    {
        $this->message = $message;
        $this->relativeFilePath = $relativeFilePath;
        $this->line = $line;
        $this->rectorClass = $rectorClass;
    }
    public function getRelativeFilePath() : string
    {
        return $this->relativeFilePath;
    }
    public function getMessage() : string
    {
        return $this->message;
    }
    public function getLine() : ?int
    {
        return $this->line;
    }
    /**
     * @return class-string<RectorInterface>|null
     */
    public function getRectorClass() : ?string
    {
        return $this->rectorClass;
    }
    /**
     * @param array<string, mixed> $json
     */
    public static function decode($json) : \RectorPrefix20211123\Symplify\EasyParallel\Contract\SerializableInterface
    {
        return new self($json['message'], $json['relative_file_path'], $json['line'], $json['rector_class']);
    }
    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize() : array
    {
        return ['message' => $this->message, 'relative_file_path' => $this->relativeFilePath, 'line' => $this->line, 'rector_class' => $this->rectorClass];
    }
}
