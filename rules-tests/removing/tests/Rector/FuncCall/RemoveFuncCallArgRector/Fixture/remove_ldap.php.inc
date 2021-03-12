<?php

namespace Rector\Removing\Tests\Rector\FuncCall\RemoveFuncCallArgRector\Fixture;

class RemoveLdap
{
    public function run()
    {
        $ber_identifier = 'random_value';
        $name = ldap_first_attribute($this->_ldap->getResource(), $this->_current, $ber_identifier);

        return $name;
    }
}

?>
-----
<?php

namespace Rector\Removing\Tests\Rector\FuncCall\RemoveFuncCallArgRector\Fixture;

class RemoveLdap
{
    public function run()
    {
        $ber_identifier = 'random_value';
        $name = ldap_first_attribute($this->_ldap->getResource(), $this->_current);

        return $name;
    }
}

?>
