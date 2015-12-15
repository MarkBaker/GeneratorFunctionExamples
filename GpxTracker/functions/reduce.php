<?php

function reduce(Traversable $filter, Callable $callback, $initial = null) {
    $result = $initial;
    foreach($filter as $value) {
        $result = $callback($result, $value);
    }
    return $result;
}
