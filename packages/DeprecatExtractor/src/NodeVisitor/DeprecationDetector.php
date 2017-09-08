<?php declare(strict_types=1);

/**
 * @todo rename to deprecation extractor
 */

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
     * @var string[]
     */
    private const DEPRECATEABLE_NODES = [ClassLike::class, ClassMethod::class, Function_::class];

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
        if (! $this->isDeprecateableNode($node)) {
            return;
        }

        if (! $this->hasDeprecation($node)) {
            return;
        }

        $scope = $node->getAttribute(Attribute::SCOPE);

        /** @var FuncCall $node */
        $deprecation = $this->deprecationFactory->createFromNode($node->args[0]->value, $scope);

        $this->deprecationCollector->addDeprecation($deprecation);
    }

    private function isDeprecateableNode(Node $node): bool
    {
        foreach (self::DEPRECATEABLE_NODES as $deprecateableNode) {
            if (is_a($node, $deprecateableNode, true)) {
                return true;
            }
        }

        return false;
    }

    private function hasDeprecation(Node $node): bool
    {
        if ($this->docBlockAnalyzer->hasAnnotation($node, 'deprecated')) {
            return true;
        }

        if ($this->hasTriggerErrorUserDeprecated($node)) {
            return true;
        }

        return false;
    }

    private function hasTriggerErrorUserDeprecated(Node $node): bool
    {
        return Strings::contains(
            $this->prettyPrinter->prettyPrint([$node]),
            'E_USER_DEPRECATED'
        );
    }
}
