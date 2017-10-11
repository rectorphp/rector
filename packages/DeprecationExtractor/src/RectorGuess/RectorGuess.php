<?php declare(strict_types=1);

namespace Rector\DeprecationExtractor\RectorGuess;

use PhpParser\Node;

final class RectorGuess
{
    /**
     * @var string
     */
    public const TYPE_REMOVAL = 'REMOVAL';

    /**
     * @var string
     */
    public const TYPE_UNSUPPORTED = 'UNSUPPORTED';

    /**
     * @var string
     */
    private $guessedRectorClass;

    /**
     * @var Node
     */
    private $node;

    /**
     * @var string
     */
    private $message;

    public function __construct(
        string $rectorClassOrType,
        Node $node,
        string $message
    ) {
        $this->guessedRectorClass = $rectorClassOrType;
        $this->node = $node;
        $this->message = $message;
    }

    public function getGuessedRectorClass(): string
    {
        return $this->guessedRectorClass;
    }

    public function getNode(): Node
    {
        return $this->node;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function isUseful(): bool
    {
        return class_exists($this->guessedRectorClass);
    }
}
