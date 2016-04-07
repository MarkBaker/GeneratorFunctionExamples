<?php

function withGenerator(\Generator $generator) {
    return new \GeneratorHelper\fluent($generator);
}
