<?php
require_once __DIR__ . '/vendor/autoload.php';


define('APPLICATION_NAME', 'Google Sheets API PHP Quickstart');
define('CREDENTIALS_PATH', '~/.credentials/sheets.googleapis.com-php-quickstart.json');
define('CLIENT_SECRET_PATH', __DIR__ . '/client_secret.json');
// If modifying these scopes, delete your previously saved credentials
// at ~/.credentials/sheets.googleapis.com-php-quickstart.json
define('SCOPES', implode(' ', array(
  Google_Service_Sheets::SPREADSHEETS_READONLY)
));

if (php_sapi_name() != 'cli') {
  throw new Exception('This application must be run on the command line.');
}

/**
 * Returns an authorized API client.
 * @return Google_Client the authorized client object
 */
function getClient() {
  $client = new Google_Client();
  $client->setApplicationName(APPLICATION_NAME);
  $client->setScopes(SCOPES);
  $client->setAuthConfig(CLIENT_SECRET_PATH);
  $client->setAccessType('offline');

  // Load previously authorized credentials from a file.
  $credentialsPath = expandHomeDirectory(CREDENTIALS_PATH);
  if (file_exists($credentialsPath)) {
    $accessToken = json_decode(file_get_contents($credentialsPath), true);
  } else {
    // Request authorization from the user.
    $authUrl = $client->createAuthUrl();
    printf("Open the following link in your browser:\n%s\n", $authUrl);
    print 'Enter verification code: ';
    $authCode = trim(fgets(STDIN));

    // Exchange authorization code for an access token.
    $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);

    // Store the credentials to disk.
    if(!file_exists(dirname($credentialsPath))) {
      mkdir(dirname($credentialsPath), 0700, true);
    }
    file_put_contents($credentialsPath, json_encode($accessToken));
    printf("Credentials saved to %s\n", $credentialsPath);
  }
  $client->setAccessToken($accessToken);

  // Refresh the token if it's expired.
  if ($client->isAccessTokenExpired()) {
    $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
    file_put_contents($credentialsPath, json_encode($client->getAccessToken()));
  }

  return $client;
}

/**
 * Expands the home directory alias '~' to the full path.
 * @param string $path the path to expand.
 * @return string the expanded path.
 */
function expandHomeDirectory($path) {
  $homeDirectory = getenv('HOME');
  if (empty($homeDirectory)) {
    $homeDirectory = getenv('HOMEDRIVE') . getenv('HOMEPATH');
  }
  return str_replace('~', realpath($homeDirectory), $path);
}

// Get the API client and construct the service object.
$client = getClient();
$service = new Google_Service_Sheets($client);

// Prints the names and majors of students in a sample spreadsheet:
// https://docs.google.com/spreadsheets/d/1BxiMVs0XRA5nFMdKvBdBZjgmUUqptlbs74OgvE2upms/edit
$spreadsheetId = '1LhCT9KRfMrXinRyphcBn1jz3JIUh5LQSli9mQFmOc7w';
$range = 'signups!A1:D';
$response = $service->spreadsheets_values->get($spreadsheetId, $range);
$values = $response->getValues();

	$Cfn = array_search ( 'First Name', $values[0]);
	$Cln = array_search ( 'Last Name', $values[0]);
	$Cgrade = array_search ( 'Grade', $values[0]);
	$Cevents = array_search ( 'Events', $values[0]);

	$ppl = array();
	
	for ($i = 1 ; $i < count ($values) ; $i++){
		$info = $values[$i];
		$person = [];
		$person ['name']= $info[$Cfn] . ' ' . $info[$Cln];
		$person ['grade'] = $info[$Cgrade];
		$person ['events'] = explode (", " , $info[$Cevents]);
		$person ['numevents'] = count ($person ['events']);
		$ppl[$info[$Cfn] . ' ' . $info[$Cln]] = $person; 
	}

//	 echo json_encode ($ppl);    // $ppl[$name][$info]
	
$range = 'events!A1:B';
$response = $service->spreadsheets_values->get($spreadsheetId, $range);
$values = $response->getValues();

$countins = 0; 
$countevents = 0; 

$blocks = array ();
for ($i = 2 ; $i < count ($values); $i++){
	
	// time slots
	if (isset ($values [$i][0]) && $values [$i][0]!=''){
		$time = $values[$i][0];
	
		$k = $i - 1;
		do{
			$k++;
		}
		while ( isset ($values[$k]) && isset ($values[$k][0]) );
		
		$events = array ();
		for ($p = $i ; $p<$k; $p++){
			$event = $values [$p][1];
			// echo "\n". $event. ' : ';
			$countevents++;
			
			$competitors = array ();
		 
			foreach ($ppl as $person){
				if (in_array ( $event, $person ['events'] )){
					// echo ' in ';
					$countins++;
					$competitors [] = $person;
				}
			}

			$events[$event]['competitors'] = $competitors; 
			$events[$event]['numcompetitors'] = count ($competitors);
		}
		
		$blocks [$time] = $events;
		echo "\n";
	}
}
   // $blocks [$time][$event][$person (key is name)][$info e.g events, event count, name, grade]
   
   
//echo json_encode ($blocks, JSON_PRETTY_PRINT);
echo "\n";
echo 'TOTAL EVENT REQUESTS: ' . $countins. "\n";
echo 'TOTAL PEOPLE: ' . count ($ppl). "\n";
echo 'TOTAL EVENTS: ' . $countevents;


 
 

