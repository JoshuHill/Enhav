<?php
/**
 * Created by PhpStorm.
 * User: gseidel
 * Date: 15.08.18
 * Time: 18:22
 */

namespace Enhavo\Bundle\SearchBundle\Engine\ElasticSearch;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Elastica\Query;
use Elastica\ResultSet;
use Elastica\SearchableInterface;
use Pagerfanta\Adapter\AdapterInterface;

class ElasticaORMAdapter implements AdapterInterface
{
    /**
     * @var Query
     */
    private $query;

    /**
     * @var \Elastica\ResultSet
     */
    private $resultSet;

    /**
     * @var SearchableInterface
     */
    private $searchable;

    /**
     * @var EntityRepository
     */
    private $em;

    public function __construct(SearchableInterface $searchable, Query $query, EntityManagerInterface $em)
    {
        $this->searchable = $searchable;
        $this->query = $query;
        $this->em = $em;
    }

    /**
     * Returns the number of results.
     *
     * @return integer The number of results.
     */
    public function getNbResults()
    {
        if (!$this->resultSet) {
            return $this->searchable->search($this->query)->getTotalHits();
        }

        return $this->resultSet->getTotalHits();
    }

    /**
     * Returns the Elastica ResultSet. Will return null if getSlice has not yet been
     * called.
     *
     * @return \Elastica\ResultSet|null
     */
    public function getResultSet()
    {
        return $this->convertResultSet($this->resultSet);
    }

    /**
     * Returns an slice of the results.
     *
     * @param integer $offset The offset.
     * @param integer $length The length.
     *
     * @return array|\Traversable The slice.
     */
    public function getSlice($offset, $length)
    {
        $this->resultSet = $this->searchable->search($this->query, array(
            'from' => $offset,
            'size' => $length
        ));

        return $this->convertResultSet($this->resultSet);
    }

    private function convertResultSet(ResultSet $resultSet)
    {
        $result = [];
        foreach($resultSet as $data) {
            $id = $data['id'];
            $className = $data['className'];
            $result[] = $this->em->getRepository($className)->find($id);
        }
        return $result;
    }
}