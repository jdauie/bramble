<?php

namespace Jacere\Bramble\Core\Serialization;

interface IPhpSerializable {
	
	/**
	 * @param PhpSerializationMap $map
	 */
	public function phpSerializable(PhpSerializationMap $map);
}
