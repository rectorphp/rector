<?php declare(strict_types=1);

namespace Rector\DeprecationExtractor\NodeVisitor;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use Rector\DeprecationExtractor\Deprecation\DeprecationCollector;
use Rector\DeprecationExtractor\NodeAnalyzer\TriggerErrorAnalyzer;
use Rector\Node\Attribute;
use Rector\NodeAnalyzer\DocBlockAnalyzer;

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

    public function __construct(
        DeprecationCollector $deprecationCollector,
        DocBlockAnalyzer $docBlockAnalyzer,
        TriggerErrorAnalyzer $triggerErrorAnalyzer
    ) {
        $this->deprecationCollector = $deprecationCollector;
        $this->docBlockAnalyzer = $docBlockAnalyzer;
        $this->triggerErrorAnalyzer = $triggerErrorAnalyzer;
    }

    public function enterNode(Node $node): void
    {
        if ($this->docBlockAnalyzer->hasAnnotation($node, 'deprecated')) {
            $this->processDocBlockDeprecation($node);

            return;
        }

        // @todo detect the elments it's realted to
        if ($this->triggerErrorAnalyzer->isUserDeprecation($node)) {
            dump($node->getAttribute(Attribute::SCOPE));
            die;

            $scope = $node->getAttribute(Attribute::SCOPE);
            $this->deprecationCollector->addDeprecation($deprecation);

            return;
        }
    }

    private function processDocBlockDeprecation(Node $node): void
    {
        $deprecation = $this->docBlockAnalyzer->getAnnotationFromNode($node, 'deprecated');
        $this->deprecationCollector->addDeprecationMessage($deprecation);
    }
}
