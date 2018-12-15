<?php declare(strict_types=1);

namespace Rector\Examples;

use Nette\Utils\FileSystem;
use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Expression;
use Rector\PhpParser\Node\Maintainer\ClassMaintainer;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

/**
 * @see https://github.com/rectorphp/rector/pull/807
 */
final class JoinWrongAndCorrectTestsRector extends AbstractRector
{
    /**
     * @var ClassMaintainer
     */
    private $classMaintainer;

    public function __construct(ClassMaintainer $classMaintainer)
    {
        $this->classMaintainer = $classMaintainer;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Simplify tests', [new CodeSample('', '')]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }

    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isType($node, AbstractRectorTestCase::class)) {
            return null;
        }

        if ($node->isAbstract()) {
            return null;
        }

        $classMethodsByName = $this->classMaintainer->getMethodsByName($node);
        if (! isset($classMethodsByName['test'])) {
            return null;
        }

        $testClassMethod = $classMethodsByName['test'];

        foreach ($testClassMethod->stmts as $stmt) {
            if (! $stmt instanceof Expression) {
                continue;
            }

            if (! $stmt->expr instanceof MethodCall) {
                continue;
            }

            if (! $this->isName($stmt->expr, 'doTestFiles')) {
                continue;
            }

            $filesArrayNode = $stmt->expr->args[0]->value;

            if ($filesArrayNode instanceof Array_) {
                foreach ($filesArrayNode->items as $i => $item) {
                    if (! $item->value instanceof Array_) {
                        continue;
                    }

                    if (count($item->value->items) !== 2) {
                        continue;
                    }

                    // from array to string
                    $filesArrayNode->items[$i] = $item->value->items[0];

                    $wrongFilePath = $this->getValue($item->value->items[0]->value);
                    $correctFilePath = $this->getValue($item->value->items[1]->value);

                    // remove node

                    $wrongFileContent = FileSystem::read($wrongFilePath);
                    $correctFileContent = FileSystem::read($correctFilePath);

                    // save integration content
                    $integrationFileContent = $wrongFileContent . PHP_EOL . '?>' . PHP_EOL . '-----' . PHP_EOL . $correctFileContent . PHP_EOL . '?>' . PHP_EOL;

                    // remove strict types, can be only once in the file
                    $integrationFileContent = Strings::replace(
                        $integrationFileContent,
                        '#((\s+)?declare(\s+)?\(strict_types=1\);)#',
                        ''
                    );

                    FileSystem::write($wrongFilePath, $integrationFileContent);
                    // remove old file
                    unlink($correctFilePath);
                }
            }
        }

        return $node;
    }
}
