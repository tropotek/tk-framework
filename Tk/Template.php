<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Tk;

/**
 * This is a string template type system
 *
 * EG:
 *
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
 *    </body>
 *  </html>
 *
 * </code>
 *
 * This template is designed to work with standard string type templates.
 *
 * @todo Nested blocks are not currently working until we get some other method of implementation.
 */
class Template extends Object
{
    /**
     * @var string
     */
    protected $lDelim = '{';

    /**
     * @var string
     */
    protected $rDelim = '}';

    /**
     * Parse a template fom this static call.
     *
     * @param $template
     * @param $data
     * @return string
     */
    static function parseTemplate($template, $data)
    {
        $tpl = new self();
        return $tpl->parse($template, $data);
    }

    /**
     *
     * @param string $template
     * @param $data
     * @return string
     */
    function parse($template, $data)
    {
        $template = $this->parseTagsRecursive($template, $data);
        $template = $this->parseBlock($template, $data);
        return $template;
    }

    /**
     * @param $input
     * @param array $data
     * @return string
     * @todo Get nested blocks working if possible
     */
    private function parseTagsRecursive($input, $data = array())
    {
        $ld = preg_quote($this->lDelim);
        $rd = preg_quote($this->rDelim);

        // original regex, not working?
        //$reg = '#' . $ld . '([^' . $ld . ']*?)' .  $rd . '((?:[^{]|{(?!/?(.+)})|(?R))+)' . $ld . '[^' . $ld . ']\1' . $rd . '#si';

        // My interpretation that seems to work
        $reg = '#' . $ld . '([^' . $ld . ']*?)' .  $rd . '(.*?)' . $ld . '[^' . $ld . ']\1' . $rd . '#si';

        if (is_array($input)) {
            $name = $input[1];
            if (!isset($data[$name])) {
                return '';
            }

            $input = $input[2];
            if (isset($data[$name][0])) {
                $data = $data[$name];
                $block = '';
                foreach($data as $rowData) {
                    $block .= $this->parseBlock($input, $rowData);
                }
                $input = $block;
            } else {
                $input = $this->parseBlock($input, $data);
            }
        }

        return preg_replace_callback($reg, function ($matches) use ($data) {
            return $this->parseTagsRecursive($matches, $data);
        }, $input);
    }

    /**
     *
     *
     * @param string $string
     * @param array $data
     * @return string
     */
    private function parseBlock($string, $data)
    {
        foreach($data as $k => $v) {
            if (is_array($v)) continue;
            if ($v === true || $v === false) continue;
            $string = str_replace($this->lDelim . $k . $this->rDelim, $v, $string);
        }
        return $string;
    }

    /**
     *  Set the left/right variable delimiters
     *
     * @access    public
     * @param    string
     * @param    string
     * @return    void
     */
    public function setDelimiters($l = '{', $r = '}')
    {
        $this->lDelim = $l;
        $this->rDelim = $r;
    }



}