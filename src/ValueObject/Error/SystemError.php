<?php

declare (strict_types=1);
namespace Rector\Core\ValueObject\Error;

use Rector\Parallel\ValueObject\Name;
use RectorPrefix20211123\Symplify\EasyParallel\Contract\SerializableInterface;
final class SystemError implements \RectorPrefix20211123\Symplify\EasyParallel\Contract\SerializableInterface
{
    /**
     * @var int
     */
    private $line;
    /**
     * @var string
     */
    private $message;
    /**
     * @var string
     */
    private $relativeFilePath;
    public function __construct(int $line, string $message, string $relativeFilePath)
    {
        $this->line = $line;
        $this->message = $message;
        $this->relativeFilePath = $relativeFilePath;
    }
    public function getMessage() : string
    {
        return $this->message;
    }
    public function getFileWithLine() : string
    {
        return $this->relativeFilePath . ':' . $this->line;
    }
    /**
     * @return array{line: int, message: string, relative_file_path: string}
     */
    public function jsonSerialize() : array
    {
        return [\Rector\Parallel\ValueObject\Name::LINE => $this->line, \Rector\Parallel\ValueObject\Name::MESSAGE => $this->message, \Rector\Parallel\ValueObject\Name::RELATIVE_FILE_PATH => $this->relativeFilePath];
    }
    /**
     * @param mixed[] $json
     */
    public static function decode($json) : \RectorPrefix20211123\Symplify\EasyParallel\Contract\SerializableInterface
    {
        return new self($json[\Rector\Parallel\ValueObject\Name::LINE], $json[\Rector\Parallel\ValueObject\Name::MESSAGE], $json[\Rector\Parallel\ValueObject\Name::RELATIVE_FILE_PATH]);
    }
}
