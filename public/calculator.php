<!DOCTYPE html>
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
 
<title>MyAltCoins.net - Easy Profit Calculator</title>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js" type="text/javascript"></script>
 
<script  type="text/javascript">
 
$(document).ready(function() {

	var precision = 2;

	$('input[name="currency"]').on('change', function() {
		var val = $(this).val();
		if(val === 'fiat')
			precision = 2;
		else if(val === 'crypto')
			precision = 10;
	});
 
    $('#current').keyup(function(ev){
        var invested = $('#invested').val() * 1;
        $('#iinvested').html((invested).toFixed(precision));
    });
    $('#current').keyup(function(ev){
        var worth = ($('#invested').val() / $('#initial').val()) * $('#current').val();
        $('#worth').html((worth).toFixed(precision));
    });
    $('#current').keyup(function(ev){
        var profit =  (($('#invested').val() / $('#initial').val()) * $('#current').val()) - ($('#invested').val()*1);
        $('#profit').html((profit).toFixed(precision));
    });
});
</script>
 
</head>
<body>
Profit Calculator<br>
<input type="radio" name="currency" value="fiat"> FIAT ($)<br>
 <input type="radio" name="currency" value="crypto"> Crypto Currency<br>
I invested <input type="text" name="invested" id="invested" /><br>
When each asset was worth: <input type="text" name="initial" id="initial" /><br>
Each asset is currently worth: <input type="text" name="current" id="current" /><br>
Results:<br>
I invested <span id="iinvested">0.00</span><br>
It is now worth: <span id="worth">0.00</span><br>
I have a profit of: <span id="profit">0.00</span><br>
</body>
</html>