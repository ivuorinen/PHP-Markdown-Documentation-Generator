<?php

namespace PHPDocsMD\Console;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command line interface used to extract markdown-formatted documentation from classes
 *
 * @package PHPDocsMD\Console
 */
class CLI extends Application
{
    public function __construct()
    {
        $json = json_decode(file_get_contents(__DIR__ . '/../../../composer.json'), false);
        parent::__construct('PHP Markdown Documentation Generator', $json->version ?? 'No version');
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface|null   $input
     * @param \Symfony\Component\Console\Output\OutputInterface|null $output
     *
     * @return int
     * @throws \Exception
     */
    public function run(InputInterface $input = null, OutputInterface $output = null): int
    {
        $this->add(new PHPDocsMDCommand());

        return parent::run($input, $output);
    }
}
