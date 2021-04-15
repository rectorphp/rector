<?php

declare(strict_types=1);

namespace Rector\Transform\Rector\Assign;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Expr\Variable;
use PHPStan\Type\ObjectType;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\NodeAnalyzer\PropertyFetchAnalyzer;
use Rector\Core\NodeManipulator\MagicPropertyFetchAnalyzer;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Tests\Transform\Rector\Assign\GetAndSetToMethodCallRector\GetAndSetToMethodCallRectorTest
 */
final class GetAndSetToMethodCallRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const TYPE_TO_METHOD_CALLS = 'type_to_method_calls';

    /**
     * @var string
     */
    private const GET = 'get';

    /**
     * @var string[][]
     */
    private $typeToMethodCalls = [];

    /**
     * @var PropertyFetchAnalyzer
     */
    private $propertyFetchAnalyzer;

    /**
     * @var MagicPropertyFetchAnalyzer
     */
    private $magicPropertyFetchAnalyzer;

    public function __construct(
        PropertyFetchAnalyzer $propertyFetchAnalyzer,
        MagicPropertyFetchAnalyzer $magicPropertyFetchAnalyzer
    ) {
        $this->propertyFetchAnalyzer = $propertyFetchAnalyzer;
        $this->magicPropertyFetchAnalyzer = $magicPropertyFetchAnalyzer;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Turns defined `__get`/`__set` to specific method calls.', [
            new ConfiguredCodeSample(
<<<'CODE_SAMPLE'
$container = new SomeContainer;
$container->someService = $someService;
CODE_SAMPLE
                ,
<<<'CODE_SAMPLE'
$container = new SomeContainer;
$container->setService("someService", $someService);
CODE_SAMPLE
                ,
                [
                    self::TYPE_TO_METHOD_CALLS => [
                        'SomeContainer' => [
                            'set' => 'addService',
                        ],
                    ],
                ]
            ),
            new ConfiguredCodeSample(
                <<<'CODE_SAMPLE'
$container = new SomeContainer;
$someService = $container->someService;
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
$container = new SomeContainer;
$someService = $container->getService("someService");
CODE_SAMPLE
                ,
                [
                    self::TYPE_TO_METHOD_CALLS => [
                        'SomeContainer' => [
                            self::GET => 'getService',
                        ],
                    ],
                ]
            ),
        ]);
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Assign::class, PropertyFetch::class];
    }

    /**
     * @param Assign|PropertyFetch $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node instanceof Assign) {
            if ($node->var instanceof PropertyFetch || $node->var instanceof StaticPropertyFetch) {
                return $this->processMagicSet($node->expr, $node->var);
            }

            return null;
        }

        return $this->processPropertyFetch($node);
    }

    public function configure(array $configuration): void
    {
        $this->typeToMethodCalls = $configuration[self::TYPE_TO_METHOD_CALLS] ?? [];
    }

    /**
     * @param PropertyFetch|StaticPropertyFetch $propertyFetch
     */
    private function processMagicSet(Expr $expr, Expr $propertyFetch): ?Node
    {
        foreach ($this->typeToMethodCalls as $type => $transformation) {
            $objectType = new ObjectType($type);
            if ($this->shouldSkipPropertyFetch($propertyFetch, $objectType)) {
                continue;
            }

            return $this->createMethodCallNodeFromAssignNode($propertyFetch, $expr, $transformation['set']);
        }

        return null;
    }

    private function processPropertyFetch(PropertyFetch $propertyFetch): ?MethodCall
    {
        foreach ($this->typeToMethodCalls as $type => $transformation) {
            $objectType = new ObjectType($type);
            if ($this->shouldSkipPropertyFetch($propertyFetch, $objectType)) {
                continue;
            }

            // setter, skip
            $parentNode = $propertyFetch->getAttribute(AttributeKey::PARENT_NODE);
            if (! $parentNode instanceof Assign) {
                return $this->createMethodCallNodeFromPropertyFetchNode($propertyFetch, $transformation[self::GET]);
            }
            if ($parentNode->var !== $propertyFetch) {
                return $this->createMethodCallNodeFromPropertyFetchNode($propertyFetch, $transformation[self::GET]);
            }
            continue;
        }

        return null;
    }

    /**
     * @param StaticPropertyFetch|PropertyFetch $propertyFetch
     */
    private function shouldSkipPropertyFetch(Expr $propertyFetch, ObjectType $objectType): bool
    {
        if ($propertyFetch instanceof StaticPropertyFetch) {
            if (! $this->isObjectType($propertyFetch->class, $objectType)) {
                return true;
            }
        }

        if ($propertyFetch instanceof PropertyFetch) {
            if (! $this->isObjectType($propertyFetch->var, $objectType)) {
                return true;
            }
        }

        if (! $this->magicPropertyFetchAnalyzer->isMagicOnType($propertyFetch, $objectType)) {
            return true;
        }

        return $this->propertyFetchAnalyzer->isPropertyToSelf($propertyFetch);
    }

    /**
     * @param PropertyFetch|StaticPropertyFetch $propertyFetch
     */
    private function createMethodCallNodeFromAssignNode(
        Expr $propertyFetch,
        Expr $expr,
        string $method
    ): MethodCall {
//        if ($propertyFetch instanceof PropertyFetch) {
//            $variableNode = $propertyFetch->var;
//        } else {
//            $variableNode = $propertyFetch->class;
//        }

        $propertyName = $this->getName($propertyFetch->name);
        return $this->nodeFactory->createMethodCall($propertyFetch, $method, [$propertyName, $expr]);
    }

    private function createMethodCallNodeFromPropertyFetchNode(
        PropertyFetch $propertyFetch,
        string $method
    ): MethodCall {
        /** @var Variable $variableNode */
        $variableNode = $propertyFetch->var;

        return $this->nodeFactory->createMethodCall($variableNode, $method, [$this->getName($propertyFetch)]);
    }
}
