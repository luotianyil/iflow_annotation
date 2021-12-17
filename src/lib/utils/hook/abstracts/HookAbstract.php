<?php

namespace iflow\annotation\lib\utils\hook\abstracts;

use iflow\annotation\lib\utils\hook\interfaces\HookInterface;

abstract class HookAbstract implements HookInterface {
    public function handle(string|object $class, \Reflection|\Reflector $reflection, array &$args = []): mixed {
        // TODO: Implement handle() method.
        return null;
    }
}