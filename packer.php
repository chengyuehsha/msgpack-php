<?php

require __DIR__ . '/vendor/autoload.php';

use Chengyueh\MsgPack\Converter;
use Chengyueh\MsgPack\Packer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\SingleCommandApplication;

(new SingleCommandApplication())
    ->addArgument('json', InputArgument::REQUIRED, 'Convert json to msgpack')
    ->setCode(function (InputInterface $input, OutputInterface $output) {
        $json = $input->getArgument('json');
        $data = json_decode($json, false);

        $result = Packer::pack($data);
        $result = Converter::byteArrayToHexArray($result);
        $output->writeln($result);

        return Command::SUCCESS;
    })
    ->run();
