<?php

namespace iflow\annotation\lib\utils\hook\interfaces;

interface HookInterface {
    public function handle(string|object $class, \Reflection $reflection, array &$args = []): mixed;
}