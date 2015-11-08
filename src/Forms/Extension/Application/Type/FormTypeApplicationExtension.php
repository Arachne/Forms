<?php

/**
 * This file is part of the Arachne
 *
 * Copyright (c) Jáchym Toušek (enumag@gmail.com)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Arachne\Forms\Extension\Application\Type;

use Arachne\Forms\Extension\Application\ApplicationRequestHandler;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class FormTypeApplicationExtension extends AbstractTypeExtension
{

	/** @var ApplicationRequestHandler */
	private $requestHandler;

	public function __construct()
	{
		$this->requestHandler = new ApplicationRequestHandler();
	}

	/**
	 * {@inheritdoc}
	 */
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder->setRequestHandler($this->requestHandler);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getExtendedType()
	{
		return 'Symfony\Component\Form\Extension\Core\Type\FormType';
	}

}
