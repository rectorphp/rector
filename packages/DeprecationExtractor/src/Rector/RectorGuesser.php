<?php declare(strict_types=1);

namespace Rector\DeprecationExtractor\Rector;

use Nette\Utils\Strings;
use PhpParser\Builder\Class_;
use PhpParser\Node;
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
     * @var NodeValueResolver
     */
    private $nodeValueResolver;

    /**
     * @var RectorGuessFactory
     */
    private $rectorGuessFactory;

    /**
     * @var string[]
     */
    private $yamlDeprecationMessages = [
        'Autowiring-types are deprecated since',
        'The "=" suffix that used to disable strict references',
        'The XmlFileLoader will raise an exception in Symfony 4.0, instead of silently ignoring unsupported',
        'The "strict" attribute used when referencing the "" service is deprecated since version 3.3 and will be removed in 4.0.',
        'Service names that start with an underscore are deprecated since Symfony 3.3 and will be reserved in 4.0',
        'configuration key',
    ];

    /**
     * @var string[]
     */
    private $serviceDeprecationMessages = [
        'It should either be deprecated or its implementation upgraded.',
        'It should either be deprecated or its factory upgraded.',
        'Service identifiers will be made case sensitive',
        'Generating a dumped container without populating the method map is deprecated',
        'Dumping an uncompiled ContainerBuilder is deprecated',
        'service is private, ',
        'service is already initialized, ',
        'Relying on its factory\'s return-type to define the class of service',
    ];

    public function __construct(
        NodeValueResolver $nodeValueResolver,
        ClassPrepender $classPrepender,
        RectorGuessFactory $rectorGuessFactory
    ) {
        $this->nodeValueResolver = $nodeValueResolver;
        $this->classPrepender = $classPrepender;
        $this->rectorGuessFactory = $rectorGuessFactory;
    }

    public function guessFromMessageAndNode(string $message, Node $node): ?RectorGuess
    {
        // @todo: group to early filter method
        foreach ($this->yamlDeprecationMessages as $yamlDeprecationMessage) {
            if (Strings::contains($message, $yamlDeprecationMessage)) {
                return $this->rectorGuessFactory->createYamlConfiguration($message, $node);
            }
        }

        foreach ($this->serviceDeprecationMessages as $serviceDeprecationMessage) {
            if (Strings::contains($message, $serviceDeprecationMessage)) {
                return $this->rectorGuessFactory->createService($message, $node);
            }
        }


        $message = $this->classPrepender->completeClassToLocalMethods(
            $message,
            (string) $node->getAttribute(Attribute::CLASS_NAME)
        );

        if ($message === '') {
            throw new NotImplementedException(sprintf(
                'Not implemented yet. Go to "%s()" and add check for "%s" node.',
                __METHOD__,
                get_class($node)
            ));
        }


        if (Strings::contains($message, 'It will be made mandatory in') || Strings::contains($message, 'Not defining it is deprecated since')) {
            return $this->rectorGuessFactory->createNewArgument($message, $node);
        }

        $result = Strings::split($message, '#use |Use#');

        if (count($result) === 2) {
            if (Strings::contains($message, 'class is deprecated')) {
                return $this->rectorGuessFactory->createClassReplacer(
                    '...',
                    $message,
                    $node
                );
            }

            return $this->rectorGuessFactory->createMethodNameReplacerGuess(
                $message,
                $node
            );
        }

        return $this->rectorGuessFactory->createRemoval($message, $node);
    }

    public function guess(string $message, Node $node): ?RectorGuess
    {
        // @todo: per node resolver...
        if ($node instanceof Class_) {
            return $this->rectorGuessFactory->createClassReplacer(
                $node->namespacedName->toString(),
                $message,
                $node
            );
        }

        if ($node instanceof Node\Stmt\ClassMethod) {
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
