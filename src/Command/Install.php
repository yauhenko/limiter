<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class Install extends Command {

    protected function configure(): void {
        $this->setName('install');
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
            if(!is_dir("/home/{$user}/.config/autostart")) {
                $io->text('Creating autostart directory...');
                mkdir("/home/{$user}/.config/autostart", 0755, true);
            }
            $io->text('Creating desktop entry...');
            file_put_contents("/home/{$user}/.config/autostart/limiter.desktop", '[Desktop Entry]
Type=Application
Name=Family Link
Exec=limiter run
Icon=' . realpath(__DIR__ . '/../../icon.png') . '
X-GNOME-Autostart-enabled=true
');

            $io->text('Chowning...');
            system("chown -R {$user}:{$user} /home/{$user}/.config");


        }

        if($global) {
            $io->text('Linking /usr/bin/limiter ...');
            if(!file_exists("/usr/bin/limiter")) {
                system("ln -s " . (__DIR__ . '/../../bin/limiter.php') . " /usr/bin/limiter");
            }
        }

        $io->success('Limiter installed');
        return Command::SUCCESS;
    }

}
