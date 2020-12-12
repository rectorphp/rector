<?php

namespace Rector\EarlyReturn\Tests\Rector\If_\ChangeNestedIfsToEarlyReturnRector\Fixture;

use PhpParser\Node\Stmt\If_;

class RectorSample
{
    public function run(If_ $node)
    {
        // $a < $b
        if ($node->cond instanceof Smaller) {
            // $a < $b ? -1
            if ($this->getValue($node->if) === -1) {
                // $a < $b ? -1 : ( ?  : )
                if ($node->else instanceof Ternary) {
                    // $a < $b ? -1 : ( > ?  : )
                    if ($node->else->cond instanceof BinaryOp\Greater) {
                        // $a < $b ? -1 : ($a > $b ?  : )
                        if ($this->areNodesEqual($node->cond->left, $node->else->cond->left)) {
                            if ($this->areNodesEqual($node->cond->right, $node->else->cond->right)) {
                                // $a < $b ? -1 : ($a > $b ? 1 : 0)
                                if ($this->getValue($node->else->if) === 1) {
                                    if ($this->getValue($node->else->else) === 0) {
                                        return new Spaceship($node->cond->left, $node->cond->right);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        return null;
    }
}

?>
-----
<?php

namespace Rector\EarlyReturn\Tests\Rector\If_\ChangeNestedIfsToEarlyReturnRector\Fixture;

use PhpParser\Node\Stmt\If_;

class RectorSample
{
    public function run(If_ $node)
    {
        // $a < $b
        if (!$node->cond instanceof Smaller) {
            return null;
        }
        // $a < $b ? -1
        if ($this->getValue($node->if) !== -1) {
            return null;
        }
        // $a < $b ? -1 : ( ?  : )
        if (!$node->else instanceof Ternary) {
            return null;
        }
        // $a < $b ? -1 : ( > ?  : )
        if (!$node->else->cond instanceof BinaryOp\Greater) {
            return null;
        }
        // $a < $b ? -1 : ($a > $b ?  : )
        if (!$this->areNodesEqual($node->cond->left, $node->else->cond->left)) {
            return null;
        }
        if (!$this->areNodesEqual($node->cond->right, $node->else->cond->right)) {
            return null;
        }
        // $a < $b ? -1 : ($a > $b ? 1 : 0)
        if ($this->getValue($node->else->if) !== 1) {
            return null;
        }
        if ($this->getValue($node->else->else) === 0) {
            return new Spaceship($node->cond->left, $node->cond->right);
        }

        return null;
    }
}

?>
