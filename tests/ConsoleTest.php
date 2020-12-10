<?php
namespace App\Tests;

use PHPUnit\Framework\TestCase;
use App\Console;
use Symfony\Component\Console\Tester\CommandTester;

final class ConsoleTest extends TestCase {
    public function testConsole() {
        $app = new Console(__DIR__."/config/*.config.php");

        $username = "yoyoyo";

        $command = $app->getApplication()->find('test');
        $commandTester = new CommandTester($command);
        $commandTester->execute(["username" => $username]);
        $output = $commandTester->getDisplay();
        $this->assertEquals($output, "Hello $username\n");
    }

}


?>
