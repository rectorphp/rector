<?php

namespace Rector\Tests\Php80\Rector\FuncCall\Php8ResourceReturnToObjectRector\Fixture;

final class SomeLdapConnect
{
    public function run()
    {
        // LDAP variables
        $ldapuri = "ldap://ldap.example.com:389";  // your ldap-uri

        // Connecting to LDAP
        $ldapconn = ldap_connect($ldapuri);
        is_resource($ldapconn);
    }
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\FuncCall\Php8ResourceReturnToObjectRector\Fixture;

final class SomeLdapConnect
{
    public function run()
    {
        // LDAP variables
        $ldapuri = "ldap://ldap.example.com:389";  // your ldap-uri

        // Connecting to LDAP
        $ldapconn = ldap_connect($ldapuri);
        $ldapconn instanceof \LDAP\Connection;
    }
}

?>