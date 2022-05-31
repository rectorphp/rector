<?php

namespace App;

class Foo {

    /**
     * @param \DateTime|\DateTimeImmutable $date
     * @return string
     */
    public function bar($date) {
        return $date->format('C');
    }
}
