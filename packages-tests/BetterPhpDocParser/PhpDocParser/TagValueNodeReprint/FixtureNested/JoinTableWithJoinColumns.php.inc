<?php

use Doctrine\ORM\Mapping as ORM;

final class JoinTableWithJoinColumns
{
    /**
     * @ORM\JoinTable(name="PushCampaignCluster", joinColumns={
     *      @ORM\JoinColumn(name="pushCampaignId", referencedColumnName="id")
     * })
     */
    public $name;
}

?>
-----
PhpParser\Node\Stmt\Property
-----
Doctrine\ORM\Mapping\JoinTable
