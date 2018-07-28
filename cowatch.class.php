<?php
// ------------------------------------------------------------------------
//
// cowatch.class.php - Simple PHP class to time sections of code.
//
// Features (list dups README.md):
//  - Automatically report code execution time to stdout as it completes.
//  - Time any code block with ms precision.
//  - Add triggers for slow code (max_ms) - allows for easy identification
//    of bottlenecks once the code goes into production.
//  - Very little overhead - grab the time at the start and do simple calcs
//    to get the runtime.
// 
// See run_unit_tests() for usage.
//
// ToDo
//  - Optional HTML string return instead of echo to stderr - for webapps.
//     - Or a callback function that allows the app to log slow code.
// 
// Copyright Stig H. Jacobsen 2013-2018.
// This code is free to use for everyone and anything.
//
// 25-Aug-2013/shj
//
// ------------------------------------------------------------------------


// If invoked from the shell as stand-alone script, then run the unit tests
if (realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME'])) {
   cowatch::run_unit_tests();
   exit();
}


// ------------------------------------------------------------------------

class cowatch {

   protected
      $start_tm,
      $title,
      $runtime_ms = -1,       // set by end_watch(), >=0 means that watch has ended
      $max_ms;

   // If max is >0, then echo warning for slow code
   function __construct($title, $max_runtime_ms = 0) {
      $this->title = $title;
      $this->start_tm = microtime(true);
      $this->max_ms = $max_runtime_ms;
   }

   function __destruct() {
      $this->end_watch();
   }

   // Return runtime sofar or recorded runtime if end_watch() has been called.
   function get_ms() {
      if ($this->runtime_ms >= 0)      //end_watch() called yet?
         return $this->runtime_ms;
      else                             //timing still running
         return intval((microtime(true) - $this->start_tm) * 1000);
   }

   // Does not return anything, call get_ms() to get recorded timing afterwards.
   function end_watch($echo_timings = false) {

      if ($this->runtime_ms >= 0)     // end_watch() already called?
         return;

      $this->runtime_ms = $this->get_ms();

      if ($this->max_ms && $this->runtime_ms > $this->max_ms) {
         $msg = sprintf('%s: Slow code took %d ms to run - max = %d ms (%s.php)',
                        $this->title, $this->runtime_ms, $this->max_ms, __CLASS__);
         error_log($msg);                                // Message to stderr
         // $log = new mylogger();
         // $log->write('notice', $msg);
      }

      if ($echo_timings)
         echo empty($this->title)?__CLASS__:$this->title . ': Runtime ', $this->timings_str(), "\n";

   } // end_watch()

   function timings_str() {

      $ms = $this->get_ms();
      $secs = intval($ms / 1000);

      if ($secs < 1)                         // 408 ms
         $s = sprintf('%d ms', $ms);
      elseif ($secs < 10)
         $s = sprintf('%.1f secs', $secs);   // 3.9 secs
      elseif ($secs >= 60*60) {              // 2h17m
         $s = sprintf('%dh', $secs/(60*60));
         if (($m = $secs % (60*60)) > 0)
            $s .= intval($m).'m';
      } elseif ($secs >= 60) {               // 3m22s
         $s = sprintf('%dm', $secs/60);
         if ($secs%60 != 0)
            $s .= intval($secs%60).'s';
      } else
         $s = sprintf('%d secs', $secs);     // 28 secs, the default

      return $s;

   } //timings_str()


   // --- Unit testing ----------------------------------------------------

   static function run_unit_tests() {

      echo ($class = __CLASS__) . " class: Unit tests starting\n";

      { // Test 1
         $bench = new $class('Unit test #1', 10);

         // Do something to consume time before ending watch
         for ($i=0; $i < 500; $i++) {
            sys_getloadavg();
            if ($i % 100 == 0)
               echo "Test-1 runtime sofar ", $bench->get_ms(), " milliseconds\n";
         }

         $bench->end_watch(true);          // 'true' argument causes end_watch() to report timings to stdout
      }

      { // Test 2 - this will run silently unless your system needs more than 20ms to execute it
         $wo = new $class('Unit test #2', 20);
         for ($i=0; $i < 500; $i++)
            sys_getloadavg();
         $wo->end_watch();
         echo "(unit test #2 silently done, maybe)\n";
      }

      { // Test 3 - which is faster, intval or floor?
         echo "intval() versus floor() - make your bets\n";
         $iterations = 1000*5000;
         $value = rand() / 1000;

         $t3 = new $class('Unit test #3.1 - intval()');
         for ($i=0; $i<$iterations; $i++)
            $x = intval($value);
         $t3->end_watch(true);

         $t3 = new $class('Unit test #3.2 - floor()');
         for ($i=0; $i<$iterations; $i++)
            $x = floor($value);
         $t3->end_watch(true);
      }

      echo __CLASS__ . " class: All unit tests completed, successfully or not\n";

   } //run_unit_tests


} //class cowatch


// vim:ts=3:sw=3:sts=1:
?>
