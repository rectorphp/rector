<?php
function someFunction() {
    switch(true) {
        case 1:
            $foo = 1;
            continue;
        case 2:
            $foo = 2;
            continue 1;
        case 3:
            $foo = 3;
            continue 2;
        case 4:
            $foo = 4;
            break;
        case 5:
            break;
        default:
            continue;
    }
}
?>
-----
<?php
function someFunction() {
    switch(true) {
        case 1:
            $foo = 1;
            break;
        case 2:
            $foo = 2;
            break;
        case 3:
            $foo = 3;
            continue 2;
        case 4:
            $foo = 4;
            break;
        case 5:
            break;
        default:
            break;
    }
}
?>
