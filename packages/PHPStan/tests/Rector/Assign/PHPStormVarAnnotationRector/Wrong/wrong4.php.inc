<?php

function phpstormVarAnnotation4()
{
    $em = $this->getContainer()->get('doctrine.orm.entity_manager');
    /* @var $em \Doctrine\ORM\EntityManager */
    $queryBuilder = $em->createQueryBuilder();
    /* @var $queryBuilder \Doctrine\ORM\QueryBuilder */
}

?>
-----
<?php

function phpstormVarAnnotation4()
{
    /** @var \Doctrine\ORM\EntityManager $em */
    $em = $this->getContainer()->get('doctrine.orm.entity_manager');
    /** @var \Doctrine\ORM\QueryBuilder $queryBuilder */
    $queryBuilder = $em->createQueryBuilder();
}

?>
