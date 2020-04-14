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
include('templates/header.php');
echo '<title>MyAltCoins - My Ledger</title><div class="container">
	<div align="center">
        <h1>My Ledger</h1>
		</div>
        <div id="portfolio">
            <table class="table" id="ledgertable" style="font-size: 15px;">
                <thead>
                    <tr>
                        <th id="date">Buy Date</th>
                        <th id="coin">Coin</th>
                        <th id="coins"># of Coins</th>
                        <th id="costpercoin"> Cost per coin</th>
                        <th id="spent"> Spent </th>
                        <th id="sellDate"> Sell Date </th>
                        <th id="valuePerCoin"> Value per Coin</th>
                        <th id="totalReceived"> Total Received </th>
                        <th id="profit"> Profit </th>
                        <th id="growth"> Growth </th>
                    </tr>
                </thead>
                <tbody>
';
	

$getInvestments = $odb  -> prepare("SELECT * FROM `investments` INNER JOIN coins on investments.coin = coins.list_symbol WHERE investments.userID = :id AND `soldFor` IS NOT NULL ORDER BY coin ASC");
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
	$dateSold = $getInfo['dateSold'];
	$soldAt = $getInfo['soldAt'];
	$soldFor = $getInfo['soldFor'];
	$growth = ($soldFor/$totalCost)*100;
	if ($growth>100) {
					$growth=$growth-100;
	}

	        echo ' <tr>
	            <td><a href=manageinvestment.php?i='.$investmentID.'>'.$date.'</a></td>
	            <td>'.$coin.'</td>
	            <td>'.(float)$amount.'</td>
	            <td>$'.(float)$costPerCoin.'</td>
	            <td>$'.round((float)$totalCost,6).' </td>
	            <td>'.$dateSold.' </td>
	            <td>$'.round((float)$soldAt,6).' </td>
	            <td>$'.round((float)$soldFor,6).' </td>';
	      
			if ($profit < 0){
				echo '<td style="color:#C00">$'.$profit.'</td>
				<td style="color:#C00">-'.(round(100-$growth,2)).'% </td>';
			}
			else{
				echo '<td style="color:#0C0">$'.round((float)$profit,6).' </td>
				 <td style="color:#0C0">+'.(round($growth,2)).'% </td>';
			}
	         echo '</tr>';

}
	   echo '</tbody>
	   </table>
	  <div>
		<h1 align="center">Summary</h1>
        <div id="ledgerSummary">
            <table class="table" id="ledgerSummary" style="font-size: 15px;">
                <thead>
                    <tr>
                        <th>Coin</th>
                        <th># of Coins</th>
                        <th> Spent </th>
                        <th> Received </th>
                        <th> Profit </th>
                        <th> Growth </th>

                    </tr>
                </thead>
                <tbody>
';
	

$getInvestments = $odb  -> prepare("SELECT coin as coin, SUM(amount) as amount, SUM(totalCost) as totalCost, SUM(profit) as profit, SUM(soldFor) as soldFor FROM `investments` INNER JOIN coins on investments.coin = coins.list_symbol WHERE investments.userID = :id AND investments.soldFor IS NOT NULL GROUP by coin");
$getInvestments -> execute(array(':id' => $userID ));
$AlltotalCostSummary=0;
$AllprofitSummary=0;
$AllReceivedSummary=0;
while($getInfo = $getInvestments -> fetch(PDO::FETCH_ASSOC))
{
	$coinSummary = $getInfo['coin'];
	$amountSummary = $getInfo['amount'];
	$totalCostSummary = $getInfo['totalCost'];
	$totalSoldForSummary = $getInfo['soldFor'];
	$profitSummary = $getInfo['profit'];
	$growthSummary = ($totalSoldForSummary/$totalCostSummary)*100;
	if ($growthSummary>100) {
		$growthSummary=$growthSummary-100;
	}
	$AlltotalCostSummary = $AlltotalCostSummary + $totalCostSummary;
	$AllprofitSummary = $AllprofitSummary + $profitSummary;
	$AllReceivedSummary = $AllReceivedSummary + $totalSoldForSummary;
	        echo ' <tr>
	            <td>'.$coinSummary.'</td>
	            <td>'.round((float)$amountSummary,6).'</td>
	            <td>$'.round((float)$totalCostSummary,6).' </td>
	            <td>$'.round((float)$totalSoldForSummary,6).' </td>';
				
	            if ($profitSummary < 0){
				echo '<td style="color:#C00">$'.round((float)$profitSummary,6).' </td>
				<td style="color:#C00">-'.(round(100-$growthSummary,2)).'%</td>';
				}
				else{
					echo '<td style="color:#0C0">$'.round((float)$profitSummary,6).' </td>
					 <td style="color:#0C0">+'.(round($growthSummary,2)).'% </td>';
				}
				 echo '</tr>';


}
	   echo '</tbody>
	   </table>
	   </div>';
		echo '  <div id="summarytext" align="center">
				<p>Total Spent: $'.round((float)$AlltotalCostSummary,6).' </p>
				<p>Total Received: $'.round((float)$AllReceivedSummary,6).' </p>
				<p>Total Profit: $'.round((float)$AllprofitSummary,6).' </p>';

				   
			// Summary End

echo '<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <script>window.jQuery || document.write(\'<script src="https://code.jquery.com/jquery-3.2.1.min.js"><\/script>\')</script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="templates/jquery-latest.js"></script> 
	<script type="text/javascript" src="templates/jquery.tablesorter.js"></script> 
	<script type="text/javascript">
	    $(document).ready(function() 
	    { 
	        $("#ledgertable,#ledgerSummary").tablesorter(); 
	    } 
		); 
	</script>
  ';
?>
</center>


</html>
