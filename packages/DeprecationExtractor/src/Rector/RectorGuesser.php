<?php declare(strict_types=1);

namespace Rector\DeprecationExtractor\Rector;

use Nette\Utils\Strings;
use PhpParser\Builder\Class_;
use PhpParser\Node;
use Rector\DeprecationExtractor\Deprecation\Deprecation;
use Rector\DeprecationExtractor\RectorGuess\RectorGuess;
use Rector\DeprecationExtractor\RectorGuess\RectorGuessFactory;
use Rector\Exception\NotImplementedException;
use Rector\Node\Attribute;
use Rector\NodeValueResolver\Message\ClassPrepender;
use Rector\NodeValueResolver\NodeValueResolver;

/**
 * This class tries to guess, which Rector could be used to create refactoring
 * based on deprecation message and related options.
 */
final class RectorGuesser
{
    /**
     * @var RectorGuessFactory
     */
    private $rectorGuessFactory;

    /**
     * @var UnsupportedDeprecationFilter
     */
    private $unsupportedDeprecationFilter;

    public function __construct(
//        NodeValueResolver $nodeValueResolver,
        ClassPrepender $classPrepender,
        RectorGuessFactory $rectorGuessFactory,
        UnsupportedDeprecationFilter $unsupportedDeprecationFilter
    ) {
//        $this->nodeValueResolver = $nodeValueResolver;
        $this->classPrepender = $classPrepender;
        $this->rectorGuessFactory = $rectorGuessFactory;
        $this->unsupportedDeprecationFilter = $unsupportedDeprecationFilter;
    }

    public function guessForDeprecation(Deprecation $deprecation): ?RectorGuess
    {
        if ($this->unsupportedDeprecationFilter->matches($deprecation)) {
            return $this->rectorGuessFactory->createUnsupported($deprecation->getMessage(), $deprecation->getNode());
        }

        $message = $this->classPrepender->completeClassToLocalMethods(
            $deprecation->getMessage(),
            (string) $deprecation->getNode()->getAttribute(Attribute::CLASS_NAME)
        );

        if ($message === '') {
            throw new NotImplementedException(sprintf(
                'Not implemented yet. Go to "%s()" and add check for "%s" node.',
                __METHOD__,
                get_class($deprecation->getNode())
            ));
        }

        if (Strings::contains($message, 'It will be made mandatory in') || Strings::contains($message, 'Not defining it is deprecated since')) {
            return $this->rectorGuessFactory->createNewArgument($message, $deprecation->getNode());
        }

        $result = Strings::split($message, '#use |Use#');

        if (count($result) === 2) {
            if (Strings::contains($message, 'class is deprecated')) {
                return $this->rectorGuessFactory->createClassReplacer(
                    '...',
                    $message,
                    $deprecation->getNode()
                );
            }

            return $this->rectorGuessFactory->createMethodNameReplacerGuess(
                $message,
                $deprecation->getNode()
            );
        }

        return $this->rectorGuessFactory->createRemoval($message, $deprecation->getNode());
    }

    /**
     * @param Deprecation[] $deprecations
     * @return RectorGuess[]
     */
    public function guessForDeprecations(array $deprecations): array
    {
        $guessedRectors = [];

        foreach ($deprecations as $deprecation) {
            $guessedRectors[] = $this->guessForDeprecation($deprecation);
        }

        return $guessedRectors;
    }

//    private function guess(string $message, Node $node): ?RectorGuess
//    {
//        // @todo: per node resolver...
//        if ($node instanceof Class_) {
//            return $this->rectorGuessFactory->createClassReplacer(
//                $node->namespacedName->toString(),
//                $message,
//                $node
//            );
//        }
//
//        if ($node instanceof Node\Stmt\ClassMethod) {
//            $classWithMethod = $this->classAndMethodMatcher->matchClassWithMethod($message);
//            $localMethod = $this->classAndMethodMatcher->matchLocalMethod($message);
//
//            $className = $node->getAttribute(Attribute::CLASS_NODE)->namespacedName->toString();
//            $methodName = (string) $node->name . '()';
//            $fqnMethodName = $className . '::' . $methodName;
//
//            if ($classWithMethod === '' && $localMethod === '') {
//                return $this->rectorGuessFactory->createRemoval($message, $node);
//            }
//
//            if ($localMethod) {
//                return $this->rectorGuessFactory->createRemoval(
//                    $fqnMethodName . ' => ' . $className . '::' . $localMethod . '()' . $message,
//                    $node
//                );
//            }
//
//            $namespacedClassWithMethod = $this->classAndMethodMatcher->matchNamespacedClassWithMethod($message);
//
//            /** @var string[] $useStatements */
//            $useStatements = $node->getAttribute(Attribute::USE_STATEMENTS);
//            $fqnClassWithMethod = $this->completeNamespace($useStatements, $namespacedClassWithMethod);
//
//            return $this->rectorGuessFactory->createRemoval(
//                $fqnMethodName . '=> ' . $fqnClassWithMethod,
//                $node
//            );
//        }
//
//        throw new NotImplementedException(sprintf(
//            '%s() was unable to create a Deprecation based on "%s" string and "%s" Node. Create a new method there.',
//            __METHOD__,
//            $message,
//            get_class($node)
//        ));
//    }

    /**
     * @param string[] $useStatements
     */
    private function completeNamespace(array $useStatements, string $namespacedClassWithMethod): string
    {
        [$class, $method] = explode('::', $namespacedClassWithMethod);
        foreach ($useStatements as $useStatement) {
            if (Strings::endsWith($useStatement, $class)) {
                return $useStatement . '::' . $method;
            }
        }

        return $namespacedClassWithMethod;
    }
}
