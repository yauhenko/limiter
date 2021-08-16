<?php

namespace App;

class Database {

	protected string $path;
	protected array $data = [];
	protected string $user;

	public function __construct(string $user) {
		$this->user = $user;
		$this->path = "/home/{$user}/.config/limiter.json";
	}

	public function load(): self {
		if(file_exists($this->path)) {
			$this->data = json_decode(file_get_contents($this->path), true) ?: [];
		} else {
			$this->data = [];
		}
 		return $this;
	}

	public function getUsage(): int {
		$this->load();
		return $this->data[ date('Y-m-d') ] ?? 0;
	}

	public function tick(): self {
		$current = $this->getUsage();
		$this->data[ date('Y-m-d') ] = $current + 1;
		$this->save();
		return $this;
	}

	public function save(): self {
		file_put_contents($this->path, json_encode($this->data, JSON_PRETTY_PRINT));
		return $this;
	}

}
