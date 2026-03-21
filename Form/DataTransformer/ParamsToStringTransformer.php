<?php

declare(strict_types=1);

namespace MauticPlugin\CustomObjectsBundle\Form\DataTransformer;

use JMS\Serializer\SerializerInterface;
use MauticPlugin\CustomObjectsBundle\Entity\CustomField\Params;
use Symfony\Component\Form\DataTransformerInterface;

class ParamsToStringTransformer implements DataTransformerInterface
{
    public function __construct(private SerializerInterface $serializer)
    {
    }

    /**
     * Transforms an object (Params) to a string (json).
     *
     * @param Params|null $params
     *
     * @return string
     */
    public function transform(mixed $params = null): mixed
    {
        if (null === $params) {
            // Param can be null because entities are not using constructors
            return '[]';
        }

        if ($params instanceof Params) {
            $params = $params->__toArray();
        }

        return $this->serializer->serialize($params, 'json');
    }

    /**
     * Transforms a string (json) to an object (Params).
     *
     * @param mixed $params
     *
     * @return Params
     */
    public function reverseTransform(mixed $params): mixed
    {
        $params = json_decode($params, true);

        return new Params($params);
    }
}
