# cowatch.class.php

Simple PHP class for timing / benchmarking sections of code with millisecond precision.

Features:
 - Automatically report code execution time to stdout as it completes.
 - Time any code block with ms precision.
 - Add triggers for slow code (max_ms) - allows for easy identification
   of bottlenecks once the code goes into production.
 - Very little overhead - record the time at the start and do simple calcs
   to get the runtime.

See run_unit_tests() for usage.
