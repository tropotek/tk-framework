<?php
namespace Tk;


/**
 * Class CollectionTrait
 *
 * @author Tropotek <http://www.tropotek.com/>
 */
trait CollectionTrait
{

    protected Collection $_collection;

    /**
     * Call this in your constructor to init traits data
     */
    public function _CollectionTrait(): void
    {
        $this->_collection = new Collection();
    }

    public function getCollection(): Collection
    {
        return $this->_collection;
    }

    public function set(string $key, mixed $value): static
    {
        $this->getCollection()->set($key, $value);
        return $this;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->getCollection()->get($key, $default);
    }

    public function has(string $key): bool
    {
        return $this->getCollection()->has($key);
    }

    public function remove(string $key): static
    {
        $this->getCollection()->remove($key);
        return $this;
    }

    public function replace(array $items): static
    {
        $this->getCollection()->replace($items);
        return $this;
    }

    public function all(): array
    {
        return $this->getCollection()->all();
    }

}