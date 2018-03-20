<?php
namespace tests;


/**
 * Class EncryptTest
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @see http://www.tropotek.com/
 * @license Copyright 2016 Michael Mifsud
 */
class CurlyTemplateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected $html = '';

    /**
     * @var \Tk\CurlyTemplate
     */
    protected $template = null;


    public function __construct()
    {
        parent::__construct('CurlyTemplate Test');

    }

    public function setUp()
    {

        $this->html = <<<HTML
<html>
  <head>
    <title>{headTitle}</title>
  </head>
  <body>
    <h1>{pageTitle}</h1>
    <table>{rowBlock}
        <tr>{dataBlock}
          <td>{dataValue}</td>{/dataBlock}
        </tr>{/rowBlock}
    </table>
    <ul>{listBlock}
      <li><a href="{linkUrl}">{linkText}</a></li>{\listBlock}
    </ul>
  </body>
</html>
HTML;
        $this->template = \Tk\CurlyTemplate::create($this->html);



    }

    public function tearDown()
    {

    }

    public function testNestedBlocks()
    {
        $str = $this->template->parse(array(
            'headTitle' => 'This is the Head Title.',
            'pageTitle' => 'This is the main page title.',
            'rowBlock' => array(
                array(
                    'dataBlock' => array(
                        array('dataValue' => 'dataValue1.1'),
                        array('dataValue' => 'dataValue1.2'),
                        array('dataValue' => 'dataValue1.3')
                    )
                ),
                array(
                    'dataBlock' => array(
                        array('dataValue' => 'dataValue2.1'),
                        array('dataValue' => 'dataValue2.2'),
                        array('dataValue' => 'dataValue2.3')
                    )
                ),
                array(
                    'dataBlock' => array(
                        array('dataValue' => 'dataValue3.1'),
                        array('dataValue' => 'dataValue3.2'),
                        array('dataValue' => 'dataValue3.3')
                    )
                )
            ),
            'listBlock' => array(
                array('linkUrl' => 'link1.html', 'linkText' => 'Link 1'),
                array('linkUrl' => 'link2.html', 'linkText' => 'Link 2'),
                array('linkUrl' => 'link3.html', 'linkText' => 'Link 3'),
                array('linkUrl' => 'link4.html', 'linkText' => 'Link 4')
            )
        ));

        $res = <<<HTML
<html>
  <head>
    <title>This is the Head Title.</title>
  </head>
  <body>
    <h1>This is the main page title.</h1>
    <table>
        <tr>
          <td>dataValue1.1</td>
          <td>dataValue1.2</td>
          <td>dataValue1.3</td>
        </tr>
        <tr>
          <td>dataValue2.1</td>
          <td>dataValue2.2</td>
          <td>dataValue2.3</td>
        </tr>
        <tr>
          <td>dataValue3.1</td>
          <td>dataValue3.2</td>
          <td>dataValue3.3</td>
        </tr>
    </table>
    <ul>
      <li><a href="link1.html">Link 1</a></li>
      <li><a href="link2.html">Link 2</a></li>
      <li><a href="link3.html">Link 3</a></li>
      <li><a href="link4.html">Link 4</a></li>
    </ul>
  </body>
</html>
HTML;

        $this->assertEquals($res, $str);
    }

    public function testChoiceBlocks()
    {
        $str = $this->template->parse(array(
            'headTitle' => 'This is the Head Title.',
            'pageTitle' => 'This is the main page title.',
            'rowBlock' => true,
            'dataBlock' => true,
            'dataValue' => 'This is a test'
        ));

        $res = <<<HTML
<html>
  <head>
    <title>This is the Head Title.</title>
  </head>
  <body>
    <h1>This is the main page title.</h1>
    <table>
        <tr>
          <td>This is a test</td>
        </tr>
    </table>
    <ul>
    </ul>
  </body>
</html>
HTML;


        $this->assertEquals($res, $str);
    }


    public function testChoiceBlocksTwo()
    {
        $str = $this->template->parse(array(
            'headTitle' => 'This is the Head Title.',
            'pageTitle' => 'This is the main page title.',
            'rowBlock' => false,
            'dataBlock' => true,
            'dataValue' => 'This is a test'
        ));

        $res = <<<HTML
<html>
  <head>
    <title>This is the Head Title.</title>
  </head>
  <body>
    <h1>This is the main page title.</h1>
    <table>
    </table>
    <ul>
    </ul>
  </body>
</html>
HTML;

        $this->assertEquals($res, $str);
    }


}

