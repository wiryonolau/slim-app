<?php
namespace App\Tests;

use PHPUnit\Framework\TestCase;
use App\View\View;

final class ClassTest extends TestCase {
    public function testView() {
        $view = new View();
        $this->assertEquals(is_object($view), true);
    }

}


?>
