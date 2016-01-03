<?php

namespace tests\tomzx\Dataflow;

use tomzx\Dataflow\Node;

class NodeTest extends \PHPUnit_Framework_TestCase {
	public function testProcess()
	{
		$node = new Node(function() { return 0; });
		$this->assertSame(0, $node->process());
	}

	public function testOutput()
	{
		$node = new Node(function() { return 0; });
		$this->assertSame(null, $node->output());
		$node->process();
		$this->assertSame(0, $node->output());
	}
}
