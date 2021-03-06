<?php

namespace ArusServerBundle\Repository;

use ArusEntityTaskBundle\Entity\Search as EntityTaskSearch;

use Actarus\Utils;


/**
 * ArusServerRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ArusServerRepository extends \Doctrine\ORM\EntityRepository
{
	public function search( $data, $offset=null, $limit=null )
	{
		$data = Utils::array2object( $data, 'ArusServerBundle\Entity\Search' );
		$t_params = array();
		$qb = $this->_em->createQueryBuilder();

		if( $offset < 0 ) {
			$offset = null;
			$count  = true;
			$query  = $qb->select( 'count(s.id)' );
		} else {
			$count  = false;
			$query  = $qb->select( array('s') );
		}
		$query = $query->from('ArusServerBundle:ArusServer','s');

		if( $data )
		{
			if( ($id=$data->getId()) ) {
				if( !is_array($id) ) {
					$id = [ $id, '=' ];
				}
				$query->andWhere( 's.id '.$id[1].' :id' );
				$t_params['id'] = $id[0];
			}
			if( $data->getProject() ) {
				$query->andWhere('s.project=:project_id');
				$t_params['project_id'] = $data->getProject()->getId();
			}
			if( ($name=$data->getName()) ) {
				if( !is_array($name) ) {
					$name = [ $name.'%', 'LIKE' ];
				}
				$query->andWhere('s.name '.$name[1].' :name');
				$t_params['name'] = $name[0];
			}
			if($data->getAlias()) {
				$query->andWhere('s.alias LIKE :alias');
				$t_params['alias'] = '%' . $data->getAlias() . '%';
			}
			if( !is_null($data->getStatus()) ) {
				$status = $data->getStatus();
				if( !is_array($status) ) {
					$status = [ $status, '=' ];
				}
				$query->andWhere('s.status '.$status[1].' :status');
				$t_params['status'] = $status[0];
			}
			if( $data->getMinCreatedAt() ) {
				$query->andWhere('s.createdAt >= :min_created_date');
				$t_params['min_created_date'] = date( 'Y-m-d 00:00:00', Utils::dateFR2Time($data->getMinCreatedAt()) );
			}
			if( $data->getMaxCreatedAt() ) {
				$query->andWhere('s.createdAt <= :max_created_date');
				$t_params['max_created_date'] = date( 'Y-m-d 23:59:59', Utils::dateFR2Time($data->getMaxCreatedAt()) );
			}
		}

		$query->setParameters( $t_params );
		$query->orderBy('s.id', 'DESC');
		if( !is_null($limit) ) {
			$query->setMaxResults( $limit );
		}
		if( !is_null($offset) ) {
			$query->setFirstResult($offset);
		}

		$t_result = $query->getQuery()->getResult();

		if( $count ) {
			return (int)$t_result[0][1];
		} else {
			return $t_result;
		}
	}


	public function getTasks( $server )
	{
		$search = new EntityTaskSearch();
		$search->setEntityId( $server->getEntityId() );
		$t_task = $this->_em->getRepository('ArusEntityTaskBundle:ArusEntityTask')->search( $search );

		return $t_task;
	}
}
