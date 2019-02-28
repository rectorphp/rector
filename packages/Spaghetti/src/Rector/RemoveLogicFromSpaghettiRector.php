<?php declare(strict_types=1);

namespace Rector\Spaghetti\Rector;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\AssignOp;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Echo_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\InlineHTML;
use Rector\FileSystemRector\Rector\AbstractFileSystemRector;
use Rector\PhpParser\NodeTraverser\CallableNodeTraverser;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;
use Rector\Spaghetti\Controller\ControllerCallAndExtractFactory;
use Symplify\PackageBuilder\FileSystem\SmartFileInfo;
use Symplify\PackageBuilder\Strings\StringFormatConverter;

final class RemoveLogicFromSpaghettiRector extends AbstractFileSystemRector
{


    public function refactor(SmartFileInfo $smartFileInfo): void
    {
        $nodes = $this->parseFileInfoToNodes($smartFileInfo);

        // analyze here! - collect variables
        $i = 0;
        foreach ($nodes as $key => $node) {
            if ($node instanceof InlineHTML) {
                // @todo are we in a for/foreach?
                continue;
            }

            if ($node instanceof Echo_) {
                if (count($node->exprs) === 1) {
                    // is it already a variable? nothing to change
                    if ($node->exprs[0] instanceof Variable) {
                        continue;
                    }

                    ++$i;

                    $variableName = 'variable' . $i;
                    $node->exprs[0] = new Variable($variableName);
                }
            } else {
                // expression assign variable!?
                if (! $this->isNodeEchoedAnywhereInside($node)) {
                    // nodes to skip
                    unset($nodes[$key]);
                    continue;
                }
            }
        }

        
    }
}
