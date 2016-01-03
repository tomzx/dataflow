<?php

namespace tests\tomzx\Dataflow;

use Mockery as m;
use tomzx\Dataflow\Result;

class ResultTest extends \PHPUnit_Framework_TestCase {

	public function tearDown()
	{
		parent::tearDown();

		m::close();
	}

	public function testOutputReturnNullIfInvalidNodeIndex()
	{
		$result = new Result([]);
		$this->assertNull($result->output(9000));
	}

	public function testOutput()
	{
		$node = m::mock(\tomzx\Dataflow\Node::class);

		$node->shouldReceive('output')->once()->andReturn(9000);

		$result = new Result([0 => $node]);
		$this->assertSame(9000, $result->output(0));
	}
}
