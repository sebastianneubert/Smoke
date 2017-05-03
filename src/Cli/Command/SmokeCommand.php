<?php

namespace whm\Smoke\Cli\Command;

use Cache\Adapter\Filesystem\FilesystemCachePool;
use GuzzleHttp\Client;
use Ivory\HttpAdapter\CurlHttpAdapter;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use phm\HttpWebdriverClient\Http\Client\Chrome\ChromeClient;
use phm\HttpWebdriverClient\Http\Client\Decorator\CacheDecorator;
use phm\HttpWebdriverClient\Http\Client\Guzzle\GuzzleClient;
use phm\HttpWebdriverClient\Http\Client\HttpClient;
use phmLabs\Components\Annovent\Dispatcher;
use PhmLabs\Components\Init\Init;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use whm\Smoke\Config\Configuration;
use whm\Smoke\Extensions\SmokeHttpClient\CacheAware;
use whm\Smoke\Scanner\Scanner;
use whm\Smoke\Yaml\EnvAwareYaml;

class SmokeCommand extends Command
{
    /**
     * @var OutputInterface
     */
    protected $output;
    protected $eventDispatcher;

    /**
     * @var Configuration
     */
    protected $config;

    protected function init(InputInterface $input, OutputInterface $output, $url = null)
    {
        if ($input->hasOption('bootstrap') && !is_null($input->getOption('bootstrap'))) {
            include $input->getOption('bootstrap');
        }

        $this->output = $output;
        $this->eventDispatcher = new Dispatcher();

        Init::registerGlobalParameter('_eventDispatcher', $this->eventDispatcher);
        Init::registerGlobalParameter('_output', $output);

        $this->writeSmokeCredentials($url);
    }

    /**
     * This function creates the credentials header for the command line.
     *
     * @param null $url
     */
    protected function writeSmokeCredentials($url = null)
    {
        $this->output->writeln("\n Smoke " . SMOKE_VERSION . " by Nils Langner\n");

        if ($url) {
            $this->output->writeln(' <info>Scanning ' . $url . "</info>\n");
        }
    }

    protected function scan()
    {
        $scanner = new Scanner($this->config->getRules(),
            $this->config->getClient(),
            $this->eventDispatcher,
            $this->config->getExtension('_ResponseRetriever')->getRetriever());

        $scanner->scan();

        return $scanner->getStatus();
    }

    /**
     * Returns an array representing the configuration.
     *
     * @param $configFile
     *
     * @return array
     */
    protected function getConfigArray($configFile, $mandatory = false)
    {
        $configArray = array();

        if ($configFile) {
            if (strpos($configFile, 'http://') === 0 || strpos($configFile, 'https://') === 0) {
                $curlClient = new Client();
                $fileContent = (string)$curlClient->get($configFile)->getBody();
            } else {
                if (file_exists($configFile)) {
                    $fileContent = file_get_contents($configFile);
                } else {
                    throw new \RuntimeException("Config file was not found ('" . $configFile . "').");
                }
            }
            $configArray = EnvAwareYaml::parse($fileContent);
        } else {
            if ($mandatory) {
                throw new \RuntimeException('Config file was not defined.');
            }
        }

        return $configArray;
    }
}
