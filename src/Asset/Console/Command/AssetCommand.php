<?php

namespace Itseasy\Asset\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Itseasy\Asset\AssetManager;
use Exception;

class AssetCommand extends Command {
    protected static $defaultName = "asset";

    public function __construct(AssetManager $assetManager) {
        parent::__construct();

        $this->assetManager = $assetManager;
    }

    public function execute(InputInterface $input, OutputInterface $output) {
        if ($input->getOption("clear") or $input->getOption("build")) {
            try {
                $output->writeln("Clearing asset cache");
                $this->assetManager->clear();
                $output->writeln("Clearing done");
            } catch (Exception $e) {
                return Command::FAILURE;
            }
        }

        if ($input->getOption("build")) {
            try {
                $output->writeln("Rebuild asset cache");
                $this->assetManager->build();
                $output->writeln("Rebuild done");
            } catch (Exception $e) {
                return Command::FAILURE;
            }
        }

        return Command::SUCCESS;
    }

    protected function configure() {
        $this->addOption(
            "clear",
            null,
            InputOption::VALUE_NONE,
            "Clear Cached Asset"
        );

        $this->addOption(
            "build",
            null,
            InputOption::VALUE_NONE,
            "Build and Reload Asset"
        );
    }
}
