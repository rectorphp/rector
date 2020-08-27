<?php

declare(strict_types=1);

namespace Rector\PHPUnit\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use Rector\Core\PhpParser\Node\Manipulator\IdentifierManipulator;
use Rector\Core\Rector\AbstractPHPUnitRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @source https://github.com/sebastianbergmann/phpunit/pull/4365
 *
 * @see \Rector\PHPUnit\Tests\Rector\MethodCall\AssertResourceToClosedResourceRector\AssertResourceToClosedResourceRectorTest
 */
final class AssertResourceToClosedResourceRector extends AbstractPHPUnitRector
{
    /**
     * @var array<string, string>
     */
    private const RENAME_METHODS_MAP = [
        'assertIsNotResource' => 'assertIsClosedResource',
    ];

    /**
     * @var IdentifierManipulator
     */
    private $identifierManipulator;

    public function __construct(IdentifierManipulator $identifierManipulator)
    {
        $this->identifierManipulator = $identifierManipulator;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Turns `assertIsNotResource()` into stricter `assertIsClosedResource()` for resource values in PHPUnit TestCase',
            [
                new CodeSample(
                    '$this->assertIsNotResource($aResource, "message");',
                    '$this->assertIsClosedResource($aResource, "message");'
                ),
            ]
        );
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
        if (! $this->isInTestClass($node)) {
            return null;
        }

        $methodNames = array_keys(self::RENAME_METHODS_MAP);
        if (! $this->isNames($node->name, $methodNames)) {
            return null;
        }

        if (! isset($node->args[0])) {
            return null;
        }

        $this->identifierManipulator->renameNodeWithMap($node, self::RENAME_METHODS_MAP);

        return $node;
    }
}
