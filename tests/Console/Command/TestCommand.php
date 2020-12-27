<?php

namespace Itseasy\Test\Console\Command;

use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TestCommand extends SymfonyCommand {
    public function __construct() {
        parent::__construct("test");
    }

    protected function configure() {
        $this->addArgument('username', InputArgument::REQUIRED, 'The username of the user.');
    }

    public function execute(InputInterface $input, OutputInterface $output) {
        $username = $input->getArgument("username");
        $output->writeln("Hello $username");
        return SymfonyCommand::SUCCESS;
    }
}
