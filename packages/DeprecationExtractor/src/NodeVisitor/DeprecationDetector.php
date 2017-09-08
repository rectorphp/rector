<?php declare(strict_types=1);

namespace Rector\DeprecationExtractor\NodeVisitor;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use PhpParser\PrettyPrinter\Standard;
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

    public function __construct(
        DeprecationCollector $deprecationCollector,
        DocBlockAnalyzer $docBlockAnalyzer,
        Standard $prettyPrinter
    ) {
        $this->deprecationCollector = $deprecationCollector;
        $this->docBlockAnalyzer = $docBlockAnalyzer;
        $this->prettyPrinter = $prettyPrinter;
    }

    public function enterNode(Node $node): void
    {
        if ($this->docBlockAnalyzer->hasAnnotation($node, 'deprecated')) {
            $this->processDocBlockDeprecation($node);
            return;
        }

        if ($this->hasTriggerErrorUserDeprecated($node)) {
            dump($node);
            die;

            $scope = $node->getAttribute(Attribute::SCOPE);
            $this->deprecationCollector->addDeprecation($deprecation);

            return;
        }
    }

    private function hasTriggerErrorUserDeprecated(Node $node): bool
    {
        return Strings::contains($this->prettyPrinter->prettyPrint([$node]), 'E_USER_DEPRECATED');
    }

    private function processDocBlockDeprecation(Node $node): void
    {
        $deprecation = $this->docBlockAnalyzer->getAnnotationFromNode($node, 'deprecated');
        $this->deprecationCollector->addDeprecationMessage($deprecation);
    }
}
