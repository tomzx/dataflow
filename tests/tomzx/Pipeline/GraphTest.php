<?php

namespace tests\tomzx\Dataflow;

use tomzx\Dataflow\Graph;
use tomzx\Dataflow\Processor\Sequential;

class GraphTest extends \PHPUnit_Framework_TestCase {
	public function testSimpleGraph()
	{
		$graph = [
			1 => [function($a) { return $a; }],
		];

		$graph = new Graph($graph, new Sequential());
		$this->assertNotNull($graph);

		$results = $graph->process('test');
		$this->assertSame('test', $results->output(1));
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testGraphWithNoElementInArrayShouldThrowAnException()
	{
		$graph = [
			1 => [],
		];

		new Graph($graph, new Sequential());
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testGraphWithFirstElementNotACallableShouldThrowAnException()
	{
		$graph = [
			1 => [null],
		];

		new Graph($graph, new Sequential());
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testGraphToAMissingNodeShouldThrowAnException()
	{
		$graph = [
			1 => [function() {}, 2],
		];

		new Graph($graph, new Sequential());
	}

	/**
	 * @expectedException \RuntimeException
	 */
	public function testGraphWithLoopOnSameNode()
	{
		$graph = [
			1 => [function() {}, 1],
		];

		new Graph($graph, new Sequential());
	}

	/**
	 * @expectedException \RuntimeException
	 */
	public function testGraphWithLoop()
	{
		$graph = [
			1 => [function() {}, 2],
			2 => [function() {}, 1],
		];

		new Graph($graph, new Sequential());
	}

	public function testGraphLinear()
	{
		$graph = [
			1 => [function($a) { return $a | 0x1; }],
			2 => [function($a) { return $a | 0x10; }, 1],
			3 => [function($a) { return $a | 0x100; }, 2],
		];

		$graph = new Graph($graph, new Sequential());
		$this->assertNotNull($graph);

		$results = $graph->process(0);
		$this->assertSame(0x001, $results->output(1));
		$this->assertSame(0x011, $results->output(2));
		$this->assertSame(0x111, $results->output(3));
	}

	public function testGraphTree()
	{
		$graph = [
			1 => [function($a) { return $a | 0x1; }],
			2 => [function($a) { return $a | 0x10; }, 1],
			3 => [function($a) { return $a | 0x100; }, 1],
			4 => [function($a) { return $a | 0x1000; }, 2],
			5 => [function($a) { return $a | 0x10000; }, 2],
			6 => [function($a) { return $a | 0x100000; }, 3],
			7 => [function($a) { return $a | 0x1000000; }, 3],
		];

		$graph = new Graph($graph, new Sequential());
		$this->assertNotNull($graph);

		$results = $graph->process(0);
		$this->assertSame(0x00000001, $results->output(1));
		$this->assertSame(0x00000011, $results->output(2));
		$this->assertSame(0x00000101, $results->output(3));
		$this->assertSame(0x00001011, $results->output(4));
		$this->assertSame(0x00010011, $results->output(5));
		$this->assertSame(0x00100101, $results->output(6));
		$this->assertSame(0x01000101, $results->output(7));
	}

	public function testGraphDiamond()
	{
		$graph = [
			1 => [function($a) { return $a | 0x1; }],
			2 => [function($a) { return $a | 0x10; }, 1],
			3 => [function($a) { return $a | 0x100; }, 1],
			4 => [function($a, $b) { return $a | $b | 0x1000; }, 2, 3],
		];

		$graph = new Graph($graph, new Sequential());
		$this->assertNotNull($graph);

		$results = $graph->process(0);
		$this->assertSame(0x0001, $results->output(1));
		$this->assertSame(0x0011, $results->output(2));
		$this->assertSame(0x0101, $results->output(3));
		$this->assertSame(0x1111, $results->output(4));
	}

	public function testGraphIndexesCanBeStrings()
	{
		$graph = [
			'A' => [function($a) { return $a | 0x1; }],
			'B' => [function($a) { return $a | 0x10; }, 'A'],
			'C' => [function($a) { return $a | 0x100; }, 'B'],
		];

		$graph = new Graph($graph, new Sequential());
		$this->assertNotNull($graph);

		$results = $graph->process(0);
		$this->assertSame(0x001, $results->output('A'));
		$this->assertSame(0x011, $results->output('B'));
		$this->assertSame(0x111, $results->output('C'));
	}
}
