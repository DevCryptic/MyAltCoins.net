<?php
ob_start();
require_once 'includes/config.php';
require_once 'includes/global.php';
if (!($user -> IsLogged($odb)))
{
    header('Location: login.php');
    die();
}

if (isset($_GET['i'])) {//Check if the investment exists.
    $investmentID = $_GET['i'];
    $checkIfExists = $odb -> prepare("SELECT `id`, `userID` FROM `investments` WHERE `id` = :investmentID AND `userID` = :userID");
    $checkIfExists -> execute(array(':investmentID' => $investmentID, ':userID' => $_SESSION['rID']));
    if($checkIfExists -> rowCount() == 0)
    {
        header('location: dashboard.php');
        die();
    }

    $SQLGetInfo = $odb -> prepare("SELECT * FROM `investments` WHERE `id` = :id AND `userID` = :userID LIMIT 1");
    $SQLGetInfo -> execute(array(':id' => $investmentID, ':userID' => $_SESSION['rID']));
    $investmentInfo = $SQLGetInfo -> fetch(PDO::FETCH_ASSOC);
    $date = $investmentInfo['date'];
    $coin = $investmentInfo['coin'];
    $amount = $investmentInfo['amount'];
    $costPerCoin = $investmentInfo['costPerCoin'];
    $userID = $investmentInfo['userID'];
    $cID = $investmentInfo['id'];
    $soldFor = $investmentInfo['soldFor'];
    $dateSold = $investmentInfo['dateSold'];

    $SQLGetUserInfo = $odb -> prepare("SELECT `username` FROM `accounts` WHERE `id` = :id LIMIT 1");
    $SQLGetUserInfo -> execute(array(':id' => $_SESSION['rID']));
    $userInfo = $SQLGetUserInfo -> fetch(PDO::FETCH_ASSOC);
    $userName = $userInfo['username'];

    include ('templates/header.php');

}
?>
<?

               if (isset($_POST['addToLedger'])) {
               	$investmentID =  $_POST['iid'];
                $SQLGetInfo = $odb -> prepare("SELECT * FROM `investments` WHERE `id` = :id AND `userID` = :userID LIMIT 1");
                $SQLGetInfo -> execute(array(':id' => $investmentID, ':userID' => $_SESSION['rID']));
                $investmentInfo = $SQLGetInfo -> fetch(PDO::FETCH_ASSOC);
                $soldFor = $investmentInfo['soldFor'];
                $dateSold = $investmentInfo['dateSold'];
                $amount = $investmentInfo['amount'];
                $totalCost = $investmentInfo['totalCost'];

                $update = false;
                $errors = array();

                if (empty($_POST['soldFor']) || empty($_POST['dateSold']))
                {
                    $errors[] = 'Please ensure all fields are filled.';
                }

                if ($_POST['dateSold'] <=0){
                	$errors[] = 'Sold for can not be less than or equal to 0';
                }

                if (!preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$_POST['dateSold']))
                {
                    $errors[] = 'Invalid Date';
                }  

                if (empty($errors))
                    {
                        if ($dateSold != $_POST['dateSold'])
                        {
                            $SQL = $odb -> prepare("UPDATE `investments` SET `dateSold` = :dateSold WHERE `id` = :id");
                            $SQL -> execute(array(':dateSold' => $_REQUEST['dateSold'], ':id' => $investmentID));
                            $update = true;
                            $dateSold = $_REQUEST['dateSold'];
                        }

                        if ($soldFor != $_REQUEST['soldFor'])
                        {
                            $soldAtX = ($_REQUEST['soldFor']/$amount);
                            $profitX = ($_REQUEST['soldFor'])-$totalCost;
                            $SQL = $odb -> prepare("UPDATE `investments` SET `soldFor` = :soldFor, `soldAt` = :soldAt, `profit` = :profit WHERE `id` = :id");
                            $SQL -> execute(array(':soldFor' => $_REQUEST['soldFor'], ':soldAt'=> $soldAtX, ':profit'=> $profitX, ':id' => $investmentID));
                            $update = true;
                            $soldFor = $_REQUEST['soldFor'];
                        }

                         if ($update == true)
                        {
                            $SQL = $odb -> prepare("UPDATE `investments` SET `totalCost` = amount*costPerCoin WHERE `id` = :id");
                            $SQL -> execute(array(':id' => $investmentID));

                            echo '<div class="alert alert-success" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button> Update Success. <a href="dashboard.php">Click Here</a></div>';
                        }
                        else
                        {
                            echo '<div class="alert alert-danger" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button> No changes were made.</div>';
                        }
                }
                else {
                foreach($errors as $error)
                {
                    echo '<div class="alert alert-danger">'.$error.'</div><br />';
                }
                echo '</div>';
               }


                }

               if (isset($_POST['updateInvestment'])) {
                    $investmentID =  $_POST['iid'];
                    $SQLGetInfo = $odb -> prepare("SELECT * FROM `investments` WHERE `id` = :id AND `userID` = :userID LIMIT 1");
                    $SQLGetInfo -> execute(array(':id' => $investmentID, ':userID' => $_SESSION['rID']));
                    $investmentInfo = $SQLGetInfo -> fetch(PDO::FETCH_ASSOC);
                    $date = $investmentInfo['date'];
                    $coin = $investmentInfo['coin'];
                    $amount = $investmentInfo['amount'];
                    $costPerCoin = $investmentInfo['costPerCoin'];
                    $userID = $investmentInfo['userID'];
                    $totalCost = $investmentInfo['totalCost'];
                    $update = false;

                    $errors = array();
                    if (empty($_POST['date']) || empty($_POST['coin']) || empty($_POST['amount']) || empty($_POST['costPerCoin']))
                    {
                        $errors[] = 'Please ensure all fields are filled.';
                    }
                    if (!ctype_alnum($_POST['coin']) || strlen($_POST['coin']) < 1 || strlen($_POST['coin']) > 28)
                    {
                        $errors[] = 'Coin Names must be between 1-28 characters. If you are seeing this error contact the admin immediately.';
                    }
                    if ($_POST['amount']<=0)
                    {
                        $errors[] = 'You have entered an invalid number of coins.';
                    } 
                    if ($_POST['costPerCoin']<=0)
                    {
                        $errors[] = 'You have entered an invalid cost per coin.';
                    }   
                    if (!preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$_POST['date']))
                    {
                        $errors[] = 'Invalid Date';
                    }                    

                    if (empty($errors))
                    {
                        if ($date != $_POST['date'])
                        {
                            $SQL = $odb -> prepare("UPDATE `investments` SET `date` = :date WHERE `id` = :id");
                            $SQL -> execute(array(':date' => $_REQUEST['date'], ':id' => $investmentID));
                            $update = true;
                            $date = $_REQUEST['date'];
                        }
                        if ($coin != $_REQUEST['coin'])
                        {
                            $SQL = $odb -> prepare("UPDATE `investments` SET `coin` = :coin WHERE `id` = :id");
                            $SQL -> execute(array(':coin' => $_REQUEST['coin'], ':id' => $investmentID));
                            $update = true;
                            $coin = $_REQUEST['coin'];
                        }
                        if ($amount != $_REQUEST['amount'])
                        {
                            $SQL = $odb -> prepare("UPDATE `investments` SET `amount` = :amount WHERE `id` = :id");
                            $SQL -> execute(array(':amount' => $_REQUEST['amount'], ':id' => $investmentID));
                            $update = true;
                            $amount = $_REQUEST['amount'];
                        }

                        if ($costPerCoin != $_REQUEST['costPerCoin'])
                        {
                            $SQL = $odb -> prepare("UPDATE `investments` SET `costPerCoin` = :costPerCoin WHERE `id` = :id");
                            $SQL -> execute(array(':costPerCoin' => $_REQUEST['costPerCoin'], ':id' => $investmentID));
                            $update = true;
                            $coin = $_REQUEST['costPerCoin'];
                        }

                        if ($update == true)
                        {
                            $SQL = $odb -> prepare("UPDATE `investments` SET `totalCost` = amount*costPerCoin WHERE `id` = :id");
                            $SQL -> execute(array(':id' => $investmentID));

                            echo '<div class="alert alert-success" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button> Update Success. <a href="dashboard.php">Click Here</a></div>';
                        }
                        else
                        {
                            echo '<div class="alert alert-danger" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button> No changes were made.</div>';
                        }
                }
                else {
                foreach($errors as $error)
                {
                    echo '<div class="alert alert-danger">'.$error.'</div><br />';
                }
                echo '</div>';
               }


                }

                if (isset($_POST['removeFromLedger'])) {
                    $investmentID =  $_POST['iid'];
                    $sql = $odb -> prepare("UPDATE `investments` SET `dateSold` = NULL, `soldAt` = NULL, `soldFor` = NULL, `profit` = NULL WHERE `id` = :id AND `userID` = :userID");
                    $sql -> execute(array(':id' => $investmentID, ':userID' => $_SESSION['rID']));

                        echo '<div class="alert alert-success" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button> Investment has been removed from your Ledger and is back in your portfolio. <a href="dashboard.php">Click Here</a></div>';
                }     

                if (isset($_POST['deleteInvestment'])) {
                    $investmentID =  $_POST['iid'];
                    $sql = $odb -> prepare("DELETE FROM `investments` WHERE `id` = :id AND `userID` = :userID");
                    $sql -> execute(array(':id' => $investmentID, ':userID' => $_SESSION['rID']));

                        echo '<div class="alert alert-success" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button> Investment Deleted. <a href="dashboard.php">Click Here</a></div>';
                }                
                

                if (isset($_GET['i'])) {
                
                     echo '<p>Edit Investment #'.$investmentID.':<br>
                    <form action = "manageinvestment.php" method="post">
                    Date <input type="date" name="date" value='.$date.'>
                    Coin <select name="coin">';
                    $getCoins = $odb-> query("SELECT list_name, list_symbol FROM `coins` ORDER BY list_name ASC");
                    while($getInfo = $getCoins -> fetch(PDO::FETCH_ASSOC)){
                                  $list_name = $getInfo['list_name'];
                                  $list_symbol = $getInfo['list_symbol']; 
                                  if ($list_symbol == $coin) {
                                    echo '<option value="'.$list_symbol.'" selected>'.$list_name.'</option>';
                                  }else{
                                    echo '<option value="'.$list_symbol.'">'.$list_name.'</option>';
                                  }
                    }
                    echo '</select>
                    # of Coins  <input type="text" name="amount" value='.(float)$amount.'>
                    Cost per Coin $<input type="text" name="costPerCoin" value='.(float)$costPerCoin.'>USD
                    <input type="hidden" name="iid" value='.$investmentID.'>
                    <input type="submit" name="updateInvestment" value="Update Investment"/><p>
                    <p align="right"><button name="deleteInvestment" type="submit">Delete Investment (CAN NOT BE REVERSED)</button></p>
                    </form>';


                    echo'<strong><p>****WARNING: ONCE YOU SELL YOUR INVESTMENT USING THE FORM BELOW, IT WILL APPEAR ON YOUR <a href="ledger.php">LEDGER</a> and will no longer be visible on your Portfolio****.</strong/></p><p>
                    <form action = "manageinvestment.php" method="post">
                    I sold all of my coins in this investment for a total of $<input type="text" name="soldFor" value='.(float)$soldFor.'> on <input type="date" name="dateSold" value='.$dateSold.'>.
                    <input type="hidden" name="iid" value='.$investmentID.'>';

                    if ($soldFor != NULL) {
                        echo '<input type="submit" name="addToLedger" value="Update Ledger"/><p>
                        <p align="right"><button name="removeFromLedger" type="submit">Remove investment from Ledger (Places it back in your portfolio).</button></p><input type="hidden" name="iid" value='.$investmentID.'>';
                     }
                    else {
                        echo '<input type="submit" name="addToLedger" value="Add to Ledger"/><p></form>';
                    }

                }
        ?>     