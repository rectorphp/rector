<?php declare(strict_types=1);

namespace Rector\DeprecationExtractor\RectorGuess;

use PhpParser\Node;
use PhpParser\Node\Arg;

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
        string $guessedRectorClass,
        Node $node,
        string $message = ''
    ) {
        $this->guessedRectorClass = $guessedRectorClass;
        $this->node = $node;
        $this->message = $message;
    }

    public function getGuessedRectorClass(): string
    {
        return $this->guessedRectorClass;
    }

    public function getNode(): Node
    {
        if ($this->node instanceof Arg) {
            return $this->node->value;
        }

        return $this->node;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function canBeCreated(): bool
    {
        return class_exists($this->guessedRectorClass);
    }
}
