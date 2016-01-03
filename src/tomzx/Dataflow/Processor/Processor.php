<?php

namespace tomzx\Dataflow\Processor;

use tomzx\Dataflow\Result;

abstract class Processor {
	/**
	 * Configuration given by the user.
	 *
	 * @var array
	 */
	protected $nodes = [];

	/**
	 * @param array $nodes
	 */
	public function initialize(array $nodes)
	{
		$this->nodes = $nodes;
	}

	/**
	 * @return \tomzx\Dataflow\Result
	 */
	public function process(array $arguments)
	{
		return new Result([]);
	}
}
