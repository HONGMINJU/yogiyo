<?php
require 'function.php';

const JWT_SECRET_KEY = "TEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEY";

$res = (Object)Array();

header('Content-Type: json');
$req = json_decode(file_get_contents("php://input"));
try {
    addAccessLogs($accessLogs, $req);
    switch ($handler) {
        /*
         * API No. 0
         * API Name : JWT 유효성 검사 테스트 API
         * 마지막 수정 날짜 : 19.04.25
         */

        case "validateJwt":
            // jwt 유효성 검사

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            http_response_code(200);
            if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                $res->isSuccess = FALSE;
                $res->code = 201;
                $res->message = "유효하지 않은 토큰입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "테스트 성공";

            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        /*
         * API No. 1
         * API Name : JWT 생성 테스트 API (로그인)
         * 마지막 수정 날짜 : 20.09.01
         */
        case "logIn":
            // jwt 유효성 검사
            http_response_code(200);

            if(!isValidUser($req->id, $req->pw)){
                $res->isSuccess = FALSE;
                $res->code = 201;
                $res->message = "로그인에 실패했습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }
            else if(isRestUser($req->id, $req->pw)){
                $res->isSuccess = FALSE;
                $res->code = 202;
                $res->message = "휴먼계정 입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }
            else {
                $jwt = getJWToken($req->id, $req->pw, JWT_SECRET_KEY);
                $res->result->jwt = $jwt;
                $res->isSuccess = TRUE;
                $res->code = 100;
                $res->message = "로그인 성공";
                echo json_encode($res, JSON_NUMERIC_CHECK);
            }
            break;
    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}
