<?php
namespace Tk;

use Tk\Traits\SingletonTrait;

/**
 * This will hold any persistent system configuration values.
 *
 * After changing any Registry values remember to call save() to store the updated registry.
 *
 * NOTE: Objects should not be saved in the Registry storage, only primitive types.
 */
class Registry extends Db\Collection
{
    public static string $TABLE_REGISTRY = 'registry';

    use SingletonTrait;

    public function __construct()
    {
        parent::__construct(self::$TABLE_REGISTRY);
        $this->setDb($this->getFactory()->getDb());
        $this->load();
    }

    /**
     * Save modified Data to the DB
     */
    public function save(): static
    {
        try {
            if ($this->installTable()) {
                $this->set('system.site.name', 'Tropotek Lib');
                $this->set('system.site.shortName', 'TkLib');
                $this->set('system.email', 'webmaster@'.$this->getRequest()->getHost());
                $this->set('system.maintenance.enabled', false);
                $this->save();
            }
        } catch (\Exception $e) { \Tk\Log::error($e->__toString());}
        return parent::save();
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
        return (bool)$this->get('system.maintenance.enabled', false);
    }

    public function setMaintenanceMode(bool $b = true): static
    {
        $this->set('system.maintenance.enabled', $b);
        $this->save();
        return $this;
    }

}