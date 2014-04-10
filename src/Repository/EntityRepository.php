<?php

namespace Jma\ResourceBundle\Repository;

use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository as BaseRepository;
use Symfony\Component\Form\FormInterface;
use Lexik\Bundle\FormFilterBundle\Filter\FilterBuilderUpdaterInterface;
use Doctrine\ORM\QueryBuilder;

/**
 * Description of ResourceRepository
 *
 * @author Maarek
 */
class EntityRepository extends BaseRepository
{

    /**
     * @param array $criteria
     * @param array $orderBy
     * @return QueryBuilder
     * @throws \InvalidArgumentException
     */
    public function builderAll(array $criteria = null, array $orderBy = null)
    {
        $queryBuilder = $this->getCollectionQueryBuilder();

        $this->applyCriteria($queryBuilder, $criteria);
        $this->applySorting($queryBuilder, $orderBy);

        return $queryBuilder;
    }

    /**
     * @param FilterBuilderUpdaterInterface $updater
     * @param FormInterface $form
     * @param array $criteria
     * @param array $orderBy
     * @return \Pagerfanta\Pagerfanta
     * @throws \InvalidArgumentException
     */
    public function createPaginatorWithFilters(FilterBuilderUpdaterInterface $updater, FormInterface $form = null, array $criteria = null, array $orderBy = null)
    {
        $queryBuilder = $this->getCollectionQueryBuilder();
        $this->applyCriteria($queryBuilder, $criteria);
        $this->applySorting($queryBuilder, $orderBy);
        $this->applyFilterForm($updater, $form, $queryBuilder);

        return $this->getPaginator($queryBuilder);
    }

    /**
     * @param FilterBuilderUpdaterInterface $updater
     * @param FormInterface $form
     * @param QueryBuilder $queryBuilder
     */
    public function applyFilterForm(FilterBuilderUpdaterInterface $updater, FormInterface $form, QueryBuilder $queryBuilder)
    {
        if ($form !== null) {
            $updater->addFilterConditions($form, $queryBuilder);
        }
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param array $criteria
     * @throws \InvalidArgumentException
     */
    protected function applyCriteria(QueryBuilder $queryBuilder, array $criteria = null)
    {
        if (null === $criteria) {
            return;
        }

        foreach ($criteria as $property => $value) {
            if ($property === '_join') {
                $this->applyJoin($queryBuilder, $value);
            } else {
                if (null === $value) {
                    $queryBuilder
                        ->andWhere($queryBuilder->expr()->isNull($this->getPropertyName($property)));
                } elseif (!is_array($value)) {
                    $queryBuilder
                        ->andWhere($queryBuilder->expr()->eq($this->getPropertyName($property), ':' . $property))
                        ->setParameter($property, $value);
                } else {
                    $queryBuilder->andWhere($queryBuilder->expr()->in($this->getPropertyName($property), $value));
                }
            }
        }
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param array $joins
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    protected function applyJoin(QueryBuilder $queryBuilder, array $joins = array())
    {
        $default = ["type" => "left", "fetch" => true];
        foreach ($joins as $join) {
            if (false == is_array($join)) {
                $join = ["field" => $join];
            }
            $join = array_merge($default, $join);

            $field = $join['field'];
            $type = $join['type'];
            $fetch = $join['fetch'];
            $alias = isset($join["alias"]) ? $join['alias'] : $field;

            if ($type === "left") {
                $queryBuilder->leftJoin($this->getPropertyName($field), $alias);
            } elseif ($type === "inner") {
                $queryBuilder->innerJoin($this->getPropertyName($field), $alias);
            }

            if ($fetch === true) {
                $queryBuilder->addSelect($alias);
            }
        }
    }

}