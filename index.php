<?php

include_once __DIR__ . '/AmoCRMService.php';
include_once __DIR__ . '/AmoCRMHelper.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: *');
header('Access-Control-Allow-Headers: *');

$clientId = 'e0a4ab12-6d12-48e9-b919-b3401ddca436';
$clientSecret = 'LCrX6J2kHIUMQxAExO9lMqnBQkil6quMncccnrHYWrtsO8TntNz7mkokPP7LFlDA';
$redirectUri = 'https://7782-185-107-94-194.ngrok-free.app';

session_start();

$amoCRMService = new AmoCRMService($clientId, $clientSecret, $redirectUri);

$authorization = $amoCRMService->getAuthorization();

if (isset($_GET['referer'])) {
    $authorization->setBaseDomain($_GET['referer']);
}

// Если нет доступа, перенаправляем на страницу авторизации
if (!$authorization->isAuthorised()) {
    if (!isset($_GET['code'])) {
        $authorizationUrl = $authorization->getAuthorizationUrl();
        header('Location: ' . $authorizationUrl);
        echo json_encode($authorizationUrl);
        exit();
    } else {
        $code = $_GET['code'];
        $state = $_GET['state'];
        $accessToken = $authorization->authorize($code, $state, $redirectUri, $clientId, $clientSecret);

        if ($accessToken) {
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit();
        } else {
            echo 'Authorization failed.';
            exit();
        }
    }
}

$authorization->refreshTokenIfNeeded();

// Обработка формы для создания контакта и сделки
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $price = $_POST['lead_price'];
    $leadName = $_POST['lead_name'];

    $formData = AmoCRMHelper::getComplexData($name, $email, $phone, $price, $leadName);

    $amoCRMService->createLeadWithContact($formData);
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Form to AmoCRM</title>
</head>
<body>
<form method="POST" action="">
    <input type="text" name="name" placeholder="Имя"><br>
    <input type="email" name="email" placeholder="Почта"><br>
    <input type="text" name="phone" placeholder="Телефон"><br>
    <input type="text" name="lead_name" placeholder="Название сделки"><br>
    <input type="number" name="lead_price" placeholder="Цена сделки"><br>
    <button type="submit">Submit</button>
</form>
</body>
</html>
