<?php
namespace Tk;


use Tk\Traits\SingletonTrait;

/**
 * This will hold any persistent system configuration values.
 *
 * After changing any Registry values remember to call save() st store the updated registry.
 *
 * NOTE: Objects should not be saved in the Registry storage, only primitive types.
 *
 * @author Tropotek <http://www.tropotek.com/>
 */
class Registry extends Db\Collection
{
    public static string $TABLE_REGISTRY = 'registry';

    use SingletonTrait;

    public function __construct()
    {
        parent::__construct(self::$TABLE_REGISTRY);
        $this->setDb($this->getFactory()->getDb());
        if ($this->installTable()) {
            $this->set('system.site.name', 'Tropotek Lib');
            $this->set('system.site.shortName', 'TkLib');
            $this->set('system.email', 'webmaster@'.$this->getRequest()->getHost());
            $this->set('site.maintenance.enabled', false);
        }
        $this->load();
    }


    public function getSiteName(): string
    {
        return $this->get('system.site.name', '');
    }

    public function getSiteShortName(): string
    {
        return $this->get('system.site.shortName', '');
    }

    public function getSiteEmail(): string
    {
        return $this->get('system.email', '');
    }

    public function isMaintenanceMode(): bool
    {
        return (bool)$this->get('site.maintenance.enabled', false);
    }

    public function setMaintenanceMode(bool $b = true): Registry
    {
        $this->set('site.maintenance.enabled', $b);
        $this->save();
        return $this;
    }


}