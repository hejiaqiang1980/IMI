<?php
namespace Imi\Model\Relation;

use Imi\Util\Imi;
use Imi\Util\Text;
use Imi\Model\BaseModel;
use Imi\Bean\BeanFactory;
use Imi\Model\ModelManager;
use Imi\Model\Parser\RelationParser;
use Imi\Model\Relation\Struct\OneToOne;


abstract class Insert
{
	/**
	 * 处理插入
	 *
	 * @param \Imi\Model\Model $model
	 * @param string $propertyName
	 * @param \Imi\Bean\Annotation\Base $annotation
	 * @return void
	 */
	public static function parse($model, $propertyName, $annotation)
	{
		if($annotation instanceof \Imi\Model\Annotation\Relation\OneToOne)
		{
			static::parseByOneToOne($model, $propertyName, $annotation);
		}
		else if($annotation instanceof \Imi\Model\Annotation\Relation\OneToMany)
		{
			static::parseByOneToMany($model, $propertyName, $annotation);
		}
	}

	/**
	 * 处理一对一插入
	 *
	 * @param \Imi\Model\Model $model
	 * @param string $propertyName
	 * @return void
	 */
	public static function parseByOneToOne($model, $propertyName, $annotation)
	{
		if(!$model->$propertyName)
		{
			return;
		}
		$relationParser = RelationParser::getInstance();
		$className = BeanFactory::getObjectClass($model);
		$autoInsert = $relationParser->getPropertyAnnotation($className, $propertyName, 'AutoInsert');
		$autoSave = $relationParser->getPropertyAnnotation($className, $propertyName, 'AutoSave');
		if((!$autoInsert || $autoInsert->status) && (!$autoSave || $autoSave->status))
		{
			$struct = new OneToOne($className, $propertyName, $annotation);
			$leftField = $struct->getLeftField();
			$rightField = $struct->getRightField();

			$model->$propertyName->$rightField = $model->$leftField;
			$model->$propertyName->insert();
		}
	}
	
	/**
	 * 处理一对多插入
	 *
	 * @param \Imi\Model\Model $model
	 * @param string $propertyName
	 * @return void
	 */
	public static function parseByOneToMany($model, $propertyName, $annotation)
	{
		if(!$model->$propertyName)
		{
			return;
		}
		$relationParser = RelationParser::getInstance();
		$className = BeanFactory::getObjectClass($model);
		$autoInsert = $relationParser->getPropertyAnnotation($className, $propertyName, 'AutoInsert');
		$autoSave = $relationParser->getPropertyAnnotation($className, $propertyName, 'AutoSave');
		if((!$autoInsert || $autoInsert->status) && (!$autoSave || $autoSave->status))
		{
			$struct = new OneToOne($className, $propertyName, $annotation);
			$leftField = $struct->getLeftField();
			$rightField = $struct->getRightField();
			$rightModel = $struct->getRightModel();

			foreach($model->$propertyName as $index => $row)
			{
				if(!$row instanceof $rightModel)
				{
					$row = $rightModel::newInstance($row);
					$model->$propertyName[$index] = $row;
				}
				$row[$rightField] = $model->$leftField;
				$row->insert();
			}
		}
	}
}