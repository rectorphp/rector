<?php declare(strict_types=1);

namespace Rector\DeprecationExtractor\Rector;

use PhpParser\Node;

final class RectorGuess
{
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
        return $this->node;
    }

    public function getMessage(): string
    {
        return $this->message;
    }
}
