<?php
namespace Tk\Db\Util;

class ModelProperty extends \Tk\Collection
{
    const TYPE_ARRAY   = 'array';
    const TYPE_STRING  = 'string';
    const TYPE_INT     = 'int';
    const TYPE_FLOAT   = 'float';
    const TYPE_DATE    = '\DateTime';
    const TYPE_BOOL    = 'bool';

    /**
     * The new class property name
     */
    protected string $name = '';

    /**
     * The new php property type
     */
    protected string $type = '';


    public function __construct(array $data = [])
    {
        parent::__construct($data);
        $this->getName();
        $this->getType();
    }

    public static function create(array $data): ModelProperty
    {
        return new static($data);
    }

    public function isPrimaryKey(): bool
    {
        return (strtoupper($this->get('Key') ?? '') == 'PRI');
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

    public function getDefaultValue(): float|bool|int|string|null
    {
        $def = $this->get('Default');

        if ($def) {
            $def = str_replace("''", '', $def);
        }

        return match ($this->getType()) {
            self::TYPE_BOOL => boolval($def ?? false) ? 'true' : 'false',
            self::TYPE_INT => intval($def ?? 0),
            self::TYPE_FLOAT => floatval($def ?? 0.0),
            self::TYPE_STRING => $this->quote($def ?? ''),
            self::TYPE_DATE => null,
            default => 'null',
        };
    }

    public function getDefinition(): string
    {
        $tpl = <<<PHP
            public %s $%s%s;
        PHP;
        $value = $this->getDefaultValue();
        if (!is_null($value)) $value = ' = ' . $value;
        return sprintf($tpl,
            $this->getType(),
            $this->getName(),
            $value
        );
    }

    public function getInitaliser(): string
    {
        $val = $this->getDefaultValue();
        if (str_starts_with($this->getType(), '\\')) {
            $val = sprintf('new %s()', $this->getType());
        }

        $tpl = <<<TPL
                \$this->%s = %s;
        TPL;
        return sprintf($tpl, $this->getName(), $val);
    }

    public function getAccessor(): string
    {
        $method = 'get';
        if ($this->getType() == 'bool' || $this->getType() == 'boolean')
            $method = 'is';

        $tpl = <<<TPL
            public function %s%s(): %s
            {
                return \$this->%s;
            }
        TPL;

        return sprintf($tpl,
            $method,
            ucfirst($this->getName()),
            $this->getType(),
            $this->getName()
        );
    }

    public function getMutator(string $classname = ''): string
    {
        if (!$classname)
            $classname = 'static';

        $tpl = <<<TPL
            public function set%s(%s \$%s): %s
            {
                \$this->%s = \$%s;
                return \$this;
            }
        TPL;

        return sprintf($tpl,
            ucfirst($this->getName()),
            $this->getType(),
            $this->getName(),
            $classname,
            $this->getName(),
            $this->getName()
        );
    }

    public function getValidation(string $errorParam = 'errors'): string
    {
        $tpl = <<<TPL
                if (!\$this->%s) {
                    \$%s['%s'] = 'Invalid value: %s';
                }
        TPL;

        return sprintf($tpl,
            $this->getName(),
            $errorParam,
            $this->getName(),
            $this->getName()
        );
    }

    public function getColumnMap(): string
    {
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
//        if ($this->isPrimaryKey())
//            $tag = ', ' . $this->quote('key');

        $tpl = <<<TPL
                    \$map->addDataType(new %s(%s%s)%s);
        TPL;

        return sprintf($tpl,
            $mapClass,
            $propertyName,
            $columnName,
            $tag
        );
    }

    public function getFormMap(): string
    {
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
//        if ($this->isPrimaryKey())
//            $tag = ', ' . $this->quote('key');

        $tpl = <<<TPL
                    \$map->addDataType(new %s(%s)%s);
        TPL;
        return sprintf($tpl,
            $mapClass,
            $propertyName,
            $tag
        );
    }


    public function getTableMap(): string
    {
        $mapClass = 'Form\Text';
        switch ($this->getType()) {
            case self::TYPE_INT:
                $mapClass = 'Form\Integer';
                break;
            case self::TYPE_FLOAT:
                $mapClass = 'Form\Decimal';
                break;
            case self::TYPE_BOOL:
                $mapClass = 'Table\Boolean';
                break;
            case self::TYPE_DATE:
                $mapClass = 'Form\Date';
                break;
        }

        $propertyName = $this->quote($this->getName());
        $tag = '';
//        if ($this->isPrimaryKey())
//            $tag = ', ' . $this->quote('key');

        $tpl = <<<TPL
                    \$map->addDataType(new %s(%s)%s)
        TPL;
        if ($this->getType() == self::TYPE_DATE) {
            $tpl .= "->setDateFormat('d/m/Y h:i:s')";
        }
        $tpl .= ';';
        return sprintf($tpl,
            $mapClass,
            $propertyName,
            $tag
        );
    }

    public function getFilterQuery(): string
    {
        //if ($this->getName() == 'id' ) return '';
        if ($this->isPrimaryKey()) return '';

        $filterVal = sprintf("\$this->quote(\$filter['%s'])", $this->getName());
        switch ($this->getType()) {
            case self::TYPE_INT:
            case self::TYPE_BOOL:
                $filterVal = sprintf("(int)\$filter['%s']", $this->getName());
                break;
            case self::TYPE_FLOAT:
                $filterVal = sprintf("(float)\$filter['%s']", $this->getName());
                break;
        }

        $tpl = <<<TPL
                if (!empty(\$filter['%s'])) {
                    \$filter->appendWhere('a.%s = %%s AND ', %s);
                }
        TPL;
        if (str_ends_with($this->getName(), 'Id') && $this->getType() == self::TYPE_INT) {
            $tpl = <<<TPL
                    if (!empty(\$filter['%s'])) {
                        \$filter->appendWhere('a.%s = %%s AND ', %s);
                    }
            TPL;
        }
        return sprintf($tpl,
            $this->getName(),
            $this->get('Field'),
            $filterVal
        );
    }

    public function getPreparedFilterQuery(): string
    {
        if ($this->isPrimaryKey()) return '';

        $filterValid = sprintf("!empty(\$filter['%s'])", $this->getName());
        if ($this->getType() == self::TYPE_BOOL) {
            $filterValid = sprintf("!\$this->isEmpty(\$filter['%s'] ?? null)", $this->getName());
        }

        $tpl = <<<TPL
                if (%s) {
                    \$filter->appendWhere('a.%s = :%s AND ');
                }
        TPL;
        return sprintf($tpl,
            $filterValid,
            $this->get('Field'),
            $this->getName()
        );
    }

    public function getTableCell(string $className, string $namespace): string
    {
        $mapClass = 'Cell\Text';
        switch ($this->getType()) {
            case self::TYPE_BOOL:
                $mapClass = 'Cell\Boolean';
                break;
//            case self::TYPE_DATE:
//                $mapClass = 'Cell\Date';
//                break;
        }
        if ($this->isPrimaryKey()) {
            $mapClass = 'Cell\Checkbox';
        }

        $propertyName = $this->quote($this->getName());
        $append = '';
        if ($this->getName() == 'name' || $this->getName() == 'title') {
            $append .= '->addCss(\'key\')';
            $append .= '->setUrl($editUrl)';
        }

        $tpl = <<<TPL
                \$this->appendCell(new %s(%s))%s;
        TPL;

        return sprintf($tpl,
            $mapClass,
            $propertyName,
            $append
        );
    }

    public function getFormField(string $className, string $namespace, bool $isModelForm = false): string
    {
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

        $tpl = <<<TPL
                \$this->appendField(new %s(%s%s))%s;
        TPL;
        if ($isModelForm) {
            $tpl = <<<TPL
                    \$this->appendField(new %s(%s%s))%s;
            TPL;
        }

        return sprintf($tpl,
            $mapClass,
            $propertyName,
            $argAppend,
            $append
        );
    }

    protected function quote(string $str, string $q = "'"): string
    {
        return $q.$str.$q;
    }

}