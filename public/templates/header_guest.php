<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=1000">
	<meta name="keywords" content="MyAltCoins, altcoin, currency, crypto, investment, track, tracking, price"/>
	<meta name="description" content="MyAltCoins is an easy to use investment tracking solution. Keep track of your Bitcoin and Altcoin investments with ease!."
    <link rel="icon" href="favicon.ico">
    <link rel="stylesheet" href="templates/style.css">
    <link href="templates/bootstrap.min.css" rel="stylesheet">

    <style>
    #wrap {
    float: left;
    position: relative;
    left: 50%;
}

#content {
    float: left;
    position: relative;
    left: -50%;
}
        body {
            padding-top: 50px;
        }
    th, td {
        align: center;
        text-align: center;
    }
    </style>
</head>
<body>
<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-77945555-2', 'auto');
  ga('send', 'pageview');

</script>
<script>
        function getCookie(cname) {
            var name = cname + "=";
            var decodedCookie = decodeURIComponent(document.cookie);
            var ca = decodedCookie.split(';');
            for (var i = 0; i < ca.length; i++) {
                var c = ca[i];
                while (c.charAt(0) == ' ') {
                    c = c.substring(1);
                }
                if (c.indexOf(name) == 0) {
                    return c.substring(name.length, c.length);
                }
            }
            return "";
        }

        function changeTheme() {
            if (getCookie("theme") == 'dark') {
                document.cookie = "theme=light; expires=Mon, 1 Jan 2020 12:00:00 UTC";
            } else {
                document.cookie = "theme=dark; expires=Mon, 1 Jan 2020 12:00:00 UTC";
            }

            location.reload();
        }

        if (getCookie("theme") == 'dark') {
            document.write("<style>");
            document.write("th, td, form {color: #4fb3b7;}");
            document.write("p, h1 {color: #4fb3b7;}");
            document.write("h3 {color:#4fb3b7;padding:0px;margin:0px;");
            document.write("</style>");
            document.body.style.backgroundColor = "#000000";
        }
        else
        {
            document.write("<style>");          
             document.write("h3 {padding: 0px; margin: 0px;}");           
             document.write("</style>");
        }
        
    </script>

    <nav class="navbar navbar-inverse navbar-fixed-top">
        <div class="container">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="index.php">MyAltCoins.net</a>
            </div>
            <div id="navbar" class="collapse navbar-collapse">
                <ul class="nav navbar-nav">
                    <li><a href="login.php">Login</a>
                    </li>
                    <li><a href="register.php">Register</a>
                    </li>
                </ul>
                <ul class="nav navbar-nav navbar-right">
                    <li>
                        <a onclick="changeTheme();" href="#">
                            <script>
                                if (getCookie("theme") == 'dark') {
                                    document.write('Light');
                                } else {
                                    document.write('Dark')
                                }
                            </script>
                        </a>
                    </li>
                </ul>
            </div>
            <!--/.nav-collapse -->
        </div>
    </nav>

