<?php declare(strict_types=1);

namespace Rector\DeprecationExtractor\Transformer;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\DeprecationExtractor\Contract\Deprecation\DeprecationInterface;
use Rector\DeprecationExtractor\Deprecation\ClassDeprecation;
use Rector\DeprecationExtractor\Deprecation\ClassMethodDeprecation;
use Rector\DeprecationExtractor\Deprecation\RemovedClassMethodDeprecation;
use Rector\DeprecationExtractor\RegExp\ClassAndMethodMatcher;
use Rector\Exception\NotImplementedException;
use Rector\Node\Attribute;

final class MessageToDeprecationTransformer
{
    /**
     * @var ClassAndMethodMatcher
     */
    private $classAndMethodMatcher;

    public function __construct(ClassAndMethodMatcher $classAndMethodMatcher)
    {
        $this->classAndMethodMatcher = $classAndMethodMatcher;
    }

    public function transform(string $message, Node $node): DeprecationInterface
    {
        if ($node instanceof Class_) {
            return new ClassDeprecation(
                $node->namespacedName->toString(),
                $this->classAndMethodMatcher->matchClassWithMethodInstead($message)
            );
        }

        if ($node instanceof ClassMethod) {
            $classWithMethod = $this->classAndMethodMatcher->matchClassWithMethod($message);
            $localMethod = $this->classAndMethodMatcher->matchLocalMethod($message);


            $className = $node->getAttribute(Attribute::CLASS_NODE)->namespacedName->toString();
            $methodName = (string) $node->name . '()';
            $fqnMethodName = $className . '::' . $methodName;

            if ($classWithMethod === '' && $localMethod === '') {
                return new RemovedClassMethodDeprecation($className, $methodName);
            }

            if ($localMethod) {
                return new ClassMethodDeprecation($fqnMethodName, $className . '::' . $localMethod . '()');
            }

            $namespacedClassWithMethod = $this->classAndMethodMatcher->matchNamespacedClassWithMethod($message);

            /** @var string[] $useStatements */
            $useStatements = $node->getAttribute(Attribute::USE_STATEMENTS);
            $fqnClassWithMethod = $this->completeNamespace($useStatements, $namespacedClassWithMethod);

            return new ClassMethodDeprecation($fqnMethodName, $fqnClassWithMethod);
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
                return $useStatement . '::'. $method;
            }
        }

        return $namespacedClassWithMethod;
    }
}
