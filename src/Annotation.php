<?php


namespace iflow\annotation;

use iflow\annotation\lib\abstracts\AnnotationAbstract;
use iflow\annotation\lib\Config;
use iflow\annotation\lib\initializer\AnnotationInitializer;
use iflow\annotation\lib\utils\FileUtils;

#[\Attribute]
class Annotation extends AnnotationAbstract {


    protected Config $config;
    protected FileUtils $fileUtils;
    protected AnnotationInitializer $annotationInitializer;

    // 所找到的类
    protected array $useClass = [];

    protected array $classes = [];

    protected string $projectRoot;

    /**
     * Annotation constructor.
     * @param array $config
     */
    public function __construct(array $config = []) {
        $this->config = annotation_instance(Config::class) -> reSetConfig($config);
        $this->fileUtils = new FileUtils();
        $this->annotationInitializer = new AnnotationInitializer($this->config);

        $content = file_exists($this->config -> getCachePath()) ? file_get_contents($this->config -> getCachePath()) : "";
        $this->useClass = $content ? unserialize($content) : [];

        $this->projectRoot = $this->config -> getProjectRoot();
    }

    /**
     * 扫描并实例化类
     * @throws \ReflectionException
     */
    public function process(&...$args): static {
        if (!$this->config -> getCache() || empty($this->useClass)) {
            foreach ($this->config -> getNameSpaces() as $path) {
                $this->useClass[$path] = $this->fileUtils -> loadFileList($path, '.php', true);
            }
            $this->loadPackClass($this->useClass);
        } else {
            $this->loadCachePackClass();
        }
        return $this;
    }

    /**
     * 加载可用类
     * @param array $useClass
     * @param string $nameSpace
     * @throws \ReflectionException
     */
    protected function loadPackClass(array $useClass = [], string $nameSpace = '') {
        foreach ($useClass as $key => $value) {
            if (is_array($value)) {
                if (sizeof($value) > 0) $this->loadPackClass($value, $nameSpace.'\\'.$key);
            } elseif (file_exists($value)) {
                $class = str_replace('.php', '', str_replace($this->projectRoot, '', $value));
                $class = str_replace('/', '\\', $class);
                if (class_exists($class) || !in_array($class, $this->useClass)) {
                    $this->classes[] = $class;
                    $this->annotationInitializer -> loadAnnotations(new \ReflectionClass($class));
                }
            }
        }
        if ($this->config -> getCache()) $this->saveCachePackClass();
    }

    /**
     * 加载缓存类
     * @throws \ReflectionException
     */
    public function loadCachePackClass() {
        foreach ($this->useClass as $class) {
            $this->annotationInitializer -> loadAnnotations(new \ReflectionClass($class));
        }
    }

    /**
     * 储存缓存文件
     * @return bool|int
     */
    public function saveCachePackClass(): bool|int {
        $path = str_replace("\\", '/', $this->config -> getCachePath());
        !is_dir(dirname($path)) && mkdir(dirname($path), recursive: true);
        $content = serialize($this->classes);
        return file_put_contents($path, $content);
    }
}