<?php
namespace Tt\Table\Action;

use Tk\CallbackCollection;
use Tt\Table\Cell\RowSelect;

/**
 *
 * NOTE: This Action does not call the onExecute() or onShow() callback queues
 */
class Delete extends Select
{

    public function __construct(string $name)
    {
        parent::__construct($name);
        $this->setAttr('title', 'Delete Selected Records');
    }

    public static function create(RowSelect $rowSelect, string $name = 'delete', $icon = 'fa fa-fw fa-trash'): static
    {
        $obj = parent::create($rowSelect, $name, $icon);
        $obj->setConfirmStr('Delete the selected records?');
        return $obj;
    }

    /**
     * @callable function (\Tk\Table\Action\Delete $action, $obj): ?bool { }
     */
    public function addOnDelete(callable $callable, int $priority = CallbackCollection::DEFAULT_PRIORITY): static
    {
        $this->addOnSelect($callable, $priority);
        return $this;
    }

    public function getOnDelete(): CallbackCollection
    {
        return $this->getOnSelect();
    }

}