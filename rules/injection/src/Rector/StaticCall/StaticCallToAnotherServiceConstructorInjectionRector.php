<?php

declare(strict_types=1);

namespace Rector\Injection\Rector\StaticCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\ConfiguredCodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Injection\ValueObject\StaticCallToMethodCall;
use Rector\Naming\Naming\PropertyNaming;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PHPStan\Type\FullyQualifiedObjectType;

/**
 * @see \Rector\Injection\Tests\Rector\StaticCall\StaticCallToAnotherServiceConstructorInjectionRector\StaticCallToAnotherServiceConstructorInjectionRectorTest
 */
final class StaticCallToAnotherServiceConstructorInjectionRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const STATIC_CALLS_TO_METHOD_CALLS = '$staticCallsToMethodCalls';

    /**
     * @var StaticCallToMethodCall[]
     */
    private $staticCallsToMethodCalls = [];

    /**
     * @var PropertyNaming
     */
    private $propertyNaming;

    public function __construct(PropertyNaming $propertyNaming)
    {
        $this->propertyNaming = $propertyNaming;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Change static call to service method via constructor injection', [
            new ConfiguredCodeSample(
                <<<'PHP'
use Nette\Utils\FileSystem;

class SomeClass
{
    public function run()
    {
        return FileSystem::write('file', 'content');
    }
}
PHP
,
                <<<'PHP'
use Symplify\SmartFileSystem\SmartFileSystem;

class SomeClass
{
    /**
     * @var SmartFileSystem
     */
    private $smartFileSystem;
    public function __construct(SmartFileSystem $smartFileSystem)
    {
        $this->smartFileSystem = $smartFileSystem;
    }
    public function run()
    {
        return $this->smartFileSystem->dumpFile('file', 'content');
    }
}
PHP
            , [
                self::STATIC_CALLS_TO_METHOD_CALLS => [
                    new StaticCallToMethodCall(
                        'Nette\Utils\FileSystem',
                        'write',
                        'Symplify\SmartFileSystem\SmartFileSystem',
                        'dumpFile'
                    ),
                ],
            ]),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [StaticCall::class];
    }

    /**
     * @param StaticCall $node
     */
    public function refactor(Node $node): ?Node
    {
        $classLike = $node->getAttribute(AttributeKey::CLASS_NODE);
        if (! $classLike instanceof Class_) {
            return null;
        }

        $classMethod = $node->getAttribute(AttributeKey::METHOD_NODE);
        if (! $classMethod instanceof ClassMethod) {
            return null;
        }

        foreach ($this->staticCallsToMethodCalls as $staticCallToMethodCall) {
            if (! $staticCallToMethodCall->matchStaticCall($node)) {
                continue;
            }

            if ($classMethod->isStatic()) {
                return $this->refactorToInstanceCall($node, $staticCallToMethodCall);
            }

            $serviceObjectType = new FullyQualifiedObjectType($staticCallToMethodCall->getClassType());

            $propertyName = $this->propertyNaming->fqnToVariableName($serviceObjectType);
            $this->addPropertyToClass($classLike, $serviceObjectType, $propertyName);

            $propertyFetchNode = $this->createPropertyFetch('this', $propertyName);

            return new MethodCall($propertyFetchNode, $staticCallToMethodCall->getMethodName(), $node->args);
        }

        return $node;
    }

    public function configure(array $configuration): void
    {
        $this->staticCallsToMethodCalls = $configuration[self::STATIC_CALLS_TO_METHOD_CALLS] ?? [];
    }

    private function refactorToInstanceCall(
        StaticCall $staticCall,
        StaticCallToMethodCall $staticCallToMethodCall
    ): MethodCall {
        $new = new New_(new FullyQualified($staticCallToMethodCall->getClassType()));
        return new MethodCall($new, $staticCallToMethodCall->getMethodName(), $staticCall->args);
    }
}
