<?php?>
<!DOCTYPE html>
<html lang=en>
  <meta charset=UTF-8>
  <head>
    <title>PHP Test</title>
    <script src="upload.js" type="text/javascript">
    </script>
  </head>
  <body>
    <script type="text/javascript">
    var file = randName();
    var data = '"subtests": [ { "name": "PMT TextTrack attributes.", "status": "NOTRUN", "message": null } ]';

    var uploadData = function ()
    {
      ajax("upload.php",
           function (r) { 
             return true; 
           },
           "file="+file+"&log="+data);

      var d = document.getElementById("uploadLink");
      d.innerHTML = "Your file is: "+file+".log<br>";

      return false;
    }
    </script>
    <a onclick="return uploadData();" href="">Upload Data</a>
    <div id="uploadLink"></div>
  </body>
</html>
