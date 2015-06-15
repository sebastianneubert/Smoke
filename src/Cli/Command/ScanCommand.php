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
use whm\Smoke\Http\HttpClient\PhantomJs\PhantomJsHttpClient;
use whm\Smoke\Http\HttpClient\Standard\StandardHttpClient;
use whm\Smoke\Scanner\Scanner;

class ScanCommand extends Command
{
    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setDefinition([
                new InputArgument('url', InputArgument::REQUIRED, 'the url to start with'),
                new InputOption('parallel_requests', 'p', InputOption::VALUE_OPTIONAL, 'number of parallel requests.', 10),
                new InputOption('num_urls', 'u', InputOption::VALUE_OPTIONAL, 'number of urls to be checled', 20),
                new InputOption('config_file', 'c', InputOption::VALUE_OPTIONAL, 'config file'),
                new InputOption('bootstrap', 'b', InputOption::VALUE_OPTIONAL, 'bootstrap file'),
            ])
            ->setDescription('analyses a website')
            ->setHelp('The <info>analyse</info> command runs a cache test.')
            ->setName('analyse');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $eventDispatcher = new Dispatcher();

        $config = $this->initConfiguration(
            $input->getOption('config_file'),
            $input->getOption('num_urls'),
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

        $httpClient = new PhantomJsHttpClient();
        $httpClient->init("/usr/local/Cellar/phantomjs/1.9.7/bin/phantomjs");

        $httpClient = new StandardHttpClient(HttpAdapterFactory::guess());

        $scanner = new Scanner($config, $httpClient, $eventDispatcher);

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
    private function initConfiguration($configFile, $num_urls, $parallel_requests, Uri $uri, Dispatcher $dispatcher)
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

        $config = new Configuration($uri, $dispatcher, $configArray);

        if ($num_urls) {
            $config->setContainerSize($num_urls);
        }

        if ($parallel_requests) {
            $config->setParallelRequestCount($parallel_requests);
        }

        return $config;
    }
}
