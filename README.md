# Dataflow

[![License](https://poser.pugx.org/tomzx/dataflow/license.svg)](https://packagist.org/packages/tomzx/dataflow)
[![Latest Stable Version](https://poser.pugx.org/tomzx/dataflow/v/stable.svg)](https://packagist.org/packages/tomzx/dataflow)
[![Latest Unstable Version](https://poser.pugx.org/tomzx/dataflow/v/unstable.svg)](https://packagist.org/packages/tomzx/dataflow)
[![Build Status](https://img.shields.io/travis/tomzx/dataflow.svg)](https://travis-ci.org/tomzx/dataflow)
[![Code Quality](https://img.shields.io/scrutinizer/g/tomzx/dataflow.svg)](https://scrutinizer-ci.com/g/tomzx/dataflow/code-structure)
[![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/tomzx/dataflow.svg)](https://scrutinizer-ci.com/g/tomzx/dataflow)
[![Total Downloads](https://img.shields.io/packagist/dt/tomzx/dataflow.svg)](https://packagist.org/packages/tomzx/dataflow)

Create simple pipelines as well as complex graphs which may be used to process data or compute only when a stage's dependencies are resolved.

## Getting started

### A simple pipeline (sequential)

```
$a = function($payload) {
	return $payload * -1;
};

$b = function($payload) {
	return $payload * 10000;
};

$pipeline = (new Pipeline)
	->pipe($a)
	->pipe($b);

// Returns 100000
$pipeline->process(-10);

// Can also be built as the following

$stages = [
	1 => [$a],
	2 => [$b, 1],
];

$pipeline = new Pipeline($stages);
```

### A graph flow

```
$p1 = function($payload) {
	return $payload . '1';
};

$p2 = function($payloadP1) {
	return $payloadP1 . '2';
};

$p3 = function($payloadP1) {
	return $payloadP1 . '3';
};

$p4 = function($payloadP1) {
	return $payloadP1 . '4';
};

$p5 = function($payloadP2, $payloadP3) {
	return $payloadP2 . ' ' . $payloadP3 . '5';
};

$p6 = function($payloadP5) {
	return $payloadP5 . '6';
};

$stages = [
	1 => [$p1],
	2 => [$p2, 1],
	3 => [$p3, 1],
	4 => [$p4, 1],
	5 => [$p5, 2, 3],
	6 => [$p6, 5],
];

$pipeline = new Pipeline($stages);

// Returns Test 12 Test 1356
$pipeline->process('Test ');

// May also be built using the Graph class

$graph = new Graph($stages, new Sequential);
// or $graph = new Graph($stages, new Parallel);

$results = $graph->process('Test ');

// You may inspect the output of every node
// Returns Test 1
$result->output(1);
// Returns Test 12
$result->output(2);
// Returns Test 13
$result->output(3);
// Returns Test 14
$result->output(4);
// Returns Test 12 Test 135
$result->output(5);
// Returns Test 12 Test 1356
$result->output(6);
```

## Processors

In order to process your graph, you are provided with two processors: a `Sequential` processor and a `Parallel` processor.

The `Sequential` processor takes a graph and turns it into a sequence of operations to accomplish. It basically will compute the graph dependencies and ensure that those dependencies are resolved by the time each stage is to be executed. With the `Sequential` processor, only a single task may be executed at a time.

The `Parallel` processor is useful when many tasks can be executed in parallel. Similar to the `Sequential` processor, it will validate that the given graph can be resolved. Then, it will execute all the tasks that can be executed in parallel at the same time. On a multi-core processor, it is expected that the `Parallel` processor will complete faster if it is given parallelizable tasks.

## License

The code is licensed under the [MIT license](http://choosealicense.com/licenses/mit/). See [LICENSE](LICENSE).
