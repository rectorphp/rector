<?php

declare (strict_types=1);
namespace Rector\Symfony\Symfony51\Rector\StaticCall;

use PhpParser\Node;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use Rector\Rector\AbstractRector;
use Rector\RuleDoc\CodeSample\CodeSample;
use Rector\RuleDoc\RuleDefinition;
use Rector\VersionBonding\Contract\ComposerPackageConstraintInterface;
use Rector\VersionBonding\ValueObject\ComposerPackageConstraint;
/**
 * @see \Rector\Symfony\Tests\Symfony51\Rector\StaticCall\ResponseCreateToNewRector\ResponseCreateToNewRectorTest
 */
final class ResponseCreateToNewRector extends AbstractRector implements ComposerPackageConstraintInterface
{
    /**
     * @var string[]
     */
    private const RESPONSE_CLASSES = ['Symfony\Component\HttpFoundation\Response', 'Symfony\Component\HttpFoundation\JsonResponse', 'Symfony\Component\HttpFoundation\RedirectResponse', 'Symfony\Component\HttpFoundation\StreamedResponse'];
    public function provideComposerPackageConstraint(): ComposerPackageConstraint
    {
        return new ComposerPackageConstraint('symfony/http-foundation', '>=5.1');
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change deprecated Response::create() and its children to use __construct() instead', [new CodeSample(<<<'CODE_SAMPLE'
use Symfony\Component\HttpFoundation\JsonResponse;

final class SomeClass
{
    public function run()
    {
        return JsonResponse::create(['key' => 'value'], 200);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Component\HttpFoundation\JsonResponse;

final class SomeClass
{
    public function run()
    {
        return new JsonResponse(['key' => 'value'], 200);
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [StaticCall::class];
    }
    /**
     * @param StaticCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (!$node->class instanceof Name) {
            return null;
        }
        if (!$this->isName($node->name, 'create')) {
            return null;
        }
        if (!$this->isNames($node->class, self::RESPONSE_CLASSES)) {
            return null;
        }
        if ($node->isFirstClassCallable()) {
            return null;
        }
        return new New_(new FullyQualified($this->getName($node->class)), $node->args);
    }
}
