<?php

function mmap(Callable $callback, ...$iterators) {
    $mi = new MultipleIterator(MultipleIterator::MIT_NEED_ANY);
    foreach($iterators as $iterator) {
        $mi->attachIterator($iterator);
    }

    foreach($mi as $values) {
        yield $callback(...$values);
    }
}
