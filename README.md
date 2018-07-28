# cowatch.class.php

Simple PHP class for timing / benchmarking sections of code with millisecond precision.

This was developed on Linux, but I see no reason that it shouldn't work on
other platforms. Please contact me, if the unit tests fails on your platform.

## Features

 - Automatically report code execution time to stdout as it completes.
 - Time any code block with ms precision.
 - Add triggers for slow code (max_ms) - allows for easy identification
   of bottlenecks once the code goes into production.
 - Very little overhead - record the time at the start and do simple calcs
   to get the runtime.

## Usage

    $bench = new cowatch('My code block');
    for ($i=0; $i<500; $i++)
       sys_getloadavg();
    $bench->end_watch(true);          // Stop timer and report runtime to stdout

See run_unit_tests() for example code. The class unit tests can be run from the
shell for a quick demonstration:

    /usr/bin/php cowatch.class.php
