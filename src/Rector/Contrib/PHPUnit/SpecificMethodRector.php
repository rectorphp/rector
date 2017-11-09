<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\PHPUnit;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use Rector\NodeAnalyzer\MethodCallAnalyzer;
use Rector\Rector\AbstractRector;

/**
 * Before:
 * - $this->assertTrue(is_readable($readmeFile), 'messag');
 *
 * After:
 * - $this->assertIsReadable($readmeFile, 'message'));
 */
final class SpecificMethodRector extends AbstractRector
{
    /**
     * @var string[][]|false[][]
     */
    private $oldToNewMethods = [
        'is_readable' => ['assertIsReadable', 'assertNotIsReadable'],
        'array_key_exists' => ['assertArrayHasKey', 'assertArrayNotHasKey'],
        'empty' => ['assertEmpty', 'assertNotEmpty'],
        'file_exists' => ['assertFileExists', 'assertFileNotExists'],
        'is_dir' => ['assertDirectoryExists', 'assertDirectoryNotExists'],
        'is_infinite' => ['assertInfinite', 'assertFinite'],
        'is_null' => ['assertNull', 'assertNotNull'],
        'is_writable' => ['assertIsWritable', 'assertNotIsWritable'],
        'is_nan' => ['assertNan', false],
    ];

    /**
     * @var MethodCallAnalyzer
     */
    private $methodCallAnalyzer;

    /**
     * @var string|null
     */
    private $activeFuncCallName;

    public function __construct(MethodCallAnalyzer $methodCallAnalyzer)
    {
        $this->methodCallAnalyzer = $methodCallAnalyzer;
    }

    public function isCandidate(Node $node): bool
    {
        $this->activeFuncCallName = null;

        if (! $this->methodCallAnalyzer->isTypesAndMethods(
            $node,
            ['PHPUnit\Framework\TestCase', 'PHPUnit_Framework_TestCase'],
            ['assertTrue', 'assertFalse']
        )) {
            return false;
        }

        /** @var MethodCall $node */
        if (! isset($node->args[0])) {
            return false;
        }

        $firstArgumentValue = $node->args[0]->value;
        if (! $firstArgumentValue instanceof FuncCall) {
            return false;
        }

        $funcCallName = $firstArgumentValue->name->toString();
        if (! isset($this->oldToNewMethods[$funcCallName])) {
            return false;
        }

        $this->activeFuncCallName = $funcCallName;

        return true;
    }

    /**
     * @param MethodCall $methodCallNode
     */
    public function refactor(Node $methodCallNode): ?Node
    {
        $this->renameMethod($methodCallNode);
        $this->moveArgumentUp($methodCallNode);

        return $methodCallNode;
    }

    private function renameMethod(MethodCall $methodCallNode): void
    {
        $oldMethodName = $methodCallNode->name->toString();

        [$trueMethodName, $falseMethodName] = $this->oldToNewMethods[$this->activeFuncCallName];

        if ($oldMethodName === 'assertTrue' && $trueMethodName) {
            $methodCallNode->name = $trueMethodName;
        } elseif ($oldMethodName === 'assertFalse' && $falseMethodName) {
            $methodCallNode->name = $falseMethodName;
        }
    }

    private function moveArgumentUp(MethodCall $methodCallNode): void
    {
        /** @var FuncCall $funcCall */
        $funcCall = $methodCallNode->args[0]->value;
        $methodCallNode->args[0] = $funcCall->args[0];
    }
}
