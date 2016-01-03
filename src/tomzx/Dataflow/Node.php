<?php

namespace tomzx\Dataflow;

use tomzx\Dataflow\Processor\Parallel\Threaded;

class Node extends Threaded {
	/**
	 * @var callable
	 */
	private $callable;

	/**
	 * @var mixed
	 */
	private $result;

	/**
	 * @param callable $callable
	 */
	public function __construct(callable $callable)
	{
		$this->callable = $callable;
	}

	/**
	 * @return mixed
	 */
	public function process()
	{
		$this->result = call_user_func_array($this->callable, func_get_args());

		return $this->result;
	}

	/**
	 * @return mixed
	 */
	public function output()
	{
		return $this->result;
	}
}
