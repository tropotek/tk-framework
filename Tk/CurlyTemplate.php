<?php
namespace Tk;

/**
 *
 * This is a curly braces style template parser.
 *
 * Note: Strings will replace all '{paramName}' curly params in the template
 *       However blocks can be used for repeating or choice elements '{blockName}...{/blockName}'
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
 *   $tpl->parse([
 *     'headTitle' => 'This is the Head Title.',
 *     'pageTitle' => 'This is the main page title.',
 *     'rowBlock' => [
 *       [
 *         'dataBlock' => [
 *           ['dataValue' => 'dataValue1.1'],
 *           ['dataValue' => 'dataValue1.2'],
 *           ['dataValue' => 'dataValue1.3'],
 *         ]
 *       ],
 *       [
 *         'dataBlock' => [
 *           ['dataValue' => 'dataValue2.1'],
 *           ['dataValue' => 'dataValue2.2'],
 *           ['dataValue' => 'dataValue2.3'],
 *         ]
 *       ],
 *       [
 *         'dataBlock' => [
 *           ['dataValue' => 'dataValue3.1'],
 *           ['dataValue' => 'dataValue3.2'],
 *           ['dataValue' => 'dataValue3.3'],
 *         ]
 *       ],
 *     ],
 *     'listBlock' => [
 *         ['linkUrl' => 'link1.html', 'linkText' => 'Link 1']
 *         ['linkUrl' => 'link2.html', 'linkText' => 'Link 2']
 *         ['linkUrl' => 'link3.html', 'linkText' => 'Link 3']
 *         ['linkUrl' => 'link4.html', 'linkText' => 'Link 4']
 *       ]
 *     ],
 *     'dynamicData' => function ($curlyTemplate) use ($message) { return 'Some String...'; }
 *   );
 * </code>
 *
 * Data Example 2: Use boolean values to show/hide blocks
 *
 * <code>
 *   $tpl->parse([
 *       'headTitle' => 'This is the Head Title.',
 *       'pageTitle' => 'This is the main page title.',
 *       'rowBlock' => true,
 *       'dataBlock' => true,
 *       'dataValue' => 'This is a test` test'
 *     ]
 *   )
 * </code>
 */
class CurlyTemplate
{
    protected string $ld = '{';
    protected string $rd = '}';
    protected string $template = '';


    public function __construct(string $template)
    {
        $this->template = $template;
    }

    static function create(string $template): self
    {
        return new self($template);
    }

    /**
     * Parse the template with the supplied data.
     *
     * This is a mutable object and parse() can be called multiple times with
     * different data collections without issue.
     */
    function parse(array $data = []): string
    {
        $template = $this->template;
        $template = $this->parseRecursive($template, $data);
        return $this->parseBlock($template, $data);
    }

    /**
     * Parse a block and replace all curly variable with their
     * appropriate data value from the data array
     */
    protected function parseBlock(string $str, array $data): string
    {
        foreach($data as $k => $v) {
            if (!is_string($v) && is_callable($v)) {
                $v = call_user_func_array($v, [$this]);
                if (!is_string($v)) throw new \Tk\Exception('Invalid return type. Function must return a string.');
            }
            if (!is_string($v) && !is_numeric($v)) continue;
            $str = str_replace($this->ld . $k . $this->rd, $v, $str);
        }
        return $str;
    }

    private function parseRecursive(string $template, ?array $data = null): string
    {
        $ld = preg_quote($this->ld);
        $rd = preg_quote($this->rd);

        // Reg to find the curly text blocks
        $reg = '#' . $ld . '([^' . $ld . ']*?)' .  $rd . '(.*?)' . $ld . '[^' . $ld . ']\1' . $rd . '#si';
        $ctpl = $this;
        $template = preg_replace_callback($reg, function ($matches) use ($data, $ctpl) {
            $tplData = $data;
            if (isset($matches[0])) {
                $tpl = $matches[2];
                $name = $matches[1];
                if (isset($tplData[$name]) && $tplData[$name] !== false) {
                    if (is_array($tplData[$name]) && isset($tplData[$name][0])) {
                        $block = '';
                        foreach($tplData[$name] as $rowData) {
                            $block .= $ctpl->parseRecursive($tpl, $rowData);
                        }
                        return $block;
                    } else {
                        if ($tplData[$name] === true) {
                            return $ctpl->parseRecursive($tpl, $tplData);
                        } else {
                            return $ctpl->parseRecursive($tpl, $tplData[$name]);
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
     */
    public function setDelimiters(string $ld = '{', string $rd = '}'): self
    {
        $this->ld = $ld;
        $this->rd = $rd;
        return $this;
    }

}