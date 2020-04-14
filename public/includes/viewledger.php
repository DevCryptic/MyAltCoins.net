<?php
ob_start();
require_once 'includes/config.php';
require_once 'includes/global.php';

?>

<?php

if(!filter_var(($_GET['u']), FILTER_VALIDATE_INT)){
	echo "Invalid Link";	
}
else {
	
	$userID = $_GET['u'];
	$CheckPublic = $odb -> prepare("SELECT COUNT(*) FROM `accounts` WHERE `id` = :id AND `ispublic` = 1");
	$CheckPublic -> execute(array(':id' => $userID));
	$count = $CheckPublic -> fetchColumn(0);
	if ($count == 1){
	
		$getUsername = $odb -> prepare("SELECT username FROM `accounts` WHERE `id` = :id");
		$getUsername -> execute(array(':id' => $userID));
		while($getInfo = $getUsername -> fetch(PDO::FETCH_ASSOC))
			{
				$userName = $getInfo['username'];		
			}
			include ('templates/header_guest.php');
		echo '<div><title>'.$userName.'\'s Ledger</title>

    	<div align="right"><p>Click <a href="view.php?u='.$_GET['u'].'">here</a> to see '.$userName.'\'s Portfolio.</p></div>
		<div align="center">
	        <h1>'.$userName.'\'s Ledger</h1>
			</div>
	        <div id="portfolio">
	            <table class="table" id="portfoliotable" style="font-size: 15px;">
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
	if (($growth>100) && ($growth<200)) {
					$growth=$growth-100;
	}

	        echo ' <tr>
	            <td>'.$date.'</td>
	            <td>'.$coin.'</td>
	            <td>'.(float)$amount.'</td>
	            <td>$'.(float)$costPerCoin.'</td>
	            <td>$'.round((float)$totalCost,6).' </td>
	            <td>'.$dateSold.' </td>
	            <td>$'.round((float)$soldAt,6).' </td>
	            <td>$'.round((float)$soldFor,6).' </td>';
	      
			if ($profit < 0){
				echo '<td style="color:red">$'.$profit.'</td>
				<td style="color:red">-'.(round(100-$growth,2)).'% </td>';
			}
			else{
				echo '<td>$'.round((float)$profit,6).' </td>
				 <td>'.(round($growth,2)).'% </td>';
			}
	         echo '</tr>';

}
	   echo '</tbody>
	   </table>
	  <div>
		<h1 align="center">Summary</h1>
        <div id="summary">
            <table class="table" style="font-size: 15px;">
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
	if (($growthSummary>100) && ($growthSummary<200)) {
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
				echo '<td style="color:red">$'.round((float)$profitSummary,6).' </td>
				<td style="color:red">-'.(round(100-$growthSummary,2)).'%</td>';
				}
				else{
					echo '<td>$'.round((float)$profitSummary,6).' </td>
					 <td>'.(round($growthSummary,2)).'% </td>';
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
    <script>
        window.jQuery || document.write(\'<script src="https://code.jquery.com/jquery-3.2.1.min.js"><\/script>\')
    </script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
  ';
}
	else {
		echo "This user has sharing disabled.";
	}
  }


?>
</center>


</html>
