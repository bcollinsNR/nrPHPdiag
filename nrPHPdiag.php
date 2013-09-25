<?php

// ******************************** New Relic ********************************
//
// PHP Agent Diagnostic Tool v 0.3.3
// Author: Tony Mayse, Brian Collins, Steven Minor
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

// Report all PHP errors
error_reporting(E_ALL);

// Get phpinfo() into array
ob_start();
phpinfo();
$phpinfo = array('phpinfo' => array());
if(preg_match_all('#(?:<h2>(?:<a name=".*?">)?(.*?)(?:</a>)?</h2>)|(?:<tr(?: class=".*?")?><t[hd](?: class=".*?")?>(.*?)\s*</t[hd]>(?:<t[hd](?: class=".*?")?>(.*?)\s*</t[hd]>(?:<t[hd](?: class=".*?")?>(.*?)\s*</t[hd]>)?)?</tr>)#s', ob_get_clean(), $matches, PREG_SET_ORDER))
  foreach($matches as $match)
    if(strlen($match[1]))
        $phpinfo[$match[1]] = array();
      elseif(isset($match[3]))
        $phpinfo[end(array_keys($phpinfo))][$match[2]] = isset($match[4]) ? array($match[3], $match[4]) : $match[3];
      else
        $phpinfo[end(array_keys($phpinfo))][] = $match[2];

// Output diagnostic data
print "<pre>";

// Basic table of phpinfo() data


echo '<table>';
foreach ($phpinfo as $value1) {
	foreach ($value1 as $key2 => $value2) {
	    if (is_array($value2)) //gets local / active & master
	    {
	    	foreach ($value2 as $key3 => $value3) {
	    		echo '<tr><td>'.$key3.'</td><td>'.$value3.'</td></tr>';
	    	}
	    }  
	    else
	    {
        	echo '<tr><td>'.$key2.'</td><td>'.$value2.'</td></tr>';
        }
	}
}
echo '</table>';

//print_r($phpinfo);

//TODO: Only get what we need:
//print_r($phpinfo['phpinfo']); //TODO: don't display $phpinfo['phpinfo'][0], or $phpinfo['phpinfo'][1]...
//print_r($phpinfo['Apache Environment']);
//print_r($phpinfo['PHP Variables']); //TODO: don't display $phpinfo['PHP Variables']['_SERVER["argv"]']
//print_r($phpinfo['<newrelic>']); //TODO: what is this name set to? 
// there may be a few more sections we need, ask support team what they use

//TODO: Steven is going to work on live logging

$numberOfLines = '2'; //customer input? param could be simple way to implement...
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
passthru($tailCommand.' '.$agentLogLocation);
echo "<br />";echo "<br />";
echo "<b>Daemon Log (".$tailCommand.")</b>";
echo "<br />";
passthru($tailCommand.' '.$daemonLogLocation);

//basic rum check
echo "<br /><script>if(typeof NREUMQ != 'undefined')
  document.write ('<b>RUM</b>'); //TODO: also output the var...
else 
  document.write ('<b>No RUM</b>');</script>";
print "</pre>";
?>