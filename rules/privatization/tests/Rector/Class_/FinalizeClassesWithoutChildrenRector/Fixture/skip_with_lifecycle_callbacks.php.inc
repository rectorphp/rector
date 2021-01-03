<?php

namespace Rector\Privatization\Tests\Rector\Class_\FinalizeClassesWithoutChildrenRector\Fixture;

use Doctrine\ORM\Mapping as ORM;
use Snowflake\Snowflake;

/**
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table
 * @ORM\Entity
 */
class SkipWithLifecycleCallbacks
{
    /**
     * @var Expense
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Expense", inversedBy="expenseTags")
     */
    private $expense;

    /**
     * @var Snowflake
     *
     * @ORM\Id
     * @ORM\Column(name="tag_id", type="snowflake")
     */
    private $tagId;

    public function __construct(Expense $expense, Snowflake $tagId)
    {
        $this->expense = $expense;
        $this->tagId = $tagId;
    }

    public function tagId(): Snowflake
    {
        return $this->tagId;
    }
}
