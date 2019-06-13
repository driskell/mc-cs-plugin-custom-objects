<?php

declare(strict_types=1);

/*
 * @copyright   2019 Mautic, Inc. All rights reserved
 * @author      Mautic, Inc.
 *
 * @link        https://mautic.com
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\CustomObjectsBundle\CustomFieldType;

use Symfony\Component\Validator\Constraints\UrlValidator;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use MauticPlugin\CustomObjectsBundle\Entity\CustomFieldValueInterface;

class UrlType extends AbstractTextType
{
    /**
     * @var string
     */
    public const NAME = 'custom.field.type.url';

    /**
     * @var string
     */
    protected $key = 'url';

    /**
     * @return string
     */
    public function getSymfonyFormFieldType(): string
    {
        return \Symfony\Component\Form\Extension\Core\Type\UrlType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getSymfonyFormConstraints(): array
    {
        return [
            new \Symfony\Component\Validator\Constraints\Url(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function validateValue(CustomFieldValueInterface $valueEntity, ExecutionContextInterface $context): void
    {
        parent::validateValue($valueEntity, $context);

        $value = $valueEntity->getValue();

        if (empty($value)) {
            return;
        }

        $validator = new UrlValidator();
        $validator->initialize($context);
        $validator->validate($value, $this->getSymfonyFormConstraints()[0]);
    }
}
