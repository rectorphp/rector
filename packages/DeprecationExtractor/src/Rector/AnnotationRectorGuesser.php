<?php declare(strict_types=1);

namespace Rector\DeprecationExtractor\Rector;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\DeprecationExtractor\RectorGuess\RectorGuess;
use Rector\DeprecationExtractor\RectorGuess\RectorGuessFactory;
use Rector\DeprecationExtractor\Regex\ClassAndMethodMatcher;
use Rector\Exception\NotImplementedException;
use Rector\Node\Attribute;

final class AnnotationRectorGuesser
{
    /**
     * @var ClassAndMethodMatcher
     */
    private $classAndMethodMatcher;

    /**
     * @var RectorGuessFactory
     */
    private $rectorGuessFactory;

    public function __construct(ClassAndMethodMatcher $classAndMethodMatcher, RectorGuessFactory $rectorGuessFactory)
    {
        $this->classAndMethodMatcher = $classAndMethodMatcher;
        $this->rectorGuessFactory = $rectorGuessFactory;
    }

    public function guess(string $message, Node $node): ?RectorGuess
    {
        if ($node instanceof Class_) {
            return $this->rectorGuessFactory->createClassReplacer(
                $node->namespacedName->toString(),
                $message,
                $node
            );
        }

        if ($node instanceof ClassMethod) {
            $classWithMethod = $this->classAndMethodMatcher->matchClassWithMethod($message);
            $localMethod = $this->classAndMethodMatcher->matchLocalMethod($message);

            $className = $node->getAttribute(Attribute::CLASS_NODE)->namespacedName->toString();
            $methodName = (string) $node->name . '()';
            $fqnMethodName = $className . '::' . $methodName;

            if ($classWithMethod === '' && $localMethod === '') {
                return $this->rectorGuessFactory->createRemoval($message, $node);
            }

            if ($localMethod) {
                return $this->rectorGuessFactory->createRemoval(
                    $fqnMethodName . ' => ' . $className . '::' . $localMethod . '()' . $message,
                    $node
                );
            }

            $namespacedClassWithMethod = $this->classAndMethodMatcher->matchNamespacedClassWithMethod($message);

            /** @var string[] $useStatements */
            $useStatements = $node->getAttribute(Attribute::USE_STATEMENTS);
            $fqnClassWithMethod = $this->completeNamespace($useStatements, $namespacedClassWithMethod);

            return $this->rectorGuessFactory->createRemoval(
                $fqnMethodName . '=> ' . $fqnClassWithMethod,
                $node
            );
        }

        throw new NotImplementedException(sprintf(
            '%s() was unable to create a Deprecation based on "%s" string and "%s" Node. Create a new method there.',
            __METHOD__,
            $message,
            get_class($node)
        ));
    }

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
