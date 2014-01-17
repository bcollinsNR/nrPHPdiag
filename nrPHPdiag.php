<?php
if (extension_loaded('newrelic')) {
  echo 'Test API succesful';
  //newrelic_name_transaction('newTransaction');
  //newrelic_end_transaction();

  //newrelic_start_transaction('PHPDiagnosticTool');
}
// ******************************** New Relic ********************************
//
// PHP Agent Diagnostic Tool v 0.5 a
// Author: Brian Collins, Steven Minor
//
// ***************************************************************************
//
//    THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
//    EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
//    MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
//    NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
//    LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
//    OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
//    WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
//
// ***************************************************************************

// Report all PHP errors on this page
error_reporting(E_ALL);

//kiss
phpinfo();

// CURL Firewall check:
if (!function_exists('curl_init')){
  echo 'Sorry cURL is not installed!';
}
else 
{
  echo 'cURL installed.';
  echo '<br />';

  echo "Non-SSL:";
  echo '<br />';
  
  $ch = curl_init();
  // Set URL to download
  curl_setopt($ch, CURLOPT_URL, "http://collector.newrelic.com/status/mongrel");
  curl_setopt($ch, CURLOPT_HEADER, 0);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);    
  curl_setopt($ch, CURLOPT_TIMEOUT, 20);
  $output = curl_exec($ch);
  curl_close($ch);
  echo '.<br />';
  echo "SSL:";
  echo '<br />';

  $ch = curl_init();
  // Set URL to download
  curl_setopt($ch, CURLOPT_URL, "https://collector.newrelic.com/status/mongrel");
  curl_setopt($ch, CURLOPT_HEADER, 0);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);    
  curl_setopt($ch, CURLOPT_TIMEOUT, 20);
  $output = curl_exec($ch);
  curl_close($ch);

  echo '.<br />';
}

// Only get logs if localhost or
if ($_SERVER["HTTP_HOST"] == '127.0.0.1' || 'localhost') {

  $numberOfLines = '50'; //customer input? param could be simple way to implement this if this need to be customizable
  $tailCommand = 'tail -'.$numberOfLines;

  //TODO: Get the actual locations of the logs from config, for now assume default
  //$agentLogLocation = //get from phpinfo() array
  //$daemonLogLocation = //get from phpinfo() array
  $defaultAgentLogLocation = '/var/log/newrelic/php_agent.log';
  $defaultDaemonLogLocation = '/var/log/newrelic/newrelic-daemon.log';

  $agentLogLocation = $defaultAgentLogLocation;
  $daemonLogLocation = $defaultDaemonLogLocation;
  echo "<b>Agent Log (".$tailCommand.")</b>";
  echo "<br />";
  echo "<pre>";
  passthru($tailCommand.' '.$agentLogLocation);
  echo "</pre>";
  echo "<br />";echo "<br />";
  echo "<b>Daemon Log (".$tailCommand.")</b>";
  echo "<br />";
  echo "<pre>";
  passthru($tailCommand.' '.$daemonLogLocation);
  echo "</pre>";
}
else
{
  echo "Log access denied: non-local request detected.";
}

// Basic RUM check
  echo "<br /><script>
    document.write ('<br />==========================================<br /><b>RUM</b><br />');
    if (typeof NREUM != 'undefined') { 
      document.write ('</b>Yep.</b><br />==========================================<br />');
    }
    else 
      document.write ('<b>Nope.</b><br />==========================================<br />');</script>";

  // Basic request queueing header check
  echo '<br />==========================================<br />';
  echo '<b>Request Queuing</b>';
  echo '<br />';
  if (array_key_exists('HTTP_X_REQUEST_START', $_SERVER))
  {
    echo '<b>Yep: </b>';
    echo '$_SERVER["HTTP_X_REQUEST_START"] =' . $_SERVER["HTTP_X_REQUEST_START"];
    echo '<br />==========================================<br /><br /><br />';
  }
  else {
    echo '<b>Nope.</b><br />==========================================<br /><br /><br />';
  }

if (extension_loaded('newrelic')) {
  //newrelic_name_transaction('nameItAsLateAsPossible');
  //newrelic_end_transaction();
}
