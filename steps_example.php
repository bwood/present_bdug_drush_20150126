<?php
/*
 * Global variables
 */
/*
$drush_path = exec('which drush');
$pantheon_aliases = $_SERVER['HOME'] . '/.drush/pantheon.aliases.drushrc.php';
$git = exec('which git');

if (!is_executable($drush_path)) {
  print "We found your drush at:\n$drush_path\n...but it's not executable.";
  print "Please fix that.\n";
  exit(1);
}
else {
  $drush = $drush_path . " --strict=0 ";
}

if ((!file_exists($pantheon_aliases)) || (!is_readable($pantheon_aliases))) {
  print "Error: $pantheon_aliases doesn't exist or isn't readable\n";
  exit(1);
}
*/

/*
// Check terminus 2.0 version
$terminus_version_cmd = "terminus cli version";
exec($terminus_version_cmd, $output, $result);
if ($result !== 0) {
  print "\nCouldn't find version of terminus.\nVerify that it's installed:\n";
  print "\thttps://github.com/pantheon-systems/cli/wiki/Installation\n\n";
  exit(1);
}
$parts = explode(' ', $output[0]);
$parts = explode('-', $parts[1]);
$parts = explode('.', $parts[0]);
if (($parts[0] < 0) || ($parts[1] < 3) || ($parts[2] < 4)) {
  print "Error: Terminus must be at version 0.3.4-beta or greater.\nI detected version " . $output[0] . "\n";
  exit(1);
}
unset($output);
unset($return);
 */

$usage = <<<EOT

USAGE:

php $argv[0] \

  -L                        # List steps
  -B number                 # Begin with this step number
  -E number                 # End after this step number
  -S 3,5,7                  # Specify non-contiguous steps to execute
                            # - steps will be sorted ascending
                            # - Can't be used with -B or -E

  -h                        # print help and exit

EOT;

$longopts = array();
$shortopts = "B:E:S:Lh";
$options = getopt($shortopts, $longopts);

if (in_array('h', array_keys($options))) {
  print $usage;
  exit(0);
}

$steps = array();
$functions = get_defined_functions();
foreach ($functions['user'] as $function) {
  if (strpos($function, 'step_') !== FALSE) {
    $steps[] = $function;
  }
}
asort($steps);

if (in_array('S', array_keys($options))) {
  if ((in_array('B', array_keys($options))) || (in_array('E', array_keys($options)))) {
    print "-S can't be used with -B nor -E.\n";
    print $usage;
    exit(1);
  }
  $user_steps = explode(",", $options['S']);
  foreach ($user_steps as $v) {
    if (($v < 10) && (strpos($v, "0") === FALSE)) {
      $v = "0$v";
    }
    $arbitrary_steps[] = 'step_' . trim($v);
  }
  $steps = array_intersect($steps, $arbitrary_steps);
}

if (array_key_exists('L', $options)) {
  $list = TRUE;
}
else {
  $list = FALSE;
}

if (array_key_exists('B', $options)) {
  $begin = $options['B'];
  if ($begin == 0 ) {
    print "-B0 is meaningless. Ignoring.\n";
  }
  if ($begin > count($steps)) {
    print "Error: -B ($begin) must be less than or equal to " . count($steps) . "\n";
    exit(1);
  }
  ($begin > 0) ? $begin-- : $begin = 0;
}
else {
  $begin = 0;
}

if (array_key_exists('E', $options)) {
  //is_numeric and <= array_pop($steps)
  $end = $options['E'];
  if ($end == 0 ) {
    print "-E0 is meaningless. Ignoring.\n";
  }
  if ($end > count($steps)) {
    print "Error: -E ($end) must be less than or equal to " . count($steps) . "\n";
    exit(1);
  }
  if (($end > 0) && ($end <= count($steps))) {
    $end = $end - count($steps);
  }
  else {
    $end = 0;
  }
}
else {
  $end = NULL;
}

$first_step = $begin + 1;
$last_step = count($steps) + $end;

if (($end !== NULL) && ($last_step < $first_step)) {
  print "Error: -E ($last_step) must be greater than or equal to -B ($first_step)\n";
}

$steps = array_slice($steps, $begin, $end);

// Step Functions

function step_01() {
  global $list, $step_output;
  $step_title = "*** " . __FUNCTION__ . " Description of step ***\n";
  if ($list) {
    print $step_title;
    return;
  }

  // Step process code

}

function step_02() {
  global $list, $step_output;
  $step_title = "*** " . __FUNCTION__ . " Description of step ***\n";
  if ($list) {
    print $step_title;
    return;
  }

  // Step process code

}

function step_03() {
  global $list, $step_output;
  $step_title = "*** " . __FUNCTION__ . " Description of step ***\n";
  if ($list) {
    print $step_title;
    return;
  }

  // Step process code

}

function step_04() {
  global $list, $step_output;
  $step_title = "*** " . __FUNCTION__ . " Description of step ***\n";
  if ($list) {
    print $step_title;
    return;
  }

  // Step process code

}

function step_05() {
  global $list, $step_output;
  $step_title = "*** " . __FUNCTION__ . " Description of step ***\n";
  if ($list) {
    print $step_title;
    return;
  }

  // Step process code

}

// Other Functions
  /*
   * Dual-purpose Yes/No function: continues/exits script (default) or returns boolean value
   *
   * @param $question string
   * @param $boolean boolean
   */
  function yesno($question, $boolean = FALSE) {
    $line = NULL;
    while ((strtolower(substr(trim($line), 0, 1)) != 'y') && (strtolower(substr(trim($line), 0, 1)) != 'n')) {
      if ($line !== NULL) {
        print "Please answer with \"y\" or \"n\"\n";
      }
      echo $question . " (y/n): ";
      $handle = fopen("php://stdin", "r");
      $line = fgets($handle);
    }
    if (strtolower(substr(trim($line), 0, 1)) != 'y') {
      echo "You said 'no'.\n";
      if ($boolean) {
        return FALSE;
      }
      else {
        exit(0);
      }
    }
    if ($boolean) {
      return TRUE;
    }
    else {
      echo "\nContinuing...\n";
    }
    return;
  }

  function take_input($question, $default = NULL) {
    (!empty($default)) ? $default_prompt = "[$default]" : $default_prompt = NULL;
    (!empty($default_prompt)) ? $question = $question . " $default_prompt: " : $question = $question . ": ";
    print wordwrap($question, 80);
    $handle = fopen("php://stdin", "r");
    $input = trim(fgets($handle));
    if (empty($input)) {
      if (!empty($default)) {
        return $default;
      }
    }
    return $input;
  }

/*
 * Execute steps
 */
foreach ($steps as $step) {
  $out = $step();
  if (is_array($out)) {
    $step_output = array_merge($step_output, $out);
  }
}
