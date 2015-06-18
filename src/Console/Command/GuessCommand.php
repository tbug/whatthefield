<?php

namespace WhatTheField\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;

use WhatTheField\Reader\XMLReader;
use WhatTheField\Guesser\Guesser;

class GuessCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('guess')
            ->setDescription('Guesses field types based on given configuration')
            ->addArgument(
                'file',
                InputArgument::REQUIRED,
                'Data collection file'
            );
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $logger = new ConsoleLogger($output);
        $name = $input->getArgument('file');
        if (!is_file($name)) {
            throw new \RuntimeException("{$name} is not a file");
        }

        $reader = new XMLReader($name);
        $root = $reader->read();
        //$guesser = new Guesser($reader->read(), $logger);
        //$guesser->execute();

        $root->query('item[id]');

    }
}