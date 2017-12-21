<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\PHPUnit;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Empty_;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
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
        'array_search' => ['assertContains', 'assertNotContains'],
        'in_array' => ['assertContains', 'assertNotContains'],
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

        $funcCallName = $this->resolveFunctionName($firstArgumentValue);
        if ($funcCallName === null) {
            return false;
        }

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
        $this->moveFunctionArgumentsUp($methodCallNode);

        return $methodCallNode;
    }

    private function renameMethod(MethodCall $methodCallNode): void
    {
        /** @var Identifier $identifierNode */
        $identifierNode = $methodCallNode->name;
        $oldMethodName = $identifierNode->toString();

        [$trueMethodName, $falseMethodName] = $this->oldToNewMethods[$this->activeFuncCallName];

        if ($oldMethodName === 'assertTrue' && $trueMethodName) {
            /** @var string $trueMethodName */
            $methodCallNode->name = new Identifier($trueMethodName);
        } elseif ($oldMethodName === 'assertFalse' && $falseMethodName) {
            /** @var string $falseMethodName */
            $methodCallNode->name = new Identifier($falseMethodName);
        }
    }

    /**
     * Before:
     * - $this->assertTrue(array_key_exists('...', ['...']), 'second argument');
     *
     * After:
     * - $this->assertArrayHasKey('...', ['...'], 'second argument');
     */
    private function moveFunctionArgumentsUp(MethodCall $methodCallNode): void
    {
        $funcCallOrEmptyNode = $methodCallNode->args[0]->value;
        if ($funcCallOrEmptyNode instanceof FuncCall) {
            $oldArguments = $methodCallNode->args;
            unset($oldArguments[0]);
            $newArguments = array_merge($funcCallOrEmptyNode->args, $oldArguments);

            $methodCallNode->args = $newArguments;
        }

        if ($funcCallOrEmptyNode instanceof Empty_) {
            $methodCallNode->args[0] = new Arg($funcCallOrEmptyNode->expr);
        }
    }

    private function resolveFunctionName(Node $node): ?string
    {
        if ($node instanceof FuncCall) {
            /** @var Name $nameNode */
            $nameNode = $node->name;

            return $nameNode->toString();
        }

        if ($node instanceof Empty_) {
            return 'empty';
        }

        return null;
    }
}
