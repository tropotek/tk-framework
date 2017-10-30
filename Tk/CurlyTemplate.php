<?php
namespace Tk;

/**
 * 
 * This is a curly braces style template parser.
 * 
 * Note: Strings will replace all '{paramName}' curly params in the template
 *       However blocks can be use for repeating or choice elements '{blockName}...{/blockName}'
 *
 * Template Example:
 * <code>
 *
 *  <html>
 *    <head>
 *      <title>{headTitle}</title>
 *    </head>
 *    <body>
 *      <h1>{pageTitle}</h1>
 *      <table>
 *        {rowBlock}
 *          <tr>
 *          {dataBlock}
 *            <td>{dataValue}</td>
 *          {/dataBlock}
 *          </tr>
 *        {/rowBlock}
 *      </table>
 *
 *      <div>{dynamicData}</div>
 *    </body>
 *  </html>
 *
 * </code>
 *
 * 
 * 
 * Data Example 1: Using nested arrays for repeating blocks
 * 
 * <code>
 * 
 *   $tpl->parse(array(
 *     'headTitle' => 'This is the Head Title.',
 *     'pageTitle' => 'This is the main page title.',
 *     
 *     'rowBlock' => array(
 *       array(
 *         'dataBlock' => array(
 *           array('dataValue' => 'dataValue1.1'),
 *           array('dataValue' => 'dataValue1.2'),
 *           array('dataValue' => 'dataValue1.3')
 *         )
 *       ),
 *       array(
 *         'dataBlock' => array(
 *           array('dataValue' => 'dataValue2.1'),
 *           array('dataValue' => 'dataValue2.2'),
 *           array('dataValue' => 'dataValue2.3')
 *         )
 *       ),
 *       array(
 *         'dataBlock' => array(
 *           array('dataValue' => 'dataValue3.1'),
 *           array('dataValue' => 'dataValue3.2'),
 *           array('dataValue' => 'dataValue3.3')
 *         )
 *       )
 *     ),
 *     'listBlock' => array(
 *         array('linkUrl' => 'link1.html', 'linkText' => 'Link 1'),
 *         array('linkUrl' => 'link2.html', 'linkText' => 'Link 2'),
 *         array('linkUrl' => 'link3.html', 'linkText' => 'Link 3'),
 *         array('linkUrl' => 'link4.html', 'linkText' => 'Link 4')
 *       )
 *     ),
 *     'dynamicData' => function ($curlyTemplate) use ($message) { return 'Some String...'; }   // <---- must return a string...
 *   );
 * 
 * </code>
 * 
 * Data Example 2: Use boolean values to show/hide blocks 
 * 
 * <code>
 *   $tpl->parse(array(
 *       'headTitle' => 'This is the Head Title.',
 *       'pageTitle' => 'This is the main page title.',
 *       'rowBlock' => true,
 *       'dataBlock' => true,
 *       'dataValue' => 'This is a fricken` test'
 *     )
 *   )
 * </code>
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2016 Michael Mifsud
 * @todo Add caching ability
 */
class CurlyTemplate
{
    
    /**
     * @var string
     */
    protected $ld = '{';

    /**
     * @var string
     */
    protected $rd = '}';

    /**
     * @var string
     */
    protected $template = '';


    /**
     * CurlyTemplate constructor.
     * @param string $template
     */
    public function __construct($template)
    {
        $this->template = $template;

    }


    /**
     * Create a CurlyTemplate object
     *
     * @param string $template
     * @return CurlyTemplate
     */
    static function create($template)
    {
        $tpl = new static($template);
        
        return $tpl;
    }

    /**
     * Parse the template with the supplied data.
     * 
     * This is a mutable object and parse() can be called multiple times with 
     * different data collections without issue.
     * 
     * 
     * @param array $data
     * @return string
     */
    function parse($data)
    {
        $template = $this->template;
        $template = $this->parseRecursive($template, $data);
        $template = $this->parseBlock($template, $data);
        return $template;
    }

    /**
     * Parse a block and replace all curly variable with their
     * appropriate data value from the data array
     *
     * @param string $str
     * @param array $data
     * @return string
     * @throws Exception
     */
    private function parseBlock($str, $data)
    {
        if (!is_array($data)) return $str;
        foreach($data as $k => $v) {
            if (is_callable($v)) {
                $v = call_user_func_array($v, array($this));
                if (!is_string($v)) throw new \Tk\Exception('Invalid return type. Function must return a string.');
            }
            if (!is_string($v) && !is_numeric($v)) continue;
            $str = str_replace($this->ld . $k . $this->rd, $v, $str);
        }
        return $str;
    }

    /**
     * @param string $template
     * @param array $data
     * @return string
     *
     * @todo Add escape delimiters '{{' and '}}
     */
    private function parseRecursive($template, $data = null)
    {
        $ld = preg_quote($this->ld);
        $rd = preg_quote($this->rd);

        // Reg to find the curly text blocks
        $reg = '#' . $ld . '([^' . $ld . ']*?)' .  $rd . '(.*?)' . $ld . '[^' . $ld . ']\1' . $rd . '#si';
        $template = preg_replace_callback($reg, function ($matches) use ($data) {
            $tplData = $data;
            if (isset($matches[0])) {
                $tpl = $matches[2];
                $name = $matches[1];
                if (isset($tplData[$name]) && $tplData[$name] !== false) {
                    if (isset($tplData[$name][0])) {
                        $block = '';
                        foreach($tplData[$name] as $rowData) {
                            $block .= $this->parseRecursive($tpl, $rowData);
                        }
                        return $block;
                    } else {
                        if ($tplData[$name] === true) {
                            return $this->parseRecursive($tpl, $tplData);
                        } else {
                            return $this->parseRecursive($tpl, $tplData[$name]);
                        }
                    }
                }
            }
            return '';
        }, $template);

        return $this->parseBlock($template, $data);
    }

    /**
     *  Set the left/right variable delimiters
     *
     * @param string $ld Left Delimiter
     * @param string $rd Right Delimiter
     * @return $this
     */
    public function setDelimiters($ld = '{', $rd = '}')
    {
        $this->ld = $ld;
        $this->rd = $rd;
        return $this;
    }

}