<?php

namespace whm\Smoke\Cli\Command;

use phmLabs\Components\Annovent\Dispatcher;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use whm\Html\Uri;
use whm\Smoke\Config\Configuration;

class CustomCommand extends ConfigurableCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->configureCommand(
            'analyses a website given a config file',
            'The <info>custom</info> command runs a custom website analysis.',
            'custom'
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->init($input, $output);

        $this->initConfiguration(
            $input->getOption('config_file'),
            $this->eventDispatcher);

        if ($input->getOption('bootstrap')) {
            include $input->getOption('bootstrap');
        }

        return $this->scan();
    }

    /**
     * Initializes the configuration.
     *
     * @return Configuration
     */
    private function initConfiguration($configFile, Dispatcher $dispatcher)
    {
        $configArray = $this->getConfigArray($configFile, true);

        if (is_string($configArray)) {
            throw new \RuntimeException('Unable to load config file. Please check ' . $configFile);
        }

        $this->config = new Configuration(new Uri('http://www.example.com'), $dispatcher, $configArray);
    }
}
