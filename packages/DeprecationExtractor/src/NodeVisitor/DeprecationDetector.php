<?php declare(strict_types=1);

namespace Rector\DeprecationExtractor\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\NodeVisitorAbstract;
use Rector\DeprecationExtractor\Deprecation\DeprecationCollector;
use Rector\DeprecationExtractor\NodeAnalyzer\TriggerErrorAnalyzer;
use Rector\NodeValueResolver\NodeValueResolver;
use Rector\ReflectionDocBlock\NodeAnalyzer\DocBlockAnalyzer;

/**
 * Inspired by https://github.com/sensiolabs-de/deprecation-detector/blob/master/src/Visitor/Deprecation/FindDeprecatedTagsVisitor.php
 */
final class DeprecationDetector extends NodeVisitorAbstract
{
    /**
     * @var DeprecationCollector
     */
    private $deprecationCollector;

    /**
     * @var DocBlockAnalyzer
     */
    private $docBlockAnalyzer;

    /**
     * @var TriggerErrorAnalyzer
     */
    private $triggerErrorAnalyzer;

    /**
     * @var NodeValueResolver
     */
    private $nodeValueResolver;

    public function __construct(
        DeprecationCollector $deprecationCollector,
        DocBlockAnalyzer $docBlockAnalyzer,
        TriggerErrorAnalyzer $triggerErrorAnalyzer,
        NodeValueResolver $nodeValueResolver
    ) {
        $this->deprecationCollector = $deprecationCollector;
        $this->docBlockAnalyzer = $docBlockAnalyzer;
        $this->triggerErrorAnalyzer = $triggerErrorAnalyzer;
        $this->nodeValueResolver = $nodeValueResolver;
    }

    public function enterNode(Node $node): void
    {
        if ($this->docBlockAnalyzer->hasAnnotation($node, 'deprecated')) {
            $this->processDocBlockDeprecation($node);

            return;
        }

        if ($this->triggerErrorAnalyzer->isUserDeprecation($node)) {
            /** @var FuncCall $node */
            $argNode = $node->args[0];

            $message = $this->nodeValueResolver->resolve($argNode);
            if ($message === null) {
                return;
            }

            $this->deprecationCollector->addDeprecation($message, $node);

            return;
        }
    }

    private function processDocBlockDeprecation(Node $node): void
    {
        $deprecation = $this->docBlockAnalyzer->getAnnotationFromNode($node, 'deprecated');
        if ($deprecation === '') {
            return;
        }

        $this->deprecationCollector->addDeprecation($deprecation, $node);
    }
}
