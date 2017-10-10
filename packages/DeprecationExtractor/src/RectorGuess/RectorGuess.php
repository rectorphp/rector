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
    public const YAML_CONFIGURATION = 'YAML_CONFIGURATION';

    /**
     * @var string
     */
    private $guessedRectorClass;

    /**
     * @var float
     */
    private $certainity;

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
        float $certainity,
        Node $node,
        string $message = ''
    ) {
        $this->guessedRectorClass = $guessedRectorClass;
        $this->certainity = $certainity;
        $this->node = $node;
        $this->message = $message;
    }

    public function getGuessedRectorClass(): string
    {
        return $this->guessedRectorClass;
    }

    public function getCertainity(): float
    {
        return $this->certainity;
    }

    public function getNode(): Node
    {
        if ($this->node instanceof Node\Arg) {
            return $this->node->value;
        }

        return $this->node;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getNodeClass(): string
    {
        return get_class($this->getNode());
    }

    public function canBeCreated(): bool
    {
        return class_exists($this->guessedRectorClass);
    }
}
