<?php

//Call API function; think of this as instantiating the API. Since the API relies on a POST request if this page is accessed via the browser a json error will be echoed since no parameters were passed in
apiCall();

function apiCall(){
$result = "";
//Check user has provided api key and name of function
	if (isset($_POST['apiKey']) and isset($_POST['function'])){

		$ApiKey = $_POST['apiKey'];
		$function = $_POST['function'];
		$apiKeyResult = checkApiKey($ApiKey);
		//Given a valid API key, call the API function requested by the user
		if ($apiKeyResult['validApiKey']){

			switch ($function){

				case 'getCountCaseClosed':

					$User = $_POST['user'];
					$result = getCountCaseClosed($apiKeyResult['conn'], $User);
					break;
				
				//No function is found return an error
				default:

	        		$result = json_encode(array('Status' => 400, 'Error message' => 'Function not found'));
					break;
			}
		}//API key isn't valid return an error 
		else{

			$result = json_encode(array('Status' => 400, 'Error message' => 'Invalid or expired API key')); 
		}
	}//No API key/function found in users' parameter
		else{
	        $result = json_encode(array('Status' => 400, 'Error message' => 'Request missing API key or function'));
	}
	//Echo will return json in http.responseText whilst also printing the result to screen if accessing simple-api.php through the browser
	echo $result;
}

function checkApiKey($Apikey){

	/* Update credentials for your database
	$servername = "localhost";
	$username = "";
	$password = "";
	$db = "";
	*/

	//Create connection
	$conn = new mysqli($servername, $username, $password, $db);
	//Check connection
	if ($conn->connect_error) {
	    die("Connection failed: " . $conn->connect_error);
	}
	
	//SQL query to get the number of valid API keys found
	//Preparing the SQL query protects against SQL injection, see http://stackoverflow.com/questions/60174/how-can-i-prevent-sql-injection-in-php
	$stmt = $conn->prepare('SELECT COUNT(*) FROM ApiKeys WHERE Apikey = ? AND CURDATE() < Expires');
	$stmt->bind_param('s', $Apikey);
	$stmt->execute();
	$stmt->bind_result($count);
	$stmt->store_result();
	if ($stmt->num_rows > 0) {
		$stmt->fetch();
	} 
	if ($count > 0) {
		//API key is valid;
		$checkApiKeyResult = array('validApiKey' => true, 'conn' => $conn);
	} else {
		//API key is not valid;
		$checkApiKeyResult = array('validApiKey' => false);
	}
	return $checkApiKeyResult;
}


function getCountCaseClosed($conn, $User){
	//SQL query to count the number of cases closed for the user specified in the request
	//Protect against SQL injection, see http://stackoverflow.com/questions/60174/how-can-i-prevent-sql-injection-in-php
	$stmt = $conn->prepare('SELECT COUNT(*) FROM CaseClosed WHERE User = ?');
	$stmt->bind_param('s', $User);
	$stmt->execute();
	$stmt->bind_result($count);
	$stmt->store_result();
	if ($stmt->num_rows > 0) {
		
	    //output data of each row
	    while($stmt->fetch()) {
	        //only one row returned for Count(*) function
	        $jsonResult = json_encode(array('Status' => 200, 'CasesClosed' => $count,'User' => $User ));
	        
	        return $jsonResult;
	    }
	} else {
	    //echo "0 results";
	    $jsonResult = json_encode(array('Status' => 400, 'Error message' => 'Could not count cases closed' ));
	}
	$conn->close();
}
?>
