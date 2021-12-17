<?php

namespace iflow\annotation\lib\utils\hook;

use iflow\annotation\lib\abstracts\AnnotationAbstract;
use iflow\annotation\lib\Config;
use iflow\annotation\lib\utils\hook\abstracts\HookAbstract;
use iflow\annotation\lib\utils\hook\exception\InitializerHookException;
use ReflectionAttribute;

class Hook {

    protected array $hookProcess = [
        'beforeCreate', 'Initializer', 'Created'
    ];

    protected ReflectionAttribute $reflectionAttribute;

    // 实例化完成后的对象
    protected ?AnnotationAbstract $_obj = null;

    public function __construct(protected Config $config) {
    }

    /**
     * 执行入口
     * @param ReflectionAttribute $reflectionAttribute
     * @return AnnotationAbstract
     */
    public function process(ReflectionAttribute $reflectionAttribute): AnnotationAbstract {
        $this->reflectionAttribute = $reflectionAttribute;
        array_map(fn($value) => call_user_func([$this, $value]), $this->hookProcess);
        return $this->_obj;
    }

    /**
     * 初始化对象前
     * @return void
     */
    public function beforeCreate() {
        $this->hookProcess('beforeCreate', $this -> reflectionAttribute -> getName(), $this -> reflectionAttribute);
    }


    /**
     * 初始化对象
     * @return void
     * @throws InitializerHookException
     */
    public function Initializer() {
        $obj = $this->hookProcess('Initializer', $this -> reflectionAttribute -> getName(), $this -> reflectionAttribute);

        if (!is_object($obj)) $obj = $this->reflectionAttribute -> newInstance();
        if ($obj instanceof AnnotationAbstract) $this->_obj = $obj;

        if ($this->_obj === null) throw new InitializerHookException('注解类型错误 object instanceof AnnotationAbstract has valid fail className: '. $obj::class);
    }

    /**
     * 类型创建完毕后回调
     * @return void
     */
    public function Created() {
        $obj = $this->hookProcess('Created', $this -> _obj, $this -> reflectionAttribute);
        if ($obj instanceof AnnotationAbstract) $this->_obj = $obj;
    }


    /**
     * 执行HOOK方法
     * @param string $hookName
     * @return mixed
     */
    public function hookProcess(string $hookName): mixed {
        $_hook = $this->config -> getHook($hookName);
        $args = func_get_args();
        array_shift($args);
        if (is_callable($_hook) || function_exists($_hook)) return call_user_func($_hook, ...$args);
        if (!class_exists($_hook)) return null;

        $_ = new $_hook;
        if ($_ instanceof HookAbstract) return $_ -> handle(...$args);

        return null;
    }
}