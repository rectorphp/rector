<?php declare(strict_types=1);

namespace Rector\Rector\Architecture\RepositoryAsService;

use get_class;
use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use Rector\Builder\Class_\ClassPropertyCollector;
use Rector\Contract\Bridge\RepositoryForDoctrineEntityProviderInterface;
use Rector\Exception\Bridge\RectorProviderException;
use Rector\Exception\ShouldNotHappenException;
use Rector\Naming\PropertyNaming;
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

    /**
     * @var PropertyNaming
     */
    private $propertyNaming;

    public function __construct(
        MethodCallAnalyzer $methodCallAnalyzer,
        PropertyFetchNodeFactory $propertyFetchNodeFactory,
        RepositoryForDoctrineEntityProviderInterface $repositoryForDoctrineEntityProvider,
        ClassPropertyCollector $classPropertyCollector,
        PropertyNaming $propertyNaming
    ) {
        $this->methodCallAnalyzer = $methodCallAnalyzer;
        $this->propertyFetchNodeFactory = $propertyFetchNodeFactory;
        $this->repositoryForDoctrineEntityProvider = $repositoryForDoctrineEntityProvider;
        $this->classPropertyCollector = $classPropertyCollector;
        $this->propertyNaming = $propertyNaming;
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

        return ! Strings::endsWith($className, 'Repository');
    }

    public function refactor(Node $node): ?Node
    {
        $repositoryFqn = $this->repositoryFqn($node);

        $this->classPropertyCollector->addPropertyForClass(
            (string) $node->getAttribute(Attribute::CLASS_NAME),
            [$repositoryFqn],
            $this->propertyNaming->fqnToVariableName($repositoryFqn)
        );

        return $this->propertyFetchNodeFactory->createLocalWithPropertyName(
            $this->propertyNaming->fqnToVariableName($repositoryFqn)
        );
    }

    private function repositoryFqn(Node $node): string
    {
        $entityFqnOrAlias = $this->entityFqnOrAlias($node);

        $repositoryClassName = $this->repositoryForDoctrineEntityProvider->provideRepositoryForEntity(
            $entityFqnOrAlias
        );

        if ($repositoryClassName !== null) {
            return $repositoryClassName;
        }

        throw new RectorProviderException(sprintf(
            'A repository was not provided for "%s" entity by your "%s" class.',
            $entityFqnOrAlias,
            get_class($this->repositoryForDoctrineEntityProvider)
        ));
    }

    private function entityFqnOrAlias(Node $node): string
    {
        $repositoryArgument = $node->args[0]->value;

        if ($repositoryArgument instanceof String_) {
            return $repositoryArgument->value;
        }

        if ($repositoryArgument instanceof ClassConstFetch && $repositoryArgument->class instanceof Name) {
            return $repositoryArgument->class->getAttribute(Attribute::RESOLVED_NAME)->toString();
        }

        throw new ShouldNotHappenException('Unable to resolve repository argument');
    }
}
