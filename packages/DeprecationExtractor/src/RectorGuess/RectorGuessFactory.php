<?php declare(strict_types=1);

namespace Rector\DeprecationExtractor\RectorGuess;

use PhpParser\Node;
use Rector\Rector\Dynamic\ClassReplacerRector;
use Rector\Rector\Dynamic\MethodArgumentChangerRector;
use Rector\Rector\Dynamic\MethodNameReplacerRector;

final class RectorGuessFactory
{
    public function createRemoval(string $message, Node $node): RectorGuess
    {
        return new RectorGuess(
            RectorGuess::TYPE_REMOVAL,
            $node,
            $message
        );
    }

    public function createClassReplacer(string $className, string $message, Node $node): RectorGuess
    {
        return new RectorGuess(
            ClassReplacerRector::class,
            $node,
            $className . ' - ' . $message
        );
    }

    public function createMethodNameReplacerGuess(string $message, Node $node): RectorGuess
    {
        return new RectorGuess(
            MethodNameReplacerRector::class,
            $node,
            $message
        );
    }

    public function createYamlConfiguration(string $message, Node $node): RectorGuess
    {
        return new RectorGuess(
            RectorGuess::YAML_CONFIGURATION,
            $node,
            $message
        );
    }

    public function createService(string $message, Node $node): RectorGuess
    {
        return new RectorGuess(
            RectorGuess::SERVICE,
            $node,
            $message
        );
    }

    public function createNewArgument(string $message, Node $node): RectorGuess
    {
        return new RectorGuess(
            MethodArgumentChangerRector::class,
            $node,
            $message
        );
    }
}
