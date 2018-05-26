<?php
    require('tokenValidation.php');
    $headers = apache_request_headers();
    if(isset($headers['Authorization'])){
        $authorizationHeader = $headers['Authorization'];
    }else{
        $authorizationHeader = '';
    }

    $reponse = array();

    if(($userOwnId = validateToken($authorizationHeader)) > 0){
        if(strcasecmp($_SERVER['REQUEST_METHOD'], 'GET') == 0){
            $_SERVER['REQUEST_URI_PATH'] = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            $pathSegments = explode('/', $_SERVER['REQUEST_URI_PATH']);
            $userId= $pathSegments[count($pathSegments)-1];

            require('../config.php');

            $conn = mysqli_connect($sql_host, $sql_user, $sql_pass, $sql_db);

            if(strcasecmp($userId, 'users.php') != 0){
                $sql_check_credential = "SELECT * FROM users WHERE id='$userId'";
                $result = mysqli_query($conn,$sql_check_credential);
                if (mysqli_num_rows($result)==0) {
                    http_response_code(404);
                    $response["statusCode"] = 404;
                    $response["message"] = "User not found"; 
                } else {
                    $row= mysqli_fetch_assoc($result);
                    $responseUser = array();
                    $responseUser["userId"]  = $row["ID"];
                    $responseUser["firstname"] = $row["firstname"];
                    $responseUser["lastname"] = $row["lastname"];;

                    http_response_code(200);
                    $response["statusCode"] = 200;
                    $response["user"] = $responseUser;
                }
            }else{
                $sql_check_credential = "SELECT * FROM users WHERE not(id='$userOwnId')";
                $result = mysqli_query($conn,$sql_check_credential);
                $reponseCollectionOfUsers = array();

                while ($row= mysqli_fetch_assoc($result)) {
                    $responseUser = array();
                    $responseUser["userId"]  = $row["ID"];
                    $responseUser["firstname"] = $row["firstname"];
                    $responseUser["lastname"] = $row["lastname"];;
                    array_push($reponseCollectionOfUsers, $responseUser);
                }
                http_response_code(200);
                $response["statusCode"] = 200;
                $response["user"] = $reponseCollectionOfUsers;
            }
        }else{
            http_response_code(405);
            $response["statusCode"] = 405;
            $response["message"] = "Request not supported";
        }
    }else{
        http_response_code(401);
        $response["statusCode"] = 401;
        $response["message"] = "Unauthorized";
    }
    echo json_encode($response);
?>