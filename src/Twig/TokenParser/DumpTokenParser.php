<?php

/**
 * This file is part of the Arachne
 *
 * Copyright (c) Jáchym Toušek (enumag@gmail.com)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Arachne\Twig\TokenParser;

use Arachne\Twig\Node\DumpNode;
use Twig_Token;
use Twig_TokenParser;

/**
 * Token Parser for the 'dump' tag.
 *
 * Dump variables with:
 * <pre>
 *  {% dump %}
 *  {% dump foo %}
 *  {% dump foo, bar %}
 * </pre>
 *
 * @author Julien Galenski <julien.galenski@gmail.com>
 */
class DumpTokenParser extends Twig_TokenParser
{

	/**
	 * {@inheritdoc}
	 */
	public function parse(Twig_Token $token)
	{
		$values = null;
		if (!$this->parser->getStream()->test(Twig_Token::BLOCK_END_TYPE)) {
			$values = $this->parser->getExpressionParser()->parseMultitargetExpression();
		}
		$this->parser->getStream()->expect(Twig_Token::BLOCK_END_TYPE);

		return new DumpNode($this->parser->getVarName(), $values, $token->getLine(), $this->getTag());
	}

	/**
	 * {@inheritdoc}
	 */
	public function getTag()
	{
		return 'dump';
	}

}
