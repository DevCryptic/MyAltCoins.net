<?php

require '/home/nginx/domains/myaltcoins.net/public/includes/config.php';

$content = json_decode(file_get_contents('https://api.coinmarketcap.com/v1/ticker/'), true);

foreach ($content as $obj) {
  $qry = 'insert into coins values(:id, :name, :symbol, :rank, :price_usd, :price_btc, :24h_volume_usd, :market_cap_usd, :available_supply, :total_supply, :percent_change_1h, :percent_change_24h, :percent_change_7d, :last_updated)';

  $stmt = $odb->prepare('select count(*) as count from coins where list_symbol = :symbol');
  $stmt->execute(['symbol' => $obj['symbol']]);

  if(intval($stmt->fetchAll(PDO::FETCH_ASSOC)[0]['count']) === 1)
  {
    $qry = 'update coins set list_id = :id, list_name = :name, list_rank = :rank, list_symbol = :symbol, list_price_usd = :price_usd, list_price_btc = :price_btc, list_24h_volume_usd = :24h_volume_usd, list_market_cap_usd = :market_cap_usd, list_available_supply = :available_supply, list_total_supply = :total_supply, list_percent_change_1h = :percent_change_1h, list_percent_change_24h = :percent_change_24h, list_percent_change_7d = :percent_change_7d, list_last_updated = :last_updated where list_symbol = :symbol';
  }

  $stmt = $odb->prepare($qry);
  $stmt->execute($obj);
}

$file = "/home/nginx/domains/myaltcoins.net/private/lastUpdated.txt";
$f=fopen($file, 'w');
fwrite($f,date('Y-m-d g:i A')); 
fclose($f);

