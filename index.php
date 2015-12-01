<?PHP

$wpt_base = "http://web-platform.test:9001";
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
                    <li data-bind="css: { active: isHome }">
                        <a href="#home">Home</a>
                    </li>
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">Test Runner</a>
                        <ul class="dropdown-menu">
                            <li>
                                <a href="<?= $wpt_base ?>/tools/runner/runner_simple.html" target="_blank">Simple</a>
                            </li>
                            <li>
                                <a href="<?= $wpt_base ?>/tools/runner/index.html" target="_blank">Full</a>
                            </li>
                        </ul>
                    </li>
                    <li data-bind="css: { active: isResults }">
                        <a href="#results">Results</a>
                    </li>
                    <li data-bind="css: { active: isAbout }">
                        <a href="#about">About</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container tab-content">
        <div class="starter-template" data-bind="visible: isHome">
            <h1>DLNA HTML5 Test Tools</h1>
            <p class="lead">
                Use this document as a way to quickly start any new project.
                <br />
                All you get is this text and a mostly barebones HTML document.
            </p>
        </div>

        <div data-bind="visible: isResults">
            <div data-bind="visible: false === session()">
                <table class="sessions">
                    <thead>
                        <tr>
                            <th>Id</th>
                            <th>Name</th>
                            <th>Count</th>
                            <th>Created</th>
                            <th>Modified</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody data-bind="foreach: sessionList">
                        <tr>
                            <td data-bind="text: id, click: $root.goToSession"></td>
                            <td data-bind="liveEditor: name, click: name.edit">
                                <span class="view" data-bind="text: name"></span>
                                <input class="edit" data-bind="value: name,
                                                               executeOnEnter: name.stopEditing,
                                                               focus: name.editing,
                                                               event: { blur: name.stopEditing }" />
                            </td>
                            <td data-bind="text: count, click: $root.goToSession"></td>
                            <td data-bind="text: new Date($data.created() * 1000), click: $root.goToSession"></td>
                            <td data-bind="text: new Date($data.modified() * 1000), click: $root.goToSession"></td>
                            <td>
                                <a data-bind="click: $root.deleteSession" aria-hidden="true" aria-label="Delete">
                                    <span class="glyphicon glyphicon-trash"></span>
                                </a>
                                <a data-bind="click: $root.downloadSession" aria-hidden="true" aria-label="Download">
                                    <span class="glyphicon glyphicon-download-alt"></span>
                                </a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div data-bind="visible: session">
                <p>
                    Session:
                    <span data-bind="text: session"></span>
                </p>

                <table class='table resultsSummary'>
                    <thead>
                        <tr>
                            <th></th>
                            <th>Passed</th>
                            <th>Failed</th>
                            <th>Timeouts</th>
                            <th>Errors</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td></td>
                            <td class="pass" data-bind="text: totalPass"></td>
                            <td class="fail" data-bind="text: totalFail"></td>
                            <td class="timeout" data-bind="text: totalTimeout"></td>
                            <td class="error" data-bind="text: totalError"></td>
                        </tr>
                        <tr>
                            <td>
                                <label>Display:</label>
                            </td>
                            <td>
                                <input type="checkbox" data-bind="checked: showPass" value="PASS" />
                            </td>
                            <td>
                                <input type="checkbox" data-bind="checked: showFail" value="FAIL" />
                            </td>
                            <td>
                                <input type="checkbox" data-bind="checked: showTimeout" value="TIMEOUT" />
                            </td>
                            <td>
                                <input type="checkbox" data-bind="checked: showError" value="ERROR" />
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div class="container" data-bind="visible: fetching">
                    <button class="btn btn-lg btn-warning center-block">
                        <span class="glyphicon glyphicon-refresh glyphicon-refresh-animate"></span>
                        Loading...
                    </button>
                </div>

                <table class="results" data-bind="visible: !fetching()">
                    <thead>
                        <tr>
                            <th>Test</th>
                            <th>Status</th>
                            <th>Message</th>
                            <th>Subtest Pass Rate</th>
                        </tr>
                    </thead>
                    <tbody data-bind="foreach: results">
                        <tr data-bind="css: resultClasses">
                            <td data-bind="text: test.url"></td>
                            <td data-bind="text: result"></td>
                            <td data-bind="text: message"></td>
                            <td>
                                <span data-bind="text: totals.PASS"></span>
                                /
                                <span data-bind="text: totals.ALL"></span>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div class="row pull-right">
                    <ul class="pagination">
                        <li data-bind="css: { disabled: pageIndex() <= 1 }">
                            <a data-bind="click: function () { goToPage(1) }">|&laquo;</a>
                        </li>
                        <li data-bind="css: { disabled: pageIndex() <= 1 }">
                            <a data-bind="click: function () { goToPage(pageIndex() - 1) }">&laquo;</a>
                        </li>
                        <!-- ko foreach: pages -->
                        <li data-bind="css: { active: $data == $root.pageIndex(), disabled: '&hellip;' == $data }">
                            <a data-bind="text: $data, click: $root.goToPage.bind($data)"></a>
                        </li>
                        <!-- /ko -->
                        <li data-bind="css: { disabled: pageIndex() >= numPages() }">
                            <a data-bind="click: function () { goToPage(pageIndex() + 1) }">&raquo;</a>
                        </li>
                        <li data-bind="css: { disabled: pageIndex() >= numPages() }">
                            <a data-bind="click: function () { goToPage(numPages()) }">&raquo;|</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div data-bind="visible: isAbout"></div>
    </div>
    <!-- /.container -->

    <script type="text/html" id="DeleteSession">
        <div class="modal fade">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3>Delete session <span data-bind="text: session.id()"></span></h3>
                    </div>
                    <div class="modal-body">
                        Are you sure you wish to delete session <span data-bind="text: session.id()"></span>?
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-bind="click: cancel">Cancel</button>
                        <button type="button" class="btn btn-danger" data-bind="click: complete">Delete</button>
                    </div>
                </div>
            </div>
        </div>
    </script>

    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="js/jquery-1.9.1.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/knockout-3.3.0.js"></script>
    <script src="js/knockout.mapping-latest.js"></script>
    <script src="js/sammy-latest.min.js"></script>
    <script src="js/autobahn.min.js"></script>
    <script src="js/modal.js"></script>
    <script src="js/test_tool_manager.js" charset="UTF-8"></script>
</body>
</html>
