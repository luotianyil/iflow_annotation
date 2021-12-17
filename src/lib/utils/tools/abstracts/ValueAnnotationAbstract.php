<?php

namespace iflow\annotation\lib\utils\tools\abstracts;

use iflow\annotation\lib\abstracts\AnnotationAbstract;
use iflow\annotation\lib\utils\tools\exceptions\ValueException;

abstract class ValueAnnotationAbstract extends AnnotationAbstract {

    // 变量默认值
    protected mixed $default = '';

    protected string $error = '';

    /**
     * 获取当前值
     * @param \ReflectionProperty|\ReflectionParameter $ref
     * @param object|null $object
     * @param array $args
     * @return mixed
     * @throws \Error|\ReflectionException
     */
    public function getValue(\ReflectionProperty|\ReflectionParameter $ref, object|null $object = null, array &$args = []): mixed
    {
        $refIsProperty = $ref instanceof \ReflectionProperty;
        try {
            if ($refIsProperty) return $ref -> getValue($object);

            $value = $args[$ref -> getPosition()] ?? $ref -> getDefaultValue();
            if (is_bool($value) || is_null($value) || is_numeric($value)) return $value;

            return $value ?: throw new \Exception('method miss params null');
        } catch (\Error|\Exception $exception) {
            if ($refIsProperty) return $ref -> getDefaultValue() ?: $this->defaultIsClass();
            // 当方法参数 不存在且无默认值时
            return (
            $ref -> isDefaultValueAvailable() ? $ref -> getDefaultValue() : null
            ) ?: $this->defaultIsClass();
        }
    }

    /**
     * 验证是否为类
     * @return mixed
     */
    protected function defaultIsClass(): mixed {
        if (is_string($this->default) && class_exists($this->default)) {
            // 初始化类
            $this->default = annotation_instance() -> make($this->default);
        }
        return $this->default;
    }

    /**
     * @return mixed
     */
    public function getDefault(): mixed {
        return $this->default;
    }


    protected function getRefDefaultValue(\ReflectionProperty|\ReflectionParameter $ref) {
        if ($ref instanceof \ReflectionProperty) {
            return $ref -> hasDefaultValue() ? $ref -> getDefaultValue() : null;
        }
        return $ref -> isDefaultValueAvailable() ? $ref -> getDefaultValue() : null;
    }

    protected function throw_error($ref) {
        throw new ValueException($this->error ?: "{$ref -> getName()} required");
    }
}