<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\PHPUnit;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Rector\AbstractPHPUnitRector;
use Rector\ReflectionDocBlock\NodeAnalyzer\DocBlockAnalyzer;

/**
 * Before:
 * - @scenario
 *
 * After:
 * - @test
 */
final class ScenarioToTestAnnotationRector extends AbstractPHPUnitRector
{
    /**
     * @var DocBlockAnalyzer
     */
    private $docBlockAnalyzer;

    public function __construct(DocBlockAnalyzer $docBlockAnalyzer)
    {
        $this->docBlockAnalyzer = $docBlockAnalyzer;
    }

    public function isCandidate(Node $node): bool
    {
        if (! $this->isInTestClass($node)) {
            return false;
        }

        if (! $node instanceof ClassMethod) {
            return false;
        }

        return $this->docBlockAnalyzer->hasAnnotation($node, 'scenario');
    }

    /**
     * @param ClassMethod $classMethodNode
     */
    public function refactor(Node $classMethodNode): ?Node
    {
        $this->docBlockAnalyzer->replaceAnnotationInNode($classMethodNode, 'scenario', 'test');

        return $classMethodNode;
    }
}
