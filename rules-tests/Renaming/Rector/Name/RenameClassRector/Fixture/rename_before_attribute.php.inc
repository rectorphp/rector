<?php

namespace Rector\Tests\Renaming\Rector\Name\RenameClassRector\Fixture;

use Doctrine\DBAL\DBALException;

class RenameBeforeAttribute
{
    /**
     * @throws DBALException
     */
    #[Route(path: '/top/', name: 'top')]
    public function testWithRouteAttribute(){
        // something throws the exception deep down here....
    }
}

?>
-----
<?php

namespace Rector\Tests\Renaming\Rector\Name\RenameClassRector\Fixture;

use Doctrine\DBAL\DBALException;

class RenameBeforeAttribute
{
    /**
     * @throws \Doctrine\DBAL\Exception
     */
    #[Route(path: '/top/', name: 'top')]
    public function testWithRouteAttribute(){
        // something throws the exception deep down here....
    }
}

?>
