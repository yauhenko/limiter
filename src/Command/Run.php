<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class Run extends Command {

    protected function configure(): void {
        $this->setName('run');
        $this->addOption('detach', 'd', InputOption::VALUE_NONE);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {
        $io = new SymfonyStyle($input, $output);
        $user = getenv('USER');
        $io->text('Runnig limiter for: ' . $user);
        $time = (int)@file_get_contents("/home/{$user}/.config/limiter");
        $max = 3600 * 2;
        $welcome = true;
        $prevRestMin = null;

        if($input->getOption('detach')) {
            $pid = pcntl_fork();
            if($pid) {
                $io->success('Running in detached mode. Pid: ' . $pid);
                fclose(STDIN);
                fclose(STDOUT);
                fclose(STDERR);
                return Command::SUCCESS;
            }
            posix_setsid();
        }

        while (true) {
            $rest = $max - $time;
            $restMin = floor($rest / 60);

            if($rest && $welcome) {
                $this->notify('Сервис запущен, осталось ' . $restMin . ' мин.');
                $welcome = false;
            }

            if($prevRestMin !== $restMin) {

                $io->text($restMin);
                if($restMin === 20) {
                    $this->notify('Осталось 20 мин.');
                } elseif ($restMin <= 5 && $rest >= 60) {
                    $this->notify("Осталось {$restMin} мин.");
                } elseif($rest < 60) {
                    $this->notify("Осталось меньше минуты");
                }
            }

            if($rest === 0) {
                $this->notify("Время вышло! Выключи компьютер");
                sleep(5);
                system('dbus-send --session --type=method_call --print-reply --dest=org.gnome.SessionManager /org/gnome/SessionManager org.gnome.SessionManager.Logout uint32:1');
            }

            file_put_contents("/home/{$user}/.config/limiter", $time);
            $prevRestMin = $restMin;
            $time++;
            if($time > $max) $time = $max;
            sleep(1);
        }

    }

    protected function notify(string $text) {
        //system('notify-send --urgency normal --expire-time=10000 --icon ' . realpath(__DIR__ . '/../../icon.png') . ' "FamilyLink" ' . escapeshellarg($text));
        system('notify-send --urgency normal --expire-time=10000 "Family Link" ' . escapeshellarg($text));
    }

}