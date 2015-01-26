<?php
/*
 * Global variables
 */

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

$usage = <<<EOT

USAGE:

php $argv[0] \

  Commonly used switches:
  -s pantheon-site-name     # REQUIRED (Unless -L is used.) Source site name.

  Step control switches:
  -L                        # List steps
  -B number                 # Begin with this step number
  -E number                 # End after this step number
  -S 3,5,7                  # Specify non-contiguous steps to execute
                            # - steps will be sorted ascending
                            # - Can't be used with -B or -E

  -h                        # print help and exit

EOT;

$longopts = array();
$shortopts = "B:E:S:Lhs:";
$options = getopt($shortopts, $longopts);

if (in_array('h', array_keys($options))) {
  print $usage;
  exit(0);
}

// Site Name
if (!in_array('L', array_keys($options))) {
  if (!in_array('s', array_keys($options))) {
    print "Required options missing:\n";
    print $usage . "\n";
    exit(1);
  }
  else {
    $source_site_name = $options['s'];
    $source_site_alias = '@pantheon.' . $source_site_name . '.dev';
    if (in_array('u', array_keys($options))) {
      $source_site_uuid = $options['u'];
    }
    else {
      // Get the site's UUID on Pantheon
      exec("$drush psite-uuid " . $source_site_name, $output, $return);
      $uuid_extracted = explode(': ', $output[0]); //Explode to temp variable first or array_pop may give pass by reference warning
      $source_site_uuid = array_pop($uuid_extracted);
      if ($source_site_uuid == "No uuid found.") {
        print "Error: No uuid found for $source_site_name\n";
        exit(1);
      }
      unset($output);
    }
  }
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
  // Sync live->dev
  global $list, $source_site_name;
  $step_title = "*** " . __FUNCTION__ . " Simple pattern for command execution ***\n";
  if ($list) {
    print $step_title;
    return;
  }
  else {
    print "\n" . $step_title . "\n";
  }

  $info_cmd = "terminus site info --site=" . $source_site_name;
  // I like to always print out the command to facilitate error analysis
  print $info_cmd . "\n";
  exec($info_cmd, $output, $return);
  if ($return != 0) {
    print "Error: Problem with last command.\n";
    exit(1);
  }
  // I like to always print command output
  print implode("\n", $output) . "\n";

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

function step_08() {
  global $list, $drush, $source_site_name, $source_site_uuid, $step_output;
  $step_title = "*** " . __FUNCTION__ . " Refresh your drush aliases ***\n";
  if ($list) {
    print $step_title;
    return;
  }
  else {
    print "\n" . $step_title . "\n";
  }

  //no love from drush paliases lately...
  $manual_download = TRUE;
  $manual_message = wordwrap("Unable to assist with downloading your drush aliases from Pantheon. Please download them by visiting the Pantheon dashbaord and hit Y when you've got them. (No aborts).", 80);

  if (!$manual_download) {
    $psite_aliases_cmd = "$drush paliases";
    exec($psite_aliases_cmd);
    return;
  }

  // get team for site which yields user's UUID
  $psite_team_cmd = "$drush psite-team " . $step_output['target_site_uuid'];
  exec($psite_team_cmd, $output, $return);
  preg_match("/([a-z0-9]+-[a-z0-9]+-[a-z0-9]+-[a-z0-9]+-[a-z0-9]+)/", $output[1], $matches);
  $user_uuid = $matches[1];
  if ($return != 0) {
    print "Can't find UUID for your Pantheon user.\n";
    yesno($manual_message);
  }
  unset($output);
  unset($return);

  // Open browser at download url
  $url_drush_aliases = "https://dashboard.getpantheon.com/users/$user_uuid/drush_aliases";

  $help_msg = <<<EOT

Note: In the save dialog, press "SHIFT+Command+."" to show/save hidden directories/files, which will allow you to save to ~/.drush/
Otherwise, after the file downloads you can move it with a command like:

  mv ~/Downloads/pantheon.aliases.drushrc.php ~/.drush/

(Assuming ~/Downloads is your browser's default download directory.)

EOT;

  print $help_msg;
  if (yesno("\nOpen the download url for your drush aliases?", TRUE)) {
    exec("open $url_drush_aliases");
  }
  else {
    print "Paste this url into your browser location bar:\n\t$url_drush_aliases\n";
  }

  yesno("Continue?");
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
