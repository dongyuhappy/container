## Introduction

This package implement PSR-11 Container interface.


## Install via Composer

`composer require dongyu/container`

## Usage

 ```php
 
 
require './vendor/autoload.php';

use Dongyu\Container\Container;

$container = new Container();


//basic

$container->bind("name", function () {
    return "messi";
});
echo $container->make("name"),PHP_EOL; // 'messi'


// make class
$container->bind('superman',Superman::class);
$spiderMan = $container->make('superman',['name'=>'spiderman']);
echo $spiderMan->name,PHP_EOL;// spiderman




class Superman
{
    public $name;
    public $power;

    /**
     * Superman constructor.
     * @param $name
     * @param Power $power
     */
    public function __construct($name, Power $power)
    {
        $this->name = $name;
        $this->power = $power;
    }

}


class Power
{
    public $name = 'power name';


}
 
 
 
 ```