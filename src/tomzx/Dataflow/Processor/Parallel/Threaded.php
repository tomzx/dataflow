<?php

namespace tomzx\Dataflow\Processor\Parallel;

if (extension_loaded('pthreads')) {
	class Threaded extends \Threaded {

	}
} else {
	class Threaded {

	}
}
