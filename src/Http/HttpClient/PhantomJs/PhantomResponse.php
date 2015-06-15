<?php
/**
 * Created by PhpStorm.
 * User: langn
 * Date: 11.06.15
 * Time: 16:15
 */

namespace whm\Smoke\Http\HttpClient\PhantomJs;


use whm\Smoke\Http\Response;

class PhantomResponse extends Response
{
    private $consoleOutput;

    /**
     * @return mixed
     */
    public function getConsoleOutput()
    {
        return $this->consoleOutput;
    }

    /**
     * @param mixed $consoleOutput
     */
    public function setConsoleOutput($consoleOutput)
    {
        $this->consoleOutput = $consoleOutput;
    }
}
