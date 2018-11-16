<?php

$em = $this->getContainer()->get('doctrine.orm.entity_manager');
/* @var $em \Doctrine\ORM\EntityManager */
$queryBuilder = $em->createQueryBuilder();
/* @var $queryBuilder \Doctrine\ORM\QueryBuilder */
