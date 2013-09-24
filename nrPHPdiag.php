<?php

// ******************************** New Relic ********************************
//
// PHP Agent Diagnostic Tool v 0.3.2
// Author: Tony Mayse 
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

// GLOBAL VARIABLES (so shoot me)
$nrResult = "";
$nrMessages = "";
$nrInitialAppName = ini_get('newrelic.appname'); // saving it since it's about to change

// record all metrics as "New Relic PHP Diagnostic"
newrelic_set_appname("New Relic PHP Diagnostic");

// CONSTANTS

define("nrDebug",						FALSE);
define("nrDefaultDaemonPort",			33142);
define("nrDefaultDaemonConfigFile",		"/etc/newrelic/newrelic.cfg");
define("nrDefaultDaemonLogFileDefault",	"/var/log/newrelic/newrelic-daemon.log");
define("nrDefaultAgentLogFile",			"/var/log/newrelic/php_agent.log");
define("nrPHPdiagVer",					"0.3.2");
define("nrPHPdiagDir",					"/tmp/nrPHPdiag/");
define("nrLogFile",     				nrPHPdiagDir."nrPHPdiag.log");
define("nrDiagFile",     				nrPHPdiagDir."nrPHPdiagFiles.tar.gz");

// FUNCTIONS

if(nrDebug) echo "creating functions ";

if(nrDebug) echo "nrCSS";

// return inline CSS
function nrCSS(){
	$nrCSS = "";
	// add some CSS output here
	return($nrCSS);
}

// exercise application
// this is in a function so New Relic
// will trace it automatically
function exercise($delay){
	usleep($delay);
	// add random errors and external connections
	return;
}

if(nrDebug) echo "timestamp ";

// return constructed human-readable timestamp
function timestamp(){
	if(nrDebug) echo "getting date ";
	$theTime = getdate(); // need to update to ensure GMT

	$timestampNow .= $theTime[weekday]." ";
	$timestampNow .= $theTime[month]." ";
	$timestampNow .= $theTime[mday].", ";
	$timestampNow .= $theTime[year]." - ";
	$timestampNow .= $theTime[hours].":";
	$timestampNow .= $theTime[minutes].":";
	$timestampNow .= $theTime[seconds];
	if(nrDebug) echo "returning date ";

	return($timestampNow);
}

if(nrDebug) echo "nrInitLog ";

// zero log file and write header
// exit script if unable to write to logfile
// initialize $nrResult global variable
function nrInitLog(){
	global $nrResult;
	if(nrDebug) echo "initializing log ";
	if (!is_dir(nrPHPdiagDir)) {
		if (!mkdir(nrPHPdiagDir)) {
    		echo('Failed to create '.nrPHPdiagDir);
    		exit(126);
    	}
    }
    $nrLogFileHandle = fopen(nrLogFile, "w");
	if($nrLogFileHandle != FALSE){

		$initMessage = "***************************************************************************\n";
		$initMessage .= "Version : " . nrPHPdiagVer . "\n" ;
		$initMessage .= "Start Time : " . timestamp() . " GMT\n\n";

		$nrResult = $initMessage;

		fwrite($nrLogFileHandle,$initMessage);
		if(nrDebug) printf("Log Init Message : " . $initMessage);

		fclose($nrLogFileHandle);
		if(nrDebug) echo "log initialized ";
	}
	else { echo("Unable to open log file at " . nrLogFile ."\n" ); exit(126);}
}

if(nrDebug) echo "nrOut";

// output data both to webpage & logfile
// handle HTML tags
function nrOut($tag,$msg){
	global $nrResult;
	if(nrDebug) echo "nrOut ";
	$nrLogFileHandle = fopen (nrLogFile, "a");
	fwrite($nrLogFileHandle, $msg . "\n");
	fclose($nrLogFileHandle);

	$nrResult .= msg . "\n";

	if(! empty($tag)){
		$openTag = "<" . $tag . ">";
		$closeTag = "</" . $tag . ">";
	}
	else{
		$openTag = "";
		$closeTag = "";
	}

	printf($openTag . $msg . $closeTag . "\n") ;
}

// I would like to create nrPHPValue which gets the value from ini_get and get_cfg_var
// then goes on its merry way if they match and does something different if they don't
// perhaps this isn't necessary or possible?

if(nrDebug) echo "getting to dispatcher ";


// ***************************************************************************
//
//  
//                              APPLICATION CODE 
//
//
// ***************************************************************************

nrInitLog();

if(empty($_GET)){ // was the script called without any parameters? if so, this is the main function
	printf("<html>\n");
	printf(nrCSS()); // use SASS to generate CSS with Data URI encoded logo image
	printf("<body>\n");

	printf("<h1>New Relic PHP Agent Diagnostic Tool</h1>");
	printf("<br />");
	nrOut("h2","Basic System Info");
	printf("<ul>");

		nrOut("li","System : " . php_uname('a')); // kernel
		nrOut("li","Hostname : " . gethostname()); // hostname
		nrOut("li","Document Root : " . $_SERVER[DOCUMENT_ROOT]); // PHP document root
		nrOut("li","Self : " . $_SERVER[PHP_SELF]); // script file
		nrOut("li","Address : " . $_SERVER[SERVER_ADDR]); // IP address connected to
		nrOut("li","Name : " . $_SERVER[SERVER_NAME]); // hostname from URL 
		nrOut("li","Server Root : " . $_SERVER[SERVER_ROOT]); // PHP document root

	printf("</ul>");

	if (extension_loaded('newrelic')) { 
		nrOut("p","Extension is Loaded");
		nrOut("p","New Relic App Name: " . $nrInitialAppName);
		nrOut("p","New Relic Full License Key: " . ini_get('newrelic.license'));
		nrOut("p","Loaded Config File: " . get_cfg_var('cfg_file_path'));
		nrOut("p","Root config file: " . getenv('PHP_INI_SCAN_DIR'));
		nrOut("p","Scanned ini files: " . php_ini_scanned_files());


		$daemon_running = shell_exec("ps -ef | grep newrelic-daemon | grep -v grep ");
		if(empty($daemon_running)){
			nrOut("strong","Daemon IS NOT Running");
			// check newrelic-daemon file permissions
			if(file_exists(nrDaemonConfigFile)){
				nrOut("p","Try starting your newrelic-daemon with /etc/init.d/newrelic-daemon start and then rerun the diagnostic.");
				nrOut("p","If you try staring the daemon and get the same results, email support@newrelic.com and include the ouput from /etc/init.d/newrelic-daemon start along with the logfile /etc/var/log/newrelic-daemon.log");
				// external startup - /etc/init.d/newrelic-daemon start
			}
			else{
				nrOut("p","Try restarting your web server or PHP dispatcher (FPM,Apache,nginx etc) and then rerun the diagnostic.");
				nrOut("p","If you get the same results, email support@newrelic.com and include any relevant web server logs from startup along with the logfile /etc/var/log/newrelic-daemon.log");
				// agent init - restart web server
			}

			$nrDaemonLogFile = ini_get(newrelic.daemon.logfile);
			if(empty($nrDaemonLogFile)) $nrDaemonLogFile = nrDaemonLogFileDefault;

			if(file_exists($nrDaemonLogFile)){
				$nrDaemonLogErrors = shell_exec("grep error ".$nrDaemonLogFile);
				if(empty($nrDaemonLogErrors)){
					nrOut("p","No Errors in Daemon Log : ".$nrDaemonLogFile);
				}
				else{
					nrOut("p","Daemon Log Errors");
					nrOut("pre",$nrDaemonLogErrors);
				}
			}
			else{
				nrOut("strong","Daemon running but no log file is being created. Check permissions for ".$nrDaemonLogFile);
			}

			// is the port a port or a socket file? I think for now, if it's a socket file, I'll just ignore trying to connect to it
			// $sock = fsockopen('unix:///full/path/to/my/socket.sock', NULL, $errno, $errstr);
			// check /var/log/newrelic/php-agent.log and find the last startup - does it say init?
			
			$nrDaemonSocket = fsockopen("127.0.0.1:",nrDaemonPort);
			if(!$nrDaemonSocket){
				nrOut("strong","Unable to connect to newrelic-daemon on 127.0.0.1 port ".strval(nrDaemonPort));
				nrOut("p","Try restarting the daemon with /etc/init.d/newrelic-daemon restart (don't worry if you get an error)");
				nrOut("p","And then restart your web server. If the problem persists, there are three common causes:");
				printf("<ul>");
					nrOut("li","The newrelic-daemon is unable to contact New Relic's servers.");
					nrOut("li","A firewall is preventing the connection.");
					nrOut("li","Security enhancements such as GRSecurity, App Armor, or SELinux.");
				printf("</ul>");
				//check iptables

				// this applies for socket files too 

				//check kernel for grsec
				//check for app armor
				//check for SELinux

				//connect to mongrels
			}
			else{
				nrOut("p","Connecting to Daemon.");
				if(fwrite($nrDaemonSocket, "ver")) nrOut("p","Daemon reacted normally once.");
				if(!fwrite($nrDaemonSocket, "version")) nrOut("p","Daemon reacted normally twice.");
			}

			fclose($nrDaemonSocket);
		}
		else{
			nrOut("","Daemon is Running"); // is this being evaluated? I didn't see the output in testing.
			nrOut("pre",$daemon_running);

			// do the versions match?
		}
		// create a diag application and generate metrics
		// maybe create a deployment with some critical info in the change log?
	}
	else {
		nrOut("strong","Extension NOT Loaded");
		// find out abi & extension_dir and report how to create the link https://newrelic.com/docs/php/php-agent-installation-non-standard-php#manual
		// if newrelic.so is there, is extension in the php.ini
		// if the extension line is in php.ini, where does the symbolic link go? what are the permissions ls -laZ
	}

	// find relic in logs and inis

	printf("</body></html>");
}
elseif(in_array( "exercise", array_keys($_GET))){
	excercise($_GET["excercise"]);
}


//Put phpinfo() into array
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

//phpinfo() in an array (we can focus on the New Relic section)
print "<pre>";
print_r($phpinfo);
print "</pre>";

?>
