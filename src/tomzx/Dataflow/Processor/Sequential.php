<?php

namespace tomzx\Dataflow\Processor;

use InvalidArgumentException;
use RuntimeException;
use tomzx\Dataflow\Node;
use tomzx\Dataflow\Result;

class Sequential extends Processor {
	/**
	 * Node containing the callable and its result after an iteration of call to process().
	 *
	 * @var array
	 */
	private $processingNodes = [];

	/**
	 * The iteration at which they can be processed (useful for parallelization).
	 *
	 * @var array
	 */
	private $processIteration = [];

	/**
	 * @param array $nodes
	 */
	public function initialize(array $nodes)
	{
		parent::initialize($nodes);

		$this->validateNodes();
		$this->buildProcessingNodes();
		$this->prepareForProcessing();
	}

	/**
	 * @return void
	 */
	private function validateNodes()
	{
		foreach ($this->nodes as $nodeIndex => $nodes) {
			if (empty($nodes)) {
				throw new InvalidArgumentException('A node should have at least a callable function');
			}

			$callable = $nodes[0];
			if ( ! is_callable($callable)) {
				throw new InvalidArgumentException('Node index = ' . $nodeIndex . '. Expected type callable as first array value, but got type ' . gettype($callable) . '.');
			}
		}

		$identifiers = array_keys($this->nodes);
		foreach ($this->nodes as $nodeIndex => $nodes) {
			$testedNodes = array_slice($nodes, 1);
			$missingNodes = array_diff($testedNodes, $identifiers);
			if ( ! empty($missingNodes)) {
				throw new InvalidArgumentException('Node index = ' . $nodeIndex . '. Nodes [' . implode(', ', $missingNodes) . '] do not exist. Cannot create a graph to missing nodes.');
			}
		}
	}

	/**
	 * @return void
	 */
	private function buildProcessingNodes()
	{
		foreach ($this->nodes as $nodeIndex => $nodes) {
			$callable = $nodes[0];
			$this->processingNodes[$nodeIndex] = new Node($callable);
		}
	}

	/**
	 * @return void
	 */
	private function prepareForProcessing()
	{
		$dependents = [];
		$dependencies = [];
		$dependencyCount = [];
		foreach ($this->nodes as $nodeIndex => $nodes) {
			if ( ! array_key_exists($nodeIndex, $dependents)) {
				$dependents[$nodeIndex] = [];
			}

			$nodeDependencies = array_splice($nodes, 1);
			foreach ($nodeDependencies as $dependency) {
				$dependents[$dependency][] = $nodeIndex;
			}
			$dependencies[$nodeIndex] = array_flip($nodeDependencies);
			$dependencyCount[$nodeIndex] = count($nodeDependencies);
		}

		while ( ! empty($dependencyCount)) {
			// Order the nodes by their number of unresolved dependencies so that we may
			// indicate that the nodes which have no unresolved dependencies left are ready to be processed.
			asort($dependencyCount);

			if (reset($dependencyCount) !== 0) {
				throw new RuntimeException('Cannot create a sequence of execution for this graph.');
			}

			$iteration =& $this->processIteration[];
			foreach ($dependencyCount as $nodeIndex => $count) {
				if ($count !== 0) {
					break;
				}

				assert(empty($dependencies[$nodeIndex]));
				assert($dependencyCount[$nodeIndex] === 0);

				$iteration[] = $nodeIndex;

				unset($dependencies[$nodeIndex]);
				unset($dependencyCount[$nodeIndex]);
				foreach ($dependents[$nodeIndex] as $dependent) {
					unset($dependencies[$dependent][$nodeIndex]);
					--$dependencyCount[$dependent];
				}
				unset($dependents[$nodeIndex]);
			}
		}
	}

	/**
	 * @param array $arguments
	 * @return \tomzx\Dataflow\Result
	 */
	public function process(array $arguments)
	{
		/** @var \tomzx\Dataflow\Node $node */
		foreach ($this->processIteration as $nodes) {
			foreach ($nodes as $nodeIndex) {
				$node = $this->processingNodes[$nodeIndex];
				$arguments = $this->getArguments($nodeIndex) ?: $arguments;
				call_user_func_array([$node, 'process'], $arguments);
			}
		}

		return new Result($this->processingNodes);
	}

	/**
	 * @param int|string $nodeIndex
	 * @return array
	 */
	private function getArguments($nodeIndex)
	{
		$argumentsIndex = array_slice($this->nodes[$nodeIndex], 1);
		$arguments = [];
		foreach ($argumentsIndex as $nodeIndex) {
			$arguments[] = $this->processingNodes[$nodeIndex]->output();
		}
		return $arguments;
	}
}
