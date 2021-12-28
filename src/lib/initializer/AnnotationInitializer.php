<?php


namespace iflow\annotation\lib\initializer;

use iflow\annotation\Annotation;
use iflow\annotation\lib\Config;
use iflow\annotation\lib\utils\hook\Hook;
use iflow\annotation\lib\utils\ObjectUtil;
use ReflectionClass;
use ReflectionFunction;
use Reflector;

class AnnotationInitializer {

    // 类全部注解
    protected array $annotations = [];

    // 注解执行顺序
    protected array $annotationProcess = [
        'beforeCreate' => [],
        'Created' => [],
        'beforeMounted' => [],
        'Mounted' => []
    ];

    protected Hook $hook;
    protected Reflector $annotationClass;
    protected ObjectUtil $objectUtil;

    public function __construct(protected Config $config) {

        include_once dirname(__DIR__)."../../helper.php";

        $this->hook = new Hook();
        $this->objectUtil = ObjectUtil::instance();
        $this->objectUtil -> make($this -> config);
        $this->objectUtil -> make($this);
    }

    /**
     * 加载指定类注解
     * @param Reflector $annotationClass
     * @param bool $nonLife
     * @param array $args
     * @param object|null $object
     * @return Reflector
     */
    public function loadAnnotations(
        Reflector $annotationClass,
        bool $nonLife = false,
        array &$args = [],
        ?object &$object = null
    ): Reflector {
        // 如果为系统注解 跳过
        if ($annotationClass -> getName() === \Attribute::class) return $annotationClass;

        $this -> annotationClass = $annotationClass;
        $annotations = $annotationClass -> getAttributes();
        foreach ($annotations as $annotation) {
            // 实例化注解并 获取注解执行顺序
            if ($annotation -> getName() === \Attribute::class) continue;
            $_annotation = $this->hook -> process($annotation);
            if ($nonLife) $_annotation -> process($annotationClass, $args, $object);
            else $this -> annotationProcess = $_annotation -> hookEnum -> getAnnotationLife($this -> annotationProcess, $_annotation);
        }
        return $annotationClass;
    }

    /**
     * 执行指定类方法
     * @param string $class 执行类
     * @param string|\ReflectionMethod $method 执行方法
     * @param array $args 执行参数
     * @return mixed
     * @throws \ReflectionException
     */
    public function execute(string $class = '', string|\ReflectionMethod $method = '', array &$args = []): mixed {
        if ($class !== '' && !class_exists($class)) throw new \Exception('class does not exists');
        $invokeArgs = [];

        if ($class !== '') {
            $reflection = new ReflectionClass($class);

            // 调用类创建前钩子
            $this->hook -> hookProcess('beforeCreate', $class, $reflection);
            $annotationClass = $this -> loadAnnotations($reflection);

            // 执行创建前注解钩子
            $this -> executeAnnotationLifeProcess('beforeCreate', $annotationClass, $args);
            $args['classParameters'] = $args['classParameters'] ?? [];

            // 调用初始化钩子
            $_object = $this->hook -> hookProcess('Initializer', $class, $reflection);
            $_object = (!is_object($_object)) ? (
                empty($args['classParameters']) ? $annotationClass -> newInstance() : $annotationClass -> newInstance(...array_values($args['classParameters']))
            ) : $_object;

            // 创建完毕执行注解 挂载参数
            $this -> executeAnnotationLifeProcess('Created', $annotationClass, $args)
                  -> executeAnnotationLifeProcess('beforeMounted', $annotationClass, $args)
                  -> setRefClassProperties($_object, $annotationClass, $args);

            // 调用创建完毕时钩子
            $this->hook -> hookProcess('Created', $_object, $reflection);
            if ($method === '') return $_object;

            $method = $annotationClass -> getMethod($method);
            $method -> setAccessible(true);
            $invokeArgs[] = $_object;
        }

        if ($method === '') throw new \Exception('method does not exists');
        if (is_string($method) && function_exists($method)) $method = new ReflectionFunction($method);

        // 执行方法注解
        $annotationMethod = $this->loadAnnotations($method, true);
        $this->executeRefMethodParametersAnnotation($annotationMethod, $args);

        $invokeArgs[] = $args['parameters'] ?? [];
        // 执行方法
        return $annotationMethod -> invokeArgs(...$invokeArgs);
    }

    /**
     * 批量设置参数
     * @param object $object
     * @param ReflectionClass $reflectionClass
     * @param array $args
     * @return AnnotationInitializer
     */
    protected function setRefClassProperties(object $object, Reflector &$reflectionClass, array &$args): static {
        foreach ($reflectionClass -> getProperties() as $property) {
            if (isset($args['parameters'][$property -> getName()])) {
                if (!$property -> isPublic()) $property -> setAccessible(true);
                $property -> setValue($object, $property);
            }
            $this -> loadAnnotations($property, true, $args, $object);
        }
        return $this->executeAnnotationLifeProcess('Mounted', $reflectionClass, $args);
    }

    /**
     *
     * @param ReflectionFunction $reflectionFunction
     * @param array $args
     * @return $this
     */
    public function executeRefMethodParametersAnnotation(Reflector &$reflectionFunction, array &$args = []): static {
        $args['parameters'] = $args['parameters'] ?? [];
        array_map(function ($parameter) use (&$reflectionFunction, &$args) {
            $this -> loadAnnotations($parameter, true, $args);
        }, $reflectionFunction -> getParameters());
        return $this;
    }

    /**
     * 获取类注解执行顺序
     * @return array
     */
    public function getAnnotationProcess(): array {
        return $this->annotationProcess;
    }

    protected function executeAnnotationLifeProcess(string $lifeName, Reflector &$reflectionClass, array &$args): static {
        foreach ($this->annotationProcess[$lifeName] as $process) {
            $process -> process($reflectionClass, $args);
        }
        return $this;
    }
}