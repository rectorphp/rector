<?php declare(strict_types=1);

namespace Rector\DeprecationExtractor\Transformer;

use Nette\Utils\Strings;
use PhpParser\Node\Arg;
use Rector\DeprecationExtractor\Contract\Deprecation\DeprecationInterface;
use Rector\DeprecationExtractor\Deprecation\ClassMethodDeprecation;
use Rector\DeprecationExtractor\Deprecation\RemovedFunctionalityDeprecation;
use Rector\DeprecationExtractor\RegExp\ClassAndMethodMatcher;
use Rector\Exception\NotImplementedException;
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

    public function __construct(ClassAndMethodMatcher $classAndMethodMatcher, NodeValueResolver $nodeValueResolver)
    {
        $this->classAndMethodMatcher = $classAndMethodMatcher;
        $this->nodeValueResolver = $nodeValueResolver;
    }

    public function transform(Arg $argNode): ?DeprecationInterface
    {
//        if ($argNode->value instanceof Variable) {
//            // @todo: get value?
//            $message = '$' . $argNode->value->name;
//        } elseif ($argNode->value instanceof MethodCall) {
//            $message = $this->standardPrinter->prettyPrint([$argNode->value]);
//        }

        $message = $this->nodeValueResolver->resolve($argNode->value);

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
        $oldMethod = $this->classAndMethodMatcher->matchClassWithMethod($oldMessage);
        $newMethod = $this->classAndMethodMatcher->matchClassWithMethod($newMessage);

        return new ClassMethodDeprecation($oldMethod, $newMethod);
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
