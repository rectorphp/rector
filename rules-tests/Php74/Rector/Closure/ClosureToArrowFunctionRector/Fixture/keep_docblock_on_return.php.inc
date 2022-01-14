<?php

function() {
    /** @psalm-suppress UndefinedFunction */
    return ff();
};
?>
-----
<?php

fn() => /** @psalm-suppress UndefinedFunction */
ff();
?>
