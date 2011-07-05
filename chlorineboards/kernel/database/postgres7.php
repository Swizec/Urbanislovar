<?php
///////////////////////////////////////////////////////////////////
//                                                               //
//     file:               postgres7.php                         //
//     scripter:              swizec                             //
//     contact:          swizec@swizec.com                       //
//     started on:        8th June 2005                          //
//     version:               0.2.0                              //
//                                                               //
///////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////
//                                                               //
// This program is free software; you can redistribute it        //
// and/or modify it under the terms of the GNU General Public    //
// License as published by the Free Software Foundation;         //
// either version 2 of the License, or (at your option)          //
// any later version.                                            //
//                                                               //
///////////////////////////////////////////////////////////////////
 
// original file from phpBB2

// basic security
if ( !defined( 'RUNNING_CL' ) )
{
	die( 'You bastard, this is not for you' );
}

if(!defined("SQL_LAYER"))
{

// create this class
$vars = array( 'db_connect_id', 'query_result', 'in_transaction', 'row', 'rowset', 'rownum', 'num_queries', 'last_query' );
$visible = array( 'public', 'public', 'public', 'public', 'public', 'public', 'public', 'public' );
eval( Varloader::createclass( 'sql_db', $vars, $visible ) );
// end class creation

define("SQL_LAYER","postgresql");

class sql_db extends sql_db_def
{
	//
	// Constructor
	//
	function sql_db($sqlserver, $sqluser, $sqlpassword, $database, $persistency = true)
	{
		$this->in_transaction = 0;
		$this->row = array();
		$this->rowset = array();
		$this->rownum = array();
		$this->num_queries = 0;
		$this->last_query = '';
	
		$this->connect_string = "";

		if( $sqluser )
		{
			$this->connect_string .= "user=$sqluser ";
		}

		if( $sqlpassword )
		{
			$this->connect_string .= "password=$sqlpassword ";
		}

		if( $sqlserver )
		{
			if( ereg(":", $sqlserver) )
			{
				list($sqlserver, $sqlport) = split(":", $sqlserver);
				$this->connect_string .= "host=$sqlserver port=$sqlport ";
			}
			else
			{
				if( $sqlserver != "localhost" )
				{
					$this->connect_string .= "host=$sqlserver ";
				}
			}
		}

		if( $database )
		{
			$this->dbname = $database;
			$this->connect_string .= "dbname=$database";
		}

		$this->persistency = $persistency;

		$this->db_connect_id = ( $this->persistency ) ? pg_pconnect($this->connect_string) : pg_connect($this->connect_string);

		return ( $this->db_connect_id ) ? $this->db_connect_id : false;
	}

	//
	// Other base methods
	//
	function sql_close()
	{
		if( $this->db_connect_id )
		{
			//
			// Commit any remaining transactions
			//
			if( $this->in_transaction )
			{
				@pg_exec($this->db_connect_id, "COMMIT");
			}

			if( $this->query_result )
			{
				@pg_freeresult($this->query_result);
			}

			return @pg_close($this->db_connect_id);
		}
		else
		{
			return false;
		}
	}

	//
	// Query method
	//
	function sql_query($query = "", $transaction = false)
	{
		//
		// Remove any pre-existing queries
		//
		unset($this->query_result);
		// set last query
		$this->last_query = $query;
		if( $query != "" )
		{
			$this->num_queries++;

			$query = preg_replace("/LIMIT ([0-9]+),([ 0-9]+)/", "LIMIT \\2 OFFSET \\1", $query);

			if( $transaction == BEGIN_TRANSACTION && !$this->in_transaction )
			{
				$this->in_transaction = TRUE;

				if( !@pg_exec($this->db_connect_id, "BEGIN") )
				{
					return false;
				}
			}

			$this->query_result = @pg_exec($this->db_connect_id, $query);
			if( $this->query_result )
			{
				if( $transaction == END_TRANSACTION )
				{
					$this->in_transaction = FALSE;

					if( !@pg_exec($this->db_connect_id, "COMMIT") )
					{
						@pg_exec($this->db_connect_id, "ROLLBACK");
						return false;
					}
				}

				$this->last_query_text[$this->query_result] = $query;
				$this->rownum[$this->query_result] = 0;

				unset($this->row[$this->query_result]);
				unset($this->rowset[$this->query_result]);

				return $this->query_result;
			}
			else
			{
				if( $this->in_transaction )
				{
					@pg_exec($this->db_connect_id, "ROLLBACK");
				}
				$this->in_transaction = FALSE;

				return false;
			}
		}
		else
		{
			if( $transaction == END_TRANSACTION && $this->in_transaction )
			{
				$this->in_transaction = FALSE;

				if( !@pg_exec($this->db_connect_id, "COMMIT") )
				{
					@pg_exec($this->db_connect_id, "ROLLBACK");
					return false;
				}
			}

			return true;
		}
	}

	//
	// Other query methods
	//
	function sql_numrows($query_id = 0)
	{
		if( !$query_id )
		{
			$query_id = $this->query_result;
		}

		return ( $query_id ) ? @pg_numrows($query_id) : false;
	}

	function sql_numfields($query_id = 0)
	{
		if( !$query_id )
		{
			$query_id = $this->query_result;
		}

		return ( $query_id ) ? @pg_numfields($query_id) : false;
	}

	function sql_fieldname($offset, $query_id = 0)
	{
		if( !$query_id )
		{
			$query_id = $this->query_result;
		}

		return ( $query_id ) ? @pg_fieldname($query_id, $offset) : false;
	}

	function sql_fieldtype($offset, $query_id = 0)
	{
		if( !$query_id )
		{
			$query_id = $this->query_result;
		}

		return ( $query_id ) ? @pg_fieldtype($query_id, $offset) : false;
	}

	function sql_fetchrow($query_id = 0)
	{
		if( !$query_id )
		{
			$query_id = $this->query_result;
		}

		if($query_id)
		{
			$this->row = @pg_fetch_array($query_id, $this->rownum[$query_id]);

			if( $this->row )
			{
				$this->rownum[$query_id]++;
				return $this->row;
			}
		}

		return false;
	}

	function sql_fetchrowset($query_id = 0)
	{
		if( !$query_id )
		{
			$query_id = $this->query_result;
		}

		if( $query_id )
		{
			unset($this->rowset[$query_id]);
			unset($this->row[$query_id]);
			$this->rownum[$query_id] = 0;

			while( $this->rowset = @pg_fetch_array($query_id, $this->rownum[$query_id], PGSQL_ASSOC) )
			{
				$result[] = $this->rowset;
				$this->rownum[$query_id]++;
			}

			return $result;
		}

		return false;
	}

	function sql_fetchfield($field, $row_offset=-1, $query_id = 0)
	{
		if( !$query_id )
		{
			$query_id = $this->query_result;
		}

		if( $query_id )
		{
			if( $row_offset != -1 )
			{
				$this->row = @pg_fetch_array($query_id, $row_offset, PGSQL_ASSOC);
			}
			else
			{
				if( $this->rownum[$query_id] )
				{
					$this->row = @pg_fetch_array($query_id, $this->rownum[$query_id]-1, PGSQL_ASSOC);
				}
				else
				{
					$this->row = @pg_fetch_array($query_id, $this->rownum[$query_id], PGSQL_ASSOC);

					if( $this->row )
					{
						$this->rownum[$query_id]++;
					}
				}
			}

			return $this->row[$field];
		}

		return false;
	}

	function sql_rowseek($offset, $query_id = 0)
	{

		if(!$query_id)
		{
			$query_id = $this->query_result;
		}

		if( $query_id )
		{
			if( $offset > -1 )
			{
				$this->rownum[$query_id] = $offset;
				return true;
			}
			else
			{
				return false;
			}
		}

		return false;
	}

	function sql_nextid()
	{
		$query_id = $this->query_result;

		if($query_id && $this->last_query_text[$query_id] != "")
		{
			if( preg_match("/^INSERT[\t\n ]+INTO[\t\n ]+([a-z0-9\_\-]+)/is", $this->last_query_text[$query_id], $tablename) )
			{
				$query = "SELECT currval('" . $tablename[1] . "_id_seq') AS last_value";
				$temp_q_id =  @pg_exec($this->db_connect_id, $query);
				if( !$temp_q_id )
				{
					return false;
				}

				$temp_result = @pg_fetch_array($temp_q_id, 0, PGSQL_ASSOC);

				return ( $temp_result ) ? $temp_result['last_value'] : false;
			}
		}

		return false;
	}

	function sql_affectedrows($query_id = 0)
	{
		if( !$query_id )
		{
			$query_id = $this->query_result;
		}

		return ( $query_id ) ? @pg_cmdtuples($query_id) : false;
	}

	function sql_freeresult($query_id = 0)
	{
		if( !$query_id )
		{
			$query_id = $this->query_result;
		}

		return ( $query_id ) ? @pg_freeresult($query_id) : false;
	}
	
	function sql_error($query_id = 0)
	{
		if( !$query_id )
		{
			$query_id = $this->query_result;
		}

		$result['message'] = @pg_errormessage($this->db_connect_id);
		$result['code'] = -1;

		return $result;
	}
	
	//
	// The backup part of this file, I nicked this from phpBB's admin_db_utils.php so I hope it works properly :D
	// also I made it a wee bit more compact and ofcourse 'twas edited to work in this environment
	//
	//
	// This function is used for grabbing the sequences for postgres...
	//
	function pg_get_sequences($crlf)
	{
		$get_seq_sql = "SELECT relname FROM pg_class WHERE NOT relname ~ 'pg_.*'
			AND relkind = 'S' ORDER BY relname";

		$seq = $this->sql_query($get_seq_sql);

		if( !$num_seq = $this->sql_numrows($seq) )
		{
			$return_val = "# No Sequences Found $crlf";
		}
		else
		{
			$return_val = "# Sequences $crlf";
			$i_seq = 0;

			while($i_seq < $num_seq)
			{
				$row = $this->sql_fetchrow($seq);
				$sequence = $row['relname'];

				$get_props_sql = "SELECT * FROM $sequence";
				$seq_props = $db->sql_query($get_props_sql);

				if($this->sql_numrows($seq_props) > 0)
				{
					$row1 = $this->sql_fetchrow($seq_props);

					$return_val .= "CREATE SEQUENCE $sequence start " . $row['last_value'] . ' increment ' . $row['increment_by'] . ' maxvalue ' . $row['max_value'] . ' minvalue ' . $row['min_value'] . ' cache ' . $row['cache_value'] . "; $crlf";
				}  // End if numrows > 0

				if(($row['last_value'] > 1))
				{
					$return_val .= "SELECT NEXTVALE('$sequence'); $crlf";
					unset($row['last_value']);
				}

				$i_seq++;
			} // End while..
		} // End else...
		return $returnval;
	} // End function...
	
	//
	// This function returns, will return the table def's for postgres...
	//
	function get_table_def($table, $crlf)
	{
		$schema_create = "";
		//
		// Get a listing of the fields, with their associated types, etc.
		//
		$field_query = "SELECT a.attnum, a.attname AS field, t.typname as type, a.attlen AS length, a.atttypmod as lengthvar, a.attnotnull as notnull
			FROM pg_class c, pg_attribute a, pg_type t
			WHERE c.relname = '$table'
				AND a.attnum > 0
				AND a.attrelid = c.oid
				AND a.atttypid = t.oid
			ORDER BY a.attnum";
			
		if(!$result = $this->sql_query($field_query))
		{
			return FALSE;
		} // end if..

		//
		// Ok now we actually start building the SQL statements to restore the tables
		//

		$schema_create .= "CREATE TABLE $table(";

		while ($row = $this->sql_fetchrow($result))
		{
			//
			// Get the data from the table
			//
			$sql_get_default = "SELECT d.adsrc AS rowdefault
				FROM pg_attrdef d, pg_class c
				WHERE (c.relname = '$table')
					AND (c.oid = d.adrelid)
					AND d.adnum = " . $row['attnum'];
			$def_res = $this->sql_query($sql_get_default);

			if (!$def_res)
			{
				unset($row['rowdefault']);
			}
			else
			{
				$row['rowdefault'] = @pg_result($def_res, 0, 'rowdefault');
			}

			if ($row['type'] == 'bpchar')
			{
				// Internally stored as bpchar, but isn't accepted in a CREATE TABLE statement.
				$row['type'] = 'char';
			}

			$schema_create .= '	' . $row['field'] . ' ' . $row['type'];

			if (eregi('char', $row['type']))
			{
				if ($row['lengthvar'] > 0)
				{
					$schema_create .= '(' . ($row['lengthvar'] -4) . ')';
				}
			}

			if (eregi('numeric', $row['type']))
			{
				$schema_create .= '(';
				$schema_create .= sprintf("%s,%s", (($row['lengthvar'] >> 16) & 0xffff), (($row['lengthvar'] - 4) & 0xffff));
				$schema_create .= ')';
			}

			if (!empty($row['rowdefault']))
			{
				$schema_create .= ' DEFAULT ' . $row['rowdefault'];
			}

			if ($row['notnull'] == 't')
			{
				$schema_create .= ' NOT NULL';
			}

			$schema_create .= ",";
		}
		//
		// Get the listing of primary keys.
		//

		$sql_pri_keys = "SELECT ic.relname AS index_name, bc.relname AS tab_name, ta.attname AS column_name, i.indisunique AS unique_key, i.indisprimary AS primary_key
			FROM pg_class bc, pg_class ic, pg_index i, pg_attribute ta, pg_attribute ia
			WHERE (bc.oid = i.indrelid)
				AND (ic.oid = i.indexrelid)
				AND (ia.attrelid = i.indexrelid)
				AND	(ta.attrelid = bc.oid)
				AND (bc.relname = '$table')
				AND (ta.attrelid = i.indrelid)
				AND (ta.attnum = i.indkey[ia.attnum-1])
			ORDER BY index_name, tab_name, column_name ";
		
		if(!$result = $this->sql_query($sql_pri_keys))
		{
			return FALSE;
		}

		while ( $row = $this->sql_fetchrow($result))
		{
			if ($row['primary_key'] == 't')
			{
				if (!empty($primary_key))
				{
					$primary_key .= ', ';
				}

				$primary_key .= $row['column_name'];
				$primary_key_name = $row['index_name'];
			}
			else
			{
				//
				// We have to store this all this info because it is possible to have a multi-column key...
				// we can loop through it again and build the statement
				//
				$index_rows[$row['index_name']]['table'] = $table;
				$index_rows[$row['index_name']]['unique'] = ($row['unique_key'] == 't') ? ' UNIQUE ' : '';
				$index_rows[$row['index_name']]['column_names'] .= $row['column_name'] . ', ';
			}
		}

		if (!empty($index_rows))
		{
			while(list($idx_name, $props) = each($index_rows))
			{
				$props['column_names'] = ereg_replace(", $", "" , $props['column_names']);
				$index_create .= 'CREATE ' . $props['unique'] . " INDEX $idx_name ON $table (" . $props['column_names'] . ");$crlf";
			}
		}

		if (!empty($primary_key))
		{
			$schema_create .= "	CONSTRAINT $primary_key_name PRIMARY KEY ($primary_key),$crlf";
		}

		//
		// Generate constraint clauses for CHECK constraints
		//
		$sql_checks = "SELECT rcname as index_name, rcsrc
			FROM pg_relcheck, pg_class bc
			WHERE rcrelid = bc.oid
				AND bc.relname = '$table'
				AND NOT EXISTS (
					SELECT *
						FROM pg_relcheck as c, pg_inherits as i
						WHERE i.inhrelid = pg_relcheck.rcrelid
							AND c.rcname = pg_relcheck.rcname
							AND c.rcsrc = pg_relcheck.rcsrc
							AND c.rcrelid = i.inhparent
				)";
		
		if (!$result = $this->sql_query($sql_checks))
		{
			return FALSE;
		}

		//
		// Add the constraints to the sql file.
		//
		while ($row = $this->sql_fetchrow($result))
		{
			$schema_create .= '	CONSTRAINT ' . $row['index_name'] . ' CHECK ' . $row['rcsrc'] . ",";
		}

		$schema_create = ereg_replace(',' . $crlf . '$', '', $schema_create);
		$index_create = ereg_replace(',' . $crlf . '$', '', $index_create);

		$schema_create .= "$crlf);$crlf";

		if (!empty($index_create))
		{
			$schema_create .= $index_create;
		}

		//
		// Ok now we've built all the sql return it to the calling function.
		//
		return (stripslashes($schema_create));
	}
	
	function get_table_content($table)
	{
		//
		// Grab all of the data from current table.
		//
		if (!$result = $this->sql_query("SELECT * FROM $table"))
		{
			return FALSE;
		}

		$i_num_fields = $this->sql_numfields($result);

		for ($i = 0; $i < $i_num_fields; $i++)
		{
			$aryType[] = $this->sql_fieldtype($i, $result);
			$aryName[] = $this->sql_fieldname($i, $result);
		}

		$iRec = 0;

		$out = '';
		while($row = $this->sql_fetchrow($result))
		{
			unset($schema_vals);
			unset($schema_fields);
			unset($schema_insert);
			//
			// Build the SQL statement to recreate the data.
			//
			for($i = 0; $i < $i_num_fields; $i++)
			{
				$strVal = $row[$aryName[$i]];
				if (eregi("char|text|bool", $aryType[$i]))
				{
					$strQuote = "'";
					$strEmpty = "";
					$strVal = addslashes($strVal);
				}
				elseif (eregi("date|timestamp", $aryType[$i]))
				{
					if ($empty($strVal))
					{
						$strQuote = "";
					}
					else
					{
						$strQuote = "'";
					}
				}
				else
				{
					$strQuote = "";
					$strEmpty = "NULL";
				}

				if (empty($strVal) && $strVal != "0")
				{
					$strVal = $strEmpty;
				}

				$schema_vals .= " $strQuote$strVal$strQuote,";
				$schema_fields .= " $aryName[$i],";
			}

			$schema_vals = ereg_replace(",$", "", $schema_vals);
			$schema_vals = ereg_replace("^ ", "", $schema_vals);
			$schema_fields = ereg_replace(",$", "", $schema_fields);
			$schema_fields = ereg_replace("^ ", "", $schema_fields);

			//
			// Take the ordered fields and their associated data and build it
			// into a valid sql statement to recreate that field in the data.
			//
			$schema_insert = "INSERT INTO $table ($schema_fields) VALUES($schema_vals);\n";
			$out .= $schema_insert;
		}
		return $out;
	}// end function get_table_content...
	
	function sql_backup()
	{
		$list = $this->pg_get_sequences( "\n" );
		
		$sql = "SELECT relname FROM pg_class";
		if ( !$result = $this->sql_query( $sql ) )
		{
			return FALSE;
		}
		while ( $row = $this->sql_fetchrow( $result ) )
		{
			$list .= $this->get_table_def( $row[ 'relname' ], "\n" );
			$list .= $this->get_table_content( $row[ 'relname' ] );
		}
		return explode( "\n", $list );
	}

} // class ... db_sql

} // if ... defined

?>