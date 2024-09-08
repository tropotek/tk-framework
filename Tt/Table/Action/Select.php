<?php
namespace Tt\Table\Action;

use Tk\CallbackCollection;
use Tk\Ui\Traits\AttributesTrait;
use Tk\Uri;
use Tt\Table\Action;
use Tt\Table\Cell\RowSelect;

/**
 * This action depends on \Tk\Table\Cell\RowSelect Cell
 * Use this to attach an action that can be triggered on selected rows
 *
 * NOTE: This Action does not call the onExecute() or onShow() callback queues
 */
class Select extends Action
{
    use AttributesTrait;

    protected string             $confirmStr = 'Execute the selected records?';
    protected string             $icon       = '';
    protected RowSelect          $rowSelect;
    protected CallbackCollection $onSelect;


    public function __construct(string $name)
    {
        parent::__construct($name);
        $this->onSelect = CallbackCollection::create();
        $this->addCss('btn btn-sm btn-light tk-action-select');
        $this->setAttr('disabled');
        $this->setAttr('data-confirm', $this->confirmStr);
    }

    public static function create(RowSelect $rowSelect, string $name = 'select', $icon = 'fa fa-fw fa-check'): static
    {
        $obj = new static($name);
        $obj->rowSelect = $rowSelect;
        $obj->icon = $icon;
        $obj->setAttr('data-row-select', $rowSelect->getName());
        return $obj;
    }

    public function execute(): void
    {
        $val = $this->getTable()->makeRequestKey($this->getName());
        $this->setActive(($_POST[$this->getName()] ?? '') == $val);
        if (!$this->isActive()) return;

        $selected = $_POST[$this->rowSelect->getName()] ?? [];
        $this->getOnSelect()->execute($this, $selected);
        Uri::create()->redirect();
    }

    /**
     * @note see `tkTable.js` for supporting JS to this action
     */
    public function getHtml(): string
    {
        $val = $this->getTable()->makeRequestKey($this->getName());

        return <<<HTML
<button type="submit" name="{$this->getName()}" value="{$val}" class="{$this->getCssString()}" {$this->getAttrString()}>
    <i class="{$this->icon}"></i> {$this->getLabel()}
</button>
HTML;
    }

    protected function getConfirmStr(): string
    {
        return $this->confirmStr;
    }

    public function setConfirmStr(string $confirmStr): static
    {
        $this->confirmStr = $confirmStr;
        $this->setAttr('data-confirm', $this->confirmStr);
        return $this;
    }

    /**
     * @callable function (\Tk\Table\Action\Delete $action, $obj): ?bool { }
     */
    public function addOnSelect(callable $callable, int $priority = CallbackCollection::DEFAULT_PRIORITY): static
    {
        $this->getOnSelect()->append($callable, $priority);
        return $this;
    }

    public function getOnSelect(): CallbackCollection
    {
        return $this->onSelect;
    }

}