<?php declare(strict_types=1);

namespace Rector\DeprecationExtractor\Transformer;

use Nette\Utils\Strings;
use PhpParser\Node\Arg;
use Rector\DeprecationExtractor\Contract\Deprecation\DeprecationInterface;
use Rector\DeprecationExtractor\Deprecation\ClassMethodDeprecation;
use Rector\DeprecationExtractor\Deprecation\RemovedFunctionalityDeprecation;
use Rector\DeprecationExtractor\RegExp\ClassAndMethodMatcher;
use Rector\Exception\NotImplementedException;
use Rector\Node\Attribute;
use Rector\NodeValueResolver\Message\ClassPrepender;
use Rector\NodeValueResolver\NodeValueResolver;

final class ArgumentToDeprecationTransformer
{
    /**
     * @var ClassAndMethodMatcher
     */
    private $classAndMethodMatcher;

    /**
     * @var NodeValueResolver
     */
    private $nodeValueResolver;

    public function __construct(
        ClassAndMethodMatcher $classAndMethodMatcher,
        NodeValueResolver $nodeValueResolver,
        ClassPrepender $classPrepender
    ) {
        $this->classAndMethodMatcher = $classAndMethodMatcher;
        $this->nodeValueResolver = $nodeValueResolver;
        $this->classPrepender = $classPrepender;
    }

    public function transform(Arg $argNode): ?DeprecationInterface
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

        return $this->createFromMessage($message);
    }

    public function tryToCreateClassMethodDeprecation(string $oldMessage, string $newMessage): ?DeprecationInterface
    {
        $oldMethod = $this->classAndMethodMatcher->matchClassWithMethodWithoutArguments($oldMessage);
        $newMethod = $this->classAndMethodMatcher->matchClassWithMethodWithoutArguments($newMessage);

        $oldArguments = $this->classAndMethodMatcher->matchMethodArguments($oldMessage);
        $newArguments = $this->classAndMethodMatcher->matchMethodArguments($newMessage);

        return new ClassMethodDeprecation(
            $oldMethod,
            $newMethod,
            $oldArguments,
            $newArguments
        );
    }

    private function createFromMessage(string $message): DeprecationInterface
    {
        $result = Strings::split($message, '#use |Use#');

        if (count($result) === 2) {
            [$oldMessage, $newMessage] = $result;
            $deprecation = $this->tryToCreateClassMethodDeprecation($oldMessage, $newMessage);
            if ($deprecation) {
                return $deprecation;
            }
        }

        return new RemovedFunctionalityDeprecation($message);
    }
}
