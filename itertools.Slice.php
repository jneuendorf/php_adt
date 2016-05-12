<?php

class Slice {
    // [start,] stop [, step])
    public function __construct(...$args) {
        if (count($args) == 1) {
            $this->start = 0;
            $this->stop = $args[0];
            $this->step = 1;
        } else {
            $this->start = $args[0];
            $this->stop = $args[1];
            if (count($args) >= 3) {
                $this->step = $args[2];
            } else {
                $this->step = 1;
            }
        }
    }
}
