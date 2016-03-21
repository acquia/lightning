#!/usr/bin/perl

=head1 logviewer.pl

  logviewer.pl - a shell script to aggregate logs from across many web servers

  Usage:  ./logviewer.pl -f php-errors.log -c tail -n example.com -s web-1 web-2 web-3

=head2 DESCRIPTION
  
  This shell script assists with gathering server logs for a partilcar site
  on Acquia Hosting.
  
=head2 SETUP

  1. Check out your Acquia Hosting site repository using SVN
  
  2. Install the acquia-utils/logview.php script on your Acquia Hosting site 
  per its instructions
  
  3. Visit the Hosting tab of your Acquia Network subscription, and note the 
  list of web servers currenly serving your Acquia Hosting site.
  
  4. Run this script from your local desktop to reach out to each web server
  and pull your logs from each server.
  
=head2 OPTIONS

  See the usage message (run this script with -h option for help.)

=cut

use strict;

my @servers = ();     # list of web servers
my $command = '';     # the logview command sent to each server
my $sitename = '';    # the name of the acquia hosting site
my $logview_uri = ''; # relative path of logview.php
my $file = '';        # the log file being requested
my $lines = '';       # the range of lines being requested
my @urls = ();        # list of request urls
my $agent = '';       # the command line util to make the request (wget or curl)
my $debug = 0;        # set anything truthy to see debug output

# Check for command-line args
unless ($ARGV[0]) {
  &brief_usage();
  exit(1);
}

# Parse command-line args
while (my $arg = shift @ARGV) {
  processArg($arg);
}

&setDefaultArgs();
&verfifyArgs();
&verifyUserAgent();
&makeRequestUrls();
&makeRequests();


=head2 processArg()

  process the given command line argument.

=cut
sub processArg {
  my ($arg) = @_;

  print "DEBUG: arg: $arg\n" if $debug;
  
  # agent
  if ($arg eq '-a') {
    $agent = shift @ARGV;
    return;
  }
  
  # command
  if ($arg eq '-c') {
    $command = shift @ARGV;
    return;
  }
    
  # debug
  if ($arg eq '-d') {
    $debug = 1;
    return;
  }
  
  # file  
  if ($arg eq '-f') {
    $file = shift @ARGV;
    return;
  }

  # help
  if ($arg eq '-h') {
    usage();
  }
  
  # lines
  if ($arg eq '-l') {
    $lines = shift @ARGV;
    return;
  }
  
  # logview_uri
  if ($arg eq '-u') {
    $logview_uri = shift @ARGV;
    return;
  }
  
  # site name
  if ($arg eq '-n') {
    $sitename = shift @ARGV;
    print "DEBUG: sitename: $sitename\n" if $debug;
    return;
  }

  # web servers: a space-delimited list of servers
  if ($arg eq '-s') {
    while ($arg = shift @ARGV) {
      if ($arg =~ /^\-/) {
        return processArg($arg);
      }
      push(@servers, $arg);
    }
    return;
  }
  
  # unknown arg
  print "ERROR: Unknown argument: $arg";
  &brief_usage();
  exit(1);
}


=head2 setDefaultArgs()

  set default values for unspecified arguments

=cut
sub setDefaultArgs {
  $file ||= 'access.log';
  $command ||= 'tail';
  $logview_uri ||= 'ahutils/logview.php';
}



=head2 verfifyArgs

  Verify each command line argument

=cut
sub verfifyArgs {
  
  # file
  unless ($file =~ /^[\w\-\.]{3,40}$/) {
    print "ERROR: Invalid file argument: $file\n";
    exit(1);
  }
  
  # command
  unless ($command =~ /^\w{2,10}$/) {
    print "ERROR: Invalid command argument: $command\n";
    exit(1);
  }
  
  # siet name
  unless ($sitename =~ /^[\w\-\.]{1,60}$/) {
    &brief_usage();
    print "ERROR: No site name specified or invalid site name.\n";
    exit(1);
  }

  # lines
  if ($lines && $lines !~ /^[\d\,]{1,40}$/) {
    print "ERROR: Invalid range argument: $lines\n";
    exit(1);
  }
    
  # logview.php uri path
  if ($logview_uri && $logview_uri !~ /^[\w\-\.\/]{2,60}$/) {
    print "ERROR: Invalid logview uri path argument: $logview_uri\n";
    exit(1);
  }
  
  # servers
  unless (@servers) {
    print "ERROR: No servers specified.\n";
    exit(1);
  }
  foreach my $server (@servers) {
    unless ($server =~ /^[\w\-]{2,60}/) {
      print "ERROR: Invalid server argument: $server\n";
      exit(1);
    }
  }
}


=head2 verifyUserAgent() 

  Test ability to run curl or wget

=cut
sub verifyUserAgent {
  # If an agent was given then test it
  if ($agent) {
    if (testAgent($agent)) {
      return;
    } 
    else {
      print "ERROR: web user agent $agent could not be verified.\n";
      exit(1);
    }
  }
  # Check each default user agent until one of them appears to be installed.
  if ($agent = testAgent('curl')) {
    return;
  }
  elsif ($agent = testAgent('wget')) {
    return;
  }
  print "ERROR: Can't determine which web browser agent to use. Please install either wget or curl in this shell environment.";
  exit(1);
}

=head2 testAgent()

  Simple test to see if the user agent command can be run at all.
  
=cut
sub testAgent {
  my ($agent) = @_;
  print "\nDEBUG: Trying user agent $agent ... " if $debug;
  my $output = `which $agent`;
  if ($?) {
    print "Failed: $! $output" if $debug;
    return;
  }
  print "OK. Using $agent.\n" if $debug;
  return $agent;
}


=head2 makeRequests()

  Request each url and display the result

=cut
sub makeRequests {
  foreach my $url (@urls) {
    print "DEBUG: Requesting $url ...\n" if $debug;
    # Command-line args per user agent:
    my $agent_opts = {
      'curl' => '-H ',
      'wget' => '-qO- --header='
    };
    my $cmd = "$agent $agent_opts->{$agent}'Host: $sitename' '$url'";
    print "DEBUG: shell command: $cmd\n" if $debug;
    print STDERR "\nGetting $url ...\n";
    system "$cmd"
  }
}


=head2 makeRequestUrls()

  Construct a list of urls to be requested

=cut
sub makeRequestUrls {
  foreach my $server (@servers) {
    # expand server name to its fqdn if not specified
    if ($server =~ /^([\w\-]{2,60})$/) {
      $server .= '.prod.hosting.acquia.com';
    }
    my $url = "http://$server/$logview_uri?op=$command&file=$file";
    $url .= "&lines=$lines" if $lines;
    push(@urls, $url);
  }
}


sub brief_usage {
  print qq|usage: ./logviewer.pl -n example.com -u /utils/lv.php -f php-errors.log -c cat -s web-2 web-8 web-11
Use "./logviewer.pl -h" for full documentation.

|;
}

=head2 usage()

  print the help message and exit.

=cut
sub usage {
  print qq|
  logviewer.pl - Aggregate Acquia Hosting server logs.
  
  Example: 
  
  ./logviewer.pl -n example.com -u /utils/lv.php -f php-errors.log -c cat -s web-2 web-8 web-11
  
  For the actual command arguments that work with your site refer to the 
  Hosting section of your Acquia Network subscription on acquia.com.
  
  Setup:
  
  This script communicates with the logview.php script on your Acquia Hosting 
  site. If you have not setup the logview.php script on your Acquia Hosting site 
  yet, then you can do so by copying the logview.php script from your svn repo 
  from /trunk/acquia-utils/ into your site docroot then deploying it using svn. 
  After deploying logview.php you can then use this script locally to easily 
  download log data from logview.php across many servers.
  
  Options:
  
  -n domain_name:   (Required) The domain name of your web site. 
  
  -s server1 server2, ...   (Required) A space-separated list of  
          Acquia Hosting web servers serving your site. 
          
  -u uri_path: The relative uri path of the logview.php script installed 
          on your Acquia Hosting site. (Default: /ahutils/logview.php)
  
  -f file:  (Optional) Which server log file to read. Can be one of access.log, 
          error.log, or php-errors.log (Optional: Defaults to access.log if 
          not specified)
  
  -c command: (Optional) Which part of the log file to download. Can be one
          of these:

          head: the top 30 lines of the file
          tail: (Default) the bottom 30 lines of the file
          cat: the entire file
          range: lines start,stop (requires the lines argument be specified)
          
  -l lines: (Optional) How many lines (a number), or a range of lines when 
          using the range command in the form of number,number 
  
  -a agent: (Optional) Which shell-based web agent to use (curl or wget. Default
          is curl)
          
  -d:     (Optional) Show debug messages.
  
  -h:     Help. Shows this message.  

|;
  exit(0);
}
