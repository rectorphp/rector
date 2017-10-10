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
}
