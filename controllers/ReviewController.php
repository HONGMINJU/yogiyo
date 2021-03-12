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
         * API Name : 사장님 댓글 달기 API
         * 마지막 수정 날짜 : 20.09.05
         */
        case "createMasterComment":
            http_response_code(200);
            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            $reviewIdx=$vars["reviewIdx"];

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
            if(!isMaster($data->id)){
                $res->isSuccess = FALSE;
                $res->code = 213;
                $res->message = "사장님만 이용할 수 있습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            else if(!isVaildReview($reviewIdx)){
                $res->isSuccess = FALSE;
                $res->code = 216;
                $res->message = "해당 리뷰가 없습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            $masterIdx=email_userIdx($data->id);
            if(!isMyStoreReview($masterIdx,$reviewIdx)){
                $res->isSuccess = FALSE;
                $res->code = 214;
                $res->message = "해당 가게의 사장님만 이용할 수 있습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            else if(isAlreadyMasterReview($reviewIdx)){
                $res->isSuccess = FALSE;
                $res->code = 218;
                $res->message = "해당 리뷰에 이미 사장님 댓글이 있습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            else if (strlen($req->contents)==0){
                $res->isSuccess = FALSE;
                $res->code = 260;
                $res->message = "내용을 입력해주세요.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            else if(strlen($req->contents)>300){
                $res->isSuccess = FALSE;
                $res->code = 215;
                $res->message = "300자를 넘을 수 없습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            else{
                $res->result=createMasterComment($reviewIdx,$req->contents);
                $res->isSuccess = TRUE;
                $res->code = 100;//
                $res->message = "사장님 댓글 업로드 성공";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

        /*
         * API Name : 리뷰 좋아요 및 취소 API
         * 마지막 수정 날짜 : 20.09.05
         */
        case "createReviewLike":
            http_response_code(200);
            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            $reviewIdx=$vars["reviewIdx"];
            $data= getDataByJWToken($jwt,JWT_SECRET_KEY);
            $userIdx=email_userIdx($data->id);

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
            else if(isVaildReview($reviewIdx)) {

                if (!isAlreadyReviwHeart($reviewIdx, $userIdx)) {
                    //insert
                    $res->result=createReviewHeart($reviewIdx,$userIdx);
                    $res->isSuccess = TRUE;
                    $res->code = 100;
                    $res->message = "리뷰 좋아요 성공";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }
                else if (isReviewHeart_L($reviewIdx, $userIdx)) {
                    //L-N
                    $res->result=patchReviewHeart_N($reviewIdx,$userIdx);
                    $res->isSuccess = TRUE;
                    $res->code = 100;
                    $res->message = "리뷰 좋아요 취소 성공";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }
                else {
                    //N->L
                    $res->result=patchReviewHeart_L($reviewIdx,$userIdx);
                    $res->isSuccess = TRUE;
                    $res->code = 100;
                    $res->message = "리뷰 다시 좋아요 성공";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }
            }
            else{
                $res->isSuccess = FALSE;
                $res->code = 216;
                $res->message = "해당 리뷰가 없습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

        /*
        * API Name : 리뷰 조회 API
        * 마지막 수정 날짜 : 20.09.08
        */
        case "getReview":
            http_response_code(200);
            $storeIdx=$vars["storeIdx"];
            $sorting=$_GET["sort"];
            if($sorting==null or $sorting=="time"){
                $res->result=getStoreReview($storeIdx,"review.createdAt","D");
            }
            else if($sorting=="heart"){
                $res->result=getStoreReview($storeIdx,"reviewHeart","D");
            }
            else if($sorting=="upStar"){
                $res->result=getStoreReview($storeIdx,"review.totalScore","D");
            }
            else if($sorting=="downStar"){
                $res->result=getStoreReview($storeIdx,"review.totalScore","X");
            }
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "리뷰 조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        /*
         * API Name : 리뷰 삭제 API
         * 마지막 수정 날짜 : 20.09.09
         */
        case "deleteReview":
            http_response_code(200);
            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            $reviewIdx=$vars["reviewIdx"];
            if($jwt==null){
                $res->isSuccess = FALSE;
                $res->code = 210;
                $res->message = "로그인 후 이용해주세요.";
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
            else{
                $data= getDataByJWToken($jwt,JWT_SECRET_KEY);
                $userIdx=email_userIdx($data->id);
                if(!isVaildReview($reviewIdx)){
                    $res->isSuccess = FALSE;
                    $res->code = 256;
                    $res->message = "해당 리뷰가 없습니다.";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }
                else if(!isMyReview($userIdx,$reviewIdx)){
                    $res->isSuccess = FALSE;
                    $res->code = 257;
                    $res->message = "자신의 리뷰만 삭제 가능합니다.";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }
                else{
                    deleteMyReview($userIdx,$reviewIdx);
                    $res->isSuccess = TRUE;
                    $res->code = 100;
                    $res->message = "리뷰 삭제 성공";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }
            }

        /*
         * API Name : 나의 리뷰 조회 API
         * 마지막 수정 날짜 : 20.09.09
         */
        case "getMyReview":
            http_response_code(200);
            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            if($jwt==null){
                $res->isSuccess = FALSE;
                $res->code = 210;
                $res->message = "로그인 후 이용해주세요.";
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
            else{
                $data= getDataByJWToken($jwt,JWT_SECRET_KEY);
                $userIdx=email_userIdx($data->id);
                $res->notYetReviews=getMyReviewNotYet($userIdx);
                $res->alreadyReviews=getMyReviewAlready($userIdx);
                $res->isSuccess = TRUE;
                $res->code = 100;
                $res->message = "나의 리뷰 정보조회 성공";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

        /*
         * API Name : 사장님 댓글 삭제 API
         * 마지막 수정 날짜 : 20.09.09
         */
        case "deleteMasterComment":
            http_response_code(200);
            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            $reviewIdx=$vars["reviewIdx"];

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
            if(!isMaster($data->id)){
                $res->isSuccess = FALSE;
                $res->code = 213;
                $res->message = "사장님만 이용할 수 있습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            else if(!isVaildReview($reviewIdx)){
                $res->isSuccess = FALSE;
                $res->code = 216;
                $res->message = "해당 리뷰가 없습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            $masterIdx=email_userIdx($data->id);
            if(!isMyStoreReview($masterIdx,$reviewIdx)){
                $res->isSuccess = FALSE;
                $res->code = 214;
                $res->message = "해당 가게의 사장님만 이용할 수 있습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            else if(!isAlreadyMasterReview($reviewIdx)){
                $res->isSuccess = FALSE;
                $res->code = 268;
                $res->message = "해당 리뷰에는 사장님 댓글이 없습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            else{
                deleteMasterComment($reviewIdx,$req->contents);
                $res->isSuccess = TRUE;
                $res->code = 100;
                $res->message = "사장님 댓글 삭제 성공";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

        /*
         * API Name : 사장님 댓글 수정 API
         * 마지막 수정 날짜 : 20.09.09
         */
        case "patchMasterComment":
            http_response_code(200);
            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            $reviewIdx=$vars["reviewIdx"];
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
            if(!isMaster($data->id)){
                $res->isSuccess = FALSE;
                $res->code = 213;
                $res->message = "사장님만 이용할 수 있습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            else if(!isVaildReview($reviewIdx)){
                $res->isSuccess = FALSE;
                $res->code = 216;
                $res->message = "해당 리뷰가 없습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            $masterIdx=email_userIdx($data->id);
            if(!isMyStoreReview($masterIdx,$reviewIdx)){
                $res->isSuccess = FALSE;
                $res->code = 214;
                $res->message = "해당 가게의 사장님만 이용할 수 있습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            else if(!isAlreadyMasterReview($reviewIdx)){
                $res->isSuccess = FALSE;
                $res->code = 268;
                $res->message = "해당 리뷰에는 사장님 댓글이 없습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            else if (strlen($req->contents)==0){
                $res->isSuccess = FALSE;
                $res->code = 260;
                $res->message = "내용을 입력해주세요.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            else if(strlen($req->contents)>300){
                $res->isSuccess = FALSE;
                $res->code = 215;
                $res->message = "300자를 넘을 수 없습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            else{
                patchMasterComment($reviewIdx,$req->contents);
                $res->isSuccess = TRUE;
                $res->code = 100;
                $res->message = "사장님 댓글 수정 성공";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}
