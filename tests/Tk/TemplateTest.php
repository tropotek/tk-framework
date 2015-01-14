<?php
/**
 * Created by PhpStorm.
 * User: mifsudm
 * Date: 1/15/15
 * Time: 9:12 AM
 */

namespace Tk\Test;


class TemplateTest extends \PHPUnit_Framework_TestCase {


    public function __construct()
    {
        parent::__construct('Template Test');
        \Ext\Config::getInstance( dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) );
    }

    public function setUp()
    {

    }

    public function tearDown()
    {

    }


    public function testSimpleTemplate()
    {
        $in = <<<HTML
<html>
 <head>
   <title>{docTitle}</title>
 </head>
 <body>
   <h1>{pageTitle}</h1>
   <table>{rowBlock}
       <tr>{dataBlock}
         <td>{dataValue}</td>{/dataBlock}{dataBlockHidden}
         <td>{dataValueHidden}</td>{/dataBlockHidden}
       </tr>{/rowBlock}
   </table>
   <p>{dataBlock1}</p>
   <p>Content For Data Block 1</p>
   <p>{/dataBlock1}</p>
 </body>
</html>
HTML;
        $params = array(
            'docTitle' => 'Doc Title Test',
            'pageTitle' => 'Page Title Test',
            'rowBlock' => true,
            'dataBlock' => true,
            'dataBlock1' => true,
            'dataBlockHidden' => false,
            'dataValue' => 'Data Value Test',
            'dataValueHidden' => '----'
        );

        $result1 = <<<HTML
<html>
 <head>
   <title>Doc Title Test</title>
 </head>
 <body>
   <h1>Page Title Test</h1>
   <table>
       <tr>
         <td>Data Value Test</td>
         <td>----</td>
       </tr>
   </table>
   <p></p>
   <p>Content For Data Block 1</p>
   <p></p>
 </body>
</html>
HTML;

        $tpl = \Tk\Template::parseTemplate($in, $params);

        tklog($tpl);

        $this->assertEquals(trim($tpl), trim($result1));

    }



}
