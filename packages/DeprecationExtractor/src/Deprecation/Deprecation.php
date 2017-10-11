<?php declare(strict_types=1);

namespace Rector\DeprecationExtractor\Deprecation;

use PhpParser\Node;

final class Deprecation
{
    /**
     * @var string
     */
    private $message;

    /**
     * @var Node
     */
    private $node;

    private function __construct(string $message, Node $node)
    {
        $this->message = $message;
        $this->node = $node;
    }

    public static function createFromMessageAndNode(string $message, Node $node): self
    {
        return new self($message, $node);
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getNode(): Node
    {
        return $this->node;
    }
}
