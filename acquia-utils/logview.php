<?php

# Acquia Cloud logview.php: A generic web interface for viewing
# local web logs.
#
# INSTALL
#
# Copy this script to anywhere under your site docroot folder,
# and you may want to password protect it using htaccess/htpasswd
#
# ARGUMENTS (sent as GET params)
#
# file: The name of the log file to send (not including path)
# op: What part of the file to send (head|tail|cat|range)
#     head: just the top N lines (default is 30 lines)
#     tail: just the bottom N lines (default is 30 lines)
#     cat: all of it
#     range: just send lines N to M (must pass a lines N,M arg)
#     menu: show HTML formatted list of local log files
# lines: How many lines to send. Can be an integer or a range 
#     of two integers in the form of N,M (start_line,stop_line)
# 
# No op argument: shows a plain text list of local log files.
#
# EXAMPLES
#
# Get a list of all local log files
#   curl -H "Host: example.com" 'http://web-1.example.acquia-sites.com/utils/logview.php'
#
# View the last 50 lines in error.log
#   curl -H "Host: example.com" 'http://web-1.example.acquia-sites.com/utils/logview.php?op=tail&lines=50&file=error.log'
#
# Download and save compressed log file
#   curl -H "Host: example.com" -o access.log-20091214.gz 'http://web-1.example.acquia-sites.com/utils/logview.php?op=cat&file=access.log-20091214.gz' 
#
# Download and decompress a compressed log file
#   curl -H "Host: example.com" 'http://web-1.example.acquia-sites.com/utils/logview.php?op=cat&file=access.log-20091214.gz' | gunzip -dc
#
# COPYRIGHT
#
# Copyright (c) 2009 Acquia. Permission granted to modify and redistribute 
# this file without restrictions.
#


/**
 * Read and verify inputs
 */
global $args; 
aq_read_input_args();
aq_set_default_args();
aq_verify_args();
global $site;
aq_get_site_paths();
aq_check_file_access();

/**
 * Output
 */
switch ($args['op']) {
  // head or tail the log
  case 'head':
  case 'tail':
    $lines = $args['lines'] ? "-n {$args['lines']}" : '-n 30'; # default head and tail to 30 lines
    aq_passthru("{$args['op']} $lines");
    break;
  // cat the entire log file
  case 'cat':
    aq_passthru('cat');
    break;
  // output a specific range of lines
  case 'range':
    aq_passthru("sed -un {$args['lines']},{$args['lines_stop']}p");
    break;
  // show a HTML menu of log files
  case 'menu':
    aq_show_main_menu();
    break;
  // show a plain list of local log files
  default:
    aq_show_logs_list_as_text();
    break;
}


/**
 * Read web inputs
 */
function aq_read_input_args() {
  global $args;
  $args = array('op' => '', 'file' => '', 'lines' => '', 'lines_stop' => '');
  $matches = array();
  // op: the requested operation
  if (isset($_GET['op'])) {
    preg_match('@(\w{2,10})@', $_GET['op'], $matches);
    $args['op'] = isset($matches[1]) ? $matches[1] : '';
  }
  // file: the log file name
  if (isset($_GET['file'])) {
    preg_match('@([\w\.\-]{3,40})@', $_GET['file'], $matches);
    $args['file'] = isset($matches[1]) ? $matches[1] : '';  
  }
  // lines: numbers of lines or a range of lines (start_at,stop_at)
  if (isset($_GET['lines'])) {
    preg_match('@(\d{1,20})(?:,(\d{0,20}))?@', $_GET['lines'], $matches);
    $args['lines'] = isset($matches[1]) ? $matches[1] : '';
    $args['lines_stop'] = isset($matches[2]) ? $matches[2] : '';
  }
}

/**
 * Set default args
 */
function aq_set_default_args() {
  global $args;
  // op: the requested operation
  if (!in_array($args['op'], array('head', 'tail', 'cat', 'range', 'menu'))) {
    $args['op'] = ''; # default to main menu
  }
  // file: the log file name
  if (!$args['file']) {
    $args['file'] = 'access.log';
  }
  // the file name extension
  $args['file_ext'] = array_pop(explode(".", $args['file']));
}

/**
 * Verify the arguments
 */
function aq_verify_args() {
  global $args;
  switch ($args['op']) {
    case 'head':
    case 'tail':
      if ($args['file_ext'] == 'gz') {
        aq_error("head and tail command is only available for uncompressed log files");
      }
      if ($args['lines_stop']) {
        aq_error("head and tail do not accept a range option. Use the range command instead.");
      }
      break;
    case 'cat':
      if ($args['lines']) {
        aq_error("cat does not accept a number of lines option. Use the head, tail, or range command instead.");
      }
      break;
    // output a range of lines
    case 'range':
      if ($args['file_ext'] == 'gz') {
        aq_error("The range command is only available for uncompressed log files");
      }
      if (!($args['lines'] && $args['lines_stop'])) {
        aq_error("range command requires a starting line number and a stop line number: start,stop");
      }
      if ($args['lines'] > $args['lines_stop']) {
        aq_error("The starting line number for the range has to be less than or equal to stop line number.");
      }
      break;
  }
}

/**
 * Determine the local log paths of this site
 */
function aq_get_site_paths() {
  global $site;
  $site = array();
  $site['docroot'] = $_SERVER["DOCUMENT_ROOT"];
  $matches = array();
  preg_match('@^/var/www/html/([\w\.]{2,40})@', $site['docroot'], $matches);
  $site['name'] = isset($matches[1]) ? $matches[1] : '';
  if (!($site['name'] && $site['docroot'])) {
    aq_error("Could not determine the name of this site.");
  }
  $hostname = `hostname`;
  preg_match('@^([\w\-]{2,30})@', $hostname, $matches);
  $site['host'] = isset($matches[1]) ? $matches[1] : '';
  $site['logdir'] = "/var/log/sites/{$site['name']}/logs/{$site['host']}";
  if (!is_dir($site['logdir'])) {
    header('HTTP/1.1 404 Not Found');
    aq_error("Could not find a local web logs directory for this site.");
  }
}


/**
 * Check read access for the log file to be read
 */
function aq_check_file_access() {
  global $args;
  global $site;
  // no log file or command specified
  if (!($args['op'] && $args['file'])) {
    return FALSE;
  }
  // check that file exists
  if (!is_file("{$site['logdir']}/{$args['file']}")) {
    header('HTTP/1.1 404 Not Found');
    aq_error("<h1>Not Found</h1>The file {$site['logdir']}/{$args['file']} was not found on this server.");
    return FALSE;
  }  
  // check file read access
  if ($fh = @fopen("{$site['logdir']}/{$args['file']}", 'r')) {
    fclose($fh);
    return TRUE;
  }
  // Can't read the file
  header('HTTP/1.1 403 Forbidden');
  aq_error("<h1>Forbidden</h1>The file {$site['logdir']}/{$args['file']} is not readable to this process.");
}

/**
 * Show error and exit
 */
function aq_error($error="logview could not process this request.") {
  print "LOGVIEW ERROR: $error";
  exit();
}

/**
 * Pipe the raw file IO directly back to client
 */
function aq_passthru($op) {
  global $args;
  global $site;
  if ($args['file_ext'] == 'gz') {
    header("Content-Type: application/x-gzip");
  }
  else {
    header("Content-Type: text/plain");
  }
  passthru("$op {$site['logdir']}/{$args['file']}"); 
}

/**
 * Show a fancy HTML main and files list
 */
function aq_show_main_menu() {
  global $site;
  print "<html>
<head>
<style>
* { 
  font-family: sans-serif;
  font-size: 10pt;
  line-height: 1.3;
}
td {
  padding-left: 1em;
  white-space: nowrap;
}
</style>
</head>
<body>
<div style='width:50%;margin-left:25%;'>
<strong>Acquia Cloud: web server logs for {$site['name']} on {$site['host']}</strong><br/>
  {$site['logdir']}";
  aq_show_logs_list_as_html();
  print "
</div>
</body>
</html>
";
}

/**
 * Render an HTML formatted list of log files
 */
function aq_show_logs_list_as_html() {
  global $site;
  $file_list = '';
  aq_get_log_files_list();
  foreach ($site['log_files'] as $file => $file_props) {
    $file_list .= '<tr>';
    // Offer range downloads for uncompressed log files
    if ($file_props['ext'] != 'gz' && $file_props['size']) {
      $file_list .= "<td><a href=\"?op=head&file=$file\">head</a></td>
      <td><a href=\"?op=tail&file=$file\">tail</a></td>
      <td><a href=\"?op=range&lines=10,20&file=$file\">lines 10-20</a></td>";
    }
    else {
      $file_list .= "<td colspan='3'></td>";
    }
    // file size
    $file_list .= "<td style='text-align:right;'>{$file_props['size']}</td>";
    // file name
    $file_list .= "<td><a href=\"?op=cat&file=$file\">$file</a></td>";
    $file_list .= '</tr>';
  }
  print "<table> $file_list </table>";
}

/**
 * Render a plain text list of log files
 */
function aq_show_logs_list_as_text() {
  global $site;
  $file_list = '';
  aq_get_log_files_list();
  header("Content-Type: text/plain");
  foreach ($site['log_files'] as $file => $file_props) {
    print "$file\n";
  }
}


/**
 * Get list of local log files
 */
function aq_get_log_files_list() {
  global $site;
  $site['log_files'] = array();
  if (! ($dh = opendir($site['logdir']))) {
    aq_error("Could not read web logs directory for this site.");
  }
  while (($file = readdir($dh)) !== false) {
    // list only regular files:
    if (!is_file("{$site['logdir']}/$file")) {
      continue;
    }
    $site['log_files'][$file] = array();
    $site['log_files'][$file]['ext'] = array_pop(explode(".", $file));
    $site['log_files'][$file]['size'] = number_format(filesize("{$site['logdir']}/$file"));    
  }
  closedir($dh);
  if (empty($site['log_files'])) {
    aq_error("No files found in {$site['logdir']}.");
  }
}

?>
