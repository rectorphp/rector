<?php

declare(strict_types=1);

namespace App\Rector;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Type\ObjectType;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Exception\Bridge\RectorProviderException;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Doctrine\Contract\Mapper\DoctrineEntityAndRepositoryMapperInterface;
use Rector\Naming\Naming\PropertyNaming;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class MethodCallToPropertyFetchRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var DoctrineEntityAndRepositoryMapperInterface
     */
    private $doctrineEntityAndRepositoryMapper;

    /**
     * @var PropertyNaming
     */
    private $propertyNaming;
    /** @var string[]  */
    private array $methodCallToPropertyFetchCollection;

    public function __construct(
        DoctrineEntityAndRepositoryMapperInterface $doctrineEntityAndRepositoryMapper,
        PropertyNaming $propertyNaming
    ) {
        $this->doctrineEntityAndRepositoryMapper = $doctrineEntityAndRepositoryMapper;
        $this->propertyNaming = $propertyNaming;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Turns "$this->something()" to "$this->something"', []);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [MethodCall::class];
    }

    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        foreach ($this->methodCallToPropertyFetchCollection as $methodName => $propertyName) {

            if ($node->name->name !== $methodName) {
                continue;
            }

            if($propertyName === null){
                return new Variable('this');
            }

            $this->createPropertyFetch('this', $propertyName);
        }

        return $node;
    }

    public function configure(array $configuration): void
    {
        $this->methodCallToPropertyFetchCollection = $configuration['$methodCallToPropertyFetchCollection'] ?? [];
    }
}