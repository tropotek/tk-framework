<?php
namespace Tk\Db\Event;


use Symfony\Contracts\EventDispatcher\Event;
use Tk\Db\Mapper\Model;

/**
 *
 * @author Tropotek <http://www.tropotek.com/>
 */
class DbEvent extends Event
{

    protected Model $model;

    private bool $queryStopped = false;


    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function getModel(): Model
    {
        return $this->model;
    }

    /**
     * Return true if the query executed after this event should be halted
     * @note will not work for pose query events
     */
    public function isQueryStopped(): bool
    {
        return $this->queryStopped;
    }

    /**
     * Stops the main query from being executed on pre-query events
     */
    public function stopQuery(): DbEvent
    {
        $this->queryStopped = true;
        return $this;
    }

}