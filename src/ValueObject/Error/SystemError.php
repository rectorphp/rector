<?php

declare (strict_types=1);
namespace Rector\Core\ValueObject\Error;

use Rector\Parallel\ValueObject\Name;
use RectorPrefix202211\Symplify\EasyParallel\Contract\SerializableInterface;
final class SystemError implements SerializableInterface
{
    /**
     * @readonly
     * @var string
     */
    private $message;
    /**
     * @readonly
     * @var string|null
     */
    private $relativeFilePath = null;
    /**
     * @readonly
     * @var int|null
     */
    private $line = null;
    /**
     * @readonly
     * @var string|null
     */
    private $rectorClass = null;
    public function __construct(string $message, ?string $relativeFilePath = null, ?int $line = null, ?string $rectorClass = null)
    {
        $this->message = $message;
        $this->relativeFilePath = $relativeFilePath;
        $this->line = $line;
        $this->rectorClass = $rectorClass;
    }
    public function getMessage() : string
    {
        return $this->message;
    }
    public function getFile() : ?string
    {
        return $this->relativeFilePath;
    }
    public function getLine() : ?int
    {
        return $this->line;
    }
    public function getFileWithLine() : string
    {
        return $this->relativeFilePath . ':' . $this->line;
    }
    public function getRelativeFilePath() : ?string
    {
        return $this->relativeFilePath;
    }
    /**
     * @return array{message: string, relative_file_path: string|null, line: int|null, rector_class: string|null}
     */
    public function jsonSerialize() : array
    {
        return [Name::MESSAGE => $this->message, Name::RELATIVE_FILE_PATH => $this->relativeFilePath, Name::LINE => $this->line, Name::RECTOR_CLASS => $this->rectorClass];
    }
    /**
     * @param mixed[] $json
     */
    public static function decode(array $json) : SerializableInterface
    {
        return new self($json[Name::MESSAGE], $json[Name::RELATIVE_FILE_PATH], $json[Name::LINE], $json[Name::RECTOR_CLASS]);
    }
    public function getRectorClass() : ?string
    {
        return $this->rectorClass;
    }
}
