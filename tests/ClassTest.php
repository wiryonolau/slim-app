<?php
namespace App\Tests;

use PHPUnit\Framework\TestCase;
use App\View;

final class ClassTest extends TestCase {
    public function testView() {
        $view = new View\View();
        $this->assertEquals(is_object($view), true);
    }
    
}


?>
