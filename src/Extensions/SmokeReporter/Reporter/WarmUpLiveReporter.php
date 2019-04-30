<?php

namespace whm\Smoke\Extensions\SmokeReporter\Reporter;

use Symfony\Component\Console\Output\OutputInterface;

class WarmUpLiveReporter implements Reporter
{
    /**
     * @var OutputInterface
     */
    private $output;
    private $urlCount = 0;

    public function init(OutputInterface $_output)
    {
        $this->output = $_output;
    }

    public function processResults($results)
    {
        if (count($results) > 0) {
            ++$this->urlCount;
            $firstResult = array_pop($results);
            $this->output->writeln('   ' . (string) $firstResult->getResponse()->getUri());
            $this->output->writeln('');
        }
    }

    public function finish()
    {
        $this->output->writeln('   <comment>Warm up finished. ' . $this->urlCount . ' urls visited.</comment>');
        $this->output->writeln('');
    }
}
