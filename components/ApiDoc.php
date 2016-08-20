<?php
/**
 *
 * Author: chenhongwei <chenhw@mysoft.com.cn>
 * Time: 2016/7/21 14:54
 */

namespace craa\ApiDebugger\components;


class ApiDoc
{
    /**
     * @var \ReflectionClass|\ReflectionMethod $reflection
     */
    private $reflection;
    /**
     * @var string $id
     */
    private $id;
    /**
     * @var string $route 当前注解所在路由
     */
    private $route;
    /**
     * @var string $namespace 当前注解所在命名空间
     */
    private $namespace;
    /**
     * @var string $name 名称
     */
    private $name;
    /**
     * @var bool $enable 是否开启
     */
    private $enable;

    /**
     * @param \ReflectionClass|\ReflectionMethod $doc
     */
    public function __construct($doc)
    {
        $this->reflection = $doc;
        $this->extractProperties($doc->getDocComment());
    }

    private function extractProperties($comment, $prefix = '')
    {
        if (preg_match_all('/(?<=@' . $prefix . ')(\w+)([^@]*)?/u', $comment, $matches)) {
            for ($i = 0; $i < count($matches[0]); $i++) {
                $key = $matches[1][$i];
                $value = trim(str_replace(['/*', '     * ', " *\n", ' * ', ' */'], '', $matches[2][$i]));
                if (property_exists($this, $key)) {
                    if (empty($this->$key)) {
                        $this->$key = $value;
                    } elseif (is_array($this->$key)) {
                        $this->{$key}[] = $value;
                    } else {
                        $this->$key = [$this->$key, $value];
                    }
                }
            }
        }
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name ? $this->name : $this->getId();
    }

    /**
     * @return mixed
     */
    public function getEnable()
    {
        return $this->enable !== 'false';
    }

    /**
     * @return string
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * @param string $route
     */
    public function setRoute($route)
    {
        $this->route = $route;
    }

    /**
     * @return string
     */
    public function getNamespace()
    {
        return $this->reflection->getNamespaceName();
    }

    /**
     * @return \ReflectionClass|\ReflectionMethod
     */
    public function getReflection()
    {
        return $this->reflection;
    }
    
}
