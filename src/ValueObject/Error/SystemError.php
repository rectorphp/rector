<?php

declare (strict_types=1);
namespace Rector\ValueObject\Error;

use RectorPrefix202506\Nette\Utils\Strings;
use Rector\Parallel\ValueObject\BridgeItem;
use RectorPrefix202506\Symplify\EasyParallel\Contract\SerializableInterface;
final class SystemError implements SerializableInterface
{
    /**
     * @readonly
     */
    private string $message;
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
    public function getLine() : ?int
    {
        return $this->line;
    }
    public function getRelativeFilePath() : ?string
    {
        return $this->relativeFilePath;
    }
    public function getAbsoluteFilePath() : ?string
    {
        if ($this->relativeFilePath === null) {
            return null;
        }
        return \realpath($this->relativeFilePath);
    }
    /**
     * @return array{
     *     message: string,
     *     relative_file_path: string|null,
     *     absolute_file_path: string|null,
     *     line: int|null,
     *     rector_class: string|null
     * }
     */
    public function jsonSerialize() : array
    {
        return [BridgeItem::MESSAGE => $this->message, BridgeItem::RELATIVE_FILE_PATH => $this->relativeFilePath, BridgeItem::ABSOLUTE_FILE_PATH => $this->getAbsoluteFilePath(), BridgeItem::LINE => $this->line, BridgeItem::RECTOR_CLASS => $this->rectorClass];
    }
    /**
     * @param mixed[] $json
     */
    public static function decode(array $json) : self
    {
        return new self($json[BridgeItem::MESSAGE], $json[BridgeItem::RELATIVE_FILE_PATH], $json[BridgeItem::LINE], $json[BridgeItem::RECTOR_CLASS]);
    }
    public function getRectorClass() : ?string
    {
        return $this->rectorClass;
    }
    public function getRectorShortClass() : ?string
    {
        $rectorClass = $this->rectorClass;
        if ($rectorClass !== null && $rectorClass !== '' && $rectorClass !== '0') {
            return (string) Strings::after($rectorClass, '\\', -1);
        }
        return null;
    }
}
