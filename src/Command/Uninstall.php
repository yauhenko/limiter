<?php


namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class Uninstall extends Command {

    protected function configure(): void {
        $this->setName('uninstall');
        $this->addArgument('user', InputArgument::OPTIONAL);
        $this->addOption('global', 'g', InputOption::VALUE_NONE);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {
        $io = new SymfonyStyle($input, $output);
        $user = $input->getArgument('user');
        $global = $input->getOption('global');

        if(getenv('USER') !== 'root') {
            $io->error('Run this command as root');
            return Command::FAILURE;
        }

        if($user) {
            if(!is_dir("/home/{$user}")) {
                $io->error('Invalid user');
                return Command::FAILURE;
            }
            $io->text('Deleting autostart entry');
            @unlink("/home/{$user}/.config/autostart/limiter.desktop");
        }

        if($global) {
            $io->text('Deleting /usr/bin/limiter');
            @unlink("/usr/bin/limiter");
        }

        $io->success('Limiter uninstalled');
        return Command::SUCCESS;
    }

}