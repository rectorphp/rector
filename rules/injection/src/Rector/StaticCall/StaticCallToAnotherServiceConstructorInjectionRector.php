<?php

declare(strict_types=1);

namespace Rector\Injection\Rector\StaticCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt\Class_;
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
final class StaticCallToAnotherServiceConstructorInjectionRector extends AbstractRector
{
    /**
     * @var StaticCallToMethodCall[]
     */
    private $staticCallsToMethodCalls = [];

    /**
     * @var PropertyNaming
     */
    private $propertyNaming;

    /**
     * @param StaticCallToMethodCall[] $staticCallsToMethodCalls
     */
    public function __construct(array $staticCallsToMethodCalls, PropertyNaming $propertyNaming)
    {
        $this->staticCallsToMethodCalls = $staticCallsToMethodCalls;
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
                '$staticCallsToMethodCalls' => [
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

        foreach ($this->staticCallsToMethodCalls as $staticCallsToMethodCall) {
            if (! $staticCallsToMethodCall->matchStaticCall($node)) {
                continue;
            }

            $serviceObjectType = new FullyQualifiedObjectType($staticCallsToMethodCall->getClassType());

            $propertyName = $this->propertyNaming->fqnToVariableName($serviceObjectType);
            $this->addPropertyToClass($classLike, $serviceObjectType, $propertyName);

            $propertyFetchNode = $this->createPropertyFetch('this', $propertyName);

            return new MethodCall($propertyFetchNode, $staticCallsToMethodCall->getMethodName(), $node->args);
        }

        return $node;
    }
}
