<?php
namespace Tk\Db\Util;



/**
 * @author Tropotek <http://www.tropotek.com/>
 */
class ModelProperty extends \Tk\Collection
{
    const TYPE_STRING = 'string';
    const TYPE_INT = 'int';
    const TYPE_FLOAT = 'float';
    const TYPE_DATE = '\DateTime';
    const TYPE_BOOL = 'bool';


    /**
     * The new class property name
     */
    protected string $name = '';

    /**
     * The new php property type
     */
    protected string $type = '';


    /**
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        parent::__construct($data);
        $this->getName();
        $this->getType();
    }

    /**
     * @param array $data
     * @return ModelProperty
     */
    public static function create(array $data): ModelProperty
    {
        $obj = new static($data);
        return $obj;
    }

    public function isPrimaryKey(): bool
    {
        return (strtoupper($this->get('Key')) == 'PRI');
    }

    public function getName(): string
    {
        if (!$this->name) {
            $this->name = preg_replace_callback('/_([a-z])/i', function ($matches) {
                return strtoupper($matches[1]);
            }, $this->get('Field'));
        }
        return $this->name;
    }

    public function getType(): string
    {
        if (!$this->type) {
            $this->type = self::TYPE_STRING;
            $dtype = 'text';
            if (!empty($this->get('Type')))
                $dtype = strtolower($this->get('Type'));

            if (preg_match('/^(tinyint\(1\)|bool|boolean)/', $dtype)) {
                $this->type = self::TYPE_BOOL;
            } else if (preg_match('/^(int|bigint|tinyint|mediumint|integer|bit)/', $dtype)) {
                $this->type = self::TYPE_INT;
            } else if (preg_match('/^(float|decimal|dec|double)/', $dtype)) {
                $this->type = self::TYPE_FLOAT;
            } else if (preg_match('/^(datetime|timestamp|date|time|year)/', $dtype)) {
                $this->type = self::TYPE_DATE;
            }
        }
        return $this->type;
    }

    public function getDefaultValue(): float|bool|int|string
    {
        $def = $this->get('Default');
        switch ($this->getType()) {
            case self::TYPE_BOOL:
                $def = boolval($def) ? 'true' : 'false';
                break;
            case self::TYPE_INT:
                $def = intval($def);
                break;
            case self::TYPE_FLOAT:
                $def = floatval($def);
                break;
            case self::TYPE_STRING:
                $def = $this->quote($def);
                break;
            default:
                $def = 'null';
        }
        return $def;
    }

    public function getDefinition(): string
    {
        $tpl = <<<TPL
    /**
     * @var %s
     */
    public $%s = %s;
TPL;
        return sprintf($tpl, $this->getType(), $this->getName(), $this->getDefaultValue());
    }

    public function getInitaliser(): string
    {
        $tpl = <<<TPL
        \$this->%s = %s;
TPL;
        $val = $this->getDefaultValue();
        if (substr($this->getType(), 0 ,1) == '\\') {
            $val = sprintf('new %s()', $this->getType());
        }
        return sprintf($tpl, $this->getName(), $val);
    }

    public function getAccessor(): string
    {
        $tpl = <<<TPL
    /**
     * @return %s
     */
    public function %s%s() : %s
    {
        return \$this->%s;
    }
TPL;
        // ...
        $method = 'get';
        if ($this->getType() == 'bool' || $this->getType() == 'boolean')
            $method = 'is';

        return sprintf($tpl, $this->getType(), $method, ucfirst($this->getName()), $this->getType(), $this->getName());
    }

    public function getMutator(string $classname = ''): string
    {
        if (!$classname)
            $classname = '$this';
        $param = $this->getName();

        $tpl = <<<TPL
    /**
     * @param %s \$%s
     * @return %s
     */
    public function set%s(\$%s) : %s
    {
        \$this->%s = \$%s;
        return \$this;
    }
TPL;
        // ...

        return sprintf($tpl, $this->getType(), $param, $classname, ucfirst($this->getName()),
            $param, $classname, $this->getName(), $param
        );
    }

    public function getValidation(string $errorParam = 'errors'): string
    {
        $tpl = <<<TPL
        if (!\$this->%s) {
            \$%s['%s'] = 'Invalid value: %s';
        }
TPL;
        return sprintf($tpl, $this->getName(), $errorParam, $this->getName(), $this->getName());
    }

    public function getColumnMap(): string
    {
        $tpl = <<<TPL
            \$this->dbMap->addPropertyMap(new %s(%s%s)%s);
TPL;

        $mapClass = 'Db\Text';
        switch ($this->getType()) {
            case self::TYPE_INT:
                $mapClass = 'Db\Integer';
                break;
            case self::TYPE_FLOAT:
                $mapClass = 'Db\Decimal';
                break;
            case self::TYPE_BOOL:
                $mapClass = 'Db\Boolean';
                break;
            case self::TYPE_DATE:
                $mapClass = 'Db\Date';
                break;
        }

        $propertyName = $this->quote($this->getName());
        $columnName = '';
        if ($this->getName() != $this->get('Field'))
            $columnName = ', '.$this->quote($this->get('Field'));
        $tag = '';
        if ($this->isPrimaryKey())
            $tag = ', ' . $this->quote('key');

        return sprintf($tpl, $mapClass, $propertyName, $columnName, $tag);
    }

    public function getFormMap(): string
    {
        $tpl = <<<TPL
            \$this->formMap->addPropertyMap(new %s(%s)%s);
TPL;

        $mapClass = 'Form\Text';
        switch ($this->getType()) {
            case self::TYPE_INT:
                $mapClass = 'Form\Integer';
                break;
            case self::TYPE_FLOAT:
                $mapClass = 'Form\Decimal';
                break;
            case self::TYPE_BOOL:
                $mapClass = 'Form\Boolean';
                break;
            case self::TYPE_DATE:
                $mapClass = 'Form\Date';
                break;
        }

        $propertyName = $this->quote($this->getName());
        $tag = '';
        if ($this->isPrimaryKey())
            $tag = ', ' . $this->quote('key');

        return sprintf($tpl, $mapClass, $propertyName, $tag);
    }

    public function getFilterQuery(): string
    {
        if ($this->getName() == 'id' ) return '';

        $tpl = <<<TPL
        if (!empty(\$filter['%s'])) {
            \$filter->appendWhere('a.%s = %%s AND ', %s);
        }
TPL;
        if (preg_match('/Id$/', $this->getName()) && $this->getType() == self::TYPE_INT) {
            $tpl = <<<TPL
        if (!empty(\$filter['%s'])) {
            \$filter->appendWhere('a.%s = %%s AND ', %s);
        }
TPL;
        }

        $filterVal = sprintf("\$this->quote(\$filter['%s'])", $this->getName());
        switch ($this->getType()) {
            case self::TYPE_INT:
            case self::TYPE_BOOL:
                $filterVal = sprintf("(int)\$filter['%s']", $this->getName());
                break;
            case self::TYPE_FLOAT:
                $filterVal = sprintf("(float)\$filter['%s']", $this->getName());
                break;
//            case self::TYPE_BOOL:
//                $filterVal = sprintf("(int)\$filter['%s']", $this->getName());
//                break;
        }

        return sprintf($tpl, $this->getName(), $this->get('Field'), $filterVal);
    }

    public function getTableCell(string $className, string $namespace): string
    {
        $tpl = <<<TPL
        \$this->appendCell(new %s(%s))%s;
TPL;

        $mapClass = 'Cell\Text';
        switch ($this->getType()) {
            case self::TYPE_BOOL:
                $mapClass = 'Cell\Boolean';
                break;
            case self::TYPE_DATE:
                $mapClass = 'Cell\Date';
                break;
        }
        if ($this->getName() == 'id') {
            $mapClass = 'Cell\Checkbox';
        }

        $propertyName = $this->quote($this->getName());
        $append = '';
        if ($this->getName() == 'name' || $this->getName() == 'title') {
            $append .= sprintf('->addCss(\'key\')');
            $append .= sprintf('->setUrl($this->getEditUrl())', lcfirst($className));
            //$append .= sprintf('->setUrl(\Bs\Uri::createHomeUrl(\'/%sEdit.html\'))', lcfirst($className));
        }

        return sprintf($tpl, $mapClass, $propertyName, $append);
    }

    public function getFormField(string $className, string $namespace, bool $isModelForm = false): string
    {
        $tpl = <<<TPL
        \$this->appendField(new %s(%s%s))%s;
TPL;
        if ($isModelForm) {
            $tpl = <<<TPL
        \$this->getForm()->appendField(new %s(%s%s))%s;
TPL;
        }

        $mapClass = 'Field\Input';
        $argAppend = '';
        $append = '';
        if ($this->get('Type') == 'text') {
            $mapClass = 'Field\Textarea';
        }
        if ($this->getType() == self::TYPE_BOOL) {
            $mapClass = 'Field\Checkbox';
        }
        if (str_ends_with($this->getName(), 'Id')) {
            $mapClass = 'Field\Select';
            $argAppend = sprintf(', []');
            $append = sprintf('->prependOption(\'-- Select --\', \'\')');
        }
        $propertyName = $this->quote($this->getName());

        return sprintf($tpl, $mapClass, $propertyName, $argAppend, $append);
    }

    protected function quote(string $str, string $q = "'"): string
    {
        return $q.$str.$q;
    }

}