<?php

// make sure browsers see this page as utf-8 encoded HTML
header('Content-Type: text/html; charset=utf-8');
header('Access-Control-Allow-Origin: *');

$limit = 10;
$query = isset($_REQUEST['q']) ? $_REQUEST['q'] : false;
$results = false;

if ($query)
{
  // The Apache Solr Client library should be on the include path
  // which is usually most easily accomplished by placing in the
  // same directory as this script ( . or current directory is a default
  // php include path entry in the php.ini)
  require_once('/home/tommy/Downloads/solr-7.7.0/solr-php-client/Apache/Solr/Service.php');

  // create a new solr service instance - host, port, and webapp
  // path (all defaults in this example)
  $solr = new Apache_Solr_Service('localhost', 8983, '/solr/fox_example');

  // if magic quotes is enabled then stripslashes will be needed
  if (get_magic_quotes_gpc() == 1)
  {
    $query = stripslashes($query);
  }

  // in production code you'll always want to use a try /catch for any
  // possible exceptions emitted  by searching (i.e. connection
  // problems or a query parsing error)
  try
  {
      $radioVal = $_GET["radio"];
      if($radioVal == "PageRank"){
          $additionalParameters=array(
          'fl'=>array('title','og_url','id','description'),
          'sort'=>'pageRankFile desc');
      }
      else{
          $additionalParameters=array(
           'fl'=>array('title','og_url','id','description'));
       
}

   $results=$solr->search($query, 0, $limit, $additionalParameters);

  }
  catch (Exception $e)
  {
    // in production you'd probably log or email this error to an admin
    // and then show a special message to the user but for this example
    // we're going to show the full exception
    die("<html><head><title>SEARCH EXCEPTION</title><body><pre>{$e->__toString()}</pre></body></html>");
  }
}

?>
<html>
  <head>
    <title>PHP Solr Client Example</title>
    <link href="http://code.jquery.com/ui/1.10.4/themes/ui-lightness/jquery-ui.css" rel="stylesheet"></link>
    <script src="http://code.jquery.com/jquery-1.10.2.js"></script>
    <script src="http://code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
  </head>
  <body>
    <script>
	$( function() {
 	  var URL_PREFIX = "http://localhost:8983/solr/fox_example/suggest?q=";
    	  $( "#q" ).autocomplete({
      	    source: function( request, response ) {
	      var usrinput = $("#q").val();
	      var URL = URL_PREFIX + usrinput;
              $.ajax( {
          	url: URL,
          	dataType: "json",
          	crossDomain: "true",
	  	data: {
	   	  term: request.term
	  	},    
                success: function( data ) {
		  var i;
		  var len = data.suggest.suggest[usrinput].suggestions.length;
		  var myArr = new Array(len);
		  for (i = 0; i < len; i++) { 
		    myArr[i] = data.suggest.suggest[usrinput].suggestions[i].term;
		  }
		  response(myArr);
	        }
               });
      	     },
      	     minLength: 1,
	   });
	 });

/**
	var suggest_arr = [];
        function autocomp(val){
	    suggest_arr = [];
            var xmlhttp = new XMLHttpRequest();
	    xmlhttp.open("GET", "http://localhost:8983/solr/fox_example/suggest?q=" + val, true);
	    xmlhttp.send();
            xmlhttp.onreadystatechange = function(){
                if (xmlhttp.readyState == 4 && xmlhttp.status == 200){
                    var temp_arr = JSON.parse(xmlhttp.responseText).suggest.suggest[val].suggestions;
		    var str = '';
		    for(var i = 0; i < temp_arr.length; i++){
			suggest_arr.push(temp_arr[i].term);
		    }
                    document.getElementById("example").innerHTML = suggest_arr.toString();
		    for(var i = 0; i < suggest_arr.length; i++){
			str += '<option value="'+suggest_arr[i]+'"></option>';
		    }
		    console.log(str);
		    document.getElementById("searchresults").innerHTML = str;
                }
            };
        }
**/
    </script>
    <form  accept-charset="utf-8" method="get">
      <label for="q">Search:</label>
      <input id="q" name="q" type="text" value="<?php echo htmlspecialchars($query, ENT_QUOTES, 'utf-8'); ?>"/>
      <datalist id="searchresults">
      </datalist>
      <input type="submit"/>
       <div align="left">
        <input id="radio1" name="radio" type="radio" value="Lucene" checked> Lucene</input>
        <input id="radio2" name="radio" type="radio" value="PageRank"> PageRank</input><br>
      </div>
      <div id="example"></div>
     </form>
<?php

// display results
if ($results)
{
  $total = (int) $results->response->numFound;
  $start = min(1, $total);
  $end = min($limit, $total);
?>

<?php
  include 'SpellCorrector.php';
  $corrected = SpellCorrector::correct($query);

  //if query contains more than one word
  if($corrected == $query && strpos($query, ' ') == true){
    $arr = explode(' ', $query);
    $corrected = '';
    for($i = 0; $i < count($arr); $i++){
      if($i == count($arr) - 1){
        $corrected = $corrected.SpellCorrector::correct($arr[$i]);
      }
      else{
        $corrected = $corrected.SpellCorrector::correct($arr[$i]).' ';
      }
    }
  }

  if($corrected != strtolower($query)){
?>
  <div>
    Results for <?php echo $query?>
  </div>
  <div>
    Did You Mean: <a href="index.php?q=<?php echo $corrected ?>&radio=<?php echo $_GET["radio"]?>"><?php echo$corrected ?></a>
  </div>
<?php
  }
?>

    <div>Results <?php echo $start; ?> - <?php echo $end;?> of <?php echo $total; ?>:</div>
    <ol>
<?php
  // iterate result documents
  foreach ($results->response->docs as $doc)
  {
?>
      <li>
        <table style="border: 1px solid black; text-align: left">
<?php
    foreach ($doc as $field => $value)
    {
	if($field == 'og_url'){
        	$url = $value;
        }
    }
    foreach ($doc as $field => $value)
    {
	
?>
          <tr>
            <th><?php echo htmlspecialchars($field, ENT_NOQUOTES, 'utf-8'); ?></th>
             <td>
		<?php if ($field == 'og_url' || $field == 'title'): ?>
		<a href=
			<?php 
			   echo htmlspecialchars($url, ENT_NOQUOTES, 'utf-8');
			?>><?php echo htmlspecialchars($value, ENT_NOQUOTES, 'utf-8');?> 
	        </a>
		<?php 
			else: echo htmlspecialchars($value, ENT_NOQUOTES, 'utf-8');
			endif;
		?>
             </td>
          </tr>
<?php
    }
?>
        </table>
      </li>
<?php
  }
?>
    </ol>
<?php
}
?>
  </body>
</html>
