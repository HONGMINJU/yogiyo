<?php
<<<<<<< HEAD
=======
require './pdos/UtilityPdo.php';
<<<<<<< HEAD
>>>>>>> 192fe8be3d89930e0347f92a86187b1a0f7b82ac
=======
require './pdos/StorePdo.php';
require './pdos/ReviewPdo.php';
>>>>>>> 5e5d52175e56436b9844242ba6186ada7f331795
require './pdos/DatabasePdo.php';
require './pdos/IndexPdo.php';
require './vendor/autoload.php';


use \Monolog\Logger as Logger;
use Monolog\Handler\StreamHandler;

date_default_timezone_set('Asia/Seoul');
ini_set('default_charset', 'utf8mb4');

//에러출력하게 하는 코드
<<<<<<< HEAD
<<<<<<< HEAD
//error_reporting(E_ALL); ini_set("display_errors", 1);
=======
error_reporting(E_ALL); ini_set("display_errors", 1);
>>>>>>> 192fe8be3d89930e0347f92a86187b1a0f7b82ac
=======
//error_reporting(E_ALL); ini_set("display_errors", 1);
>>>>>>> 5e5d52175e56436b9844242ba6186ada7f331795

//Main Server API
$dispatcher = FastRoute\simpleDispatcher(function (FastRoute\RouteCollector $r) {

<<<<<<< HEAD
<<<<<<< HEAD
=======
    $r->addRoute('POST', '/user', ['IndexController', 'createUser']);
    $r->addRoute('DELETE', '/user', ['IndexController', 'deleteUser']);


>>>>>>> 192fe8be3d89930e0347f92a86187b1a0f7b82ac
    $r->addRoute('GET', '/hi', ['IndexController', 'index']);
=======


    $r->addRoute('DELETE', '/searchKeyword', ['StoreController', 'deleteAllKeyword']);
    $r->addRoute('DELETE', '/searchKeyword/{KeywordIdx}', ['StoreController', 'deleteSomeKeyword']);
    $r->addRoute('DELETE', '/review/{reviewIdx}', ['ReviewController', 'deleteReview']);
    $r->addRoute('DELETE', '/review/{reviewIdx}/masterComment', ['ReviewController', 'deleteMasterComment']);
>>>>>>> 5e5d52175e56436b9844242ba6186ada7f331795


    $r->addRoute('GET', '/category/{categoryIdx}', ['StoreController', 'getStores']);
    $r->addRoute('GET', '/store/heart', ['StoreController', 'getHeartStores']);
    $r->addRoute('GET', '/store/{storeIdx}/review', ['ReviewController', 'getReview']);
    $r->addRoute('GET', '/stores', ['StoreController', 'getAllStores']);
    $r->addRoute('GET', '/categories', ['StoreController', 'getCategories']);
    $r->addRoute('GET', '/search', ['StoreController', 'searchStore']);
    $r->addRoute('GET', '/searchKeyword', ['StoreController', 'getSearchKeyword']);
    $r->addRoute('GET', '/myReview', ['ReviewController', 'getMyReview']);
    $r->addRoute('GET', '/advertises', ['IndexController', 'getAdvertise']);


    $r->addRoute('POST', '/coupon', ['IndexController', 'postCoupon']);
    $r->addRoute('POST', '/levelCoupon', ['IndexController', 'postLevelCoupon']);
    $r->addRoute('POST', '/logIn', ['MainController', 'logIn']);
    $r->addRoute('POST', '/review/{reviewIdx}/masterComment', ['ReviewController', 'createMasterComment']);

    $r->addRoute('PATCH', '/review/{reviewIdx}/reviewLike', ['ReviewController', 'createReviewLike']);
    $r->addRoute('PATCH', '/store/{storeIdx}/minDeliveryTime', ['StoreController', 'patchMinDeliveryTime']);
    $r->addRoute('PATCH', '/store/{storeIdx}/maxDeliveryTime', ['StoreController', 'patchMaxDeliveryTime']);
    $r->addRoute('PATCH', '/store/{storeIdx}/minPrice', ['StoreController', 'patchMinPrice']);
    $r->addRoute('PATCH', '/review/{reviewIdx}/masterComment', ['ReviewController', 'patchMasterComment']);
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    $r->addRoute('POST', '/user', ['IndexController', 'createUser']);
    $r->addRoute('DELETE', '/user', ['IndexController', 'deleteUser']);
    $r->addRoute('GET', '/user', ['IndexController', 'getUser']);
    $r->addRoute('GET', '/user/detail', ['IndexController', 'getUserDetail']);
    $r->addRoute('PATCH', '/user/nickName', ['IndexController', 'patchNickName']);
    $r->addRoute('PATCH', '/user/phoneNum', ['IndexController', 'patchPhoneNum']);

    $r->addRoute('GET', '/store/{storeIdx}', ['StoreController', 'storeInfo']);
    $r->addRoute('GET', '/store/{storeIdx}/bestMenu', ['StoreController', 'storeBestMenu']);
    $r->addRoute('GET', '/store/{storeIdx}/category', ['StoreController', 'storeCategory']);
    $r->addRoute('GET', '/store/{storeIdx}/category/{categoryIdx}', ['StoreController', 'storeCategoryClick']);
    $r->addRoute('GET', '/store/{storeIdx}/heart', ['StoreController', 'storeWishButton']);
    $r->addRoute('GET', '/store/{storeIdx}/storeDetail', ['StoreController', 'detailInfo']);

    $r->addRoute('GET', '/store/{storeIdx}/menu/{menuIdx}', ['StoreController', 'storeMenuDetail']);

    // 장바구니 보기
    $r->addRoute('GET', '/user/cart', ['StoreController', 'getCart']);
    // 주문표에 넣기
    $r->addRoute('POST', '/store/{storeIdx}/menu/{menuIdx}', ['StoreController', 'postCartMenu']);
    // 장바구니 수량 변경
    $r->addRoute('PATCH', '/user/cart/count', ['StoreController', 'patchCartCount']);
    // 카트에서 메뉴 삭제
    $r->addRoute('DELETE', '/user/cart/{cartMenuIdx}', ['StoreController', 'deleteCartMenu']);
    // 장바구니 전체 삭제
    $r->addRoute('DELETE', '/user/cart', ['StoreController', 'deleteCart']);
    // 주문하기
    $r->addRoute('POST', '/user/cart/order', ['StoreController', 'postOrder']);

    // 주문목록 확인하기
    $r->addRoute('GET', '/user/orderList', ['StoreController', 'getOrderList']);
    // 주문 상세 내역 조회
    $r->addRoute('GET', '/user/orderList/{orderIdx}', ['StoreController', 'getOrderListDetail']);
    // 주문 내역 삭제하기
    $r->addRoute('DELETE', '/user/orderList/{orderIdx}', ['StoreController', 'deleteOrderListDetail']);

    $r->addRoute('POST', '/store/{storeIdx}/category', ['StoreController', 'postStoreCategory']);
    $r->addRoute('DELETE', '/store/{storeIdx}/category/{menuCategoryIdx}', ['StoreController', 'deleteStoreCategory']);
    $r->addRoute('POST', '/store/{storeIdx}/menu', ['StoreController', 'postMenu']);
    $r->addRoute('DELETE', '/store/{storeIdx}/menu/{menuIdx}', ['StoreController', 'deleteMenu']);

//    $r->addRoute('GET', '/users', 'get_all_users_handler');
//    // {id} must be a number (\d+)
//    $r->addRoute('GET', '/user/{id:\d+}', 'get_user_handler');
//    // The /{title} suffix is optional
//    $r->addRoute('GET', '/articles/{id:\d+}[/{title}]', 'get_article_handler');
});

// Fetch method and URI from somewhere
$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

// Strip query string (?foo=bar) and decode URI
if (false !== $pos = strpos($uri, '?')) {
    $uri = substr($uri, 0, $pos);
}
$uri = rawurldecode($uri);

$routeInfo = $dispatcher->dispatch($httpMethod, $uri);

// 로거 채널 생성
$accessLogs = new Logger('ACCESS_LOGS');
$errorLogs = new Logger('ERROR_LOGS');
// log/your.log 파일에 로그 생성. 로그 레벨은 Info
$accessLogs->pushHandler(new StreamHandler('logs/access.log', Logger::INFO));
$errorLogs->pushHandler(new StreamHandler('logs/errors.log', Logger::ERROR));
// add records to the log
//$log->addInfo('Info log');
// Debug 는 Info 레벨보다 낮으므로 아래 로그는 출력되지 않음
//$log->addDebug('Debug log');
//$log->addError('Error log');

switch ($routeInfo[0]) {
    case FastRoute\Dispatcher::NOT_FOUND:
        // ... 404 Not Found
        echo "404 Not Found";
        break;
    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        $allowedMethods = $routeInfo[1];
        // ... 405 Method Not Allowed
        echo "405 Method Not Allowed";
        break;
    case FastRoute\Dispatcher::FOUND:
        $handler = $routeInfo[1];
        $vars = $routeInfo[2];

        switch ($routeInfo[1][0]) {
            case 'IndexController':
                $handler = $routeInfo[1][1];
                $vars = $routeInfo[2];
                require './controllers/IndexController.php';
                break;
            case 'MainController':
                $handler = $routeInfo[1][1];
                $vars = $routeInfo[2];
                require './controllers/MainController.php';
                break;
            case 'StoreController':
                $handler = $routeInfo[1][1];
                $vars = $routeInfo[2];
                require './controllers/StoreController.php';
                break;
            case 'ReviewController':
                $handler = $routeInfo[1][1];
                $vars = $routeInfo[2];
                require './controllers/ReviewController.php';
                break;
            /*
        case 'ProductController':
            $handler = $routeInfo[1][1]; $vars = $routeInfo[2];
            require './controllers/ProductController.php';
            break;
        case 'SearchController':
            $handler = $routeInfo[1][1]; $vars = $routeInfo[2];
            require './controllers/SearchController.php';
            break;
        case 'ReviewController':
            $handler = $routeInfo[1][1]; $vars = $routeInfo[2];
            require './controllers/ReviewController.php';
            break;
        case 'ElementController':
            $handler = $routeInfo[1][1]; $vars = $routeInfo[2];
            require './controllers/ElementController.php';
            break;
        case 'AskFAQController':
            $handler = $routeInfo[1][1]; $vars = $routeInfo[2];
            require './controllers/AskFAQController.php';
            break;*/
        }

        break;
}
