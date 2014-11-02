<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2005 Michael Mifsud
 */
namespace Tk\Debug;

/**
 * A debug object to view any values during development
 *
 * @package Tk\Debug
 */
class Console {


    protected $title = 'Debug Console';

    protected $pageHtml = '';

    private $template = '';

    protected $extras = array();


    /**
     * __construct
     *
     * @param string $pageHtml
     */
    public function __construct($pageHtml)
    {

        $this->pageHtml = $pageHtml;

        $this->template = <<<HTML
<?xml version="1.0"?>
<div id="_DcWrap" style="display: none;">
  <div id="_DcToggle">Debug</div>
  <div id="_DcConsole">
    <div id="_DcHead">%s</div>
    <div id="_DcContent">%s</div>
  </div>

<script type="text/javascript">

  function _DcGetTk_Cookie (cookie_name) {
    var results = document.cookie.match ( '(^|;) ?' + cookie_name + '=([^;]*)(;|$)' );
    if ( results ) return ( unescape ( results[2] ) );
    else return null;
  };
  function _DcToggle(id) {
    var el = document.getElementById(id);
    if ( el.style.display == 'none' || el.style.display == '' ) {
        el.style.display = 'block';
        document.cookie = id + '_display=block';
    } else {
        el.style.display = 'none';
        document.cookie = id + '_display=none';
    }
  };
  document.getElementById('_DcToggle').onclick = function (e) {
    _DcToggle('_DcConsole');
  };
  document.getElementById('_DcHead').onclick = function (e) {
    _DcToggle('_DcConsole');
  };
  var state = _DcGetTk_Cookie('_DcConsole_display');
  if (state == 'block') {
      document.getElementById('_DcConsole').style.display = 'block';
  };

  document.getElementById('_DcWrap').style.display = 'block';

</script>
<style type="text/css">
div#_DcWrap {
  text-align: left;
  font-size: 11px;
  font-family: Tahoma,Arial,sans-serif;
  position: fixed;
  bottom: 0;
  right: 0;
  width: 700px;
  opacity:0.9;
  z-index: 99999;
  text-shadow: 1px 1px #000;
}
div#_DcWrap #_DcToggle {
  background-color: #000;
  border-bottom: 1px solid #9c9c9c;
  width: 50px;
  font-size: 10px;
  line-height: 1.5em;
  color: #FFF;
  text-align: center;
  cursor: pointer;
  float: right;
  bottom: 0;
  margin: 0px 15px;
  position: relative;

  border-radius: 5px 5px 0px 0px;

}
div#_DcWrap #_DcConsole {
  background-color: #333;
  color: #EFEFEF;
  display: none;
  width: 100%%;
  max-height: 500px;
  overflow: auto;

  border: 1px solid #FFFF;
  border-radius: 5px 0px 0px 0px;
}
div#_DcWrap #_DcHead {
  padding: 2px 2px 2px 10px;
  font-weight: bold;
  background-color: #000;
  border-bottom: 1px solid #9c9c9c;
  cursor: pointer;
  border: 1px solid #FFFF;
  border-radius: 5px 5px 0px 0px;
}
div#_DcWrap #_DcContent {
  padding: 5px 5px 5px 10px;
}
div#_DcWrap #_DcContent pre {
  border: 1px dashed #999;
  color: #FFF;
  background-color: #444;

  height: 300px;
  width: 100%%;
  font-size: 10px;
  overflow: auto;
   white-space: pre-wrap;

  display: none;
}
div#_DcWrap #_DcContent p {
  margin: 10px 0px 0px 0px;
  padding: 0;
}

div#_DcWrap #_DcContent a,
div#_DcWrap #_DcContent a:link {
  color: #99F;
}
</style>
</div>
HTML;
    }

    /**
     * Add an extra string to the console
     *
     * @param string $title
     * @param string $html
     */
    public function addExtra($title, $html)
    {
        $this->extras[$title] = $html;
    }

    /**
     * Get the new html
     *
     * @return string
     */
    public function getHtml()
    {
        // Setup the required capture data
        $this->title = date('d/m/Y H:i:s - ') . $this->title;
        $data = new \stdClass();
        $data->phpversion = \PHP_VERSION;
        $data->os = \PHP_OS;
        $data->http_host = $_SERVER['HTTP_HOST'];
        $data->request_uri = $_SERVER['REQUEST_URI'];
        $data->document_root = $_SERVER['DOCUMENT_ROOT'];

        $data->http_referer = 'N/A';
        if (isset($_SERVER['HTTP_REFERER'])) {
            $data->http_referer = $_SERVER['HTTP_REFERER'];
        }
        $data->http_accept_charset = 'N/A';
        if (isset($_SERVER['HTTP_ACCEPT_CHARSET'])) {
          $data->http_accept_charset = $_SERVER['HTTP_ACCEPT_CHARSET'];
        }
        $data->http_accept_encoding = 'N/A';
        if (isset($_SERVER['HTTP_ACCEPT_ENCODING'])) {
            $data->http_accept_encoding = $_SERVER['HTTP_ACCEPT_ENCODING'];
        }
        $data->http_accept_language = 'N/A';
        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])){
            $data->http_accept_language = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
        }
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $data->http_user_agent = $_SERVER['HTTP_USER_AGENT'];
        }
        $data->page_load_time = round(\Tk\FrontController::scriptDuration(), 4);
        //TODO
        //$data->classLookups = \Tk\Autoloader::getLookupCount();
        //$data->objectDbLoad = \Tk\Db\Pdo::getLoadCount();

        $data->upload_max_filesize = ini_get('upload_max_filesize');
        $data->post_max_size = ini_get('post_max_size');
        $data->memory_limit = ini_get('memory_limit');
        //$data->include_path = ini_get('include_path');
        $data->memory_get_peak_usage = 'N/A';
        if (function_exists('memory_get_peak_usage')) {
            $data->memory_get_peak_usage = number_format(memory_get_peak_usage(true)) . ' bytes';
        }
        $data->safe_mode = ini_get('safe_mode') ? 'On' : 'Off';
        $data->register_globals = ini_get('register_globals') ? 'On' : 'Off';
        $data->error_log = ini_get('error_log');
        $data->magic_quotes_gpc = ini_get('magic_quotes_gpc') ? 'On' : 'Off';

        $data->request = '';
        if ($_REQUEST) {
            $data->request = '<div><p><a href="javascript:;" onclick="_DcToggle(\'__request\')">_REQUEST</a></p><pre id="__request">'.nl2br(str_replace("  ", ' ', htmlentities(print_r($_REQUEST, true)))).'</pre></div>';
        }
        $data->files = '';
        if (count($_FILES)) {
            $data->files = '<div><p><a href="javascript:;" onclick="_DcToggle(\'__files\')">_FILES</a></p><pre id="__files">'.nl2br(str_replace("  ", ' ', htmlentities(print_r($_FILES, true)))).'</pre></div>';
        }
        $data->session = '';
        if ($_SESSION) {
            $data->session = '<div><p><a href="javascript:;" onclick="_DcToggle(\'__session\')">_SESSION</a></p><pre id="__session">'.nl2br(str_replace("  ", ' ', htmlentities(print_r($_SESSION, true)))).'</pre></div>';
        }

        $data->extras = '';
        foreach ($this->extras as $title => $extra) {
            $id = preg_replace('/[^a-zA-Z_-]/', '', $title);
            $data->extras .= '<div><p><a href="javascript:;" onclick="_DcToggle(\'__'.$id.'\')">' . $title . '</a></p><pre id="__'.$id.'">'.$extra.'</pre></div>';
        }

        $data->sesName = \Tk\Session::getinstance()->getName();
        $data->sesID = \Tk\Session::getinstance()->getId();
        $data->sesSize = \Tk\Path::bytes2String(strlen(session_encode()));
        $toolsUrl = \Tk\Url::create('/admin/_dev/index.html')->toString();
        $logUrl = \Tk\Url::create('/admin/_dev/tailLog.html')->toString();
        $pageUrl = \Tk\Url::create('/admin/_dev/pageList.html')->toString();
        
        
        
        $contentHtml = <<<STR
<p>
  <a href="$toolsUrl" target="_blank">Dev Tools</a> |
  <a href="$logUrl" target="_blank">Tail Log</a> |
  <a href="$pageUrl" target="_blank">Static Urls</a>
</p>
<p>
  <b>PHP Version:</b> {$data->phpversion} ({$data->os}) <br/>
  <b>\Tk\Request URI:</b> http://{$data->http_host}{$data->request_uri} <br/>
  <b>Referer:</b> {$data->http_referer} <br/>
  <b>Character Set:</b> {$data->http_accept_charset}  ({$data->http_accept_language})<br/>
  <b>Encoding:</b> {$data->http_accept_encoding}<br/>
  <b>Ses Name:</b> {$data->sesName}<br/>
  <b>Ses ID:</b> {$data->sesID  }<br/>
  <b>Ses Size:</b> {$data->sesSize  }
</p>

<p>
  <b>Load Time:</b> {$data->page_load_time} sec
</p>

<p><i><b>-- php.ini --</b></i></p>
<p style="padding-left: 5px;">
  <b>include_path:</b> {$data->include_path}<br/>
  <b>upload_max_filesize:</b> {$data->upload_max_filesize}<br/>
  <b>post_max_size:</b> {$data->post_max_size}<br/>
  <b>memory_limit:</b> {$data->memory_limit}<br/>
  <b>memory_get_peak_usage:</b> {$data->memory_get_peak_usage}<br/>
  <b>safe_mode:</b> {$data->safe_mode}<br/>
  <b>register_globals:</b> {$data->register_globals}<br/>
  <b>magic_quotes_gpc:</b> {$data->magic_quotes_gpc}<br/>
  <b>error_log:</b> {$data->error_log}
</p>

{$data->request}
{$data->session}
{$data->files}
{$data->extras}

<p>&#160;</p>
STR;

        $html = sprintf($this->template, $this->title, $contentHtml);
        $pos = strripos($this->pageHtml, '</body>');
        $fullhtml = substr($this->pageHtml, 0, $pos) . "\n\n" . trim(str_replace(array("\n", '<?xml version="1.0"?>'), ' ', $html)) . "  </body>\n</html>";

        return $fullhtml;
    }


}