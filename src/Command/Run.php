<?php

namespace App\Command;

use App\Database;
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
		$db = new Database($user);
		$max = 2 * 60 * 60;
		$welcome = false;

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

		while(true) {

			$time = $db->getUsage();
			$rest = (int)ceil(($max - $time) / 60);
			$io->text("Time used: <fg=green>{$time} sec</>; Time left: <fg=red>{$rest} min</>");

			if($rest > 0 && !$welcome) {
				$this->notify('Service started, ' . $rest . ' min. left');
				$welcome = true;
			}

			if($rest === 20) {
				$this->notify('20 minutes left');
			} elseif($rest <= 5 && $rest > 0) {
				$this->notify("{$rest} min. left");
			}

			if($rest <= 0) {
				$this->notify("Timeout! Turn off your computer!");
				sleep(5);
				system('dbus-send --session --type=method_call --print-reply --dest=org.gnome.SessionManager /org/gnome/SessionManager org.gnome.SessionManager.Logout uint32:1');
			}

			$db->tick();
			sleep(60);
		}

	}

	protected function notify(string $text) {
		system('notify-send --urgency normal --expire-time=10000 "Family Link" ' . escapeshellarg($text));
	}

}
