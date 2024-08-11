<?php
namespace Tt\Table\Action;

use Symfony\Component\HttpFoundation\Request;
use Tk\CallbackCollection;
use Tk\Uri;
use Tt\Table\Action;
use Tt\Table\Cell\RowSelect;

/**
 *
 * NOTE: This Action does not call the onExecute() or onShow() callback queues
 */
class Delete extends Action
{

    protected string             $confirmStr = 'Delete the selected records?';
    protected RowSelect          $rowSelect;
    protected CallbackCollection $onDelete;


    public function __construct(string $name)
    {
        parent::__construct($name);
        $this->onDelete = CallbackCollection::create();
    }

    public static function create(RowSelect $rowSelect, string $name = 'delete'): static
    {
        $obj = new static($name);
        $obj->rowSelect = $rowSelect;
        return $obj;
    }

    public function execute(Request $request): void
    {
        $val = $this->getTable()->makeRequestKey($this->getName());
        $this->setActive($request->get($this->getName(), '') == $val);
        if (!$this->isActive()) return;

        $selected = $request->get($this->rowSelect->getName(), []);
        $this->getOnDelete()->execute($this, $selected);
        Uri::create()->redirect();
    }

    /**
     * @note see `tkTable.js` for supporting JS to this action
     */
    public function getHtml(): string
    {
        $val = $this->getTable()->makeRequestKey($this->getName());

        return <<<HTML
<button type="submit" name="{$this->getName()}" value="{$val}" class="tk-action-delete btn btn-sm btn-light" disabled
    title="Delete Records"
    data-confirm="{$this->getConfirmStr()}"
    data-row-select="{$this->rowSelect->getName()}">
    <i class="fa fa-fw fa-trash"></i> {$this->getLabel()}
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
        return $this;
    }

    /**
     * @callable function (\Tk\Table\Action\Delete $action, $obj): ?bool { }
     */
    public function addOnDelete(callable $callable, int $priority = CallbackCollection::DEFAULT_PRIORITY): static
    {
        $this->getOnDelete()->append($callable, $priority);
        return $this;
    }

    public function getOnDelete(): CallbackCollection
    {
        return $this->onDelete;
    }

}