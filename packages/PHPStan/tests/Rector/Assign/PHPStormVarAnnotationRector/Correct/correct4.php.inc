<?php

/** @var \Doctrine\ORM\EntityManager $em */
$em = $this->getContainer()->get('doctrine.orm.entity_manager');
/** @var \Doctrine\ORM\QueryBuilder $queryBuilder */
$queryBuilder = $em->createQueryBuilder();
