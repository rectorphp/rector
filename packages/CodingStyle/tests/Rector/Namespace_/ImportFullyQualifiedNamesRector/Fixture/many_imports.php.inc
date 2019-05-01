<?php

namespace Elasticr\Inventory;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\QueryBuilder;
use Elasticr\Inventory\Stock\Query;

final class StockRepository
{
    /** @var \Doctrine\Common\Persistence\ManagerRegistry */
    private $registry;

    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
    }

    public function clear(array $stockrooms): void
    {
        /** @var \Doctrine\DBAL\Connection $connection */
        $connection = $this->registry->getConnection();
        $query      = $connection->createQueryBuilder();

        $query->delete('elasticr_stocks')
            ->andWhere('stockroom_id IN (:id)')
            ->setParameter('id', $stockrooms, Connection::PARAM_INT_ARRAY);

        $query->execute();
    }

    /**
     * @throws \Doctrine\DBAL\DBALException
     */
    public function add(InventoryItems $items): void
    {
        /** @var \Doctrine\DBAL\Connection $connection */
        $connection = $this->registry->getConnection();

        $counter = 0;
        $inserts = [];

        foreach ($items->items() as $item) {
            foreach ($item->stocks() as $stock) {
                $index = $counter / 1000;

                $inserts[$index]['values'][] = '(:uuid'.$counter.', :stockroom'.$counter.', :quantity'.$counter.')';

                $inserts[$index]['parameters'][':uuid'.$counter]       = $item->uuid();
                $inserts[$index]['parameters'][':stockroom'.$counter]  = $stock->stockroom()->id();
                $inserts[$index]['parameters'][':quantity'.$counter]   = $stock->quantity();

                ++$counter;
            }
        }

        foreach ($inserts as $insert) {
            $query = 'INSERT INTO elasticr_stocks (uuid, stockroom_id, quantity) VALUES'.implode(',', $insert['values']);

            $connection->executeUpdate($query, $insert['parameters']);
        }
    }

    public function filter(Stock\Query $query): InventoryItems
    {
        if (!($query instanceof Stock\Querying\Query)) {
            throw new \InvalidArgumentException('Wrong query provided');
        }

        /** @var Querying\Query $query */
        $builder = $this->createBuilder();
        $query->build($builder);

        /** @var \Elasticr\Inventory\Doctrine\Stock[] $stocks */
        $stocks = $builder->getQuery()
            ->useQueryCache(true)
            ->useResultCache(true)
            ->getResult();

        $inventoryItems = new InventoryItems();

        foreach ($stocks as $stock) {
            $inventoryItems->add($stock->uuid->toString(), $stock->stockroom, $stock->quantity);
        }

        return $inventoryItems;
    }

    private function createBuilder(): QueryBuilder
    {
        /** @var \Doctrine\ORM\EntityManager $manager */
        $manager = $this->registry->getManager();

        return $manager->createQueryBuilder();
    }
}
