<?php
class Build
{
	public $expansion;
	public $major;
	public $minor;
	public $build;

	public function __construct($build)
	{
		$splitBuild = explode(".", $build);
		$this->expansion = $splitBuild[0];
		$this->major = $splitBuild[1];
		$this->minor = $splitBuild[2];
		$this->build = $splitBuild[3];
	}

	public function __toString()
	{
		return $this->expansion.".".$this->major.".".$this->minor.".".$this->build;
	}
}

class BuildRange
{
	public $minBuild;
	public $maxBuild;

	public function __construct($minBuild, $maxBuild)
	{
		$this->minBuild = new Build($minBuild);
		$this->maxBuild = new Build($maxBuild);
	}

	public function __toString()
	{
		return $this->minBuild."-".$this->maxBuild;
	}

	public function Contains(Build $build) : bool
	{
		return
		$build->expansion >= $this->minBuild->expansion && $build->expansion <= $this->maxBuild->expansion &&
		$build->major >= $this->minBuild->major && $build->major <= $this->maxBuild->major &&
		$build->build >= $this->minBuild->build && $build->build <= $this->maxBuild->build;
	}
}

class DBDReader
{
	public function Read(string $file, bool $validate = false){
		if(!file_exists($file)){
			throw new Exception("Unable to find definitions file " . $file);
		}

		$dbd['columnDefinitions'] = array();

		$lines = explode("\n", file_get_contents($file));
		$lineNumber = 0;

		if(substr($lines[0], 0, 7) == "COLUMNS"){
			$lineNumber++;
			while(true){
				$line = $lines[$lineNumber++];

				// Column definitions are done after encountering a newline
				if (empty($line)) break;

				// Create a new column definition to store information in
				$columnDefinition = array();

				/* TYPE READING */
				// List of valid types, uint should be removed soon-ish
				$validTypes = array("uint", "int", "float", "string", "locstring");

				// Read line up to space (end of type) or < (foreign key)
				$spacePos = strpos($line, ' ');
				$larrPos = strpos($line, '<');

				if(!empty($larrPos) && $spacePos > $larrPos){
					$type = substr($line, 0, $larrPos);
				}else{
					$type = substr($line, 0, $spacePos);
				}

				// Check if type is valid, throw exception if not!
				if (!in_array($type, $validTypes))
				{
					throw new Exception("Invalid type: " . $type . " on line " . $lineNumber);
				}
				else
				{
					$columnDefinition['type'] = $type;
				}

				/* FOREIGN KEY READING */
				// Only read foreign key if foreign key identifier is found right after type (it could also be in comments)
				if (substr($line, 0, strlen($columnDefinition['type']) + 1) == $columnDefinition['type']."<")
				{
					// Read foreign key info between < and > without < and > in result, then split on :: to separate table and field
					$foreignKey = substr($line, strpos($line, '<') + 1, strpos($line, '>') - strpos($line, '<') - 1);
					$foreignKey = explode("::", $foreignKey);

					// There should only be 2 values in foreignKey (table and col)
					if(count($foreignKey) != 2)
					{
						throw new Exception("Invalid foreign key length: " .  strlen($foreignKey));
					}
					else
					{
						$columnDefinition['foreignTable'] = $foreignKey[0];
						$columnDefinition['foreignColumn'] = $foreignKey[1];
					}
				}

				/* NAME READING */
				$name = "";
				// If there's only one space on the line at the same locaiton as the first one, assume a simple line like "uint ID", this can be better
				if(strrpos($line, ' ') == strpos($line, ' '))
				{
					$name = substr($line, strpos($line, ' ') + 1);
				}
				else
				{
					// Location of first space (after type)
					$start = strpos($line, ' ');

					// Second space (after name)
					$end = strpos($line, ' ', $start + 1) - $start - 1;

					$name = substr($line, $start + 1, $end);
				}

				// If name ends in ? it's unverified
				if ($name[strlen($name) - 1] == "?")
				{
					$columnDefinition['verified'] = false;
					$name = str_replace("?", "", $name);
				}
				else
				{
					$columnDefinition['verified'] = true;
				}

				/* COMMENT READING */
				if (strpos($line, "//") !== false)
				{
					$columnDefinition['comment'] = trim(substr($line, strpos($line, "//") + 2));
				}

				// Add to dictionary
				if (array_key_exists($name, $dbd['columnDefinitions']))
				{
					throw new Exception("Collision with existing column name while adding new column name!");
				}
				else
				{
					$dbd['columnDefinitions'][$name] = $columnDefinition;
				}
			}
		}else{
			throw new Exception("File does not start with column definitions!");
		}

		$versionDefinitions = array();
		$definitions = array();
		$layoutHashes = array();
		$comment = "";
		$builds = array();
		$buildRanges = array();

		for($i = $lineNumber; $i < count($lines); $i++)
		{
			$line = $lines[$i];

			if (empty($line))
			{
				$dbd['versionDefinitions'][] = array(
					"builds" => $builds,
					"buildRanges" => $buildRanges,
					"layoutHashes" => $layoutHashes,
					"comment" => $comment,
					"definitions" => $definitions
				);

				$definitions = array();
				$layoutHashes = array();
				$comment = "";
				$builds = array();
				$buildRanges = array();
			}

			if (substr($line, 0, 6) == "LAYOUT")
			{
				$splitLayoutHashes = str_replace("LAYOUT ", "", $line);
				$splitLayoutHashes = explode(", ", $splitLayoutHashes);
				$layoutHashes = array_merge($layoutHashes, $splitLayoutHashes);
			}

			if (substr($line, 0, 5) == "BUILD")
			{
				$splitBuilds = str_replace("BUILD ", "", $line);
				$splitBuilds = explode(", ", $splitBuilds);
				foreach($splitBuilds as $splitBuild)
				{
					if (strpos($splitBuild, "-") !== false)
					{
						$splitRange = explode("-", $splitBuild);
						$buildRanges[] = new BuildRange($splitRange[0], $splitRange[1]);
					}
					else{
						$builds[] = new Build($splitBuild);
					}
				}
			}

			if (substr($line, 0, 7) == "COMMENT")
			{
				$comment = trim(str_replace($line, "COMMENT", ""));
			}

			if (substr($line, 0, 6) != "LAYOUT" && substr($line, 0, 5) != "BUILD" && substr($line, 0, 7) != "COMMENT" && !empty($line))
			{
				$definition = array();

				// Default to everything being inline
				$definition['isNonInline'] = false;

				if (strpos($line, "$") !== false)
				{
					$annotationStart = strpos($line, "$");
					$annotationEnd = strpos($line, "$", 1);

					$annotations = substr($line, $annotationStart + 1, $annotationEnd - $annotationStart - 1);
					$annotations = explode(",", $annotations);

					if (in_array("id", $annotations))
					{
						$definition['isID'] = true;
					}

					if (in_array("noninline", $annotations))
					{
						$definition['isNonInline'] = true;
					}

					if (in_array("relation", $annotations))
					{
						$definition['isRelation'] = true;
					}

					$line = substr($line, $annotationEnd + 1);
				}

				if (strpos($line, "<") !== false)
				{
					$size = substr($line, strpos($line, '<') + 1, strpos($line, '>') - strpos($line, '<') - 1);

					if ($size[0] == 'u')
					{
						$definition['isSigned'] = false;
						$definition['size'] = (int)str_replace("u", "", $size);
						$line = str_replace("<u" . $definition['size'] . ">", "", $line);

					}
					else
					{
						$definition['isSigned'] = true;
						$definition['size'] = (int)$size;
						$line = str_replace("<" . $definition['size'] . ">", "", $line);
					}
				}

				if (strpos($line, "[") !== false)
				{
					$definition['arrLength'] = substr($line, strpos($line, '[') + 1, strpos($line, ']') - strpos($line, '[') - 1);
					$line = str_replace("[" . $definition['arrLength'] . "]", "", $line);
				}

				if (strpos($line, "//") !== false)
				{
					$definition['comment'] = trim(substr($line, strpos($line, "//") + 2));
					$line = substr($line, 0, strpos($line, "//"));
				}

				$definition['name'] = trim($line);

				// Check if this column name is known in column definitions, if not throw exception
				if (!array_key_exists($definition['name'], $dbd['columnDefinitions']))
				{
					throw new Exception("Unable to find " . $definition['name'] . " (".basename($file).") in column definitions: " . print_r($dbd['columnDefinitions'], true));
				}

				$definitions[] = $definition;
			}

			if (count($lines) == ($i + 1) && !empty($definitions))
			{
				$dbd['versionDefinitions'][] = array(
					"builds" => $builds,
					"buildRanges" => $buildRanges,
					"layoutHashes" => $layoutHashes,
					"comment" => $comment,
					"definitions" => $definitions
				);
			}
		}

		return $dbd;
	}
}