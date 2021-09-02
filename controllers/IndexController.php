<?php
require 'function.php';

const JWT_SECRET_KEY = "TEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEY";

$res = (Object)Array();
$res1 = (Object)Array();
header('Content-Type: json');
$req = json_decode(file_get_contents("php://input"));
try {
    addAccessLogs($accessLogs, $req);
    switch ($handler) {

        case "ACCESS_LOGS":
            //            header('content-type text/html charset=utf-8');
            header('Content-Type: text/html; charset=UTF-8');
            getLogs("./logs/access.log");
            break;
        case "ERROR_LOGS":
            //            header('content-type text/html charset=utf-8');
            header('Content-Type: text/html; charset=UTF-8');
            getLogs("./logs/errors.log");
            break;


        /*
         * API No. 0
         * API Name : 테스트 API
         * 마지막 수정 날짜 : 20.08.16
         */
        case "getUsers":
            http_response_code(200);

            $res->result = getUsers();
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "조회 성공";
         * API Name : 회원가입 API
         * 마지막 수정 날짜 : 20.09.01
         */

        case "createUser":
            http_response_code(200);

            $method = $req->method;
            $userEmail = $req->userEmail;
            $password = $req->password;
            $checkPassword = $req->checkPassword;
            $nickName = $req->nickName;
            $phoneNum = $req->phoneNum;

            $checkEmail=preg_match("/^[_\.0-9a-zA-Z-]+@([0-9a-zA-Z][0-9a-zA-Z-]+\.)+[a-zA-Z]{2,6}$/i", $userEmail);
            $checkPhoneNum = preg_replace("/[^0-9]/", "", $phoneNum);

            $passCheck = passwordCheck($password);

            if(isValidUserEmail($userEmail))
            {
                $res->isSuccess = FALSE;
                $res->code = 202;
                $res->message = "해당 이메일을 사용하는 아이디가 이미 존재합니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            else if($userEmail == NULL || $password == NULL || $phoneNum == NULL)
            {
                $res->isSuccess = FALSE;
                $res->code = 203;
                $res->message = "필수 항목이 충족되지 못하였습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            else if($checkEmail==false)
            {
                $res->isSuccess = FALSE;
                $res->code = 204;
                $res->message = "잘못된 이메일 형식입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            else if($passCheck[0] == false)
            {
                $res->isSuccess = FALSE;
                $res->code = 205;
                $res->message = $passCheck[1];
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            else if($password != $checkPassword)
            {
                $res->isSuccess = FALSE;
                $res->code = 206;
                $res->message = "비밀번호가 일치하지 않습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            else if(!preg_match("/^01[0-9]{8,9}$/", $checkPhoneNum))
            {
                $res->isSuccess = FALSE;
                $res->code = 207;
                $res->message = "휴대폰 번호가 형식에 맞지 않습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            else if($passCheck[0] == true)
            {
                createUser($userEmail,$password,$method,$nickName,$phoneNum);
                $userIdx = email_userIdx($userEmail);
                userLevel($userIdx);

                $res->isSuccess = TRUE;
                $res->code = 100;
                $res->message = "성공";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

        /*
         * API No. 0
         * API Name : 회원탈퇴 API
         * 마지막 수정 날짜 : 20.09.01
         */

        case "deleteUser":
            http_response_code(200);

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            $data = getDataByJWToken($jwt, JWT_SECRET_KEY);
            $userEmail = $data->id;

            if(!isValidUserEmail($userEmail))
            {
                $res->isSuccess = FALSE;
                $res->code = 208;
                $res->message = "해당 회원 정보가 없습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            deleteUser($userEmail);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "회원탈퇴 성공";
            $res->message = "성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "getUser":
            http_response_code(200);

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            $data = getDataByJWToken($jwt, JWT_SECRET_KEY);
            $userEmail = $data->id;
            $userIdx = email_userIdx($userEmail);

            $res->result = getUser($userIdx);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "getUserDetail":
            http_response_code(200);

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            $data = getDataByJWToken($jwt, JWT_SECRET_KEY);
            $userEmail = $data->id;
            $userIdx = email_userIdx($userEmail);

            $res->result = getUserDetail($userIdx);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "patchNickName":
            http_response_code(200);

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            $data = getDataByJWToken($jwt, JWT_SECRET_KEY);
            $userEmail = $data->id;

            $nickName = $req -> nickName;
            $userIdx = email_userIdx($userEmail);

            patchNickName($nickName,$userIdx);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "patchPhoneNum":
            http_response_code(200);

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            $data = getDataByJWToken($jwt, JWT_SECRET_KEY);
            $userEmail = $data->id;

            $phoneNum = $req -> phoneNum;
            $userIdx = email_userIdx($userEmail);
            $checkPhoneNum = preg_replace("/[^0-9]/", "", $phoneNum);

            if(!preg_match("/^01[0-9]{8,9}$/", $checkPhoneNum))
            {
                $res->isSuccess = FALSE;
                $res->code = 207;
                $res->message = "휴대폰 번호가 형식에 맞지 않습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            patchPhoneNum($phoneNum,$userIdx);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "postCoupon":
            http_response_code(200);

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            $data = getDataByJWToken($jwt, JWT_SECRET_KEY);
            $userEmail = $data->id;

            if($jwt==null){
                $res->isSuccess = FALSE;
                $res->code = 210;
                $res->message = "로그인 후 이용해주세요";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            else if(!isValidHeader($jwt,JWT_SECRET_KEY)){
                $res->isSuccess = FALSE;
                $res->code = 211;
                $res->message = "로그인 정보가 잘못 되었습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            $data= getDataByJWToken($jwt,JWT_SECRET_KEY);
            $userIdx=email_userIdx($data->id);
            $couponId=$req->couponId;
            if(strlen($couponId)==0){
                $res->isSuccess = FALSE;
                $res->code = 261;
                $res->message = "쿠폰 번호를 입력해주세요.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            else if(!isVaildCoupon($couponId)){
                $res->isSuccess = FALSE;
                $res->code = 258;
                $res->message = "유효하지 않은 쿠폰입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            else if(isMyCoupon($couponId,$userIdx)){
                $res->isSuccess = FALSE;
                $res->code = 259;
                $res->message = "이미 존재하는 쿠폰입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            else{
                $res->result=postCoupon($couponId,$userIdx);
                $res->isSuccess = TRUE;
                $res->code = 100;
                $res->message = "쿠폰 등록 성공";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

        case "getAdvertise":
            http_response_code(200);
            $res->result=getAdvertise();
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "광고 조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "postLevelCoupon":
            http_response_code(200);
            $res->result=postLevelCoupon();
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "레벨 쿠폰 지금 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}
