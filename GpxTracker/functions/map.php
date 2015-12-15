<?php

function map(Callable $callback, Traversable $filter) {
    foreach ($filter as $key => $value) {
        yield $key => $callback($value);
    }
}
