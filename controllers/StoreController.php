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
         * API Name : 카테고리 별 가게 조회 API
         * 마지막 수정 날짜 : 20.09.05
         */
        case "getStores":
            http_response_code(200);
            $categoryIdx=$vars["categoryIdx"];
            $latitude=$_GET["latitude"];
            $longitude=$_GET["longitude"];
            $sorting=$_GET["sort"];
            $pay=$_GET["pay"];
            if($latitude==null or $longitude==null){
                $res->isSuccess = FALSE;
                $res->code = 217;
                $res->message = "주소를 설정해주세요.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if($sorting==null){
                $res->result=getStores($categoryIdx,$latitude,$longitude,$pay);
            }
            else if($sorting=="deliveryCharge"){
                $res->result=getStores_sortByDelivery($categoryIdx,$latitude,$longitude,$pay);
            }
            else if($sorting=="star"){
                $res->result=getStores_sortByStar($categoryIdx,$latitude,$longitude,$pay);
            }
            else if($sorting=="review"){
                $res->result=getStores_sortByReview($categoryIdx,$latitude,$longitude,$pay);
            }
            else if($sorting=="minPrice"){
                $res->result=getStores_sortByMinPrice($categoryIdx,$latitude,$longitude,$pay);
            }
            else if($sorting=="distance"){
                $res->result=getStores_sortByDistance($categoryIdx,$latitude,$longitude,$pay);
            }
            else if($sorting=="discount"){
                $res->result=getStores_sortByDiscount($categoryIdx,$latitude,$longitude,$pay);
            }
            else if($sorting=="masterComment"){
                $res->result=getStores_sortByMasterComment($categoryIdx,$latitude,$longitude,$pay);
            }
            else if($sorting=="deliveryTime"){
                $res->result=getStores_sortByDeliveryTime($categoryIdx,$latitude,$longitude,$pay);
            }
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "카테고리별 가게 조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        /*
         * API Name : 모든 가게 조회 API
         * 마지막 수정 날짜 : 20.09.05
         */
        case "getAllStores":
            http_response_code(200);
            $latitude=$_GET["latitude"];
            $longitude=$_GET["longitude"];
            $sorting=$_GET["sort"];
            $pay=$_GET["pay"];
            if($latitude==null or $longitude==null){
                $res->isSuccess = FALSE;
                $res->code = 217;
                $res->message = "주소를 설정해주세요.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if($sorting==null){
                $res->result=getALLStores($latitude,$longitude,$pay);
            }
            else if($sorting=="deliveryCharge"){
                $res->result=getALLStores_sortByDelivery($latitude,$longitude,$pay);
            }
            else if($sorting=="star"){
                $res->result=getALLStores_sortByStar($latitude,$longitude,$pay);
            }
            else if($sorting=="review"){
                $res->result=getALLStores_sortByReview($latitude,$longitude,$pay);
            }
            else if($sorting=="minPrice"){
                $res->result=getALLStores_sortByMinPrice($latitude,$longitude,$pay);
            }
            else if($sorting=="distance"){
                $res->result=getALLStores_sortByDistance($latitude,$longitude,$pay);
            }
            else if($sorting=="discount"){
                $res->result=getALLStores_sortByDiscount($latitude,$longitude,$pay);
            }
            else if($sorting=="masterComment"){
                $res->result=getALLStores_sortByMasterComment($latitude,$longitude,$pay);
            }
            else if($sorting=="deliveryTime"){
                $res->result=getALLStores_sortByDeliveryTime($latitude,$longitude,$pay);
            }
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "모든 가게 조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        /*
         * API Name : 찜한 가게 조회 API
         * 마지막 수정 날짜 : 20.09.04
         */
        case "getHeartStores":
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
                $res->result=getHeartStores($userIdx);
                $res->isSuccess = TRUE;
                $res->code = 100;
                $res->message = "찜한 가게 조회 성공";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

        /*
        * API Name : 카테고리 조회 API
        * 마지막 수정 날짜 : 20.09.06
        */
        case "getCategories":
            http_response_code(200);
            $res->result=getCategories();
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "카테고리 조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        /*
        * API Name : 검색 API
        * 마지막 수정 날짜 : 20.09.07
        */
        case "searchStore":
            http_response_code(200);
            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            $keyword=$_GET["keyword"];
            $latitude=$_GET["latitude"];
            $longitude=$_GET["longitude"];
            if($latitude==null or $longitude==null){
                $res->isSuccess = FALSE;
                $res->code = 217;
                $res->message = "주소를 설정해주세요.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if(strlen($keyword)==0){
                $res->isSuccess = FALSE;
                $res->code = 265;
                $res->message = "키워드를 입력해주세요.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if($jwt==null){
                $res->result=searchStore($keyword,null,$latitude,$longitude);
            }
            else{
                $data= getDataByJWToken($jwt,JWT_SECRET_KEY);
                $userIdx=email_userIdx($data->id);
                $res->result=searchStore($keyword,$userIdx,$latitude,$longitude);
            }
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "키워드 조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

       /*
        * API Name : 검색된 키워드 조회 API
        * 마지막 수정 날짜 : 20.09.07
        */
        case "getSearchKeyword":
            http_response_code(200);
            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            if($jwt==null){
                $res->result=null;
                $res->isSuccess = FALSE;
                $res->code = 210;
                $res->message = "로그인 후 이용해주세요";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            else{
                $data= getDataByJWToken($jwt,JWT_SECRET_KEY);
                $userIdx=email_userIdx($data->id);
                $res->result=getKeyword($userIdx);
            }
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "검색 기록 조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
            
       /*
        * API Name : 검색된 모든 키워드 삭제 API
        * 마지막 수정 날짜 : 20.09.09
        */
        case "deleteAllKeyword":
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
                $res->result=deleteAllKeyword($userIdx);
                $res->isSuccess = TRUE;
                $res->code = 100;
                $res->message = "모든 검색기록 제거 성공";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

        /*
         * API Name : 검색된 일부 키워드 삭제 API
         * 마지막 수정 날짜 : 20.09.09
         */
        case "deleteSomeKeyword":
            http_response_code(200);
            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            $KeywordIdx=$vars["KeywordIdx"];
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
                if(!isAlreadyKerwordIdx($KeywordIdx)){
                    $res->result=null;
                    $res->isSuccess = FALSE;
                    $res->code = 225;
                    $res->message = "해당 검색기록이 없습니다.";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }
                else if(!isMyKerwordIdx($userIdx,$KeywordIdx)){
                    $res->result=null;
                    $res->isSuccess = FALSE;
                    $res->code = 224;
                    $res->message = "자신의 검색기록만 삭제 가능";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }
                else{
                    $res->result=deleteSomeKeyword($userIdx,$KeywordIdx);
                    $res->isSuccess = TRUE;
                    $res->code = 100;
                    $res->message = "선택한 검색기록 삭제 성공";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }
            }

        /*
         * API Name : 최소 배달 시간 수정 API
         * 마지막 수정 날짜 : 20.09.10
         */
        case "patchMinDeliveryTime":
            http_response_code(200);
            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            $storeIdx=$vars["storeIdx"];
            $time=$req->time;
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
            $masterIdx=email_userIdx($data->id);
            if(!isMyStore($masterIdx,$storeIdx)){
                $res->isSuccess = FALSE;
                $res->code = 214;
                $res->message = "해당 가게의 사장님만 이용할 수 있습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            else if($time==null){
                $res->isSuccess = FALSE;
                $res->code = 264;
                $res->message = "정보를 입력해주세요.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            else if(!is_numeric($time)){
                $res->isSuccess = FALSE;
                $res->code = 218;
                $res->message = "숫자만 입력해주세요.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            else{
                $res->result=patchStoreMinDelivery($storeIdx,$time);
                $res->isSuccess = TRUE;
                $res->code = 100;
                $res->message = "최소 배달시간 수정 성공";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

        /*
         * API Name : 최대 배달 시간 수정 API
         * 마지막 수정 날짜 : 20.09.10
         */
        case "patchMaxDeliveryTime":
            http_response_code(200);
            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            $storeIdx=$vars["storeIdx"];
            $time=$req->time;
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
            $masterIdx=email_userIdx($data->id);
            if(!isMyStore($masterIdx,$storeIdx)){
                $res->isSuccess = FALSE;
                $res->code = 214;
                $res->message = "해당 가게의 사장님만 이용할 수 있습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            else if($time==null){
                $res->isSuccess = FALSE;
                $res->code = 264;
                $res->message = "정보를 입력해주세요.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            else if(!is_numeric($time)){
                $res->isSuccess = FALSE;
                $res->code = 218;
                $res->message = "숫자만 입력해주세요.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            else{
                $res->result=patchStoreMaxDelivery($storeIdx,$time);
                $res->isSuccess = TRUE;
                $res->code = 100;
                $res->message = "최대 배달시간 수정 성공";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

        /*
         * API Name : 최소 주문금액 수정 API
         * 마지막 수정 날짜 : 20.09.10
         */
        case "patchMinPrice":
            http_response_code(200);
            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            $storeIdx=$vars["storeIdx"];
            $price=$req->price;
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
            $masterIdx=email_userIdx($data->id);
            if(!isMyStore($masterIdx,$storeIdx)){
                $res->isSuccess = FALSE;
                $res->code = 214;
                $res->message = "해당 가게의 사장님만 이용할 수 있습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            else if($price==null){
                $res->isSuccess = FALSE;
                $res->code = 264;
                $res->message = "정보를 입력해주세요.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            else if(!is_numeric($price)){
                $res->isSuccess = FALSE;
                $res->code = 218;
                $res->message = "숫자만 입력해주세요.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            else{
                $res->result=patchStoreMinPrice($storeIdx,$price);
                $res->isSuccess = TRUE;
                $res->code = 100;
                $res->message = "최소 배달금액 수정 성공";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }


        /////////////////////////////////////////////////////////////////////////////////////////////////////////////


        case "storeInfo":
            http_response_code(200);
            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            $data = getDataByJWToken($jwt, JWT_SECRET_KEY);
            $userEmail = $data->id;

            $userIdx = email_userIdx($userEmail);
            $no = $vars["storeIdx"];

            $isDiscount = storeIdx_isDiscount($no);

            if(!isValidStore($no))
            {
                $res->isSuccess = FALSE;
                $res->code = 209;
                $res->message = "해당 가게정보가 없습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if($isDiscount =='N')
            {
                $res->result = noDiscount($userIdx,$no);
                $res->isSuccess = TRUE;
                $res->code = 100;
                $res->message = "성공";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if($isDiscount =='R')
            {
                $res->result = redWeek($userIdx,$no);
                $res->isSuccess = TRUE;
                $res->code = 100;
                $res->message = "성공";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if($isDiscount =='D')
            {
                $res->result = yesDiscount($userIdx,$no);
                $res->isSuccess = TRUE;
                $res->code = 100;
                $res->message = "성공";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

        case "storeBestMenu":
            http_response_code(200);
            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            $data = getDataByJWToken($jwt, JWT_SECRET_KEY);
            $userEmail = $data->id;

            $userIdx = email_userIdx($userEmail);
            $no = $vars["storeIdx"];

            if(!isValidStore($no))
            {
                $res->isSuccess = FALSE;
                $res->code = 209;
                $res->message = "해당 가게정보가 없습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            $res->result = storeBestMenu($no);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "storeCategory":
            http_response_code(200);
            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            $data = getDataByJWToken($jwt, JWT_SECRET_KEY);
            $userEmail = $data->id;

            $userIdx = email_userIdx($userEmail);
            $no = $vars["storeIdx"];

            if(!isValidStore($no))
            {
                $res->isSuccess = FALSE;
                $res->code = 209;
                $res->message = "해당 가게정보가 없습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            $res->result = storeCategory($no);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "storeCategoryClick":
            http_response_code(200);
            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            $data = getDataByJWToken($jwt, JWT_SECRET_KEY);
            $userEmail = $data->id;

            $userIdx = email_userIdx($userEmail);
            $storeIdx = $vars["storeIdx"];
            $categoryIdx = $vars["categoryIdx"];

            if(!isValidStore($storeIdx))
            {
                $res->isSuccess = FALSE;
                $res->code = 209;
                $res->message = "해당 가게정보가 없습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if(!isValidStoreCategory($categoryIdx))
            {
                $res->isSuccess = FALSE;
                $res->code = 212;
                $res->message = "해당 메뉴 카테고리가 없습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            $res->result = storeCategoryMenu($categoryIdx);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;


        case "storeWishButton":
            http_response_code(200);
            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            $data = getDataByJWToken($jwt, JWT_SECRET_KEY);
            $userEmail = $data->id;

            $userIdx = email_userIdx($userEmail);
            $storeIdx = $vars["storeIdx"];

            if(!isValidStore($storeIdx))
            {
                $res->isSuccess = FALSE;
                $res->code = 209;
                $res->message = "해당 가게정보가 없습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            if(isWishedStore($userIdx,$storeIdx)=='1')
            {
                if(isWishedStatus($userIdx,$storeIdx)=='L')
                {
                    offWishedButton($userIdx,$storeIdx);
                    $res->isSuccess = TRUE;
                    $res->code = 100;
                    $res->message = "찜 취소";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }
                else{
                    onWishedButtonBeforeOn($userIdx,$storeIdx);

                    $res->isSuccess = TRUE;
                    $res->code = 100;
                    $res->message = "찜 성공";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }
            }
            else{
                onWishedButton($userIdx,$storeIdx);

                $res->isSuccess = TRUE;
                $res->code = 100;
                $res->message = "찜 취소";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;

            }

        case "detailInfo":
            http_response_code(200);
            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            $data = getDataByJWToken($jwt, JWT_SECRET_KEY);
            $userEmail = $data->id;

            $userIdx = email_userIdx($userEmail);
            $storeIdx = $vars["storeIdx"];

            if(!isValidStore($storeIdx))
            {
                $res->isSuccess = FALSE;
                $res->code = 209;
                $res->message = "해당 가게정보가 없습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            $res->result = detailInfo($storeIdx);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;


        case "storeMenuDetail":
            http_response_code(200);
            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            $data = getDataByJWToken($jwt, JWT_SECRET_KEY);
            $userEmail = $data->id;

            $userIdx = email_userIdx($userEmail);
            $storeIdx = $vars["storeIdx"];
            $menuIdx = $vars["menuIdx"];

            if(!isValidStore($storeIdx))
            {
                $res->isSuccess = FALSE;
                $res->code = 209;
                $res->message = "해당 가게정보가 없습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if(!isValidStoreMenu($storeIdx,$menuIdx))
            {
                $res->isSuccess = FALSE;
                $res->code = 219;
                $res->message = "해당 메뉴정보가 없습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            $res->result = storeMenuDetail($menuIdx);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "getCart":
            http_response_code(200);

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            $data = getDataByJWToken($jwt, JWT_SECRET_KEY);
            $userEmail = $data->id;

            if($data == null)
            {
                $res->isSuccess = TRUE;
                $res->code = 210;
                $res->message = "로그인 후 이용해 주세요";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            $userIdx = email_userIdx($userEmail);

            if(!isExistCart($userIdx))
            {
                $res->isSuccess = TRUE;
                $res->code = 100;
                $res->message = "성공";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            $res->result = getCart($userIdx);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;


        case "postCartMenu":
            http_response_code(200);

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            $data = getDataByJWToken($jwt, JWT_SECRET_KEY);
            $userEmail = $data->id;

            if($data == null)
            {
                $res->isSuccess = TRUE;
                $res->code = 210;
                $res->message = "로그인 후 이용해 주세요";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            $userIdx = email_userIdx($userEmail);
            $beforeStoreIdx= getStoreIdx($userIdx);

            $storeIdx = $vars["storeIdx"];
            $menuIdx = $vars["menuIdx"];

            if(!isValidStore($storeIdx))
            {
                $res->isSuccess = FALSE;
                $res->code = 209;
                $res->message = "해당 가게정보가 없습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if(!isValidStoreMenu($storeIdx,$menuIdx))
            {
                $res->isSuccess = FALSE;
                $res->code = 219;
                $res->message = "해당 메뉴정보가 없습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if(isExistCart($userIdx) == 1 && $beforeStoreIdx != $storeIdx)
            {
                $res->isSuccess = FALSE;
                $res->code = 263;
                $res->message = "가게가 다른 메뉴는 장바구니에 있을 수 없습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            $countMenu = $req -> count;

            $optionCnt = howManyOption($menuIdx);

            if(!isExistCart($userIdx))
            {
                postCartIdx($storeIdx,$userIdx);
            }

            $cartIdx = getCartIdx($storeIdx,$userIdx);

            // 카트메뉴에 수량 등록
            postCartMenu($cartIdx,$menuIdx,$countMenu);
            if($optionCnt == 0)
            {
                $res->isSuccess = TRUE;
                $res->code = 100;
                $res->message = "성공";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            // 메뉴 옵션 등록 안된 카트 메뉴 idx 번호 확인
            $cartMenuIdx = getCartMenuIdx($cartIdx,$menuIdx,$countMenu);
            // 카트 메뉴 옵션 번호 등록
            postMenuOptionIdx($cartIdx,$cartMenuIdx);

            $menuOptionIdx = getCartMenuOptionIdx($cartIdx,$cartMenuIdx);

            for($i = 0; $i < $optionCnt; $i++)
            {
                $option[$i] = $req->option[$i];
                menuOptionRegister($menuOptionIdx,$option[$i]);
            }

            updateMenuOptionIdx($menuOptionIdx,$cartIdx,$cartMenuIdx);

            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "patchCartCount":
            http_response_code(200);

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            $data = getDataByJWToken($jwt, JWT_SECRET_KEY);
            $userEmail = $data->id;

            $userIdx = email_userIdx($userEmail);
            $cartMenuIdx = $req -> cartMenuIdx;
            $countMenu = $req->count;
            $cartIdx = getCartIdx2($userIdx);
            if(!isExistCart($userIdx))
            {
                $res->isSuccess = FALSE;
                $res->code = 220;
                $res->message = "장바구니가 비었습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if(!isExistCartMenuIdx($cartIdx,$cartMenuIdx))
            {
                $res->isSuccess = FALSE;
                $res->code = 262;
                $res->message = "해당메뉴가 장바구니에 없습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            patchCartCount($countMenu,$cartMenuIdx);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "deleteCartMenu":
            http_response_code(200);

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            $data = getDataByJWToken($jwt, JWT_SECRET_KEY);
            $userEmail = $data->id;

            $userIdx = email_userIdx($userEmail);
            $cartMenuIdx = $vars["cartMenuIdx"];
            $cartIdx = getCartIdx2($userIdx);
            if(!isExistCart($userIdx))
            {
                $res->isSuccess = FALSE;
                $res->code = 220;
                $res->message = "장바구니가 비었습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if(!isExistCartMenuIdx($cartIdx,$cartMenuIdx))
            {
                $res->isSuccess = FALSE;
                $res->code = 262;
                $res->message = "해당메뉴가 장바구니에 없습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            deleteCartMenu($cartMenuIdx);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "deleteCart":
            http_response_code(200);

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            $data = getDataByJWToken($jwt, JWT_SECRET_KEY);
            $userEmail = $data->id;

            $userIdx = email_userIdx($userEmail);

            if(!isExistCart($userIdx))
            {
                $res->isSuccess = FALSE;
                $res->code = 220;
                $res->message = "장바구니가 비었습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            deleteCart($userIdx);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "장바구니 삭제 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "postOrder":
            http_response_code(200);

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            $data = getDataByJWToken($jwt, JWT_SECRET_KEY);
            $userEmail = $data->id;

            $userIdx = email_userIdx($userEmail);
            if(!isExistCart($userIdx))
            {
                $res->isSuccess = FALSE;
                $res->code = 220;
                $res->message = "장바구니가 비었습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            $cartIdx = userIdx_cartIdx($userIdx);
            $menuCnt = getMenuCnt($cartIdx);
            $storeIdx = getStoreIdx($userIdx);
            // orderList에 추가
            $toMaster = $req -> toMaster;
            $payMethod = $req -> payMethod;
            $safeDelivery = $req ->safeDelivery;
            $noSpoon = $req -> noSpoon;

            if($payMethod == null)
            {
                $res->isSuccess = FALSE;
                $res->code = 221;
                $res->message = "결제방법을 선택하여 주세요.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            postOrder($userIdx,$storeIdx,$toMaster,$payMethod,$safeDelivery,$noSpoon);

            $orderIdx = getOrderIdx($userIdx);
            // cartMenuIdx값
//            echo implode("",getOrderCartMenuIdx($cartIdx)[0]);
            for($i = 0;$i < $menuCnt; $i++)
            {
                $menuOptionIdx = getCartMenuMenuOptionIdx($cartIdx,implode("",getOrderCartMenuIdx($cartIdx)[$i]));
                $menuIdx = getCartMenuMenuIdx($cartIdx,implode("",getOrderCartMenuIdx($cartIdx)[$i]));
                $count = getCartMenuCount($cartIdx,implode("",getOrderCartMenuIdx($cartIdx)[$i]));
                insertOrderMenuList(implode("",getOrderCartMenuIdx($cartIdx)[$i]),$menuOptionIdx,$orderIdx,$menuIdx,$count);
            }

            deleteCart($userIdx);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "getOrderList":
            http_response_code(200);

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            $data = getDataByJWToken($jwt, JWT_SECRET_KEY);
            $userEmail = $data->id;

            if($data == null)
            {
                $res->isSuccess = TRUE;
                $res->code = 210;
                $res->message = "로그인 후 이용해 주세요";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            $userIdx = email_userIdx($userEmail);

            if(!isExistOrder($userIdx))
            {
                $res->isSuccess = TRUE;
                $res->code = 100;
                $res->message = "성공";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            $res->result = getOrderList($userIdx);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "getOrderListDetail":
            http_response_code(200);

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            $data = getDataByJWToken($jwt, JWT_SECRET_KEY);
            $userEmail = $data->id;

            $userIdx = email_userIdx($userEmail);
            $orderIdx = $vars["orderIdx"];
            if(!isExistOrder($userIdx))
            {
                $res->isSuccess = FALSE;
                $res->code = 222;
                $res->message = "주문내역이 없습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if(!isExistOrderDetail($orderIdx))
            {
                $res->isSuccess = FALSE;
                $res->code = 223;
                $res->message = "해당 주문 내역이 없습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            $res->result = getOrderListDetail($orderIdx);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "deleteOrderListDetail":
            http_response_code(200);

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            $data = getDataByJWToken($jwt, JWT_SECRET_KEY);
            $userEmail = $data->id;

            $userIdx = email_userIdx($userEmail);
            $orderIdx = $vars["orderIdx"];

            if(!isExistOrder($userIdx))
            {
                $res->isSuccess = FALSE;
                $res->code = 222;
                $res->message = "주문내역이 없습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if(!isExistOrderDetail($orderIdx))
            {
                $res->isSuccess = FALSE;
                $res->code = 223;
                $res->message = "해당 주문 내역이 없습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            deleteOrderListDetail($orderIdx);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "postStoreCategory":
            http_response_code(200);
            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            $data = getDataByJWToken($jwt, JWT_SECRET_KEY);
            $userEmail = $data->id;

            $userIdx = email_userIdx($userEmail);
            $storeIdx = $vars["storeIdx"];

            $categoryName = $req->categoryName;
            $masterIdx = getMasterIdx($storeIdx);
            if(!isValidStore($storeIdx))
            {
                $res->isSuccess = FALSE;
                $res->code = 209;
                $res->message = "해당 가게정보가 없습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if($masterIdx != $userIdx)
            {
                $res->isSuccess = FALSE;
                $res->code = 267;
                $res->message = "해당 가게 사장님만 이용할 수 있습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if($categoryName == NULL)
            {
                $res->isSuccess = FALSE;
                $res->code = 266;
                $res->message = "카테고리 이름은 비울 수 없습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            postStoreCategory($storeIdx,$categoryName);

            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;


        case "deleteStoreCategory":
            http_response_code(200);
            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            $data = getDataByJWToken($jwt, JWT_SECRET_KEY);
            $userEmail = $data->id;

            $userIdx = email_userIdx($userEmail);
            $storeIdx = $vars["storeIdx"];

            $menuCategoryIdx = $vars["menuCategoryIdx"];
            $masterIdx = getMasterIdx($storeIdx);
            if(!isValidStore($storeIdx))
            {
                $res->isSuccess = FALSE;
                $res->code = 209;
                $res->message = "해당 가게정보가 없습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if($masterIdx != $userIdx)
            {
                $res->isSuccess = FALSE;
                $res->code = 267;
                $res->message = "해당 가게 사장님만 이용할 수 있습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if(!isExistMenuCategory($storeIdx,$menuCategoryIdx))
            {
                $res->isSuccess = FALSE;
                $res->code = 268;
                $res->message = "해당 카테고리가 없습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            deleteStoreCategory($menuCategoryIdx);

            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "postMenu":
            http_response_code(200);
            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            $data = getDataByJWToken($jwt, JWT_SECRET_KEY);
            $userEmail = $data->id;

            $userIdx = email_userIdx($userEmail);
            $storeIdx = $vars["storeIdx"];

            $menuCategoryIdx= $req->menuCategoryIdx;
            $contents = $req->contents;
            $photoUrl = $req->photoUrl;
            $menuName = $req->menuName;
            $price = $req->price;
            $isRepresent = $req->isRepresent;

            $masterIdx = getMasterIdx($storeIdx);
            if(!isValidStore($storeIdx))
            {
                $res->isSuccess = FALSE;
                $res->code = 209;
                $res->message = "해당 가게정보가 없습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if($masterIdx != $userIdx)
            {
                $res->isSuccess = FALSE;
                $res->code = 267;
                $res->message = "해당 가게 사장님만 이용할 수 있습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if($menuName == NULL)
            {
                $res->isSuccess = FALSE;
                $res->code = 269;
                $res->message = "메뉴 이름은 비울 수 없습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if($menuCategoryIdx == NULL)
            {
                $res->isSuccess = FALSE;
                $res->code = 270;
                $res->message = "카테고리는 비울 수 없습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if($price == NULL)
            {
                $res->isSuccess = FALSE;
                $res->code = 271;
                $res->message = "가격은 비울 수 없습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            postMenu($menuCategoryIdx,$storeIdx,$contents,$photoUrl,$menuName,$price,$isRepresent);

            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "deleteMenu":
            http_response_code(200);
            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            $data = getDataByJWToken($jwt, JWT_SECRET_KEY);
            $userEmail = $data->id;

            $userIdx = email_userIdx($userEmail);
            $storeIdx = $vars["storeIdx"];

            $menuIdx = $vars["menuIdx"];
            $masterIdx = getMasterIdx($storeIdx);
            if(!isValidStore($storeIdx))
            {
                $res->isSuccess = FALSE;
                $res->code = 209;
                $res->message = "해당 가게정보가 없습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if($masterIdx != $userIdx)
            {
                $res->isSuccess = FALSE;
                $res->code = 267;
                $res->message = "해당 가게 사장님만 이용할 수 있습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if(!isExistMenu($storeIdx,$menuIdx))
            {
                $res->isSuccess = FALSE;
                $res->code = 272;
                $res->message = "해당 메뉴가 없습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            deleteMenu($menuIdx);

            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;


    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}
