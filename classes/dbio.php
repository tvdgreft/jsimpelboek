<?php
/**
 * dbio - database functions
 **/
namespace SIMPELBOEK;

class dbio
{
	public $dbiodefaults = array(
		'table' => '',
		'sort' => '',
		'filters' => '',
		'where' => '',
		'sql' => '',
		'search' => '',
		'page' => '',	#current pagenummer
		'maxlines' => '',	#
		'output' => 'OBJECT',
	);
	/**
	* create a new table
	* $table = name of the table (without joomla prefix)
	* $columns = array of colums like: `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	* example:
	* $dbio -> CreateTable($prefix . Dbtables::titels['name'],Dbtables::titels['columns']);
	* const titels = ["name"=>"docman", "columns"=>"
	*	`id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    *    `crdate` datetime NOT NULL,					#creationdate of record
    *    `nummer` int(5) NOT NULL,		#nummer
    *    `oudnummer` int(5) NOT NULL,				#oud nummer
    *    `seizoen` varchar(255) NOT NULL,
    *    `titel` varchar(512) NOT NULL,
    *    `auteur` varchar(255) NOT NULL,						#auteur
    *    `bladzijden` varchar(255) NOT NULL,			#bladzijden
    *    `artikel` varchar(255),
	*	  PRIMARY KEY (`id`)"]; 
	**/
	public function CreateTable($table,$columns)
	{
		$db = \JFactory::getDbo();
		if(!$table) { return(FALSE); }
		$query = 'CREATE TABLE IF NOT EXISTS `' . '#__' . $table . '` (' . $columns . ') ENGINE=InnoDB DEFAULT CHARSET=utf8;';
		$db->setQuery($query);
		$db->execute();
		return(0);
	}
	public function DeleteTable($table)
	{
		$db = \JFactory::getDbo();
		if(!$table) { return(FALSE); }
		$query = 'DROP TABLE IF EXISTS ' . '#__' . $table;
		$db->setQuery($query);
		$db->execute();
		return(0);
	}
	/**
	 * create record
	 * the fields created and modified are set to the current date
	 * $args['table'] - databasetable
	 * $args['fields'] - array of fields $fields=array("field1"=>$value,"field2"=>$value .... )
	 */
	public function CreateRecord($args)
	{
		$db = \JFactory::getDbo();
		$query = $db->getQuery(true);
		$table = isset($args["table"]) ? '#__' . $args["table"] : "";
		if($this->IsColumn($args["table"],'crdate')) { $args['fields'] += ["crdate" => date("Y-m-d H:i:s")]; }
		if($this->IsColumn($args["table"],'created')) { $args['fields'] += ["created" => date("Y-m-d H:i:s")]; }
		$columns = array();
		$values = array();
		foreach ($args["fields"] as $f =>$value)
		{
			array_push($columns,$f);
			if(is_array($value)) {$value = json_encode($value);}
			array_push($values,$db->quote($value));
		}
	/*
		echo $table;
		echo '<br>';
		print_r($columns);
		echo '<br>';
		print_r($values);
		return(0);
	*/
		
		// Prepare the insert query.
		$query
		->insert($db->quoteName($table))
		->columns($db->quoteName($columns))
		->values(implode(',', $values));

	// Set the query using our newly populated query object and execute it.
		$db->setQuery($query);
		$db->execute();
		return $db->insertid();
	}
	/**
	* update record
	* $args['table'] - databasetable
	* $args['fields'] - array of fields $fields=array("field1"=>$value,"field2"=>$value .... )
	* $args['key'] - name of unique key
	* $args['value'] - value of unique key
	 */
	public function ModifyRecord($args)
	{
		$db = \JFactory::getDbo();
		$query = $db->getQuery(true);

		$table = isset($args["table"]) ? '#__' . $args["table"] : "";
		$fields = array();
		foreach ($args["fields"] as $f =>$value)
		{
			if($f == "modified") { $value = date("Y-m-d H:i:s"); }
			if($f == "mddate") { $value = date("Y-m-d H:i:s"); }
			if(is_array($value)) { $value = json_encode($value);}
			array_push($fields,$db->quoteName($f) . '=' . $db->quote($value));
		}
		$conditions = array(
			$db->quoteName($args["key"]) . ' = ' . $db->quote($args['value'])
		);
		$query->update($db->quoteName($table))->set($fields)->where($conditions);
		$db->setQuery($query);
		$result = $db->execute();
		return($result);
	}
	/**
	 * delete a record on unique key
	 * $args['table'] - databasetable
	* $args['key'] = name of unique key
	* $args['value'] = value of unique ke
	 */
	public function DeleteRecord($args)
	{
		$db = \JFactory::getDbo();
		$table = isset($args["table"]) ? '#__' . $args["table"] : "";
		$key = isset($args["key"]) ? $args["key"] : "";
		$value = isset($args["value"]) ? $args["value"] : "";
		$conditions = $key . ' = "' . $value . '"';
		$query='DELETE FROM '. $table . ' WHERE (' . $conditions . ')';
		#echo $query;
		$db->setQuery($query);
		$result = $db->execute();
		return($result);
	}
	/**
	* delete all recods in table
	* $args['table'] - databasetable
	**/
	public function DeleteAllRecords($args)
	{
		$db = \JFactory::getDbo();
		$table = isset($args["table"]) ? '#__' . $args["table"] : "";
		$query = 'DELETE FROM ' . $table;
		$db->setQuery($query);
		$result = $db->execute();
		return($result);
	}
	public function DropRecord($args)
	{
		$db = \JFactory::getDbo();
		$table = isset($args["table"]) ? '#__' . $args["table"] : "";
		$value = isset($args["value"]) ? $args["value"] : "";
		$query = $db->getQuery(true);
		$conditions = array(
			$db->quoteName('id') . ' = ' . $db->quote($value)
		);
		#print_r($conditions);
		$query->delete($table);
		$query->where($conditions);
		$db->setQuery($query);
		$db->execute();
	}
	/**
 	* Returns the count of records in the database.
 	*
	 * @return null|string
 	*/

	public function CountRecords($args) 
	{
		$table = isset($args["table"]) ? '#__' . $args["table"] : "";
		$db = \JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select(array('e.*'));
		$query->from($db->quoteName($table, 'e'));
		$db->setQuery($query);
		$count = count($db->loadObjectList());
		return($count);
 	}
	#
	# get description of all columns
	#
	
	public function DescribeColumns($args)
	{
		$table = isset($args["table"]) ? '#__' . $args["table"] : "";
		$db = \JFactory::getDbo();
		$query = 'DESCRIBE '.$table;
		$db->setQuery($query);
		$result = $db->loadObjectList();
		return($result);
	}
	/**
	* lijst van alle kolommen
	* bv:
	* Array ( [0] => id [1] => created [2] => modified [3] => catalogusnummer [4] => auteur [5] => titel [6] => annotatie [7] => uitgever [8] => jaarvanuitgave ) 
	*/
	public function Columns($table)
	{
		$db = \JFactory::getDbo();
		$jtable = '#__' . $table;
		$cols = $db->getTableColumns($jtable);
		$columns = array();
		foreach ($cols as $key => $value) 
		{
			array_push($columns,$key);
		}
		return($columns);
	}
	/**
	* Is column present in table?
	**/
	public function IsColumn($table,$col)
	{
		$db = \JFactory::getDbo();
		$jtable = '#__' . $table;
		$cols = $db->getTableColumns($jtable);
		return(isset($cols[$col]));
	}
	#
	# read a record 
	# $args['table'] - databasetable
	# $args['id'] - id of record
	public function ReadRecord($args)
	{
		$table = isset($args["table"]) ? '#__' . $args["table"] : "";
		$id = isset($args["id"]) ? $args["id"] : "";
		$db = \JFactory::getDbo();
		$query = $db->getQuery(true);
		$query
			->select(array('e.*'))
			->where($db->quoteName('e.id') . " = " . $db->quote($id))
			->from($db->quoteName($table, 'e'));
		$db->setQuery($query);
		$row = $db->loadObject();
		return($row);	
	}
	/**
	* read all records of  table
	* $args['table'] - databasetable
	* $args['sort'] - sort on this field
	**/
	public function ReadAllRecords($args)
	{
		$table = isset($args["table"]) ? '#__' . $args["table"] : "";
		$sort  = isset($args["sort"]) ? $args["sort"] : "id";
		$db = \JFactory::getDbo();
		$query = $db->getQuery(true);
		$query
			->select(array('e.*'))
			->from($db->quoteName($table, 'e'))
			->order($sort);
		$db->setQuery($query);
		$rows = $db->loadObjectList();
		return($rows);
	}
	#
	# read a record with unique key
	# $args['table'] - databasetable
	# $args['key'] - name of unique key
	# $args['value'] - value of unique key 
	public function ReadUniqueRecord($args)
	{
		$db = \JFactory::getDbo();
		$table = isset($args["table"]) ? '#__' . $args["table"] : "";
		$key = isset($args["key"]) ? "e." . $args["key"] : "";
		$value = isset($args["value"]) ? $args["value"] : "";
		$query = $db->getQuery(true);
		$query
			->select(array('e.*'))
			->where($db->quoteName($key) . " = " . $db->quote($value))
			->from($db->quoteName($table, 'e'));
		$db->setQuery($query);
		$row = $db->loadObject();
		return($row);	
	}
	/**
	* ReadRecords 
	* $args['table'] - databasetable
	* $args['columns'] - column info (not required) e.g.  [["id","nr","int"],["name","naam","string"]]
	* $args['sort'] - column to be sorted followed by direction (column ASC/DESC) ASC is default vale
	* $args['filters'] - Array ( [column1] => value [column2] => value ........ )
	*					Checks if value appears in content
	*					and conditions of columns
	* 					Bij filters : value may be preceded by:
	*					# : search on full content
	*					< : content should be <= value
	*					> : content should be >= value
	* $args['where']    SQL statement
	* $args['sql']		Array ( [column1] => value [column2] => value ........ ) 
	*					Checks if value is exact the contenst of a field
	*					or conditions of columns
	* $args["search'] - array(array ('column1','column2' ....),$value)
	*					- match $value in the given columns
	* $args['page'} - current pagenumber
	* $args['maxlines'] - maxlines per page
	* $args['output'] - (string) (Optional) Any of ARRAY_A | ARRAY_N | OBJECT | OBJECT_K constants. default=OBJECT
	*/
	public function ReadRecords($args)
	{
		$args = parse_args( $args, $this->dbiodefaults );
		#echo('<br> start readrecord');
		#print_r($args);
		$db = \JFactory::getDbo();
		#
		# make conditions for the query
		#
		$orconditions=array();
		$andconditions=array();
		#
		# translate filters to query conditions
		#
		/**
		 * test if values are equal woth content of collumns
		 */
		if($args["sql"])
		{
			$conditions = $args["sql"];
		}
		if($args["where"])
		{
			#echo "<br>where";
			#print_r($$args["where"]);
			foreach($args["where"] as $f => $value)
			{
				array_push($orconditions,$db->quoteName($f) . ' = ' . $db->quote($value));
			}
		}
		/**
		 * all records where the key is part of the content in any of the given columns
		 * OR condition
		 */
		if($args["search"])
		{
			#echo "<br>search";
			#print_r($args["search"]);
			$columns = $args["search"][0];
			$value = $args["search"][1];
			if($columns && $value)
			{
				foreach ($columns as $f)
				{
					$key = $db->quote('%'.$value.'%'); #match on content
					array_push($orconditions,$db->quoteName($f) . ' LIKE ' . $key);
				}
			}
		}
		/**
		 * all records where all combinations of values and columns is positive.
		 * AND condition.
		 */
		if($args["filters"])
		{
			#echo'<br>filter';
			#print_r($args["filters"]);
			foreach($args["filters"] as $f => $value)
			{
				#echo "<br". $f . "<>" . $value;
				if(!$value) { continue; }
				#
				# If < or > before value search on <= resp >=
				#
				if(preg_match('/^>(.*)/',$value,$match))   
				{
					$value = $match[1];
					echo "<br>push". $value;
					array_push($andconditions,$db->quoteName($f) . ' >= ' . $db->quote($value));
				}
				elseif(preg_match('/^<(.*)/',$value,$match))   
				{
					$value = $match[1];
					array_push($andconditions,$db->quoteName($f) . ' <= ' . $db->quote($value));
				}
				#
				# if key is numeric then check if key is solid (not a part of string)
				#
				elseif(is_numeric($value))
				{
					# columns defined and is column integer, match full record
					if(isset($args["columns"]) && $this->Columntype(array("columns"=>$args["columns"],"column"=>$f)) == "int")
					{
						echo $value . "value is numeric and numeric field ";
						$key = '^'.$value.'$';
						array_push($andconditions,$db->quoteName($f) . ' REGEXP '. $db->quote($key));
					}
					else
					{
						# nu test die op deel van field, kan beter TODO
						$key = '^(.*?(\b'.$value.'\b)[^$]*)$';
						array_push($andconditions,$db->quoteName($f) . ' REGEXP '. $db->quote($key));
					}
				}
				else
				{
					if(preg_match("/NOTNULL/",$value))
					{
						array_push($andconditions,$db->quoteName($f) . ' IS NOT NULL');
					}
					elseif(preg_match("/NULL/",$value))
					{
						array_push($andconditions,$db->quoteName($f) . ' IS NULL');
					}
					elseif(preg_match("/#/",$value))
					{
						$key=$db->quote(substr($value,1));   #search on full content
						array_push($andconditions,$db->quoteName($f) . ' = ' . $key);
					}
					else
					{
						$key = $db->quote('%'.$value.'%'); #match on content
						array_push($andconditions,$db->quoteName($f) . ' LIKE ' . $key);
					}
				}
			}
		}
		#
		# start the query
		#
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from($db->quoteName('#__' . $args["table"]));
		#echo '<br andconditions>';
		#print_r($andconditions);
		if(isset($conditions))
		{
			#echo '<br conditions>';
			#print_r($conditions);
			$query->where($conditions);
		}
		foreach ($andconditions as $index => $c)
		{
			if($index) $query->andwhere($c);
			else $query->where($c);
		}
		foreach ($orconditions as $index => $c)
		{
			if($index) $query->orwhere($c);
			else $query->where($c);
		}
		if($args["sort"] && $args["sort"] != 'no') 
		{ 
			$s = explode(" ",$args["sort"]);
			if(!isset($s[1])) { $s[1]="ASC"; } #ASC is default order direction
			$query->order($db->quoteName($s[0]) . $s[1]);
	    }
		#
		# $limit is maximum number of rows to be displayed
		# $page = current pagenumber
		# so calculate offset
		#
		if($args["maxlines"])
		{
			$offset=0;
			if(is_numeric($args["maxlines"])) { $offset=($args["page"]-1)*$args["maxlines"]; }
			$query->setLimit($args["maxlines"],$offset);
		}
		$db->setQuery($query);
		$rows = $db->loadObjectList();
		return($rows);
	}
	/*
		* args["tableA"]  - relations
		* args["tableB"]  - appartments
		* $args["colA"]   - house
		* $args["colB"]   - huisnummer
		* $args["col"]	  - gebouw
		* $args["value"]  - 411
		* ["gebouw","house","appartments","huisnummer","building"]
 */
	public function JoinRecord($args)
	{
		$db = \JFactory::getDbo();

		// Create a new query object.
		$query = $db->getQuery(true);
    	$query->select(array('a.*', 'b.*'));
    	$query->from($db->quoteName('#__' . $args["tableA"] , 'a'));
		$query->join('INNER', $db->quoteName('#__' . $args["tableB"], 'b') . ' ON ' . $db->quoteName('a.'.$args["colA"]) . ' = ' . $db->quoteName('b.'.$args["colB"]));
    	$query->where($db->quoteName('a.'.$args["col"]) . ' = ' . $db->quote($args["value"]));
		$db->setQuery($query);
		$row = $db->loadObject();
		return($row);
	}	
	/**
	* display all fields of a record
	* $args['table'] - databasetable
	* $args['key'] - name of unique key
	* $args['value'] - value of unique key
	 */
	public function DisplayAllFields($args)
	{
		$html = '';
		#
		# get the column names in the table
		#
		$columns = $this->Columns($args["table"]);
		$p=$this->ReadUniqueRecord($args);
		#
		# display content of all fields
		#
		foreach($columns as $c)
		{    # table => relationtable

			$html .= '<div class="row" style="margin-bottom:2px;">';
			$html .= 	'<div class="col-md-2">';
			$html .= $c;
			$html .=	'</div>';
			$html .= '<div class="col-md-8">';
			$html .= $p->$c;
			$html .= '</div>';
			$html .= '</div>';
		}
		return($html);
	}
	/**
	 * $args['table'] - databasetable
	 * $args["column'] - distinct column
	 * $args['output'] - (string) (Optional) Any of ARRAY_A | ARRAY_N | OBJECT | OBJECT_K constants. default=OBJECT
	 */
	public function DistinctRecords($args)
	{
		$db = \JFactory::getDbo();
		$table = isset($args["table"]) ? '#__' . $args["table"] : "";
		$output = isset($args["output"]) ? $args["output"] : "OBJECT";
		$query = 'SELECT DISTINCT ' . $args['column'] . ' FROM ' . $table;
		$query .= ' ORDER BY ' . $args['column'];
		$db->setQuery($query);
		$rows = $db->loadObjectList();
		return($rows);
    }
		/**
	 * Columntype($args) 
	 * $args["columns'] - column info (not required) e.g.  [["id","nr","int"],["name","naam","string"]]
	 * $args['column'] - column nam
	 */
	public function Columntype($args)
	{
		foreach ($args['columns'] as $c)
		{
			if($c[0] == $args["column"]) { return($c[2]); }
		}
    }

}
?>