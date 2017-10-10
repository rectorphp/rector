<?php declare(strict_types=1);

namespace Rector\DeprecationExtractor\Rector;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Arg;
use Rector\DeprecationExtractor\RectorGuess\RectorGuessFactory;
use Rector\DeprecationExtractor\Regex\ClassAndMethodMatcher;
use Rector\Exception\NotImplementedException;
use Rector\Node\Attribute;
use Rector\NodeValueResolver\Message\ClassPrepender;
use Rector\NodeValueResolver\NodeValueResolver;

final class TriggerErrorRectorGuesser
{
    /**
     * @var NodeValueResolver
     */
    private $nodeValueResolver;

    /**
     * @var RectorGuessFactory
     */
    private $rectorGuessFactory;

    public function __construct(
        ClassAndMethodMatcher $classAndMethodMatcher,
        NodeValueResolver $nodeValueResolver,
        ClassPrepender $classPrepender,
        RectorGuessFactory $rectorGuessFactory
    ) {
        $this->nodeValueResolver = $nodeValueResolver;
        $this->classPrepender = $classPrepender;
        $this->rectorGuessFactory = $rectorGuessFactory;
    }

    /**
     * @return mixed
     */
    public function guess(Arg $argNode)
    {
        $message = $this->nodeValueResolver->resolve($argNode->value);

        if ($message === null) {
            return $message;
        }

        $message = $this->classPrepender->completeClassToLocalMethods(
            $message,
            (string) $argNode->getAttribute(Attribute::CLASS_NAME)
        );

        if ($message === '') {
            throw new NotImplementedException(sprintf(
                'Not implemented yet. Go to "%s()" and add check for "%s" node.',
                __METHOD__,
                get_class($argNode->value)
            ));
        }

        return $this->createFromMessage($argNode, $message);
    }

    /**
     * @return mixed
     */
    private function createFromMessage(Node $node, string $message)
    {
        if (Strings::contains($message, 'Autowiring-types are deprecated since')) {
            return $this->rectorGuessFactory->createYamlConfiguration($message, $node);
        }

        if (Strings::contains($message, 'configuration key') && Strings::contains($message, 'Yaml')) {
            return $this->rectorGuessFactory->createYamlConfiguration($message, $node);
        }

        $result = Strings::split($message, '#use |Use#');

        if (count($result) === 2) {

            return $this->rectorGuessFactory->createMethodNameReplacerGuess(
                $message,
                $node
            );
        }


        return $this->rectorGuessFactory->createRemoval($message, $node);
    }
}
