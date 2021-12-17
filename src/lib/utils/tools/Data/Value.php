<?php

namespace iflow\annotation\lib\utils\tools\Data;

use iflow\annotation\lib\utils\tools\abstracts\ValueAnnotationAbstract;

#[\Attribute]
class Value extends ValueAnnotationAbstract {

    public function __construct(
        protected mixed $default = "",
        protected string $desc = ""
    ) {}

    /**
     * @throws \ReflectionException
     */
    public function process(&...$args): mixed {
        // TODO: Implement process() method.
        $ref = $args[0];
        if ($ref instanceof \ReflectionParameter) {
            $args[1]['parameters'][$ref -> getPosition()] = $this->getValue($ref, args: $args[1]['parameters']);
        } else {
            $object = $args[count($args) - 1];
            $ref -> setValue($object, $this->getValue($ref, $object));
        }
        return $ref;
    }
}