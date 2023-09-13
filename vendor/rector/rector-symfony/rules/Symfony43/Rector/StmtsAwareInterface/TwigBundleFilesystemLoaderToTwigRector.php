<?php

declare (strict_types=1);
namespace Rector\Symfony\Symfony43\Rector\StmtsAwareInterface;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Expression;
use PHPStan\Type\ObjectType;
use Rector\Core\Contract\PhpParser\Node\StmtsAwareInterface;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Symfony\Tests\Symfony43\Rector\StmtsAwareInterface\TwigBundleFilesystemLoaderToTwigRector\TwigBundleFilesystemLoaderToTwigRectorTest
 */
final class TwigBundleFilesystemLoaderToTwigRector extends AbstractRector
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change TwigBundle FilesystemLoader to native one', [new CodeSample(<<<'CODE_SAMPLE'
use Symfony\Bundle\TwigBundle\Loader\FilesystemLoader;
use Symfony\Bundle\FrameworkBundle\Templating\Loader\TemplateLocator;
use Symfony\Bundle\FrameworkBundle\Templating\TemplateNameParser;

$filesystemLoader = new FilesystemLoader(new TemplateLocator(), new TemplateParser());
$filesystemLoader->addPath(__DIR__ . '/some-directory');
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Twig\Loader\FilesystemLoader;

$fileSystemLoader = new FilesystemLoader([__DIR__ . '/some-directory']);
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [StmtsAwareInterface::class];
    }
    /**
     * @param StmtsAwareInterface $node
     */
    public function refactor(Node $node) : ?Node
    {
        $filesystemLoaderNew = $this->resolveFileSystemLoaderNew($node);
        if (!$filesystemLoaderNew instanceof New_) {
            return null;
        }
        $collectedPathExprs = [];
        foreach ((array) $node->stmts as $key => $stmt) {
            if (!$stmt instanceof Expression) {
                continue;
            }
            if (!$stmt->expr instanceof MethodCall) {
                continue;
            }
            $methodCall = $stmt->expr;
            if (!$this->isName($methodCall->name, 'addPath')) {
                continue;
            }
            $collectedPathExprs[] = $methodCall->getArgs()[0];
            unset($node->stmts[$key]);
        }
        $filesystemLoaderNew->class = new FullyQualified('Twig\\Loader\\FilesystemLoader');
        $array = $this->nodeFactory->createArray($collectedPathExprs);
        $filesystemLoaderNew->args = [new Arg($array)];
        return $node;
    }
    private function resolveFileSystemLoaderNew(StmtsAwareInterface $stmtsAware) : ?New_
    {
        foreach ((array) $stmtsAware->stmts as $stmt) {
            if (!$stmt instanceof Expression) {
                continue;
            }
            if (!$stmt->expr instanceof Assign) {
                continue;
            }
            $assign = $stmt->expr;
            if (!$assign->expr instanceof New_) {
                continue;
            }
            $new = $assign->expr;
            if (!$this->isObjectType($new, new ObjectType('Symfony\\Bundle\\TwigBundle\\Loader\\FilesystemLoader'))) {
                continue;
            }
            return $new;
        }
        return null;
    }
}
