<?php

namespace tomzx\Dataflow\Processor\Parallel;

use tomzx\Dataflow\Node;
use tomzx\Dataflow\Processor\Parallel\Threaded;

class Job extends Threaded {
	/**
	 * @var \tomzx\Dataflow\Node
	 */
	private $node;

	/**
	 * @var array
	 */
	private $arguments;

	/**
	 * @param \tomzx\Dataflow\Node $node
	 * @param array                $arguments
	 */
	public function __construct(Node $node, array $arguments = [])
	{
		$this->node = $node;
		$this->arguments = $arguments;
	}

	/**
	 * @return void
	 */
	public function run()
	{
		// var_dump($this->node, $this->arguments);
		// Must cast to array since it is a Volatile
		call_user_func_array([$this->node, 'process'], (array)$this->arguments);
	}
}
