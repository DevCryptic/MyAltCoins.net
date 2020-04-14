<?php
ob_start();
require_once 'includes/config.php';
require_once 'includes/global.php';

if (!($user -> IsLogged($odb)))
{
	header('Location: login.php');
	die();
}

if (!($user -> IsActive($odb)))
{
	header('Location: activate.php');
	die();
}

//Get email
$SQLGetUserInfo = $odb -> prepare("SELECT `id`, `username`, `email` FROM `accounts` WHERE `id` = :id LIMIT 1");
$SQLGetUserInfo -> execute(array(':id' => $_SESSION['rID']));
$userInfo = $SQLGetUserInfo -> fetch(PDO::FETCH_ASSOC);
$userMail = $userInfo['email'];
$userID = $userInfo['id'];
$userName = $userInfo['username'];
?>

<?

if ( isset( $_POST['submit'] ) ) {
	$date = trim($_REQUEST['date']);
	$coin = trim($_REQUEST['coin']);
	$amount = trim($_REQUEST['amount']);
	$costPerCoin = trim($_REQUEST['costPerCoin']);

	$errors = array();
	if (empty($date) || empty($coin) || empty($amount) || empty($costPerCoin))
	{
		$errors[] = 'Please ensure all fields are filled.';
	}
	if (!ctype_alnum($coin) || strlen($coin) < 1 || strlen($coin) > 28)
	{
		$errors[] = 'Coin Names must be between 1-28 characters. If you are seeing this error contact the admin immediately.';
	}
	if (!is_numeric($amount) || ($amount<0))
	{
		$errors[] = 'You have entered an invalid number of coinst.';
	}	
	if (!is_numeric($costPerCoin) || ($costPerCoin<0))
	{
		$errors[] = 'You have entered an invalid cost per coin.';
	}	
	if (!preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$date))
    {
		$errors[] = 'Invalid Date';
    }

	if (empty($errors))
	{
		
		$totalCost = $amount * $costPerCoin;
		/*echo $date;  
		echo $coin; 
		echo $amount; 
		echo $costPerCoin;
		*/
		$statement = $odb->prepare("INSERT INTO investments(id, date, coin, amount, costPerCoin, totalCost, soldAt, soldFor, profit, userID)
		VALUES(NULL, :date, :coin, :amount, :costPerCoin, :totalCost, NULL, NULL, NULL, :id)");
		$statement->execute(array(
		"date" => $date,
		"coin" => $coin,
		"amount" => $amount,
		"costPerCoin" => $costPerCoin,
		"totalCost" => $totalCost,
		"id" => $userID
		));
		header('Location: dashboard.php');
	}
	else {
		echo '<div class="alert alert-danger" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>';
		foreach($errors as $error)
		{
			echo '- '.$error.'<br />';
		}
		echo '</div>';

	}
}

?>

<?php
include('templates/header.php');?>

    <strong>This project is going to shut down on April 30th. This is a project I started and learned a lot from, but I no longer have time to continue developing it. I highly recommend <a href="https://getdelta.io/">https://getdelta.io/</a> it's the best Portfolio tracker in the market and highly advanced.</strong>

    <?
echo '<title>MyAltCoins - Dashboard</title><div class="container">
	<div align="center">
        <h1>My Portfolio</h1>
		</div>
        <div id="portfolio">
            <table class="table" id="portfolio" style="font-size: 15px;">
                <thead>
                    <tr>
                        <th id="date">Date</th>
                        <th id="coin">Coin</th>
                        <th id="coins"># of Coins</th>
                        <th id="costpercoin"> Cost per Coin</th>
                        <th id="spent"> Spent </th>
                        <th id="currentPriceCoin"> Current Price per Coin </th>
                        <th id="currentInvestmentValue"> Current Investment Value </th>
                        <th id="profit"> Profit </th>
                        <th id="growth"> Growth </th>
                    </tr>
                </thead>
                <tbody>
';
	

$getInvestments = $odb  -> prepare("SELECT * FROM `investments` INNER JOIN coins on investments.coin = coins.list_symbol WHERE investments.userID = :id AND `soldFor` IS NULL ORDER BY coin ASC");
$getInvestments -> execute(array(':id' => $userID ));

while($getInfo = $getInvestments -> fetch(PDO::FETCH_ASSOC))
{
	$investmentID = $getInfo['id'];
	$date = $getInfo['date'];
	$coin = $getInfo['coin'];
	$amount = $getInfo['amount'];
	$costPerCoin = $getInfo['costPerCoin'];
	$totalCost = $getInfo['totalCost'];
	$profit = $getInfo['profit'];
	$currentPrice = $getInfo['list_price_usd'];
	$currentValue = $amount*$currentPrice;
	$profit = $currentValue - $totalCost;
	$growth = ($currentValue/$totalCost)*100;
	if ($growth>100) {
					$growth=$growth-100;
	}

	        echo ' <tr>
	            <td><a href=manageinvestment.php?i='.$investmentID.'>'.$date.'</a></td>
	            <td>'.$coin.'</td>
	            <td>'.(float)$amount.'</td>
	            <td>$'.(float)$costPerCoin.'</td>
	            <td>$'.round((float)$totalCost,6).' </td>
	            <td>$'.round((float)$currentPrice,6).' </td>
	            <td>$'.round((float)$currentValue,6).' </td>';
	      
			if ($profit < 0){
				echo '<td style="color:#C00">$'.$profit.'</td>
				<td style="color:#C00">-'.(round(100-$growth,2)).'% </td>';
			}
			else{
				echo '<td><p style="color:#0C0">$'.round((float)$profit,6).' </td>
				 <td><p style="color:#0C0">+'.(round($growth,2)).'% </td>';
			}
	         echo '</tr>';

}
	   echo '</tbody>
	   </table>
	  <div>
		<h1 align="center">Summary</h1>
        <div id="summary">
          	<table class="table" id="portfolioSummary" style="font-size: 15px;">
                <thead>
                    <tr>
                        <th>Coin</th>
                        <th># of Coins</th>
                        <th> Spent </th>
                        <th> Current Investment Value </th>
                        <th> Profit </th>
                        <th> Growth </th>

                    </tr>
                </thead>
                <tbody>
';
	

$getInvestments = $odb  -> prepare("SELECT coin as coin, SUM(amount) as amount, SUM(totalCost) as totalCost, list_price_usd AS list_price_usd FROM `investments` INNER JOIN coins on investments.coin = coins.list_symbol WHERE investments.userID = :id and investments.soldFor IS NULL GROUP by coin");
$getInvestments -> execute(array(':id' => $userID ));

$AlltotalCostSummary=0;
$AllcurrentValueSummary=0;
$AllprofitSummary=0;

while($getInfo = $getInvestments -> fetch(PDO::FETCH_ASSOC))
{
	$coinSummary = $getInfo['coin'];
	$amountSummary = $getInfo['amount'];
	$totalCostSummary = $getInfo['totalCost'];
	$currentPriceSummary = $getInfo['list_price_usd'];
	$currentValueSummary = $amountSummary*$currentPriceSummary;
	$profitSummary = $currentValueSummary - $totalCostSummary;
	$growthSummary = ($currentValueSummary/$totalCostSummary)*100;
	$growthSummary = ($currentValueSummary/$totalCostSummary)*100;
	if ($growthSummary>100) {
		$growthSummary=$growthSummary-100;
	}
	$AlltotalCostSummary = $AlltotalCostSummary + $totalCostSummary;
	$AllcurrentValueSummary = $AllcurrentValueSummary + $currentValueSummary;
	$AllprofitSummary = $AllprofitSummary + $profitSummary;
	 
	        echo ' <tr>
	            <td>'.$coinSummary.'</td>
	            <td>'.round((float)$amountSummary,6).'</td>
	            <td>$'.round((float)$totalCostSummary,6).' </td>
	            <td>$'.round((float)$currentValueSummary,6).' </td>';
				
	            if ($profitSummary < 0){
				echo '<td style="color:#C00">$'.round((float)$profitSummary,6).' </td>
				<td style="color:#C00">-'.(round(100-$growthSummary,2)).'%</td>';
				}
				else{
					echo '<td><p style="color:#0C0">$'.round((float)$profitSummary,6).' </td>
					 <td><p style="color:#0C0">+'.(round($growthSummary,2)).'% </td>';
				}
				 echo '</tr>';


}
	   echo '</tbody>
	   </table>
	   </div>';
		echo '  <div id="summarytext" align="center">
				<p>Total Invested: $'.round((float)$AlltotalCostSummary,6).' </p>
				<p>Current Value: $'.round((float)$AllcurrentValueSummary,6).' </p>
				<p>Current Profit: $'.round((float)$AllprofitSummary,6).' </p>';
				   
			// Summary End

	echo "<p>Last Updated: ";
	//echo file_get_contents("lastUpdated.txt");
  	echo "<br>";
echo '<br><p>Add an investment:<br>
<form action = "dashboard.php" method="post">
Date <input type="date" name="date" value='.date('Y-m-d').' style="color: black">
Coin <select name="coin" style="color: black">';
$getCoins = $odb-> query("SELECT list_name, list_symbol FROM `coins` ORDER BY list_name ASC");
while($getInfo = $getCoins -> fetch(PDO::FETCH_ASSOC)){
			  $list_name = $getInfo['list_name'];
			  $list_symbol = $getInfo['list_symbol']; 
			  echo '<option value="'.$list_symbol.'">'.$list_name.'</option>';
}
echo '</select>
# of Coins  <input type="text" name="amount" style="color: black">
Cost per Coin $<input type="text" name="costPerCoin" style="color: black">USD
<input type="submit" name="submit" style="color: black"/>
</form>
';

echo '<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <script>window.jQuery || document.write(\'<script src="https://code.jquery.com/jquery-3.2.1.min.js"><\/script>\')</script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="templates/jquery-latest.js"></script> 
	<script type="text/javascript" src="templates/jquery.tablesorter.js"></script> 
	<script type="text/javascript">
	    $(document).ready(function() 
	    { 
	        $("#portfolio, #portfolioSummary, #ledger, #ledgerSummary").tablesorter(); 
	    } 
		); 
	</script>
  ';
include ('templates/footer.php');
?>
</center>


</html>
