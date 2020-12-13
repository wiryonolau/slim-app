<?php
namespace App\Tests;

use PHPUnit\Framework\TestCase;
use App\Application;
use Symfony\Component\Console\Tester\CommandTester;

final class ConsoleTest extends TestCase {
    public function testConsole() {
        $app = new Application([
            "config_path" => __DIR__."/config/*.config.php"
        ]);
        $app->setApplicationType(Application::APP_CONSOLE)->build();

        $username = "yoyoyo";
        $command = $app->getApplication()->find('test');
        $commandTester = new CommandTester($command);
        $commandTester->execute(["username" => $username]);
        $output = $commandTester->getDisplay();
        $this->assertEquals($output, "Hello $username\n");
    }

}


?>
