<?php
namespace Tk\Db\Mapper;

/**
 * @deprecated To be removed
 */
class DbEvents
{

    /**
     * Fired when a \Tk\Db\Mapper\Model object is inserted to the DB.
     * Called just before the DB query
     *
     * @event \Tk\Db\Event\DbEvent
     */
    const MODEL_INSERT = 'db.model.insert';

    /**
     * Fired when a \Tk\Db\Mapper\Model object is inserted to the DB.
     * Called just after the DB query
     *
     * @event \Tk\Db\Event\DbEvent
     */
    const MODEL_POST_INSERT = 'db.model.post.insert';

    /**
     * Fired when a \Tk\Db\Mapper\Model object is updated in the DB.
     * Called just before the DB query
     *
     * @event \Tk\Db\Event\DbEvent
     */
    const MODEL_UPDATE = 'db.model.update';

    /**
     * Fired when a \Tk\Db\Mapper\Model object is updated in the DB.
     * Called just after the DB query
     *
     * @event \Tk\Db\Event\DbEvent
     */
    const MODEL_POST_UPDATE = 'db.model.post.update';

    /**
     * Fired when a \Tk\Db\Mapper\Model object is saved the DB.
     * Also one th event of INSERT/UPDATE will be fired after the SAVE event
     * Called just before the DB query
     *
     * @event \Tk\Db\Event\DbEvent
     */
    const MODEL_SAVE = 'db.model.save';

    /**
     * Fired when a \Tk\Db\Mapper\Model object is saved the DB.
     * Also one th event of INSERT/UPDATE will be fired after the SAVE event
     * Called just after the DB query
     *
     * @event \Tk\Db\Event\DbEvent
     */
    const MODEL_POST_SAVE = 'db.model.post.save';

    /**
     * Fired when a \Tk\Db\Mapper\Model object is deleted from the DB.
     * Called just before the DB query
     *
     * @event \Tk\Db\Event\DbEvent
     */
    const MODEL_DELETE = 'db.model.delete';

    /**
     * Fired when a \Tk\Db\Mapper\Model object is deleted from the DB.
     * Called just after the DB query
     *
     * @event \Tk\Db\Event\DbEvent
     */
    const MODEL_POST_DELETE = 'db.model.post.delete';

}