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
    public static string $DB_TABLE = 'registry';

    use SingletonTrait;

    public function __construct(\PDO $pdo = null)
    {
        parent::__construct(self::$DB_TABLE, $pdo);
        $this->load();
    }

    public function getSiteName(): string
    {
        return $this->get('site.name', '');
    }

    public function getSiteShortName(): string
    {
        return $this->get('site.name.short', '');
    }

    public function getSiteEmail(): string
    {
        return $this->get('site.email', '');
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