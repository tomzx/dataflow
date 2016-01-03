<?php

namespace tomzx\Dataflow;

use tomzx\Dataflow\Processor\Sequential;

class Pipeline {
	/**
	 * @var array
	 */
	private $stages = [];

	/**
	 * @param array $stages
	 */
	public function __construct(array $stages = [])
	{
		$this->stages = $stages;
	}

	/**
	 * @param callable $stage
	 * @return static
	 */
	public function pipe(callable $stage)
	{
		$stages = $this->stages;
		if ( ! empty($stages)) {
			end($this->stages);
			$lastStageIndex = key($this->stages);
			reset($this->stages);
			$stages[] = [$stage, $lastStageIndex];
		} else {
			$stages[] = [$stage];
		}

		return new static($stages);
	}

	/**
	 * @param mixed|null $payload
	 * @return mixed|null
	 */
	public function process($payload = null)
	{
		if (empty($this->stages)) {
			return null;
		}

		// Call all stages which depend only on the input
		$graph = new Graph($this->stages, new Sequential());

		$results = $graph->process($payload);

		end($this->stages);
		$lastStageIndex = key($this->stages);
		reset($this->stages);

		return $results->output($lastStageIndex);
	}

	/**
	 * @param mixed $payload
	 */
	public function __invoke($payload)
	{
		$this->process($payload);
	}
}
