<?php

namespace Dongyu\Container;

use Psr\Container\ContainerInterface;

class Container implements ContainerInterface
{
    /**
     * @var array
     */
    protected $binds = [];

    protected $resolved = [];

    public function get($id)
    {
        return $this->make($id);
    }

    public function has($id)
    {
        return isset($this->binds[$id]);
    }

    /**
     * @param string $id
     * @param string|\Closure $concrete
     */
    public function bind($id, $concrete)
    {
        $this->binds[$id] = $concrete;
    }


    public function make($id, $params = [])
    {
        if (!$this->has($id)) {
            throw new NotFoundException("{$id} not bound");
        }

        $concrete = $this->binds[$id];
        if ($concrete instanceof \Closure) {
            $object = call_user_func_array($concrete, [$this, $params]);
        } else {
            $object = $this->resolve($concrete,$params);
        }

        $this->resolved[$id] = true;
        return $object;
    }

    protected function resolve($className, $params = [])
    {
        if (!class_exists($className)) {
            throw new ContainerException("not found class {$className}");
        }

        $reflector = new \ReflectionClass($className);

        if (!$reflector->isInstantiable()) {
            throw  new ContainerException("{$className} is not instantiable");
        }

        $constructor = $reflector->getConstructor();
        if (is_null($constructor)) {
            return $reflector->newInstance();
        }


        $dependencies = $constructor->getParameters();
        $argvValues = $this->resolveDependencies($dependencies, $params);
        return $reflector->newInstanceArgs($argvValues);
    }


    protected function resolveDependencies($dependencies, $params = [])
    {
        $results = [];
        foreach ($dependencies as $dependency) {
            if (!$dependency instanceof \ReflectionParameter) {
                continue;
            }
            $name = $dependency->name;

            // 处理传参的情况
            if (isset($params[$name])) {
                $argValue = $params[$name];
                if ($argValue instanceof \Closure) {
                    $results[] = call_user_func_array($argValue, [$this, $params]);
                } else {
                    $results[] = $argValue;
                }
                continue;
            }



            //  处理未传参数的情况
            $results[] = is_null($dependency->getClass()) ? $this->resolvePrimitive($dependency) : $this->resolveClass($dependency, $params);
        }
        return $results;
    }

    /**
     * 处理基础类型参数的默认值
     * @param \ReflectionParameter $parameter
     * @return mixed
     */
    protected function resolvePrimitive(\ReflectionParameter $parameter)
    {

        // 默认值
        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }

        $this->unresolvablePrimitive($parameter);
    }


    protected function resolveClass(\ReflectionParameter $parameter)
    {
        try {
            return $this->resolve($parameter->getClass()->name);
        } catch (\Exception $exception) {
            if ($parameter->isOptional()) {
                return $parameter->getDefaultValue();
            }
            throw $exception;
        }
    }


    protected function unresolvablePrimitive(\ReflectionParameter $parameter)
    {
        $message = "Unresolvable dependency resolving [$parameter] in class {$parameter->getDeclaringClass()->getName()}";

        throw new ContainerException($message);
    }


}