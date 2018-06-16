<?php


namespace Dongyu\Tests;


use Dongyu\Container\Container;
use PHPUnit\Framework\TestCase;

class ContainerTest extends TestCase
{
    public function testBind()
    {
        $container = new Container();
        $container->bind("name", function () {
            return "messi";
        });
        $this->assertEquals(true, $container->has("name"));
    }

    public function testGet()
    {
        $container = new Container();
        $container->bind("name", function () {
            return "Messi";
        });

        $this->assertEquals("Messi", $container->get("name"));
    }


    public function testMake()
    {
        $container = new Container();
        $container->bind("class", \stdClass::class);
        $this->assertInstanceOf(\stdClass::class, $container->make("class"));
    }


    public function testMakeWithPrimitiveArgs()
    {
        $container = new Container();
        $container->bind("animal", AnimalStub::class);
        $animal = $container->make("animal", ["name" => "echo"]);
        $this->assertEquals("echo", $animal->name);
    }


    public function testMakeWithClassArgs()
    {
        $container = new Container();
        $container->bind("super", SuperStub::class);
        $this->assertInstanceOf(SuperStub::class, $container->make("super"));
    }


}

class AnimalStub
{
    public $name;

    public function __construct($name)
    {
        $this->name = $name;
    }
}


class PowerStub
{

}

class SuperStub
{

    public $power;

    public function __construct(PowerStub $power)
    {
        $this->power = $power;
    }
}