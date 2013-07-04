<?php

require_once("include/bootstrap.php");

class LoadTest extends PHPUnit_Framework_TestCase
{
    protected function setUp() {
    }

    protected function tearDown() {
    }

    public function testAboutName() {
        $this->assertEquals(1, 1);
    }
}