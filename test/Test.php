<?php
/**
 * Des:
 * Author: larry
 * Date: 24/01/2018
 * Time: 6:20 PM
 */
class Test extends PHPUnit_Framework_TestCase
{
    public function testAutoPass()
    {
        $this->assertEquals(
            'yubolun',
            'yubolun'
        );
    }
}