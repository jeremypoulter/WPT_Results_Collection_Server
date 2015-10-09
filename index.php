<?PHP

$wpt_base = "http://web-platform.test:9001";
$http = array_key_exists("REQUEST_SCHEME", $_SERVER) ? $_SERVER["REQUEST_SCHEME"] : "http";
$server = array_key_exists("HTTP_HOST", $_SERVER) ? $_SERVER["HTTP_HOST"] : "http";
$port = (array_key_exists("SERVER_PORT", $_SERVER) && $_SERVER["SERVER_PORT"] != 80) ? ":".$_SERVER["SERVER_PORT"] : "";
$path = str_replace('//', '/', (array_key_exists("SCRIPT_NAME", $_SERVER) ? str_replace('\\', '/', dirname($_SERVER["SCRIPT_NAME"])) : "")."/api/results");

$results_endpoints = $http."://".$server.$port.$path;

?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="favicon.ico">

    <title>DLNA HTML 5 Test Tool</title>

    <!-- Bootstrap core CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="htt.css" rel="stylesheet">
  </head>

  <body>

    <nav class="navbar navbar-inverse navbar-fixed-top">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="#">DLNA HTML5 Test Tool</a>
        </div>
        <div id="navbar" class="collapse navbar-collapse">
            <ul class="nav navbar-nav">
                <li class="active"><a href="#">Home</a></li>
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">Test Runner</a>
                    <ul class="dropdown-menu">
                        <li><a href="<?= $wpt_base ?>/tools/runner/tests.html?results_endpoint=<?= urlencode($results_endpoints) ?>" target="_blank">Simple</a></li>
                        <li><a href="<?= $wpt_base ?>/tools/runner/index.html?results_endpoint=<?= urlencode($results_endpoints) ?>" target="_blank">Full</a></li>
                    </ul>
                </li>
                <li><a href="#results">Results</a></li>
                <li><a href="#about">About</a></li>
            </ul>
        </div><!--/.nav-collapse -->
      </div>
    </nav>

    <div class="container">

      <div class="starter-template">
        <h1>DLNA HTML5 Test Tools</h1>
        <p class="lead">Use this document as a way to quickly start any new project.<br> All you get is this text and a mostly barebones HTML document.</p>
      </div>

    </div><!-- /.container -->


    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="js/jquery-1.9.1.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
  </body>
</html>
