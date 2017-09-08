<?php declare(strict_types=1);

namespace Rector\DeprecationExtractor\NodeVisitor;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\NodeVisitorAbstract;
use PhpParser\PrettyPrinter\Standard;
use Rector\Node\Attribute;
use Rector\NodeAnalyzer\DocBlockAnalyzer;
use Rector\DeprecationExtractor\Deprecation\DeprecationCollector;
use Rector\DeprecationExtractor\Deprecation\DeprecationFactory;

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
     * @var DeprecationFactory
     */
    private $deprecationFactory;

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
        DeprecationFactory $triggerMessageResolver,
        DocBlockAnalyzer $docBlockAnalyzer,
        Standard $prettyPrinter
    ) {
        $this->deprecationCollector = $deprecationCollector;
        $this->deprecationFactory = $triggerMessageResolver;
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
