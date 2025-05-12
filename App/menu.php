<?php
require_once 'sms.php';
require_once 'db.php';
require_once 'util.php';

class Menu {
    protected $text;
    protected $sessionId;
    protected $phoneNumber;
    protected $conn;

    function __construct($text, $sessionId, $phoneNumber, $conn) {
        $this->text = $text;
        $this->sessionId = $sessionId;
        $this->phoneNumber = $phoneNumber;
        $this->conn = $conn;
    }

    public function mainMenuUnregistered() {
        echo " Welcome to MTN MOMO\n1. Register User\n2. Register Agent\n0. Exit";
    }

    public function menuRegister($textArray) {
        $level = count($textArray);
    
        if ($level == 1) {
            echo "CON Enter your full User name";
        } elseif ($level == 2) {
            echo "CON Enter your PIN";
        } elseif ($level == 3) {
            echo "CON Re-enter your PIN";
        } elseif ($level == 4) {
            $name = trim($textArray[1]);
            $pin = trim($textArray[2]);
            $confirmPin = trim($textArray[3]);
     
            // Check if PINs match
            if ($pin !== $confirmPin) {
                echo "END PINs do not match. Please try again.";
                return;
            }
    
            
            $stmt = $this->conn->prepare("SELECT * FROM users WHERE phone_number = ?");
            $stmt->execute([$this->phoneNumber]);
            if ($stmt->rowCount() > 0) {
                echo "END This phone number is already registered.";
                return;
            }
    
            // Hash PIN and insert into database
            
            $hashedPin = password_hash($pin, PASSWORD_DEFAULT);
            $stmt = $this->conn->prepare("INSERT INTO users (phone_number, full_name, pin, balance) VALUES (?, ?, ?, 500)");
            if ($stmt->execute([$this->phoneNumber, $name, $hashedPin])) {
          
            echo "END Dear $name, you have successfully registered.Initial balance Account is:".Util::$user_balance."Rwf";
            } else {
                echo "END Registration failed. Please try again.";
            }
        } else {
            echo "END Invalid input. Please try again.";
        }
    }

    
    public function RegisterAgent($textArray){
    $level = count($textArray);

    if ($level == 1) {
        echo "CON Enter Your Full Agent Name:"; // Use "CON" to prompt next input
        return;
    } elseif ($level == 2) {
        echo "CON Enter Agent Code:";
        return;
    } elseif ($level == 3) {
        $name = trim($textArray[1]);
        $Code = trim($textArray[2]);

        // Check if phone is already registered
        $stmt = $this->conn->prepare("SELECT * FROM agents WHERE phone_number = ?");
        $stmt->execute([$this->phoneNumber]);

        if ($stmt->rowCount() > 0) {
            echo "END This phone number is already registered.";
            return;
        }

        // Register new agent
        $stmt = $this->conn->prepare("INSERT INTO agents (phone_number, full_name, Agent_Code, balance) VALUES (?, ?, ?, 500)");
        if ($stmt->execute([$this->phoneNumber, $name, $Code])) {
            echo "END Dear $name, you have successfully registered. Your Code is: $Code. Thank you!";
        } else {
            echo "END Registration failed. Please try again.";
        }
    }
   }


    

    public function mainMenuRegistered() {
        echo "CON Welcome back to XYZ MOMO\n1. Send Money\n2. Withdraw Money\n3. Check Balance\n4. Deposit Money\n0. Exit";
    }

    public function menuSendMoney($textArray) {
        $level = count($textArray);

        if ($level == 1) {
            echo "CON Enter recipient phone number\n0. Go Back";
        } elseif ($level == 2) {
            if ($textArray[1] == "0") {
                echo "END Going back to main menu";
                return;
            }
            echo "CON Enter amount\n0. Go Back";
        } elseif ($level == 3) {
            if ($textArray[2] == "0") {
                echo "CON Enter recipient phone number\n0. Go Back";
                return;
            }
            echo "CON Enter PIN\n0. Go Back";
        } elseif ($level == 4) {
            if ($textArray[3] == "0") {
                echo "CON Enter amount\n0. Go Back";
                return;
            }
            $response="CON Do you want to send Amount of $textArray[2]RWF to $textArray[1]?\n";
            $response .="1.Confirm\n";
            $response .="2.Cancel\n";
            $response .="0.Go Back\n";
            echo $response;

            list(, $recipient, $amount, $pin) = $textArray;

            $stmt = $this->conn->prepare("SELECT * FROM users WHERE phone_number = ?");
            $stmt->execute([$this->phoneNumber]);
            $sender = $stmt->fetch();

            if (!$sender || !password_verify($pin, $sender['pin'])) {
                echo "END Incorrect PIN.";
                return;
            }

            $stmt = $this->conn->prepare("SELECT * FROM users WHERE phone_number = ?");
            $stmt->execute([$recipient]);
            $receiver = $stmt->fetch();

            if (!$receiver) {
                echo "END Recipient does not exist.";
                return;
            }

            if ($sender['balance'] < $amount) {
                echo "END Insufficient balance.";
                return;
            }

            $this->conn->beginTransaction();

            $this->conn->prepare("UPDATE users SET balance = balance - ? WHERE phone_number = ?")
                ->execute([$amount, $this->phoneNumber]);

            $this->conn->prepare("UPDATE users SET balance = balance + ? WHERE phone_number = ?")
                ->execute([$amount, $recipient]);

            $this->conn->prepare("INSERT INTO transactions (sender_phone, recipient_phone, amount, transaction_type) VALUES (?, ?, ?, 'SEND')")
                ->execute([$this->phoneNumber, $recipient, $amount]);

            $this->conn->commit();

            // Send SMS to sender
            $sms = new Sms();
            $senderMessage = "You have sent $amount Rwf to $receiver[full_name] ($recipient). Your new balance is " . ($sender['balance'] - $amount) . " Rwf.";
            $sms->sendSMS($senderMessage, $this->phoneNumber);

            // Send SMS to recipient
            $recipientMessage = "You have received $amount Rwf from $sender[full_name] ($this->phoneNumber). Your new balance is " . ($receiver['balance'] + $amount) . " Rwf.";
            $sms->sendSMS($recipientMessage, $recipient);
         }
      
   elseif ($level == 5 && $textArray[4] == "1") {

    $recipientPhone = trim($textArray[1]);
    $amount = trim($textArray[2]);

    $stmt = $this->conn->prepare("SELECT full_name FROM users WHERE phone_number = ?");
    $stmt->execute([$recipientPhone]);

    if ($stmt->rowCount() > 0) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $recipientName = $row['full_name'];
        echo "END You have sent $amount Rwf to $recipientName ($recipientPhone) successfully.";
    } 
}

elseif($level== 5 && $textArray[4]==2){
        echo"Thank you for using Service";
        }
        elseif($level== 5 && $textArray[4]=="0. Go Back"){
            echo"END Your are requesting to go back one step";
        }
        elseif($level== 5 && $textArray[4]=="99. Main menu"){
            echo"End your are requestin to go menu";
        }
        else{
            echo"Invalid Option";
        }
    }



    public function menuCheckBalance($textArray) {
        $level = count($textArray);

        if ($level == 1) {
            echo "CON Enter your PIN\n0. Go Back";
        } elseif ($level == 2) {
            if ($textArray[1] == "0") {
                echo "END Going back to main menu";
                return;
            }
            $pin = trim($textArray[1]);

            $stmt = $this->conn->prepare("SELECT * FROM users WHERE phone_number = ?");
            $stmt->execute([$this->phoneNumber]);
            $user = $stmt->fetch();

            if (!$user || !password_verify($pin, $user['pin'])) {
                echo "END Incorrect PIN.";
                return;
            }

            // Send SMS with balance
            $sms = new Sms();
            $message = "Your current balance is " . $user['balance'] . " Rwf.";
            $sms->sendSMS($message, $this->phoneNumber);

            echo "END Your balance is " . $user['balance'] . " Rwf.";
        }
    }



    public function menuWithdrawMoney($textArray) {
        $level = count($textArray);

        if ($level == 1) {
            echo "CON Enter agent code\n0. Go Back";
        } elseif ($level == 2) {
            if ($textArray[1] == "0") {
                echo "END Going back to main menu";
                return;
            }
            echo "CON Enter amount\n0. Go Back";
        } elseif ($level == 3) {
            if ($textArray[2] == "0") {
                echo "CON Enter agent code\n0. Go Back";
                return;
            }
            echo "CON Enter PIN\n0. Go Back";
        } elseif ($level == 4) {
            if ($textArray[3] == "0") {
                echo "CON Enter amount\n0. Go Back";
                return;
            }
            $agentCode = trim($textArray[1]);
            $amount = trim($textArray[2]);
            $pin = trim($textArray[3]);

            // Verify user PIN
            $stmt = $this->conn->prepare("SELECT * FROM users WHERE phone_number = ?");
            $stmt->execute([$this->phoneNumber]);
            $user = $stmt->fetch();

            if (!$user || !password_verify($pin, $user['pin'])) {
                echo "END Incorrect PIN.";
                return;
            }

            // Check agent exists by code
            $stmt = $this->conn->prepare("SELECT * FROM agents WHERE Agent_Code = ?");
            $stmt->execute([$agentCode]);
            $agent = $stmt->fetch();

            if (!$agent) {
                echo "END Invalid agent code.";
                return;
            }

            if ($user['balance'] < $amount) {
                echo "END Insufficient balance.";
                return;
            }

            $this->conn->beginTransaction();

            // Update user balance
            $this->conn->prepare("UPDATE users SET balance = balance - ? WHERE phone_number = ?")
                ->execute([$amount, $this->phoneNumber]);

            // Update agent balance
            $this->conn->prepare("UPDATE agents SET balance = balance + ? WHERE Agent_Code = ?")
                ->execute([$amount, $agentCode]);

            // Record transaction
            $this->conn->prepare("INSERT INTO transactions (sender_phone, amount, transaction_type, agent_phone) VALUES (?, ?, 'WITHDRAW', ?)")
                ->execute([$this->phoneNumber, $amount, $agent['phone_number']]);

            $this->conn->commit();

            // Send SMS to user
            $sms = new Sms();
            $userMessage = "You have withdrawn $amount Rwf from agent $agent[full_name] (Code: $agentCode). Your new balance is " . ($user['balance'] - $amount) . " Rwf.";
            $sms->sendSMS($userMessage, $this->phoneNumber);

            // Send SMS to agent
            $agentMessage = "User $user[full_name] has withdrawn $amount Rwf. Your new balance is " . ($agent['balance'] + $amount) . " Rwf.";
            $sms->sendSMS($agentMessage, $agent['phone_number']);

            echo "END You have successfully withdrawn $amount Rwf from agent $agent[full_name] (Code: $agentCode).";
        }
    }

    public function menuDepositMoney($textArray) {
        $level = count($textArray);

        if ($level == 1) {
            echo "CON Enter agent code\n0. Go Back";
        } elseif ($level == 2) {
            if ($textArray[1] == "0") {
                echo "END Going back to main menu";
                return;
            }
            echo "CON Enter amount\n0. Go Back";
        } elseif ($level == 3) {
            if ($textArray[2] == "0") {
                echo "CON Enter agent code\n0. Go Back";
                return;
            }
            echo "CON Enter PIN\n0. Go Back";
        } elseif ($level == 4) {
            if ($textArray[3] == "0") {
                echo "CON Enter amount\n0. Go Back";
                return;
            }
            $agentCode = trim($textArray[1]);
            $amount = trim($textArray[2]);
            $pin = trim($textArray[3]);

            // Verify user PIN
            $stmt = $this->conn->prepare("SELECT * FROM users WHERE phone_number = ?");
            $stmt->execute([$this->phoneNumber]);
            $user = $stmt->fetch();

            if (!$user || !password_verify($pin, $user['pin'])) {
                echo "END Incorrect PIN.";
                return;
            }

            // Check agent exists by code
            $stmt = $this->conn->prepare("SELECT * FROM agents WHERE Agent_Code = ?");
            $stmt->execute([$agentCode]);
            $agent = $stmt->fetch();

            if (!$agent) {
                echo "END Invalid agent code.";
                return;
            }

            if ($agent['balance'] < $amount) {
                echo "END Agent has insufficient balance.";
                return;
            }

            $this->conn->beginTransaction();

            // Update user balance
            $this->conn->prepare("UPDATE users SET balance = balance + ? WHERE phone_number = ?")
                ->execute([$amount, $this->phoneNumber]);

            // Update agent balance
            $this->conn->prepare("UPDATE agents SET balance = balance - ? WHERE Agent_Code = ?")
                ->execute([$amount, $agentCode]);

            // Record transaction
            $this->conn->prepare("INSERT INTO transactions (recipient_phone, amount, transaction_type, agent_phone) VALUES (?, ?, 'DEPOSIT', ?)")
                ->execute([$this->phoneNumber, $amount, $agent['phone_number']]);

            $this->conn->commit();

            // Send SMS to user
            $sms = new Sms();
            $userMessage = "You have deposited $amount Rwf through agent $agent[full_name] (Code: $agentCode). Your new balance is " . ($user['balance'] + $amount) . " Rwf.";
            $sms->sendSMS($userMessage, $this->phoneNumber);

            // Send SMS to agent
            $agentMessage = "User $user[full_name] has deposited $amount Rwf. Your new balance is " . ($agent['balance'] - $amount) . " Rwf.";
            $sms->sendSMS($agentMessage, $agent['phone_number']);

            echo "END You have successfully deposited $amount Rwf through agent $agent[full_name] (Code: $agentCode).";
        }
    }

public function middleware($text){
    return $this->goBack($this->goBackMenu($text));

    }
    
public function goBack($text){
    $explodedText=explode("*",$text);
    while(array_search('98',$explodedText)!=false){
        $firstIndex=array_search('98',$explodedText);
        array_splice($explodedText,$firstIndex-1,2);
    }
  return join("*",$explodedText);
}


public function goBackMenu($text){
    $explodedText=explode("*",$text);
    while(array_search('99',$explodedText)!=false){
        $firstIndex=array_search('99',$explodedText);
        $explodedText = array_slice($explodedText,$firstIndex+1);
        
    }
  return join("*",$explodedText);
}
}
?>
