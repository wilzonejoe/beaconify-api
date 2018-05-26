<?php
require('tokenValidation.php');

$response = array();

if(strcasecmp($_SERVER['REQUEST_METHOD'], 'POST') != 0){
    $response["statusCode"] = 405;
    $response["message"] = "Request not supported";
} 
else {
    $jsonReceived = trim(file_get_contents("php://input"));

    $content = json_decode($jsonReceived, true);

    if(!is_array($content)){
        $response["statusCode"] = 405;
        $response["message"] = "Request must be in valid json";
    }else{
        if (isset($content["firstname"]) && !empty($content["firstname"]) && isset($content["lastname"]) 
        && !empty($content["lastname"]) && isset($content["username"]) && !empty($content["username"]) 
        && isset($content["password"]) && !empty($content["password"])) {
        
            require_once('../config.php');
            $conn = mysqli_connect($sql_host, $sql_user, $sql_pass, $sql_db);
            $firstname = $content["firstname"];
            $lastname = $content["lastname"];
            $username = $content["username"];
            $password = $content["password"];
    
            if (!$conn) {
                http_response_code(500);
                $response["statusCode"] = 500;
                $response["message"] = "Database connection failure";
            } else {
                $stmt = $conn->prepare("insert into users" . "(firstname,lastname,username,password)" . " values (?,?,?,?)");
                $stmt->bind_param("ssss", $firstname, $lastname, $username, $password);
    
                $result = $stmt->execute();
                if (!$result) {
                    http_response_code(400);
                    $response["statusCode"] = 400;
                    $response["message"] = "Register is not successful."; 
                } else {
                    $userId = mysqli_insert_id($conn);

                    $userResponse = array();
                    $userResponse["userId"] = $userId;
                    $userResponse["username"] = $username;
                    $userResponse["firstname"] = $firstname;
                    $userResponse["lastname"] = $lastname;

                    $resultToken = generateToken($userId);

                    if (!$resultToken) {
                         http_response_code(400);
                         $response["statusCode"] = 400;
                         $response["message"] = "Register is not successful."; 
                    }else{
                        http_response_code(200);
                        $response["statusCode"] = 200;
                        $response["token"] = $resultToken;
                        $response["user"] = $userResponse;
                    }
                }
                mysqli_close($conn);
            }
        } 
        else {
            http_response_code(400);
            $response["statusCode"] = 400;
            $response["message"] = "Input missing."; 
        }
    }
}

echo json_encode($response);
?>