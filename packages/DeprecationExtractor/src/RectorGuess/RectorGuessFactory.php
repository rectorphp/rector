<?php declare(strict_types=1);

namespace Rector\DeprecationExtractor\RectorGuess;

use PhpParser\Node;
use Rector\Rector\Dynamic\ClassReplacerRector;
use Rector\Rector\Dynamic\MethodNameReplacerRector;

final class RectorGuessFactory
{
    public function create(): RectorGuess
    {
        return new RectorGuess(

        );
    }

    public function createRemoval(string $message, Node $node): RectorGuess
    {
        return new RectorGuess(
            'NONE_REMOVAL',
            0.9,
            $node,
            $message
        );
    }

    public function createClassReplacer(string $className, string $message, Node $node): RectorGuess
    {
        return new RectorGuess(
            ClassReplacerRector::class,
            0.95,
            $node,
            $className . ' - ' . $message
        );
    }

    public function createMethodNameReplacerGuess(string $message, Node $node): RectorGuess
    {
        return new RectorGuess(
            MethodNameReplacerRector::class,
            0.9,
            $node,
            $message
        );
    }
}
