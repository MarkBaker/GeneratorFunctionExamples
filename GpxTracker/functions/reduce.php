<?php

function reduce(Traversable $iterator, Callable $callback, $initial = null) {
    $result = $initial;
    foreach($iterator as $value) {
        $result = $callback($result, $value);
    }
    return $result;
}
