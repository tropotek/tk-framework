<?php
namespace Tk\Traits;

use Tk\Collection;

trait CollectionTrait
{

    protected ?Collection $_collection = null;


    public function getCollection(): Collection
    {
        if (!$this->_collection) $this->_collection = new Collection();
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