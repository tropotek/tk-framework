<?php
namespace Tt\Table;

use Symfony\Component\HttpFoundation\Request;
use Tk\CallbackCollection;

class Action extends Renderer
{

    protected string $name    = '';
    protected string $label   = '';
    protected bool   $active = true;

    public CallbackCollection $onExecute;
    public CallbackCollection $onShow;


    public function __construct(string $name)
    {
        $this->onExecute = CallbackCollection::create();
        $this->onShow    = CallbackCollection::create();
        $this->name      = preg_replace('/[^a-z0-9_-]/i', '_', $name);
        $this->label     = ucfirst(preg_replace('/[A-Z]/', ' $0', $name));
    }

    public function execute(Request $request): void
    {
        if (!$this->isActive()) return;
        $this->getOnExecute()->execute($this, $request);
    }

    public function getHtml(): string
    {
        if (!$this->isActive()) return '';
        $html = $this->getOnShow()->executeAll($this);
        return  implode("\n", $html);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): static
    {
        $this->active = $active;
        return $this;
    }

    /**
     * @callable function (ActionInterface $action, Request $request) { }
     */
    public function addOnExecute(callable $callable, int $priority = CallbackCollection::DEFAULT_PRIORITY): static
    {
        $this->getOnExecute()->append($callable, $priority);
        return $this;
    }

    public function getOnExecute(): CallbackCollection
    {
        return $this->onExecute;
    }

    /**
     * @callable function (ActionInterface $action) { }
     */
    public function addOnShow(callable $callable, int $priority = CallbackCollection::DEFAULT_PRIORITY): static
    {
        $this->getOnShow()->append($callable, $priority);
        return $this;
    }

    public function getOnShow(): CallbackCollection
    {
        return $this->onShow;
    }

}