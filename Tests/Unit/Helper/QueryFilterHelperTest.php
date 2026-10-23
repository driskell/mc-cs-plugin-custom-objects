<?php

declare(strict_types=1);

namespace MauticPlugin\CustomObjectsBundle\Tests\Unit\Helper;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;
use Mautic\LeadBundle\Provider\FilterOperatorProviderInterface;
use Mautic\LeadBundle\Segment\ContactSegmentFilter;
use Mautic\LeadBundle\Segment\ContactSegmentFilterCrate;
use Mautic\LeadBundle\Segment\Decorator\FilterDecoratorInterface;
use Mautic\LeadBundle\Segment\Query\Expression\ExpressionBuilder;
use Mautic\LeadBundle\Segment\Query\Filter\FilterQueryBuilderInterface;
use Mautic\LeadBundle\Segment\Query\QueryBuilder;
use Mautic\LeadBundle\Segment\RandomParameterName;
use Mautic\LeadBundle\Segment\TableSchemaColumnsCache;
use MauticPlugin\CustomObjectsBundle\CustomFieldType\TextType;
use MauticPlugin\CustomObjectsBundle\Helper\QueryFilterFactory;
use MauticPlugin\CustomObjectsBundle\Helper\QueryFilterHelper;
use MauticPlugin\CustomObjectsBundle\Provider\CustomFieldTypeProvider;
use MauticPlugin\CustomObjectsBundle\Repository\CustomFieldRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

class QueryFilterHelperTest extends TestCase
{
    /**
     * @var QueryFilterHelper
     */
    private $queryFilterHelper;

    /**
     * @var QueryBuilder|MockObject
     */
    private $queryBuilder;

    /**
     * @var ExpressionBuilder|MockObject
     */
    private $expressionBuilder;

    protected function setUp(): void
    {
        parent::setUp();

        $entityManager = $this->createMock(EntityManager::class);
        $entityManager
            ->method('getConnection')
            ->willReturn($this->createMock(Connection::class));

        $customFieldTypeProvider = new CustomFieldTypeProvider();
        $customFieldTypeProvider->addType(new TextType(
            $this->createMock(TranslatorInterface::class),
            $this->createMock(FilterOperatorProviderInterface::class)
        ));

        $this->queryFilterHelper = new QueryFilterHelper(
            $entityManager,
            new QueryFilterFactory(
                $entityManager,
                $customFieldTypeProvider,
                $this->createMock(CustomFieldRepository::class),
                new QueryFilterFactory\Calculator(),
                1
            ),
            new RandomParameterName()
        );
        $this->queryBuilder      = $this->createMock(QueryBuilder::class);
        $this->expressionBuilder = $this->createMock(ExpressionBuilder::class);
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testAddCustomObjectNameExpression(): void
    {
        $this->queryBuilder
            ->expects($this->any())
            ->method('expr')
            ->willReturn($this->expressionBuilder);

        $this->expressionBuilder
            ->expects($this->any())
            ->method('eq')
            ->willReturn($this->expressionBuilder);

        $this->queryBuilder
            ->expects($this->any())
            ->method('andWhere')
            ->with($this->expressionBuilder);

        $this->queryBuilder
            ->expects($this->any())
            ->method('setParameter')
            ->with('par0', 'acquia', null);

        $this->queryFilterHelper
            ->addCustomObjectNameExpression($this->queryBuilder, 'test', 'eq', 'acquia');
    }

    public function testAddCustomObjectNameExpressionWithErrorForIntegerValue(): void
    {
        $this->expectException(\TypeError::class);
        $this->queryBuilder
            ->expects($this->any())
            ->method('expr')
            ->willReturn($this->expressionBuilder);

        $this->expressionBuilder
            ->expects($this->any())
            ->method('eq')
            ->willReturn($this->expressionBuilder);

        $this->queryBuilder
            ->expects($this->any())
            ->method('andWhere')
            ->with($this->expressionBuilder);

        $this->queryBuilder
            ->expects($this->any())
            ->method('setParameter')
            ->with('test_value_value', 10, null);

        $this->queryFilterHelper
            ->addCustomObjectNameExpression($this->queryBuilder, 'test', 'eq', 10);
    }

    public function testCreateMergeFilterQueryMatchesItemNamesForItemNameFilters(): void
    {
        $sql = $this->queryFilterHelper
            ->createMergeFilterQuery($this->createMergedFilter([['!empty', 'notEmpty', null]]), 'l')
            ->getSQL();

        $this->assertStringContainsString('cin_7_item.custom_object_id = 7', $sql);
        $this->assertStringContainsString('cin_7_item.name IS NOT NULL', $sql);
        $this->assertStringNotContainsString('custom_field_value', $sql);
    }

    public function testCreateMergeFilterQueryAppliesEachFiltersOwnOperatorAndValue(): void
    {
        $queryBuilder = $this->queryFilterHelper->createMergeFilterQuery(
            $this->createMergedFilter([['=', 'eq', 'Object emotion'], ['!empty', 'notEmpty', null]]),
            'l'
        );
        $sql = $queryBuilder->getSQL();

        $this->assertMatchesRegularExpression('/cin_7_item\.name = :par\d+/', $sql);
        $this->assertStringContainsString('cin_7_item.name IS NOT NULL', $sql);
        $this->assertContains('Object emotion', $queryBuilder->getParameters());
    }

    /**
     * Builds the merged filter as core does: the last factored filter carries every nested filter in its merged property.
     *
     * @param array<array{0: string, 1: string, 2: string|null}> $nestedFilters operator, decorated operator and value
     */
    private function createMergedFilter(array $nestedFilters): ContactSegmentFilter
    {
        $mergedProperty = [];
        $filter         = null;
        foreach ($nestedFilters as [$operator, $decoratedOperator, $value]) {
            $crate = new ContactSegmentFilterCrate([
                'object'   => 'custom_object',
                'glue'     => 'and',
                'field'    => 'cmo_7',
                'type'     => 'text',
                'operator' => $operator,
                'filter'   => $value,
            ]);

            $decorator = $this->createMock(FilterDecoratorInterface::class);
            $decorator->method('getField')->willReturn('7');
            $decorator->method('getOperator')->willReturn($decoratedOperator);
            $decorator->method('getParameterValue')->willReturn($value);

            $filter = new ContactSegmentFilter(
                $crate,
                $decorator,
                $this->createMock(TableSchemaColumnsCache::class),
                $this->createMock(FilterQueryBuilderInterface::class)
            );

            $mergedProperty[] = [
                'filter_value' => $value,
                'operator'     => $decoratedOperator,
                'field'        => '7',
                'type'         => 'text',
                'filter'       => $filter,
            ];
        }

        $filter->contactSegmentFilterCrate->setMergedProperty($mergedProperty);

        return $filter;
    }
}
