<?php
require_once 'Menu.php';
require_once 'Util.php';

try {
    $conn = new PDO("mysql:host=" . Util::$host . ";dbname=" . Util::$db, Util::$user, Util::$pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Collect input from Africa's Talking
    $sessionId   = $_POST["sessionId"];
    $serviceCode = $_POST["serviceCode"];
    $phoneNumber = $_POST["phoneNumber"];
    $text        = $_POST["text"];

    // Middleware
    $menu = new Menu($text, $sessionId, $phoneNumber, $conn);

    $text = $menu->middleWare($text);
    
    $textArray = explode("*", $text);

    // Check if user exists
    $stmt = $conn->prepare("SELECT * FROM users WHERE phone_number = ?");
    $stmt->execute([$phoneNumber]);
    $user = $stmt->fetch();

  if (!$user) {
    if ($text == "") {
        $menu->mainMenuUnregistered(); // Show initial menu
    } 
    else {
        $textArray = explode("*", $text);

        // Route based on user's input
        if ($textArray[0] == "1") {
            $menu->menuRegister($textArray);
        } elseif ($textArray[0] == "2") {
            $menu->RegisterAgent($textArray);
        } else {
            echo "END Invalid option. Please try again.";
        }
    }

    }
     
    else {
        switch ($textArray[0]) {
            case "1":
                $menu->menuSendMoney($textArray);
                break;
            case "2":
                $menu->menuWithdrawMoney($textArray);
                break;
            case "3":
                $menu->menuCheckBalance($textArray);
                break;
            case "4":
                $menu->menuDepositMoney($textArray);
                break;
            default:
                $menu->mainMenuRegistered();
                break;
        }
    }
} 
catch (Exception $e) {
    echo "END An error occurred. Please try again.";
}
?>