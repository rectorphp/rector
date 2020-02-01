<?php

namespace Rector\DeadCode\Tests\Rector\ClassMethod\RemoveUnusedParameterRector\Fixture;

class SkipDeepNestedParams
{
    public function run($eventQb, $parentQb, $q, $contactId, $type)
    {
        $q->select('e,c')
            ->innerJoin('e.campaign', 'c')
            ->innerJoin('c.leads', 'l')
            ->where(
                $q->expr()->andX(
                    $q->expr()->eq('c.isPublished', 1),
                    $q->expr()->eq('e.type', ':type'),
                    $q->expr()->eq('IDENTITY(l.lead)', ':contactId'),
                    $q->expr()->eq('l.manuallyRemoved', 0),
                    $q->expr()->notIn('e.id', $eventQb->getDQL()),
                    $q->expr()->orX(
                        $q->expr()->isNull('e.parent'),
                        $q->expr()->exists($parentQb->getDQL())
                    )
                )
            )
            ->setParameter('type', $type)
            ->setParameter('contactId', (int) $contactId);
        return $q->getQuery()->getResult();
    }
}
