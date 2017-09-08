<?php declare(strict_types=1);

namespace Rector\DeprecationExtractor\NodeVisitor;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use PhpParser\PrettyPrinter\Standard;
use Rector\DeprecationExtractor\NodeAnalyzer\TriggerErrorAnalyzer;
use Rector\Node\Attribute;
use Rector\NodeAnalyzer\DocBlockAnalyzer;
use Rector\DeprecationExtractor\Deprecation\DeprecationCollector;

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
     * @var Standard
     */
    private $prettyPrinter;
    /**
     * @var TriggerErrorAnalyzer
     */
    private $triggerErrorAnalyzer;

    public function __construct(
        DeprecationCollector $deprecationCollector,
        DocBlockAnalyzer $docBlockAnalyzer,
        Standard $prettyPrinter,
        TriggerErrorAnalyzer $triggerErrorAnalyzer
    ) {
        $this->deprecationCollector = $deprecationCollector;
        $this->docBlockAnalyzer = $docBlockAnalyzer;
        $this->prettyPrinter = $prettyPrinter;
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
//            dump($node);
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
