<?php

namespace tomzx\Dataflow;

use tomzx\Dataflow\Processor\Processor;

class Graph {
	/**
	 * @param array                               $nodes
	 * @param \tomzx\Dataflow\Processor\Processor $processor
	 */
	public function __construct(array $nodes, Processor $processor)
	{
		$this->processor = $processor;

		$this->processor->initialize($nodes);
	}

	/**
	 * @return \tomzx\Dataflow\Result
	 */
	public function process()
	{
		return $this->processor->process(func_get_args());
	}
}
