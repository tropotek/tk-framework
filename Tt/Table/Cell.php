<?php
namespace Tt\Table;

use Tk\CallbackCollection;
use Tk\Ui\Attributes;
use Tk\Ui\Traits\AttributesTrait;
use Tk\Uri;
use Tt\Table;

class Cell
{
    use AttributesTrait;

    protected string     $name        = '';
    protected ?string    $value       = null;
    protected string     $header      = '';
    protected bool       $visible     = true;
    protected bool       $sortable    = false;
    protected ?Table     $table       = null;

    protected Attributes $headerAttrs;
    protected CallbackCollection $onValue;


    public function __construct(string $name, string $header = '')
    {
        $this->name = $name;
        $this->onValue = CallbackCollection::create();

        $this->headerAttrs = new Attributes();
        $this->addCss('m'.ucfirst($name));
        $this->headerAttrs->addCss('mh'.ucfirst($name));

        if (!$header) {  // Set the default header label if none supplied
            $header = preg_replace('/(Id|_id)$/', '', $name);
            $header = str_replace(['_', '-'], ' ', $header);
            $header = ucwords(preg_replace('/[A-Z]/', ' $0', $header));
        }
        $this->setHeader($header);
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Callbacks are executed when getValue() is called
     * @callable function (array|object $row, Cell $cell) {  }
     */
    public function addOnValue(callable $callable, int $priority = CallbackCollection::DEFAULT_PRIORITY): static
    {
        $this->getOnValue()->append($callable, $priority);
        return $this;
    }

    /**
     * an array of callable types, called with call_user_func_array()
     */
    public function getOnValue(): CallbackCollection
    {
        return $this->onValue;
    }

    /**
     * Get the cell value:
     *     1. execute callbacks return non-null value
     *     2. return non-null value from this cells value property
     *     3. return the value if exists in the $row
     *
     */
    public function getValue(array|object $row): mixed
    {
        if (is_array($row)) $row = (object)$row;
        $return = $this->getOnValue()->execute($row, $this);
        if (!is_null($return)) return $return;
        if (!is_null($this->value)) return $this->value;
        return $row->{$this->getName()} ?? null;
    }

    public function setValue(mixed $value): static
    {
        $this->value = $value;
        return $this;
    }

    public function setHeader(string $header): static
    {
        $this->header = $header;
        return $this;
    }

    public function getHeader(): string
    {
        return $this->header;
    }

    public function getHeaderAttrs(): Attributes
    {
        return $this->headerAttrs;
    }

    public function setHeaderAttrs(Attributes $headerAttrs): static
    {
        $this->headerAttrs = $headerAttrs;
        return $this;
    }

    public function setVisible(bool $visible): static
    {
        $this->visible = $visible;
        return $this;
    }

    public function isVisible(): bool
    {
        return $this->visible;
    }

    public function isSortable(): bool
    {
        return $this->sortable;
    }

    public function setSortable(bool $sortable): static
    {
        $this->sortable = $sortable;
        return $this;
    }

    public function getTable(): ?Table
    {
        return $this->table;
    }

    public function setTable(?Table $table): Cell
    {
        $this->table = $table;
        return $this;
    }

    /**
     * Get the order by url for this cell.
     * This will create an orderBy URL, when clicked it will
     * redirect the page and update the table order to the opposite order
     */
    public function getOrderByUrl(): ?Uri
    {
        if (!$this->isSortable()) return null;

        $key = $this->getTable()->makeRequestKey(Table::PARAM_ORDERBY);
        $url = Uri::create()->remove($key);

        $col = $this->getTable()->getOrderBy();
        $dir = '-';
        if ($col && $col[0] == '-') {
            $col = substr($col, 1);
            $dir = '';
        }

        if ($col == $this->getName()) {
            if ($dir == '-') {
                $url->set($key, $dir.$col);
            } else {
                $url->set($key, '');
            }
        } else {
            $url->set($key, $this->getName());
        }

        return $url;
    }

}