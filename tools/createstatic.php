<?php
date_default_timezone_set("Europe/London");

class builder{
  /*CONFIG START*/
  private $destinationFolder = 'htdocs/';
  private $dir       = '';
  private $dirPages  = 'pages/';
  private $cssPath   = 'c.css';
  private $css       = '';
  private $justCopy  = array('c.css','j.js','robots.txt');
  /*CONFIG END*/

  function __construct($dir=''){
    $this->dir = $dir;
    $this->destinationFolder = $this->dir.$this->destinationFolder;
  }

  private function recurse_copy($src,$dst) {
    $dir = opendir($src);
    @mkdir($dst);
    while(false !== ( $file = readdir($dir)) ) {
        if (( $file != '.' ) && ( $file != '..' )) {
            if ( is_dir($src . '/' . $file) ) {
                $this->recurse_copy($src . '/' . $file,$dst . '/' . $file);
            }
            else {
                echo 'copied '.$dst.'/'.$file."\n";
                copy($src . '/' . $file,$dst . '/' . $file);
            }
        }
    }
    closedir($dir);
  }

  public function build(){
    $this->css = file_get_contents($this->dir.$this->cssPath);
    echo "Copying Static Files\n";
    $this->copyStaticFiles();
    echo "Building Pages\n";
    $this->buildPages($this->dir.$this->dirPages);
  }

  private function copyStaticFiles(){
    @mkdir($this->destinationFolder);
    //copy static files
    foreach($this->justCopy as $fileOrFolder){
      //if we are copying a file
      if(is_file($this->dir.$fileOrFolder)){
        echo 'copied '.$this->destinationFolder.$fileOrFolder."\n";
        copy($this->dir.$fileOrFolder,$this->destinationFolder.$fileOrFolder);
      }else{ //else we are copying a folder
        //check if the folder needs creating in the destination
        if(!is_dir($this->destinationFolder.$fileOrFolder)){
          mkdir($this->destinationFolder.$fileOrFolder);
        }
        //copy the contents over
        $this->recurse_copy($this->dir.$fileOrFolder,$this->destinationFolder.$fileOrFolder);
      }
    }
  }
  
  //build pages
  private function buildPages($dir){
    if($handle = opendir($dir)){
      while(false !== ($entry = readdir($handle))){
        if(is_dir($dir.$entry) && $entry != '.' && $entry != '..'){
          $this->buildPages($dir.$entry.'/');
        }
        if(substr($entry,-5) != '.json'){continue;}
        $json = json_decode(file_get_contents($dir.$entry),true);
        $page = new page($json['title'],$this->css);
        $page->setContent(file_get_contents(substr($dir.$entry,0,-5).'.html'));
        $content = $page->build();
        $this->generateFile($this->destinationFolder.$json['url'].'/index.html',$content);
      }
    }
  }

  private function generateFile($name,$content){
    $dir = dirname($name);
    if(!is_dir($dir)){
      mkdir($dir,0777,true);
    }
    file_put_contents($name,$content);
    echo 'Generated '.$name."\n";
  }
}

class page{
  private $content  = '';
  private $title    = '';
  function __construct($title,$css=''){
    $this->title = $title;

    $this->header =
    '<!DOCTYPE html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Mayhew Tech</title>
<link rel="stylesheet" href="/c.css" media="screen" type="text/css" />
<script src="/j.js" type="text/javascript"></script>
</head>
<body>';
  }
  private function buildFooter(){
    $this->footer =
    '<div id="footer">
    <div class="content clearfix">

    <div class="left">
            <ul>
                <li><a href="/policies-terms/">Terms &amp; Conditions</a></li>
                <li><a href="/policies-terms/cookies/">Cookie policy</a></li>
            </ul>
            <ul>
                <li>&nbsp;</li>
            </ul>
    </div>
    <div class="right">
            <ul>
                <li>&nbsp;</li>
            </ul>
            <ul>
                <li>&nbsp;</li>
            </ul>
    </div>
    <div class="clearfloat"></div>
    <div id="companyaddress">Registered Address: St Dunstans House, 15-17 South Street, Worthing, West Sussex, BN14 7LG, England, Company No. 05073043</div>
    </div>
<script type="text/javascript">
window.onerror = function(msg, url, linenumber){
    errorHandler.call(msg, url, linenumber);
    return true;
}
</script>
</body>
</html>';
  }
  public function setContent($content){
    $this->content = $content;
  }
  public function build(){
    $this->buildFooter();
    return $this->header.$this->content.$this->footer;
  }
}

// work out if we are in the tool directory or the root of the repo
if (file_exists(getcwd().'/vendor/autoload.php')){
  $dir = '';
} else {
  $dir = '../';
}

//go build stuff!
$builder = new builder($dir);
$builder->build();
