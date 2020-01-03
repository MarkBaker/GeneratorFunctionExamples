<?php

function map(Callable $callback, Traversable $iterator) {
    foreach ($iterator as $key => $value) {
        yield $key => $callback($value);
    }
}
