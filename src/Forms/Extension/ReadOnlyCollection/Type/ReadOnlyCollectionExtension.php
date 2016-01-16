<?php

/**
 * This file is part of the Arachne
 *
 * Copyright (c) Jáchym Toušek (enumag@gmail.com)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Arachne\Forms\Extension\ReadOnlyCollection\Type;

use Arachne\Forms\Extension\ReadOnlyCollection\EventListener\ReadOnlyCollectionListener;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class ReadOnlyCollectionExtension extends AbstractTypeExtension
{

	/**
	 * {@inheritdoc}
	 */
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder->addEventSubscriber(new ReadOnlyCollectionListener());
	}

	/**
	 * {@inheritdoc}
	 */
	public function getExtendedType()
	{
		return 'Symfony\Component\Form\Extension\Core\Type\CollectionType';
	}

}
