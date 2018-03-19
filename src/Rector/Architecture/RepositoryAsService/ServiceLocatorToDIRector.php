<?php declare(strict_types=1);

namespace Rector\Rector\Architecture\RepositoryAsService;

use get_class;
use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\String_;
use Rector\Builder\Class_\ClassPropertyCollector;
use Rector\Contract\Bridge\RepositoryForDoctrineEntityProviderInterface;
use Rector\Exception\Bridge\RectorProviderException;
use Rector\Exception\ShouldNotHappenException;
use Rector\Node\Attribute;
use Rector\Node\PropertyFetchNodeFactory;
use Rector\NodeAnalyzer\MethodCallAnalyzer;
use Rector\Rector\AbstractRector;

final class ServiceLocatorToDIRector extends AbstractRector
{
    /**
     * @var MethodCallAnalyzer
     */
    private $methodCallAnalyzer;

    /**
     * @var PropertyFetchNodeFactory
     */
    private $propertyFetchNodeFactory;

    /**
     * @var RepositoryForDoctrineEntityProviderInterface
     */
    private $repositoryForDoctrineEntityProvider;

    /**
     * @var ClassPropertyCollector
     */
    private $classPropertyCollector;

    public function __construct(
        MethodCallAnalyzer $methodCallAnalyzer,
        PropertyFetchNodeFactory $propertyFetchNodeFactory,
        RepositoryForDoctrineEntityProviderInterface $repositoryForDoctrineEntityProvider,
        ClassPropertyCollector $classPropertyCollector
    ) {
        $this->methodCallAnalyzer = $methodCallAnalyzer;
        $this->propertyFetchNodeFactory = $propertyFetchNodeFactory;
        $this->repositoryForDoctrineEntityProvider = $repositoryForDoctrineEntityProvider;
        $this->classPropertyCollector = $classPropertyCollector;
    }

    public function isCandidate(Node $node): bool
    {
        if (! $this->methodCallAnalyzer->isMethod($node, 'getRepository')) {
            return false;
        }

        $className = $node->getAttribute(Attribute::CLASS_NAME);

        if ($className === null) {
            return false;
        }

        return ! Strings::endsWith($node->getAttribute(Attribute::CLASS_NAME), 'Repository');
    }

    public function refactor(Node $node): ?Node
    {
        $this->classPropertyCollector->addPropertyForClass(
            (string) $node->getAttribute(Attribute::CLASS_NAME),
            [$this->repositoryFqn($node)],
            $this->repositoryVariableName($node)
        );

        return $this->propertyFetchNodeFactory->createLocalWithPropertyName(
            $this->repositoryVariableName($node)
        );
    }

    private function repositoryVariableName(Node $node): string
    {
        return lcfirst($this->repositoryShortName($node));
    }

    private function repositoryShortName(Node $node): string
    {
        $nameSpaceParts = explode('\\', $this->repositoryFqn($node));
        return end($nameSpaceParts);
    }

    private function repositoryFqn(Node $node): string
    {
        $entityFqnOrAlias = $this->entityFqnOrAlias($node);

        $repositoryClassName = $this->repositoryForDoctrineEntityProvider->provideRepositoryForEntity(
            $entityFqnOrAlias
        );

        if ($repositoryClassName === null) {
            throw new RectorProviderException(sprintf(
                'A repository was not provided for "%s" entity by your "%s" class.',
                $entityFqnOrAlias,
                get_class($this->repositoryForDoctrineEntityProvider)
            ));
        }

        return $repositoryClassName;
    }

    /**
     * @throws ShouldNotHappenException
     */
    private function entityFqnOrAlias(Node $node): string
    {
        $repositoryArgument = $node->args[0]->value;

        if ($repositoryArgument instanceof String_) {
            return $repositoryArgument->value;
        }

        if ($repositoryArgument->class instanceof Name) {
            return $repositoryArgument->class->getAttribute(Attribute::TYPES)[0];
        }

        if ($repositoryArgument->class instanceof FullyQualified) {
            return $repositoryArgument->class->toString();
        }

        throw new ShouldNotHappenException('Unable to resolve repository argument');
    }
}
