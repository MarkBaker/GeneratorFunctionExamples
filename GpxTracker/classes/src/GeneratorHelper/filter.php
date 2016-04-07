<?php

namespace GeneratorHelper;

class filter {
    public $callback;
    public $flag;
    
    public function __construct(Callable $callback, $flag = 0) {
        $this->callback = $callback;
        $this->flag = $flag;
    }
}
