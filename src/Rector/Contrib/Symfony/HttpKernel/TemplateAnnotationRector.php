<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\Symfony\HttpKernel;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Builder\Class_\ClassPropertyCollector;
use Rector\Contract\Bridge\ServiceTypeForNameProviderInterface;
use Rector\Naming\PropertyNaming;
use Rector\Node\Attribute;
use Rector\Node\PropertyFetchNodeFactory;
use Rector\NodeAnalyzer\Contrib\Symfony\ContainerCallAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\ReflectionDocBlock\NodeAnalyzer\DocBlockAnalyzer;

/**
 * Converts all:
 * - @template()
 * - public function indexAction() { }
 *
 * into:
 * - public function indexAction() {
 * -     $this->render('index.html.twig');
 * - }
 */
final class TemplateAnnotationRector extends AbstractRector
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
        if (! $node instanceof ClassMethod) {
            return false;
        }

        if (! $this->docBlockAnalyzer->hasAnnotation($node, 'template')) {
            return false;
        }

        return true;
    }

    /**
     * @param ClassMethod $classMethodNode
     */
    public function refactor(Node $classMethodNode): ?Node
    {
        $this->docBlockAnalyzer->removeAnnotationFromNode($classMethodNode, 'template');

        $methodName = $classMethodNode->name->toString();
        $templateName = $this->resolveTemplateNameFromActionMethodName($methodName);

        dump($templateName);
        die;

        return $classMethodNode;
//        dump($classMethodNode->stmts);
//        dump($classMethodNode->stmts);
//        die;

        // 1.remove annotation
        // 2. derive template name
        // 3. add $this->render method call with template
        // 4. to bottom of method - probably $methodCall->stmts[]
    }

    private function resolveTemplateNameFromActionMethodName(string $methodName): string
    {
        // ...?
    }
}
