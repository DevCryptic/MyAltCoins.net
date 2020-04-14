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
			echo '
				<div><title>'.$userName.'\'s Portfolio</title>
    			<div align="right"><p>Click <a href="viewledger.php?u='.$_GET['u'].'">here</a> to see '.$userName.'\'s Ledger.</p></div>

    		<div class="container">
				<h1 align="center">'.$userName.'\'s Portfolio</h1>
				   <div id="portfolio">
            <table class="table" style="font-size: 15px;">
                <thead>
                    <tr>

							<th>Date</th>
							<th>Coin</th>
							<th># of Coins</th>
							<th> Cost per Coin</th> 
							<th> Spent </th>
							<th> Current Price per Coin </th>
							<th> Current Investment Value </th>
							<th> Profit </th>
							<th> Growth </th>
						 </tr>
					  </thead>
					  <tbody>';
				

			$getInvestments = $odb  -> prepare("SELECT * FROM `investments` INNER JOIN coins on investments.coin = coins.list_symbol WHERE investments.userID = :id ORDER BY coin ASC");
			$getInvestments -> execute(array(':id' => $userID ));

			while($getInfo = $getInvestments -> fetch(PDO::FETCH_ASSOC))
			{
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
				if (($growth>100) && ($growth<200)) {
					$growth=$growth-100;
				}

						echo ' <tr>
							<td>'.$date.'</td>
							<td>'.$coin.'</td>
							<td>'.(float)$amount.'</td>
							<td>$'.(float)$costPerCoin.'</td>
							<td>$'.round((float)$totalCost,6).' </td>
							<td>$'.round((float)$currentPrice,6).' </td>
							<td>$'.round((float)$currentValue,2).' </td>';
					  
						if ($profit < 0){
							echo '<td> <p style="color:red">$'.round((float)$profit,2).' </p></td>
							<td> <p style="color:red">-'.(round(100-$growth,2)).'% </p></td>';
						}
						else{
							echo '<td>$'.round((float)$profit,2).' </td>
							 <td>'.(round($growth,2)).'% </td>';
						}
						 echo '</tr>';

			}
				   echo '</tbody>
				   </table>
				   </div>
			<h1 align="center">Summary</h1>

				   <div id="summary">
          		  <table class="table" style="font-size: 15px;">
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
					  <tbody>';
				

			$getInvestments = $odb  -> prepare("SELECT coin as coin, SUM(amount) as amount, SUM(totalCost) as totalCost, list_price_usd AS list_price_usd FROM `investments` INNER JOIN coins on investments.coin = coins.list_symbol WHERE investments.userID = :id GROUP by coin");
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
				if (($growthSummary>100) && ($growthSummary<200)) {
					$growthSummary=$growthSummary-100;
				}
				$AlltotalCostSummary = $AlltotalCostSummary + $totalCostSummary;
				$AllcurrentValueSummary = $AllcurrentValueSummary + $currentValueSummary;
				$AllprofitSummary = $AllprofitSummary + $profitSummary;
				
					echo ' <tr>
							<td>'.$coinSummary.'</td>
							<td>'.round((float)$amountSummary,6).'</td>
							<td>$'.round((float)$totalCostSummary,6).' </td>
							<td>$'.round((float)$currentValueSummary,2).' </td>';
							
							if ($profitSummary < 0){
							echo '<td> <p style="color:red">$'.round((float)$profitSummary,2).' </p></td>
							<td> <p style="color:red">-'.(round(100-$growthSummary,2)).'% </p></td>';
							}
							else{
								echo '<td>$'.round((float)$profitSummary,2).' </td>
								 <td>'.(round($growthSummary,2)).'% </td>';
							}
							 echo '</tr>';
			}
				   echo '</tbody>
				   </table></div>';
			  
			  echo '<div id="summarytext" align="center">
					<p>Total Invested: $'.round((float)$AlltotalCostSummary,2).' </p>
					<p>Current Value: $'.round((float)$AllcurrentValueSummary,2).' </p>
					<p>Current Profit: $'.round((float)$AllprofitSummary,2).'</p>';
				   			
			echo "<p>Last Updated: ";
			echo file_get_contents("/home/nginx/domains/myaltcoins.net/private/lastUpdated.txt");
			echo "</p>";
			include 'templates/footer.php';	

			echo '<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <script>
        window.jQuery || document.write(\'<script src="https://code.jquery.com/jquery-3.2.1.min.js"><\/script>\')
    </script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
</html>';
	}
	else {
		echo "This user has sharing disabled.";
	}
  }


?>