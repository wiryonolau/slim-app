<?php
namespace Itseasy\Test;

use PHPUnit\Framework\TestCase;
use Itseasy\View\View;

final class ClassTest extends TestCase
{
    public function testView()
    {
        $view = new View();
        $this->assertEquals(is_object($view), true);
    }
}
