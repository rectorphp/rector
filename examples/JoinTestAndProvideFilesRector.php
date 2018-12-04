<?php declare(strict_types=1);

namespace Rector\Examples;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Yield_;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Expression;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockAnalyzer;
use Rector\PhpParser\Node\Maintainer\ClassMaintainer;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

/**
 * 15 minutes to write
 */
final class JoinTestAndProvideFilesRector extends AbstractRector
{

    /**
     * @var ClassMaintainer
     */
    private $classMaintainer;

    /**
     * @var DocBlockAnalyzer
     */
    private $docBlockAnalyzer;

    public function __construct(
        ClassMaintainer $classMaintainer,
        DocBlockAnalyzer $docBlockAnalyzer
    ) {
        $this->classMaintainer = $classMaintainer;
        $this->docBlockAnalyzer = $docBlockAnalyzer;
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
        if (! isset($classMethodsByName['test']) || ! isset($classMethodsByName['provideFiles'])) {
            return null;
        }

        $testMethod = $classMethodsByName['test'];

        // remove params
        $testMethod->params = [];

        // remove annotation
        $this->docBlockAnalyzer->removeTagFromNode($testMethod, 'dataProvider');

        $provideFilesMethod = $classMethodsByName['provideFiles'];

        $fileArrayItems = [];
        foreach ($provideFilesMethod->stmts as $stmt) {
            if ($stmt instanceof Expression) {
                if ($stmt->expr instanceof Yield_) {
                    $fileArrayItems[] = $stmt->expr->value;
                }
            }
        }

        foreach ($testMethod->stmts as $stmt) {
            if ($stmt instanceof Expression) {
                if ($stmt->expr instanceof MethodCall) {
                    $doTestFilesMethodCall = $stmt->expr;

                    $doTestFilesMethodCall->name = new Identifier('doTestFiles');
                    $doTestFilesMethodCall->args = [new Arg(new Array_($fileArrayItems))];
                    break;
                }
            }
        }

        $this->removeNode($provideFilesMethod);

        return $node;
    }
}
