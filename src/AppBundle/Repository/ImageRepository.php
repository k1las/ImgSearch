<?php

namespace AppBundle\Repository;

use Doctrine\ORM\Tools\Pagination\Paginator;
use AppBundle\Utils\ImagesComparer as ImagesComparer;

/**
 * ImageRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ImageRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * Paginator Helper
     *
     * Pass through a query object, current page & limit
     * the offset is calculated from the page and limit
     * returns an `Paginator` instance, which you can call the following on:
     *
     *     $paginator->getIterator()->count() # Total fetched (ie: `5` posts)
     *     $paginator->count() # Count of ALL posts (ie: `20` posts)
     *     $paginator->getIterator() # ArrayIterator
     *
     * @param Doctrine\ORM\Query $dql DQL Query Object
     * @param integer $page Current page (defaults to 1)
     * @param integer $limit The total number per page (defaults to 5)
     *
     * @return \Doctrine\ORM\Tools\Pagination\Paginator
     */
    public function paginate($dql, $page = 1, $limit = 16)
    {
        $paginator = new Paginator($dql);

        $paginator->getQuery()
            ->setFirstResult($limit * ($page - 1))// Offset
            ->setMaxResults($limit); // Limit

        return $paginator;
    }

    /**
     *Get Images With Pagination
     */
    public function getImages($currentPage = 1, $limit = 16)
    {
        // Create our query
        $query = $this->createQueryBuilder('image')
            ->orderBy('image.id', 'DESC')
            ->getQuery();

        // No need to manually get get the result ($query->getResult())

        $paginator = $this->paginate($query, $currentPage, $limit);

        return $paginator;
    }

    /**
     * @param int $currentPage
     * @param int $limit
     * @param string $imagePath
     * @return Paginator|false
     */
    public function searchByImage($currentPage = 1, $limit = 16, $imagePath)
    {
        $ids = $this->getImagesIds($imagePath);
        if(empty($ids)){
            return false;
        }
        // Create our query
        $queryBuilder = $this->createQueryBuilder('image');
        $query = $queryBuilder
            ->where($queryBuilder->expr()->in('image.id', $ids))
            ->orderBy('image.id', 'DESC')
            ->getQuery();

        // No need to manually get get the result ($query->getResult())

        $paginator = $this->paginate($query, $currentPage, $limit);

        return $paginator;
    }

    /**Get similar images ids
     *
     * @param string $imagePath
     * @return array
     */
    protected function getImagesIds($imagePath)
    {
        $ids = array();
        $comparer = new ImagesComparer();
        $images = $this->findAll();
        foreach ($images as $image) {
            $result = $comparer->compare($_SERVER['DOCUMENT_ROOT'] . $image->getPath(), $imagePath);
            if ($result <= 20) {
                $ids[] = $image->getId();
            }
        }

        return $ids;
    }
}
