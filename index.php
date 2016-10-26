<?PHP

$wpt_base = "http://web-platform.test:8000";
//$wpt_base = "http://localhost:57826/";
$http = array_key_exists("REQUEST_SCHEME", $_SERVER) ? $_SERVER["REQUEST_SCHEME"] : "http";
$server = array_key_exists("HTTP_HOST", $_SERVER) ? $_SERVER["HTTP_HOST"] : "http";
$port = (array_key_exists("SERVER_PORT", $_SERVER) && $_SERVER["SERVER_PORT"] != 80) ? ":".$_SERVER["SERVER_PORT"] : "";
$path = str_replace('//', '/', (array_key_exists("SCRIPT_NAME", $_SERVER) ? str_replace('\\', '/', dirname($_SERVER["SCRIPT_NAME"])) : "")."/api/results");

$results_endpoints = $http."://".$server.$port.$path;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <meta name="description" content="" />
    <meta name="author" content="" />
    <link rel="icon" href="favicon.ico" />

    <title>DLNA HTML 5 Test Tool</title>

    <!-- Bootstrap core CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet" />

    <!-- Custom styles for this template -->
    <link href="htt.css" rel="stylesheet" />
</head>

<body>
    <?PHP include('html/nav.html'); ?>

    <div class="container tab-content">
    <?PHP
        include('html/tab-home.html');
        include('html/tab-results.html');
        include('html/tab-validation.html');
        include('html/tab-about.html');
    ?>   
    </div>

    <?PHP

    include('html/dialog-session-delete.html');
    include('html/dialog-report-delete.html');
    include('html/dialog-report-new.html');
    include('html/dialog-reference-delete.html');
    include('html/dialog-reference-new.html');

    ?>

    <!-- JavaScript
            ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->

    <script src="js/jquery-1.9.1.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/knockout-3.3.0.js"></script>
    <script src="js/knockout.mapping-latest.js"></script>
    <script src="js/sammy-latest.min.js"></script>
    <script src="js/autobahn.min.js"></script>
    <script src="js/modal.js"></script>

    <script src="js/LiveEditor.js" charset="UTF-8"></script>
    <script src="js/DeleteViewModel.js" charset="UTF-8"></script>
    <script src="js/NewReportViewModel.js" charset="UTF-8"></script>
    <script src="js/NewReferenceViewModel.js" charset="UTF-8"></script>
    <script src="js/TestReferenceViewModel.js" charset="UTF-8"></script>
    <script src="js/TestResultsViewModel.js" charset="UTF-8"></script>
    <script src="js/TestSessionViewModel.js" charset="UTF-8"></script>
    <script src="js/TestReportListViewModel.js" charset="UTF-8"></script>
    <script src="js/TestReportViewModel.js" charset="UTF-8"></script>
    <script src="js/ValidationViewModel.js" charset="UTF-8"></script>
    <script src="js/ResultsViewModel.js" charset="UTF-8"></script>
    <script src="js/AboutViewModel.js" charset="UTF-8"></script>
    <script src="js/test_tool_manager.js" charset="UTF-8"></script>
</body>
</html>
