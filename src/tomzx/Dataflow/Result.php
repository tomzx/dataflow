<?php

namespace tomzx\Dataflow;

class Result {
	/**
	 * @var array
	 */
	private $nodes = [];

	/**
	 * @param array $nodes
	 */
	public function __construct(array $nodes)
	{
		$this->nodes = $nodes;
	}

	/**
	 * @param int|string $nodeIndex
	 * @return mixed|null
	 */
	public function output($nodeIndex)
	{
		if ( ! array_key_exists($nodeIndex, $this->nodes)) {
			return null;
		}

		return $this->nodes[$nodeIndex]->output();
	}
}
