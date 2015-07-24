<?php

namespace Jacere\Bramble\Core\Serialization;

class PhpSerializationHelper {
	
	public static function serialize_array(array $array, PhpSerializationMap $map) {
		$lines = [];
		foreach ($array as $key => $value) {
			$value = self::serialize_value($value, $map);
			$key = self::serialize_value($key, $map);
			$lines[] = "$key=>$value";
		}
		return sprintf('[%s]', implode(",", $lines));
	}
	
	public static function serialize_value($value, PhpSerializationMap $map) {
		if (is_array($value)) {
			return self::serialize_array($value, $map);
		}
		else if (is_object($value)) {
			if (!($value instanceof IPhpSerializable)) {
				throw new \Exception('Object cannot be serialized');
			}
			return $value->phpSerializable($map);
		}
		else {
			return var_export($value, true);
		}
	}
	
	public static function serialize($value) {
		$map = new PhpSerializationMap();
		$value = self::serialize_value($value, $map);
		$lines = [
			'<?php'
		];
		foreach ($map->using() as $key => $alias) {
			$lines[] = "use $key as $alias;";
		}
		$lines[] = "return $value;";
		return implode("\n", $lines);
	}
}
