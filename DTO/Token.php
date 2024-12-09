<?php

declare(strict_types=1);

namespace MauticPlugin\CustomObjectsBundle\DTO;

/**
 * Object that represents parsed token like this one:.
 *
 * {custom-object=product:sku | where=segment-filter |order=latest|limit=1 | default=Nothing to see here | format=or-list}
 */
class Token
{
    private int $limit = 1;

    private string $where = '';

    private string $order = 'latest';

    private string $defaultValue = '';

    private string $format = '';

    private string $customFieldAlias = '';

    private string $customObjectAlias = '';

    public function __construct(private string $token)
    {
    }

    public function setDefaultValue(string $defaultValue): void
    {
        $this->defaultValue = $defaultValue;
    }

    public function getDefaultValue(): string
    {
        return $this->defaultValue;
    }

    public function getOrder(): string
    {
        return $this->order;
    }

    public function setOrder(string $order): void
    {
        $this->order = $order;
    }

    public function getWhere(): string
    {
        return $this->where;
    }

    public function setWhere(string $where): void
    {
        $this->where = $where;
    }

    public function getFormat(): string
    {
        return $this->format;
    }

    public function setFormat(string $format): void
    {
        $this->format = $format;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function setLimit(int $limit): void
    {
        $this->limit = $limit;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function getCustomFieldAlias(): string
    {
        return $this->customFieldAlias;
    }

    public function setCustomFieldAlias(string $customFieldAlias): void
    {
        $this->customFieldAlias = $customFieldAlias;
    }

    public function getCustomObjectAlias(): string
    {
        return $this->customObjectAlias;
    }

    public function setCustomObjectAlias(string $customObjectAlias): void
    {
        $this->customObjectAlias = $customObjectAlias;
    }
}
