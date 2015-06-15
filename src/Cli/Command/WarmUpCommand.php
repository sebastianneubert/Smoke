<?php

namespace whm\Smoke\Cli\Command;

use Ivory\HttpAdapter\HttpAdapterFactory;
use Phly\Http\Uri;
use phmLabs\Components\Annovent\Dispatcher;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;
use whm\Smoke\Config\Configuration;
use whm\Smoke\Http\HttpClient\Standard\StandardHttpClient;
use whm\Smoke\Scanner\Scanner;

class WarmUpCommand extends Command
{
    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setDefinition([
                new InputArgument('url', InputArgument::REQUIRED, 'the url to start with'),
                new InputOption('duration', 'd', InputOption::VALUE_OPTIONAL, 'duration in seconds', 60),
                new InputOption('config_file', 'c', InputOption::VALUE_OPTIONAL, 'config file'),
                new InputOption('parallel_requests', 'p', InputOption::VALUE_OPTIONAL, 'number of parallel requests.', 10),
                new InputOption('bootstrap', 'b', InputOption::VALUE_OPTIONAL, 'bootstrap file'),
            ])
            ->setDescription('analyses a website')
            ->setHelp('The <info>warmup</info> command warms a website up.')
            ->setName('warmup');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $eventDispatcher = new Dispatcher();

        $config = $this->initConfiguration(
            $input->getOption('config_file'),
            $input->getOption('parallel_requests'),
            new Uri($input->getArgument('url')),
            $eventDispatcher);

        $eventDispatcher->simpleNotify('ScannerCommand.Config.Register', array('config' => $config));
        $eventDispatcher->simpleNotify('ScannerCommand.Output.Register', array('output' => $output));

        $output->writeln("\n Smoke " . SMOKE_VERSION . " by Nils Langner\n");
        $output->writeln(' <info>Scanning ' . $config->getStartUri() . "</info>\n");

        if ($input->getOption('bootstrap')) {
            include $input->getOption('bootstrap');
        }

        $scanner = new Scanner($config, new StandardHttpClient(HttpAdapterFactory::guess()), $eventDispatcher);

        $timeStrategy = $config->getExtension('_SmokeStop')->getStrategy('_TimeStop');
        $timeStrategy->init($input->getOption('duration'));

        $scanner->scan();

        return $scanner->getStatus();
    }

    /**
     * Initializes the configuration.
     *
     * @param $configFile
     * @param $loadForeign
     * @param Uri $uri
     *
     * @return Configuration
     */
    private function initConfiguration($configFile, $parallel_requests, Uri $uri, Dispatcher $dispatcher)
    {
        if ($configFile) {
            if (file_exists($configFile)) {
                $configArray = Yaml::parse(file_get_contents($configFile));
            } else {
                throw new \RuntimeException("Config file was not found ('" . $configFile . "').");
            }
        } else {
            $configArray = [];
        }

        $defaultConfig = Yaml::parse(file_get_contents(__DIR__ . '/../../settings/warmup.yml'));

        $config = new Configuration($uri, $dispatcher, $configArray, $defaultConfig);

        $config->setContainerSize(0);

        if ($parallel_requests) {
            $config->setParallelRequestCount($parallel_requests);
        }

        return $config;
    }
}
